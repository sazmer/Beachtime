<?php
function getPlayerFromID($playerArrays, $playerID) {

	foreach ($playerArrays as $player) {
		if ($player -> id == $playerID) {
			return $player;
		}
	}

	//echo $playerID . 'SPELARID!';
}

include 'Player.php';
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
	$playerId = $_REQUEST['id'];
	$query = sprintf("SELECT * FROM players WHERE id = '%s' AND user = '%s'", $playerId, $_SESSION['username']);
	$result = $mysqli -> query($query);
	$gottenPlayer = $result -> fetch_row();
	if (isset($_SESSION['playerArray'])) {
		$playerArrayStats = $_SESSION['playerArray'];
		$player = getPlayerFromID($playerArrayStats, $playerId);
	}
	if (isset($player)) {
		$gottenPlayer[] = $player -> rest;
	} else {
		$gottenPlayer[] = 0;
	}

	if ($_REQUEST['BN'] == "true") {
		$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE playerid='%s' AND session='%s' AND user='%s'", $playerId, $_SESSION['sessID'], $_SESSION['username']);
		$gottenBN = $mysqli -> query($getBNSQL);
		$BN;
		while ($GBN = $gottenBN -> fetch_row()) {
			$BN = $GBN[0];
		}
		$gottenPlayer[] = $BN;
	}
	$result = json_encode($gottenPlayer);
	echo $result;
} else {
	echo 'You are not authorized to access this page, please login. <br/>';
	header('Location: login.php?error=1');
	exit();
	die();
}
?>