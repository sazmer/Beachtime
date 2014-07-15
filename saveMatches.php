<?php

include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
    $together = $_REQUEST['saveMatches'];
    if($_REQUEST['chRest'] != null){
    foreach($_REQUEST['chRest'] as $chr){
        $together[1][] = $chr;
    }
    }
    $_SESSION['savedMatches'] = $together;
    echo json_encode($together);
} else {
    echo 'You are not authorized to access this page, please login. <br/>';
    header('Location: login.php?error=1');
    exit();
    die();
}
?>
