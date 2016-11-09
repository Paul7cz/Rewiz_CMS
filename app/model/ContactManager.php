<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 14.07.2016
 * Time: 23:05
 */

namespace App\Model;

use Nette\Utils\ArrayHash;

class ContactManager extends BaseManager
{
    const
        TABLE_CONTACT = 'contact';

    /**
     * Pridanie kontaktu
     * @param ArrayHash $values Dáta z formulára
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function addContact($values)
    {
        return $this->database->table(self::TABLE_CONTACT)->insert($values);
    }

    /**
     * Vytiahnutie kontaktu podľa ID sekcie
     * @param int $section ID sekcie
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     */
    public function getContacts($section)
    {
        return $this->database->table(self::TABLE_CONTACT)->where('section', $section)->fetchAll();
    }

    /**
     * Vymazanie kontaktu podľa ID
     * @param int $id ID kontaktu
     * @return int
     */
    public function deleteContact($id)
    {
        return $this->database->table(self::TABLE_CONTACT)->where('id', $id)->delete();
    }

}