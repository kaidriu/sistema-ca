<?PHP
	include("../conexiones/conectalogin.php");
	session_start();
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];

//para mostrar el detalle de productos al momento de dar clic en detalle de factura programada
if (isset($_GET['muestra_detalle_fp'])){
	include("../ajax/muestra_detalle_factura_programada.php");
}

//para agregar el detalle de productos a la factura programada
if (isset($_GET['agregar_detalle_fp'])){
			$id_cliente="CLIENTE".mysqli_real_escape_string($con,(strip_tags($_GET["id_cliente"],ENT_QUOTES)));
			$id_producto=mysqli_real_escape_string($con,(strip_tags($_GET["id_producto"],ENT_QUOTES)));
			$cantidad=mysqli_real_escape_string($con,(strip_tags($_GET["cantidad_producto"],ENT_QUOTES)));
			$precio_producto=mysqli_real_escape_string($con,(strip_tags($_GET["precio_producto"],ENT_QUOTES)));
			$periodo=mysqli_real_escape_string($con,(strip_tags($_GET["periodo"],ENT_QUOTES)));
			$fecha_registro=date("Y-m-d H:i:s");
			$guarda_detalle_por_facturar=mysqli_query($con, "INSERT INTO detalle_por_facturar VALUES (null, '$ruc_empresa','$id_cliente',$id_producto,$cantidad,$precio_producto,'$periodo','$fecha_registro',$id_usuario,0)");
	include("../ajax/muestra_detalle_factura_programada.php");
}

//para eliminar un producto por facturarse
if (isset($_GET['eliminar_detalle_fp'])){
	$id_registro = $_GET['id_detalle_fp'];
	$elimina_detalle_por_facturarse = mysqli_query($con, "DELETE FROM detalle_por_facturar WHERE id_detalle_pf=$id_registro");
	include("../ajax/muestra_detalle_factura_programada.php");			
}

?>