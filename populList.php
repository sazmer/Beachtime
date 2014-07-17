<?php

include ('Player.php');
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();

switch ($_REQUEST['action']) {
	case "main" :
		$query = sprintf("SELECT * FROM players WHERE user = '%s'", $_SESSION['username']);
		$result = $mysqli -> query($query);
		$arrayToPrint = array();
		while ($row = $result -> fetch_row()) {
			$playerA = array();
			$playerA[] = $row[0];
			$playerA[] = $row[1];
			$playerA[] = $row[2];
			$arrayToPrint[] = $playerA;
		}

		function sortByFname($p1, $p2) {

			$al = strtolower($p1[1]);
			$bl = strtolower($p2[1]);
			if ($al == $bl) {
				return 0;
			}
			return ($al > $bl) ? +1 : -1;
		}

		usort($arrayToPrint, "sortByFname");

		$returnArray = array();
		foreach ($arrayToPrint as $playerB) {
			$returnArray[] = '<option  value="' . $playerB[0] . '">' . $playerB[1] . " " . $playerB[2] . '</option>';
		}

		break;
	case "filter" :
		$pieces = array();
		if (strpos($_REQUEST['q'], ' ') !== false) {
			$pieces = explode(" ", $_REQUEST['q']);
		$query = sprintf("SELECT * FROM players WHERE (first_name LIKE '%%%s%%' OR last_name LIKE '%%%s%%' OR CONCAT( first_name,  ' ', last_name ) LIKE '%%%s%%' OR (first_name LIKE '%%%s%%' AND last_name LIKE '%%%s%%')) AND user = '%s'", $_REQUEST['q'], $_REQUEST['q'], $_REQUEST['q'], $pieces[0], $pieces[1], $_SESSION['username']);
		}else{
			$query = sprintf("SELECT * FROM players WHERE (first_name LIKE '%%%s%%' OR last_name LIKE '%%%s%%' OR CONCAT( first_name,  ' ', last_name ) LIKE '%%%s%%') AND user = '%s'", $_REQUEST['q'], $_REQUEST['q'], $_REQUEST['q'], $_SESSION['username']);
		}
		$result = $mysqli -> query($query);

		$arrayToPrint = array();
		while ($row = $result -> fetch_row()) {
			$playerA = array();
			$playerA[] = $row[0];
			$playerA[] = $row[1];
			$playerA[] = $row[2];
			$arrayToPrint[] = $playerA;
		}
		$returnArray = array();
		if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'mPairs') {
			$round = $_REQUEST['round'];
			$sessionWithoutName = str_replace($_SESSION['username'], "", $_SESSION['playID']);
			$existCheck = sprintf("SELECT * FROM `chosenPairs` WHERE `round`= '%s' AND `user` = '%s' AND `session`= '%s'", $round, $_SESSION['username'], $sessionWithoutName);
			$checked = $mysqli -> query($existCheck);
			foreach ($arrayToPrint as $playerB) {

				$exists = false;
				while ($check = $checked -> fetch_assoc()) {
					// var_dump($playerB[0]);
					// var_dump($check['player1']);
					// var_dump($check['player2']);
					//                    echo "<br>";
					//                    var_dump($check);
					//                    echo "<br>";
					//                    var_dump($playerB[0]);
					//                    echo "SLUT<br>";
					//
					if ($check['player1'] == $playerB[0] || $check['player2'] == $playerB[0]) {
						$exists = true;
					}
				}
				// var_dump($exists);
				$smallArr = array();
				foreach ($_SESSION['playerArray'] as $player) {
					if ($player -> id == $playerB[0] && !$exists) {
						$smallArr[] = $playerB[0];
						$smallArr[] = $playerB[1] . " " . $playerB[2];
						$returnArray[] = $smallArr;
					}
				}
			}
		} else {
			foreach ($arrayToPrint as $playerB) {
				$smallArr = array();
				$smallArr[] = $playerB[0];
				$smallArr[] = $playerB[1] . " " . $playerB[2];
				$returnArray[] = $smallArr;
			}
		}
		break;

	case "day" :
		//Playing
		$allPlayingSQL = sprintf("SELECT playerid FROM `playerlist-session-play` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$allPlayingG = $mysqli -> query($allPlayingSQL);
		$allPID = array();
		while ($allPlaying = $allPlayingG -> fetch_row()) {
			$allPID[] = $allPlaying[0];
		}
		$players = array();
		foreach ($allPID as $PID) {
			$getPlayerSQL = sprintf("SELECT * FROM players WHERE id='%s' AND user='%s'", $PID, $_SESSION['username']);
			$getPlayerG = $mysqli -> query($getPlayerSQL);
			while ($getPG = $getPlayerG -> fetch_row()) {
				$players[] = $getPG;
			}
		}

		$pArray = array();
		foreach ($players as $player) {
			$newPlayer = new Player($player[0], $player[1], $player[2], $player[3]);
			$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE playerid='%s' AND session='%s' AND user='%s'", $player[0], $_SESSION['sessID'], $_SESSION['username']);
			$gottenBN = $mysqli -> query($getBNSQL);
			$BN;
			while ($GBN = $gottenBN -> fetch_row()) {
				$BN = $GBN[0];
			}
			$newPlayer -> boardNumber = $BN;
			$pArray[] = $newPlayer;
		}

		$returnArray = array();

		usort($pArray, "sortByNumber");
		foreach ((array)$pArray as $player) {
			$returnArray[] = '<option value="' . $player -> id . '">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</option>';
		}
		if (count($returnArray2) < 1) {
			$returnArray[] = "empty";
		}
		// $returnArray = array();
		// //var_dump($_SESSION['savedPlayers']);
		// if (isset($_SESSION['savedPlayers']) && count($_SESSION['savedPlayers']) > 0) {
		// usort($_SESSION['savedPlayers'], "sortByNumber");
		// foreach ($_SESSION['savedPlayers'] as $player) {
		// $returnArray[] = '<option value="' . $player -> id . '">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</option>';
		// }
		// } else {
		// $returnArray[] = "empty";
		// }
		break;
	case "rest" :
		$allRestingSQL = sprintf("SELECT playerid FROM `playerlist-session-rest` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$allRestingG = $mysqli -> query($allRestingSQL);
		$allRID = array();
		while ($allResting = $allRestingG -> fetch_row()) {
			$allRID[] = $allResting[0];
		}
		$players = array();
		foreach ($allRID as $RID) {
			$getPlayerSQL = sprintf("SELECT * FROM players WHERE id='%s' AND user='%s'", $RID, $_SESSION['username']);
			$getPlayerG = $mysqli -> query($getPlayerSQL);
			while ($getPG = $getPlayerG -> fetch_row()) {
				$players[] = $getPG;
			}
		}

		$rArray = array();
		foreach ($players as $player) {
			$newPlayer = new Player($player[0], $player[1], $player[2], $player[3]);
			$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE playerid='%s' AND session='%s' AND user='%s'", $player[0], $_SESSION['sessID'], $_SESSION['username']);
			$gottenBN = $mysqli -> query($getBNSQL);
			$BN;
			while ($GBN = $gottenBN -> fetch_row()) {
				$BN = $GBN[0];
			}
			$newPlayer -> boardNumber = $BN;
			$rArray[] = $newPlayer;
		}
		$returnArray = array();
		usort($rArray, "sortByNumber");
		foreach ((array)$rArray as $player) {
			$returnArray[] = '<option  value="' . $player -> id . '">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</option>';
		}
		if (count($returnArray) < 1) {
			$returnArray[] = "empty";
		}
		// if (isset($_SESSION['restPlayers']) && count($_SESSION['restPlayers']) > 0) {
		// usort($_SESSION['restPlayers'], "sortByNumber");
		// $returnArray = array();
		// foreach ($_SESSION['restPlayers'] as $player) {
		// $returnArray[] = '<option value="' . $player -> id . '">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</option>';
		// }
		// } else {
		// $returnArray[] = "empty";
		// }
		break;
	case "list" :
		//Playing
		$allPlayingSQL = sprintf("SELECT playerid FROM `playerlist-session-play` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$allPlayingG = $mysqli -> query($allPlayingSQL);
		$allPID = array();
		while ($allPlaying = $allPlayingG -> fetch_row()) {
			$allPID[] = $allPlaying[0];
		}
		$players = array();
		foreach ($allPID as $PID) {
			$getPlayerSQL = sprintf("SELECT * FROM players WHERE id='%s' AND user='%s'", $PID, $_SESSION['username']);
			$getPlayerG = $mysqli -> query($getPlayerSQL);
			while ($getPG = $getPlayerG -> fetch_row()) {
				$players[] = $getPG;
			}
		}

		$pArray = array();
		foreach ($players as $player) {
			$newPlayer = new Player($player[0], $player[1], $player[2], $player[3]);
			$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE playerid='%s' AND session='%s' AND user='%s'", $player[0], $_SESSION['sessID'], $_SESSION['username']);
			$gottenBN = $mysqli -> query($getBNSQL);
			$BN;
			while ($GBN = $gottenBN -> fetch_row()) {
				$BN = $GBN[0];
			}
			$newPlayer -> boardNumber = $BN;
			$pArray[] = $newPlayer;
		}

		$returnArray = array();
		$returnArray2 = array();
		usort($pArray, "sortByNumber");
		foreach ((array)$pArray as $player) {
			$returnArray2[] = '<li data-theme="a" data-icon="user" id="' . $player -> id . '"><a href="#" id="player' . $player -> id . '" class="editBN">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</a></li>';
		}
		if (count($returnArray2) < 1) {
			$returnArray2[] = "empty";
		}

		//Resting --------------------------------------
		$allRestingSQL = sprintf("SELECT playerid FROM `playerlist-session-rest` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$allRestingG = $mysqli -> query($allRestingSQL);
		$allRID = array();
		while ($allResting = $allRestingG -> fetch_row()) {
			$allRID[] = $allResting[0];
		}
		$players = array();
		foreach ($allRID as $RID) {
			$getPlayerSQL = sprintf("SELECT * FROM players WHERE id='%s' AND user='%s'", $RID, $_SESSION['username']);
			$getPlayerG = $mysqli -> query($getPlayerSQL);
			while ($getPG = $getPlayerG -> fetch_row()) {
				$players[] = $getPG;
			}
		}

		$rArray = array();
		foreach ($players as $player) {
			$BN = 0;
			$newPlayer = new Player($player[0], $player[1], $player[2], $player[3]);
			$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE playerid='%s' AND session='%s' AND user='%s'", $player[0], $_SESSION['sessID'], $_SESSION['username']);
			$gottenBN = $mysqli -> query($getBNSQL);
			$BN;
			while ($GBN = $gottenBN -> fetch_row()) {
				$BN = $GBN[0];
			}
			$newPlayer -> boardNumber = $BN;
			$rArray[] = $newPlayer;
		}
		$returnArray1 = array();
		usort($rArray, "sortByNumber");
		foreach ((array)$rArray as $player) {
			$returnArray1[] = '<li data-icon="user" data-theme="b" id="' . $player -> id . '"><a href="#" id="player' . $player -> id . '" class="editBN">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</a></li>';
		}
		if (count($returnArray1) < 1) {
			$returnArray1[] = "empty";
		}

		// if (isset($_SESSION['restPlayers']) && count($_SESSION['restPlayers']) > 0) {
		// usort($_SESSION['restPlayers'], "sortByNumber");
		// $returnArray = array();
		// foreach ($_SESSION['restPlayers'] as $player) {
		// $returnArray1[] = '<li data-icon="arrow-r" data-theme="c" id="' . $player -> id . '">' . $player -> boardNumber . " " . $player -> firstname . " " . $player -> lastname . '</li>';
		// }
		// } else {
		// $returnArray1[] = "empty";
		// }
		$returnArray[] = $returnArray2;
		$returnArray[] = $returnArray1;
		break;
	case "freeBN" :
		$getBoardNumsSQL = sprintf("SELECT boardnumber,playerid FROM `players-boardnumbers` WHERE session = '%s' AND user = '%s'", $_SESSION['sessID'], $_SESSION['username']);
		$BNFromDB = $mysqli -> query($getBoardNumsSQL);
		$existingBNArr = array();
		while ($bn = $BNFromDB -> fetch_row()) {
			$existingBNArr[] = (int)$bn[0];
		}

		// construct a new array:1,2....max(given array).
		$compareArr = range(1, max($existingBNArr));
		$missingBNs = array();
		// use array_diff to get the missing elements
		$missingBNs = array_diff($compareArr, $existingBNArr);
		
		// (3,6)
		$nextBig = max($existingBNArr);
		$returnArray = array();
		$returnArray[] = $missingBNs;
		$returnArray[] = $nextBig + 1;
		break;
}

echo json_encode($returnArray);

function sortByNumber($p1, $p2) {
	if ($p1 -> boardNumber < $p2 -> boardNumber) {
		return -1;
	} else if ($p1 -> boardNumber > $p2 -> boardNumber) {
		return 1;
	} else {
		return 0;
	}
}
?>