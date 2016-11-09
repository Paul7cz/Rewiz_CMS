<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 16.07.2016
 * Time: 0:14
 */

namespace App\Model;


class PermissionsManager extends BaseManager
{
    const
        TABLE_ROLE = "role";

    /**
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     */
    public function getRoles(){
        return $this->database->table(self::TABLE_ROLE)->fetchAll();
    }

}