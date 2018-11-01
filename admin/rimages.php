<?php
ini_set("max_execution_time","300");
function redim($img,$dest,$prop){
	if(is_file($img)){
		if($prop == 1){
			#Miniatura Listado
			$rewidth = 600;
			$reheight = 600;
			$rerat = 1;
		} else if($prop == 2){
			#Full
			$rewidth = 2500;
			$reheight = 2500;
			$rerat = 1;
		} else if($prop == 3){
			#Miniatura Album
			$rewidth = 600;
			$reheight = 600;
			$rerat = 1;
		} else if($prop == 4){
			#Foto Perfil
			$rewidth = 132;
			$reheight = 132;
			$rerat = 1;
		} else if($prop == 5){
			#Recomendado
			$rewidth = 400;
			$reheight = 320;
			$rerat = 1.25;
		} else if($prop == 6){
			#Publicidad Inicio
			$rewidth = 200;
			$reheight = 175;
			$rerat = 1.142857142857143;
		} else if($prop == 7){
			#Infografias Recomendado
			$rewidth = 260;
			$reheight = 150;
			$rerat = 1.733333333333333;
		}
		$dimensiones = getimagesize($img);
		$ancho = $dimensiones[0];
		$alto = $dimensiones[1];
		$tipo = $dimensiones[2];
		$rat = $ancho / $alto;
	/*	1 => 'GIF',
		2 => 'JPG',
		3 => 'PNG',*/
		switch($tipo) {
			case 1:
			$prev = imagecreatefromgif($img);
			break;
			case 2:
			$prev = imagecreatefromjpeg($img);
			break;
			case 3:
			$prev = imagecreatefrompng($img);
			break;
		}
		if($prop == 25){
			$image = imagecreatetruecolor($rewidth,384);
		} else {
			$image = imagecreatetruecolor($rewidth,$reheight);
		}
		if(($tipo == 25)||($tipo == 25)){
			imagecolortransparent($image, imagecolorallocatealpha($image, 255, 255, 255, 127));
			imagealphablending($image, false);
			imagesavealpha($image, true);
		} else {
			$blanco = imagecolorallocate($image,255,255,255);
			$negro = imagecolorallocate($image,0,0,0);
			imagefilledrectangle($image,0,0,$rewidth,$reheight,$negro);
		}
		if(($ancho != $rewidth)||($alto != $reheight)){
			if($prop == 2 || $prop == 5){ //No Recorta
				if ($rerat > $rat) {
					$tempw = $reheight*$rat;
					$tempw = round($tempw,0);
					$x = ($rewidth - $tempw) /2;
					$x = round($x,0);
					imagecopyresampled($image,$prev,$x,0,0,0,$tempw,$reheight,$ancho,$alto);
				} else {
					$temph = $rewidth/$rat;
					$temph = round($temph,0);
					$y = ($reheight - $temph)/2;
					$y = round($y,0);
					imagecopyresampled($image,$prev,0,$y,0,0,$rewidth,$temph,$ancho,$alto);
				}
			} else { //Recorta
				if($rat > $rerat){
					$tempw = $reheight * $rat;
					$tempw = round($tempw,0);
					$x = ($tempw - $rewidth) /2;
					$x = round($x,0);
					imagecopyresampled($image,$prev,0,0,$x,0,$tempw,$reheight,$ancho,$alto);
				} elseif($rat < $rerat) {
					$temph = $rewidth / $rat;
					$temph = round($temph,0);
					$y = ($temph-$reheight)/2;
					$y = round($y,0);
					imagecopyresampled($image,$prev,0,0,0,$y,$rewidth,$temph,$ancho,$alto);
				} else {
					imagecopyresampled($image,$prev,0,0,0,0,$rewidth,$reheight,$ancho,$alto);
				}
			}
		} else {
			imagecopyresampled($image,$prev,0,0,0,0,$rewidth,$reheight,$ancho,$alto);
		}
		//Poner borde negro
		//imagerectangle($image,0,0,($rewidth-1),($reheight-1),$negro);
		if($prop == 25){
			$dimension = getimagesize($sup);
			$anchox = $dimension[0];
			$altox = $dimension[1];
			$tipox = $dimension[2];
			$ratx = $anchox / $altox;
		/*	1 => 'GIF',
			2 => 'JPG',
			3 => 'PNG',*/
			switch($tipox) {
				case 1:
				$prestampa = imagecreatefromgif($sup);
				break;
				case 2:
				$prestampa = imagecreatefromjpeg($sup);
				break;
				case 3:
				$prestampa = imagecreatefrompng($sup);
				break;
			}
			$estampa = imagecreatefrompng('mask.png');
			
			
			// Establecer los márgenes para la estampa y obtener el alto/ancho de la imagen de la estampa
			$margen_dcho = 0;
			$margen_inf = 0;
			$sx = imagesx($estampa);
			$sy = imagesy($estampa);
			
			// Copiar la imagen de la estampa sobre nuestra foto usando los índices de márgen y el
			// ancho de la foto para calcular la posición de la estampa. 
			imagecopy($image,$estampa,0,0,0,0,$sx,$sy);
			imagecopy($image,$prestampa,0,0,0,0,$anchox,$altox);
		}
		switch($tipo) {
			case 1:
				if(imagegif($image,$dest)){ return true; } else {return false; }
				imagedestroy($image);
			break;
			case 2:
				if(imagejpeg($image,$dest,85)){ return true; } else {return false; }
				imagedestroy($image);
			break;
			case 3:
				if(imagepng($image,$dest)){ return true; } else {return false; }
				imagedestroy($image);
				return true;
			break;
		}
	} else {
		return false;
	}
}