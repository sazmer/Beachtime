
<html>
    <head>
        <title>Valkommen till Fyrisbeach</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            
    
            <?php

include('DB.php');
include('loginfunctions.php');
//Hämta användarens passord
$password = $_POST['p'];
$email = $_POST['email'];
$username = $_POST['user'];
//Kryptera och lägg till saltnyckel
$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
$password = hash('sha512', $password . $random_salt);


//lägg in i databasen:
if ($insert_stmt = $mysqli->prepare("INSERT INTO users (username,email,password,salt) VALUES (?,?,?,?)")) {

    $insert_stmt->bind_param('ssss', $username, $email, $password, $random_salt);
    $insert_stmt->execute();
   
    echo "<META HTTP-EQUIV='refresh' CONTENT='2;URL=index.php?reg=1'>";
    echo '</head>';
     echo 'Skapar användare...';
}
?> 

</html>