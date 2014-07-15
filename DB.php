<?php
define("HOST", "mysql12.citynetwork.se"); // The host you want to connect to.
define("USER", "120268-xh59089"); // The database username.
define("PASSWORD", "B3achT1me!"); // The database password. 
define("DATABASE", "120268-beachtime"); // The database name.
$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
$link = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
?>