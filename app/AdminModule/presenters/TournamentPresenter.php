<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 31.05.2016
 * Time: 15:48
 */

namespace App\AdminModule\Presenters;

use App\Model\LeagueManager;
use App\Model\TournamentManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;


/**
 * Class TournamentPresenter
 * @package App\AdminModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter/Model na správu XXX
 */
class TournamentPresenter extends BasePresenter
{

    /** @var  TournamentManager */
    private $tournamentManager;

    /** @var LeagueManager */
    private $leagueManager;

    /** @var UserManager */
    private $userManager;

    /**
     * TournamentPresenter constructor.
     * @param TournamentManager $tournamentManager
     * @param LeagueManager $leagueManager
     * @param UserManager $userManager
     */
    public function __construct(TournamentManager $tournamentManager, LeagueManager $leagueManager, UserManager $userManager)
    {
        $this->tournamentManager = $tournamentManager;
        $this->leagueManager = $leagueManager;
        $this->userManager = $userManager;
    }


    protected function createComponentCreateTournament()
    {

        $leagues = $this->leagueManager->getLeagues2()->fetchPairs('id', 'name');

        $form = new Form();

        $form->addText('name');

        $form->addSelect('leagues_id')->setItems($leagues);

        $form->addText('start');
        $form->addSelect('max_teams')->setItems(array(
            '8' => 8,
            '16' => 16,
            '32' => 32,
        ));
        $form->addText('prices');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'createTournamentSucceeded'];

        return $form;

    }

    public function createTournamentSucceeded(Form $form, $values)
    {
        if ($this->action != 'edit'){
        $this->tournamentManager->createTournament($values);
        $this->flashMessage('turnaj bol vytvorený');
        }
        else{
            $this->flashMessage('turnaj bol editovaný');
            $this->tournamentManager->updateTournament($this->getParameter('id'), $values);
        }
        $this->redirect('Tournament:list');

    }

    public function renderList()
    {
        $this->template->tournament = $this->tournamentManager->getAllTournament();
    }

    public function renderEdit($id)
    {
        $query = $this->tournamentManager->getTournament($id);
        $this['createTournament']->setDefaults($query);
    }

    public function renderReply(){
        $this->template->reply = $this->tournamentManager->getRequest();
    }

    protected function createComponentReplyForm($id){
        $form = new Form();

        $form->addTextArea('answer');
        $form->addHidden('id');
        $form->addHidden('user_id');
        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'createReplySucceeded'];

        return $form;
    }

    public function createReplySucceeded(Form $form, $values){
        $this->tournamentManager->updateRequest($values->id, $values);
        $this->flashMessage('Na žiadosť|sťažnosť bolo odpovedané');
        $this->userManager->insertNotification($values->user_id, 'Na tvoju sťažnosť žiadosť bolo odpovedané');
        $this->redirect('this');
    }


}