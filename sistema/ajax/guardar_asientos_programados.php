<?php
session_start();
$id_usuario = $_SESSION['id_usuario'];
$ruc_empresa = $_SESSION['ruc_empresa'];
include("../conexiones/conectalogin.php");
$con = conenta_login();

$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

if($action=="guarda_cuenta"){
	$id_cuenta=$_GET['id_cuenta'];
	$tipo=$_GET['tipo'];
	$id_registro=$_GET['id'];
	$concepto_cuenta=$_GET['concepto_cuenta'];

		eliminar_registro($con, $ruc_empresa, $id_registro, $tipo);
		guardar_registro($con, $ruc_empresa, $id_cuenta, $tipo, $concepto_cuenta, $id_registro, $id_usuario);
		echo "<script>$.notify('Cuenta guardada.','success')</script>";

}

if($action=="eliminar_cuenta"){
	$tipo=$_GET['tipo'];
	$id_registro=$_GET['id'];
	eliminar_registro($con, $ruc_empresa, $id_registro, $tipo);
	echo "<script>$.notify('Registro eliminado.','info');
			</script>";
}

function eliminar_registro($con, $ruc_empresa, $id_registro, $tipo_asiento){
	$eliminar_asiento_tipo=mysqli_query($con,"DELETE FROM asientos_programados WHERE ruc_empresa = '".$ruc_empresa."' and tipo_asiento = '".$tipo_asiento."' and id_pro_cli='".$id_registro."' ");
return $eliminar_asiento_tipo;
}

function guardar_registro($con, $ruc_empresa, $id_cuenta, $tipo_asiento, $concepto_cuenta, $id_registro, $id_usuario){
	$fecha_agregado=date("Y-m-d H:i:s");
	ini_set('date.timezone','America/Guayaquil');
	$guardar_asiento_tipo=mysqli_query($con,"INSERT INTO asientos_programados VALUES (NULL, '".$ruc_empresa."', '".$tipo_asiento."','".$id_cuenta."','DEBE-HABER','".$concepto_cuenta."','".$id_registro."','".$id_usuario."','".$fecha_agregado."')");				
	return $guardar_asiento_tipo;
}
?>