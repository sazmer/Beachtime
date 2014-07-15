$pairsForPlayer = getPairsFromPlayer($smaller[$n]);
					
					//kolla vilka spelare som redan har parats denna match och ta bort ur pairsForPlayer
					for ($i=0; $i<count($thisMatch);$i++){
						for ($j=0; $j<count($pairsForPlayer);$j++){
							if (($thisMatch[$i]->getPlayer1() == $pairsForPlayer[$j]->getPlayer1())||($thisMatch[$i]->getPlayer1() == $pairsForPlayer[$j]->getPlayer2())||($thisMatch[$i]->getPlayer2() == $pairsForPlayer[$j]->getPlayer1())||($thisMatch[$i]->getPlayer1() == $pairsForPlayer[$j]->getPlayer2())) {
								unset($pairsForPlayer[$j]);
								$pairsForPlayer = array_values($pairsForPlayer);
							
							}
						}
					}
					
					$min = $pairsForPlayer[0]->getTimesPlayed();
					$minPair = $pairsForPlayer[0];
					
					for ($i = 0; $i<count($pairsForPlayer); $i++){ //hittar lägsta timesplayed för spelarens par
						if($min > $pairsForPlayer[$i]->getTimesPlayed()){
							$minPair = $pairsForPlayer[$i];
							$min = $pairsForPlayer[$i]->getTimesPlayed();
						}
					}
					
					$thisMatch[] = $minPair;

					for ($i=0; $i<count($pairArray);$i++){ //kollar spelare i paret och ökar deras timesplayed
						if(($pairArray[$i]->getPlayer1() == $minPair->getPlayer1() && $pairArray[$i]->getPlayer2() == $minPair->getPlayer2()) || ($pairArray[$i]->getPlayer2() == $minPair->getPlayer1() && $pairArray[$i]->getPlayer1() == $minPair->getPlayer2())){
						
							$pairArray[$i]->incPlayed();
						
						}
					}
				//	$minPair->incPlayed();
					unset($bigger[$randIndex]);
					$bigger = array_values($bigger);
					
					
					
					
					
					
					
					
					
					
					
					
					gammal idé om  andra omgången - innan stable wife:
					
					/*
	for($m = 0; $m<count($andraOmgangen);$m++){
		$pairsForPlayer = getPairsFromPlayer($andraOmgangen[$m]);
					
					//kolla vilka spelare som redan har parats denna match och ta bort ur pairsForPlayer
					for ($i=0; $i<count($thisMatch);$i++){
						for ($j=0; $j<count($pairsForPlayer);$j++){
							if (($thisMatch[$i]->getPlayer1() == $pairsForPlayer[$j]->getPlayer1())||($thisMatch[$i]->getPlayer1() == $pairsForPlayer[$j]->getPlayer2())||($thisMatch[$i]->getPlayer2() == $pairsForPlayer[$j]->getPlayer1())||($thisMatch[$i]->getPlayer1() == $pairsForPlayer[$j]->getPlayer2())) {
								unset($pairsForPlayer[$j]);
								$pairsForPlayer = array_values($pairsForPlayer);
							
							}
						}
					}
					
					$min = $pairsForPlayer[0]->getTimesPlayed();
					$minPair = $pairsForPlayer[0];
					
					for ($i = 0; $i<count($pairsForPlayer); $i++){ //hittar lägsta timesplayed för spelarens par
						if($min > $pairsForPlayer[$i]->getTimesPlayed()){
							$minPair = $pairsForPlayer[$i];
							$min = $pairsForPlayer[$i]->getTimesPlayed();
						}
					}
					
					$thisMatch[] = $minPair;

					for ($i=0; $i<count($pairArray);$i++){ //kollar spelare i paret och ökar deras timesplayed
						if(($pairArray[$i]->getPlayer1() == $minPair->getPlayer1() && $pairArray[$i]->getPlayer2() == $minPair->getPlayer2()) || ($pairArray[$i]->getPlayer2() == $minPair->getPlayer1() && $pairArray[$i]->getPlayer1() == $minPair->getPlayer2())){
						
							$pairArray[$i]->incPlayed();
						
						}
					}
				//	$minPair->incPlayed();
					unset($bigger[$randIndex]);
					$bigger = array_values($bigger);
	
	
	
	}
	
	
	*/
	
	
			$done=false;
			while(!empty($tempBigger2) && !$done){
				
				if(pairExists($tempSmaller[$n], $tempBigger[$randIndex])){
					unset($tempBigger2[$randIndex]);
					$tempBigger2 = array_values($tempBigger2);
					$randIndex = rand(0,count($tempBigger2)-1);
				}else{$done=true;}
			}
			
			
			------------------------hela skiten-----------------------
			$tempBigger2 = $bigger;
		while(!empty($tempSmaller)){
			$randIndex = rand(0,count($tempBigger)-1);
	
			//kolla om den här har några som den aldrig spelat med
			if(!pairExists($tempSmaller[$n], $tempBigger[$randIndex])){ //paret finns inte
				$numPairsThisMatch = getPairsFromPlayerThisMatch($tempBigger[$randIndex],$thisMatch); //Är bigger förlovad?
				if (empty($numPairsThisMatch)){ //om bigger inte var förlovad med någon
					$thePair = new Pair($tempSmaller[$n],$tempBigger[$randIndex]);
					$thisMatch[] = $thePair; //bigger är nu förlovad med smaller
					unset($tempSmaller[$n]);
					$tempSmaller = array_values($tempSmaller); //ta bort smaller som friare
				}
				else{ //om bigger var förlovad
					//lägg till det nya paret och jämför det med biggers gamla par
					
					for($o = 0;$o<count($numPairsThisMatch);$o++){ 
						//numparsthismatch borde bara ha en post - om den nya är bättre ska vi byta
						if($numPairsThisMatch[$o]->getTimesPlayed() > 1){
							//hitta vilken post i thismatch som ska tas bort och lägg till det nya istället
							for($p = 0;$p<count($thisMatch);$p++){ 
								
								if (($thisMatch[$p]->getPlayer1() == $tempBigger[$randIndex]) || ($thisMatch[$p]->getPlayer2() == $tempBigger[$randIndex])) {
									unset($thisMatch[$p]);
									$thisMatch = array_values($thisMatch);
									$thePair = new Pair($tempSmaller[$n],$tempBigger[$randIndex]);
									$thisMatch[] = $thePair; //bigger är nu förlovad med smaller
									unset($tempSmaller[$n]);//ta bort den här friaren
									//lägg tillbaka den nu inte längre upptagne friaren
									
									if(!($thisMatch[$p]->getPlayer1() == $tempBigger)){
										$tempSmaller[] = $thisMatch[$p]->getPlayer1();
									}else{
										$tempSmaller[] = $thisMatch[$p]->getPlayer2();
									}
								}
							}
					
						}
						/*else{
							$tempSmaller[] = $tempSmaller[$n];
						}*/
					
					}
				}
			}else{ //paret fanns i pairArray
				$numPairsThisMatch = getPairsFromPlayerThisMatch($tempBigger[$randIndex],$thisMatch); //Är bigger förlovad?
				if (empty($numPairsThisMatch)){ //om bigger inte var förlovad med någon
					$x = count($pairArray);
					for($q = 0;$q < $x ;$q++){ 
						if ((($pairArray[$q]->getPlayer1() == $tempBigger[$randIndex]) || ($pairArray[$q]->getPlayer2() == $tempBigger[$randIndex]))&&(($pairArray[$q]->getPlayer1() == $tempSmaller[$n]) || ($pairArray[$q]->getPlayer2() == $tempSmaller[$n]))) {
						
							$thisMatch[] = $pairArray[$q]; //lägg till som förlovade
							unset($tempSmaller[$n]);
							$tempSmaller = array_values($tempSmaller); //ta bort smaller som friare
							
						}
					}
				}
				else{ //om bigger var förlovad
					//lägg till det nya paret och jämför det med biggers gamla par
				
					for($o = 0;$o<count($numPairsThisMatch);$o++){ 
						//numparsthismatch borde bara ha en post - om den nya är bättre ska vi byta
						$thisPairing;
						
						for($q = 0;$q<count($pairArray);$q++){ 
								if ((($pairArray[$q]->getPlayer1() == $tempBigger[$randIndex]) || ($pairArray[$q]->getPlayer2() == $tempBigger[$randIndex]))&&(($pairArray[$q]->getPlayer1() == $tempSmaller[$n]) || ($pairArray[$q]->getPlayer2() == $tempSmaller[$n]))) {
								
									$thisPairing = $pairArray[$q];
									
								}
						}
						
						if($numPairsThisMatch[$o]->getTimesPlayed() > $thisPairing->getTimesPlayed()){
							//hitta vilken post i thismatch som ska tas bort och lägg till det nya istället
							for($p = 0;$p<count($thisMatch);$p++){ 
								if (($thisMatch[$p]->getPlayer1() == $tempBigger[$randIndex]) || ($thisMatch[$p]->getPlayer2() == $tempBigger[$randIndex])) {
									
									$thisMatch[] = $thisPairing;
									unset($tempSmaller[$n]);
									$tempSmaller = array_values($tempSmaller); //ta bort smaller som friare
									//lägg tillbaka den nu inte längre upptagne friaren
									if(!($thisMatch[$p]->getPlayer1() == $tempBigger)){
										$tempSmaller[] = $thisMatch[$p]->getPlayer1();
									}else{
										$tempSmaller[] = $thisMatch[$p]->getPlayer2();
									}
									unset($thisMatch[$p]);
									$thisMatch = array_values($thisMatch);
								}
							}
						}
					}
				}
			}
				/*unset($tempSmaller[$n]);
				$tempSmaller = array_values($tempSmaller);
				
			
			*/
		
			}	
	}