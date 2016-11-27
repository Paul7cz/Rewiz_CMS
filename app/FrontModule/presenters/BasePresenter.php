<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Panels;
use App\Model\MessagesManager;
use App\Model\PermissionsManager;
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

    /** @var UserManager @inject */
    public $perm;

    /**
     * BasePresenter constructor.
     * @param UserManager $userManager
     * @param TournamentManager $tournamentManager
     * @param MessagesManager $messageManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
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
            $this->checkVipDate($this->user->getId());
            $this->checkVip($this->user->getId());
        }

        if ($this->user->isLoggedIn()) {
            $this->template->messages_count = $this->messageManager->unseenMessageCount($this->user->getId());
            $this->template->not = $this->userManager->getUnseenNotoficatin($this->user->getId());
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


    public function banPostExpired()
    {
        $ban = $this->user->getIdentity()->ban_post;

        if ($ban != NULL) {
            $today = new DateTime();
            $ban_time = new DateTime($ban);

            if ($today >= $ban_time) {
                $this->userManager->deleteBanPost($this->user->getId());
            }

            //TODO: LOG
        }
    }

    public function banLoginExpired()
    {
        $ban = $this->user->getIdentity()->ban_login;

        if ($ban != NULL) {
            $this->user->logout();
            $today = new DateTime();
            $ban_time = new DateTime($ban);

            if ($today >= $ban_time) {
                $this->userManager->deleteBanLogin($this->user->getId());
            }

        }
    }

    public function checkVipDate($id)
    {
        $user = $this->userManager->getUser($id);
        $today = new DateTime();
        if ($user->premium_time <= $today) {
            $this->userManager->vip_deactive($id);
        }
    }

    public function checkVip($id)
    {
        $user = $this->userManager->getUser($id);

        if ($user->premium == 0 AND $user->premium_time == NULL) {
            return TRUE;
        } elseif ($user->premium == 0 AND $user->premium_time != NULL) {
            $this->userManager->vipact($id, 1);
        } elseif ($user->premium == 1 AND $user->premium_time == NULL) {
            $this->userManager->vipact($id, 0);
        }
    }


    /**
     * Turnajový panel
     * @return Panels
     */
    protected
    function createComponentPanels()
    {
        $control = new Panels($this->tournamentManager);
        return $control;
    }

}

