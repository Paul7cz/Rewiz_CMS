<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 29.05.2016
 * Time: 0:36
 */

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Tournament;
use App\Model\LeagueManager;
use App\Model\MessagesManager;
use App\Model\TournamentManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use Tracy\Debugger;
use Nette\Utils\Image;
use IPub\VisualPaginator\Components as VisualPaginator;


/**
 * Class LoginPresenter
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na prihlásenie do systému
 */
class TournamentPresenter extends BasePresenter
{
    /** @var TournamentManager */
    private $tournamentManager;

    /** @var LeagueManager */
    private $leagueManager;

    /** @var MessagesManager */
    private $messagesManager;

    /** @var UserManager */
    public $userManager;

    /**
     * TournamentPresenter constructor.
     * @param TournamentManager $tournamentManager
     * @param LeagueManager $leagueManager
     * @param MessagesManager $messagesManager
     * @param UserManager $userManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(TournamentManager $tournamentManager, LeagueManager $leagueManager, MessagesManager $messagesManager, UserManager $userManager)
    {
        $this->tournamentManager = $tournamentManager;
        $this->leagueManager = $leagueManager;
        $this->messagesManager = $messagesManager;
        $this->userManager = $userManager;
    }


    /**
     * Porovnávanie času
     * @param $id
     * @return bool
     */
    public function tournamentDateCompare($id)
    {
        $row = $this->tournamentManager->getTournament($id);

        $today = new DateTime();

        $compare = new DateTime($row['start']);

        if ($today <= $compare) {
            return TRUE; /*vypršala*/
        } else {
            return FALSE; /*beží*/
        }
    }

    public function renderDefault($id)
    {
        $this->template->tournament = $this->tournamentManager->getTournament($id);
        $this->template->team_count = $this->tournamentManager->getRegisteredTeamsActive($id)->count('*');
        $this->template->date = $this->tournamentDateCompare($id);
    }

    public function renderRules($id)
    {
        $this->template->tournament = $this->tournamentManager->getTournament($id);
        $this->template->team_count = $this->tournamentManager->getRegisteredTeamsActive($id)->count('*');
        $this->template->date = $this->tournamentDateCompare($id);
    }

    public function renderTeams($id)
    {
        $this->template->tournament = $this->tournamentManager->getTournament($id);
        $this->template->active = $this->tournamentManager->getRegisteredTeamsActive($id);
        $this->template->noactive = $this->tournamentManager->getRegisteredTeamsNoactive($id);
        $this->template->team_count = $this->tournamentManager->getRegisteredTeamsActive($id)->count('*');
        $this->template->date = $this->tournamentDateCompare($id);

    }

    public function teamsCount($id)
    {
        return $this->tournamentManager->getRegisteredTeamsActive($id)->count('*');
    }

    protected function createComponentVisualPaginator()
    {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }

    public function renderAll()
    {
        $tournament = $this->tournamentManager->getAllTournament();

        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $tournament->count();

        $tournament->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->data = $tournament;
    }


    public function renderPlayoff($id)
    {
        $this->template->tournament = $this->tournamentManager->getTournament($id);
        $this->template->team_count = $this->tournamentManager->getRegisteredTeamsActive($id)->count('*');
        $this->template->date = $this->tournamentDateCompare($id);
        $this->template->playoff = $this->tournamentManager->getMatches($id);
        $this->template->rounds = $this->tournamentManager->test($id);
    }

    public function renderMatch($id)
    {
        $this->template->match = $this->tournamentManager->getMatch($id);
        $this->template->league = $this->tournamentManager->getLeague($id);
        $this->template->logs = $this->tournamentManager->getMatchLogs($id)->order('id DESC')->fetchAll();
        $this->template->screenshots = $this->tournamentManager->getScreenshots($id)->fetchAll();
        $this->template->demos = $this->tournamentManager->getDemos($id)->fetchAll();
        $this->template->reports = $this->tournamentManager->getReports($id)->fetchAll();
    }

