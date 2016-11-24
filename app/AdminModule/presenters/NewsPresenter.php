<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 05.05.2016
 * Time: 8:53
 */

namespace App\AdminModule\Presenters;

use App\Model\NewsManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Image;

/**
 * Class NewsPresenter
 * @package App\AdminModule\Presenters
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Presenter na správu noviniek
 */
class NewsPresenter extends BasePresenter
{

    /** @var newsManager Instance triedy modelu pre prácu s novinkami */
    private $newsManager;

    /**
     * NewsPresenter constructor.
     * @param NewsManager $newsManager Automaticky injektovaná trieda modelu pre prácu s novinkami
     */
    public function __construct(NewsManager $newsManager)
    {
        parent::__construct();
        $this->newsManager = $newsManager;
    }

    /**
     * Vytvára a vracia komponentu formulára pre pridanie novinky
     * @return Form $form
     */
    protected function createComponentCreateNewsForm()
    {
        $select = $this->newsManager->getCategory()->fetchPairs('id', 'name');


        $form = new Form();
        $form->addText('title');

        $form->addUpload('image')->setRequired();

        if ($this->action !== 'edit') {
            $form['image']
                ->addCondition(Form::FILLED)
                ->addRule(Form::IMAGE, 'Soubor musí být JPEG, PNG nebo GIF.');
        }

        $form->addText('source');

        $form->addTextArea('content');

        $form->addSelect('news_category_id')->setItems($select)->setPrompt('Vyberte kategóriu...');

        $form->addSubmit('submit', 'Publikovať Novinku');

        $form->onSuccess[] = [$this, 'CreateNewsFormSucceeded'];

        return $form;
    }

    /**
     * Callback po odoslaní formulára pre pridanie novinky
     * @param Form      $form   Formulár pre pridanie novinky
     * @param ArrayHash $values Hodnoty z formulára
     */
    public function createNewsFormSucceeded(Form $form, $values)
    {
        $values['news_users_id'] = $this->user->id;

        if ($values['image']->isImage() and $values['image']->isOk()) {
            $file = $values['image']; //Prehodenie do $file
            $file_name = $file->getSanitizedName();
            $file->move($this->context->parameters['wwwDir'] . '/img/slider/' . $file_name);
            $image = Image::fromFile($this->context->parameters['wwwDir'] . '/img/slider/' . $file_name);
            $image->resize(673, 282, Image::EXACT);
            $image->sharpen();
            $image->save($this->context->parameters['wwwDir'] . '/img/slider/' . $file_name);
            $values['image'] = $file_name;
            //TODO: random string na meno
        }

        if ($this->action !== 'edit') {
            $this->newsManager->createNews($values);
            $this->flashMessage('Novinka bola úspešne pridaná.', self::GREEN);
        } else {
            $this->newsManager->updateNews($this->getParameter('id'), $values);
            $this->flashMessage('Novinka bola úspešne editovaná.', self::BLUE);
        }
        $this->redirect('News:default');

    }

    /**
     * Render dát do Default.latte
     */
    public function renderDefault()
    {
        $this->template->news = $this->newsManager->getNews();
    }

    /**
     * @param int $id ID novinky
     *                Editácia novinky
     */
    public function renderEdit($id)
    {
        $query = $this->newsManager->getOne($id);
        $this['createNewsForm']->setDefaults($query);
    }

    /**
     * Render dát do Category.latte
     */
    public function renderCategory()
    {
        $this->template->category = $this->newsManager->getCategory();
    }

    /**
     * Vymazanie novinky
     * @param int $id ID novinky
     */
    public function actionNewsDelete($id)
    {
        $this->newsManager->deleteNews($id);
        $this->flashMessage('Novinka bola úspešne vymazaná', self::RED);
        $this->redirect('News:default');
    }

    /**
     * Vytvára a vracia komponentu formulára pre pridanie kategorie
     * @return Form $form
     */
    protected function createComponentCreateCategoryForm()
    {
        $form = new Form();
        $form->addText('name')->setRequired();
        $form->addSubmit('Submit', 'Přidat novou kategorii');
        $form->onSuccess[] = [$this, 'CreateCategoryFormSucceeded'];

        return $form;
    }


    /**
     * Callback po odoslaní formulára pre pridanie kategorie
     * @param Form      $form   Formulár pre pridanie kategorie
     * @param ArrayHash $values Hodnoty z formulára
     */
    public function createCategoryFormSucceeded(Form $form, $values)
    {
        $this->newsManager->createCategory($values);
        $this->flashMessage('Kategoria bola úspešne pridaná.', self::GREEN);
        $this->redirect('News:Category');
    }

    /**
     * Vymazanie kategorie
     * @param int $id ID kategórie
     */
    public function actionCategoryDelete($id)
    {
        $row = $this->newsManager->checkCategory($id);
        if ($row == FALSE) {
            $this->newsManager->deleteCategory($id);
            $this->flashMessage('Kategoria bola úspešne zmazaná.', self::RED);
        } else {
            $this->flashMessage('Kategoria nemôže byť zmazaná pretože ju obsahuje jedna z noviniek', self::BLUE);
        }
        $this->redirect('News:Category');

    }

    public function renderComments()
    {
        $this->template->reported = $this->newsManager->getReportedComments();
        $this->template->log = $this->newsManager->getCommentLog();
    }

    /**
     * @param $id
     */
    public function actionSave($id, $report_by, $block_by)
    {
        $this->newsManager->saveComment($id);
        $this->newsManager->createCommentLog($id, '1', $report_by, $block_by);
        $this->flashMessage('Komentár bol zachovaný');
        $this->redirect('News:comments');
    }

    /**
     * @param $id
     * @param $block_by
     */
    public function actionDel($id, $report_by, $block_by)
    {
        $this->newsManager->deleteComment($id, $block_by);
        $this->newsManager->createCommentLog($id, '0', $report_by, $block_by);
        $this->flashMessage('Komentár bol vymazaný');
        $this->redirect('News:comments');
    }
}