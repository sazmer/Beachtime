<?php

include ('DB.php');
include ('Player.php');
include ('loginfunctions.php');
sec_session_start();
// if (login_check($mysqli) == true) {
//
// } else {
// echo 'You are not authorized to access this page, please login. <br/>';
// header('Location: login.php?error=1');
// exit();
// die();
// }

// $playerIds = explode(',', $_REQUEST['ids']);
// $playerIdsImploded = implode(',', $playerIds);
//period
$playerIds = explode(",", $_REQUEST['ids']);
$getBoardNumsSQL = sprintf("SELECT boardnumber,playerid FROM `players-boardnumbers` WHERE session = '%s' AND user = '%s'", $_SESSION['sessID'], $_SESSION['username']);
$BNFromDB = $mysqli -> query($getBoardNumsSQL);
$existingBNArr = array();
while ($bn = $BNFromDB -> fetch_row()) {
	$existingBNArr[] = (int)$bn[0];
}
if (count($existingBNArr) < 1) {
	$nextBig = 0;
} else {
	// construct a new array:1,2....max(given array).
	$compareArr = range(1, max($existingBNArr));

	// use array_diff to get the missing elements
	$missingBNs = array_diff($compareArr, $existingBNArr);
	// (3,6)
	$nextBig;

	$nextBig = max((array)$existingBNArr);
}
$newNumbers = array();
foreach ((array)$playerIds as $rID) {
	$newBoardNum = insertBoardNumberNew($rID);
	$idToIns = $rID;
	$newNumbers[] = $newBoardNum;
	$insertBNSQL = sprintf("INSERT INTO `players-boardnumbers` (`session`, `boardnumber`, `playerid`, `user`) VALUES ('%s','%s','%s','%s')", $_SESSION['sessID'], $newBoardNum, $idToIns, $_SESSION['username']);

	if (!$mysqli -> query($insertBNSQL)) {
		printf("Errormessage: %s\n", $mysqli -> error);
	}
	if (!$mysqli -> commit()) {
		print("Transaction commit failed\n");
		exit();
	}

}
/* determine our thread id */
$thread_id = $mysqli -> thread_id;

/* Kill connection */
$mysqli -> kill($thread_id);
echo json_encode($newNumbers);

function insertBoardNumberNew($id) {
	global $missingBNs;
	global $nextBig;
	if (count($missingBNs) > 0) {
		$BNtoRet = array_shift($missingBNs);
	} else {
		$BNtoRet = $nextBig + 1;
		$nextBig = $BNtoRet;
	}

	return $BNtoRet;
}
?>
