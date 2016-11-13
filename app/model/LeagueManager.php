<?php
/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 05.06.2016
 * Time: 12:54
 */

namespace App\Model;

use Nette\Utils\ArrayHash;

/**
 * Class LeagueManager
 * @package App\Model
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Model pre správu ligy
 */
class LeagueManager extends BaseManager
{
    const
        GAMES_TABLE = "games",
        LEAGUES_TABLE = "leagues",
        TEAM_TABLE = "league_team",
        ADMINS_TABLE = "league_admins",
        TEAM_LOGS_TABLE = "league_team_logs",
        TEAM_REGISTERED = "league_registered_team",
        TEAM_ACHVIEMENT = "team_achviement",
        TEAM_LEAGUE_POINTS = "team_points";


    /**
     * @return array|\Nette\Database\Table\IRow[]
     * Vráti všetky hry
     */
    public function getGames()
    {
        return $this->database->table(self::GAMES_TABLE);
    }

    /**
     * @param ArrayHash $data
     * @return bool|int|\Nette\Database\Table\IRow
     * Pridá hru
     */
    public function addGame($data)
    {
        return $this->database->table(self::GAMES_TABLE)->insert($data);
    }

    /**
     * @param int $id
     * @return int
     * Vymaže hru
     */
    public function deleteGame($id)
    {
        return $this->database->table(self::GAMES_TABLE)->where('id', $id)->delete();
    }

    /**
     * @param ArrayHash $data
     * @return bool|int|\Nette\Database\Table\IRow
     * Pridá Ligu
     */
    public function addLeague($data)
    {
        return $this->database->table(self::LEAGUES_TABLE)->insert($data);
    }

    /**
     * @return array|\Nette\Database\Table\IRow[]
     * Výpis líg
     */
    public function getLeagues()
    {
        return $this->database->table(self::LEAGUES_TABLE)->fetchAll();
    }

    /**
     * @return array|\Nette\Database\Table\IRow[]
     * Výpis líg
     */
    public function getLeagues2()
    {
        return $this->database->table(self::LEAGUES_TABLE);
    }

    /**
     * @param $id
     * @return bool|mixed|\Nette\Database\Table\IRow
     */
    public function getTeam($id)
    {
        return $this->database->table(self::TEAM_TABLE)->where('id', $id)->fetch();
    }

    public function getAllTeam()
    {
        return $this->database->table(self::TEAM_TABLE);
    }

    /**
     * @param int $team ID teamu
     * @param int $username ID uživateľa
     * @param string $action popis akcie
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function createTeamLog($team, $username, $action)
    {
        return $this->database->table(self::TEAM_LOGS_TABLE)->insert(array(
            'team_id' => $team,
            'users_id' => $username,
            'action' => $action));
    }

    /**
     * @param int $id ID Teamu
     * @return ArrayHash
     */
    public function getTeamLogs($id)
    {
        return $this->database->table(self::TEAM_LOGS_TABLE)->where('team_id', $id)->order('created DESC')->fetchAll();
    }

    public function addTeam($values)
    {
        return $this->database->table(self::TEAM_TABLE)->insert($values);
    }

    public function editTeam($id, $values)
    {
        return $this->database->table(self::TEAM_TABLE)->where('id = ?', $id)->update($values);

    }

    public function getPassword($id)
    {
        return $this->database->table(self::TEAM_TABLE)->where('id', $id)->fetch()->password;
    }

    public function getLeague($id)
    {
        return $this->database->table(self::LEAGUES_TABLE)->where('id', $id)->fetch();
    }

    public function getAdmins($id)
    {
        return $this->database->table(self::ADMINS_TABLE)->where('leagues_id', $id)->fetchAll();
    }

    public function getRegisteredTeamsNoActive($id)
    {
        return $this->database->table(self::TEAM_REGISTERED)->where('id_league = ? AND confirmed IS NULL ', $id);
    }

