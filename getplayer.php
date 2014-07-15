<?php
include('loginfunctions.php');
sec_session_start();
include 'DB.php';
if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $query = sprintf("SELECT * FROM players WHERE id='%s' AND user = '%s'", mysql_real_escape_string($id), $_SESSION['username']);
    $result = mysql_query($query);
    $array = mysql_fetch_row($result);
    echo json_encode($array);
} else {
    $query = sprintf("SELECT * FROM players WHERE user = '%s'", $_SESSION['username']);
    $playerList = $mysqli->query($query);
    $playerArrayExport = array();
    for ($i = 0; $row = mysqli_fetch_array($playerList, MYSQLI_NUM); $i++) {
        $playerArrayExport[] = $row;
    }

    function sortByFname($p1, $p2) {

        $al = strtolower($p1[1]);
        $bl = strtolower($p2[1]);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

    usort($playerArrayExport, "sortByFname");
    echo json_encode($playerArrayExport);
}
?>