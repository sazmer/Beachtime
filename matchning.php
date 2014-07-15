<?php
include 'Player.php';
include 'Pair.php';
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
	$_SESSION['lastRound'] = array();
	// foreach ($_SESSION['playerArray'] as $player) {
	// $_SESSION['lastRound'][] = clone $player;
	// //Lägg till i tills-vidare-databasen
	// //1.
	// }
	header('Content-type: application/json');
	$playerIds = explode(',', $_REQUEST['ids']);
	$playerIdsImploded = implode(',', $playerIds);
	$courtsSQL = sprintf("SELECT setting FROM `user-settings` WHERE `user` = '%s' AND `option` = 'courts'", $_SESSION['username']);
	$courts = $mysqli -> query($courtsSQL);
	while ($court = $courts -> fetch_row()) {
		$_SESSION['courts'] = $court[0];
	}
	$_SESSION['playerArray'] = array();
	$playerArrayStats = $_SESSION['playerArray'];
	$getPlayingSQL = sprintf("SELECT playerid FROM `playerlist-session-play` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$getPlaying = $mysqli -> query($getPlayingSQL);
	$getRestingSQL = sprintf("SELECT playerid FROM `playerlist-session-rest` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$getResting = $mysqli -> query($getRestingSQL);
	$getStatsSQL = sprintf("SELECT * FROM `players-sessions` WHERE session='%s' AND user='%s'", $_SESSION['sessID'], $_SESSION['username']);
	$getStats = $mysqli -> query($getStatsSQL);
	$playerStats = array();
	while ($playerStat = $getStats -> fetch_row()) {
		$playerStats[] = $playerStat;
	}
	$playListIDArray = array();
	while ($playListID = $getPlaying -> fetch_row()) {
		$playListIDArray[] = $playListID[0];

	}
	$restListIDArray = array();
	while ($restListID = $getResting -> fetch_row()) {
		$restListIDArray[] = $restListID[0];
	}
	$allMatchIDs = array_merge($playListIDArray, $restListIDArray);
	foreach ($allMatchIDs as $addToPArray) {

		$allPlayersSQL = sprintf("SELECT * FROM players WHERE id = '%s'", $addToPArray);
		$allPlayers = $mysqli -> query($allPlayersSQL);
		while ($playerFromDB = $allPlayers -> fetch_row()) {
			$newPlayer = new Player($playerFromDB[0], $playerFromDB[1], $playerFromDB[2], $playerFromDB[3]);
			foreach ($playerStats as $pStat) {
				if ($pStat[0] == $playerFromDB[0]) {
					$newPlayer -> rest = (int)$pStat[1];
					$newPlayer -> rested_last = (int)$pStat[2];
					$newPlayer -> same_sex = (int)$pStat[3];
					$newPlayer -> history = unserialize($pStat[4]);
				}
			}
			$getBNSQL = sprintf("SELECT boardnumber FROM `players-boardnumbers` WHERE session='%s' AND user='%s' AND playerid='%s'", $_SESSION['sessID'], $_SESSION['username'], $playerFromDB[0]);
			$getBN = $mysqli -> query($getBNSQL);
			$BN = $getBN -> fetch_row();
			$newPlayer -> boardNumber = $BN[0];
			$_SESSION['playerArray'][] = $newPlayer;
			//Läs in alla från sessionen till playerarray
		}
	}
	if ($_REQUEST['prioRest'] == 'false')
		$prioRest = false;
	else
		$prioRest = true;

	if (!isset($_SESSION['matchGames'])) {
		$_SESSION['matchGames'] = array();
	}

	$returnResters = array();
	foreach ((array)$restListIDArray as $restID) {
		foreach($_SESSION['playerArray'] as $player){
			if($player->id == $restID){
				$player->rest++;
				$player->rested_last++;
				$returnResters[] = $player;
			}
		}
	}

	$_SESSION['chosenRests'] = $restingIds;

	$playerArrayStats = $_SESSION['playerArray'];
	$query = sprintf("SELECT * FROM players WHERE id IN (" . $playerIdsImploded . ") AND user = '%s'", $_SESSION['username']);
	// $thisRound = selectFromDB($query, $playerArrayStats);
	$thisRound = array();
	foreach ($playListIDArray as $playingID) {
		foreach ($_SESSION['playerArray'] as $player) {
			if ($player -> id == $playingID) {
				$thisRound[] = $player;
			}
		}
	}
	$courts = $_SESSION['courts'];
	$restingThisRound = array();


	//
	if ($prioRest)
		$restersRemoved = doRestPrioRest($thisRound, $courts);
	else
		$restersRemoved = doRest(getPlayersOfSex($thisRound, "F"), getPlayersOfSex($thisRound, "M"), $thisRound, $courts);
	$pairings = doPairs($restersRemoved, $prioRest);
	$matcherna = matchPrepping($pairings, $prioRest);
	$_SESSION['playerArray'] = $playerArrayStats;
	$resultat[] = $matcherna;
	foreach ($returnResters as $rR) {
		$restingThisRound[] = $rR;
	}
	$resultat[] = $restingThisRound;
	$_SESSION['matchGames'] = $resultat;

	//Uppdatera players-unreported + players-rests-unreported + game-statistics-unreported

	function updateDatabase($resultat, $chosenRests, $roundNum, $playID, $winners, $user, $playerArray) {
		global $mysqli;
		$matchCount = 0;
		foreach ((array)$resultat[0] as $matchNum => $match) {
			$gamestat = sprintf("INSERT INTO `game_statistics-unreported` (session, round, game,player1,player2,player3,player4,winner1,winner2, period,user)
        VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $_SESSION['sessID'], $roundNum, $matchNum, $match["match"][0]["par1"] -> player1 -> id, $match["match"][0]["par1"] -> player2 -> id, $match["match"][0]["par2"] -> player1 -> id, $match["match"][0]["par2"] -> player2 -> id, 0, 0, $_SESSION['period'], $user);
			$mysqli -> query($gamestat);
			$matchCount = $matchCount + 2;
		}
		$restIds = array();
		foreach ($resultat[1] as $resters) {
			$insert = true;
			foreach ((array)$chosenRests as $chR) {
				if ($chR->id == $resters -> id)
					$insert = false;
			}
			if ($insert) {
				$vars = array();
				$vars[] = $resters -> getId();
				$vars[] = 'N';
				$restIds[] = $vars;
			}
		}

		foreach ((array)$chosenRests as $chR) {
			$varst = array();
			$varst[] = $chR -> id;
			$varst[] = 'Y';
			$restIds[] = $varst;
		}

		if ($restIds != null) {
			foreach ($restIds as $rID) {
				$rest = sprintf("INSERT INTO `players-rests-unreported` (session, round, rester, chosen, period, user)
            VALUES ('%s', '%s', '%s','%s','%s','%s')", $playID, $roundNum, $rID[0], $rID[1], $_SESSION['period'], $user);
				$mysqli -> query($rest);
			}
		}

		foreach ($playerArray as $key => $player) {
			$sessPlaySQL = sprintf("INSERT INTO `players-sessions-unreported` (id_from_players,rested,boardnumber,rested_last,same_sex,history,session, user)" . "VALUES ('%s','%s','%s','%s','%s','%s','%s','%s')", $player -> id, $player -> rest, $player -> boardNumber, $player -> rested_last, $player -> same_sex, serialize($player -> history), $_SESSION['sessID'], $_SESSION['username']);
			$mysqli -> query($sessPlaySQL);

		}
	}

	updateDatabase($resultat, $returnResters, $_SESSION['roundNum'], $_SESSION['sessID'], 0, $_SESSION['username'], $_SESSION['playerArray']);
	//print_r($resultat);

	$together = $resultat;
	if (isset($_REQUEST['chRest'])) {
		foreach ($_REQUEST['chRest'] as $chr) {
			$together[1][] = $chr;
		}
	}

	$_SESSION['savedMatches'] = $together;
	$matchUt = json_encode($resultat);
	$_SESSION['canReport'] = true;
	echo $matchUt;
} else {
	echo 'You are not authorized to access this page, please login. <br/>';
	header('Location: login.php?error=1');
	exit();
	die();
}

function selectFromDB($query, &$playerArray) {
	include ('DB.php');
	$result = $mysqli -> query($query);
	while ($row = $result -> fetch_row()) {
		$playerExists = false;
		foreach ((array)$playerArray as $player) {
			if ($player -> getId() == $row[0]) {
				$playerExists = true;
				$thisRound[] = $player;
			}
		}
		if (!$playerExists) {
			$playerArray[] = new Player($row[0], $row[1], $row[2], $row[3], $row[4]);
			$thisRound[] = end($playerArray);
			//var_Dump("fel!");
		}
	}
	$result -> close();

	$mysqli -> close();
	return $thisRound;
}

function splitSameSex($pairArray, $prioRest) {
	$sameSexPairs = array();
	$mixedPairs = array();

	foreach ((array)$pairArray as $pair) {
		if ($pair -> player1 -> gender == $pair -> player2 -> gender) {
			$sameSexPairs[] = $pair;
		} else {
			if (!($pair == null))
				$mixedPairs[] = $pair;
		}
	}
	if ($prioRest) {
		if (!(count($mixedPairs) % 2 == 0)) {
			$temp = array_shift($mixedPairs);
			$temp1 = array_shift($sameSexPairs);
			if (!empty($mixedPairs))
				$result[] = $mixedPairs;
			if (count($sameSexPairs) > 0)
				$result[] = $sameSexPairs;
			$tempArray[] = $temp;
			$tempArray[] = $temp1;
			$result[] = $tempArray;
		} else {
			if (!empty($mixedPairs))
				$result[] = $mixedPairs;
			if (!empty($sameSexPairs))
				$result[] = $sameSexPairs;
		}
	} else {
		$result[] = $mixedPairs;
		$result[] = $sameSexPairs;
	}

	return $result;
}

function doMatches($pairs, $prioRest) {

	$matchArray = array();
	$tempPairList = array();
	if ($prioRest) {
		while (!empty($pairs)) {
			$randIndex = rand(0, count($pairs) - 1);
			$randIndex2 = rand(0, count($pairs) - 1);
			while ($randIndex2 == $randIndex) {
				$randIndex2 = rand(0, count($pairs) - 1);
			}
			$tempPairList[] = array("par1" => $pairs[$randIndex], "par2" => $pairs[$randIndex2]);
			unset($pairs[$randIndex]);
			unset($pairs[$randIndex2]);
			$pairs = array_values($pairs);
			$matchArray[] = array("match" => $tempPairList);
			$tempPairList = array();
		}
	} else {
		if (!($pairs[0] == null)) {

			foreach ($pairs as $i => $pair) {
				if (!($pairs[$i] == null)) {
					$tempPairList[] = array("par1" => $pairs[$i], "par2" => $pairs[$i + 1]);
					$matchArray[] = array("match" => $tempPairList);
					$tempPairList = array();
					unset($pairs[$i]);
					unset($pairs[$i + 1]);
				}
			}
		} else {
			return 'wtf';
		}
	}
	return $matchArray;
}

function matchPrepping($pairs, $prioRest) {
	$splittedPairs = splitSameSex($pairs, $prioRest);

	if ($prioRest) {
		foreach ((array)$splittedPairs as $i => $splittedPair) {
			if (!($splittedPair == null)) {
				if (isset($matchArray)) {
					$matchArray = array_merge($matchArray, doMatches($splittedPair, $prioRest));
				} else {
					$matchArray = doMatches($splittedPair, $prioRest);
				}
			}
		}

		//        $matchArray = array_merge($temp1,$temp2 ,$temp3 );
	} else {
		$matchArray = array_merge(doMatches($splittedPairs[0], $prioRest), doMatches($splittedPairs[1], $prioRest));
	}

	return $matchArray;
}

/* ----------------------------------------------------------- */

//Funktioner för att prioritera jämn vila!

/*
 * 1. Beräkna antalet vilande Players - courts*4 om Player < courts*4: Players - Players % 4
 * 2. Om antalet tjejer/killar är ojämt vila en.
 *
 *
 */
function doRestPrioRest($playerArray, $courts) {
	$numberOfMatches = howManyMatchesPrioRest($playerArray, $courts);
	$numberofResters = count($playerArray) - ($numberOfMatches * 4);
	//Lägg till om spelaren vilade förra gången i ekvationen..
	// usort($playerArray, "sortByRest");
	$restSorted = sortByRestAndLast($playerArray, array("rested_last", "rest"));

	if ($numberofResters > 0) {
		$leftToRest = $numberofResters;
		$count = 0;

		$lastPlayer = $restSorted[0] -> rest;
		$lastPlayerLast = $restSorted[0] -> rested_last;
		$restNumbers = array();

		//Fyller på restNumbers med antalet spelare med ett visst antal rests
		foreach ($restSorted as $player) {
			if ($player -> rest == $lastPlayer && $player -> rested_last == $lastPlayerLast) {
				$count++;
			} else {
				$restNumbers[] = $count;
				$lastPlayer = $player -> rest;
				$lastPlayerLast = $player -> rested_last;
				$count = 1;
			}
		}
		$restNumbers[] = $count;
		$n = 0;

		$r = 0;

		while ($leftToRest > 0) {

			if ($restNumbers[$n] <= $leftToRest) {

				//De som inte har chans att lira går direkt till resting

				$restSorted = removeResters($restSorted, $restNumbers[$n], $playerArray, TRUE);
				$leftToRest -= $restNumbers[$n];
			} else {

				//Nu är restsorted upper och lower - dags att dela upp den..
				$upperSlice = array_slice($restSorted, $restNumbers[$n]);
				$lowerSlice = array_slice($restSorted, 0, $restNumbers[$n]);
				$debugVar = TRUE;
				while ((count($lowerSlice) - $leftToRest) > 0 && $debugVar) {
					//jämna ut paren i upperSlice
					//                    if (count(getPlayersOfSex($upperSlice, "M")) % 2 > 0) {
					//                        moveToUpper($lowerSlice, $upperSlice, "M", 1);
					//                    }
					//                    if (count(getPlayersOfSex($upperSlice, "F")) % 2 > 0) {
					//                        moveToUpper($lowerSlice, $upperSlice, "F", 1);
					//                    }
					$r++;
					if (count(getPlayersOfSex($upperSlice, "M")) > count(getPlayersOfSex($upperSlice, "F"))) {
						$sex = "F";
						$sex2 = "M";
					} elseif (count(getPlayersOfSex($upperSlice, "F")) > count(getPlayersOfSex($upperSlice, "M"))) {
						$sex = "M";
						$sex2 = "F";
					} else {
						$sex = "M";
						$sex2 = "F";
					}
					//försök göra lika många par av varje

					if (count($upperSlice) % 4 > 0) {
						while (count($upperSlice) % 4 > 0) {

							while (count(getPlayersOfSex($upperSlice, sex)) < count(getPlayersOfSex($upperSlice, $sex2)) && hasSex($lowerSlice, $sex, 1) && count($upperSlice) % 4 > 0) {
								moveToUpper($lowerSlice, $upperSlice, $sex, 1);
							}
							if (count($upperSlice) % 4 > 0) {
								moveToUpper($lowerSlice, $upperSlice, $sex2, 1);
							}
						}
					} elseif (count(getPlayersOfSex($upperSlice, "M")) != count(getPlayersOfSex($upperSlice, "F")) && hasSex($lowerSlice, $sex, 1)) {
						if (count(getPlayersOfSex($lowerSlice, $sex)) % 2 == 1 && hasSex($lowerSlice, $sex2, 2)) {
							moveToUpper($lowerSlice, $upperSlice, $sex, 1);
						}
						while (hasSex($lowerSlice, $sex, 2) && ((count($lowerSlice) - 2) - $leftToRest) >= 0 && stillSmaller($upperSlice, $sex)) {
							moveToUpper($lowerSlice, $upperSlice, $sex, 2);
						}

						if (stillSmaller($upperSlice, $sex)) {

							while (hasSex($lowerSlice, $sex2, 2) && ((count($lowerSlice) - 2) - $leftToRest) >= 0) {
								moveToUpper($lowerSlice, $upperSlice, $sex2, 2);
							}
							while (hasSex($lowerSlice, $sex, 1) && hasSex($lowerSlice, $sex2, 1) && ((count($lowerSlice) - 2) - $leftToRest) >= 0) {
								moveToUpper($lowerSlice, $upperSlice, $sex, 1);

								moveToUpper($lowerSlice, $upperSlice, $sex2, 1);
							}
							while (hasSex($lowerSlice, $sex, 1) && ((count($lowerSlice) - 1) - $leftToRest) >= 0) {
								moveToUpper($lowerSlice, $upperSlice, $sex, 1);
							}
						}
					} elseif ((count($lowerSlice) - $leftToRest) > 0) {

						$sex = "F";
						$sex2 = "M";

						//Ingen skillnad i upper - plocka 4 åt gången
						if (hasSex($lowerSlice, $sex, 2) && hasSex($lowerSlice, $sex2, 2)) {
							moveToUpper($lowerSlice, $upperSlice, $sex, 2);

							moveToUpper($lowerSlice, $upperSlice, $sex2, 2);
						} elseif (hasSex($lowerSlice, $sex, 4)) {
							moveToUpper($lowerSlice, $upperSlice, $sex, 4);
						} elseif (hasSex($lowerSlice, $sex2, 4)) {
							moveToUpper($lowerSlice, $upperSlice, $sex2, 4);
						} elseif (hasSex($lowerSlice, $sex, 3) && hasSex($lowerSlice, $sex2, 1)) {
							moveToUpper($lowerSlice, $upperSlice, $sex, 3);

							moveToUpper($lowerSlice, $upperSlice, $sex2, 1);
						} elseif (hasSex($lowerSlice, $sex2, 3) && hasSex($lowerSlice, $sex, 1)) {
							moveToUpper($lowerSlice, $upperSlice, $sex2, 3);

							moveToUpper($lowerSlice, $upperSlice, $sex, 1);
						} else {

							moveToUpper($lowerSlice, $upperSlice, $sex, 2);

							moveToUpper($lowerSlice, $upperSlice, $sex2, 2);
						}
					}
					//                    if ($r == 3)
					//                        $debugVar = false;
				}
				$lowerSlice = removeResters($lowerSlice, $leftToRest, $playerArray, TRUE);
				$restSorted = $upperSlice;
				$leftToRest = 0;
			}
			$n++;
		}
	}
	return $restSorted;
}

function stillSmaller($upperSlice, $sex) {

	if (count(getPlayersOfSex($upperSlice, "M")) === count(getPlayersOfSex($upperSlice, "F"))) {

		return FALSE;
	}
	if ($sex == "M") {
		if (count(getPlayersOfSex($upperSlice, "M")) < count(getPlayersOfSex($upperSlice, "F"))) {
			return TRUE;
		} else {

			return FALSE;
		}
	}
	if ($sex == "F") {
		if (count(getPlayersOfSex($upperSlice, "F")) < count(getPlayersOfSex($upperSlice, "M"))) {

			return TRUE;
		} else {

			return FALSE;
		}
	}
}

function hasSex($lowerSlice, $sex, $numAsked) {
	$actualNumber = 0;

	foreach ($lowerSlice as $lower) {
		if ($lower -> gender == $sex) {
			$actualNumber++;
		}
	}
	if ($actualNumber >= $numAsked)
		return TRUE;
	else
		return FALSE;
}

function moveToUpper(&$lowerSlice, &$upperSlice, $sex, $numToMove) {
	$i = 0;
	foreach ($lowerSlice as $j => $lower) {
		if ($lower -> gender == $sex) {
			$upperSlice[] = $lowerSlice[$j];
			unset($lowerSlice[$j]);
			$i++;
			if ($i == ($numToMove))
				break;
		}
	}
}

function howManyMatchesPrioRest($playerArray, $courts) {
	if (count($playerArray) < ($courts * 4)) {
		return (int)(count($playerArray) / 4);
	} else {
		return $courts;
	}
}

/* ------------------------------------------------- */

function howManyMatches($malArray, $femArray, $max) {
	if (count($femArray) > count($malArray)) {
		$workArray = $malArray;
	} else {
		$workArray = $femArray;
	}

	$evenPairs = count($workArray) * 2;

	$remainder = $evenPairs % 4;

	if ($remainder == 0) {
		$numMatches = $evenPairs / 4;
	} else {
		$numMatches = ($evenPairs - 2) / 4;
	}

	if ($numMatches > $max) {
		return $max;
	} else {
		return $numMatches;
	}
}

function removeResters($playBySex, $numResters, $playerArray, $prio = FALSE) {

	if ($prio == false) {
		usort($playBySex, "sortByRest");
	}

	//Ta bort den spelare som ska vila från arrayen och ge den en rest.
	for ($i = 0; $i < $numResters; $i++) {
		$nyRest = array_shift($playBySex);
		$GLOBALS["restingThisRound"][] = $nyRest;

		foreach ($playerArray as $player) {
			if ($player -> getId() == $nyRest -> getId()) {
				$player -> setRest();
				$player -> rested_last++;
			}
		}
	}

	return $playBySex;
}

function doRest($femArray, $malArray, $playerArray, $courts) {
	//Kolla vilket kön som har flest spelare och väljer resters därifrån
	//Jag vet hur många matcher som ska spelas
	//om nån array / 2 är mindre än antal matcher -> alla resters från den andra
	//som så inte är fallet bort med så många från den minsta och resten från den största

	$numberOfMatches = howManyMatches($malArray, $femArray, $courts);

	//$numberofResters = $evenPairs % 4*$numberOfMatches;
	$restingMales = count($malArray) - (2 * $numberOfMatches);
	$restingFemales = count($femArray) - (2 * $numberOfMatches);
	$possibleCourts = $courts - $numberOfMatches;

	if ($numberOfMatches < $courts) {
		if ($restingMales / 4 >= 1) {
			$possSameSex = floor($restingMales / 4);
			$actualSameSex = 4 * min($possibleCourts, $possSameSex);
			$restingMales -= $actualSameSex;
		} else if ($restingFemales / 4 >= 1) {
			$possSameSex = floor($restingFemales / 4);
			$actualSameSex = 4 * min($possibleCourts, $possSameSex);
			$restingFemales -= $actualSameSex;
		}
	}
	$restedMales = removeResters($malArray, $restingMales, $playerArray);
	$restedFemales = removeResters($femArray, $restingFemales, $playerArray);

	$willBePlaying = array_merge($restedMales, $restedFemales);

	//Sortera den mergade arrayen efter hur mycket varje person vilat.
	usort($willBePlaying, "sortByRest");

	return $willBePlaying;
}

function doPairsAux($sex1, $sex2, $playerArray, $numPairs) {
	$i = 0;
	foreach ($sex1 as $sex1Player) {
		$foundMatch = false;
		//if($i>$numPairs){
		//    break;
		//}
		//Går igenom malesen och kollar om paret spelat förut
		foreach ($sex2 as $i => $sex2Player) {
			if (!isset($sex1Player -> history[$sex2[$i] -> id])) {

				//lägg till att de spelat med varandra i historyn
				foreach ($playerArray as $player) {
					if ($player -> getId() == $sex1Player -> getId() && $player -> getId() != null) {
						$player -> history[$sex2[$i] -> getId()] = 1;
					} else if ($player -> getId() == $sex2[$i] -> getId() && $player -> getId() != null) {
						$player -> history[$sex1Player -> getId()] = 1;
					}
				}
				$pairArray[] = new Pair($sex1Player, $sex2[$i]);
				if ($sex1Player -> gender == $sex2[$i] -> gender) {
					$sex1Player -> same_sex++;
					$sex2[$i] -> same_sex++;
				} else {
					$sex1Player -> same_sex = 0;
					$sex2[$i] -> same_sex = 0;
				}
				$foundMatch = true;
				unset($sex2[$i]);
				$i++;
				break;
			}
		}
		if (!$foundMatch) {

			$oneSexOnlyIDs = pruneGender($sex2, $sex1Player -> history, $sex2Player -> gender);

			$temp = array_keys($oneSexOnlyIDs, min($oneSexOnlyIDs));
			$sex1Player -> history[$temp[0]]++;

			//lägg till att de spelat med varandra i historyn
			$sex2Partner = getPlayerFromID($sex2, $temp[0]);
			$sex2Partner -> history[$sex1Player -> getId()]++;
			$pairArray[] = new Pair($sex1Player, $sex2Partner);
			if ($sex1Player -> gender == $sex2Partner -> gender) {
				$sex1Player -> same_sex++;
				$sex2Partner -> same_sex++;
			} else {
				$sex1Player -> same_sex = 0;
				$sex2Partner -> same_sex = 0;
			}
			$sex2 = unsetByID($sex2, $temp[0]);
			$i++;
		}
	}

	//Göra pairs av sex2orna som är kvar
	$returnArray[] = $pairArray;
	$returnArray[] = $sex2;

	return $returnArray;
}

function mixEmUp(&$males, &$females) {
	usort($females, "sortBySameSexAndHistory");
	usort($males, "sortBySameSexAndHistory");

	$restNumMales = mixEmUpAux($males);
	$restNumFemales = mixEmUpAux($females);

	//Tjejerna
	$femMix = array();
	$start = 0;

	for ($i = 0; $i < count($restNumFemales); $i++) {
		$femMix[] = array_slice($females, $start, $restNumFemales[$i]);
		$start = $restNumFemales[$i] + $start;
	}
	for ($j = 0; $j < count($femMix); $j++) {
		shuffle($femMix[$j]);
	}
	$females = array();
	for ($i = 0; $i < count($femMix); $i++) {
		foreach ($femMix[$i] as $fm) {
			$females[] = $fm;
		}
	}

	//Killarna
	$malMix = array();
	$start = 0;

	for ($i = 0; $i < count($restNumMales); $i++) {
		$malMix[] = array_slice($males, $start, $restNumMales[$i]);
		$start = $restNumMales[$i] + $start;
	}

	for ($j = 0; $j < count($malMix); $j++) {
		shuffle($malMix[$j]);
	}
	$males = array();
	for ($i = 0; $i < count($malMix); $i++) {
		foreach ($malMix[$i] as $mm) {
			$males[] = $mm;
		}
	}
}

function mixEmUpAux($players) {
	$count = 0;

	$lastPlayer = count($players[0] -> history);
	$restNumbers = array();

	//Fyller på restNumbers med antalet spelare med ett visst antal rests
	foreach ($players as $player) {
		if (count($player -> history) == $lastPlayer) {
			$count++;
		} else {
			$restNumbers[] = $count;
			$lastPlayer = count($player -> history);
			$count = 1;
		}
	}
	$restNumbers[] = $count;

	return $restNumbers;
}

function doPairs($playerArray, $prioRest) {

	/* --------------------the actual pairing ------------------------- */

	$females = getPlayersOfSex($playerArray, "F");
	$males = getPlayersOfSex($playerArray, "M");
	mixEmUp($males, $females);

	if (!$prioRest) {

	} else {
		if (count($females) < count($males)) {
			$pairArray = doPairsAux($females, $males, $playerArray, count($playerArray));
		} else {
			$pairArray = doPairsAux($males, $females, $playerArray, count($playerArray));
		}

		if (count($pairArray[1]) > 0) {
			if (count($pairArray[1]) < 3) {
				$oneSexArray = array_values($pairArray[1]);

				$pairArray2 = array();
				$pairArray2[] = new Pair($oneSexArray[0], $oneSexArray[1]);
				if (count($pairArray[0]) > 0) {
					$pairArray = array_merge($pairArray[0], $pairArray2);
				} else {

					$pairArray = $pairArray2;
				}
			} else {
				$sex2 = array_slice($pairArray[1], (count($pairArray[1]) / 2));
				$sex1 = array_slice($pairArray[1], 0, (count($pairArray[1]) / 2));
				$pairArray2 = doPairsAux($sex1, $sex2, $playerArray, count($playerArray));

				if (count($pairArray[0]) > 0) {
					$pairArray = array_merge($pairArray[0], $pairArray2[0]);
				} else {

					$pairArray = $pairArray2[0];
				}
			}
		} else {
			$pairArray = $pairArray[0];
		}
	}
	return $pairArray;
}

function howManyPairs($males, $females) {
	$mixedPairs;
	$sameSexPairs;

	if (count($females) > count($males)) {
		$largeArray = $males;
		$smallArray = $females;
	} else {
		$largeArray = $females;
		$smallArray = $males;
	}

	$pairAmount[] = count($smallArray);
	$pairAmount[] = (count($largeArray) - count($smallArray)) / 2;

	return $pairAmount;
}

function unsetByID($playerArray, $playerID) {
	foreach ($playerArray as $i => $player) {
		if ($player -> getID() == $playerID) {
			unset($playerArray[$i]);
		}
	}
	return $playerArray;
}

function getPlayerFromID($playerArray, $playerID) {
	foreach ($playerArray as $player) {
		if ($player -> getID() == $playerID) {
			return $player;
		}
	}
}

function pruneGender($playerArray, $playerIds, $gender) {
	$result = array();

	foreach ($playerIds as $id => &$timesPlayed) {
		foreach ($playerArray as $player) {
			if ($player -> getId() == $id) {
				if ($player -> getGender() == $gender) {
					$result[$id] = $timesPlayed;
				}
			}
		}
	}
	return $result;
}

function getPlayersOfSex($playerArray, $sex) {

	$returnArray = array();
	foreach ($playerArray as $player) {
		if ($player -> getGender() == $sex) {
			$returnArray[] = $player;
		}
	}
	return $returnArray;
}

function sortByRest($p1, $p2) {
	if ($p1 -> getRest() < $p2 -> getRest()) {
		return -1;
	} else if ($p1 -> getRest() > $p2 -> getRest()) {
		return 1;
	} else {
		return 0;
	}
}

function sortByRestAndLast($restSorted, $props) {
	usort($restSorted, function($a, $b) use ($props) {
		if ($a -> $props[0] == $b -> $props[0])
			return $a -> $props[1] < $b -> $props[1] ? -1 : 1;
		return $a -> $props[0] < $b -> $props[0] ? -1 : 1;
	});
	return $restSorted;
}

function sortByHistory($p1, $p2) {
	if (count($p1 -> history) < count($p2 -> history)) {
		return 1;
	} else if (count($p1 -> history) > count($p2 -> history)) {
		return -1;
	} else if (count($p1 -> history) > count($p2 -> history)) {
		return 1;
	} else if (array_sum($p1 -> history) < array_sum($p2 -> history)) {
		return -1;
	} else {
		return 0;
	}
}

function sortBySameSexAndHistory($p1, $p2) {
	if ($p1 -> same_sex == $p2 -> same_sex) {
		if (count($p1 -> history) < count($p2 -> history)) {
			return 1;
		} else if (count($p1 -> history) > count($p2 -> history)) {
			return -1;
		} else if (count($p1 -> history) > count($p2 -> history)) {
			return 1;
		} else if (array_sum($p1 -> history) < array_sum($p2 -> history)) {
			return -1;
		}
	}
	return $p1 -> same_sex < $p2 -> same_sex ? 1 : -1;
}
?>