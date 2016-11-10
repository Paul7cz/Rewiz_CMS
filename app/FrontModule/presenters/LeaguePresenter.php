<?php

namespace App\FrontModule\Presenters;

use App\Model\LeagueManager;
use App\Model\MessagesManager;
use App\Model\TournamentManager;
use App\Model\UserManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Class LeaguePresenter
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu ligy.
 */
class LeaguePresenter extends BasePresenter
{

    /** @var LeagueManager */
    private $leagueManager;

    /** @var UserManager */
    public $userManager;

    /** @var MessagesManager */
    private $messagesManager;

    /** @var  TournamentManager */
    private $tournamentManager;

    /**
     * LeaguePresenter constructor.
     * @param LeagueManager     $leagueManager
     * @param UserManager       $userManager
     * @param MessagesManager   $messagesManager
     * @param TournamentManager $tournamentManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(LeagueManager $leagueManager, UserManager $userManager, MessagesManager $messagesManager, TournamentManager $tournamentManager)
    {
        $this->leagueManager = $leagueManager;
        $this->userManager = $userManager;
        $this->messagesManager = $messagesManager;
        $this->tournamentManager = $tournamentManager;
    }


    /**
     * @param $id
     * @return bool
     * Ak je liga platná vráti TRUE
     */
    public function dateCompare($id)
    {
        $row = $this->leagueManager->getLeague($id);

        $today = new DateTime();
        $compare = new DateTime($row['end_date']);
        if ($today > $compare) {
            return FALSE; /*vypršala*/
        } else {
            return TRUE; /*beží*/
        }
    }

    public function badRequest($id)
    {
        $row = $this->leagueManager->getLeague($id);

        if (!$row) {
            throw new BadRequestException();
        }
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderDefault($id)
    {
        $this->badRequest($id);

        $this->template->league = $this->leagueManager->getLeague($id);
        $this->template->date = $this->dateCompare($id);
        $this->template->admins = $this->leagueManager->getAdmins($id);
    }

    /**
     * @param $id
     */
    public function renderRules($id)
    {
        $this->badRequest($id);

        $this->template->league = $this->leagueManager->getLeague($id);
        $this->template->date = $this->dateCompare($id);
    }

    /**
     * @param $id
     */
    public function renderContestants($id)
    {
        $this->badRequest($id);

        $this->template->league = $this->leagueManager->getLeague($id);
        $this->template->teamsNoActive = $this->leagueManager->getRegisteredTeamsNoActive($id);
        $this->template->teamsActive = $this->leagueManager->getRegisteredTeamsActive($id);
        $this->template->date = $this->dateCompare($id);
    }

    /**
     * @param $id
     */
    public function renderLadder($id)
    {
        $this->badRequest($id);

        $this->template->league = $this->leagueManager->getLeague($id);
        $this->template->date = $this->dateCompare($id);
        $this->template->teamsActive = $this->leagueManager->getRegisteredTeamsActive($id);
    }

    public function renderAll($id)
    {
        $this->template->csgo = $this->leagueManager->getLeagueCsgoPanel();
        $this->template->lol = $this->leagueManager->getLeagueLolPanel();
        $this->template->dota = $this->leagueManager->getLeagueDotaPanel();
        $this->template->hs = $this->leagueManager->getLeagueHearthstonePanel();
    }

    /**
     * @param $id
     * Prihlásenie teamu do ligy
     */
    public function actionJoin($id)
    {

        $this->badRequest($id);

        /**
         * Ak ešte nie je registrovaný
         */
        if ($this->leagueManager->checkRegisteredTeam($this->user->getIdentity()->team, $id) == FALSE) {

            /**
             * Ak sa liga ešte hraje
             */
            if ($this->dateCompare($id) == TRUE) {
                $team = $this->leagueManager->getTeam($this->user->getIdentity()->team);

                $join = $this->leagueManager->joinLeague($this->user->getIdentity()->team, $id);
                $this->messagesManager->sendMessage(array(
                    /*'sender_id' => '',*/
                    'receiver_id' => $team->owner,
                    'subject' => 'Registrácia do ligy',
                    'message' => 'Tvoj team bol úspešne registrovaný do ligy. Pre úspešne dokončenie registrácie potvrď voľbu na <a href="' . $this->link('League:confirm', $join->getPrimary()) . '">',
                ));
                $this->userManager->insertNotification($this->user->getId(), 'Tvoj tým bol zaregistrovaný do ligy.');
                $this->leagueManager->insertToPoints(array(
                    'team_id' => $this->user->getIdentity()->team,
                    'league_id' => $this->getParameter('id'),
                    'points' => 0
                ));
                $this->flashMessage('Uspešne si sa registroval do ligy');
            } else {
                $this->flashMessage('Lisga už vypršala a preto sa do nej nemôžeš registrovať');
            }
        } else {
            $this->flashMessage('Tvoj team už je registrovaný v tejto lige');
        }
        $this->redirect('League:Default', $id);
    }

    public function actionConfirm($id)
    {
        $this->leagueManager->confirmTeam($id);
        $this->flashMessage('potvrdiť');
        $this->redirect('Homepage:Default');
    }

    public function teamStats($id)
    {
        //TODO: Výpis teamov spolu s bodmi
    }

}
