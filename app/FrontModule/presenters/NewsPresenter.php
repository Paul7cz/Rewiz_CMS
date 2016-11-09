<?php

namespace App\FrontModule\Presenters;

use App\Model\NewsManager;
use App\Model\UserManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Security\Identity;
use Tracy\Debugger;


/**
 * Class NewsPresenter
 * @Secured
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter/Model na správu XXX
 */
class NewsPresenter extends BasePresenter
{

    /** @var NewsManager */
    private $newsManager;

    /** @var UserManager */
    private $userManager;

    /** @persistent */
    public $backlink;

    /**
     * NewsPresenter constructor.
     * @param NewsManager $newsManager
     * @param UserManager $userManager
     */
    public function __construct(NewsManager $newsManager, UserManager $userManager)
    {
        parent::__construct();
        $this->newsManager = $newsManager;
        $this->userManager = $userManager;
    }


    public function renderDefault($id)
    {
        $check = $this->newsManager->checkId($id);

        if ($check != TRUE) {
            throw new BadRequestException();
        }

        $news_id = $this->getParameter('id');

        $this->template->news = $this->newsManager->getOne($id);
        $this->template->comments = $this->newsManager->getComments($id);
        $this->template->comments_count = $this->newsManager->countComments($news_id);
    }

    public function createComponentCommentForm()
    {
        $form = new Form();
        $form->addTextArea('content')->setAttribute('placeholder', 'Pridať verejný komentár');
        $form->addSubmit('submit', 'Komentovať');
        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }

    public function commentFormSucceeded(Form $form, $values)
    {
        $user = $this->getUser();
        $values->users_id = $user->id;
        $values->news_id = $this->getParameter('id');

        $this->newsManager->addComment($values);
        $this->redirect('this');
    }

    /**
     * Vymazanie komentára k novinke
     * @param int $id ID novinky
     */
    public function actionDelete($id, $block_by)
    {
        $this->newsManager->deleteComment($id, $block_by);
        $this->flashMessage('Komentár bol úspešne vymazaný', self::RED);
        $this->restoreRequest($this->backlink);
    }

    public function actionReport($id, $user_id)
    {
        $user_id = $this->user->getId();
        $news = $this->newsManager->getComment($id);

        $this->newsManager->reportComent($id, $user_id);
        $this->userManager->insertNotification($news->users_id, 'Jeden z tvojích komentárov bol ohlasení ako nevhodný');
        $this->flashMessage('Komentár bol nahlasení', self::BLUE);
        $this->restoreRequest($this->backlink);
    }

    public function actionUnblock($id){
        $this->newsManager->unblock($id);
        $this->flashMessage('Komentár bol odblkovaní.');
        $this->restoreRequest($this->backlink);
    }


}