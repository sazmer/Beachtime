<?php
include ('DB.php');
include ('Player.php');
include ('Pair.php');
include ('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
	$pArray = array();

	$getPlayingSQL = sprintf("SELECT id_from_players FROM `players-sessions-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$getPlaying = $mysqli -> query($getPlayingSQL);
	if ($getPlaying -> num_rows == 0) {
		$getPlayingSQL = sprintf("SELECT id_from_players FROM `players-sessions` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$getPlaying = $mysqli -> query($getPlayingSQL);
	}
	while ($row = $getPlaying -> fetch_row()) {
		$allPlayersSQL = sprintf("SELECT * FROM players WHERE id = '%s' AND user='%s'", $row[0], $_SESSION['username']);
		$allPlayers = $mysqli -> query($allPlayersSQL);
		while ($playerFromDB = $allPlayers -> fetch_row()) {

			$newPlayer = new Player($playerFromDB[0], $playerFromDB[1], $playerFromDB[2], $playerFromDB[3]);
			$newPlayer -> rested = (int)$row[1];
			$newPlayer -> rested_last = (int)$row[2];
			$newPlayer -> same_sex = (int)$row[3];
			if (!$row[4] == null) {
				$newPlayer -> history = unserialize($row[4]);
			}
			$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'], $playerFromDB[0]);
			$getBN = $mysqli -> query($getBNSQL);
			$BN = $getBN -> fetch_row();
			$newPlayer -> boardNumber = $BN[0];

			$pArray[] = $newPlayer;
		}
	}
	
	$getURMatchesSQL = sprintf("SELECT * FROM `game_statistics-unreported` WHERE session = '%s' AND user = '%s'", $_SESSION['sessID'], $_SESSION['username']);
	$gottenURMatches = $mysqli -> query($getURMatchesSQL);
	if (mysqli_num_rows($gottenURMatches) > 0) {
		$outArr = array();
		$outMatch = array();
		$matchArray = array();
		//gå igenom och kolla boardnumbers???

		while ($match = $gottenURMatches -> fetch_row()) {
			$pair1 = array();
			$pair2 = array();
			$tempPairList = array();
			$matchToSave = array();
			$outArr[] = $match;
			$matchIds = array();
			$matchIds[] = $match[4];
			$matchIds[] = $match[5];
			$matchIds[] = $match[6];
			$matchIds[] = $match[7];
			$players = array();

			foreach ($matchIds as $key => $id) {
				foreach ($pArray as $player) {
					if ($id == $player -> id) {
						$players[$key] = $player;
						
						break;
					}
				}
			}
			$pair1["player1"] = $players[0];
			$pair1["player2"] = $players[1];
			$pair2["player1"] = $players[2];
			$pair2["player2"] = $players[3];
			$tempPairList[] = array("par1" => $pair1, "par2" => $pair2);
			$matchArray[] = array("match" => $tempPairList);
		}
		$outMatch[] = $matchArray;
		//get resters, lägg till array till resultat[]
		$getURRestersSQL = sprintf("SELECT * FROM `players-rests-unreported` WHERE session = '%s' AND user = '%s'", $_SESSION['sessID'], $_SESSION['username']);
		$gottenURResters = $mysqli -> query($getURRestersSQL);

		$resters = array();
		while ($rester = $gottenURResters -> fetch_row()) {
			foreach ($pArray as $player) {
				if ($rester[3] == $player -> id) {
					$resters[] = $player;
				}
			}
		}

		$outMatch[] = $resters;
		$_SESSION['savedMatches'] = $outMatch;
		echo json_encode($outMatch);
	} else {

		if (mysqli_num_rows($gottenURMatches) > 0) {
			$outArr = array();
			$outMatch = array();
			$matchArray = array();
			//gå igenom och kolla boardnumbers???

			while ($match = $gottenURMatches -> fetch_row()) {
				$pair1 = array();
				$pair2 = array();
				$tempPairList = array();
				$matchToSave = array();
				$outArr[] = $match;
				$matchIds = array();
				$matchIds[] = $match[4];
				$matchIds[] = $match[6];
				$matchIds[] = $match[8];
				$matchIds[] = $match[10];
				$players = array();
				foreach ($matchIds as $key => $id) {
					foreach ($pArray as $player) {
						if ($id == $player -> id) {
							$players[$key] = $player;

						}
					}
				}
				$pair1["player1"] = $players[0];
				$pair1["player2"] = $players[1];
				$pair2["player1"] = $players[2];
				$pair2["player2"] = $players[3];
				$tempPairList[] = array("par1" => $pair1, "par2" => $pair2);
				$matchArray[] = array("match" => $tempPairList);

			}
			$outMatch[] = $matchArray;
			//get resters, lägg till array till resultat[]
			$getURRestersSQL = sprintf("SELECT * FROM `players-rests-unreported` WHERE session = '%s' AND user = '%s'", $_SESSION['sessID'], $_SESSION['username'], $_SESSION['roundNum']);
			$gottenURResters = $mysqli -> query($getURRestersSQL);

			if (empty($gottenURResters -> fetch_row())) {
				$getURRestersSQL = sprintf("SELECT * FROM `players-rests` WHERE session = '%s' AND user = '%s' AND round='%s'", $_SESSION['sessID'], $_SESSION['username'], $_SESSION['roundNum']);
				$gottenURResters = $mysqli -> query($getURRestersSQL);
			}

			$resters = array();
			while ($rester = $gottenURResters -> fetch_row()) {
				foreach ($pArray as $player) {
					if ($rester[3] == $player -> id) {
						$resters[] = $player;
					}
				}
			}

			$outMatch[] = $resters;
			$_SESSION['savedMatches'] = $outMatch;
			echo json_encode($outMatch);
		}
	}
}
?>