    /**
     * Registrácia do turnaja
     * @param $id
     */
    public function actionJoin($id)
    {
        $tournament = $this->tournamentManager->getTournament($id);

        /**
         * Ak je uživateľ registrovaný v lige
         */
        if ($this->leagueManager->checkRegisteredTeam($this->user->getIdentity()->team, $tournament->leagues_id) == TRUE) {

            /**
             * Ak sa už neregistroval
             */
            if ($this->tournamentManager->checkRegisteredTeam($id, $this->user->getIdentity()->team) == FALSE) {

                /**
                 * Ak sa turnaj ešte nezačal
                 */
                if ($this->tournamentDateCompare($id) == TRUE) {
                    $team = $this->leagueManager->getTeam($this->user->getIdentity()->team);
                    $join = $this->tournamentManager->joinTournament($id, $this->user->getIdentity()->team);

                    $this->messagesManager->sendMessage(array(
                        /*'sender_id' => '',*/
                        'receiver_id' => $team->owner,
                        'subject' => 'Registrácia do turnaja',
                        'message' => 'Pre dokončenie registrácie do turnaja kliknite na link <a href="' . $this->link('Tournament:confirm', $join->getPrimary()) . '">dokončiť registráciu do turnaja</a>',
                    ));
                    $this->flashMessage('Uspešne si sa registroval do turnaja');

                } else {
                    $this->flashMessage('Turnaj už vypršala a preto sa do nej nemôžeš registrovať');
                }
            } else {
                $this->flashMessage('Tvoj team už je registrovaný v tomto turnaji');
            }
        } else {
            $this->flashMessage('Pred registráciou do turnaja sa musíš registrovať do ligy');
        }
        $this->redirect('Tournament:Default', $id);
    }

    public function actionConfirm($id)
    {

        $this->tournamentManager->confirmTeam($id);
        $this->flashMessage('Uspešne ste sa registrovali do turnaja čakajte na dalšie pokyny administrátora Turnaja');
        $this->redirect('Homepage:Default');
    }

    /**
     * Nešahať na to !!!
     * @param $id
     */
    public function actionGenerate($id)
    {
        $team_count = $this->tournamentManager->getRegisteredTeamsActive2($id);

        /** Lukáš !!! */
        /* if ($team_count != 8 OR $team_count != 16 OR $team_count != 32 OR $team_count != 64) {
             $this->flashMessage('Nemôžeš spustiť turnaj kvôli nedostatku hračov');
             $this->redirect('Tournament:playoff', $this->getParameter('id'));
         }*/

        $createPairs = function ($_maxTeams, $_ids, $_shuffle = FALSE) {
            if ($_shuffle) {
                shuffle($_ids);
            }

            for ($i = 0; count($_ids) < $_maxTeams; $i++) {
                $_ids[] = NULL;
            }

            $matches = [];
            $half = count($_ids) / 2;
            for ($i = 0; $i < $half; $i++) {
                $matches[] = [
                    'team1' => $_ids[$i],
                    'team2' => $_ids[$half + $i]
                ];
            }

            return $matches;

        };

        // aktualni kolo
        $currentRound = $this->tournamentManager->getMaxRound($id);
        Debugger::barDump($currentRound);


        if (!$currentRound) {

            /* Vytiahnutie ID teamov ktorý sa zúčastnia turnaja */
            $row = $this->tournamentManager->getRegisteredTeamsActive($id);

            /* Parsovanie ID */
            $ids = array_keys($row->fetchAssoc('team_id'));

            /* Max teams 8/16/32/64 */
            $maxTeams = $this->tournamentManager->getRound($id);

            $generatedMatches = $createPairs($maxTeams, $ids, TRUE);

        } else {
            if ($this->tournamentManager->getNotClosedMatches($id, $currentRound)->count('*') >= 1) {
                $this->flashMessage('Všetky zápasy neboli uzatvorené nemôžeš generovať dalšie kolo');
            } else {

                $matches = $this->tournamentManager->getRoundMatches($id, $currentRound);

                $winners = [];
                foreach ($matches as $match) {
                    if ($match['score1'] < $match['score2']) {
                        $winners[] = $match['team2_id'];
                    } else {
                        $winners[] = $match['team1_id'];
                    }
                }

                $generatedMatches = $createPairs(count($winners), $winners);

                if (count($winners) == 1) {
                    $this->tournamentManager->updateWinner($currentRound + 1, $winners[0]);
                    $this->tournamentManager->closeTournament($id);
                }

            }
        }

        if (isset($generatedMatches)) {
            Debugger::barDump($generatedMatches);

            foreach ($generatedMatches as $match) {

                $test = $this->tournamentManager->insertMatch($id, $match, $currentRound + 1);
                $this->tournamentManager->createMatchLog('Zápas bol úspešne vytorený', $this->user->getId(), $test->getPrimary());
            }
        }

        $this->redirect('Tournament:Playoff', $id);

    }

    public function createComponentTournament()
    {
        $control = new Tournament($this->tournamentManager);
        return $control;
    }

