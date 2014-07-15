<?php
include 'Player.php';
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
    
} else {
    echo 'You are not authorized to access this page, please login. <br/>';
    header('Location: login.php?error=1');
    exit();
    die();
}
?>

<html>
    <head>
        <title>Valkommen till Fyrisbeach</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="favicon.ico" >

        <link href="css/style.css" rel="stylesheet" type="text/css">


        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <script type="text/javascript" src="js/TableSorter/jquery.tablesorter.js"></script> 

        <script src="js/main.js"></script>
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
                <th>Vinster idag</th>
                <th>Vilade</th>
                <th>Vilat i rad</th>
                <th>Totalt spelade</th>
                   <th>Samma-kön-i-rad</th>

                </thead>
                <tbody>
                    <?php
                    if (!empty($_SESSION['playerArray'])) {
                        foreach ($_SESSION['playerArray'] as $player) {

                            $playerArrayStats[] = $player;
                        }
                        usort($playerArrayStats, "orderByWins");



                        foreach ($playerArrayStats as $player) {

                            echo "<tr>" .
                            "<td>$player->firstname " .
                            " $player->lastname </td>" .
                            "<td>$player->todays_wins</td>" .
                            "<td>$player->rest</td>" .
                            "<td>$player->rested_last</td>" .
                            "<td>$player->played_games</td>" .
                            "<td>$player->same_sex</td>" .
                            "</tr>";
                        }
                    } else {
                        echo 'Inga registrerade vinster';
                    }

                    function orderByWins($p1, $p2) {
                        if ($p1->todays_wins < $p2->todays_wins) {
                            return 1;
                        } else if ($p1->todays_wins > $p2->todays_wins) {
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