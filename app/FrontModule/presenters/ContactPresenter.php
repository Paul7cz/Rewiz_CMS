<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 29.05.2016
 * Time: 0:36
 */

namespace App\FrontModule\Presenters;

use App\Model\ContactManager;

/**
 * Class LoginPresenter
 * @package App\FrontModule\Presenters
 * @author Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na prihlásenie do systému
 */
class ContactPresenter extends BasePresenter
{
    /** @var ContactManager */
    private $contactManager;

    /**
     * ContactPresenter constructor.
     * @param ContactManager $contactManager Automaticky injektovaná instace triedy modelu pre prácu s kontaktmi
     */
    public function __construct(ContactManager $contactManager)
    {
        $this->contactManager = $contactManager;
    }

    /**
     *'1' => 'Management webovej stránky',
     *'2' => 'Counter-Strike: Global Offensive',
     *'3' => 'League of Legends',
     *'4' => 'Hearthstone',
     *'5' => 'Dota 2');
     */
    public function renderDefault()
    {
        $this->template->managment = $this->contactManager->getContacts(1);
        $this->template->csgo = $this->contactManager->getContacts(2);
        $this->template->lol = $this->contactManager->getContacts(3);
        $this->template->hs = $this->contactManager->getContacts(4);
        $this->template->dota = $this->contactManager->getContacts(5);
    }

}