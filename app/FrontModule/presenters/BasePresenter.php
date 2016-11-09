<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Panels;
use App\Model\MessagesManager;
use App\Model\TournamentManager;
use App\Model\UserManager;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\Utils\DateTime;

/**
 * Class BasePresenter
 * @package App\Presenters
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
    /**
     * Konštanty pre css triedy na FlashMessage
     */
    const
        GREEN = 'success',
        BLUE = 'info',
        RED = 'danger';

    /** @var UserManager */
    private $userManager;

    /** @var TournamentManager */
    private $tournamentManager;

    /** @var  MessagesManager */
    private $messageManager;

    /**
     * BasePresenter constructor.
     * @param UserManager       $userManager
     * @param TournamentManager $tournamentManager
     * @param MessagesManager   $messageManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function inject(UserManager $userManager, TournamentManager $tournamentManager, MessagesManager $messageManager)
    {
        $this->userManager = $userManager;
        $this->tournamentManager = $tournamentManager;
        $this->messageManager = $messageManager;
    }


    /**
     * Aktualizuje uživateľké dáta
     */
    public function beforeRender()
    {
        if ($this->user->isLoggedIn()) {
            $this->reauthenticate($this->user->getId());
            $this->banPostExpired();
            $this->banLoginExpired();
        }

        if ($this->user->isLoggedIn()) {
            $this->template->messages_count = $this->messageManager->unseenMessageCount($this->user->getId());
        }

    }

    /**
     * Presmerovanie uživateľa na prihlasovaciu stránku
     * @return void
     */
    public function loginRedirect()
    {
        $this->flashMessage('Pre prístup do tejto sekcie sa musíš prihlásiť.', self::BLUE);
        $this->redirect('Login:default');
    }

    /**
     * Aktualizuje uživateľké dáta
     * @param $user_id
     */
    public function reauthenticate($user_id)
    {
        $identity = $this->userManager->getUser($user_id);

        foreach ($identity as $key => $item) {
            $this->user->getIdentity()->$key = $item;
        }

    }


    public function banPostExpired(){
       $ban = $this->user->getIdentity()->ban_post;

        if ($ban != NULL){
            $today = new DateTime();
            $ban_time = new DateTime($ban);

            if ($today >= $ban_time){
                $this->userManager->deleteBanPost($this->user->getId());
            }

            //TODO: LOG
        }
    }

    public function banLoginExpired(){
        $ban = $this->user->getIdentity()->ban_login;

        if ($ban != NULL){
            $this->user->logout();
            $today = new DateTime();
            $ban_time = new DateTime($ban);

            if ($today >= $ban_time){
                $this->userManager->deleteBanLogin($this->user->getId());
            }

            //TODO: LOG
        }
    }

    /**
     * Turnajový panel
     * @return Panels
     */
    protected function createComponentPanels()
    {
        $control = new Panels($this->tournamentManager);
        return $control;
    }

}

