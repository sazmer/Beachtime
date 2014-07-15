<?php

include 'Player.php';
include 'Pair.php';
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
    if ($_REQUEST['mode'] == "ranking") {
        $sessionWithoutName = str_replace($_SESSION['username'], "", $_SESSION['playID']);
        $chosenPeriod = $_REQUEST['period'];
        $query = sprintf("SELECT * FROM players");
        $result = $mysqli->query($query);
        $gameStatsSQL = sprintf("SELECT * FROM game_statistics WHERE period = '%s' AND user = '%s'", $chosenPeriod, $_SESSION['username']);
        $statResults = $mysqli->query($gameStatsSQL);
        $playedGamesIDS = array();
        $wonGamesIDS = array();
        while ($statsGot = $statResults->fetch_row()) {
            $playedGamesIDS[] = $statsGot[4];
            $playedGamesIDS[] = $statsGot[5];
            $playedGamesIDS[] = $statsGot[6];
            $playedGamesIDS[] = $statsGot[7];
            $wonGamesIDS[] = $statsGot[8];
            $wonGamesIDS[] = $statsGot[9];
        }
        $playedGamesCounted = array_count_values($playedGamesIDS);
        $wonGamesCounted = array_count_values($wonGamesIDS);
        while ($allPlayers = $result->fetch_row()) {
            foreach ($playedGamesCounted as $playID => $PGC) {
                if ($playID == $allPlayers[0]) {
                    $newPlayer = new Player($allPlayers[0], $allPlayers[1], $allPlayers[2], $allPlayers[3]);
                    if ($wonGamesCounted[$playID] != null) {
                        $newPlayer->wins = $wonGamesCounted[$playID];
                    } else {
                        $newPlayer->wins = 0;
                    }
                    $newPlayer->played_games = $PGC;
                    $playerArrayStats[] = $newPlayer;
                }
            }
        }
//  usort($playerArrayStats, "orderByWins");
        $winRatio = 0;
        foreach ($playerArrayStats as $player) {
            if ($player->played_games > 0) {
                $winRatio = round(($player->wins / $player->played_games) * 100);
                $player->winratio = $winRatio;
            }
        }

        sortByWinsAndPercent($playerArrayStats, array("wins", "winratio"));
        $retArray = array();
        foreach ($playerArrayStats as $player) {
            $onePlayer = array();
            $onePlayer[] = $player->firstname;
            $onePlayer[] = $player->lastname;
            $onePlayer[] = $player->wins;
            $onePlayer[] = $player->winratio;
            $retArray[] = $onePlayer;
        }
        $mysqli->close();
        echo json_encode($retArray);
    } else if ($_REQUEST['mode'] == "periods") {
        $periods = array();
        $queryPeriods = sprintf("SELECT DISTINCT(period) FROM `game_statistics` WHERE user='%s' ORDER BY period", $_SESSION['username']);
        $querySessions = sprintf("SELECT DISTINCT(session), period FROM `game_statistics` WHERE user='%s' ORDER BY period", $_SESSION['username']);
        $resultPeriods = $mysqli->query($queryPeriods);
        $resultSessions = $mysqli->query($querySessions);
        while ($p = $resultPeriods->fetch_row()) {
            $periods[] = $p[0];
        }
        $sessionPeriodArray = array();
        while ($sP = $resultSessions->fetch_row()) {
            if (count($p) > 1) {
                foreach ($periods as $p) {
                    if ($sP[1] == $p) {
                        $sessionPeriodArray[$period][] = $sP[0];
                    }
                }
            } else {
                if ($sP[1] == $periods[0]) {
                    $sessionPeriodArray[$periods[0]][] = $sP[0];
                }
            }
        }
        $mysqli->close();
        echo json_encode($sessionPeriodArray);
    } else if ($_REQUEST['mode'] == "rankSess") {
        $session = $_REQUEST['session'];
        $period = $_REQUEST['period'];
        $query = sprintf("SELECT * FROM players");
        $result = $mysqli->query($query);
        $gameStatsSQL = sprintf("SELECT * FROM game_statistics WHERE period = '%s' AND user = '%s' AND session = '%s'", $period, $_SESSION['username'], $session);
        $statResults = $mysqli->query($gameStatsSQL);

        $playedGamesIDS = array();
        $wonGamesIDS = array();
        while ($statsGot = $statResults->fetch_row()) {
            $playedGamesIDS[] = $statsGot[4];
            $playedGamesIDS[] = $statsGot[5];
            $playedGamesIDS[] = $statsGot[6];
            $playedGamesIDS[] = $statsGot[7];
            $wonGamesIDS[] = $statsGot[8];
            $wonGamesIDS[] = $statsGot[9]; 
        }
        $playedGamesCounted = array_count_values($playedGamesIDS);
        $wonGamesCounted = array_count_values($wonGamesIDS);
        while ($allPlayers = $result->fetch_row()) {
            foreach ($playedGamesCounted as $playID => $PGC) {
                if ($playID == $allPlayers[0]) {
                    $newPlayer = new Player($allPlayers[0], $allPlayers[1], $allPlayers[2], $allPlayers[3]);
                    if ($wonGamesCounted[$playID] != null) {
                        $newPlayer->wins = $wonGamesCounted[$playID];
                    } else {
                        $newPlayer->wins = 0;
                    }
                    $newPlayer->played_games = $PGC;
                    $playerArrayStats[] = $newPlayer;
                }
            }
        }
//  usort($playerArrayStats, "orderByWins");
        $winRatio = 0;
        foreach ($playerArrayStats as $player) {
            if ($player->played_games > 0) {
                $winRatio = round(($player->wins / $player->played_games) * 100);
                $player->winratio = $winRatio;
            }
        }

        sortByWinsAndPercent($playerArrayStats, array("wins", "winratio"));
        $retArray = array();
        foreach ($playerArrayStats as $player) {
            $onePlayer = array();
            $onePlayer[] = $player->firstname;
            $onePlayer[] = $player->lastname;
            $onePlayer[] = $player->wins;
            $onePlayer[] = $player->winratio;
            $retArray[] = $onePlayer;
        }
        $mysqli->close();
        echo json_encode($retArray);
    }
}

function sortByWinsAndPercent(&$playerArrayStats, $props) {
    usort($playerArrayStats, function($a, $b) use ($props) {
        if ($a->$props[0] == $b->$props[0])
            return $a->$props[1] < $b->$props[1] ? 1 : -1;
        return $a->$props[0] < $b->$props[0] ? 1 : -1;
    });
}

function orderByWins($p1, $p2) {
    if ($p1->wins < $p2->wins) {
        return 1;
    } else if ($p1->wins > $p2->wins) {
        return -1;
    } else {
        return 0;
    }
}

?>