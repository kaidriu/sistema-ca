<?php
//conexion a las bases de datos
function conecta_contactos(){
$host_db = "shareddb1b.hosting.stackcp.net";
$user_db = "CmGr";
$pass_db = "CmGr1980";
$db_name = "paginaweb1980-3564bc";
$conexion = new mysqli($host_db, $user_db, $pass_db, $db_name);
	if(mysqli_connect_errno()){
		?>
		<div class="alert alert-danger alert-dismissable">
		<a href="../index.php" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></a>
		<strong>Algo pasa!</strong"> Error en la conexión a la base de datos! </div>
		 <?php
		echo mysqli_connect_error();
		exit;
	}
	mysqli_set_charset($conexion,"utf8");
return $conexion;
}
?>
