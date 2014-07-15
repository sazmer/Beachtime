<?php
include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
    $timeOut = 4*3600;
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeOut)) {
        // last request was more than 30 minutes ago
        session_unset();     // unset $_SESSION variable for the run-time 
        session_destroy();   // destroy session data in storage
        echo json_encode(0);
    } else {
        echo json_encode(1);
    }
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
} else {
    echo json_encode(10);
}
?>