    public function actionScore($id)
    {

//        Vytahnu data ze zapasu
//        zjistim jestli sedi zakladatel druheho teamu s mojim id
//        potvrdim score (vice v modelu)

        $match = $this->tournamentManager->getMatch($id);

        if ($match->team2->owner == $this->user->getId()) {

            $this->tournamentManager->newConfirmScore($id);

            $this->tournamentManager->createMatchLog('Zápas bol potvrdený a uzatvorený', $this->user->getId(), $this->getParameter('id'));
            $this->flashMessage('Skore zápasu bolo atualizované a zápas bol uzatvorený');
        } else {
            $this->flashMessage('Nejsi zakladatel druhehé teamu v tomto zápase');
        }
        $this->redirect('Tournament:all');
    }

    /* SCORE */
    public function createComponentNewScore()
    {
        $form = new Form();

        $form->addText('score1', '')->setDefaultValue(0)->setRequired();
        $form->addText('score2', '')->setDefaultValue(0)->setRequired();

        $form->addSubmit('submit', 'Odeslat');
        $form->onSuccess[] = [$this, 'newScoreSucceeded'];

        return $form;
    }

    public function newScoreSucceeded(Form $form, $values)
    {

        // TODO: ošetrit zobrazeni formu pro majitele teamu
        // TODO: ošetrit opetovne zadavani score (když uz bylo jednou zadane)
        // TODO: odeslat score druhemu teamu pro zkontrolovani

        $match = $this->tournamentManager->getMatch($this->getParameter('id'));
        $values->match_id = $this->getParameter('id');

        $dataTeam1 = $this->leagueManager->getTeam($match->team1_id);
        //$dataTeam2 = $this->leagueManager->getTeam($match->team2_id);

        $values->tournament_id = $match->tournament_id;
        $values->owner_score = $this->user->getId();

        if ($dataTeam1->owner != $this->user->getId()) {
            $this->flashMessage('Score môže pridať iba prvý team druhý ho potvrdzuje !');
            $this->redirect('this');
        }

        $this->tournamentManager->confirmScore($values);
        $this->tournamentManager->createMatchLog('Vložil score do zápasu ' . $values->score1 . ':' . $values->score2 . ' čaká sa na potvrdenie.', $this->user->getId(), $values->match_id);

        $this->messagesManager->sendMessage(array(
            'receiver_id' => $match->team2->owner,
            'subject' => 'Potvrdenie skore zo zápasu',
            'message' => 'Pre úspešne uzavretie zápasu potvrdte skore zápasu ' . $values->score1 . ':' . $values->score2 . ' <a href="' . $this->link('Tournament:score', $values->match_id) . '">kliknutím na tento link</a> alebo sa proti nemu odvolajte a podajte protest <a href="' . $this->link('Tournament:match', $this->getParameter('id')) . '">Tu</a>'
        ));
        $this->flashMessage('Score bolo vložené a čaká sa na potvdenie druhého teamu');
        $this->redirect('this');
    }

    /* ADMIN SCORE */
    public function createComponentAdminScore()
    {
        $form = new Form();

        $match = $this->tournamentManager->getMatch($this->getParameter('id'));

        $form->addText('score1', '')->setDefaultValue($match->score1)->setRequired();
        $form->addText('score2', '')->setDefaultValue($match->score2)->setRequired();

        $form->addSubmit('submit', 'Aktualizovat');
        $form->onSuccess[] = [$this, 'adminScoreSucceeded'];

        return $form;
    }

    public function adminScoreSucceeded(Form $form, $values)
    {

        $match = $this->tournamentManager->getMatch($this->getParameter('id'));
        $match = $this->tournamentManager->updateScore($this->getParameter('id'), ["score1" => $values->score1, "score2" => $values->score2, "status" => "closed"]);
        $this->tournamentManager->createMatchLog('Administrátor upravil scóre zápasu', $this->user->getId(), $this->getParameter('id'));
        $this->flashMessage('Score bolo upravené');
        $this->redirect('this');
    }

    /* REQUEST */
    public function createComponentNewRequest()
    {
        $form = new Form();

        $form->addTextarea('content', '')->setAttribute('placeholder', 'Tvoj text...')->setRequired();

        $form->addSubmit('submit', 'Odeslat');
        $form->onSuccess[] = [$this, 'newRequestSucceeded'];

        return $form;
    }

