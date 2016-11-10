<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 21.06.2016
 * Time: 14:55
 */

namespace App\Model;


class ForumManager extends BaseManager
{
    const CATEGORIES_TABLE = 'forum_categories',
        TOPIC_TABLE = 'forum_topic',
        SUB_CATEGORIES_TABLE = 'forum_sub_category',
        THREADS = 'forum_threads',
        COMMENTS = 'forum_thread_comments',
        TABLE_COMMENTS_LOG = 'forum_comments_log',
        POSTS = 'forum_post';

    public function getCategories()
    {
        return $this->database->table(self::CATEGORIES_TABLE)->fetchAll();
    }

    public function getCategories2()
    {
        return $this->database->table(self::CATEGORIES_TABLE);
    }

    public function createCategory($values)
    {
        return $this->database->table(self::CATEGORIES_TABLE)->insert($values);
    }

    public function deleteCategory($id)
    {

        return $this->database->table(self::CATEGORIES_TABLE)->where('id', $id)->delete();
    }

    public function getSubCategories($id)
    {
        return $this->database->table(self::SUB_CATEGORIES_TABLE)->where('categorie_id', $id)->fetchAll();
    }

    public function createSubCategory($values)
    {
        return $this->database->table(self::SUB_CATEGORIES_TABLE)->insert($values);
    }

    public function deleteSubCategory($id)
    {
        return $this->database->table(self::SUB_CATEGORIES_TABLE)->where('id', $id)->delete();
    }

    public function getThreads($id)
    {
        return $this->database->table(self::THREADS)->where('sub_category_id', $id)->order('id DESC')->fetchAll();
    }

    public function getThread($id)
    {
        return $this->database->table(self::THREADS)->where('id', $id)->fetch();
    }

    public function createThread($values)
    {
        return $this->database->table(self::THREADS)->insert($values);
    }

    public function getAllThreads()
    {
        return $this->database->table(self::THREADS)->limit(3)->order('id DESC')->fetchAll();
    }

    public function getComments($id)
    {
        return $this->database->table(self::COMMENTS)->where('thread_id', $id)->fetchAll();
    }

    public function insertComment($values)
    {
        return $this->database->table(self::COMMENTS)->insert($values);
    }

    public function reportComment($id, $report_by)
    {
        return $this->database->table(self::COMMENTS)->where('id', $id)->update(array(
            'reports' => 1,
            'report_by' => $report_by
        ));
    }

    public function getReportedComments()
    {
        return $this->database->table(self::COMMENTS)->where('reports IS NOT NULL')->fetchAll();
    }

    public function saveComment($id)
    {
        return $this->database->table(self::COMMENTS)->where('id', $id)->update(array(
            'reports' => NULL,
            'block_by' => NULL,
            'block' => NULL,
            'report_by' => NULL,
        ));
    }

    /**
     * @param $id
     * @return int
     */
    public function deleteComment($id, $block_by)
    {
        return $this->database->table(self::COMMENTS)->where('id', $id)->update(array(
            'block' => '1',
            'block_by' => $block_by,
            'reports' => NULL,
        ));
    }

    public function countThreads($id){
        return $this->database->table(self::THREADS)->where('sub_category_id', $id)->count('*');
    }

    public function countComments($id){

        $threads = $this->database->table(self::THREADS)->where('sub_category_id', $id)->fetchAll();

        return $this->database->table(self::COMMENTS)->where('thread_id', $threads)->count('*');
    }

    public function countCommentsCat($id){
        $category = $this->database->table(self::SUB_CATEGORIES_TABLE)->where('categorie_id', $id)->fetchAll();
        $threads = $this->database->table(self::THREADS)->where('sub_category_id', $category)->fetchAll();

        return $this->database->table(self::COMMENTS)->where('thread_id', $threads)->count('*');
    }

    public function countCommentsThread($id){
        return $this->database->table(self::COMMENTS)->where('thread_id', $id)->count('*');
    }

    public function deleteThread($id){
        return $this->database->table(self::THREADS)->where('id', $id)->delete();
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
        return $this->database->table(self::COMMENTS)->where('id', $id)->update(array(
            'block' => NULL,
            'reports' => NULL,
            'block_by' => NULL,
            'report_by' => NULL
        ));
    }





}