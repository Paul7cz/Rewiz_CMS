<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 31.05.2016
 * Time: 17:14
 */

namespace App\FrontModule\Presenters;

use App\Model\DuplicateNameException;
use App\Model\UserManager;
use Latte\Engine;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Utils\ArrayHash;


/**
 * Class RegistrationPresenter
 * @package App\FrontModule\Presenters
 * @author Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter pre registráciu nového uživateľa
 */
class RegistrationPresenter extends BasePresenter
{

    /** @var UserManager Instance triedy modelu pre prácu s uživateľmi */
    private $userManager;

    /**
     * RegistrationPresenter constructor.
     * @param UserManager $userManager Automaticky injektovaná trieda modelu pre prácu s uživateľmi
     */
    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    /**
     * @return Form
     * Vytvára a vracia komponentu formulára pre registráciu
     */
    protected function createComponentRegistrationForm()
    {
        $form = new Form;

        $form->addText('username')
            ->setAttribute('placeholder', 'Použivateľské meno')
            ->setRequired();

        $form->addPassword('password')
            ->setAttribute('placeholder', 'Heslo')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí obsahovať minimálne 6 znakov.', 6)
            ->setRequired();

        $form->addPassword('passwordVerify')
            ->setAttribute('placeholder', 'Heslo znovu')
            ->addRule(Form::EQUAL, 'Hesla sa nezhodujú.', $form['password'])
            ->setOmitted();

        $form->addText('email')
            ->setAttribute('placeholder', 'E-mail')
            ->setType('email')
            ->setRequired();

        $form->addCheckbox('vop')
            ->setRequired('Je potřeba souhlasit s podmínkami');

        $form->addSubmit('submit', 'Registrovať sa');

        $form->onSuccess[] = [$this, 'registrationFormSuccess'];

        return $form;
    }

    /**
     * @param Form $form Formulár pre registráciu
     * @param ArrayHash $values Hodnoty z formulára
     * Callback po odoslaní formulára pre registráciu
     */
    public function registrationFormSuccess(Form $form, $values)
    {
        try {
            $this->userManager->register($values);
        } catch (DuplicateNameException $e) {
            $form->addError($e->getMessage());
        }

        $latte = new Engine();
        $data = [
            'name' => $values->username,
        ];

        $mail = new Message();
        $mail->setFrom('NO-REPLY rewiz.eu <noreply@rewiz.eu>')
            ->addTo($values->email, $values->username)
            ->setHtmlBody($latte->renderToString(__DIR__ . '/../templates/Emails/registration.latte', $data));

        $mailer = new SendmailMailer();
        $mailer->send($mail);

        $this->flashMessage('Pre úspešne dokončenie registrácie potvrďte potvrdzovací email.', self::GREEN);
        $this->redirect('this');

    }


}