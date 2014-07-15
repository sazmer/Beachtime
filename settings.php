<?php

include('DB.php');
include('loginfunctions.php');
sec_session_start();
if (login_check($mysqli) == true) {
    if ($_REQUEST['getset'] == 'set') {
        $option = $_REQUEST['options'];
        $setting = $_REQUEST['settings'];

        foreach ($option as $key => $op) {

            $setSQL = sprintf("INSERT INTO `user-settings` (`user`,`option`,`setting`)"
                    . "VALUES ('%s','%s', '%s')  ON DUPLICATE KEY UPDATE setting = %s", $_SESSION['username'], $op, $setting[$key], $setting[$key]);
            $mysqli->query($setSQL);
        }

        $answer = sprintf("Settings changed!");
        echo json_encode($answer);
    } else if ($_REQUEST['getset'] == 'get') {

        if (isset($_REQUEST['specific'])) {
            $getSQLspec = sprintf("SELECT `setting` FROM `user-settings` WHERE `user` = '%s' AND `option` = '%s'", $_SESSION['username'], $_REQUEST['specific']);
            $gottenSettingSpec = $mysqli->query($getSQLspec);
            $outVal = $gottenSettingSpec->fetch_row();
            echo json_encode($outVal);
        } else if (isset($_REQUEST['courtRound'])) {
            $getSQLspec = sprintf("SELECT `setting` FROM `user-settings` WHERE `user` = '%s' AND `option` = 'courts'", $_SESSION['username']);
            $gottenSettingSpec = $mysqli->query($getSQLspec);
            $toOut = $gottenSettingSpec->fetch_row();
            $outVal[] = $toOut[0];
            $outVal[] = $_SESSION['roundNum'];
            echo json_encode($outVal);
        } else {

            $getSQL = sprintf("SELECT `option`, `setting` FROM `user-settings` WHERE user = '%s'", $_SESSION['username']);
            $gottenSetting = $mysqli->query($getSQL);

            $outArr = array();
            while ($resp = $gottenSetting->fetch_row()) {
                $outArr[] = $resp;
            }
            echo json_encode($outArr);
        }
    }
}
?>