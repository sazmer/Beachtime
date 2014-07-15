<?php

include('DB.php');

$id = $_REQUEST['id'];

              
$query=("DELETE FROM players WHERE id='$id'");
$mysqli->query($query);

$result = json_encode("Success!");
echo $result;
 
?>  
