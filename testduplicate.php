<?php

include 'Player.php';
include('DB.php');
include('loginfunctions.php');
sec_session_start();
//$sessPlaySQL = sprintf("INSERT INTO session_players (id_from_players,rested,boardnumber,rested_last,same_sex,history,session,period)"
//        . "VALUES (10,2,3,4,5,7,'sazmeraaaaaar2013-11-16 14:46:00',6)  ON DUPLICATE KEY UPDATE rested=12");
//$mysqli->query($sessPlaySQL);
$player = $_SESSION['playerArray'][0];
$hej = serialize($player->history);
var_Dump($hej);
$sessPlaySQL = sprintf("INSERT INTO `players-sessions` (id_from_players,rested,boardnumber,rested_last,same_sex,history,session)"
        . "VALUES ('%s','%s','%s','%s','%s','%s','%s')  ON DUPLICATE KEY UPDATE rested=1100", $player->id, $player->rest, $player->boardNumber, $player->rested_last, $player->same_sex, "h", $_SESSION['playID']);
$mysqli->query($sessPlaySQL);
//var_dump($sessPlaySQL);
echo "w?";
?>