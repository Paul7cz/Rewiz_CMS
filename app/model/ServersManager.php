<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 26.06.2016
 * Time: 0:37
 */

namespace App\Model;

use Nette\Utils\ArrayHash;

/**
 * Class ServersManager
 * @package App\Model
 * @author Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Model pre prácu so server statusom
 */
class ServersManager extends BaseManager
{
    const SERVER_TABLE = 'servers',
          GAME_TABLE = 'games',
          ID_COLUMN = 'id';

    /**
     * @return array Pole z dátami v tabuľke
     * Vráti výpis hier
     */
    public function getGames(){
        return $this->database->table(self::GAME_TABLE)->fetchPairs('id', 'name');
    }

    /**
     * @param ArrayHash $values Hodnoty z formulára
     * @return bool
     * Pridá server do databáze
     */
    public function addServer($values){
        return $this->database->table(self::SERVER_TABLE)->insert($values);
    }

    /**
     * @param int $id
     * @return int
     */
    public function deleteServer($id){
        return $this->database->table(self::SERVER_TABLE)->where(self::ID_COLUMN, $id)->delete();
    }

    /**
     * @return ArrayHash
     * Vráti výpis serverov
     */
    public function getServers(){
        return $this->database->table(self::SERVER_TABLE)->fetchAll();
    }

}