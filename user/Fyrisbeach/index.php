<?php
include('../../Player.php');
include('../../DB.php');
include('../../loginfunctions.php');
sec_session_start();
?>
<html>
    <head>
        <title>Ranking</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <link href="../../css/style.css" rel="stylesheet" type="text/css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>

        <script type="text/javascript" src="../../js/TableSorter/jquery.tablesorter.js"></script> 
        <script language="JavaScript">
<!--
            $(document).ready(function() {
                $("#winTableTable").tablesorter();
            });
//-->
        </script>
    </head>

    <body>
        <div id="winTable">  
            <table id="winTableTable" class="tablesorter">
                <thead>
                <th>Spelare</th>
                <th>Vinster</th>
                <th>Ratio</th>
                </thead>
                <tbody>
                    <?php
//    $query = sprintf("SELECT * FROM players WHERE period = '%s'", $_SESSION['period']);
                    $query = sprintf("SELECT * FROM players");
                    $result = $mysqli->query($query);
//        $query2 = sprintf("SELECT * FROM `players-periods` WHERE period = '%s' AND user = '%s'", $_SESSION['period'], $_SESSION['username']);
//        $result2 = $mysqli->query($query2);
                    $gameStatsSQL = sprintf("SELECT * FROM game_statistics WHERE period = '%s' AND user = '%s'", "2014", "Fyrisbeach");
                    $statResults = $mysqli->query($gameStatsSQL);
                    $playedGamesIDS = array();
                    $wonGamesIDS = array();
                    while ($statsGot = $statResults->fetch_row()) {
                        $playedGamesIDS[] = $statsGot[4];
                        $playedGamesIDS[] = $statsGot[6];
                        $playedGamesIDS[] = $statsGot[8];
                        $playedGamesIDS[] = $statsGot[10];
                        $wonGamesIDS[] = $statsGot[12];
                        $wonGamesIDS[] = $statsGot[13];
                    }
                    $playedGamesCounted = array_count_values($playedGamesIDS);
                    $wonGamesCounted = array_count_values($wonGamesIDS);
                    while ($allPlayers = $result->fetch_row()) {
                        foreach ($playedGamesCounted as $playID => $PGC) {
                            if ($playID == $allPlayers[0]) {
                                $newPlayer = new Player($allPlayers[0], $allPlayers[1], $allPlayers[2], $allPlayers[3]);
                                if ($wonGamesCounted[$playID] != null) {
                                    $newPlayer->wins = $wonGamesCounted[$playID];
                                } else {
                                    $newPlayer->wins = 0;
                                }
                                $newPlayer->played_games = $PGC;
                                $playerArrayStats[] = $newPlayer;
                            }
                        }
                    }

//                    $selectPeriod = "SELECT * FROM `players-periods` WHERE user='Fyrisbeach'";
//                    $periodPlayers = $mysqli->query($selectPeriod);
//                  
//
//                    $playerArrayStats = array();
//                    while ($pPlayer = $periodPlayers->fetch_row()) {
//                        $selectName = sprintf("SELECT * FROM players WHERE id='%s'", $pPlayer[0]);
//                        $playerName = $mysqli->query($selectName);
//                          while ($name = $playerName->fetch_row()) {
////                          var_dump($pPlayer);
////                        var_dump($name);
//                              $playerArrayStats[] = new Player( $pPlayer[0], $name[1], $name[2], $name[3], $pPlayer[1], $pPlayer[2]);
//                          }
//           
//                    }

                    usort($playerArrayStats, "orderByWins");
                    $winRatio = 0;
                    foreach ($playerArrayStats as $player) {
                        if ($player->played_games > 0) {
                            $winRatio = round(($player->wins / $player->played_games) * 100);
                            $player->winratio = $winRatio;
                        }
                    }
                    sortByWinsAndPercent($playerArrayStats, array("wins", "winratio"));
                    foreach ($playerArrayStats as $player) {
                        echo "<tr>" .
                        "<td>$player->firstname " .
                        " $player->lastname </td>" .
                        "<td>$player->wins</td>" .
                        "<td>$player->winratio %</td>" .
                        "</tr>";
                    }

                    function sortByWinsAndPercent(&$playerArrayStats, $props) {
                        usort($playerArrayStats, function($a, $b) use ($props) {
                            if ($a->$props[0] == $b->$props[0])
                                return $a->$props[1] < $b->$props[1] ? 1 : -1;
                            return $a->$props[0] < $b->$props[0] ? 1 : -1;
                        });
                    }

                    function orderByWins($p1, $p2) {
                        if ($p1->wins < $p2->wins) {
                            return 1;
                        } else if ($p1->wins > $p2->wins) {
                            return -1;
                        } else {
                            return 0;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>



