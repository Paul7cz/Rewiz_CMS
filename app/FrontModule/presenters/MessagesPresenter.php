<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 29.05.2016
 * Time: 0:36
 */

namespace App\FrontModule\Presenters;

use App\Model\MessagesManager;
use App\Model\UserManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use IPub\VisualPaginator\Components as VisualPaginator;


/**
 * Class MessagesPresenter
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na prihlásenie do systému
 */
class MessagesPresenter extends BasePresenter
{

    /**
     * @var MessagesManager
     */
    private $messagesManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * MessagesPresenter constructor.
     * @param MessagesManager $messagesManager
     * @param UserManager     $userManager
     * Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(MessagesManager $messagesManager, UserManager $userManager)
    {
        parent::__construct();
        $this->messagesManager = $messagesManager;
        $this->userManager = $userManager;
    }

    public function renderDefault()
    {
        /**
         * Ak je uživateľ prihlasení
         */
        if (!$this->user->isLoggedIn()) {
            $this->loginRedirect();
        }

        $messages = $this->messagesManager->getMessagesReceive($this->user->getId());
        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $messages->count();

        $messages->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->messages  = $messages;


        $this->template->message_send = $this->messagesManager->getMessagesSend($this->user->getId());
    }

    /**
     * @param int $id ID správy
     * @throws BadRequestException
     */
    public function renderReceive($id)
    {
        /**
         * Ak správa nexistuje
         */
        if ($this->messagesManager->getOneMessage($id) == FALSE) {
            throw new BadRequestException();
        }

        /**
         * Ak je uživateľ prihlasení
         */
        if (!$this->user->isLoggedIn()) {
            $this->loginRedirect();
        }

        /**
         * Ak je príjemca
         */
        if ($this->messagesManager->checkReceiver($this->user->getId(), $id) == FALSE) {
            throw new BadRequestException('K tejto správe nemáte prístup.');
        }

        $this->template->message = $this->messagesManager->getOneMessage($id);
        $this->messagesManager->seeMessage($id);
    }

    public function renderSend($id)
    {
        /**
         * Ak správa nexistuje
         */
        if ($this->messagesManager->getOneMessage($id) == FALSE) {
            throw new BadRequestException();
        }

        /**
         * Ak je uživateľ prihlasení
         */
        if (!$this->user->isLoggedIn()) {
            $this->loginRedirect();
        }

        /**
         * Ak je príjemca
         */
        if ($this->messagesManager->checkSender($this->user->getId(), $id) == FALSE) {
            throw new BadRequestException('K tejto správe nemáte prístup.');
        }

        $this->template->message = $this->messagesManager->getOneMessage($id);
    }

    /**
     * @param int $id ID správy
     *                Vymazanie správy podľa ID
     */
    public function actionDelete($id)
    {
        $this->messagesManager->deleteMessage($id);
        $this->flashMessage('Správa bola úspešne vymazaná', self::RED);
        $this->redirect('Messages:default');
    }

    /**
     * @return Form
     */
    protected function createComponentMessageSend()
    {
        $form = new Form();
        $form->addText('subject');
        $form->addText('receiver_id');
        $form->addTextArea('message');
        $form->addSubmit('submit')->setAttribute("placeholder","Login");

        $form->onValidate[] = [$this, 'messageSendValidate'];
        $form->onSuccess[] = [$this, 'messageSendSucceeded'];

        return $form;
    }

    public function messageSendValidate(Form $form, $values)
    {

        /**
         * Overenie existencie uživateľa
         */
        if ($this->userManager->checkReceiver($values['receiver_id']) == FALSE) {
            $form->addError('Uživateľ s týmto menom neexistuje');
        }

        /**
         * OVerenie či neposiela správu sám sebe
         */
        if ($this->user->getIdentity()->username == $values['receiver_id']){
            $form->addError('Nemôžeš poslať správu sám sebe');
        }
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function messageSendSucceeded(Form $form, $values)
    {
        $values['receiver_id'] = $this->userManager->getReceiverId($values['receiver_id']);

        $values['sender_id'] = $this->user->getId();
        $this->messagesManager->sendMessage($values);

        $this->flashMessage('Správa bola úspešne odoslaná.', self::GREEN);
        $this->redirect('Messages:default');

    }

    protected function createComponentVisualPaginator()
    {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }


}