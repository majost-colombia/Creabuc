<?php
session_start();
require ("../conexion.php");
require ("../sanity.php");
if($_SESSION['logged'] == "si"){
	$id = number($_POST['id']);
	$sql = mysqli_query($link,"SELECT * FROM `fotos` WHERE `padre`=".$id);
	if(mysqli_num_rows($sql) == 0){
		echo "<p>El album puede ser borrado con seguridad</p>";
	} else {
		echo "<p>El album aún cuenta con imágenes, si lo elimina las imágenes también serán eliminadas</p>";
	}
} else {
	echo "Su sesión ha expirado o eres un hacker muy novato intentando encontrar un agujero";
}
?>