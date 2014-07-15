<?php

include 'Player.php';
include 'Pair.php';
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
       $sessionWithoutName =  str_replace($_SESSION['username'], "", $_SESSION['playID']);
    if ($_REQUEST['whichR'] == "all") {
        $rest = sprintf("SELECT session from `players-rests` WHERE user = '%s' group by session", $_SESSION['username']);
        $gamestat = sprintf("SELECT * from game_statistics WHERE user = '%s'", $_SESSION['username']);
        var_dump($rest);
        var_dump($gamestat);
        $resters = $mysqli->query($rest);
        $stats = $mysqli->query($gamestat);
        $dates = array();
        for ($i = 0; $row = mysqli_fetch_array($resters, MYSQLI_ASSOC); $i++) {
            $string = substr($row["session"], 0, strpos($row["session"], ' '));
            $string = str_replace($_SESSION['username'], '', $string);
            $dates[] = $string;
        }
        echo json_encode($dates);
    } else { 
        $rest = sprintf("SELECT * from `players-rests` WHERE session = '%s' AND user = '%s'", $sessionWithoutName,$_SESSION['username']);
        $gamestat = sprintf("SELECT * from game_statistics  WHERE session = '%s' AND user = '%s'", $sessionWithoutName,$_SESSION['username']);
        $resters = $mysqli->query($rest);

//HÄMTA FRÅN RESTTABELLEN - SPARADE VILNINGAR
        $resters2 = array();
        for ($i = 0; $row = mysqli_fetch_array($resters, MYSQLI_ASSOC); $i++) {
            $resters2[$i][0] = $row["round"];
            $resters2[$i][1] = $row["rester"];
            $resters2[$i][2] = $row["chosen"];
        }
        $roundComp = 1;
        $roundArr = array();
        $roundGathered = array();
        foreach ($resters2 as $r2) {
            if ($r2[0] > $roundComp) {
                if ($roundArr != null)
                    $roundGathered[$roundComp] = $roundArr;
                $roundComp = $r2[0];
                $roundArr = null;
                $roundArr[] = $r2[1];
                $roundArr[] = $r2[2];
            } else {
                $roundArr[] = $r2[1];
                $roundArr[] = $r2[2];
            }
        }
        if ($roundArr != null)
            $roundGathered[$roundComp] = $roundArr;

 
        $stats = $mysqli->query($gamestat);
        $stats2 = array();
        $roundCompS = 1;
        $roundArrS = array();
        $roundGatheredS = array();
        $rowz = array();

//    HÄMTA FRÅN STATISTIKTABELLEN - SPARADE MATCHRESULTAT
        for ($i = 0; $row = mysqli_fetch_array($stats, MYSQLI_NUM); $i++) {
            $rowz[] = $row;
        }
        foreach ($rowz as $r) {
            if ($r[2] > $roundCompS) {
                $roundCompS = $r[2];
                $roundGatheredS[] = $roundArrS;
                $roundArrS = array();
                array_shift($r);
                array_shift($r);
                array_shift($r);
                $roundArrS[] = $r;
            } else {
                array_shift($r);
                array_shift($r);
                array_shift($r);
                $roundArrS[] = $r;
            }
        }
        $roundGatheredS[] = $roundArrS;
        $out[] = $roundGathered;

//ÖVERSÄTT VILANDE IDS TILL NAMN
        $idArrRest = array();
        foreach ($out[0] as $nollKey => $nollLevel) {
            foreach ($nollLevel as $ettKey => $ettLevel) {
                if ($ettKey % 2 == 0)
                    $idArrRest[] = $out[0][$nollKey][$ettKey];
            }
        }
        $idArrRest = getPlayerNames($idArrRest);
        $k = 0;
        foreach ($out[0] as $nollKey => $nollLevel) {
            foreach ($nollLevel as $ettKey => $ettLevel) {
                if ($ettKey % 2 == 0) {
                    $out[0][$nollKey][$ettKey] = $idArrRest[$k];
                    $k++;
                }
            }
        }
        $out[] = $roundGatheredS;

//    ÖVERSÄTT MATCHSPELARIDS TILL NAMN
        $matchIDarr = array();
        for ($i = 0; $i < count($out[1]); $i++) {
            for ($j = 0; $j < count($out[1][$i]); $j++) {
                $match = array();
                $match[] = $out[1][$i][$j][1];
                $match[] = $out[1][$i][$j][2];
                $match[] = $out[1][$i][$j][3];
                $match[] = $out[1][$i][$j][4];
                $matchIDarr[$i][] = $match;
            }
        }
        for ($i = 0; $i < count($matchIDarr); $i++) {
            for ($j = 0; $j < count($matchIDarr[$i]); $j++) {
                $matchIDarr[$i][$j] = getPlayerNames($matchIDarr[$i][$j]);
            }
        }
		
        for ($i = 0; $i < count($out[1]); $i++) {
            for ($j = 0; $j < count($out[1][$i]); $j++) {
                $out[1][$i][$j][1] = getBoardNumberFromID($out[1][$i][$j][1]) . " " . $matchIDarr[$i][$j][0];
                $out[1][$i][$j][2] = getBoardNumberFromID($out[1][$i][$j][2]) . " " . $matchIDarr[$i][$j][1];
                $out[1][$i][$j][3] = getBoardNumberFromID($out[1][$i][$j][3]) . " " . $matchIDarr[$i][$j][2];
                $out[1][$i][$j][4] = getBoardNumberFromID($out[1][$i][$j][4]) . " " . $matchIDarr[$i][$j][3];
            }
        }
		
        $out = json_encode($out);
        echo $out;
    }
}
function getBoardNumberFromID($playerID){
	global $mysqli;
	$getNumSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'],$playerID);
	$gottenBoardnum = $mysqli->query($getNumSQL);
	$gBN = $gottenBoardnum->fetch_row();

	return $gBN[0];
	
}
function getPlayerNames($idArray) {
    global $mysqli;
    $sql = sprintf("SELECT id, first_name, last_name FROM players");
    $result = $mysqli->query($sql);
    $playerArr = array();
    while ($row = $result->fetch_row()) {
        $player = array();
        $player[] = $row[0];
        $player[] = $row[1];
        $player[] = $row[2];
        $playerArr[] = $player;
    }
    $retArr = array();
    foreach ($idArray as $id) {
        foreach ($playerArr as $pA) {
            if ($pA[0] == $id) {
                $retArr[] = $pA[1] . " " . $pA[2];
            }
        }
    }
    return $retArr;
}

?>