    public function getRegisteredTeamsActive($id)
    {
        return $this->database->table(self::TEAM_REGISTERED)->where('id_league = ? AND confirmed = ? ', $id, 1);
    }

    public function getRegisteredTeamsActiveCount($id)
    {
        return $this->database->table(self::TEAM_REGISTERED)->where('id_league = ? AND confirmed = ? ', $id, 1)->count('*');
    }

    public function joinLeague($id_team, $id_league)
    {
        return $this->database->table(self::TEAM_REGISTERED)->insert(array(
            'id_team' => $id_team,
            'id_league' => $id_league,
        ));
    }

    /**
     * @param $id_team
     * @param $id_league
     * @return bool
     */
    public function checkRegisteredTeam($id_team, $id_league)
    {
        return $this->database->table(self::TEAM_REGISTERED)->where('id_league = ? AND id_team = ?', $id_league, $id_team)->fetch();
    }

    public function updateLeague($id, $values)
    {
        return $this->database->table(self::LEAGUES_TABLE)->where('id', $id)->update($values);
    }

    public function addAdmin($values)
    {
        return $this->database->table(self::ADMINS_TABLE)->insert($values);
    }

    public function checkAdmin($values)
    {
        return $this->database->table(self::ADMINS_TABLE)->where('users_id = ? AND leagues_id = ?', $values->users_id, $values->leagues_id)->fetch();
    }

    public function deleteAdmin($id)
    {
        return $this->database->table(self::ADMINS_TABLE)->where('id', $id)->delete();
    }

    public function getLeagueCsgoPanel()
    {
        return $this->database->table(self::LEAGUES_TABLE)->where('end_date >= NOW() AND game = ?', 'Counter-Strike: Global Offensive')->fetchAll();
    }

    public function getLeagueDotaPanel()
    {
        return $this->database->table(self::LEAGUES_TABLE)->where('end_date >= NOW() AND game = ?', 'Dota 2')->fetchAll();
    }

    public function getLeagueHearthstonePanel()
    {
        return $this->database->table(self::LEAGUES_TABLE)->where('end_date >= NOW() AND game = ?', 'Hearthstone')->fetchAll();
    }

    public function getLeagueLolPanel()
    {
        return $this->database->table(self::LEAGUES_TABLE)->where('end_date >= NOW() AND game = ?', 'League of Legends')->fetchAll();
    }

    public function confirmTeam($id)
    {
        return $this->database->table(self::TEAM_REGISTERED)->where('id = ?', $id)->update(array(
            'confirmed' => '1'
        ));
    }

    public function getPoints($team_id)
    {
        return $this->database->table(self::TEAM_LEAGUE_POINTS)->where('team_id = ?', $team_id)->fetchAll();
    }

    public function getTeamPoints($team_id, $league_id)
    {
        return $this->database->table(self::TEAM_LEAGUE_POINTS)->where('team_id = ? AND league_id = ?', $team_id, $league_id)->fetch()->point;
    }

    public function insertPoints($values)
    {
        return $this->database->table(self::TEAM_LEAGUE_POINTS)->insert($values);
    }

    public function insertToPoints($values)
    {
        return $this->database->table(self::TEAM_LEAGUE_POINTS);
    }

    public function teamAchviementInsert($values)
    {
        return $this->database->table(self::TEAM_ACHVIEMENT)->insert($values);
    }

    public function teamAchDel($team_id, $ach_id){
        return $this->database->table(self::TEAM_ACHVIEMENT)->where('team_id = ? AND achviement_id = ?', $team_id, $ach_id)->delete();
    }

    public function getTeamAchviement($id)
    {
        return $this->database->table(self::TEAM_ACHVIEMENT)->where('team_id = ?', $id)->order('id DESC')->fetchAll();
    }

    public function getAllTeamAchviement()
    {
        return $this->database->table(self::TEAM_ACHVIEMENT)->fetchAll();
    }

}