<?php
/**
 * Created by PhpStorm.
 * User: Jose Luis Silva
 * Date: 26/10/2018
 * Time: 09:16 PM
 */
session_start();
require('../conexion.php');
require('sanity.php');
require('rimages.php');

 $login = true;

if(isset($_GET['usr_error'])){
    if(number($_GET['usr_error']) == 1){
        $user_err = true;
    } else {
	    $user_err = false;
    }
}

if(isset($_GET['pass_error'])){
	if(number($_GET['pass_error']) == 1){
		$pass_err = true;
	} else {
		$pass_err = false;
	}
}

if (isset($_POST['seccion'])) {
	$seccion = plain_text($_POST['seccion']);
} elseif (isset($_GET['seccion'])) {
	$seccion = plain_text($_GET['seccion']);
} else {
	$seccion = "dashboard";
}

if (isset($_POST['accion'])) {
	$accion = plain_text($_POST['accion']);
} elseif (isset($_GET['accion'])) {
	$accion = plain_text($_GET['accion']);
} else {
	$accion = 'listar';
}

if (isset($_POST['id'])) {
	$id = number($_POST['id']);
} elseif (isset($_GET['id'])) {
	$id = number($_GET['id']);
} else {
	$id = 0;
}

switch($seccion) {
	case 'dashboard';
		if ( $_SESSION['logged'] == "si" ) {
			$login  = false;
			$titulo = "Creabuc - Inicio";
		} else {
			$login  = true;
			$titulo = "Creabuc - Login";
		}
		break;
	case "registro":
		$titulo = "Creabuc - Registro de usuario";
		break;
	case "registrar":
		$errors = [];
		if ( strlen( plain_text( $_POST['nombre'] ) ) > 3 ) {
			$nombre = plain_text( $_POST['nombre'] );
		} else {
			$errors[] = "El nombre es obligatorio, debe llevar nombre y apellido.";
		}
		if ( strlen( number( $_POST['telefono'] ) ) > 6 ) {
			$telefono = number( $_POST['telefono'] );
		} else {
			$errors[] = "El teléfono es un campo obligatorio, debe poner al menos un fijo con indicativo.";
		}
		if ( Sanitize( $_POST['email'], "email" ) ) {
			$email = $_POST['email'];
		} else {
			$errors[] = "Debe ingresar un email válido, el email indicado no parece serlo.";
		}
		$tipo = 2;
		if ( strlen( $_POST['contrasena'] ) >= 8 ) {
			$contrasena = $_POST['contrasena'];
		} else {
			$errors[] = "La contraseña debe tener al menos 8 caracteres";
		}
		$token = token( 10 );
		if ( isset( $email ) ) {
			$sql_check = mysqli_query( $link, "SELECT `id` FROM `usuarios` WHERE `email`='" . $email . "'" );
			if ( mysqli_num_rows( $sql_check ) > 0 ) {
				$errors[] = "Ese E-mail ya se encuentra registrado";
			}
		}
		if ( count( $errors ) == 0 ) {
			$sql = mysqli_query( $link, "INSERT INTO `usuarios` (`nombre`,`foto_perfil`,`telefono`,`email`,`tipo`,`contrasena`,`token`) VALUES ('" . $nombre . "','images/unknown.png','" . $telefono . "','" . $email . "','" . $tipo . "','" . md5( $contrasena ) . "','" . $token . "')" );
			if ( empty( mysqli_error( $link ) ) ) {
				$seccion               = "response";
				$titulo                = "Creabuc - Correcto";
				$response['titulo']    = "Correcto";
				$response['contenido'] = "Usuario registrado exitosamente, puedes loguearte en la aplicación haciendo <a href=\"index.php\">clic aquí</a>";
			} else {
				$seccion               = "response";
				$titulo                = "Creabuc - Error";
				$response['titulo']    = "Error";
				$response['contenido'] = "Error: ".mysqli_error($link);
            }
		} else {
			$seccion               = "response";
			$titulo                = "Creabuc - Error";
			$response['titulo']    = "Error";
			$response['contenido'] = "Tienes los siguientes errores en tu formulario de registro:<br />";
			foreach ( $errors as $mensaje ) {
				$response['contenido'] .= '
                <div class="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>¡Error!</strong> ' . $mensaje . '
                </div>';
			}
			$response['contenido'] .= "<p><a href='javascript:history.back()'>Haz clic aquí</a> para regresar y corregir los errores.</p>";
		}
		break;
	case "albumes":
		if ( $accion == "listar" ) {
			if ( $_SESSION['tipo_user'] == "1" ) {
				$titulo = "Creabuc - Albumes";
				$sql    = mysqli_query( $link, "SELECT `albumes`.`id` id_album,`categorias`.`id` id_categoria,`usuarios`.`id` id_usuario,`albumes`.`nombre` nombre_album,`categorias`.`nombre` nombre_categoria,`usuarios`.`nombre` nombre_usuario FROM `albumes`,`categorias`,`usuarios` WHERE `usuarios`.`id` = `albumes`.`usuario` AND `categorias`.`id` = `albumes`.`categoria`" );
			} elseif ( $_SESSION['tipo_user'] == "2" ) {
				$titulo = "Creabuc - Mis albumes";
				$sql    = mysqli_query( $link, "SELECT `albumes`.`id` id_album,`categorias`.`id` id_categoria,`usuarios`.`id` id_usuario,`albumes`.`nombre` nombre_album,`categorias`.`nombre` nombre_categoria,`usuarios`.`nombre` nombre_usuario FROM `albumes`,`categorias`,`usuarios` WHERE `usuarios`.`id` = `albumes`.`usuario` AND `categorias`.`id` = `albumes`.`categoria` AND `usuario` = " . $_SESSION['id_user'] );
			}
		} elseif ( $accion == "crear" ) {
			$titulo       = "Creabuc - Crear album";
			$usuarios     = mysqli_query( $link, "SELECT * FROM `usuarios`");
			$categorias   = mysqli_query( $link, "SELECT * FROM `categorias`");
		} elseif ( $accion == "guardar" ) {
			$categoria_id   = number( $_POST['categoria'] );
			$sql         = mysqli_query( $link, "SELECT `nombre` FROM `categorias` WHERE `id`=" . $categoria_id );
			$categoria   = mysqli_fetch_array( $sql );
			$nombre      = plain_text( $_POST['nombre'] );
			$descripcion = strip_tags( $_POST['descripcion'], "p,strong,b,i,span" );
			@mkdir('../images/albumes/' . normalizar($categoria['nombre']) . '/' . normalizar( $nombre ), 0775, true);
			if(isset($_FILES['miniatura'])) {
				if ( $_FILES['miniatura']['size'] > 35 ) {
					redim( $_FILES['miniatura']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/main_' . normalizar( $_FILES[ 'miniatura' ]['name'] ), 1 );
					$ruta_miniatura  = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/main_' . normalizar( $_FILES[ 'miniatura' ]['name'] );
				} else {
					$ruta_miniatura = 'images/default_categoria';
				}
			} else {
				$ruta_miniatura = 'images/default_categoria';
			}
			$sql_cond  = "INSERT INTO `fotos` (`padre`,`ruta_mini`,`ruta_full`,`activo`) VALUES ";
			$sql_album = mysqli_query( $link, "INSERT INTO `albumes` (`usuario`,`categoria`,`nombre`,`descripcion`,`miniatura`,`activo`) VALUES ('" . $_SESSION['id_user'] . "','" . $categoria_id . "','" . $nombre . "','" . $descripcion . "','" . $ruta_miniatura . "','1')");
			$album     = mysqli_insert_id( $link );
			if ( $album != false ) {
				for ( $i = 1; $i <= 5; ++ $i ) {
					if ( $_FILES[ 'imagen' . $i ]['size'] > 35 ) {
						$check_miniatura = redim( $_FILES[ 'imagen' . $i ]['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES[ 'imagen' . $i ]['name'] ), 1 );
						$check_grande    = redim( $_FILES[ 'imagen' . $i ]['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES[ 'imagen' . $i ]['name'] ), 2 );
						if ( $check_miniatura && $check_grande ) {
							$miniatura = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES[ 'imagen' . $i ]['name'] );
							$grande    = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES[ 'imagen' . $i ]['name'] );
							$sql_cond  .= "(" . $album . ",'" . $miniatura . "','" . $grande . "',1),";
						}
					}
				}
				$sql_fotos = substr( $sql_cond, 0, - 1 );
				$ejecutar  = mysqli_query( $link, $sql_fotos );
				if ( $ejecutar ) {
					$titulo                = "Creabuc - Album creado correctamente";
					$seccion               = "response";
					$response['titulo']    = "Album creado";
					$response['contenido'] = "El album se ha creado correctamente";
				} else {
					$titulo                = "Creabuc - Error";
					$seccion               = "response";
					$response['titulo']    = 'Error';
					$response['contenido'] = 'Se ha presentado un error al guardar las fotos.';
				}
			} else {
				$titulo                = "Creabuc - Error";
				$seccion               = "response";
				$response['titulo']    = 'Error';
				$response['contenido'] = 'Se presentó un error al crear el album, contacte al administrador.';
			}
		} elseif ( $accion == "editar" ) {
		    $titulo       = "Creabuc - Editar album";
			$id           = number( $_GET['id'] );
			$sql          = mysqli_query( $link, "SELECT * ,`albumes`.`id` id_album,`categorias`.`id` id_categoria,`usuarios`.`id` id_usuario,`albumes`.`nombre` nombre_album,`categorias`.`nombre` nombre_categoria,`usuarios`.`nombre` nombre_usuario, `albumes`.`miniatura` miniatura FROM `albumes`,`categorias`,`usuarios` WHERE `usuarios`.`id` = `albumes`.`usuario` AND `categorias`.`id` = `albumes`.`categoria` AND `albumes`.`id` = " . $id );
			$usuarios     = mysqli_query( $link, "SELECT * FROM `usuarios`");
			$categorias   = mysqli_query( $link, "SELECT * FROM `categorias`");
			$data         = mysqli_fetch_array( $sql );
			$sql_imagenes = mysqli_query( $link, "SELECT * FROM `fotos` WHERE `padre` = " . $data['id_album'] );
		} elseif ( $accion == "actualizar" ) {
			$album        = number( $_GET['id'] );
			$errors = array();
			$categoria_id    = number( $_POST['categoria'] );
			$sql          = mysqli_query( $link, "SELECT `nombre` FROM `categorias` WHERE `id`=" . $categoria_id );
			$categoria    = mysqli_fetch_array( $sql );
			$nombre       = plain_text( $_POST['nombre'] );
			$descripcion  = strip_tags( $_POST['descripcion'], "p,strong,b,i,span" );
			$dir          = '../images/albumes/' . $categoria['nombre'] . '/' . $nombre;
			if (!file_exists($dir) && !is_dir($dir)) {
				mkdir($dir,0775,true);
			}
			if(isset($_FILES['miniatura'])) {
				if ( $_FILES['miniatura']['size'] > 35 ) {
					redim( $_FILES['miniatura']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/main_' . normalizar( $_FILES[ 'miniatura' ]['name'] ), 3 );
					$ruta_miniatura  = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/main_' . normalizar( $_FILES[ 'miniatura' ]['name'] );
				} else {
					$ruta_miniatura = null;
				}
			} else {
			    $ruta_miniatura = null;
            }
			if ( isset( $_POST['activo'] ) ) {
				$activo = 1;
			} else {
				$activo = 0;
			}
			if ( $ruta_miniatura != null ) {
				$sql_album = mysqli_query( $link, "UPDATE `albumes` SET `usuario`='" . $_SESSION['id_user'] . "',`categoria`='" . $categoria_id . "',`nombre`='" . $nombre . "',`descripcion`='" . $descripcion . "',`miniatura`='" . $ruta_miniatura . "',`activo`=" . $activo . " WHERE `id`=" . $album );
			} else {
				$sql_album = mysqli_query( $link, "UPDATE `albumes` SET `usuario`='" . $_SESSION['id_user'] . "',`categoria`='" . $categoria_id . "',`nombre`='" . $nombre . "',`descripcion`='" . $descripcion . "',`activo`=" . $activo . " WHERE `id`=" . $album );
			}
			if ( $sql_album != false ) {
				if ( $_FILES['imagen1']['size'] > 35 ) {
					$id_imagen1      = number( $_POST['id_imagen1'] );
					$check_miniatura = redim( $_FILES['imagen1']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . 'sm_' . normalizar( $_FILES['imagen1']['name'] ), 1 );
					$check_grande    = redim( $_FILES['imagen1']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen1']['name'] ), 2 );
					if ( $check_miniatura && $check_grande ) {
						$miniatura   = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen1']['name'] );
						$grande      = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen1']['name'] );
						$sql_img1    = mysqli_query( $link, "UPDATE `fotos` SET `padre`=" . $album . ",`ruta_mini` = '" . $miniatura . "',`ruta_full` = '" . $grande . "' WHERE `id` = " . $id_imagen1 );
						if ( ! $sql_img1 ) {
							$errors[] = "Hubo un error actualizando la imagen 1";
						}
					}
				}
				if ( $_FILES['imagen2']['size'] > 35 ) {
					$id_imagen2      = number( $_POST['id_imagen2'] );
					$check_miniatura = redim( $_FILES['imagen2']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen2']['name'] ), 1 );
					$check_grande    = redim( $_FILES['imagen2']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen2']['name'] ), 2 );
					if ( $check_miniatura && $check_grande ) {
						$miniatura   = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen2']['name'] );
						$grande      = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen2']['name'] );
						$sql_img2    = mysqli_query( $link, "UPDATE `fotos` SET `padre`=" . $album . ",`ruta_mini` = '" . $miniatura . "',`ruta_full` = '" . $grande . "' WHERE `id` = " . $id_imagen2 );
						if ( ! $sql_img2 ) {
							$errors[] = "Hubo un error actualizando la imagen 2";
						}
					}
				}
				if ( $_FILES['imagen3']['size'] > 35 ) {
					$id_imagen3      = number( $_POST['id_imagen3'] );
					$check_miniatura = redim( $_FILES['imagen3']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen3']['name'] ), 1 );
					$check_grande    = redim( $_FILES['imagen3']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen3']['name'] ), 2 );
					if ( $check_miniatura && $check_grande ) {
						$miniatura   = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen3']['name'] );
						$grande      = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen3']['name'] );
						$sql_img3    = mysqli_query( $link, "UPDATE `fotos` SET `padre`=" . $album . ",`ruta_mini` = '" . $miniatura . "',`ruta_full` = '" . $grande . "' WHERE `id` = " . $id_imagen3 );
						if ( ! $sql_img3 ) {
							$errors[] = "Hubo un error actualizando la imagen 3";
						}
					}
				}
				if ( $_FILES['imagen4']['size'] > 35 ) {
					$id_imagen4      = number( $_POST['id_imagen4'] );
					$check_miniatura = redim( $_FILES['imagen4']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen4']['name'] ), 1 );
					$check_grande    = redim( $_FILES['imagen4']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen4']['name'] ), 2 );
					if ( $check_miniatura && $check_grande ) {
						$miniatura   = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen4']['name'] );
						$grande      = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen4']['name'] );
						$sql_img4    = mysqli_query( $link, "UPDATE `fotos` SET `padre`=" . $album . ",`ruta_mini` = '" . $miniatura . "',`ruta_full` = '" . $grande . "' WHERE `id` = " . $id_imagen4 );
						if ( ! $sql_img4 ) {
							$errors[] = "Hubo un error actualizando la imagen 4";
						}
					}
				}
				if ( $_FILES['imagen5']['size'] > 35 ) {
					$id_imagen5      = number( $_POST['id_imagen5'] );
					$check_miniatura = redim( $_FILES['imagen5']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen5']['name'] ), 1 );
					$check_grande    = redim( $_FILES['imagen5']['tmp_name'], '../images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen5']['name'] ), 2 );
					if ( $check_miniatura && $check_grande ) {
						$miniatura   = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/sm_' . normalizar( $_FILES['imagen5']['name'] );
						$grande      = 'images/albumes/' . normalizar( $categoria['nombre'] ) . '/' . normalizar( $nombre ) . '/' . normalizar( $_FILES['imagen5']['name'] );
						$sql_img5    = mysqli_query( $link, "UPDATE `fotos` SET `padre`=" . $album . ",`ruta_mini` = '" . $miniatura . "',`ruta_full` = '" . $grande . "' WHERE `id` = " . $id_imagen5 );
						if ( ! $sql_img5 ) {
							$errors[] = "Hubo un error actualizando la imagen 5";
						}
					}
				}
				if(count($errors) == 0){
					$titulo                = "Creabuc - Album actualizado";
					$seccion               = "response";
					$response['titulo']    = 'Correcto';
					$response['contenido'] = 'Album actualizado correctamente.';
                }
			} else {
				$titulo                = "Creabuc - Error";
				$seccion               = "response";
				$response['titulo']    = 'Error';
				$response['contenido'] = 'Se presentó un error al crear el album, contacte al administrador.';
			}
		} elseif ( $accion == "eliminar" ) {
			$id        = number( $_GET['id'] );
			$errors = array();
			$sql_fotos = mysqli_query($link,"SELECT * FROM `fotos` WHERE `padre` = " . $id);
			while ( $foto = mysqli_fetch_array( $sql_fotos ) ) {
				@unlink( '../' . $foto['ruta_mini'] );
				@unlink( '../' . $foto['ruta_full'] );
			}
			$delete_fotos = mysqli_query( $link, "DELETE FROM `fotos` WHERE `padre` = " . $id );
			if ( $delete_fotos ) {
				$delete_album = mysqli_query( $link, "DELETE FROM `albumes` WHERE `id` = " . $id );
				if ( ! $delete_album ) {
					$errors[] = "Error elimnando el album. <br />";
				}
			} else {
				$errors[] = "Error al eliminar las fotos. <br />";
			}
			if ( count( $errors ) == 0 ) {
				$seccion            = "response";
				$titulo             = "Creabuc - Album eliminado";
				$response['titulo'] = "Correcto";
				$response['contenido'] = "El album se ha eliminado correctamente.";
			} else {
				$seccion               = "response";
				$titulo                = "Creabuc - Album eliminado";
				$response['titulo']    = "Error";
				$response['contenido'] = "Han ocurrido error/es al eliminar el album: <br />" . implode( $errors );
			}
		}
		break;
	case "categorias":
		if ( $_SESSION['tipo_user'] == 1 && $accion != "crear_categoria" ) {
			if ( $accion == "listar" ) {
			    $titulo = "Creabuc - Listado de categorías";
				$sql = mysqli_query( $link, "SELECT `categorias`.`id` id_categoria,`usuarios`.`id` id_usuario,`categorias`.`nombre` nombre_categoria,`usuarios`.`nombre` nombre_usuario FROM `categorias`,`usuarios` WHERE `usuarios`.`id` = `categorias`.`creada_por`" );
			} elseif ( $accion == "crear" ) {
                $titulo = "Creabuc - Crear categoría";
			} elseif ( $accion == "guardar" ) {
				$nombre = plain_text( $_POST['nombre'] );
				if(isset($_FILES['miniatura'])) {
					if ( $_FILES['miniatura']['size'] > 35 ) {
						redim( $_FILES['miniatura']['tmp_name'], '../images/categorias/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['miniatura']['name'] ), 3 );
						$imagen = 'images/categorias/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['miniatura']['name'] );
					} else {
						$imagen = 'images/default_categoria.jpg';
					}
				} else {
					$imagen = 'images/default_categoria.jpg';
				}
				if(isset($_POST['activa'])){
				    $activa = 1;
                } else {
				    $activa = 0;
                }
				$sql = mysqli_query( $link, "INSERT INTO `categorias` (nombre, miniatura, creada_por, activa) VALUES ('" . $nombre . "','" . $imagen . "'," . $_SESSION['id_user'] . ",".$activa.")" );
				if ( $sql ) {
					$seccion               = "response";
					$titulo                = "Creabuc - Categoría creada";
					$response['titulo']    = "Correcto";
					$response['contenido'] = "La categoria se ha creado correctamente.";
				} else {
					$seccion               = "response";
					$titulo                = "Creabuc - Error al crear categoría";
					$response['titulo']    = "Error";
					$response['contenido'] = "Ha ocurrido un problema al crear la categoría.";
				}
			} elseif ( $accion == "editar" ) {
			    $titulo = "Creabuc - Editar categoría";
				$id     = number( $_GET['id'] );
				$sql    = mysqli_query( $link, "SELECT * FROM `categorias` WHERE `id` = " . $id );
				$data   = mysqli_fetch_array($sql);
			} elseif ( $accion == "actualizar" ) {
				$id     = number( $_GET['id'] );
				$nombre = plain_text( $_POST['nombre'] );
				if(isset($_FILES['miniatura'])) {
					if ( $_FILES['miniatura']['size'] > 35 ) {
						redim( $_FILES['miniatura']['tmp_name'], '../images/categorias/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['miniatura']['name'] ), 3 );
						$imagen = 'images/categorias/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['miniatura']['name'] );
					} else {
						$imagen = null;
					}
				} else {
					$imagen = null;
				}
				if ( isset( $_POST['activa'] ) ) {
					$activa = 1;
				} else {
					$activa = 0;
				}
				if ( !is_null($imagen) ) {
					$sql = mysqli_query( $link, "UPDATE `categorias` SET `nombre` = '" . $nombre . "', `miniatura` = '" . $imagen . "', `activa` = " . $activa . " WHERE `id` = " . $id );
				} else {
					$sql = mysqli_query( $link, "UPDATE `categorias` SET `nombre` = '" . $nombre . "', `activa` = " . $activa . " WHERE `id` = " . $id );
				}
				if ( $sql ) {
					$seccion               = "response";
					$titulo                = "Creabuc - Categoría actualizada";
					$response['titulo']    = "Correcto";
					$response['contenido'] = "La categoria se ha actualizado correctamente.";
				} else {
					$seccion               = "response";
					$titulo                = "Creabuc - Error al actualizar la categoría";
					$response['titulo']    = "Error";
					$response['contenido'] = "Ha ocurrido un problema al actualizar la categoría.";
				}
			} elseif ( $accion == "eliminar" ) {
				$id        = number( $_GET['id'] );
				$sql_check = mysqli_query( $link, "SELECT * FROM `categorias` WHERE `id` = " . $id );
				$data      = mysqli_fetch_array( $sql_check );
				if ( $data['miniatura'] != 'images/default_categoria.jpg' ) {
					@unlink( '../' . $data['miniatura'] );
                }
				$sql = mysqli_query( $link, "DELETE FROM `categorias` WHERE `id` = " . $id );
				if ( $sql ) {
					$seccion               = "response";
					$response['titulo']    = "Correcto";
					$response['contenido'] = "La categoría se eliminó correctamente.";
				} else {
					$seccion               = "response";
					$response['titulo']    = "Error";
					$response['contenido'] = "Se presentó un error al eliminar la categoría.";
				}
			}
		} else {
			if ( $accion == "crear_categoria" ) {
				$nombre    = plain_text( $_POST['nombre'] );
				$sql       = mysqli_query( $link, "SELECT `id` FROM `categorias` WHERE `nombre` = '" . $nombre . "'" );
				$resultado = mysqli_num_rows( $sql );
				if ( $resultado > 0 ) {
					echo "Error, la categoría que intentas crear ya existe";
				} else {
					$sql = mysqli_query( $link, "INSERT INTO `categorias` (`nombre`, `miniatura`, `creada_por`, `activa`) VALUES ('" . $nombre . "','images/default_categoria.jpg','" . $_SESSION['id_user'] . "',0)" );
					if ( $sql ) {
						echo mysqli_insert_id( $link ) . ',' . $nombre;
					}
				}
			} else {
				$seccion               = "response";
				$titulo                = "Acceso denegado";
				$response['titulo']    = "Error";
				$response['contenido'] = "Acceso denegado";
			}
		}
		break;
	case "usuarios":
		if ( $_SESSION['tipo_user'] == 1 ) {
			if ( $accion == "listar" ) {
			    $titulo  = "Creabuc - Listado de usuarios";
                $sql     = mysqli_query($link,"SELECT * FROM `usuarios`");
			} elseif ( $accion == "crear" ) {
                $titulo  = "Creabuc - Crear usuario";
			} elseif ( $accion == "guardar" ) {
                $nombre  = plain_text($_POST['nombre']);
                if(isset($_FILES['foto_perfil'])) {
	                if ( $_FILES['foto_perfil']['size'] > 35 ) {
		                redim( $_FILES['foto_perfil']['tmp_name'], '../images/usuarios/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['foto_perfil']['name'] ), 3 );
		                $foto_perfil = 'images/usuarios/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['foto_perfil']['name'] );
	                } else {
		                $foto_perfil = 'images/unknown.png';
	                }
                } else {
	                $foto_perfil = 'images/unknown.png';
                }
				$telefono = number($_POST['telefono']);
				$whatsapp = number($_POST['whatsapp']);
				if(Sanitize($_POST['email'],'email')) {
					$email = $_POST['email'];
				} else {
                    echo "El email es incorrecto";
                    die();
				}
				$web        = plain_text($_POST['web']);
				$facebook   = plain_text($_POST['facebook']);
				$instagram  = plain_text($_POST['instagram']);
				$behance    = plain_text($_POST['behance']);
				$resumen    = strip_tags($_POST['resumen']);
				$tipo       = number($_POST['tipo']);
				$contrasena = plain_text($_POST['contrasena']);
				$token      = token(10);
				$sql_check  = mysqli_query($link,"SELECT `id` FROM `usuarios` WHERE `email` = '".$email."'");
				if(mysqli_num_rows($sql_check) == 0) {
                    $sql = mysqli_query($link, "INSERT INTO `usuarios` ( nombre, foto_perfil, telefono, whatsapp, email, web, facebook, instagram, behance, resumen, tipo, contrasena, token) VALUES ('".$nombre."','".$foto_perfil."','".$telefono."','".$whatsapp."','".$email."','".$web."','".$facebook."','".$instagram."','".$behance."','".$resumen."','".$tipo."','".$contrasena."','".$token."')");
                    if($sql) {
                        $seccion = "response";
                        $titulo  = "Creabuc - Usuario creado";
                        $response['titulo'] = 'Correcto';
                        $response['contenido'] = 'El usuario fue creado correctamente';
                    } else {
	                    $seccion = "response";
	                    $titulo  = "Creabuc - Error";
	                    $response['titulo'] = 'Error';
	                    $response['contenido'] = 'Fallo al crear al usuario.';
                    }
				} else {
					$seccion = "response";
					$titulo  = "Creabuc - Error";
					$response['titulo'] = 'Error';
					$response['contenido'] = 'El correo ya existe.';
                }
			} elseif ( $accion == "editar" ) {
			    $titulo = "Creabuc - Editar usuario";
			    $id     = number($_GET['id']);
                $sql    = mysqli_query($link,"SELECT * FROM `usuarios` WHERE `id` = ".$id);
                $data   = mysqli_fetch_array($sql);
			} elseif ( $accion == "actualizar" ) {
			    $id     = number($_GET['id']);
				$nombre = plain_text($_POST['nombre']);
				if(isset($_FILES['foto_perfil'])) {
					if ( $_FILES['foto_perfil']['size'] > 35 ) {
						redim( $_FILES['foto_perfil']['tmp_name'], '../images/usuarios/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['foto_perfil']['name'] ), 4 );
						$foto_perfil = 'images/usuarios/' . normalizar( $nombre ) . '_' . normalizar( $_FILES['foto_perfil']['name'] );
					} else {
						$foto_perfil = 'images/unknown.png';
					}
				} else {
					$foto_perfil = 'images/unknown.png';
				}
				$telefono = number($_POST['telefono']);
				$whatsapp = number($_POST['whatsapp']);
				if(Sanitize($_POST['email'],'email')) {
					$email = $_POST['email'];
				} else {
					echo "El email es incorrecto";
					die();
				}
				$web = plain_text($_POST['web']);
				$facebook = plain_text($_POST['facebook']);
				$instagram = plain_text($_POST['instagram']);
				$behance = plain_text($_POST['behance']);
				$resumen = strip_tags($_POST['resumen']);
				$tipo = number($_POST['tipo']);
				if(strlen($_POST['contrasena']) >= 8) {
					$contrasena = plain_text( $_POST['contrasena'] );
				}
				$token = token(10);

				$sql_check =mysqli_query($link,"SELECT `id` FROM `usuarios` WHERE `email` = '".$email."'");
				if(mysqli_num_rows($sql_check)) {
					if ( $foto_perfil != null ) {
					    if(strlen($_POST['contrasena']) >= 8) {
						    $sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `foto_perfil` = '" . $foto_perfil . "', telefono = '" . $telefono . "', whatsapp = '" . $whatsapp . "', `email` = '" . $email . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `tipo` = '" . $tipo . "', `contrasena` = '" . $contrasena . "', `token` = '" . $token . "' WHERE `id` = " . $id );
					    } else {
						    $sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `foto_perfil` = '" . $foto_perfil . "', telefono = '" . $telefono . "', whatsapp = '" . $whatsapp . "', `email` = '" . $email . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `tipo` = '" . $tipo . "', `token` = '" . $token . "' WHERE `id` = " . $id );
                        }
					} else {
						if(strlen($_POST['contrasena']) >= 8) {
							$sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `telefono` = '" . $telefono . "', `whatsapp` = '" . $whatsapp . "', `email` = '" . $email . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `tipo` = '" . $tipo . "', `contrasena` = '" . $contrasena . "', `token` = '" . $token . "' WHERE `id` = " . $id );
						} else {
							$sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `telefono` = '" . $telefono . "', `whatsapp` = '" . $whatsapp . "', `email` = '" . $email . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `tipo` = '" . $tipo . "', `token` = '" . $token . "' WHERE `id` = " . $id );
                        }
					}
					if ( $sql ) {
						$seccion               = "response";
						$response['titulo']    = 'Correcto';
						$response['contenido'] = 'El usuario fue actualizado correctamente';
					} else {
						$seccion               = "response";
						$response['titulo']    = 'Error';
						$response['contenido'] = 'Fallo al actualizar al usuario.';
					}
				}
			} elseif ( $accion == "eliminar" ) {
			    $id = number($_GET['id']);
			    $sql_foto = mysqli_query($link, "SELECT `foto_perfil` FROM `usuarios` WHERE `id` = ".$id);
			    $usuario = mysqli_fetch_array($sql_foto);
			    if($usuario['foto_perfil'] != 'images/unknown.png') {
				    unlink( '../' . $usuario['foto_perfil'] );
			    }
			    $sql = mysqli_query($link, "DELETE FROM `usuarios` WHERE `id` = ".$id);
				if ( $sql ) {
					$seccion               = "response";
					$response['titulo']    = 'Correcto';
					$response['contenido'] = 'El usuario fue eliminado correctamente';
				} else {
					$seccion               = "response";
					$response['titulo']    = 'Error';
					$response['contenido'] = 'Fallo al eliminar al usuario.';
				}
            } elseif( $accion == "activar" ){
				$id = number($_GET['id']);
				$sql = mysqli_query($link, "UPDATE `usuarios` SET `activo` = 1 WHERE `id` = ".$id);
				if($sql){
					header("Location: index.php?seccion=usuarios");
				}
            } elseif( $accion == "inactivar" ){
				$id = number($_GET['id']);
				$sql = mysqli_query($link, "UPDATE `usuarios` SET `activo` = 0 WHERE `id` = ".$id);
				if($sql){
				    header("Location: index.php?seccion=usuarios");
                }
            }
		} else {
		    $seccion = "response";
		    $response['titulo'] = 'Error';
		    $response['contenido'] = 'No tienes permiso para usar ésta sección';
        }
		break;
	case "perfil":
		if($accion == "editar") {
		    $titulo = "Creabuc - Actualizar tu perfil";
            $sql = mysqli_query($link, "SELECT * FROM `usuarios` WHERE `id` = ".$_SESSION['id_user']);
            $data = mysqli_fetch_array($sql);
		} elseif($accion == "actualizar") {
			$nombre = plain_text($_POST['nombre']);
			if(isset($_FILES['foto_perfil'])) {
				if ( $_FILES['foto_perfil']['size'] > 35 ) {
					redim( $_FILES['foto_perfil']['tmp_name'], '../images/usuarios/' . $nombre . '_' . normalizar( $_FILES['foto_perfil']['name'] ), 4 );
					$foto_perfil = 'images/usuarios/' . $nombre . '_' . normalizar( $_FILES['foto_perfil']['name'] );
				} else {
					$foto_perfil = null;
				}
			} else {
				$foto_perfil = null;
			}
			$telefono = number($_POST['telefono']);
			$whatsapp = number($_POST['whatsapp']);
			$web = plain_text($_POST['web']);
			$facebook = plain_text($_POST['facebook']);
			$instagram = plain_text($_POST['instagram']);
			$behance = plain_text($_POST['behance']);
			$resumen = strip_tags($_POST['resumen']);
			$contrasena = plain_text($_POST['contrasena']);
			$token = token(10);

            if ( !is_null($foto_perfil) ) {
                if(strlen($contrasena) >= 8) {
	                $sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `foto_perfil` = '" . $foto_perfil . "', telefono = '" . $telefono . "', whatsapp = '" . $whatsapp . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `contrasena` = '" . md5( $contrasena ) . "', `token` = '" . $token . "' WHERE `id` = " . $_SESSION['id_user'] );
                } else {
	                $sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `foto_perfil` = '" . $foto_perfil . "', telefono = '" . $telefono . "', whatsapp = '" . $whatsapp . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `token` = '" . $token . "' WHERE `id` = " . $_SESSION['id_user'] );
                }
            } else {
                if(strlen($contrasena) >= 8) {
	                $sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `telefono` = '" . $telefono . "', `whatsapp` = '" . $whatsapp . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `contrasena` = '" . md5( $contrasena ) . "', `token` = '" . $token . "' WHERE `id` = " . $_SESSION['id_user'] );
                } else {
	                $sql = mysqli_query( $link, "UPDATE `usuarios` SET `nombre` = '" . $nombre . "', `telefono` = '" . $telefono . "', `whatsapp` = '" . $whatsapp . "', `web` = '" . $web . "', `facebook` = '" . $facebook . "', `instagram` = '" . $instagram . "', `behance` = '" . $behance . "', `resumen` = '" . $resumen . "', `token` = '" . $token . "' WHERE `id` = " . $_SESSION['id_user'] );
                }
            }
            if ( $sql ) {
                $seccion               = "response";
                $titulo                = "Creabuc - Usuario actualizado";
                $response['titulo']    = 'Correcto';
                $response['contenido'] = 'El usuario fue actualizado correctamente';
            } else {
                $seccion               = "response";
                $titulo                = "Creabuc - Error";
                $response['titulo']    = 'Error';
                $response['contenido'] = 'Fallo al actualizar al usuario.';
            }
		} elseif($accion == "eliminar") {
            $sql = mysqli_query($link, "DELETE FROM `usuarios` WHERE `id` = ". $_SESSION['id_user']);
            header("Location logout.php");
		}
		break;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?php echo $titulo; ?></title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="css/bootstrap-responsive.min.css" rel="stylesheet" type="text/css" />

    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet" type="text/css">
<?php if(($seccion == "dashboard" && $login == true) || $seccion == "registro"){ ?>
    <link href="css/pages/signin.css" rel="stylesheet" type="text/css">
<?php } ?>
</head>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">

            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>

            <a class="brand" href="index.php">
                Creabuc - Administración
            </a>

<?php if($_SESSION['logged'] == "si"){ ?>
            <div class="nav-collapse">
                <ul class="nav pull-right">

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-user"></i>
							<?php echo $_SESSION['nombre_user']; ?>
                            <b class="caret"></b>
                        </a>

                        <ul class="dropdown-menu">
                            <li><a href="index.php?seccion=perfil&accion=editar">Perfil</a></li>
                            <li><a href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>

            </div><!--/.nav-collapse -->
<?php } ?>

        </div> <!-- /container -->

    </div> <!-- /navbar-inner -->

</div> <!-- /navbar -->




<?php if($_SESSION['logged'] == "si") { ?>
<div class="subnavbar">

    <div class="subnavbar-inner">

        <div class="container">

            <ul class="mainnav">

                <li>
                    <a href="index.php">
                        <i class="icon-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>



                <li>
                    <a href="index.php?seccion=albumes">
                        <i class="icon-picture"></i>
                        <span><?php if($_SESSION['tipo_user'] == 2){ ?>Mis <?php } ?>Albumes</span>
                    </a>
                </li>
				<?php if($_SESSION['tipo_user'] == 1) { ?>
                    <li>
                        <a href="index.php?seccion=categorias">
                            <i class="icon-list"></i>
                            <span>Categorías</span>
                        </a>
                    </li>

                    <li>
                        <a href="index.php?seccion=usuarios">
                            <i class="icon-group"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
				<?php } ?>
            </ul>

        </div> <!-- /container -->

    </div> <!-- /subnavbar-inner -->

</div> <!-- /subnavbar -->
<?php } ?>

<div class="main">

<?php if($seccion == "response"){ ?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget">

                            <div class="widget-header">
                                <i class="<?php if($response['titulo'] == "Correcto") {?> icon-check<?php } else { ?>icon-remove<?php } ?>"></i>
                                <h3><?php echo $response['titulo']; ?></h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <p><?php echo $response['contenido']; ?></p>

                            </div> <!-- /widget-content -->

                        </div> <!-- /widget -->

                    </div> <!-- /spa12 -->

                </div> <!-- /row -->

            </div> <!-- /container -->

        </div> <!-- /main-inner -->
<?php }
if($seccion == "dashboard" && $login == true){ ?>
<div class="account-container">

	<div class="content clearfix">

		<form action="login.php" method="post">

			<h1>Login</h1>

			<div class="login-fields">

				<p>Ingresa tus datos de acceso, si no estás registrado, <a href="index.php?seccion=registro">registrate aquí</a>.</p>

				<div class="field">
					<label for="user">Email:</label>
					<input type="text" id="usuario" name="usuario" value="" placeholder="Email" class="login username-field<?php if($user_err == true){ ?> input-danger<?php } ?>" />
				</div> <!-- /field -->

				<div class="field">
					<label for="contrasena">Password:</label>
					<input type="password" id="contrasena" name="contrasena" value="" placeholder="Contraseña" class="login password-field<?php if($pass_err == true){ ?> input-danger<?php } ?>"/>
				</div> <!-- /password -->

			</div> <!-- /login-fields -->

			<div class="login-actions">

				<button class="button btn btn-success btn-large">Entrar</button>

			</div> <!-- .actions -->



		</form>

	</div> <!-- /content -->

</div> <!-- /account-container -->



<!--<div class="login-extra">
	<a href="#">Recuperar contraseña</a>
</div> <!-- /login-extra -->


<?php } elseif ($seccion == "registro") { ?>
    <div class="account-container register">

        <div class="content clearfix">

            <form action="index.php?seccion=registrar" method="post" enctype="multipart/form-data">

                <h1>Registro</h1>

                <div class="login-fields">

                    <p>Crea tu cuenta gratuita:</p>

                    <div class="field">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="" placeholder="Nombre completo" class="login" />
                        <p>*Obligatorio. Escribe tus nombres y un apellido</p>
                    </div> <!-- /field -->

                    <div class="field">
                        <label for="telefono">Last Name:</label>
                        <input type="number" id="telefono" name="telefono" value="" placeholder="Teléfono" class="login" />
                        <p>*Obligatorio. Solo números sin código de país, en caso de ser fijo, poner 037(el último dígito depende del indicativo del departamento) antes del número.</p>
                    </div> <!-- /field -->


                    <div class="field">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="" placeholder="Email" class="login"/>
                        <p>*Obligatorio. Debe ser un email válido.</p>
                    </div> <!-- /field -->

                    <div class="field">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" value="" placeholder="Contraseña" class="login"/>
                        <p>Debe tener al menos 8 caracteres.</p>
                    </div> <!-- /field -->

                    <div class="field">
                        <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" value="" placeholder="Confirmar Contraseña" class="login"/>
                        <p>Debe coincidir con la que escribiste arriba.</p>
                    </div> <!-- /field -->

                </div> <!-- /login-fields -->

                <div class="login-actions">

                    <button class="button btn btn-primary btn-large">Registrarme</button>

                </div> <!-- .actions -->

            </form>

        </div> <!-- /content -->

    </div> <!-- /account-container -->


           <!-- Text Under Box -->
    <div class="login-extra">
        ¿Ya tienes una cuenta? <a href="index.php">Ingresa aquí</a>
    </div> <!-- /login-extra -->
<?php }  elseif($_SESSION['logged'] == 'si' && $login == false){ ?>
    <div class="main-inner">
        <div class="container">
            <div class="row">
                <div class="span12">
                    <div class="widget widget-nopad">
                        <div class="widget-header"><i class="icon-list-alt"></i>
                            <h3> Bienvenido</h3>
                        </div>
                        <!-- /widget-header -->
                        <div class="widget-content">
                            <div class="widget big-stats-container">
                                <div class="widget-content">
                                    <h6 class="bigstats">Aquí puedes crear, editar y eliminar tus albumes y datos personales, para modificar tu perfil despliega el menú que está en la parte superior derecha bajo tu nombre.</h6>
                                </div>
                                <!-- /widget-content -->

                            </div>
                        </div>
                    </div>
                    <!-- /widget -->
                </div>
                <!-- /span12 -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /main-inner -->
<?php }
if($seccion == "albumes") {
    if($accion == "listar"){ ?>
        <div class="main-inner">

            <div class="container">

                <a href="index.php?seccion=albumes&accion=crear" class="btn btn-primary">Subir un album</a>

                <div class="row">

                    <div class="span12">

                        <!-- /widget -->
                        <div class="widget widget-table action-table">
                            <div class="widget-header">
                                <i class="icon-picture"></i>
                                <?php if($_SESSION['tipo_user'] == 2) { ?>
                                    <h3>Mis Albumes</h3>
                                <?php } elseif ($_SESSION['tipo_user'] == 1){ ?>
                                    <h3>Albumes</h3>
                                <?php } ?>
                            </div>
                            <!-- /widget-header -->
                            <div class="widget-content">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th> Título </th>
                                        <th> Categoría </th>
                                        <th> Usuario </th>
                                        <th class="td-actions"> </th>
                                    </tr>
                                    </thead>
                                    <tbody>
<?php while($album = mysqli_fetch_array($sql)){ ?>
                                    <tr>
                                        <td><?php echo $album['nombre_album']; ?></td>
                                        <td><?php echo $album['nombre_categoria']; ?></td>
                                        <td><?php echo $album['nombre_usuario']; ?></td>
                                        <td class="td-actions"><a href="index.php?seccion=albumes&accion=editar&id=<?php echo $album['id_album']; ?>" class="btn btn-small btn-success"><i class="btn-icon-only icon-pencil"> </i></a><a href="index.php?seccion=albumes&accion=eliminar&id=<?php echo $album['id_album']; ?>" class="btn btn-danger btn-small"><i class="btn-icon-only icon-remove"> </i></a></td>
                                    </tr>
<?php } ?>

                                    </tbody>
                                </table>
                            </div>
                            <!-- /widget-content -->
                        </div>
                        <!-- /widget -->

                    </div> <!-- /spa12 -->

                </div> <!-- /row -->

            </div> <!-- /container -->

        </div> <!-- /main-inner -->

    <?php } elseif($accion == "crear"){ ?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget ">

                            <div class="widget-header">
                                <i class="icon-user"></i>
                                <h3>Subir album</h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <form id="edit-profile" class="form-horizontal" action="index.php?seccion=albumes&accion=guardar" method="post" enctype="multipart/form-data">
                                    <fieldset>
<?php if($_SESSION['tipo_user'] == 2) { ?>
                                        <div class="control-group">
                                            <label class="control-label" for="usuario">Usuario</label>
                                            <div class="controls">
                                                <select class="span6" id="usuario" name="usuario">
<?php while($usuario = mysqli_fetch_array($usuarios)){ ?>
                                                    <option value="<?php echo $usuario['id']; ?>"<?php if($usuario['id'] == $_SESSION['id_user']){ ?> selected<?php } ?>><?php echo $usuario['nombre']; ?></option>
<?php } ?>
                                                </select>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->
<?php } ?>

                                        <div class="control-group">
                                            <label class="control-label" for="categoria">Categoría</label>
                                            <div class="controls">
                                                <select class="span6" id="categoria" name="categoria">
                                                        <option value="0" disabled>Selecciona una categoría</option>
				                                    <?php while($categoria = mysqli_fetch_array($categorias)){ ?>
                                                        <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
				                                    <?php } ?>
                                                        <option value="999">Otra</option>
                                                </select>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="nombre">Título</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="nombre" name="nombre" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="miniatura">Miniatura</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="miniatura" name="miniatura" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="descripcion">Descripción</label>
                                            <div class="controls">
                                                <textarea class="span6" id="descripcion" name="descripcion"></textarea>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="imagen1">Imagen 1</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="imagen1" name="imagen1">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="imagen2">Imagen 2</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="imagen2" name="imagen2">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="imagen3">Imagen 3</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="imagen3" name="imagen3">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="imagen4">Imagen 4</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="imagen4" name="imagen4">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="imagen5">Imagen 5</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="imagen5" name="imagen5">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label">Activa</label>


                                            <div class="controls">
                                                <label class="checkbox inline">
                                                    <input type="checkbox" name="activo" value="true"> Activar
                                                </label>
                                            </div>		<!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div> <!-- /form-actions -->

                                    </fieldset>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    <?php } elseif($accion == "editar"){ ?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget ">

                            <div class="widget-header">
                                <i class="icon-user"></i>
                                <h3>Subir album</h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <form id="edit-profile" class="form-horizontal" action="index.php?seccion=albumes&accion=actualizar&id=<?php echo $data['id_album']; ?>" method="post" enctype="multipart/form-data">
                                    <fieldset>
									    <?php if($_SESSION['tipo_user'] == 1) { ?>
                                            <div class="control-group">
                                                <label class="control-label" for="usuario">Usuario</label>
                                                <div class="controls">
                                                    <select class="span6" id="usuario" name="usuario">
													    <?php while($usuario = mysqli_fetch_array($usuarios)){ ?>
                                                            <option value="<?php echo $usuario['id']; ?>"<?php if($usuario['id'] == $data['id_usuario']){ ?> selected<?php } ?>><?php echo $usuario['nombre']; ?></option>
													    <?php } ?>
                                                    </select>
                                                </div> <!-- /controls -->
                                            </div> <!-- /control-group -->
									    <?php } ?>

                                        <div class="control-group">
                                            <label class="control-label" for="categoria">Categoría</label>
                                            <div class="controls">
                                                <select class="span6" id="categoria" name="categoria">
                                                    <option value="0" disabled>Selecciona una categoría</option>
												    <?php while($categoria = mysqli_fetch_array($categorias)){ ?>
                                                        <option value="<?php echo $categoria['id']; ?>"<?php if($data['id_categoria']) { ?> selected<?php } ?>><?php echo $categoria['nombre']; ?></option>
												    <?php } ?>
                                                    <option value="999">Otra</option>
                                                </select>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="nombre">Título</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="nombre" name="nombre" value="<?php echo $data['nombre_album']; ?>">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="miniatura">Miniatura</label>
                                            <div class="controls">
                                                <img src="../<?php echo $data['miniatura']; ?>">
                                                <input type="file" class="span6" id="miniatura" name="miniatura" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="descripcion">Descripción</label>
                                            <div class="controls">
                                                <textarea class="span6" id="descripcion" name="descripcion"><?php echo $data['descripcion']; ?></textarea>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

<?php $cont = 1; while($imagen = mysqli_fetch_array($sql_imagenes)) { ?>
                                        <div class="control-group">
                                            <label class="control-label" for="imagen<?php echo $cont; ?>">Imagen <?php echo $cont; ?></label>
                                            <div class="controls">
                                                <input type="hidden" name="id_imagen<?php echo $cont; ?>" value="<?php echo $imagen['id']; ?>">
                                                <img src="../<?php echo $imagen['ruta_mini']; ?>">
                                                <input type="file" class="span6" id="imagen<?php echo $cont; ?>" name="imagen<?php echo $cont; ?>">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->
<?php ++$cont; } ?>

                                        <div class="control-group">
                                            <label class="control-label">Activa</label>


                                            <div class="controls">
                                                <label class="checkbox inline">
                                                    <input type="checkbox" name="activo" value="true"<?php if($data['activo'] == 1){ ?> checked<?php } ?>> Activar
                                                </label>
                                            </div>		<!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                        </div> <!-- /form-actions -->

                                    </fieldset>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    <?php } ?>
<?php } elseif($seccion == "categorias" && $_SESSION['tipo_user'] == 1){
    if($accion == "listar"){ ?>
    <div class="main-inner">

        <div class="container">

            <a href="index.php?seccion=categorias&accion=crear" class="btn btn-primary">Crear categoría</a>

            <div class="row">

                <div class="span12">

                    <!-- /widget -->
                    <div class="widget widget-table action-table">
                        <div class="widget-header">
                            <i class="icon-picture"></i>
                                <h3>Categorias</h3>
                        </div>
                        <!-- /widget-header -->
                        <div class="widget-content">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th> Nombre </th>
                                    <th> Creador </th>
                                    <th class="td-actions"> </th>
                                </tr>
                                </thead>
                                <tbody>
								<?php while($categoria = mysqli_fetch_array($sql)){ ?>
                                    <tr>
                                        <td><?php echo $categoria['nombre_categoria']; ?></td>
                                        <td><?php echo $categoria['nombre_usuario']; ?></td>
                                        <td class="td-actions"><a href="index.php?seccion=categorias&accion=editar&id=<?php echo $categoria['id_categoria']; ?>" class="btn btn-small btn-success"><i class="btn-icon-only icon-pencil"> </i></a><a href="index.php?seccion=categorias&accion=eliminar&id=<?php echo $categoria['id_categoria']; ?>" class="btn btn-danger btn-small"><i class="btn-icon-only icon-remove"> </i></a></td>
                                    </tr>
								<?php } ?>

                                </tbody>
                            </table>
                        </div>
                        <!-- /widget-content -->
                    </div>
                    <!-- /widget -->

                </div> <!-- /spa12 -->

            </div> <!-- /row -->

        </div> <!-- /container -->

    </div> <!-- /main-inner -->

	<?php } elseif($accion == "crear"){ ?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget ">

                            <div class="widget-header">
                                <i class="icon-user"></i>
                                <h3>Crear categoría</h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <form id="edit-profile" class="form-horizontal" action="index.php?seccion=categorias&accion=guardar" method="post" enctype="multipart/form-data">
                                    <fieldset>

                                        <div class="control-group">
                                            <label class="control-label" for="nombre">Nombre</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="nombre" name="nombre" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="miniatura">Miniatura</label>
                                            <div class="controls">
                                                <input type="file" class="span6" id="miniatura" name="miniatura" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label">Activa</label>


                                            <div class="controls">
                                                <label class="checkbox inline">
                                                    <input type="checkbox" name="activa" value="true"> Activar
                                                </label>
                                            </div>		<!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div> <!-- /form-actions -->

                                    </fieldset>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
	<?php } elseif($accion == "editar"){ ?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget ">

                            <div class="widget-header">
                                <i class="icon-user"></i>
                                <h3>Crear categoría</h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <form id="edit-profile" class="form-horizontal" action="index.php?seccion=categorias&accion=actualizar&id=<?php echo $data['id']; ?>" method="post" enctype="multipart/form-data">
                                    <fieldset>

                                        <div class="control-group">
                                            <label class="control-label" for="nombre">Nombre</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="nombre" name="nombre" value="<?php echo $data['nombre']; ?>">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="miniatura">Miniatura</label>
                                            <div class="controls">
                                                <img src="../<?php echo $data['miniatura']; ?>">
                                                <input type="file" class="span6" id="miniatura" name="miniatura">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label">Activa</label>


                                            <div class="controls">
                                                <label class="checkbox inline">
                                                    <input type="checkbox" name="activa" value="true"<?php if($data['activa']) {?> checked<?php } ?>> Activar
                                                </label>
                                            </div>		<!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                        </div> <!-- /form-actions -->

                                    </fieldset>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
	<?php } ?>
<?php } elseif($seccion == "usuarios" && $_SESSION['tipo_user'] == 1){
    if($accion == "listar"){ ?>
    <div class="main-inner">

        <div class="container">

            <a href="index.php?seccion=usuarios&accion=crear" class="btn btn-primary">Nuevo usuario</a>

            <div class="row">

                <div class="span12">

                    <!-- /widget -->
                    <div class="widget widget-table action-table">
                        <div class="widget-header">
                            <i class="icon-group"></i>
                            <h3>Usuarios</h3>
                        </div>
                        <!-- /widget-header -->
                        <div class="widget-content">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th> Nombre </th>
                                    <th> Email </th>
                                    <th> Fecha registro </th>
                                    <th class="td-actions"> </th>
                                </tr>
                                </thead>
                                <tbody>
								<?php while($usuario = mysqli_fetch_array($sql)){ ?>
                                    <tr>
                                        <td><?php echo $usuario['nombre']; ?></td>
                                        <td><?php echo $usuario['email']; ?></td>
                                        <td><?php echo $usuario['fecha_registro']; ?></td>
                                        <td class="td-actions"><a href="index.php?seccion=usuarios&accion=editar&id=<?php echo $usuario['id']; ?>" class="btn btn-small btn-success"><i class="btn-icon-only icon-pencil"> </i></a><a href="index.php?seccion=usuarios&accion=eliminar&id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-small"><i class="btn-icon-only icon-remove"> </i></a></td>
                                    </tr>
								<?php } ?>

                                </tbody>
                            </table>
                        </div>
                        <!-- /widget-content -->
                    </div>
                    <!-- /widget -->

                </div> <!-- /spa12 -->

            </div> <!-- /row -->

        </div> <!-- /container -->

    </div> <!-- /main-inner -->
    <?php } elseif($accion == "crear"){ ?>
    <div class="main-inner">

        <div class="container">

            <div class="row">

                <div class="span12">

                    <div class="widget ">

                        <div class="widget-header">
                            <i class="icon-user"></i>
                            <h3>Crear usuario</h3>
                        </div> <!-- /widget-header -->

                        <div class="widget-content">

                            <form id="edit-profile" class="form-horizontal" action="index.php?seccion=usuarios&accion=guardar" method="post" enctype="multipart/form-data">
                                <fieldset>

                                    <div class="control-group">
                                        <label class="control-label" for="nombre">Nombre</label>
                                        <div class="controls">
                                            <input type="text" class="span6" id="nombre" name="nombre" value="">
                                            <p class="help-block">Usa dos (o uno) nombres y un apellido.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="foto_perfil">Foto de perfil</label>
                                        <div class="controls">
                                            <input type="file" class="span6" id="foto_perfil" name="foto_perfil" value="">
                                            <p class="help-block">Puedes subir una foto tuya o un logo.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="telefono">Teléfono</label>
                                        <div class="controls">
                                            <input type="text" class="span6" id="telefono" name="telefono" value="">
                                            <p class="help-block">Puede ser un celular sin código de país, o un fijo con 03X antes del número fijo.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="whatsapp">Whatsapp</label>
                                        <div class="controls">
                                            <input type="text" class="span4" id="whatsapp" name="whatsapp" value="">
                                            <p class="help-block">Opcional. Debes escribir el número sin código de país, solo funcionan números de colombia.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="email">Email</label>
                                        <div class="controls">
                                            <input type="text" class="span4" id="email" name="email" value="">
                                            <p class="help-block">Revísalo bien, no lo podrás cambiar más adelante sin ayuda de un administrador.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="web">Web</label>
                                        <div class="controls">
                                            <input type="text" class="span4" id="web" name="web" value="">
                                            <p class="help-block">Opcional. Si tu web usa SSL pon 'https://' antes de la dirección web.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="facebook">Facebook</label>
                                        <div class="controls">
                                            <input type="text" class="span4" id="facebook" name="facebook" value="">
                                            <p class="help-block">Opcional. Copia y pega la url de tu perfil o página.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="instagram">Instagram</label>
                                        <div class="controls">
                                            <input type="text" class="span4" id="instagram" name="instagram" value="">
                                            <p class="help-block">Opcional. Copia y pega la url de tu perfil.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="behance">Behance</label>
                                        <div class="controls">
                                            <input type="text" class="span4" id="behance" name="behance" value="">
                                            <p class="help-block">Opcional. Copia y pega la url de tu perfil.</p>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="resumen">Resumen profesional</label>
                                        <div class="controls">
                                            <textarea class="span10" id="resumen" name="resumen" rows="7"></textarea>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="tipo">Contraseña</label>
                                        <div class="controls">
                                            <select class="span4" id="tipo" name="tipo">
                                                <option value="1">Administrador</option>
                                                <option value="2" selected>Usuario</option>
                                            </select>
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="contrasena">Contraseña</label>
                                        <div class="controls">
                                            <input type="password" class="span4" id="contrasena" name="contrasena" value="">
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="control-group">
                                        <label class="control-label" for="confirmar_contrasena">Confirmar contraseña</label>
                                        <div class="controls">
                                            <input type="password" class="span4" id="confirmar_contrasena" name="confirmar_contrasena" value="">
                                        </div> <!-- /controls -->
                                    </div> <!-- /control-group -->

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div> <!-- /form-actions -->

                                </fieldset>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
<?php } elseif($accion == "editar"){ ?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget ">

                            <div class="widget-header">
                                <i class="icon-user"></i>
                                <h3>Editar usuario</h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <form id="edit-profile" class="form-horizontal" action="index.php?seccion=usuarios&accion=actualizar&id=<?php echo $data['id']; ?>" method="post" enctype="multipart/form-data">
                                    <fieldset>

                                        <div class="control-group">
                                            <label class="control-label" for="nombre">Nombre</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="nombre" name="nombre" value="<?php echo $data['nombre']; ?>">
                                                <p class="help-block">Usa dos (o uno) nombres y un apellido.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="foto_perfil">Foto de perfil</label>
                                            <div class="controls">
                                                <img src="../<?php echo $data['foto_perfil']; ?>" />
                                                <input type="file" class="span6" id="foto_perfil" name="foto_perfil" value="">
                                                <p class="help-block">Puedes subir una foto tuya o un logo.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="telefono">Teléfono</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="telefono" name="telefono" value="<?php echo $data['telefono']; ?>">
                                                <p class="help-block">Puede ser un celular sin código de país, o un fijo con 03X antes del número fijo.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="whatsapp">Whatsapp</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="whatsapp" name="whatsapp" value="<?php echo $data['whatsapp']; ?>">
                                                <p class="help-block">Opcional. Debes escribir el número sin código de país, solo funcionan números de colombia.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="email">Email</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="email" name="email" value="<?php echo $data['email']; ?>">
                                                <p class="help-block">Revísalo bien, no lo podrás cambiar más adelante sin ayuda de un administrador.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="web">Web</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="web" name="web" value="<?php echo $data['web']; ?>">
                                                <p class="help-block">Opcional. Si tu web usa SSL pon 'https://' antes de la dirección web.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="facebook">Facebook</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="facebook" name="facebook" value="<?php echo $data['facebook']; ?>">
                                                <p class="help-block">Opcional. Copia y pega la url de tu perfil o página.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="instagram">Instagram</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="instagram" name="instagram" value="<?php echo $data['instagram']; ?>">
                                                <p class="help-block">Opcional. Copia y pega la url de tu perfil.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="behance">Behance</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="behance" name="behance" value="<?php echo $data['behance']; ?>">
                                                <p class="help-block">Opcional. Copia y pega la url de tu perfil.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="resumen">Resumen profesional</label>
                                            <div class="controls">
                                                <textarea class="span10" id="resumen" name="resumen" rows="7"><?php echo $data['resumen']; ?></textarea>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="tipo">Contraseña</label>
                                            <div class="controls">
                                                <select class="span4" id="tipo" name="tipo">
                                                    <option value="1"<?php if($data['tipo'] == 1){ ?> selected<?php } ?>>Administrador</option>
                                                    <option value="2"<?php if($data['tipo'] == 2){ ?> selected<?php } ?>>Usuario</option>
                                                </select>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="contrasena">Contraseña</label>
                                            <div class="controls">
                                                <input type="password" class="span4" id="contrasena" name="contrasena" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="confirmar_contrasena">Confirmar contraseña</label>
                                            <div class="controls">
                                                <input type="password" class="span4" id="confirmar_contrasena" name="confirmar_contrasena" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                        </div> <!-- /form-actions -->

                                    </fieldset>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    <?php } ?>
<?php } elseif ($seccion == "perfil"){
    if($accion == "editar") {?>
        <div class="main-inner">

            <div class="container">

                <div class="row">

                    <div class="span12">

                        <div class="widget ">

                            <div class="widget-header">
                                <i class="icon-user"></i>
                                <h3>Tu perfil</h3>
                            </div> <!-- /widget-header -->

                            <div class="widget-content">

                                <form id="edit-profile" class="form-horizontal" action="index.php?seccion=perfil&accion=actualizar" method="post" enctype="multipart/form-data">
                                    <fieldset>

                                        <div class="control-group">
                                            <label class="control-label" for="nombre">Nombre</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="nombre" name="nombre" value="<?php echo $data['nombre']; ?>">
                                                <p class="help-block">Usa dos (o uno) nombres y un apellido.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="foto_perfil">Foto de perfil</label>
                                            <div class="controls">
                                                <img src="../<?php echo $data['foto_perfil']; ?>">
                                                <input type="file" class="span6" id="foto_perfil" name="foto_perfil">
                                                <p class="help-block">Puedes subir una foto tuya o un logo.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="telefono">Teléfono</label>
                                            <div class="controls">
                                                <input type="text" class="span6" id="telefono" name="telefono" value="<?php echo $data['telefono']; ?>">
                                                <p class="help-block">Puede ser un celular sin código de país, o un fijo con 03X antes del número fijo.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="whatsapp">Whatsapp</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="whatsapp" name="whatsapp" value="<?php echo $data['whatsapp']; ?>">
                                                <p class="help-block">Opcional. Debes escribir el número sin código de país, solo funcionan números de colombia.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="email">Email</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="email" name="email" value="<?php echo $data['email']; ?>" disabled>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="web">Web</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="web" name="web" value="<?php echo $data['web']; ?>">
                                                <p class="help-block">Opcional. Si tu web usa SSL pon 'https://' antes de la dirección web.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="facebook">Facebook</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="facebook" name="facebook" value="<?php echo $data['facebook']; ?>">
                                                <p class="help-block">Opcional. Copia y pega la url de tu perfil o página.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="instagram">Instagram</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="instagram" name="instagram" value="<?php echo $data['instagram']; ?>">
                                                <p class="help-block">Opcional. Copia y pega la url de tu perfil.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="behance">Behance</label>
                                            <div class="controls">
                                                <input type="text" class="span4" id="behance" name="behance" value="<?php echo $data['behance']; ?>">
                                                <p class="help-block">Opcional. Copia y pega la url de tu perfil.</p>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="resumen">Resumen profesional</label>
                                            <div class="controls">
                                                <textarea class="span10" id="resumen" name="resumen" rows="7"><?php echo $data['resumen']; ?></textarea>
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="contrasena">Contraseña</label>
                                            <div class="controls">
                                                <input type="password" class="span4" id="contrasena" name="contrasena" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="control-group">
                                            <label class="control-label" for="confirmar_contrasena">Confirmar contraseña</label>
                                            <div class="controls">
                                                <input type="password" class="span4" id="confirmar_contrasena" name="confirmar_contrasena" value="">
                                            </div> <!-- /controls -->
                                        </div> <!-- /control-group -->

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                        </div> <!-- /form-actions -->

                                    </fieldset>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    <?php } ?>
<?php } ?>
</div>

<div class="footer">

    <div class="footer-inner">

        <div class="container">

            <div class="row">

                <div class="span12">
                    &copy; 2018 <a href="../index.php">Creabuc</a>.
                </div> <!-- /span12 -->

            </div> <!-- /row -->

        </div> <!-- /container -->

    </div> <!-- /footer-inner -->

</div> <!-- /footer -->


<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery-1.7.2.min.js"></script>

<script src="js/bootstrap.js"></script>
<script src="js/base.js"></script>

<?php if(($seccion == "dashboard" && $login == true) || $seccion == "registro"){ ?>
    <script src="js/signin.js"></script>
<?php } ?>

</body>

</html>
