<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 31.05.2016
 * Time: 15:48
 */

namespace App\AdminModule\Presenters;

use App\AdminModule\Controls\Forum;
use App\Model\ForumManager;
use Nette\Application\UI\Form;


/**
 * Class HltvPresenter
 * @Secured
 * @package App\AdminModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu HLTV
 */
class ForumPresenter extends BasePresenter
{

    /** @var  ForumManager */
    private $forumManager;

    /**
     * ForumPresenter constructor.
     * @param ForumManager $forumManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(ForumManager $forumManager)
    {
        $this->forumManager = $forumManager;
    }

    public function renderDefault()
    {
        $this->template->categories = $this->forumManager->getCategories();
    }

    protected function createComponentCategory()
    {
        $form = new Form();
        $form->addText('name');
        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'categorySucceeded'];

        return $form;
    }

    public function categorySucceeded(Form $form, $values)
    {
        $this->forumManager->createCategory($values);
        $this->flashMessage('Kategoria bola vytvorená');
        $this->redirect('this');
    }

    protected function createComponentSubCategory()
    {

        $categories = $this->forumManager->getCategories2()->fetchPairs('id', 'name');

        $form = new Form();
        $form->addText('name');
        $form->addTextArea('about');
        $form->addSelect('categorie_id')->setItems($categories);
        $form->addSubmit('submit');

        $form->onSuccess[] = [$this, 'subCategorySucceeded'];

        return $form;
    }

    public function subCategorySucceeded(Form $form, $values)
    {
        $this->forumManager->createSubCategory($values);
        $this->flashMessage('Sub kategoria bola vytvorená');
        $this->redirect('this');
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

    public function actionDeleteSub($id)
    {
        $this->forumManager->deleteSubCategory($id);
        $this->flashMessage('Sub kategoria bola vymazaná.');
        $this->redirect('Forum:default');
    }

    public function renderComments(){
        $this->template->comments = $this->forumManager->getReportedComments();
    }

    public function actionSave($id)
    {
        $this->forumManager->saveComment($id);
        $this->forumManager->createCommentLog($id, '1');
        $this->flashMessage('Komentár bol zachovaný');
        $this->redirect('Forum:reports');
    }

    public function actionDel($id, $block_by)
    {
        $this->forumManager->deleteComment($id, $block_by);
        $this->forumManager->createCommentLog($id, '0');
        $this->flashMessage('Komentár bol vymazaný');
        $this->redirect('Forum:reports');
    }

    public function renderReports(){
        $this->template->reported = $this->forumManager->getReportedComments();
        $this->template->log = $this->forumManager->getCommentLog();
    }



}