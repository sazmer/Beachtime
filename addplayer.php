<?php
include('loginfunctions.php');
sec_session_start();
include('DB.php');

$fname = $_REQUEST['fname'];
$lname = $_REQUEST['lname'];
$sex = $_REQUEST['sex'];
//period

$checkNameQuery = sprintf("SELECT * FROM players WHERE first_name='%s' AND last_name='%s' AND user='%s'", $fname, $lname, $_SESSION['username']);
$checkResult = $mysqli->query($checkNameQuery);

if ($checkResult->num_rows > 0) {
    echo "fail";
} else {
    $query = sprintf("INSERT INTO players (`first_name`, `last_name`, `sex`, `user`) "
            . "VALUES ('%s','%s','%s','%s')",$fname,$lname,$sex,$_SESSION['username']);
    $result = $mysqli->query($query);
    echo $mysqli->insert_id;
}
?>