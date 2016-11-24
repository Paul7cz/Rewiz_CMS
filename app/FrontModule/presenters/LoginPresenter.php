<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 29.05.2016
 * Time: 0:36
 */

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Panels;
use App\FrontModule\Controls\Tournament;
use App\Model\TournamentManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * Class LoginPresenter
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na prihlásenie do systému
 */
class LoginPresenter extends Presenter
{

    /** @var userManager Instance triedy modelu pre prácu s uživateľmi */
    private $userManager;

    /** @var TournamentManager @inject */
    public $tournamentManager;


    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }


    /**
     * @return Form
     * Vytvára a vracia komponentu formulára pre prihlasenie uživateľa
     */
    protected function createComponentCreateLoginForm()
    {
        $form = new Form;

        $form->addText('username')
            ->setAttribute('placeholder', 'Použivaťelské meno')
            ->setRequired();

        $form->addText('password')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí obsahovať minimálne 6 znakov.', 6)
            ->setAttribute('placeholder', 'Heslo')
            ->setType('password')
            ->setRequired();

        $form->addSubmit('submit', 'Prihlásiť sa');

        $form->onSuccess[] = [$this, 'CreateLoginFormSucceeded'];
        return $form;
    }

    /**
     * @param Form      $form   Formulár na prihlásenie
     * @param ArrayHash $values Hodnoty z formulára
     *                          Callback po odoslaní formulára pre prihlasenie uživateľa
     */
    public function CreateLoginFormSucceeded(Form $form, $values)
    {
        try {
            $this->user->login($values->username, $values->password);
            $this->flashMessage('Úspešne ste sa prihlásili.', 'login-flash');
            $this->redirect('Homepage:default');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Homepage:default');
        $this->flashMessage('Bol si odhlásení');
    }

    protected function createComponentLostPw()
    {
        $form = new Form();

        $form->addText('email')->setType('email')->setRequired()->setAttribute('placeholder', 'Vložte e-mailovú adresu');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'lostPwSucceeded'];

        return $form;
    }

    public function createComponentPanels()
    {
        $control = new Panels($this->tournamentManager);
        return $control;
    }

    public function lostPwSucceeded(Form $form, $values)
    {

        if ($this->userManager->getMail($values->email) == TRUE) {
            $password = Strings::random();
            $this->userManager->changePassword($values->email, $password);

            $mail = new Message;
            $mail->setFrom('Support Rewiz.eu <support@rewiz.eu>')
                ->addTo($values->email)
                ->setSubject('Zabudnuté heslo')
                ->setBody("Dobrý den,\nvaše objednávka byla přijata s heslom $password");

            $mailer = new SendmailMailer;
            $mailer->send($mail);
            $this->flashMessage('Mail s heslom odoslaný');
        } else {
            $this->flashMessage('Váš email v našej databáze neevidujeme.');
        }
        $this->redirect('this');
    }


}