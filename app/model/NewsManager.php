<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 25.04.2016
 * Time: 23:57
 */

namespace App\Model;

use Nette\Utils\ArrayHash;

/**
 * Class NewsManager
 * @package App\Model
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Model pre prácu s novinkami
 */
class NewsManager extends BaseManager
{
    const
        NEWS_TABLE = 'news',
        CATEGORY_TABLE = 'news_category',
        TABLE_COMMENTS = 'news_comments',
        TABLE_COMMENTS_LOG = 'news_comments_log',
        COLUMN_ID = 'news_id';

    /**
     * @return \Nette\Database\Table\Selection
     * Vráti výpis všetkých noviniek
     */
    public function getNews()
    {
        return $this->database->table(self::NEWS_TABLE)->order('date DESC')->fetchAll();
    }

    /**
     * @param $id
     * @return bool|mixed|\Nette\Database\Table\IRow
     */
    public function getOne($id)
    {
        return $this->database->table(self::NEWS_TABLE)->where('id', $id)->fetch();
    }

    /**
     * @param int $limit Počet výsledkov
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     *                   Vráti výpis noviniek s limitom
     */
    public function getNewsLimit($limit)
    {
        return $this->database->table(self::NEWS_TABLE)->order('date DESC')->limit($limit)->fetchAll();
    }

    /**
     * @param ArrayHash $values Dáta z formulára
     * @return bool|int|\Nette\Database\Table\IRow
     *                          Pridá novinku
     */
    public function createNews($values)
    {
        return $this->database->table(self::NEWS_TABLE)->insert($values);
    }

    public function updateNews($id, $values)
    {
        return $this->database->table(self::NEWS_TABLE)->where('id', $id)->update($values);
    }

    /**
     * @return \Nette\Database\Table\Selection
     * Vráti výpis všetkých kategorii
     */
    public function getCategory()
    {
        return $this->database->table(self::CATEGORY_TABLE);
    }

    /**
     * @param int $id ID novinky
     * @return int
     *                Vymazanie novinky podľa ID
     */
    public function deleteNews($id)
    {
        return $this->database->table(self::NEWS_TABLE)->where('id', $id)->delete();
    }

    /**
     * @param ArrayHash $values Dáta z formulára
     * @return bool|int|\Nette\Database\Table\IRow
     *                          Pridanie kategorie
     */
    public function createCategory($values)
    {
        return $this->database->table(self::CATEGORY_TABLE)->insert($values);
    }

    /**
     * @param int $id ID kategórie
     * @return int
     *                Vymazanie kategorie podľa ID
     */
    public function deleteCategory($id)
    {
        return $this->database->table(self::CATEGORY_TABLE)->where('id', $id)->delete();
    }

    /**
     * @param int $id
     * @return bool|mixed|\Nette\Database\Table\IRow
     * Kontrola existencie kategorie v self::NEWS_TABLE
     */
    public function checkCategory($id)
    {
        return $this->database->table(self::NEWS_TABLE)->where('news_category_id', $id)->fetch();
    }

    /**
     * @param $id
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     */
    public function getComments($id)
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('news_id', $id)->fetchAll();
    }

    public function getComment($id){
        return $this->database->table(self::TABLE_COMMENTS)->where('id', $id)->fetch();
    }

    /**
     * @param $values
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function addComment($values)
    {
        return $this->database->table(self::TABLE_COMMENTS)->insert($values);
    }

    /**
     * @param $id
     * @return int
     */
    public function countComments($id)
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('news_id', $id)->count();
    }

    /**
     * @param $id
     * @return int
     */
    public function deleteComment($id, $block_by)
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('id', $id)->update(array(
                'block' => '1',
                'reports' => NULL,
                'block_by' => $block_by)
        );
    }

    /**
     * @param $id
     * @return bool
     */
    public function checkId($id)
    {
        return $this->database->table(self::NEWS_TABLE)->where('id', $id)->fetch();
    }

    public function reportComent($id, $user_id)
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('id', $id)->update(array(
            'reports' => '1',
            'report_by' => $user_id
        ));
    }

    public function getReportedComments()
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('reports IS NOT NULL')->fetchAll();
    }

    public function saveComment($id)
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('id', $id)->update(array(
            'reports' => NULL,
            'report_by' => NULL,
        ));
    }

    public function deleteRepComment($id)
    {
        return $this->database->table(self::TABLE_COMMENTS)->where('id', $id)->delete();
    }

    public function createCommentLog($comment_id, $status)
    {
        return $this->database->table(self::TABLE_COMMENTS_LOG)->insert(array(
            'comment_id' => $comment_id,
            'status' => $status,
        ));
    }

    public function getCommentLog()
    {
        return $this->database->table(self::TABLE_COMMENTS_LOG)->fetchAll();
    }

    public function unblock($id){
        return $this->database->table(self::TABLE_COMMENTS)->where('id', $id)->update(array(
                'block' => NULL,
                'reports' => NULL,
                'block_by' => NULL,
                'report_by' => NULL
        ));
    }

}