<?php
include('Player.php');
include('DB.php');

session_start();
$fail = false;
$date = date("Ymd_Hi");
    $tablename = "players_export".$date;
$query1 = "CREATE TABLE " . $tablename . " LIKE players_export";
$mysqli->query($query1);

foreach ($_SESSION['playerArray'] as $player) {
    $fname = $player->firstname;
    $lname = $player->lastname;
    $todays_wins = $player->todays_wins;


    $query2 = ("INSERT INTO  " . $tablename . " (first_name, last_name, todays_wins) "
            . "VALUES ('$fname','$lname','$todays_wins')");
  $mysqli->query($query2);
   
}

  $query3 = ("INSERT INTO  players_export_list (event) VALUES ('$tablename')");
     echo $mysqli->query($query3);
     if($fail){
         return "false";
     }
     return "true";
?>