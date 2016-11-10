<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 21.05.2016
 * Time: 23:34
 */

namespace App\AdminModule\Presenters;

use App\Model\LeagueManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use Tracy\Debugger;

/**
 * Class UsersPresenter
 * @Secured
 * @package App\AdminModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu uživateľov.
 */
class UsersPresenter extends BasePresenter
{
    /** @var userManager Instance triedy modelu pre prácu s uživateľmi */
    public $userManager;

    /** @var  LeagueManager */
    public $leagueManager;

    /**
     * UsersPresenter constructor.
     * @param UserManager $userManager
     * @param LeagueManager $leagueManager
     */
    public function __construct(UserManager $userManager, LeagueManager $leagueManager)
    {
        $this->userManager = $userManager;
        $this->leagueManager = $leagueManager;
    }


    /**
     * Render dát do Default.latte
     */
    public function renderDefault()
    {
        $this->template->users = $this->userManager->getUsers();
    }

    public function renderPermission()
    {
        $this->template->admins = $this->userManager->getAdmins();
    }

    /**
     * Render dát do Detail.latte
     * @param int $id
     */
    public function renderDetail($id)
    {
        $this->template->info = $this->userManager->getUser($id);
        $this->template->awards = $this->userManager->getProfileAwards($id);
    }

    /**
     * @param $id
     */
    public function handlePremiumActive($id)
    {
        $this->userManager->updateUser($id, array('premium' => '1'));
        $this->redirect('this');
    }

    /**
     * @param $id
     */
    public function handlePremiumDeactive($id)
    {
        $this->userManager->updateUser($id, array('premium' => '0'));
        $this->redirect('this');
    }

    /**
     * @param $id
     */
    public function handleLBan($id)
    {
        $this->userManager->updateUser($id, array('ban_login' => '1'));
        $this->redirect('this');
    }

    /**
     * @param $id
     */
    public function handlePBan($id)
    {
        $this->userManager->updateUser($id, array('ban_post' => '1'));
        $this->redirect('this');
    }

    /**
     * @param $id
     */
    public function handleLUnban($id)
    {
        $this->userManager->updateUser($id, array('ban_login' => NULL));
        $this->redirect('this');
    }

    /**
     * @param $id
     */
    public function handlePUnban($id)
    {
        $this->userManager->updateUser($id, array('ban_post' => NULL));
        $this->redirect('this');
    }

    public function renderEdit($id)
    {
        $this->template->data = $this->userManager->getUser($id);

        $query = $this->userManager->getUser($id);
        $this['edit']->setDefaults($query);
    }

    protected function createComponentEdit()
    {
        $form = new Form();
        $form->addText('full_name');
        $form->addText('username');
        $form->addText('email');
        $form->addTextArea('about', NULL, 3);

        $form->addSelect('gender')->setItems(array(
            'Muž' => 'Muž',
            'Žena' => 'Žena',
        ))->setPrompt('Vyberte...');

        $form->addText('birthyear');

        $form->addSelect('state')->setItems(array(
            'Slovenská Republika' => 'Slovenská Republika',
            'Česka Republika' => 'Česka Republika',
        ));

        $form->addText('csgo_sid');
        $form->addText('dota2_nick');
        $form->addText('lol_nick');
        $form->addText('hs_nick');

        $form->addSubmit('submit')->setAttribute('value', 'Aktualizovať profil');

        $form->onSuccess[] = [$this, 'editSucceeded'];

        return $form;
    }

    public function editSucceeded(Form $form, $values)
    {
        $this->userManager->updateUser($this->getParameter('id'), $values);
        $this->flashMessage('Profil bol úspečne aktualizovaný', self::GREEN);
        $this->redirect('Users:Detail', $this->user->getId());
    }

    protected function createComponentAchviements()
    {
        $form = new Form();

        $form->addText('name');
        $form->addUpload('icon');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'achviementsSucceeded'];

