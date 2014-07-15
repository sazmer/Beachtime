<?php
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
     if($_SESSION['canReport'] == false){
       echo 'false';
   }else{
       echo 'true';
   }
} else {
    echo 'You are not authorized to access this page, please login. <br/>';
    header('Location: login.php?error=1');
    exit();
    die();
}
?>
