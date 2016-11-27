<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 26.06.2016
 * Time: 22:50
 */

namespace App\AdminModule\Presenters;

use App\Model\ServersManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Class ServersPresenter
 * @Secured
 * @package App\AdminModule\Presenters
 * @author Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Nezabudni napísať k čomu tento modul slúži !
 */
class ServersPresenter extends BasePresenter
{
    /**
     * @var ServersManager
     */
    private $serverManager;

    /**
     * ServerStatusPresenter constructor.
     * @param ServersManager $serverManager
     */
    public function __construct(ServersManager $serverManager)
    {
        parent::__construct();
        $this->serverManager = $serverManager;
    }

    public function beforeRender()
    {
        if ($this->user->isLoggedIn()) {
            if (!$this->perm->isInRole($this->user->id, 'S')) {
                $this->flashMessage('K tejto sekcii nemáš prístup');
                $this->redirect(':Front:Homepage:default');
            }
        }

    }

    /**
     * @return Form $form Formulár pre pridanie servera
     * Vytvára a vracia komponentu formulára pre pridanie servera
     */
    protected function createComponentServerForm()
    {
        $select = $this->serverManager->getGames();

        $form = new Form();
        $form->addSelect('games_id')->setItems($select);
        $form->addText('name')->setRequired();
        $form->addText('ip')->setRequired();
        $form->addText('password');

        $form->addSubmit('submit', 'Pridať server');
        $form->onSuccess[] = [$this, 'ServerFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form Formulár pre pridanie servera
     * @param ArrayHash $values Hodnoty z formulára
     */
    public function serverFormSucceeded(Form $form, $values){

        $this->serverManager->addServer($values);
        $this->flashMessage('server pridaný', self::GREEN);
        $this->redirect('this');
    }

    /**
     *
     */
    public function renderDefault(){
        $this->template->servers = $this->serverManager->getServers();
    }

    /**
     * @param int $id
     */
    public function actionDelete($id){
        $this->serverManager->deleteServer($id);
        $this->flashMessage('Server bol vmazaný', self::RED);
        $this->redirect('Servers:Default');
    }



}