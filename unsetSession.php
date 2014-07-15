<?php

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

session_unset();
session_destroy();
?>
