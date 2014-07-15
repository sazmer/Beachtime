<?php
include ('DB.php');
include ('loginfunctions.php');
sec_session_start();
if (isset($_GET['email']) && preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $_GET['email'])) {
	$email = $_GET['email'];
}
if (isset($_GET['key']) && (strlen($_GET['key']) == 32))
//The Activation key will always be 32 since it is MD5 Hash
{
	$key = $_GET['key'];
}

if (isset($email) && isset($key)) {

	// Update the database to set the "activation" field to null
	$select_user = sprintf("SELECT username FROM `users` WHERE email='%s'", $email);
	$stmt = $mysqli->prepare($select_user);
	$stmt->execute();
	$stmt->bind_result($user);
	$stmt->fetch();
		$stmt->close();
	$query_activate_account = "UPDATE users SET Activation=NULL WHERE(Email ='$email' AND Activation='$key')LIMIT 1";
	$result_activate_account = mysqli_query($mysqli, $query_activate_account);

	// Print a customized message:
	if (mysqli_affected_rows($mysqli) == 1)//if update query was successfull
	{
		$query_create_settings1 = sprintf("INSERT INTO `user-settings`(`user`, `option`, `setting`) VALUES('%s','courts','1')", $user);
		$query_create_settings2 = sprintf("INSERT INTO `user-settings`(`user`, `option`, `setting`) VALUES('%s','timeSlide','10')", $user);
		$mysqli -> query($query_create_settings1);
		$mysqli -> query($query_create_settings2);
		echo '<div><h2>Congratulations</h2>Your account is now active. You may <a href="index.php?activate=1">Log in</a>.</div>';

	} else {
		echo '<div>Oops !Your account could not be activated. Please recheck the link or contact the <a href="mailto:webmaster@beachtime.se">system administrator.</a></div>';

	}

	mysqli_close($mysqli);

} else {
	echo '<div>Error Occured .</div>';
}
?>