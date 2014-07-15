<?php
include ('Player.php');
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {

	// if (isset($_SESSION['lastRound'])) {
	// $_SESSION['playerArray'] = array();
	// foreach ($_SESSION['lastRound'] as $player) {
	// $_SESSION['playerArray'][] = clone $player;
	// }
	// }
	$getRepAll = sprintf("SELECT * FROM `players-sessions` WHERE session = '%s' AND user = '%s'", $_REQUEST['chosen'], $_SESSION['username']);
	$gottenRepPlayers = $mysqli -> query($getRepAll);

	$_SESSION['playerArray'] = array();
	$usedNumbers = array();
	$toInsert = array();
	while ($row = $gottenRepPlayers -> fetch_row()) {
		$allPlayersSQL = sprintf("SELECT * FROM players WHERE id = '%s'", $row[0]);
		$allPlayers = $mysqli -> query($allPlayersSQL);
		while ($playerFromDB = $allPlayers -> fetch_row()) {
			$newPlayer = new Player($playerFromDB[0], $playerFromDB[1], $playerFromDB[2], $playerFromDB[3], $playerFromDB[4], $playerFromDB[6]);
			$newPlayer -> rested = (int)$row[1];
			$newPlayer -> rested_last = (int)$row[3];
			$newPlayer -> same_sex = (int)$row[4];
			$newPlayer -> history = unserialize($row[5]);
			$newNumber = false;
			if (count($usedNumbers) > 0) {
				foreach ($usedNumbers as $uN) {
					if ($uN == $row[2]) {
						$toInsert[] = $newPlayer;
					} else {
						$newNumber = true;
					}
				}
			} else {
				$newNumber = true;
			}
			if ($newNumber) {
				$newPlayer -> boardNumber = (int)$row[2];
				$_SESSION['boardNumberCounter'][$row[2]] = $newPlayer;
				$usedNumbers[] = $row[2];
			}
			$_SESSION['playerArray'][] = $newPlayer;
			//Läs in alla från sessionen till playerarray
		}
	}

	$_SESSION['savedMatches'] = array();
	$_SESSION['canReport'] = false;

	//töm temporära poster i databasen
	$delTempRoundsSQL = sprintf("DELETE FROM `game_statistics-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$mysqli -> query($delTempRoundsSQL);
	$delTempSessSQL = sprintf("DELETE FROM `players-sessions-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$mysqli -> query($delTempSessSQL);
	$delTempRestsSQL = sprintf("DELETE FROM `players-rests-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$mysqli -> query($delTempRestsSQL);

	$returnRound = json_encode($_SESSION['playerArray']);
	echo $returnRound;
} else {
	echo 'You are not authorized to access this page, please login. <br/>';
	header('Location: login.php?error=1');
	exit();
	die();
}
?>
