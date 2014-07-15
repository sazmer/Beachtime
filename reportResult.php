<?php
include 'Player.php';
include 'Pair.php';
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
	$_SESSION['playerArray'] = array();
	if (!isset($_SESSION['roundNum'])) {
		$_SESSION['roundNum'] = 1;
	}
	if (isset($_SESSION['canReport']) && $_SESSION['canReport']) {

		$getPlayingSQL = sprintf("SELECT * FROM `players-sessions-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$getPlaying = $mysqli -> query($getPlayingSQL);

		while ($row = $getPlaying -> fetch_row()) {
			$allPlayersSQL = sprintf("SELECT * FROM players WHERE id = '%s'", $row[0]);
			$allPlayers = $mysqli -> query($allPlayersSQL);
			while ($playerFromDB = $allPlayers -> fetch_row()) {
				$newPlayer = new Player($playerFromDB[0], $playerFromDB[1], $playerFromDB[2], $playerFromDB[3]);
				$newPlayer -> rest = (int)$row[1];
				$newPlayer -> rested_last = (int)$row[3];
				$newPlayer -> same_sex = (int)$row[4];
				$newPlayer -> history = unserialize($row[5]);
				$newPlayer -> boardNumber = $row[2];
				$_SESSION['playerArray'][] = $newPlayer;
			}
		}
		$sessionWithoutName = str_replace($_SESSION['username'], "", $_SESSION['playID']);
		$winners = array();
		foreach ($_REQUEST['winnerIds'] as $winnerId) {
			$wins = 0;

			foreach ((array)$_SESSION['playerArray'] as $player) {
				if ($player -> getId() === $winnerId) {
					$player -> wins++;
					$player -> todays_wins++;
					$winners[] = $player -> id;
				}
			}
			// $updateWins = sprintf("INSERT INTO `players-periods` (id_from_players,wins, period, user)" . "VALUES ('%s','1', '%s', '%s')  ON DUPLICATE KEY UPDATE wins= wins + 1", $winnerId, $_SESSION['period'], $_SESSION['username']);
			// $mysqli -> query($updateWins);

		}

		updateDatabase($_SESSION['savedMatches'], $_SESSION['roundNum'], $sessionWithoutName, $winners, $_SESSION['username']);
		$_SESSION['canReport'] = false;
		$playerIds = array_merge($_REQUEST['loserIDs'], $_REQUEST['winnerIds']);
		foreach ($playerIds as $playerId) {
			foreach ($_SESSION['playerArray'] as $player) {
				if ($player -> getId() === $playerId) {
					$player -> rested_last = 0;
					$player -> played_games++;
				}
			}
		}
		$playerIdsImploded = implode(',', $playerIds);
		foreach ($playerIds as $pID) {
			$updatePlayedGames = sprintf("INSERT INTO `players-periods` (id_from_players,played_games, period, user)" . "VALUES ('%s','1','%s', '%s')  ON DUPLICATE KEY UPDATE played_games = played_games + 1", $pID, $_SESSION['period'], $_SESSION['username']);
			$mysqli -> query($updatePlayedGames);
		}

		foreach ($playerIds as $id) {
			foreach ($_SESSION['playerArray'] as $player) {
				$sessPlaySQL = sprintf("INSERT INTO `players-sessions` (id_from_players,rested,rested_last,same_sex,history,session, user)" . "VALUES ('%s','%s','%s','%s','%s','%s','%s')  ON DUPLICATE KEY UPDATE rested=VALUES(`rested`),rested_last=VALUES(`rested_last`),same_sex=VALUES(`same_sex`),history=VALUES(`history`)", $player -> id, $player -> rest, $player -> rested_last, $player -> same_sex, serialize($player -> history), $_SESSION['sessID'], $_SESSION['username']);
				$mysqli -> query($sessPlaySQL);
			}
		}
		// //Ska ändras till att bara ta bort den specifika sessionens unreported såklart
		$clearUnrepSessSQL = sprintf("DELETE FROM `players-sessions-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$mysqli -> query($clearUnrepSessSQL);
		$clearUnrepRestsSQL = sprintf("DELETE FROM `players-rests-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$mysqli -> query($clearUnrepRestsSQL);
		$clearUnrepstatsSQL = sprintf("DELETE FROM `game_statistics-unreported` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
		$mysqli -> query($clearUnrepstatsSQL);
		// $truncateUnreportedSession = sprintf("TRUNCATE TABLE `players-sessions-unreported`");
		// $truncateUnreportedRests = sprintf("TRUNCATE TABLE `players-rests-unreported`");
		// $truncateUnreportedStats = sprintf("TRUNCATE TABLE `game_statistics-unreported`");
		// $mysqli -> query($truncateUnreportedSession);
		// $mysqli -> query($truncateUnreportedRests);
		// $mysqli -> query($truncateUnreportedStats);

		$_SESSION['roundNum']++;
		echo 'YES';
	} else {
		echo 'NO';
	}
} else {
	echo 'You are not authorized to access this page, please login. <br/>';
	header('Location: login.php?error=1');
	exit();
	die();
}

function updateDatabase($resultat, $roundNum, $playID, $winners, $user) {
	global $mysqli;
	$matchCount = 0;
	// foreach ($resultat[0] as $matchNum => $match) {
		// $gamestat = sprintf("INSERT INTO game_statistics (session, round, game,player1,player2,player3,player4,winner1,winner2, period,user)
        // VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s')", $playID, $roundNum, $matchNum, $match["match"][0]["par1"] -> player1 -> id, $match["match"][0]["par1"] -> player2 -> id, $match["match"][0]["par2"] -> player1 -> id, $match["match"][0]["par2"] -> player2 -> id, $winners[$matchCount], $winners[$matchCount + 1], $_SESSION['period'], $user);
		// $mysqli -> query($gamestat);
		// $matchCount = $matchCount + 2;
	// }
	$gamestat = sprintf("INSERT INTO `game_statistics` (session, round, game, player1, player2, player3, player4, winner1, winner2, period, user) SELECT session, round, game, player1, player2, player3, player4, winner1, winner2, period, user FROM `game_statistics-unreported` WHERE user='%s' AND session='%s'", $_SESSION['username'], $_SESSION['sessID']);
	$mysqli->query($gamestat);

	foreach ($resultat[0] as $matchNum => $match) {
		$updateWinsSQL = sprintf("UPDATE `game_statistics` SET winner1='%s', winner2='%s' WHERE session='%s' AND user='%s' AND round='%s' AND game='%s'",  $winners[$matchCount], $winners[$matchCount + 1], $_SESSION['sessID'], $_SESSION['username'], $roundNum, $matchNum);
		$mysqli->query($updateWinsSQL);
		$matchCount = $matchCount + 2;
	}
	



	$rest = sprintf("INSERT INTO `players-rests` SELECT * FROM `players-rests-unreported` WHERE user='%s' AND session='%s'", $_SESSION['username'], $_SESSION['sessID']);
	$mysqli -> query($rest);

}
?>