    public function newRequestSucceeded(Form $form, $values)
    {

        // TODO: ošetrit pro hrace teamu

        $values->match_id = $this->getParameter('id');
        $values->user_id = $this->user->getId();
        $values->status = 'new';
        $values->type = 1;
        $values->time = new DateTime;

        $this->tournamentManager->insertReport($values);
        $this->tournamentManager->createMatchLog('Podal žádost', $this->user->getId(), $values->match_id);

        $this->flashMessage('Žádost byla vložená');
        $this->redirect('this');
    }

    /* COMPLAINT */

    public function createComponentNewComplaint()
    {
        $form = new Form();

        $form->addTextarea('content', '')->setAttribute('placeholder', 'Tvoj text...')->setRequired();

        $form->addSubmit('submit', 'Podať protest');
        $form->onSuccess[] = [$this, 'newComplaintSucceeded'];

        return $form;
    }

    public function newComplaintSucceeded(Form $form, $values)
    {

        // TODO: ošetrit pro hrace teamu

        $values->match_id = $this->getParameter('id');
        $values->user_id = $this->user->getId();
        $values->status = 'new';
        $values->type = 2;
        $values->time = new DateTime;

        $this->tournamentManager->insertReport($values);
        $this->tournamentManager->createMatchLog('Podal stížnost', $this->user->getId(), $values->match_id);

        $this->flashMessage('Stížnost byla vložená');
        $this->redirect('this');
    }

    /* SCREENSHOTS */

    public function createComponentNewScreenshots()
    {
        $form = new Form();

        $form->addMultiUpload('img', 'Obrázky')
            ->setAttribute('id', 'filer_input')
            ->addRule(Form::MAX_LENGTH, 'Maximální počet souborů : 2', 2)
            ->addRule(Form::IMAGE, 'Obrázek musí být JPEG, PNG nebo GIF.')
            ->setRequired();

        $form->addSubmit('submit', 'Nahrať');
        $form->onSuccess[] = [$this, 'newScreenshotsSucceeded'];

        return $form;
    }

    public function newScreenshotsSucceeded(Form $form, $values)
    {

        // TODO: ošetrit pro hrace teamu

        $values->match_id = $this->getParameter('id');
        $values->user_id = $this->user->getId();

        foreach ($values['img'] as $img) {
            if ($img->isOk()) {

                $file = $img;
                $file_name = substr(str_shuffle(md5(md5(rand(0, 100) . "" . time() . "" . rand(0, 100)))), 0, 10) . $file->getSanitizedName();

                $file->move($this->context->parameters['wwwDir'] . '/img/screenshot/' . $file_name);
                $image = Image::fromFile($file);
                $image->resize(115, 115, IMAGE::EXACT);
                $image->save($this->context->parameters['wwwDir'] . '/img/screenshot/thumbs/' . $file_name);
                $values->img = $file_name;

                $this->tournamentManager->insertScreenshots($values);
                $this->tournamentManager->createMatchLog('Nahrál screen', $this->user->getId(), $values->match_id);
            }
        }

        $this->flashMessage('Vloženo');
        $this->redirect('this');
    }

    /* DEMO */

    public function createComponentNewDemo()
    {
        $form = new Form();

        $form->addText('demo_url', '')->setAttribute('placeholder', 'Url adresa dema')->addRule(Form::URL, 'Nebyla zadána platná URL')->setRequired();

        $form->addSubmit('submit', 'Odeslat demo');
        $form->onSuccess[] = [$this, 'newDemoSucceeded'];

        return $form;
    }

    public function newDemoSucceeded(Form $form, $values)
    {

        // TODO: ošetrit pro hrace teamu

        $values->match_id = $this->getParameter('id');
        $values->user_id = $this->user->getId();
        $values->time = new DateTime;

        /*Pridať colum user_id*/
        $this->tournamentManager->insertDemo($values);
        $this->tournamentManager->createMatchLog('Přidal demo', $this->user->getId(), $values->match_id);

        $this->flashMessage('Demo bylo vloženo');
        $this->redirect('this');
    }

    protected function createComponentPoint()
    {
        $team = $this->tournamentManager->getMatch($this->getParameter('id'));
        $teams = array(
            $team->team1_id => $team->team1_id,
        );

        $form = new Form();
        $form->addSelect('team_id')->setItems($teams);
        $form->addText('point');
        $form->addText('reason');

        $form->addSubmit('submit', 'Aktualizovať');
        $form->onSuccess[] = [$this, 'pointSucceeded'];

        return $form;
    }

    public function pointSucceeded(Form $form, $values){
        $this->tournamentManager->insertPointLog($values);
    }


}