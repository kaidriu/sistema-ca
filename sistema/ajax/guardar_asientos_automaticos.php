<?php
include("../validadores/generador_codigo_unico.php");

$tipo_asiento= $_POST['tipo_asiento'];
$cliente_proveedor= $_POST['cliente_proveedor'];
$fecha_desde=date('Y-m-d', strtotime($_POST['fecha_desde'],ENT_QUOTES));
$fecha_hasta=date('Y-m-d', strtotime($_POST['fecha_hasta'],ENT_QUOTES));

if(isset($_POST['tipo_asiento'])){	
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		
		$codigo_unico_bloque=codigo_unico(20);
		$sql_diario_temporal=mysqli_query($con,"select * from asientos_automaticos_tmp WHERE ruc_empresa = '".$ruc_empresa."' ");
		$count=mysqli_num_rows($sql_diario_temporal);
		if ($count==0){
		echo "<script>
				$.notify('No hay documentos para contabilizar.','error');
				</script>";
		exit;
		}
		
		$sql_faltantes=mysqli_query($con,"select * from asientos_automaticos_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_cuenta ='0'");
		$count_faltantes=mysqli_num_rows($sql_faltantes);
		if ($count_faltantes >0){
		echo "<script>
			$.notify('No hay cuentas agregadas en todos los registros a contabilizar.','error');
			</script>";
		exit;
		}

		$sql_partida_doble=mysqli_query($con,"select round(sum(debe-haber),2) as partida_doble from asientos_automaticos_tmp WHERE ruc_empresa = '".$ruc_empresa."' ");
		$row_partida_doble=mysqli_fetch_array($sql_partida_doble);
		$partida_doble =$row_partida_doble['partida_doble'];
		if ($partida_doble != 0){
		echo "<script>
			$.notify('Existen asientos que no cumplen con partida doble.','error');
			</script>";
		exit;
		}
			
		switch (strtolower($tipo_asiento)) {
			case "ventas":
				$concepto_general="FACTURA DE VENTA";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			case "nc_ventas":
				$concepto_general="NOTA DE CRÉDITO EN VENTA";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			case "retenciones_ventas":
				$concepto_general="RETENCIÓN DE VENTA";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			case "retenciones_compras":
				$concepto_general="RETENCIÓN DE COMPRA";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			case "compras_servicios":
				$concepto_general="DOCUMENTO DE COMPRA/SERVICIO";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			case "ingresos":
				$concepto_general="COMPROBANTE DE INGRESO";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			case "egresos":
				$concepto_general="COMPROBANTE DE EGRESO";
				guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general);
				guardar_detalle_asientos($con, $ruc_empresa);
				break;
			}
				  
			  actualiza_asientos_en_documentos($con, $tipo_asiento, $ruc_empresa);
			  elimina_registros_contabilizados($con, $ruc_empresa);
			  echo "<script>
				$.notify('Registros guardados con éxito','success');
				setTimeout(function (){location.href ='../modulos/generar_asientos.php'}, 1000);
				</script>";
			  		
	}else {
		echo "<script>
		$.notify('Error desconocido','error');
		setTimeout(function (){location.href ='../modulos/generar_asientos.php'}, 1000);
		</script>";
	}

	function guardar_encabezado_asientos($con, $ruc_empresa, $id_usuario, $concepto_general){
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date("Y-m-d H:i:s");
		$guardar_encabezado_diario = mysqli_query($con, "INSERT INTO encabezado_diario (
		id_diario, ruc_empresa, codigo_unico, fecha_asiento, numero_asiento, concepto_general, estado, id_usuario, fecha_registro,	tipo, id_documento, codigo_unico_bloque) 
	   (SELECT null, '" . $ruc_empresa . "', id_registro, fecha_documento, numero_asiento, concat('".$concepto_general."', ' ', documento,' ',nombre_cli_pro), 'ok', '".$id_usuario."', '".$fecha_registro."', transaccion, id_registro, codigo_unico 
	   FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' group by id_registro) ");
	}

	function guardar_detalle_asientos($con, $ruc_empresa){
		$detalle_diario_contable = mysqli_query($con, "INSERT INTO detalle_diario_contable (
		id_detalle_cuenta, ruc_empresa, codigo_unico, id_cuenta, debe, haber, detalle_item, codigo_unico_bloque, id_cli_pro) 
	   (SELECT null, '".$ruc_empresa."', id_registro, id_cuenta, debe, haber, detalle, codigo_unico, id_cli_pro
	    FROM asientos_automaticos_tmp WHERE ruc_empresa='".$ruc_empresa."') ");
	}
		
	function actualiza_asientos_en_documentos($con, $tipo_asiento, $ruc_empresa){
		switch ($tipo_asiento) {
			case "ventas":
			$query_update = mysqli_query($con, "UPDATE encabezado_factura, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET id_registro_contable = registros.numero_asiento WHERE id_encabezado_factura = registros.id_registro");
				break;
			case "nc_ventas":
				$query_update = mysqli_query($con, "UPDATE encabezado_nc, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET id_registro_contable = registros.numero_asiento WHERE id_encabezado_nc = registros.id_registro");
				break;
			case "retenciones_ventas":
				$query_update = mysqli_query($con, "UPDATE encabezado_retencion_venta, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET id_registro_contable = registros.numero_asiento WHERE id_encabezado_retencion = registros.id_registro");
				break;
			case "retenciones_compras":
				$query_update = mysqli_query($con, "UPDATE encabezado_retencion, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET id_registro_contable = registros.numero_asiento WHERE id_encabezado_retencion = registros.id_registro");
				break;
			case "compras_servicios":
				$query_update = mysqli_query($con, "UPDATE encabezado_compra, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET id_registro_contable = registros.numero_asiento WHERE id_encabezado_compra = registros.id_registro");
				break;
			case "ingresos":
				$query_update = mysqli_query($con, "UPDATE ingresos_egresos, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET codigo_contable = registros.numero_asiento WHERE id_ing_egr = registros.id_registro and tipo_ing_egr='INGRESO'");
				break;
			case "egresos":
				$query_update = mysqli_query($con, "UPDATE ingresos_egresos, (SELECT numero_asiento as numero_asiento, id_registro as id_registro FROM contabilizar_documentos_tmp WHERE ruc_empresa='".$ruc_empresa."' ) as registros SET codigo_contable = registros.numero_asiento WHERE id_ing_egr = registros.id_registro and tipo_ing_egr='EGRESO'");
				break;
			}
	}
	
	function elimina_registros_contabilizados($con, $ruc_empresa){
		$eliminar_documentos = mysqli_query($con, "DELETE FROM contabilizar_documentos_tmp WHERE ruc_empresa = '".$ruc_empresa."' " );
		$eliminar_asientos = mysqli_query($con, "DELETE FROM asientos_automaticos_tmp WHERE ruc_empresa = '".$ruc_empresa."' " );
	}
