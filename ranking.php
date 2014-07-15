<?php
session_start();
?>
<html>
    <head>
        <title>Ranking</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="favicon.ico" >
        <link href="css/style.css" rel="stylesheet" type="text/css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <script src="js/main.js"></script>
         <script type="text/javascript" src="js/TableSorter/jquery.tablesorter.js"></script> 
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
                    include 'Player.php';
                    if ($_GET['location'] == 'Uppsala')
                        $_SESSION['username'] = 'kristiina';
                    $query = "SELECT * FROM players WHERE member = 'Y'";
                    include('DB.php');
                    $result = $mysqli->query($query);


                    $playerArrayStats = array();
                    while ($row = $result->fetch_row()) {
                        $playerArrayStats[] = new Player($row[0], $row[1], $row[2], $row[3], $row[4], $row[6]);
                    }

                    //  usort($playerArrayStats, "orderByWins");
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