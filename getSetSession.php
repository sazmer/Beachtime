<?php

include ('DB.php');
include ('Player.php');
include ('loginfunctions.php');
sec_session_start();
$usedNumbers = array();
if (login_check($mysqli) == true) {
	if ($_REQUEST['getset'] == "getAll") {
		$periodsSQL = sprintf("SELECT DISTINCT(session), period FROM `user-sessions` WHERE user = '%s' GROUP BY session", $_SESSION['username']);
		$periods = $mysqli -> query($periodsSQL);
		$periodE = "";
		$periodSessions = array();
		for ($i = 0; $row = mysqli_fetch_array($periods, MYSQLI_ASSOC); $i++) {
			$sessionen = str_replace($_SESSION['username'], "", $row['session']);
			if ($row['period'] == $periodE) {
				$periodSessions[$periodE][] = $sessionen;
			} else {
				$periodE = $row['period'];
				$periodSessions[$periodE][] = $sessionen;
			}
		}
		echo json_encode($periodSessions);
	} elseif ($_REQUEST['getset'] == "set") {
		if ($_REQUEST['chosen'] == "new") {
			$_SESSION['playID'] = $_SESSION['username'] . date("Y-m-d H:i:s");
			$_SESSION['sessID'] = str_replace($_SESSION['username'], "", $_SESSION['playID']);
			$_SESSION['period'] = date("Y");
			$_SESSION['playerArray'] = array();
			$_SESSION['savedPlayers'] = array();
			$_SESSION['roundNum'] = 1;
			$_SESSION['canReport'] = false;
			$_SESSION['boardNumberCounter'] = array();
			$_SESSION['savedMatches'] = array();
			$_SESSION['lastRound'] = array();
			$_SESSION['restPlayers'] = array();
			$inserNewSessionSQL = sprintf("INSERT INTO `user-sessions` (session, period, user) VALUES ('%s','%s','%s')", $_SESSION['sessID'], $_SESSION['period'], $_SESSION['username']);
			$mysqli -> query($inserNewSessionSQL);

			echo $_SESSION['sessID'];
		} else {
			$_SESSION['playID'] = $_SESSION['username'] . $_REQUEST['chosen'];
			$_SESSION['sessID'] = str_replace($_SESSION['username'], "", $_SESSION['playID']);
			//Ändra till rätt roundNum - hämta från DB och ändra $_Session['roundNum']
			$maxRoundSQL = sprintf("SELECT MAX(round) FROM game_statistics WHERE session = '%s' AND user = '%s'", $_REQUEST['chosen'], $_SESSION['username']);
			$maxRoundNum = $mysqli -> query($maxRoundSQL);
			$maxRArr = $maxRoundNum -> fetch_row();
			$maxRoundURSQL = sprintf("SELECT MAX(round) FROM game_statistics WHERE session = '%s' AND user = '%s'", $_REQUEST['chosen'], $_SESSION['username']);
			$maxRoundURNum = $mysqli -> query($maxRoundURSQL);
			$maxRURArr = $maxRoundNum -> fetch_row();
			$_SESSION['roundNum'] = max($maxRArr[0], $maxRURArr[0]);
			// $_SESSION['roundNum'] = (int)$maxRArr[0];
			$_SESSION['roundNum']++;
			$_SESSION['period'] = date("Y");
			$_SESSION['playerArray'] = array();
			$_SESSION['savedPlayers'] = array();
			$_SESSION['canReport'] = false;
			$_SESSION['boardNumberCounter'] = array();
			$_SESSION['lastRound'] = array();
			//behövs inte?
			$_SESSION['restPlayers'] = array();
			//vad gör den här?? - till savestate?
			$getURAll = sprintf("SELECT * FROM `players-sessions-unreported` WHERE session = '%s' AND user = '%s'", $_REQUEST['chosen'], $_SESSION['username']);
			$gottenURPlayers = $mysqli -> query($getURAll);
			$gottenPlayers = array();
			$inListPlaySQL = sprintf("SELECT playerid FROM `playerlist-session-play` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
			$inListPlay = $mysqli -> query($inListPlaySQL);
			$iLP = array();
			while ($iLPlay = mysqli_fetch_row($inListPlay)) {
				$iLP = $iLPlay[0];
			}
			$inListRestSQL = sprintf("SELECT playerid FROM `playerlist-session-rest` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
			$inListRest = $mysqli -> query($inListRestSQL);
			$iLR = array();
			while ($iLRest = mysqli_fetch_row($inListRest)) {
				$iLR = $iLRest[0];
			}
			$listPlayers = array_merge((array)$iLP, (array)$iLRest);
			if (mysqli_num_rows($gottenURPlayers) == 0) {

				$getAll = sprintf("SELECT * FROM `players-sessions` WHERE session = '%s' AND user = '%s'", $_REQUEST['chosen'], $_SESSION['username']);
				$gottenPlayers = $mysqli -> query($getAll);

			} else {

				$gottenPlayers = $gottenURPlayers;
				$_SESSION['canReport'] = true;
			}
			$_SESSION['playerArray'] = array();
			$getBNSQL = sprintf("SELECT boardnumber, playerid FROM `players-boardnumbers` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
			$getBN = $mysqli -> query($getBNSQL);
			while ($gottenBN = mysqli_fetch_row($getBN)) {
				$usedNumbers[$gottenBN[0]] = $gottenBN[1];
			}
			$toInsert = array();
			while ($row = $gottenPlayers -> fetch_row()) {
				$inList = false;
				foreach ($listPlayers as $lPlay) {
					if ($lPlay == $row[0]) {
						$inList = true;
					}

				}
				if ($inList) {
					$allPlayersSQL = sprintf("SELECT * FROM players WHERE id = '%s'", $row[0]);
					$allPlayers = $mysqli -> query($allPlayersSQL);
					while ($playerFromDB = $allPlayers -> fetch_row()) {
						$newNumber = true;
						$newPlayer = new Player($playerFromDB[0], $playerFromDB[1], $playerFromDB[2], $playerFromDB[3]);
						$newPlayer -> rest = (int)$row[1];
						$newPlayer -> rested_last = (int)$row[2];
						$newPlayer -> same_sex = (int)$row[3];
						$newPlayer -> history = unserialize($row[4]);

						if (isset($usedNumbers)) {
							foreach ($usedNumbers as $uN => $uNID) {
								if ($uNID == $playerFromDB[0]) {
									//har redan ett boardnumber, ge spelaren detta
									$newPlayer -> boardNumber = $uN;
									$newNumber = false;
								}
							}
						}
						if ($newNumber) {
							$newPlayer = insertBoardNumber($newPlayer);
						}
						$_SESSION['playerArray'][] = $newPlayer;
						//Läs in alla från sessionen till playerarray
					}
				}
			}

			$_SESSION['savedPlayers'] = $_SESSION['playerArray'];
			$_SESSION['savedMatches'] = array();
			//Hämta matcher från game_stats-unrep och spara i savedmatches

			//ta id från matcherna -> ta spelarna från playerarray -> gör ny savedmatches
			$sessionID = str_replace($_SESSION['username'], "", $_SESSION['playID']);
			echo $sessionID;
		}
	} elseif ($_REQUEST['getset'] == "get" && $_REQUEST['reqSess'] == 'ja') {
		$sessionID = str_replace($_SESSION['username'], "", $_SESSION['playID']);
		echo $sessionID;
	} else {
		echo "Unknown command";
	}
	
	$mysqli->close();
}

function insertBoardNumber($player) {
	global $mysqli;
	global $usedNumbers;
	$foundSpace = false;
	$nums = count($usedNumbers);
	$insertNewBNSQL;
	for ($i = 1; $i < $nums; $i++) {
		if (!isset($usedNumbers[$i])) {
			$player -> boardNumber = $i;
			$usedNumbers[$i] = $player;
			$insertNewBNSQL = sprintf("INSERT INTO `players-boardnumbers` (session, boardnumber, playerid, user) VALUES ('%s','%s','%s','%s')", $_SESSION['sessID'], $i, $player -> id, $_SESSION['username']);
			$foundSpace = true;
			break;
		}
	}
	if (!$foundSpace) {
		$player -> boardNumber = count($usedNumbers) + 2;
		if (count($usedNumbers) == 0) {
			$usedNumbers[1] = $player;
		} else {
			$usedNumbers[] = $player;
		}
		end($usedNumbers);
		$numToIns = key($usedNumbers);
		$insertNewBNSQL = sprintf("INSERT INTO `players-boardnumbers` (session, boardnumber, playerid, user) VALUES ('%s','%s','%s','%s')", $_SESSION['sessID'], $numToIns, $player -> id, $_SESSION['username']);
	}
	$mysqli -> query($insertNewBNSQL);
	return $player;
}
?>