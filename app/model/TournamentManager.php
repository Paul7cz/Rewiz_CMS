<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 02.09.2016
 * Time: 15:51
 */

namespace App\Model;


use Nette\Database\SqlLiteral;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class TournamentManager extends BaseManager
{
    const
        TOURNAMENT_TABLE = 'league_tournament',
        REGISTERED_TABLE = 'league_tournament_registered_team',
        MATCHES_TABLE = 'league_tournament_matches',
        MATCHES_LOGS = 'league_tournament_matches_logs',
        MATCHES_REPORTS = 'league_tournament_matches_reports',
        MATCHES_DEMOS = 'league_tournament_matches_demos',
        MATCHES_SCREENSHOTS = 'league_tournament_matches_screenshots',
        CONFIRM_SCORE = 'league_tournament_confirm_matches';

    public function getTournament($id)
    {
        return $this->database->table(self::TOURNAMENT_TABLE)->where('id', $id)->fetch();
    }

    public function getAllTournament()
    {
        return $this->database->table(self::TOURNAMENT_TABLE)->order('start DESC');
    }

    public function getAllTournament2()
    {
        return $this->database->table(self::TOURNAMENT_TABLE)->order('start DESC');
    }

    public function getRegisteredTeamsActive($id)
    {
        return $this->database->table(self::REGISTERED_TABLE)->where('tournament_id = ? AND confirmed = ? ', $id, 1);
    }

    public function getRegisteredTeamsActive2($id)
    {
        return $this->database->table(self::REGISTERED_TABLE)->where('tournament_id = ? AND confirmed = ?', $id, 1)->count('*');
    }

    public function getRegisteredTeamsNoactive($id)
    {
        return $this->database->table(self::REGISTERED_TABLE)->where('tournament_id = ? AND confirmed IS NULL', $id);
    }

    public function checkRegisteredTeam($tournament_id, $team_id)
    {
        return $this->database->table(self::REGISTERED_TABLE)->where('tournament_id = ? AND team_id = ?', $tournament_id, $team_id)->fetch();
    }

    public function joinTournament($tournament_id, $id)
    {
        return $this->database->table(self::REGISTERED_TABLE)->insert(array(
            'tournament_id' => $tournament_id,
            'team_id' => $id,
        ));
    }

    public function getMaxRound($id)
    {
        $row = $this->database->table(self::MATCHES_TABLE)->where('tournament_id = ?', $id)->order('id DESC')->fetch();
        if (!$row) {
            return null;
        } else {
            return $row->round;
        }
    }

    public function getRoundMatches($id, $round)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('tournament_id = ? AND round = ?', $id, $round)->order('id ASC');
    }

    public function getNotClosedMatches($id, $round)
    {
        return $this->getRoundMatches($id, $round)->where('status = ?', 'new');
}

    public function insertMatch($id, $values, $round)
    {
        return $this->database->table(self::MATCHES_TABLE)->insert([
            'team1_id' => $values['team1'],
            'team2_id' => $values['team2'],
            'time' => new SqlLiteral('NOW() + INTERVAL 5 MINUTE'),
            'status' => 'new',
            'round' => $round,
            'tournament_id' => $id,
        ]);
    }

    public function getMatches($id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('tournament_id = ?', $id)->order('id ASC')->fetchAll();
    }

    public function getMatch($id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('id = ?', $id)->fetch();
    }

    public function getLeague($id)
    {
        $tournament = $this->getMatch($id)->tournament_id;

        return $this->database->table(self::TOURNAMENT_TABLE)->where('id = ?', $tournament)->fetch();
    }

    public function test($id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('tournament_id = ?', $id)->max('round');
    }

    public function getMatchesToPlayOff($round, $id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('tournament_id = ? AND round = ?', $id, $round)->fetchAll();
    }

    public function updateWinner($round, $winner_id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('round = ? AND (team1_id = ? OR team2_id = ?)', $round, $winner_id, $winner_id)->update(array(
            "winner" => $winner_id
        ));
    }

    public function getRound($id)
    {
        $row = $this->database->table(self::TOURNAMENT_TABLE)->where('id = ?', $id)->fetch();
        if (!$row) {
            return null;
        } else {
            return $row->max_teams;
        }
    }

    /**
     * @param $id
     * @return int
     */
    public function getTeamWin($id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('winner = ?', $id)->count('*');
    }

    /**
     * @param $id
     * @return int
     */
    public function getTeamRegistrationTournament($id)
    {

        return $this->database->table(self::REGISTERED_TABLE)->where('team_id = ? AND confirmed = 1', $id)->count('*');
    }

    public function getTeamMatches($id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('team1_id = ? OR team2_id = ? AND winner IS NULL', $id, $id)->order('id DESC');

    }

    public function closeTournament($id)
    {
        return $this->database->table(self::TOURNAMENT_TABLE)->where('id = ?', $id)->update(array(
            'status' => 'closed',
        ));
    }

    public function createTournament($values)
    {
        return $this->database->table(self::TOURNAMENT_TABLE)->insert($values);
    }

    public function updateTournament($id, $values)
    {
        return $this->database->table(self::TOURNAMENT_TABLE)->where('id = ?', $id)->update($values);
    }

    /**
     * @param $content
     * @param $user_id
     * @param $match_id
     * @return bool|int|\Nette\Database\Table\IRow
     */
    public function createMatchLog($content, $user_id, $match_id)
    {
        return $this->database->table(self::MATCHES_LOGS)->insert(array(
            'content' => $content,
            'user_id' => $user_id,
            'match_id' => $match_id
        ));
    }

    public function getMatchLogs($id)
    {
        return $this->database->table(self::MATCHES_LOGS)->where('match_id = ?', $id);
    }

    public function confirmTeam($id)
    {
        return $this->database->table(self::REGISTERED_TABLE)->where('id = ?', $id)->update(array(
            'confirmed' => '1'
        ));
    }

    public function confirmScore($values)
    {
        return $this->database->table(self::CONFIRM_SCORE)->insert($values);
    }

    public function confirmScoreFetch($id)
    {
        return $this->database->table(self::CONFIRM_SCORE)->where('match_id = ?', $id)->fetch();
    }

    public function confirmScoreMatch($id, $values)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('id = ?', $values->match_id)->update(array(
            'score1' => $values->score1,
            'score2' => $values->score2,
            'status' => 'closed'
        ));
    }

    public function insertReport($values)
    {
        return $this->database->table(self::MATCHES_REPORTS)->insert($values);
    }

    public function insertScreenshots($values)
    {
        return $this->database->table(self::MATCHES_SCREENSHOTS)->insert($values);
    }

    public function insertDemo($values)
    {
        return $this->database->table(self::MATCHES_DEMOS)->insert($values);
    }

    public function getScreenshots($id)
    {
        return $this->database->table(self::MATCHES_SCREENSHOTS)->where('match_id', $id);
    }

    public function getDemos($id)
    {
        return $this->database->table(self::MATCHES_DEMOS)->where('match_id', $id);
    }

    public function getReports($id)
    {
        return $this->database->table(self::MATCHES_REPORTS)->where('match_id', $id);
    }

    public function newConfirmScore($match_id)
    {
        // Confirmed
        $this->database->table(self::CONFIRM_SCORE)->where('match_id', $match_id)->order('id DESC')->update(["confirmed" => 1]);

        $data = $this->database->table(self::CONFIRM_SCORE)->where('match_id', $match_id)->order('id DESC')->fetch();
        $this->database->table(self::MATCHES_TABLE)->where('id', $match_id)->update(["status" => "closed", "score1" => $data->score1, "score2" => $data->score2]);

        $match = $this->getMatch($data->match_id);

        if ($data->score1 > $data->score2){
           $this->database->table('team_points')->where(array(
               'team_id' => $match->team1_id,
               'league_id' => $match->tournament->leagues_id,
           ))->update(array(
               'point' => +3
           ));
       } else{
            $this->database->table('team_points')->where(array(
                'team_id' => $match->team2_id,
                'league_id' => $match->tournament->leagues_id,
            ))->update(array(
                'point' => +3
            ));
        }

        return true;
    }

    public function updateScore($match_id, $values)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('id', $match_id)->update($values);
    }

    public function getRequest()
    {
        return $this->database->table('league_tournament_matches_reports')->where('status = ?', 'new')->fetchAll();
    }

    public function updateRequest($id, $values)
    {
        return $this->database->table('league_tournament_matches_reports')->where('id = ?', $id)->update(array(
            'status' => 'closed',
            'answer' => $values->answer
        ));
    }

    public function lastMatch($team_id)
    {
        return $this->database->table(self::MATCHES_TABLE)->where('team1_id = ? OR team2_id = ?', $team_id, $team_id)->fetch();
    }
    public function insertPointLog($values){
        return $this->database->table('team_point_log')->insert($values);
    }




}