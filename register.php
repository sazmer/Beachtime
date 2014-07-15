<?php
include ('DB.php');
include ('loginfunctions.php');
//Hämta användarens passord
$password = $_REQUEST['password'];
$username = $_REQUEST['username'];
//Kryptera och lägg till saltnyckel
$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
$password = hash('sha512', $password . $random_salt);
$outmsg;
$error = array();
if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_REQUEST['email'])) {
	//regular expression for email validation
	$email = $_REQUEST['email'];
} else {
	$error[] = 'Your Email Address is invalid.<br>';
}
if ( !preg_match('/^[A-Za-z][A-Za-z0-9]{5,13}$/', $username) ){
	$error[] = 'Your Username must be 6-13 letters and may contain a-z and 0-9.<br>';
}

$query_verify_email = "SELECT * FROM users  WHERE email ='$email'";
$result_verify_email = $mysqli -> query($query_verify_email);
$query_verify_user = "SELECT * FROM users  WHERE username='$username'";
$result_verify_user = $mysqli -> query($query_verify_user);

if (mysqli_num_rows($result_verify_user) != 0) {
	$error[] = "Username in use.<br>";
}
if (mysqli_num_rows($result_verify_email) != 0) {
	$error[] = "Email Adress in use.<br>";
}
if (count($error)<1) {
	// Create a unique  activation code:
	$activation = md5(uniqid(rand(), true));
	//lägg in i databasen:
	if ($insert_stmt = $mysqli -> prepare("INSERT INTO users (username,email,password,salt, activation) VALUES (?,?,?,?,?)")) {
		$insert_stmt -> bind_param('sssss', $username, $email, $password, $random_salt, $activation);
		$insert_stmt -> execute();

		if ($mysqli -> affected_rows == 1) {
			//If the Insert Query was successfull.

			// Send the email:
			$headers = 'From: ' . "BeachTime Activation <register@beachtime.se>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$message = '<html><body>';
			$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
			$message .= "<tr style='background: #eee;'><td> </td><td>" . "<strong>Registration request</strong>" . "</td></tr>";
			$message .= "<tr><td><strong>Username:</strong> </td><td>" . strip_tags($_REQUEST['username']) . "</td></tr>";
			$message .= "<tr><td><strong>Email:</strong> </td><td>" . strip_tags($_REQUEST['email']) . "</td></tr>";
			$message .= "<tr><td><strong>Activation:</strong> </td><td>" . "http://www.testenv.beachtime.se" . '/activate.php?email=' . urlencode($email) . "&key=$activation" . "</td></tr>";
			$message .= "</table>";
			$message .= "</body></html>";

			mail($email, 'BeachTime Registration', $message, $headers);

			// Flush the buffered output.

			// Finish the page:
			$outmsg = json_encode("registered");

		} else {
			$error[] = "Something went wrong. Contact webmaster or try again.";
		}
	} else {
		$error[] = "Something went wrong. Contact webmaster or try again.";
	}
}
if (count($error) > 0) {
	$outmsg = json_encode($error);
}
echo $outmsg;
?>