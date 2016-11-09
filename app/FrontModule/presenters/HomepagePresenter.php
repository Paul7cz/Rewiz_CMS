<?php

namespace App\FrontModule\Presenters;


use App\Model\ForumManager;
use App\Model\LeagueManager;
use App\Model\NewsManager;
use App\Model\ServersManager;
use Tracy\Debugger;

/**
 * Class HomepagePresenter
 * @secured
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter/Model na správu XXX
 */
class HomepagePresenter extends BasePresenter
{

    /** @var ServersManager */
    private $serversManager;

    /** @var NewsManager */
    private $newsManager;

    /** @var  LeagueManager */
    private $leagueManager;

    /** @var  ForumManager */
    private $forumManager;

    /**
     * HomepagePresenter constructor.
     * @param ServersManager $serversManager
     * @param NewsManager    $newsManager
     * @param LeagueManager  $leagueManager
     * @param ForumManager   $forumManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(ServersManager $serversManager, NewsManager $newsManager, LeagueManager $leagueManager, ForumManager $forumManager)
    {
        $this->serversManager = $serversManager;
        $this->newsManager = $newsManager;
        $this->leagueManager = $leagueManager;
        $this->forumManager = $forumManager;
    }


    /**
     * Render Default.latte
     */
    public function renderDefault(){
        $this->template->servers = $this->serversManager->getServers();
        $this->template->news = $this->newsManager->getNewsLimit(4);
        $this->template->csgo = $this->leagueManager->getLeagueCsgoPanel();
        $this->template->lol = $this->leagueManager->getLeagueLolPanel();
        $this->template->dota = $this->leagueManager->getLeagueDotaPanel();
        $this->template->hs = $this->leagueManager->getLeagueHearthstonePanel();
        $this->template->forum = $this->forumManager->getAllThreads();
    }

}
