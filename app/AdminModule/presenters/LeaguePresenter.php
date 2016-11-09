<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 31.05.2016
 * Time: 15:48
 */

namespace App\AdminModule\Presenters;

use App\Model\LeagueManager;
use Nette\Application\UI\Form;
use Nette\Database\ForeignKeyConstraintViolationException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Image;

/**
 * Class LeaguePresenter
 * @package App\AdminModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu líg
 */
class LeaguePresenter extends BasePresenter
{
    /** @var LeagueManager Instance triedy modelu pre prácu s ligov */
    public $leagueManager;

    /**
     * LeaguePresenter constructor.
     * @param LeagueManager $leagueManager Automaticky injektovana instance triedy modelu pre prácu s ligov
     */
    public function __construct(LeagueManager $leagueManager)
    {
        $this->leagueManager = $leagueManager;
    }

    /**
     * Render dát do Game.latte
     */
    public function renderGame()
    {
        $this->template->games = $this->leagueManager->getGames();
    }

    /**
     * Render dát do Default.latte
     */
    public function renderDefault()
    {
        $this->template->leagues = $this->leagueManager->getLeagues();
    }

    /**
     * @param $id
     */
    public function renderManage($id)
    {
        $this->template->data = $this->leagueManager->getLeague($id);
        $this->template->admins = $this->leagueManager->getAdmins($id);
        $this['leagueEdit']->setDefaults($this->leagueManager->getLeague($id));
    }

    /**
     * @return Form
     * Vytvára a vracia komponentu formulára pre pridanie hry
     */
    protected function createComponentGamesForm()
    {
        $form = new Form();
        $form->addText('name', 'Názov hry:');
        $form->addUpload('img', 'Ikonka hry');
        $form->addSelect('type', 'Typ hry')->setItems(array(
            'Steam' => 'Steam',
            'Riot' => 'Riot',
            'Ostatné' => 'Ostatné',
        ));
        $form->addSubmit('submit', 'Pridať');
        $form->onSuccess[] = [$this, 'GameFormSucceeded'];
        return $form;
    }

    /**
     * @param Form      $form   Formulár pre pridanie hry
     * @param ArrayHash $values Hodnoty z formulára
     *                          Callback po odoslaní formulára pre pridanie hry
     */
    public function GameFormSucceeded(Form $form, $values)
    {
        if ($values['img']->isImage() and $values['img']->isOk()) {
            $file = $values['img']; //Prehodenie do $file
            $file_name = $file->getSanitizedName();
            $file->move($this->context->parameters['wwwDir'] . '/img/games_icon/' . $file_name);
            $image = Image::fromFile($this->context->parameters['wwwDir'] . '/img/games_icon/' . $file_name);
            $image->resize(16, 16, Image::EXACT);
            $image->sharpen();
            $image->save($this->context->parameters['wwwDir'] . '/img/games_icon/' . $file_name);
            $values['img'] = $file_name;
            //TODO: random string na meno
        }

        $this->leagueManager->addGame($values);
        $this->flashMessage('Hra pridaná', self::GREEN);
        $this->redirect(':Admin:League:Game');
    }

    /**
     * @param int $id
     * Vymazanie hry
     */
    public function actionDelete($id)
    {
        $this->leagueManager->deleteGame($id);
        $this->flashMessage('Hra bola vymazaná', self::RED);
        $this->redirect(':Admin:League:Game');
    }

    /**
     * @return Form
     * Vytvára a vracia komponentu formulára pre pridanie ligy
     */
    protected function createComponentLeagueForm()
    {
        $form = new Form();
        $form->addText('name')->setAttribute("placeholder", 'Název ligy');

        $form->addSelect('game')->setItems(array(
            'Counter-Strike: Global Offensive' => 'Counter-Strike: Global Offensive',
            'Dota 2' => 'Dota 2',
            'Hearthstone' => 'Hearthstone',
            'League of Legends' => 'League of Legends'
        ));

        $form->addSelect('players')->setItems(array(
            '1' => '1vs1',
            '2' => '2vs2',
            '3' => '3vs3',
            '4' => '4vs4',
            '5' => '5vs5'
        ));

        $form->addText('game_account');
        $form->addText('required');

        $form->addText('start_date')->setType('datetime');
        $form->addText('end_date')->setType('datetime');

        $form->addText('about');
        $form->addText('prices');
        $form->addText('rules');

        $form->addTextArea('maps')->setAttribute('placeholder', "Zadajte mapy pre ligu. Mapy oddelujte čiarkou...");


        $form->addSubmit('submit', 'Pridať');

        $form->onSuccess[] = [$this, 'LeagueFormSucceeded'];
        return $form;

    }

    /**
     * @param Form      $form   Formulár pre pridanie ligy
     * @param ArrayHash $values Hodnoty z formulára
     *                          Callback po odoslaní formulára pre pridanie ligy
     */
    public function LeagueFormSucceeded(Form $form, $values)
    {
        $this->leagueManager->addLeague($values);
        $this->flashMessage('Hra pridaná', self::GREEN);
        $this->redirect(':Admin:League:Default');
    }

    /**
     * @return Form
     */
    protected function createComponentLeagueEdit()
    {
        $form = new Form();
        $form->addText('rules');
        $form->addText('about');
        $form->addText('prices');
        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'LeagueEditSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @param      $values
     */
    public function leagueEditSucceeded(Form $form, $values)
    {
        $this->leagueManager->updateLeague($this->getParameter('id'), $values);
        $this->flashMessage('editovaná');
        $this->redirect('League:default');
    }

    /**
     * @return Form
     */
    protected function createComponentAddAdmin()
    {
        $form = new Form();
        $form->addText('users_id')->setType('number');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'AddAdminSucceeded'];
        return $form;
    }

    /**
     * @param Form $form
     * @param      $values
     */
    public function addAdminSucceeded(Form $form, $values)
    {
        $values->leagues_id = $this->getParameter('id');

        try {
            $this->leagueManager->addAdmin($values);
            $this->flashMessage('editovaná');
            $this->redirect('League:default');
        } catch (ForeignKeyConstraintViolationException $e) {
            $form->addError('uživateľ s týmto ID neexistuje');
        }
    }

    /**
     * @param $id
     */
    public function handleDelete($id)
    {
        $this->leagueManager->deleteAdmin($id);
        $this->flashMessage('Admin vymazaný');
        $this->redirect('League:Default');
    }

}