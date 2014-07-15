<?php

include 'Player.php';
include 'Pair.php';
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
    switch ($_REQUEST['mode']) {
        case "checkRound":
            $round = $_REQUEST['round'];
            $court = $_REQUEST['court'];
            $team1SQL = sprintf("SELECT * FROM `chosenPairs` WHERE `round`= '%s' AND `team` = '1' AND `court` = '%s' AND `user` = '%s' AND `session`= '%s'", $round, $court, $_SESSION['username'], $_SESSION['playID']);
            $team2SQL = sprintf("SELECT * FROM `chosenPairs` WHERE `round`= '%s' AND `team` = '2' AND `court` = '%s' AND `user` = '%s' AND `session`= '%s'", $round, $court, $_SESSION['username'], $_SESSION['playID']);
            $team1 = $mysqli->query($team1SQL);
            $team2 = $mysqli->query($team2SQL);
            $resultPairs = array();
            $teamArr = array();
            if ($team1->num_rows == 0) {
                $teamArr[] = "emptyRound";
            } else {
                while ($pair = $team1->fetch_assoc()) {
                    $resultPairs[] = $pair;
                }
                $teamArr[] = $resultPairs;
            }
            $resultPairs = array();
            if ($team2->num_rows == 0) {
                $teamArr[] = "emptyRound";
            } else {
                while ($pair = $team2->fetch_assoc()) {
                    $resultPairs[] = $pair;
                }
                $teamArr[] = $resultPairs;
            }
            $getNamesSQL = sprintf("SELECT * FROM players WHERE `user` = '%s'", $_SESSION['username']);
            $namesArr = $mysqli->query($getNamesSQL);
            while ($name = $namesArr->fetch_assoc()) {
                foreach ($teamArr as $i=>$team) {
                    foreach ($team as $j=> $tNum) {
                        if ($tNum != "emptyRound") {
                            if ($name["id"] == $tNum["player1"]) {
                                $teamArr[$i][$j]["player1Name"] = $name["first_name"] . " " . $name["last_name"];
                            } else if ($name["id"] == $tNum["player2"]) {
                                $teamArr[$i][$j]["player2Name"] = $name["first_name"] . " " . $name["last_name"];
                            }
                        }
                    }
                }
            }
            echo json_encode($teamArr);
            break;




        case "insertPair":
            $round = $_REQUEST['round'];
            $court = $_REQUEST['court'];
            $team = $_REQUEST['team'];
            $p1 = $_REQUEST['p1'];
            $p2 = $_REQUEST['p2'];

            $insertPairsSQL = sprintf("INSERT INTO `chosenPairs`(`session`, `round`, `court`, `team`, `player1`, `player2`, `user`) VALUES ('%s','%s','%s','%s','%s','%s','%s') ON DUPLICATE KEY UPDATE player1=VALUES(player1),player2=VALUES(player2)", $_SESSION['playID'], $round, $court, $team, $p1, $p2, $_SESSION['username']);
            $mysqli->query($insertPairsSQL);
            $team1SQL = sprintf("SELECT * FROM `chosenPairs` WHERE `round`= '%s' AND `team` = '1' AND `court` = '%s' AND `user` = '%s' AND `session`= '%s'", $round, $court, $_SESSION['username'], $_SESSION['playID']);
            $team2SQL = sprintf("SELECT * FROM `chosenPairs` WHERE `round`= '%s' AND `team` = '2' AND `court` = '%s' AND `user` = '%s' AND `session`= '%s'", $round, $court, $_SESSION['username'], $_SESSION['playID']);
            $team1 = $mysqli->query($team1SQL);
            $team2 = $mysqli->query($team2SQL);
            $resultPairs = array();
            $teamArr = array();
            if ($team1->num_rows == 0) {
                $teamArr[] = "emptyRound";
            } else {
                while ($pair = $team1->fetch_assoc()) {
                    $resultPairs[] = $pair;
                }
                $teamArr[] = $resultPairs;
            }
            $resultPairs = array();
            if ($team2->num_rows == 0) {
                $teamArr[] = "emptyRound";
            } else {
                while ($pair = $team2->fetch_assoc()) {
                    $resultPairs[] = $pair;
                }
                $teamArr[] = $resultPairs;
            }
               $getNamesSQL = sprintf("SELECT * FROM players WHERE `user` = '%s'", $_SESSION['username']);
            $namesArr = $mysqli->query($getNamesSQL);
            while ($name = $namesArr->fetch_assoc()) {
                foreach ($teamArr as $i=>$team) {
                    foreach ($team as $j=> $tNum) {
                        if ($tNum != "emptyRound") {
                            if ($name["id"] == $tNum["player1"]) {
                                $teamArr[$i][$j]["player1Name"] = $name["first_name"] . " " . $name["last_name"];
                            } else if ($name["id"] == $tNum["player2"]) {
                                $teamArr[$i][$j]["player2Name"] = $name["first_name"] . " " . $name["last_name"];
                            }
                        }
                    }
                }
            }
            echo json_encode($teamArr);
            break;
    }
} else {
    echo "not logged in";
}
?>