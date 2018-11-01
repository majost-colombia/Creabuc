<?php
session_start();
$_SESSION['logged'] = "no";
require('conexion.php');
require('admin/sanity.php');

$rating_classes = array("featured-rating","featured-rating-orange","featured-rating-green");

function get_projects($id,$link) {
    $sql_totales = mysqli_query($link,"SELECT COUNT(*) total FROM `albumes` WHERE `categoria`=".$id);
    $total = mysqli_fetch_array($sql_totales);
    return $total['total'];
}

if (isset($_GET['seccion'])) {
    $seccion = plain_text($_GET['seccion']);
} else {
    $seccion = 'inicio';
}

if (isset($_GET['categoria'])) {
    $categoria = number($_GET['categoria']);
} elseif (isset($_POST['categoria'])) {
    $categoria = number($_POST['categoria']);
} else {
    $categoria = null;
}

if (isset($_GET['perfil'])) {
    $perfil = number($_GET['perfil']);
}

if (isset($_GET['album'])) {
    $album = number($_GET['album']);
}

switch($seccion) {
    case "inicio":
        $titulo = 'Directorio profesionales de diseño UDI';
        $sql_categorias = mysqli_query($link,"SELECT * FROM `categorias` WHERE `activa` = 1");
        $sql_count = mysqli_query($link,"SELECT `categoria` FROM `albumes` WHERE `activo` = 1 GROUP BY `categoria` ORDER BY COUNT(*) DESC LIMIT 0,3");
        while($counted = mysqli_fetch_array($sql_count)){
            $temp_ids[] = $counted[0];
        }
        $ids = implode(",",$temp_ids);
        $sql_populares = mysqli_query($link,"SELECT * FROM `categorias` WHERE id IN (".$ids.")");
        break;
    case "categoria":
        $titulo = "Categoría: ".plain_text($_GET['nombre_categoria']);
	    $sql_categorias = mysqli_query($link,"SELECT * FROM `categorias` WHERE `activa` = 1");
	    $sql_albumes = mysqli_query($link, "SELECT *, `albumes`.`id` id_album, `usuarios`.`id` id_usuario, `albumes`.`nombre` nombre_album, `usuarios`.`nombre` nombre_usuario FROM `albumes`,`usuarios` WHERE `albumes`.`categoria` = ".$categoria . " AND `albumes`.`activo` = 1 AND `usuarios`.`id` = `albumes`.`usuario` ORDER BY `albumes`.`id` DESC");
	    $resultados = mysqli_num_rows($sql_albumes);
        break;
    case "album":
        $titulo = plain_text($_GET['nombre_album'])." de ".plain_text($_GET['nombre_perfil']);
	    $sql_categorias = mysqli_query($link,"SELECT * FROM `categorias` WHERE `activa` = 1");
	    $sql_album = mysqli_query($link, "SELECT *, `albumes`.`id` id_album, `usuarios`.`id` id_usuario, `albumes`.`nombre` nombre_album, `usuarios`.`nombre` nombre_usuario FROM `albumes`,`usuarios` WHERE `usuarios`.`id` = `albumes`.`usuario` AND `albumes`.`activo` = 1 AND `albumes`.`id` = ".$album);
	    $data_album = mysqli_fetch_array($sql_album);
	    $sql_fotos = mysqli_query($link, "SELECT * FROM `fotos` WHERE `padre` = ".$album." AND `activo` = 1");
	    break;
    case "perfil":
        $titulo = plain_text($_GET['nombre_perfil']);
	    $sql_categorias = mysqli_query($link,"SELECT * FROM `categorias` WHERE `activa` = 1");
        $sql_usuario = mysqli_query($link, "SELECT * FROM `usuarios` WHERE `usuarios`.`id` = ".$perfil);
        $data_usuario = mysqli_fetch_array($sql_usuario);
	    break;
    default:
        echo ("¿Qué carajo intentas? muchacho/a");
        die();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Jose Luis Silva Calderón">
    <meta name="description" content="Guía profesional UDI">
    <!-- Page Title -->
    <title><?php echo $titulo; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,400i,500,700,900" rel="stylesheet">
    <!-- Simple line Icon -->
    <link rel="stylesheet" href="css/simple-line-icons.css">
    <!-- Themify Icon -->
    <link rel="stylesheet" href="css/themify-icons.css">
    <!-- Hover Effects -->
    <link rel="stylesheet" href="css/set1.css">
<?php if ($seccion == "album") { ?>
    <!-- Swipper Slider -->
    <link rel="stylesheet" href="css/swiper.min.css">
    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="css/magnific-popup.css">
<?php } ?>
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css?v=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>

<body>
<?php if ($seccion == "inicio") { ?>
    <!--============================= HEADER =============================-->
    <div class="nav-menu">
        <div class="bg transition">
            <div class="container-fluid fixed">
                <div class="row">
                    <div class="col-md-12">
                        <nav class="navbar navbar-expand-lg navbar-light">
                            <a class="navbar-brand" href="index.php">Creabuc</a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="icon-menu"></span>
              </button>
                            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                                <ul class="navbar-nav">
                                    <li><a href="admin/index.php" class="btn btn-outline-light top-btn"><span class="ti-plus"></span><?php if ($_SESSION['logged'] == "si") { echo $_SESSION['nombre_user']; } else { ?> Login/Registro<?php } ?></a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SLIDER -->
    <section class="slider d-flex align-items-center">
        <!-- <img src="images/slider.jpg" class="img-fluid" alt="#"> -->
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-md-12">
                    <div class="slider-title_box">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="slider-content_wrap">
                                    <h1>Encuentra profesionales en diseño</h1>
                                    <h5>En la Universidad de Desarrollo e Investigación.</h5>
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-10">
                                <form class="form-wrap mt-4" action="" enctype="multipart/form-data" id="form_busqueda" method="post">
                                    <div class="btn-group" role="group" aria-label="Buscador">
                                        <select class="btn-group1" id="buscador" name="categoria">
                                            <option value="0" disabled selected>¿Cual categoría deseas buscar?</option>
<?php while ($categoria = mysqli_fetch_array($sql_categorias)) { ?>
                                            <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
<?php } ?>
                                        </select>
                                        <button type="submit" class="btn-form" id="buscar"><span class="icon-magnifier search-icon"></span>Buscar<i class="pe-7s-angle-right"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--// SLIDER -->
    <!--//END HEADER -->
    <!--============================= FEATURED PLACES =============================-->
    <section class="main-block light-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="styled-heading">
                        <h3>Categorías populares</h3>
                    </div>
                </div>
            </div>
            <div class="row">
<?php while ($popular = mysqli_fetch_array($sql_populares)) { ?>
                <div class="col-md-4 featured-responsive">
                    <div class="featured-place-wrap">
                        <a href="index.php?seccion=categoria&categoria=<?php echo $popular['id']; ?>&nombre_categoria=<?php echo ucfirst($popular['nombre']); ?>">
<?php if ($popular['miniatura'] == '') { ?>
                            <img src="images/default-categorias.png" class="img-fluid" alt="<?php echo $popular['nombre']; ?>">
<?php } else { ?>
                            <img src="<?php echo $popular['miniatura']; ?>" class="img-fluid" alt="<?php echo $popular['nombre']; ?>">
<?php } ?>
                            <span class="<?php echo $rating_classes[rand(0,2)]; ?>"><?php echo get_projects($popular['id'],$link); ?></span>
                            <div class="featured-title-box">
                                <h6><?php echo $popular['nombre']; ?></h6>
                            </div>
                        </a>
                    </div>
                </div>
<?php } ?>
            </div>
        </div>
    </section>
    <!--//END FEATURED PLACES -->
<?php } elseif ($seccion == 'categoria') { ?>
    <!--============================= HEADER =============================-->
    <div class="dark-bg sticky-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand" href="index.php">Creabuc</a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="icon-menu"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <form class="filter-dropdown" action="" id="form_busqueda" method="post" enctype="multipart/form-data">
                                        <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="buscador2" name="categoria">
                                            <option value="0" disabled selected>¿Cual categoría deseas buscar?</option>
<?php while ($categoria = mysqli_fetch_array($sql_categorias)) { ?>
                                            <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
<?php } ?>
                                        </select>
                                    </form>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Home</a>
                                </li>
                                <li><a href="admin/" class="btn btn-outline-light top-btn"><span class="ti-plus"></span><?php if ($_SESSION['logged'] == "si") { echo $_SESSION['nombre_user']; } else { ?> Login/Registro<?php } ?></a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!--//END HEADER -->
    <!--============================= DETAIL =============================-->
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 responsive-wrap">
                    <div class="row detail-filter-wrap">
                        <div class="col-md-4 featured-responsive">
                            <div class="detail-filter-text">
                                <p><?php echo $resultados; ?> Resultados para <span><?php echo plain_text($_GET['nombre_categoria']); ?></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="row light-bg detail-options-wrap">
<?php while ($album = mysqli_fetch_array($sql_albumes)) { ?>
                        <div class="col-sm-6 col-lg-4 col-xl-4 featured-responsive">
                            <div class="featured-place-wrap">
                                <a href="index.php?seccion=album&album=<?php echo $album['id_album']; ?>&nombre_album=<?php echo $album['nombre_album']; ?>&nombre_perfil=<?php echo $album['nombre_usuario'] ?>">
                                    <img src="<?php  echo $album['miniatura']; ?>" class="img-fluid" alt="<?php echo $album['nombre_album']." de ".$album['nombre_usuario']; ?>">
                                    <div class="featured-title-box">
                                        <h6><?php echo $album['nombre_album']; ?></h6>
                                        <p><a href="index.php?seccion=perfil&perfil=<?php echo $album['id_usuario']; ?>&nombre_usuario=<?php echo $album['nombre_usuario']; ?>" target="_self"><?php echo $album['nombre_usuario']; ?> </a></p>
                                        <ul>
<?php if (strlen($album['whatsapp']) > 5) {?>
                                            <li><span class="icon-screen-whatsapp"><i class="fab fa-whatsapp"></i></span>
                                                <p><a href="https://api.whatsapp.com/send?phone=57<?php echo $album['whatsapp']; ?>" target="_blank"><?php echo $album['whatsapp']; ?></a></p>
                                            </li>
<?php }
if (strlen($album['telefono']) > 5) { ?>
                                            <li><span class="icon-location-pin"></span>
                                                <p><a href="tel:<?php echo $album['telefono']; ?>"><?php echo $album['telefono']; ?></a></p>
                                            </li>
<?php }
if (strlen($album['email']) > 5) { ?>
                                            <li><span class="icon-envelope"></span>
                                                <p><a href="mailto:<?php echo $album['email']; ?>"><?php echo $album['email']; ?></a></p>
                                            </li>
<?php }
if (strlen($album['web']) > 5) { ?>
                                            <li><span class="icon-link"></span>
                                                <p><a href="<?php echo $album['web']; ?>" target="_blank"> <?php echo $album['web']; ?></p>
                                            </li>
<?php } ?>

                                        </ul>
                                    </div>
                                </a>
                            </div>
                        </div>
<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--//END DETAIL -->
<?php } elseif ($seccion == "album") { ?>
    <!--============================= HEADER =============================-->
    <div class="dark-bg sticky-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand" href="index.php">Creabuc</a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="icon-menu"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <form class="filter-dropdown" action="" id="form_busqueda" method="post" enctype="multipart/form-data">
                                        <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="buscador2" name="categoria">
                                            <option value="0" disabled selected>¿Cual categoría deseas buscar?</option>
											<?php while ($categoria = mysqli_fetch_array($sql_categorias)) { ?>
                                                <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
											<?php } ?>
                                        </select>
                                    </form>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Home</a>
                                </li>
                                <li><a href="admin/" class="btn btn-outline-light top-btn"><span class="ti-plus"></span><?php if ($_SESSION['logged'] == "si") { echo $_SESSION['nombre_user']; } else { ?> Login/Registro<?php } ?></a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!--//END HEADER -->
    <!--============================= BOOKING =============================-->
    <div>
        <!-- Swiper -->
        <div class="swiper-container">
            <div class="swiper-wrapper">
<?php while ($imagen = mysqli_fetch_array($sql_fotos)) { ?>
                <div class="swiper-slide">
                    <a href="<?php echo $imagen['ruta_full']; ?>" class="grid image-link">
                        <img src="<?php echo $imagen['ruta_mini']; ?>" class="img-fluid" alt="<?php echo $data_album['nombre_album']; ?>">
                    </a>
                </div>
<?php } ?>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination swiper-pagination-white"></div>
            <!-- Add Arrows -->
            <div class="swiper-button-next swiper-button-white"></div>
            <div class="swiper-button-prev swiper-button-white"></div>
        </div>
    </div>
    <!--//END BOOKING -->
    <!--============================= RESERVE A SEAT =============================-->
    <section class="reserve-block">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo $data_album['nombre_album']; ?></h5>
                </div>
                <div class="col-md-6">
                    <div class="reserve-seat-block">
                        <div class="review-btn">
                            <a href="tel:<?php echo $data_album['telefono']; ?>" class="btn btn-outline-danger"><i class="fas fa-phone"></i> Llamar</a>
                            <span>Sólo desde celulares</span>
                        </div>
<?php if (strlen($data_album['whatsapp']) > 5) { ?>
                        <div class="reserve-btn">
                            <div class="featured-btn-wrap">
                                <a href="https://api.whatsapp.com/send?phone=57<?php echo $data_album['whatsapp']; ?>" class="btn btn-danger"><i class="fab fa-whatsapp"></i> Escribir por Whatsapp</a>
                            </div>
                        </div>
<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--//END RESERVE A SEAT -->
    <!--============================= BOOKING DETAILS =============================-->
    <section class="light-bg booking-details_wrap">
        <div class="container">
            <div class="row">
                <div class="col-md-8 responsive-wrap">
                    <div class="booking-checkbox_wrap">
                        <div class="booking-checkbox">
<?php echo $data_album['descripcion']; ?>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 responsive-wrap">
                    <div class="follow">
                        <div class="follow-img">
<?php if (strlen($data_album['foto_perfil']) > 5) { ?>
                            <img src="<?php echo $data_album['foto_perfil']; ?>" class="img-fluid" alt="<?php echo $data_album['nombre_usuario']; ?>">
<?php } else { ?>
                            <img src="images/unknown.png" class="img-fluid" alt="<?php echo $data_album['nombre_usuario']; ?>">
<?php } ?>
                            <h6><?php echo $data_album['nombre_usuario']; ?></h6>
                        </div>
                        <ul class="social-counts">
<?php if (strlen($data_album['facebook']) > 5) { ?>
                            <li>
                                <h6><a href="<?php echo $data_album['facebook']; ?>" target="_blank"><i class="fab fa-2x fa-facebook"></i></a></h6>
                            </li>
<?php }
if (strlen($data_album['instagram']) > 5) { ?>
                            <li>
                                <h6><a href="<?php echo $data_album['instagram']; ?>" target="_blank"><i class="fab fa-2x fa-instagram"></i></a></h6>
                            </li>
<?php }
if (strlen($data_album['behance']) > 5) { ?>
                            <li>
                                <h6><a href="<?php echo $data_album['behance']; ?>" target="_blank"><i class="fab fa-2x fa-behance"></i></a></h6>
                            </li>
<?php } ?>
                        </ul>
                    </div>
                    <div class="contact-info">
<?php if (strlen($data_album['whatsapp']) > 5 ) { ?>
                        <div class="address">
                            <i class="fab fa-whatsapp"></i>
                            <p><a href="https://api.whatsapp.com/send?phone=57<?php echo $data_album['whatsapp']; ?>" target="_blank"><?php echo $data_album['whatsapp']; ?></a></p>
                        </div>
<?php }
if (strlen($data_album['telefono']) > 5) { ?>
                        <div class="address">
                            <span class="icon-screen-smartphone"></span>
                            <p><a href="tel:<?php echo $data_album['telefono']; ?>"><?php echo $data_album['telefono']; ?></a></p>
                        </div>
<?php }
if (strlen($data_album['email']) > 5) { ?>
                        <div class="address">
                            <span class="icon-envelope"></span>
                            <p><a href="mailto:<?php echo $data_album['email']; ?>"><?php echo $data_album['email']; ?></a></p>
                        </div>
<?php }
if (strlen($data_album['web']) > 5) { ?>
                        <div class="address">
                            <span class="icon-link"></span>
                            <p><a href="<?php echo $data_album['web']; ?>" target="_blank"><?php echo $data_album['web']; ?></a></p>
                        </div>
<?php } ?>
                        <a href="index.php?seccion=perfil&perfil=<?php echo $data_album['id_usuario']; ?>&nombre_perfil=<?php echo $data_album['nombre_usuario']; ?>" class="btn btn-outline-danger btn-contact">VER PERFIL</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--//END BOOKING DETAILS -->
<?php }
if ($seccion == "perfil") { ?>
    <!--============================= HEADER =============================-->
    <div class="dark-bg sticky-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand" href="index.php">Creabuc</a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="icon-menu"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <form class="filter-dropdown" action="" id="form_busqueda" method="post" enctype="multipart/form-data">
                                        <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="buscador2" name="categoria">
                                            <option value="0" disabled selected>¿Cual categoría deseas buscar?</option>
											<?php while ($categoria = mysqli_fetch_array($sql_categorias)) { ?>
                                                <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
											<?php } ?>
                                        </select>
                                    </form>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Home</a>
                                </li>
                                <li><a href="admin/" class="btn btn-outline-light top-btn"><span class="ti-plus"></span><?php if ($_SESSION['logged'] == "si") { echo $_SESSION['nombre_user']; } else { ?> Login/Registro<?php } ?></a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!--//END HEADER -->
    <!--============================= RESERVE A SEAT =============================-->
    <section class="reserve-block">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Perfil</h5>
                </div>
                <div class="col-md-6">
                    <div class="reserve-seat-block">
                        <div class="review-btn">
                            <a href="tel:<?php echo $data_usuario['telefono']; ?>" class="btn btn-outline-danger"><i class="fas fa-phone"></i> Llamar</a>
                            <span>Sólo desde celulares</span>
                        </div>
						<?php if (strlen($data_usuario['whatsapp']) > 5) { ?>
                            <div class="reserve-btn">
                                <div class="featured-btn-wrap">
                                    <a href="https://api.whatsapp.com/send?phone=57<?php echo $data_usuario['whatsapp']; ?>" class="btn btn-danger"><i class="fab fa-whatsapp"></i> Escribir por Whatsapp</a>
                                </div>
                            </div>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--//END RESERVE A SEAT -->
    <!--============================= BOOKING DETAILS =============================-->
    <section class="light-bg booking-details_wrap">
        <div class="container">
            <div class="row">
                <div class="col-md-4 responsive-wrap">
                    <div class="follow">
                        <div class="follow-img">
							<?php if (strlen($data_usuario['foto_perfil']) > 5) { ?>
                                <img src="<?php echo $data_usuario['foto_perfil']; ?>" class="img-fluid" alt="<?php echo $data_usuario['nombre']; ?>">
							<?php } else { ?>
                                <img src="images/unknown.png" class="img-fluid" alt="<?php echo $data_usuario['nombre']; ?>">
							<?php } ?>
                            <h6><?php echo $data_usuario['nombre']; ?></h6>
                        </div>
                        <ul class="social-counts">
							<?php if (strlen($data_usuario['facebook']) > 5) { ?>
                                <li>
                                    <h6><a href="<?php echo $data_usuario['facebook']; ?>" target="_blank"><i class="fab fa-2x fa-facebook"></i></a></h6>
                                </li>
							<?php }
							if (strlen($data_usuario['instagram']) > 5) { ?>
                                <li>
                                    <h6><a href="<?php echo $data_usuario['instagram']; ?>" target="_blank"><i class="fab fa-2x fa-instagram"></i></a></h6>
                                </li>
							<?php }
							if (strlen($data_usuario['behance']) > 5) { ?>
                                <li>
                                    <h6><a href="<?php echo $data_usuario['behance']; ?>" target="_blank"><i class="fab fa-2x fa-behance"></i></a></h6>
                                </li>
							<?php } ?>
                        </ul>
                    </div>
                    <div class="contact-info">
						<?php if (strlen($data_usuario['whatsapp']) > 5 ) { ?>
                            <div class="address">
                                <i class="fab fa-whatsapp"></i>
                                <p><a href="https://api.whatsapp.com/send?phone=57<?php echo $data_usuario['whatsapp']; ?>" target="_blank"><?php echo $data_usuario['whatsapp']; ?></a></p>
                            </div>
						<?php }
						if (strlen($data_usuario['telefono']) > 5) { ?>
                            <div class="address">
                                <span class="icon-screen-smartphone"></span>
                                <p><a href="tel:<?php echo $data_usuario['telefono']; ?>"><?php echo $data_usuario['telefono']; ?></a></p>
                            </div>
						<?php }
						if (strlen($data_usuario['email']) > 5) { ?>
                            <div class="address">
                                <span class="icon-envelope"></span>
                                <p><a href="mailto:<?php echo $data_usuario['email']; ?>"><?php echo $data_usuario['email']; ?></a></p>
                            </div>
						<?php }
						if (strlen($data_usuario['web']) > 5) { ?>
                            <div class="address">
                                <span class="icon-link"></span>
                                <p><a href="<?php echo $data_usuario['web']; ?>" target="_blank"><?php echo $data_usuario['web']; ?></a></p>
                            </div>
						<?php } ?>
                    </div>
                </div>
                <div class="col-md-8 responsive-wrap">
                    <div class="booking-checkbox_wrap">
                        <div class="booking-checkbox">
				            <?php echo $data_usuario['resumen']; ?>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--//END BOOKING DETAILS -->
<?php } ?>
    <!--============================= FOOTER =============================-->
    <footer class="main-block dark-bg">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="copyright">
                        <p>Copyright &copy; 2018 Listing. Todos los derechos reservados | Visita <a href="index.php?seccion=nosotros">Acerca de nosotros</a> para saber más sobre éste proyecto.</p>
                        <ul>
                            <li><a href="https://www.facebook.com/creabuc.creabuc.9" target="_blank"><span class="ti-facebook"></span></a></li>
                            <li><a href="https://twitter.com/creabuc" target="_blank"><span class="ti-twitter-alt"></span></a></li>
                            <li><a href="https://www.instagram.com/creabuc/" target="_blank"><span class="ti-instagram"></span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--//END FOOTER -->




    <!-- jQuery, Bootstrap JS. -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Font Awesome SVG --
    <script defer src="https://use.fontawesome.com/releases/v5.4.1/js/all.js" integrity="sha384-L469/ELG4Bg9sDQbl0hvjMq8pOcqFgkSpwhwnslzvVVGpDjYJ6wJJyYjvG3u8XW7" crossorigin="anonymous"></script>-->

<?php if ($seccion == "album") { ?>
    <!-- Magnific popup JS -->
    <script src="js/jquery.magnific-popup.js"></script>
    <!-- Swipper Slider JS -->
    <script src="js/swiper.min.js"></script>
    <script>
        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 3,
            slidesPerGroup: 3,
            loop: true,
            loopFillGroupWithBlank: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>
    <script>
        if ($('.image-link').length) {
            $('.image-link').magnificPopup({
                type: 'image',
                gallery: {
                    enabled: true
                }
            });
        }
        if ($('.image-link2').length) {
            $('.image-link2').magnificPopup({
                type: 'image',
                gallery: {
                    enabled: true
                }
            });
        }
    </script>
<?php } ?>

<!-- Custom scripts -->
    <script src="js/custom.js"></script>

<?php if ($seccion == "inicio") { ?>
    <script>
        $(window).scroll(function() {
            // 100 = The point you would like to fade the nav in.

            if ($(window).scrollTop() > 100) {

                $('.fixed').addClass('is-sticky');

            } else {

                $('.fixed').removeClass('is-sticky');

            }
        });
    </script>
<?php } ?>
</body>

</html>
