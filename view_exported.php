<?php

include('Player.php');
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {


    $date = date("Ymd_Hi");
    $tablename = "players_export" . $date;
    $query = "SELECT * FROM players_export_list";
    $exportListRows = $mysqli->query($query);

    $listOfLists = array();
    while ($row = $exportListRows->fetch_row()) {
        $query1 = "SELECT * FROM " . $row[1] . "";
        $result = $mysqli->query($query1);
        $resultList = array();
        while ($playerObject = $result->fetch_row()) {
            $resultList[] = $playerObject;
        }
        $listOfLists[] = $resultList;
    }
    foreach ($listOfLists as $RL) {
        if (count($RL) > 1) {
            echo '<table style="float:left; margin:20px;" border=1>
                <caption>' . $tablename . '</caption>        
                <thead>
                <th>Spelare</th>
                <th>Vinster</th>
                </thead>
                <tbody>';
            foreach ($RL as $player) {

                echo "<tr>" .
                "<td>$player[1]" .
                " $player[2] </td>" .
                "<td>$player[3]</td>" .
                "</tr>";
            }
            echo '</tbody></table>';
        }
    }
} else {
    echo 'You are not authorized to access this page, please login. <br/>';
    header('Location: login.php?error=1');
    exit();
    die();
}
?> 


