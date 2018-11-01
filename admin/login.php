<?php
session_start();
include('../conexion.php');
include('sanity.php');
if(Sanitize($_POST['usuario'],'email')){
	$user = plain_text($_POST['usuario']);
}
$password = md5($_POST['contrasena']);
$sql = mysqli_query($link,"SELECT * FROM `usuarios` WHERE `email`='".$user."'");
$number = mysqli_num_rows($sql);
$data = mysqli_fetch_array($sql);
if($password == $data['contrasena']){
	$_SESSION['logged'] = "si";
	$_SESSION['nombre_user'] = $data['nombre'];
	$_SESSION['id_user'] = $data['id'];
	$_SESSION['tipo_user'] = $data['tipo'];
	header("Location: index.php");
} else {
	$params = "?";
	if($number == 0){
		$params .= "usr_error=1&pass_error=0";
	} else {
		$params .= "usr_error=0&pass_error=1";
	}
	header("Location: index.php".$params);
}
?>