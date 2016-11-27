<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 21.06.2016
 * Time: 14:46
 */

namespace App\FrontModule\Presenters;

use App\Model\ForumManager;
use App\FrontModule\Controls\Forum;
use App\Model\UserManager;
use Nette\Application\UI\Form;

/**
 * Class ForumPresenter
 * @package App\FrontModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu fóra
 */
class ForumPresenter extends BasePresenter
{
    /**
     * @var ForumManager Instance triedy modelu pre prácu s fórom
     * Public kvôli komponente
     */

    public $forumManager;

    /** @var UserManager @inject */
    public $perm;

    /**
     * @persistent
     */
    public $backlink;

    /**
     * ForumPresenter constructor.
     * @param ForumManager $forumManager Automaticky injektovaná trieda modelu pre prácu s fórom
     */
    public function __construct(ForumManager $forumManager)
    {
        parent::__construct();
        $this->forumManager = $forumManager;
    }

    public function isInRole()
    {
        if ($this->user->isLoggedIn()) {
            if ($this->perm->isInRole($this->user->getId(), 'F') == TRUE) {
                return TRUE;
            } else {
                return FALSE;
            }

        }
    }

    /**
     *
     */
    public function renderDefault()
    {
        $this->template->categories = $this->forumManager->getCategories();
    }

    /*
     * Dostáva ID subkategorie
     */
    public function renderThreads($id)
    {
        $this->template->threads = $this->forumManager->getThreads($id);
    }

    public function renderDetail($id)
    {
        $this->template->post = $this->forumManager->getThread($id);
        $this->template->comment = $this->forumManager->getComments($id);
    }

    public function renderAdd($id)
    {
    }

    protected function createComponentAddForm()
    {
        $form = new Form();

        $form->addText('name');
        $form->addTextArea('content');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'addFormSucceeded'];

        return $form;
    }

    public function addFormSucceeded(Form $form, $values)
    {
        $values->sub_category_id = $this->getParameter('id');
        $values->user_id = $this->user->getId();

        $forum = $this->forumManager->createThread($values);
        $this->flashMessage('ssss');
        $this->redirect('Forum:detail', $forum->getPrimary());
    }


    /**
     * @return Forum
     * Vytvára a vracia komponentu Fóra
     */
    protected function createComponentForum()
    {
        $control = new Forum($this->forumManager);
        return $control;
    }

    protected function createComponentComment()
    {
        $form = new Form();
        $form->addTextArea('content');
        $form->addSubmit('submit', 'Odoslať');
        $form->onSuccess[] = [$this, 'CommentSucceeded'];

        return $form;
    }

    public function CommentSucceeded(Form $form, $values)
    {
        $values->user_id = $this->user->getId();
        $values->thread_id = $this->getParameter('id');
        $this->forumManager->insertComment($values);
        $this->flashMessage('Komentár bol pridaní.');
        $this->redirect('this');
    }

    /**
     * @param $id
     * @param $report_by
     */
    public function actionReport($id, $report_by)
    {
        $report_by = $this->user->getId();

        $this->forumManager->reportComment($id, $report_by);
        $this->flashMessage('Uživateľ bol nahlasení');
        $this->restoreRequest($this->backlink);
        $this->redirect();
    }

    public function actionDelete($id, $block_by, $report_by)
    {
        $block_by = $this->user->getId();

        $this->forumManager->deleteComment($id, $block_by);
        $this->forumManager->createCommentLog($id, '0', $block_by, $report_by);
        $this->flashMessage('Uživateľ bol nahlasení');
        $this->restoreRequest($this->backlink);
        $this->redirect();
    }

    public function actionThread($id)
    {
        $this->forumManager->deleteThread($id);
        $this->flashMessage('Téma zmazaná');
        $this->redirect('Forum:default');
    }

    public function actionUnblock($id, $block_by, $report_by)
    {
        $this->forumManager->unblock($id);
        $this->forumManager->createCommentLog($id, '1', $block_by, $report_by);
        $this->flashMessage('Komentár bol odblkovaní.');
        $this->restoreRequest($this->backlink);
    }

}