<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 05.09.2016
 * Time: 14:44
 */

namespace App\FrontModule\Controls;


use App\Model\TournamentManager;
use Nette\Application\UI\Control;

class Tournament extends Control
{

    /** @var TournamentManager */
    private $tournamentManager;

    /**
     * Tournament constructor.
     * @param TournamentManager $tournamentManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(TournamentManager $tournamentManager)
    {
        parent::__construct();
        $this->tournamentManager = $tournamentManager;
    }

    public function renderPlayoff($round, $tournament_id)
    {
        $this->template->data = $this->tournamentManager->getMatchesToPlayOff($round, $tournament_id);
        $this->template->setFile(__DIR__ . '/templates/playoff.latte');
        $this->template->render();
    }


}