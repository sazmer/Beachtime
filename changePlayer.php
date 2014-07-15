<?php

function getPlayerFromID($playerArray, $playerID) {
	foreach ($playerArray as $player) {
		if ($player -> getID() == $playerID) {
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
	if ($_REQUEST['BoN'] == true) {
		$id = $_REQUEST['id'];
		$fname = $_REQUEST['fname'];
		$lname = $_REQUEST['lname'];
		$sex = $_REQUEST['sex'];
		$BN = $_REQUEST['BN'];
		$updatePlayerSQL = sprintf("UPDATE players SET first_name='%s', last_name='%s', sex='%s'" . " WHERE id='%s'", $fname, $lname, $sex, $id);
		$mysqli -> query($updatePlayerSQL);
		$updateBNSQL = sprintf("UPDATE `players-boardnumbers` SET boardnumber='%s' WHERE playerid='%s' AND session='%s' AND user='%s'",$BN, $id, $_SESSION['sessID'], $_SESSION['username']);
		$mysqli->query($updateBNSQL);
		var_dump($updateBNSQL);
	} else {
		$fname = $_REQUEST['fname'];
		$lname = $_REQUEST['lname'];
		$sex = $_REQUEST['sex'];
		//    $wins = $_REQUEST['wins'];
		//    $rests = $_REQUEST['rests'];
		$id = $_REQUEST['id'];
		$played_games = $_REQUEST['played_games'];
		//period??
		$query = sprintf("UPDATE players SET first_name='%s', last_name='%s', sex='%s'" . " WHERE id='%s'", $fname, $lname, $sex, $id);
		$result = $mysqli -> query($query);

		$playerId = $mysqli -> insert_id;

		if (isset($_SESSION['playerArray'])) {
			$playerArrayStats = $_SESSION['playerArray'];
			$player = getPlayerFromID($playerArrayStats, $id);
			if (isset($player)) {
				//make changes to player
				$player -> firstname = $fname;
				$player -> lastname = $lname;
				$player -> gender = $sex;
				//            $player->wins = $wins;
				//            $player->rest = $rests;
			}
		} else {
			//        $playerArrayStats[] = new Player($id, $fname, $lname, $sex, $wins, $rests);
			//        $_SESSION['playerArray'] = $playerArrayStats;
		}

		$_SESSION['playerArray'] = $playerArrayStats;

		echo "Success!";
	}
} else {
	echo 'You are not authorized to access this page, please login. <br/>';
	header('Location: login.php?error=1');
	exit();
	die();
}
?>
