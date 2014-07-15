<?php

include ('Player.php');
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();
$playerList = array();

$getPlayerListSQL = sprintf("SELECT * FROM `playerlist-session-play` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
$playerListDB = $mysqli -> query($getPlayerListSQL);
while ($PL = mysqli_fetch_row($playerListDB)) {
	$playerList[] = $PL[1];
	//session playerid  user
}
if ($_REQUEST['action'] == "addPlayer") {
	foreach ($_REQUEST['ids'] as $requestId) {
		$existed = false;
		foreach ((array)$playerList as $playerID) {
			if ($playerID == $requestId) {
				$existed = true;
			}
		}
		if (!$existed) {
			// var_dump($requestId);
			// $insertPlayerSQL = sprintf("INSERT INTO `playerlist-session-play` (session, playerid, user) VALUES ('%s', '%s', '%s')", $_SESSION['sessID'], $requestId, $_SESSION['username']);
			// $insertPlayerSQL2 = sprintf("INSERT INTO `playerlist-session-play` (session, playerid, user) VALUES ('%s', '%s', '%s')", $_SESSION['sessID'], 3, $_SESSION['username']);
			// var_dump($insertPlayerSQL);
			// if (!$mysqli -> query($insertPlayerSQL)) {
				// printf("Errormessage: %s\n", $mysqli -> error);
			// }

			/* Prepared statement, stage 1: prepare */
			if (!($stmt = $mysqli -> prepare("INSERT INTO `playerlist-session-play` (session, playerid, user) VALUES (?,?,?)"))) {
				echo "Prepare failed: (" . $mysqli -> errno . ") " . $mysqli -> error;
			}

			/* Prepared statement, stage 2: bind and execute */
			$id = 1;
			if (!$stmt -> bind_param("sis", $_SESSION['sessID'], $requestId, $_SESSION['username'])) {
				echo "Binding parameters failed: (" . $stmt -> errno . ") " . $stmt -> error;
			}

			if (!$stmt -> execute()) {
				echo "Execute failed: (" . $stmt -> errno . ") " . $stmt -> error;
			}

			/* explicit close recommended */
			$stmt -> close();
		}else{
			
		}
	}

}
if ($_REQUEST['action'] == "movePlayer") {
	if ($_REQUEST['type'] == "dayToRest") {
		//remove from session play -> add to session rest
		foreach ($_REQUEST['ids'] as $requestId) {
			$removePlayListSQL = sprintf("DELETE FROM `playerlist-session-play` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'], $requestId);
			$mysqli -> query($removePlayListSQL);
			$addRestListSQL = sprintf("INSERT INTO `playerlist-session-rest` (session, playerid, user) VALUES ('%s', '%s', '%s')", $_SESSION['sessID'], $requestId, $_SESSION['username']);
			$mysqli -> query($addRestListSQL);
		}
	} elseif ($_REQUEST['type'] == "restToDay") {
		//remove from session-rest -> add to session play
		foreach ((array)$_REQUEST['ids'] as $requestId) {
			$removeRestListSQL = sprintf("DELETE FROM `playerlist-session-rest` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'], $requestId);
			$mysqli -> query($removeRestListSQL);
			$addPlayListSQL = sprintf("INSERT INTO `playerlist-session-play` (session, playerid, user) VALUES ('%s', '%s', '%s')", $_SESSION['sessID'], $requestId, $_SESSION['username']);
			$mysqli -> query($addPlayListSQL);
		}
	}
}
if ($_REQUEST['action'] == "removePlayer") {
	//remove from session-play
	foreach ((array)$_REQUEST['ids'] as $requestId) {
		$removePlayListSQL = sprintf("DELETE FROM `playerlist-session-play` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'], $requestId);
		$mysqli -> query($removePlayListSQL);
		$removeBoardNumSQL = sprintf("DELETE FROM `players-boardnumbers` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'], $requestId);
		$mysqli -> query($removeBoardNumSQL);
	}

}
/* determine our thread id */
$thread_id = $mysqli->thread_id;

/* Kill connection */
$mysqli->kill($thread_id);
echo json_encode("success");
?>
