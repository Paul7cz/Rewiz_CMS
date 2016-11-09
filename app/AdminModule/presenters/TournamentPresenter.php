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

    /**
     * TournamentPresenter constructor.
     * @param TournamentManager $tournamentManager
     * @param LeagueManager     $leagueManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(TournamentManager $tournamentManager, LeagueManager $leagueManager)
    {
        $this->tournamentManager = $tournamentManager;
        $this->leagueManager = $leagueManager;
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
        $this->tournamentManager->createTournament($values);
        $this->flashMessage('turnaj bol vytvorený');
        $this->redirect('this');

    }

    public function renderList()
    {
        $this->template->tournament = $this->tournamentManager->getAllTournament();
    }


}