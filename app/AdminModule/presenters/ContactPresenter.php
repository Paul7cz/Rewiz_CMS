<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 06.07.2016
 * Time: 17:33
 */

namespace App\AdminModule\Presenters;

use App\Model\ContactManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Class ContactPresenter
 * @Secured
 * @package App\AdminModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Nezabudni napísať k čomu tento modul slúži !
 */
class ContactPresenter extends BasePresenter
{

    /** @var ContactManager Instance triedy modelu pre prácu s kontaktmi */
    private $contactManager;

    /**
     * ContactPresenter constructor.
     * @param ContactManager $contactManager Automaticky injektovaná instace triedy modelu pre prácu s kontaktmi.
     */
    public function __construct(ContactManager $contactManager)
    {
        parent::__construct();
        $this->contactManager = $contactManager;
    }

    /**
     * Vytvára a vracia komponentu formulára pre pridanie kontaktu
     * @return Form
     */
    protected function createComponentContactForm()
    {
        $form = new Form();
        $form->addText('name')
            ->setAttribute('placeholder', 'Meno "Prezývka" Prezvisko')
            ->setRequired();

        $section = array(
            '1' => 'Management webovej stránky',
            '2' => 'Counter-Strike: Global Offensive',
            '3' => 'League of Legends',
            '4' => 'Hearthstone',
            '5' => 'Dota 2');

        $form->addSelect('section')
            ->setItems($section)
            ->setPrompt('Vyberte sekciu...')
            ->setRequired();

        $form->addText('possition')
            ->setAttribute('placeholder', 'Pozícia')
            ->setRequired();

        $form->addText('email')
            ->setAttribute('placeholder', 'E-mail')
            ->setType('email')
            ->setRequired();

        $form->addSubmit('submit', 'Uložiť');
        $form->onSuccess[] = [$this, 'contactFormSucceeded'];
        return $form;
    }

    /**
     * Callback po odoslaní formulára pre pridanie kontaktu
     * @param Form      $form   Formulár pre pridanie kontaktu
     * @param ArrayHash $values Hodnoty z formulára
     */
    public function contactFormSucceeded(Form $form, $values)
    {
        $this->contactManager->addContact($values);
        $this->flashMessage('Kontakt bol pridaný', self::GREEN);
        $this->redirect('this');
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

    /**
     * Vymazanie kontaktu podľa ID
     * @param int $id ID kontaktu
     */
    public function actionDelete($id)
    {
        $this->contactManager->deleteContact($id);
        $this->flashMessage('Kontakt bol úspešne vymazaný', self::RED);
        $this->redirect('Contact:Default');
    }


}