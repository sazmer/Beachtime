<?php
include('DB.php');
$date = date("Ymd");

$query1 = "CREATE TABLE players_backup".$date." LIKE players";


$query2 = "INSERT INTO players_backup".$date." SELECT * FROM players";
$mysqli->query($query1);
$mysqli->query($query2);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
