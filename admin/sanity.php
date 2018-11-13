<?php
function Sanitize($input, $type) {
  switch ($type) {
    // 1- Input Validation
 
    case 'int': // comprueba si $input es integer
      if (is_int($input)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    case 'str': // comprueba si $input es string
      if (is_string($input)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    case 'digit': // comprueba si $input contiene solo numeros
      if (ctype_digit($input)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    case 'upper': // comprueba si $input contiene solo mayusculas
      if ($input == strtoupper($input)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    case 'lower': // comprueba si $input contiene solo minusculas
      if ($input == strtolower($input)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    case 'mobile': // comprueba si $input contiene 9 numeros
      if ((strlen($input) == 9) && (ctype_digit($input) == TRUE)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    case 'email': // comprueba si $input tiene formato de email
      $reg_exp = "/^[-_.0-9A-Z]+@([-0-9A-Z]+.)+([0-9A-Z]){2,4}$/i";
      if (preg_match($reg_exp, $input)) {
        $output = TRUE;
      } else {
        $output = FALSE;
      }
      break;
 
    // 2- SQL Encoding
 
    case 'sql': // escapar los caracteres que no son legales en SQL
 
      // si magic_quotes_gpc esta activado primero aplicar stripslashes()
      // de lo contrario los datos seran escapados dos veces
      if (get_magic_quotes_gpc()) {
        $input = stripslashes($input);
      }
 
      // requiere una conexion MySQL, de lo contrario dara error
      $output = mysqli_real_escape_string($link,trim($input));
      break;
 
    // 3- Output Filtering
 
    case 'no_html': // los datos van a una pagina web, escapar para HTML
      $output = htmlentities(trim($input), ENT_QUOTES);
      break;
 
    case 'shell_arg': // los datos van al shell, escapar para shell argument
      $output = escapeshellarg(trim($input));
      break;
 
    case 'shell_cmd': // los datos van al shell, escapar para shell command
      $output = escapeshellcmd(trim($input));
      break;
 
    case 'url': // los datos forman parte de una URL, escapar para URL
 
      // htmlentities() traduce a HTML el separador &
      $output = htmlentities(urlencode(trim($input)));
      break;
 
    case 'comment': // los datos solo pueden contener algunos tags HTML
      $output = strip_tags($input, '<b><i><img>');
      break;
  }
  return $output;
}

function number($str){
	$caracteres = "%[^0-9 \. ]%";
	$str=preg_replace($caracteres,"",$str);
	return $str;
}

function plain_text($str){
	$str=strip_tags($str);
	return $str;
}

function normalizar($cadena){
	//eliminamos los acentos
	$tofind = array("À","Á","Â","Ã","Ä","Å","à","á","â","ã","ä","å","Ò","Ó","Ô","Õ","Ö","Ø","ò","ó","ô","õ","ö","ø","È","É","Ê","Ë","è","é","ê","ë","Ç","ç","Ì","Í","Î","Ï","ì","í","î","ï","Ù","Ú","Û","Ü","ù","ú","û","ü","ÿ","Ñ","ñ");
	$replac = array("A","A","A","A","A","A","a","a","a","a","a","a","O","O","O","O","O","O","o","o","o","o","o","o","E","E","E","E","e","e","e","e","C","c","I","I","I","I","i","i","i","i","U","U","U","U","u","u","u","u","y","N","n");
	$cadena1 = str_ireplace($tofind,$replac,$cadena);
 
	//eliminamos todo lo que no sean letras numeros o el punto de la extension
	$cadena2 = preg_replace("/[^ .\w]+/", "_", $cadena1);
 
	return($cadena2); 
}

function token($tamano) {
$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $tamano; $i++) {
	    $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>