<?php
include('Player.php');
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (isset($_POST['username'], $_POST['password'])) {
    $email = $_POST['username'];
    $password = $_POST['password']; // The hashed password.
    if (login($email, $password, $mysqli) == true) {
        echo "success";
    } else {
        echo "failure";
    }
} else {
    // The correct POST variables were not sent to this page.
    echo 'Invalid Request';
}
?>