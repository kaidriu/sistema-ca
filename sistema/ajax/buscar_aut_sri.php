<?php
include("../conexiones/conectalogin.php");
$con = conenta_login();
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];

if ($action =="documento_existente" & isset($_POST['proveedor'])){
		$proveedor = mysqli_real_escape_string($con,(strip_tags($_POST['proveedor'], ENT_QUOTES)));
		$comprobante =intval(mysqli_real_escape_string($con,(strip_tags($_POST['comprobante'], ENT_QUOTES))));
		$documento =mysqli_real_escape_string($con,(strip_tags($_POST['documento'], ENT_QUOTES)));

		$sql_detalle_proveedor=mysqli_query($con,"SELECT * FROM encabezado_compra WHERE ruc_empresa='".$ruc_empresa."' and id_proveedor='".$proveedor."' and id_comprobante = '".$comprobante."' and numero_documento = '".$documento."' ");
		$contar=mysqli_num_rows($sql_detalle_proveedor);
		if ($contar>0){
		echo "El documento ya esta registrado.";
		}else{
		return false;
		}
		
}


	if ($action =="autorizacion_sri" & isset($_POST['proveedor'])){
		$proveedor = mysqli_real_escape_string($con,(strip_tags($_POST['proveedor'], ENT_QUOTES)));
		$comprobante =intval(mysqli_real_escape_string($con,(strip_tags($_POST['comprobante'], ENT_QUOTES))));
		$documento =mysqli_real_escape_string($con,(strip_tags(substr($_POST['documento'],0,7), ENT_QUOTES)));
			
		$sql_detalle_proveedor=mysqli_query($con,"SELECT * FROM encabezado_compra WHERE id_proveedor='".$proveedor."' and id_comprobante = '".$comprobante."' and substr(numero_documento,1,7) = '".$documento."' and id_sustento > 0 order by id_encabezado_compra desc ");
		$row=mysqli_fetch_array($sql_detalle_proveedor);
		$autorizacion=$row['aut_sri'];
		$id_sustento=$row['id_sustento'];
		
		$sql_sustento=mysqli_query($con,"SELECT * FROM sustento_tributario WHERE id_sustento='".$id_sustento."' ");
		$row_sustento=mysqli_fetch_array($sql_sustento);
		$sustento_tributario=$row_sustento['codigo_sustento'];
		
		$desde=$row['desde'];
		$hasta=$row['hasta'];
		$fecha_caducidad=$row['fecha_caducidad'];
		if (empty($fecha_caducidad)){
		$fecha_caducidad=date("d-m-Y");
		}else{
		$fecha_caducidad=date('d-m-Y', strtotime($row['fecha_caducidad']));
		}
		?>
		<input type="hidden" id="sustento_tri" name="sustento_tri" value="<?php echo $sustento_tributario; ?>">
		<input type="hidden" id="aut_sri_encontrada" name="aut_sri_encontrada" value="<?php echo $autorizacion; ?>">
		<input type="hidden" id="desde_encontrada" name="desde_encontrada" value="<?php echo $desde; ?>">
		<input type="hidden" id="hasta_encontrada" name="hasta_encontrada" value="<?php echo $hasta; ?>">
		<input type="hidden" id="fecha_encontrada" name="fecha_encontrada" value="<?php echo $fecha_caducidad; ?>">
		<?php
	}
?>