        return $form;
    }

    public function achviementsSucceeded(Form $form, $values)
    {

        if ($values['icon']->isImage() and $values['icon']->isOk()) {
            $file = $values['icon']; //Prehodenie do $file
            $file_name = $file->getSanitizedName();
            $file->move($this->context->parameters['wwwDir'] . '/img/awards/' . $file_name);
            $image = Image::fromFile($this->context->parameters['wwwDir'] . '/img/awards/' . $file_name);
            $image->resize(128, 128, Image::EXACT);
            $image->sharpen();
            $image->save($this->context->parameters['wwwDir'] . '/img/awards/' . $file_name);
            $values['icon'] = $file_name;
            //TODO: random string na meno
        }

        $this->userManager->createAward($values);
        $this->flashMessage('Cena vytvorená');
        $this->redirect('Users:Default');
    }

    protected function createComponentAddAch()
    {
        $ach = $this->userManager->getAwards2()->fetchPairs('id', 'name');

        $form = new Form();

        $form->addSelect('achviements_id')->setItems($ach);

        $form->onSuccess[] = [$this, 'addAchSucceeded'];

        return $form;
    }

    public function addAchSucceeded(Form $form, $values)
    {
        $values->users_id = $this->getParameter('id');
        $this->userManager->insertAward($values);
        $this->userManager->insertNotification($this->getParameter('id'), 'Práve si dostal nové ocenenie. Pozri si profil ...');
        $this->flashMessage('Cena pridaná');
        $this->redirect('this');
    }

    public function renderAwards()
    {
        $this->template->awards = $this->userManager->getAwards();
    }

    protected function createComponentUserAch()
    {
        $form = new Form();

        $users = $this->userManager->getUsers()->fetchAll();
        $achviements = $this->userManager->getAwards2()->fetchAll();

        $form->addSelect('users_id')->setItems($users)->setRequired();
        $form->addSelect('achviements_id')->setItems($achviements)->setRequired();
        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'userAchSucceeded'];

        return $form;
    }

    public function userAchSucceeded(Form $form, $values)
    {
        $this->userManager->insertAward($values);
        $this->flashMessage('Bol pridaný');
        $this->redirect('Users:default');
    }

    public function actionDelete($id)
    {
        $this->userManager->deleteAward($id);
        $this->flashMessage('Cena zmazaná');
        $this->redirect('Users:Default');
    }

    public function createComponentPerm()
    {
        $form = new Form();

        $names = $this->userManager->getNames();
        $form->addSelect('username')->setItems($names);

        $form->addSelect('role')->setItems(array(
            'admin' => 'Administrátor'
        ));

        $form->addSubmit('submit')->setAttribute('placeholder', 'Odoslať');

        $form->onSuccess[] = [$this, 'permSucceeded'];
        return $form;
    }

    public function permSucceeded(Form $form, $values)
    {
        $this->userManager->addAdmin($values->username);
        $this->flashMessage('Admin bol pridaný');
        $this->redirect('Users:default');
    }

    public function actionDerank($id)
    {
        $this->userManager->deleteAdmin($id);
        $this->flashMessage('Admin bol ostránení');
        $this->redirect('Users:Default');
    }

    protected function createComponentBanPost()
    {
        $form = new Form();
        $form->addText('ban_post');
        $form->addCheckbox('pernament');

        $form->addSubmit('submit')->setAttribute('placeholder', 'Zabanovať');

        $form->onSuccess[] = [$this, 'banPostSucceeded'];
        return $form;
    }

    public function banPostSucceeded(Form $form, $values)
    {
        if ($values->pernament == TRUE){
            $values->ban_post = '2100-01-01 00:00:00';
        }

        $this->userManager->giveBanPost($this->getParameter('id'), $values);
        $this->flashMessage('POST BAN');
        $this->redirect('this');
    }

    protected function createComponentBanLogin()
    {
        $form = new Form();
        $form->addText('ban_login');
        $form->addCheckbox('pernament');

        $form->addSubmit('submit')->setAttribute('placeholder', 'Zabanovať');

        $form->onSuccess[] = [$this, 'banLoginSucceeded'];
        return $form;
    }

    public function banLoginSucceeded(Form $form, $values)
    {
        if ($values->pernament == TRUE){
            $values->ban_login = '2100-01-01 00:00:00';
        }
        $this->userManager->giveBanLogin($this->getParameter('id'), $values);
        $this->flashMessage('Login BAN');
        $this->redirect('this');
    }

    public function actionUnbanPost($id)
    {
        $this->userManager->unbanBanPost($id);
        $this->flashMessage('Užvateľ dostal unban');
        $this->userManager->insertNotification($id, 'Práve si dostal unban na písanie príspevkov. Pozri si profil ...');
        $this->redirect('Users:detail', $id);
    }

    public function actionUnbanLogin($id)
    {
        $this->userManager->unbanBanLogin($id);
        $this->flashMessage('Užvateľ dostal unban');
        $this->userManager->insertNotification($id, 'Práve si dostal unban na login. Pozri si profil ...');
        $this->redirect('Users:detail', $id);
    }

    /**  */
    protected function createComponentAchviementsTeam()
    {
        $form = new Form();

        $form->addText('name');
        $form->addUpload('icon');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'achviementsTeamSucceeded'];

        return $form;
    }

    public function achviementsSucceededTeam(Form $form, $values)
    {

        if ($values['icon']->isImage() and $values['icon']->isOk()) {
            $file = $values['icon']; //Prehodenie do $file
            $file_name = $file->getSanitizedName();
            $file->move($this->context->parameters['wwwDir'] . '/img/awards_team/' . $file_name);
            $image = Image::fromFile($this->context->parameters['wwwDir'] . '/img/awards_team/' . $file_name);
            $image->resize(128, 128, Image::EXACT);
            $image->sharpen();
            $image->save($this->context->parameters['wwwDir'] . '/img/awards_team/' . $file_name);
            $values['icon'] = $file_name;
            //TODO: random string na meno
        }

        $this->userManager->createTeamAwards($values);
        $this->flashMessage('Cena vytvorená');
        $this->redirect('Users:Default');
    }

    protected function createComponentUserAchTeam()
    {
        $form = new Form();

        $team = $this->leagueManager->getAllTeam()->fetchPairs('id', 'name');
        $achviements = $this->userManager->getAwards2()->fetchPairs('id', 'name');

        $form->addSelect('team_id')->setItems($team)->setRequired();
        $form->addSelect('achviement_id')->setItems($achviements)->setRequired();
        $form->addText('summary')->setRequired();
        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'userAchTeamSucceeded'];

        return $form;
    }

    public function userAchTeamSucceeded(Form $form, $values)
    {
        $this->leagueManager->teamAchviementInsert($values);
        $this->flashMessage('Bol pridaný');
        $this->redirect('this');
    }

}