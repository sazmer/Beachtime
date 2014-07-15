<?php
include('DB.php');
//period?
$query = sprintf("SELECT * FROM players");
$result = $mysqli->query($query);
$arrayToPrint = array();
while ($row = $result->fetch_row()) {
    $playerA = array();
    $playerA [] = $row[0];
    $playerA [] = $row[1];
    $playerA [] = $row[2];
    $arrayToPrint [] = $playerA;
}
usort($arrayToPrint, "sortByFname");

function sortByFname($p1, $p2) {
    if ($p1[1] > $p2[1]) {
        return 1;
    } else if ($p1[1] < $p2[1]) {
        return -1;
    } else {
        return 0;
    }
}

//foreach ($arrayToPrint as $playerB) {
//    echo '<option value="' . $playerB[0] . '">' . $playerB[1] . " " . $playerB[2] . '</option>';
//}
echo json_encode($arrayToPrint);
?>