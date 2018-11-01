<?php
date_default_timezone_set('America/Bogota');
$host = "localhost";
$user = "creabuc";
$pwd = "Logan2010!";
$db_nombre= "creabuc";

$link = mysqli_connect($host,$user,$pwd,$db_nombre);
mysqli_query($link,"SET CHARACTER SET 'utf8'");
mysqli_query($link,"SET NAMES 'utf8'");
?>