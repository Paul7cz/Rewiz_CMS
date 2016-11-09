<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 26.06.2016
 * Time: 0:37
 */

namespace App\Model;

/**
 * Class ServerStatusManager
 * @package App\Model
 * @author Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Model pre prácu so server statusom
 */
class ServerStatusManager extends BaseManager
{
    const SERVER_TABLE = 'servers';

    public function getGames(){
        return $this->database->table(self::SERVER_TABLE);
    }

}