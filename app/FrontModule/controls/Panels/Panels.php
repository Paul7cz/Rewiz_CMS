<?php
namespace App\FrontModule\Controls;

use App\Model\LeagueManager;
use App\Model\TournamentManager;
use Nette\Application\UI\Control;
use Nette\Utils\DateTime;

/**
 * Class Panels
 * @package App\FrontModule\Controls
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Komponenta na panel turnajov
 */
class Panels extends Control
{

    /** @var TournamentManager */
    private $tournamentManager;

    /** @var  LeagueManager */
    private $leagueManager;

    /**
     * Panels constructor.
     * @param TournamentManager $tournamentManager
     * @param LeagueManager     $leagueManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(TournamentManager $tournamentManager)
    {
        parent::__construct();
        $this->tournamentManager = $tournamentManager;
    }


    /**
     * @param $id
     * @return bool
     * Ak je turnaj platný vráti TRUE
     */
    public function tournamentDateCompare($id)
    {
        $row = $this->tournamentManager->getTournament($id);

        $today = new DateTime();
        $compare = new DateTime($row['start']);
        if ($today <= $compare) {
            return TRUE; /* Registrácia */
        } else {
            return FALSE; /* Začiatok */
        }
    }


    /**
     * Počet registrovaných teamov v turnaji.
     * @param $id
     * @return int
     */
    public function teamsCount($id)
    {
        return $this->tournamentManager->getRegisteredTeamsActive($id)->count('*');
    }

    /**
     *
     */
    public function renderTournament()
    {
        $this->template->data = $this->tournamentManager->getAllTournament()->where('status = ?', 'open')->order('start ASC')->limit(5);
        $this->template->setFile(__DIR__ . '/templates/tournament.latte');
        $this->template->render();
    }

    /**
     *
     */
    public function renderAdvertisment()
    {
        $this->template->setFile(__DIR__ . '/templates/advertisment.latte');
        $this->template->render();
    }

}