<?php

class asientos_contables{

public function guarda_asiento($con, $fecha_asiento, $concepto_asiento, $tipo_asiento, $id_documento, $ruc_empresa, $id_usuario, $numero_asiento, $id_cli_pro){
		$codigo_unico=codigo_unico(20);
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date("Y-m-d H:i:s");
		$encabezado=mysqli_query($con, "INSERT INTO encabezado_diario VALUES (null,'".$ruc_empresa."','".$codigo_unico."','".$fecha_asiento."','".$numero_asiento."','".$concepto_asiento."','ok','".$id_usuario."','".$fecha_registro."','".$tipo_asiento."', '".$id_documento."','".$codigo_unico."')");
		
		$detalle_diario_contable=mysqli_query($con, "INSERT INTO detalle_diario_contable (id_detalle_cuenta, ruc_empresa, codigo_unico, id_cuenta, debe, haber, detalle_item, codigo_unico_bloque, id_cli_pro)
		SELECT null, '".$ruc_empresa."', '".$codigo_unico."', id_cuenta, debe, haber, detalle_item, '".$codigo_unico."', '".$id_cli_pro."'  FROM detalle_diario_tmp where id_usuario = '".$id_usuario."' and ruc_empresa = '".$ruc_empresa."' ");

		$delete_tmp=mysqli_query($con, "DELETE FROM detalle_diario_tmp WHERE id_usuario='".$id_usuario."' and ruc_empresa = '".$ruc_empresa."'");	
		if($encabezado && $detalle_diario_contable && $delete_tmp){
			return "Asiento guardado con éxito.";
		}else{
			return "Lo siento algo ha salido mal intenta nuevamente.";
		}
}

public function edita_asiento($con, $fecha_asiento, $concepto_asiento, $ruc_empresa, $id_usuario, $id_cli_pro, $codigo_unico){
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date("Y-m-d H:i:s");
		
		$encabezado_update = mysqli_query($con, "UPDATE encabezado_diario SET fecha_asiento='".$fecha_asiento."',concepto_general='".$concepto_asiento."' ,estado='Editado', id_usuario='".$id_usuario."', fecha_registro='".$fecha_registro."' WHERE codigo_unico='".$codigo_unico."'");
		$delete_detalle=mysqli_query($con, "DELETE FROM detalle_diario_contable WHERE codigo_unico='".$codigo_unico."' ");
		
		$detalle_diario_contable=mysqli_query($con, "INSERT INTO detalle_diario_contable (id_detalle_cuenta, ruc_empresa, codigo_unico, id_cuenta, debe, haber, detalle_item, codigo_unico_bloque, id_cli_pro)
		SELECT null, '".$ruc_empresa."', '".$codigo_unico."', id_cuenta, debe, haber, detalle_item, '".$codigo_unico."', '".$id_cli_pro."' FROM detalle_diario_tmp where id_usuario = '".$id_usuario."' and ruc_empresa = '".$ruc_empresa."' ");

		$delete_tmp=mysqli_query($con, "DELETE FROM detalle_diario_tmp WHERE id_usuario='".$id_usuario."' and ruc_empresa = '".$ruc_empresa."'");	
		
		$update_cli_pro_compras = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_proveedor FROM encabezado_compra WHERE id_encabezado_compra = enc.id_documento ) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='COMPRAS_SERVICIOS' and enc.ruc_empresa='".$ruc_empresa."'");
		$update_cli_pro_ret_compras = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_proveedor FROM encabezado_retencion WHERE id_encabezado_retencion = enc.id_documento ) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='RETENCIONES_COMPRAS' and enc.ruc_empresa='".$ruc_empresa."'");
		$update_cli_pro_ventas = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_cliente FROM encabezado_factura WHERE id_encabezado_factura = enc.id_documento ) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='VENTAS' and enc.ruc_empresa='".$ruc_empresa."'");
		$update_cli_pro_ret_ventas = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_cliente FROM encabezado_retencion_venta WHERE id_encabezado_retencion = enc.id_documento ) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='RETENCIONES_VENTAS' and enc.ruc_empresa='".$ruc_empresa."'");
		$update_cli_pro_nc_ventas = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_cliente FROM encabezado_nc WHERE id_encabezado_nc = enc.id_documento ) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='NC_VENTAS' and enc.ruc_empresa='".$ruc_empresa."'");
		$update_cli_pro_ingresos = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_cli_pro FROM ingresos_egresos WHERE id_ing_egr = enc.id_documento) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='INGRESOS' and enc.ruc_empresa='".$ruc_empresa."'");
		$update_cli_pro_egresos = mysqli_query($con, "UPDATE detalle_diario_contable as det INNER JOIN encabezado_diario as enc ON enc.codigo_unico=det.codigo_unico SET det.id_cli_pro= (SELECT id_cli_pro FROM ingresos_egresos WHERE id_ing_egr = enc.id_documento) WHERE det.codigo_unico=enc.codigo_unico and enc.tipo='EGRESOS' and enc.ruc_empresa='".$ruc_empresa."'");
		
		if($encabezado_update && $detalle_diario_contable && $delete_tmp){
			return "Asiento editado con éxito.";
		}else{
			return "Lo siento algo ha salido mal intenta nuevamente.";
		}
}

public function numero_asiento($con, $ruc_empresa){
		$numero_diario=mysqli_query($con,"select max(numero_asiento) as asiento from encabezado_diario where ruc_empresa = '".$ruc_empresa."'");
		$row_numero_diario=mysqli_fetch_array($numero_diario);
		$ultimo_numero_asiento=$row_numero_diario['asiento']+1;
		return $ultimo_numero_asiento;
}

}
?>
