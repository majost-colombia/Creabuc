<?php
/**
 * Created by PhpStorm.
 * User: Jose Luis Silva
 * Date: 28/10/2018
 * Time: 10:53 AM
 */
session_start();

$_SESSION['logged'] = "no";
$_SESSION['nombre_user'] = '';
$_SESSION['id_user'] = 0;
$_SESSION['tipo_user'] = 0;

header('Location: ../index.php');
?>