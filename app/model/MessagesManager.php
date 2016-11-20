<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 09.08.2016
 * Time: 22:57
 */

namespace App\Model;

use Nette\Utils\ArrayHash;

/**
 * Class MessagesManager
 * @package App\Model
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Model na správu správ
 */
class MessagesManager extends BaseManager
{
    const
        TABLE_MESSAGES = 'messages';

    /**
     * @param int $id ID prijmateľa správy
     * @return ArrayHash|bool
     *                Vráti všetky správy kde si príjemca
     */
    public function getMessagesReceive($id)
    {
        return $this->database->table(self::TABLE_MESSAGES)->where('receiver_id', $id)->order('date DESC');
    }

    /**
     * @param int $user_id    ID uživateľa
     * @param int $message_id ID správy
     * @return bool
     *                        Overenie či si príjemca správy
     */
    public function checkReceiver($user_id, $message_id)
    {
        $query = $this->database->table(self::TABLE_MESSAGES)->where('id', $message_id)->fetch();

        if ($query->receiver_id != $user_id) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @param int $user_id    ID uživateľa
     * @param int $message_id ID správy
     * @return bool
     *                        Overenie či si odosielateľ správy
     */
    public function checkSender($user_id, $message_id)
    {
        $query = $this->database->table(self::TABLE_MESSAGES)->where('id', $message_id)->fetch();

        if ($query->sender_id != $user_id) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @param int $id ID správy
     * @return bool|mixed|\Nette\Database\Table\IRow
     *                Vytiahnutie správy podľa jej ID
     */
    public function getOneMessage($id)
    {
        return $this->database->table(self::TABLE_MESSAGES)->where('id', $id)->fetch();
    }

    /**
     * @param int $id ID správy
     * @return int
     *                Vymazanie správy podľa ID
     */
    public function deleteMessage($id)
    {
        return $this->database->table(self::TABLE_MESSAGES)->where('id', $id)->delete();
    }

    /**
     * @param int $id
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     */
    public function getMessagesSend($id)
    {
        return $this->database->table(self::TABLE_MESSAGES)->where('sender_id', $id)->order('date DESC')->fetchAll();
    }

    /**
     * @param $values
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function sendMessage($values)
    {
        return $this->database->table(self::TABLE_MESSAGES)->insert($values);
    }

    /**
     * @param int $id
     * @return int
     */
    public function seeMessage($id)
    {
        return $this->database->table(self::TABLE_MESSAGES)->where('id = ?', $id)->update(array(
            'seen' => 1
        ));
    }

    /**
     * @param $id
     * @return int
     */
    public function unseenMessageCount($id)
    {
        return $this->database->table(self::TABLE_MESSAGES)->where('receiver_id = ? AND seen IS NULL', $id)->count('*');
    }

}