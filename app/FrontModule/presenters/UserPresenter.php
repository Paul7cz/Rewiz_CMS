<?php

namespace App\FrontModule\Presenters;

use App\Model\UserManager;
use IPub\VisualPaginator\Components\Control;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Utils\Image;


/**
 * Class UserPresenter
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu uživateľov
 */
class UserPresenter extends BasePresenter
{

    /** @var UserManager */
    private $userManager;


    /**
     * UserPresenter constructor.
     * @param UserManager $userManager Automaticky injektovaná instace triedy modelu pre prácu s uživateľmi
     */
    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderProfile($id)
    {

        $check = $this->userManager->checkId($id);
        if (!$check) {
            throw new BadRequestException();
        }

        $this->template->userdata = $this->userManager->getUser($id);

        $data = $this->userManager->getUser($id);
        $birthyear = date('Y') - $data->birthyear;
        $this->template->date = $birthyear;
        $this->template->awards = $this->userManager->getProfileAwards($id);

    }

    public function renderEdit()
    {

        if (!$this->user->isLoggedIn()) {
            $this->loginRedirect();
        }

        $query = $this->userManager->getUser($this->user->getId());
        $this['edit']->setDefaults($query);
    }

    protected function createComponentEdit()
    {
        $form = new Form();
        $form->addText('full_name');
        $form->addText('username')->setDisabled();
        $form->addSelect('gender')->setItems(array(
            'Muž' => 'Muž',
            'Žena' => 'Žena',
        ))->setPrompt('Vyberte...');

        $form->addText('birthyear');

        $form->addSelect('state')->setItems(array(
            'Slovenská Republika' => 'Slovenská Republika',
            'Česka Republika' => 'Česka Republika',
        ));

        $form->addText('email')->setDisabled();

        $form->addUpload('avatar');

        $form->addText('csgo_sid');
        $form->addText('dota2_nick');
        $form->addText('lol_nick');
        $form->addText('hs_nick');

        $form->addText('city');
        $form->addText('street');
        $form->addText('psc');
        $form->addText('street_number');
        $form->addText('tel');

        $form->addSubmit('submit', 'Aktualizovať profil');

        $form->onSuccess[] = [$this, 'editSucceeded'];

        return $form;
    }

    public function editSucceeded(Form $form, $values)
    {

        if ($values->birthyear == ''){
            $values->birthyear = NULL;
        }

        if ($values['avatar']->isImage() and $values['avatar']->isOk()) {
            $file = $values['avatar']; //Prehodenie do $file
            $file_name = $file->getSanitizedName();
            $file->move($this->context->parameters['wwwDir'] . '/img/avatar/' . $file_name);
            $image = Image::fromFile($this->context->parameters['wwwDir'] . '/img/avatar/' . $file_name);
            $image->resize(250, 250, Image::EXACT);
            $image->sharpen();
            $image->save($this->context->parameters['wwwDir'] . '/img/avatar/' . $file_name);
            $values['avatar'] = $file_name;
            //TODO: random string na meno
        } else {
            unset($values['avatar']);
        }

        $this->userManager->updateUser($this->user->getId(), $values);
        /* dump($this->userManager->reauthenticate($this->user->getId()));*/
        $this->flashMessage('Profil bol úspečne aktualizovaný', self::GREEN);
        $this->redirect('User:profile', $this->user->getId());
    }

    protected function createComponentPassword()
    {
        $form = new Form();

        $form->addPassword('password')
            ->setRequired();

        $form->addPassword('check_password')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password'])
            ->setRequired();

        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'passwordSucceeded'];

        return $form;
    }

    public function passwordSucceeded(Form $form, $values)
    {

        $this->userManager->updateUser($this->user->getId(), array(
            "password" => Passwords::hash($values->password)
        ));
        $this->flashMessage('Heslo zmenené');
        $this->redirect('User:Profile', $this->user->getId());

    }

    public function renderNotification(){

        $comments = $this->userManager->getNotification($this->user->getId());

        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $comments->count();

        $comments->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->notification = $comments;

        $this->userManager->seeNotification($this->user->getId());
    }

    public function actionAchDel($id){
        $this->userManager->deleteUserAchviement($id);
        $this->flashMessage('Ocenenie zmazané');
        $this->redirect('Homepage:default');
    }

    protected function createComponentVisualPaginator()
    {
        $control = new Control();
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }


}
