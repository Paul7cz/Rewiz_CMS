<?php

namespace App\AdminModule\Presenters;

use App\Model;
use Nette;
use Nette\Application\UI\Presenter;

/**
 * Class BasePresenter
 * @package App\AdminModule\Presenters
 * @author Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu ostatných presenterov.
 */
abstract class BasePresenter extends Presenter
{

    /** @var Model\UserManager @inject */
    public $perm;

    /**
     * Konštanty pre css triedy na FlashMessage
     */
    const
        GREEN = 'success',
        BLUE = 'info',
        RED = 'danger';


    public function beforeRender()
    {
        if ($this->user->isLoggedIn()) {
            if (!$this->perm->isInRole($this->user->id, 'PA')) {
                $this->flashMessage('K tejto sekcii nemáš prístup');
                $this->redirect(':Front:Homepage:default');
            }
        }


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

}

