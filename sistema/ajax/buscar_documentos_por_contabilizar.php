<?php
include("../conexiones/conectalogin.php");
include("../validadores/generador_codigo_unico.php");
$con = conenta_login();
$codigo_unico=codigo_unico(20);
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

//para eliminar registros
if ($action == 'eliminar_registro') {
	$transaccion = $_GET['transaccion'];
	$id_registro = $_GET['id_registro'];
	$eliminar_documentos = mysqli_query($con, "DELETE FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' and id_registro='" . $id_registro . "'");
	$eliminar_asientos = mysqli_query($con, "DELETE FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' and id_registro='" . $id_registro . "'");

	switch ($transaccion) {
		case "ventas":
			echo mostrar_resultados_asientos_ventas($con, $ruc_empresa);
			break;
		case "nc_ventas":
			echo mostrar_resultados_asientos_nc_ventas($con, $ruc_empresa);
			break;
		case "retenciones_ventas":
			echo mostrar_resultados_asientos_retenciones_ventas($con, $ruc_empresa);
			break;
		case "retenciones_compras":
			echo mostrar_resultados_asientos_retenciones_compras($con, $ruc_empresa);
			break;
		case "compras_servicios":
			echo mostrar_resultados_asientos_compras($con, $ruc_empresa);
			break;
		case "ingresos":
			echo mostrar_resultados_asientos_ingresos($con, $ruc_empresa);
			break;
		case "egresos":
			echo mostrar_resultados_asientos_ingresos($con, $ruc_empresa);
			break;
	}
}


//para traer facturas de ventas
if ($action == 'ventas') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));
	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_cliente = "";
	} else {
		$opcion_cliente = "and ef.id_cliente='" . $id_cliente_proveedor . "'";
	}
	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, documento, 
	subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, total, fecha_documento, 
	tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
	(SELECT null, cf.ruc_empresa, ef.id_cliente, concat_ws('-', ef.serie_factura, 
	LPAD(ef.secuencial_factura,9,'0')) ,sum(cf.subtotal_factura-cf.descuento), 
	cf.tarifa_iva, '0', cf.descuento, ef.propina, ef.tasa_turistica, ef.total_factura, ef.fecha_factura, '01', 'VENTAS', 
	ef.id_encabezado_factura, '".$codigo_unico."', '0', cli.nombre FROM cuerpo_factura cf INNER JOIN encabezado_factura ef 
	ON ef.serie_factura = cf.serie_factura and ef.secuencial_factura = cf.secuencial_factura 
	INNER JOIN clientes as cli ON cli.id=ef.id_cliente WHERE cf.ruc_empresa = '" . $ruc_empresa . "' and ef.ruc_empresa = '" . $ruc_empresa . "' 
	and DATE_FORMAT(ef.fecha_factura, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' 
	and '" . date("Y/m/d", strtotime($hasta)) . "' and ef.id_registro_contable='0' 
	$opcion_cliente and ef.estado_sri='AUTORIZADO' group by ef.serie_factura, ef.secuencial_factura, cf.tarifa_iva) ");
	
	actualizar_iva($con, $ruc_empresa);
	actualizar_total($con, $ruc_empresa);
	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	$ultimo_registro=0;
	$query_documentos = traer_info_documentos($con, $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
		$nombre_cliente = strtoupper($row['nombre_cli_pro']);

		if ($row['id_registro'] != $ultimo_registro){
			numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
				$numero_asiento++;
			}else{
				$numero_asiento++;
			}
		$ultimo_registro=$row['id_registro'];

		//para la cuenta por cobrar
		$sql_contable_cxc = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCXCC' ");
		$row_contable_cxc = mysqli_fetch_array($sql_contable_cxc);
		$id_cxc = $row_contable_cxc['id_asiento_tipo'];
		$array_datos_por_cobrar = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Factura de venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => number_format($row['total'], 2, '.', ''), 'valor_haber' => '0', 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
		$cuenta_porcobrar = generar_asiento($con, $ruc_empresa, $action, $array_datos_por_cobrar, $id_cxc);

				//para el subtotal de ventas si esta asignado una cuenta a un cliente
				$sql_cuenta_cliente_individual = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_programados WHERE id_pro_cli = '".$row['id_cli_pro']."' and tipo_asiento = 'cliente' and ruc_empresa ='".$ruc_empresa."' ");
				$row_cuenta_cliente_individual = mysqli_fetch_array($sql_cuenta_cliente_individual);
				$cuenta_cliente_individual = $row_cuenta_cliente_individual['numrows'];

				//para el subtotal de ventas si esta tarifa de iva
				$sql_cuenta_tarifa_iva = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_programados WHERE tipo_asiento = 'tarifa_iva_ventas' and ruc_empresa ='".$ruc_empresa."' ");
				$row_cuenta_tarifa_iva = mysqli_fetch_array($sql_cuenta_tarifa_iva);
				$cuenta_cliente_tarifa_iva = $row_cuenta_tarifa_iva['numrows'];

				//para cuando no esta asignado un cliente ni una tarifa de iva
				$sql_contable_subtotal_venta_general = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCSV' ");
				$row_contable_subtotal_venta_general = mysqli_fetch_array($sql_contable_subtotal_venta_general);
				$id_subtotal_venta_general = $row_contable_subtotal_venta_general['id_asiento_tipo'];
				
				$array_datos_subtotal = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Factura de venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => '0', 'valor_haber' => number_format($row['subtotal'], 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				if($cuenta_cliente_individual>0){
					$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'cliente', $array_datos_subtotal, $row['id_cli_pro']);
				}else if($cuenta_cliente_tarifa_iva>0){
				$sql_tipo_tarifa_iva = mysqli_query($con, "SELECT * FROM asientos_programados WHERE tipo_asiento = 'tarifa_iva_ventas' and ruc_empresa ='".$ruc_empresa."' and id_pro_cli='".$row['tipo_iva']."' ");
				$row_tipo_tarifa_iva = mysqli_fetch_array($sql_tipo_tarifa_iva);
				$id_tarifa_iva = $row_tipo_tarifa_iva['id_pro_cli'];	
					$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'tarifa_iva_ventas', $array_datos_subtotal, $id_tarifa_iva);
				}else{
					$cuenta_subtotal = generar_asiento($con, $ruc_empresa, $action, $array_datos_subtotal, $id_subtotal_venta_general);
				}

				//PARA LA CUENTA CONTABLE DE OTROS VALORES COMO PROPINA O TASA
				if(($row['otro_val_uno'] + $row['otro_val_dos'])>0){
				$sql_subtotal_otros = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCOV' ");
				$row_subtotal_otros = mysqli_fetch_array($sql_subtotal_otros);
				$id_subtotal_otros = $row_subtotal_otros['id_asiento_tipo'];
				$array_subtotal_otros = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Factura de venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => number_format(0, 2, '.', ''), 'valor_haber' => number_format($row['otro_val_uno'] + $row['otro_val_dos'], 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_subtotal_otros = generar_asiento($con, $ruc_empresa, $action, $array_subtotal_otros, $id_subtotal_otros);
				}

				
		if ($row['valor_iva'] > 0) {
			$sql_cuenta_iva = mysqli_query($con, "SELECT * FROM asientos_programados WHERE tipo_asiento = 'iva_ventas' and ruc_empresa ='".$ruc_empresa."' and id_pro_cli='".$row['tipo_iva']."' ");
			$row_cuenta_iva = mysqli_fetch_array($sql_cuenta_iva);
			$id_cuenta_iva = $row_cuenta_iva['id_pro_cli'];
			$array_datos_iva = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'IVA en ventas en factura de venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => '0', 'valor_haber' => number_format($row['valor_iva'], 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
			$cuenta_iva = generar_asiento($con, $ruc_empresa, 'iva_ventas', $array_datos_iva, $id_cuenta_iva);
		}	

	}
	echo mostrar_resultados_asientos_ventas($con, $ruc_empresa);
}

//para traer documentos de notas de credito de ventas
if ($action == 'nc_ventas') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));

	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_cliente = "";
	} else {
		$opcion_cliente = "and enc.id_cliente='" . $id_cliente_proveedor . "'";
	}
	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, 
	documento, subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, 
	total, fecha_documento, tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
		(SELECT null, enc.ruc_empresa, enc.id_cliente, concat_ws('-', enc.serie_nc, 
		LPAD(enc.secuencial_nc,9,'0')) ,round(sum(cnc.subtotal_nc-cnc.descuento),2), cnc.tarifa_iva, 
		'0', cnc.descuento, '0', '0', '0', enc.fecha_nc, '04', 'NC_VENTAS', enc.id_encabezado_nc, '" . $codigo_unico . "', '0', cli.nombre 
		FROM cuerpo_nc cnc INNER JOIN encabezado_nc as enc ON enc.serie_nc = cnc.serie_nc and enc.secuencial_nc = cnc.secuencial_nc 
		INNER JOIN clientes as cli ON cli.id=enc.id_cliente WHERE cnc.ruc_empresa = '" . $ruc_empresa . "' and enc.ruc_empresa = '" . $ruc_empresa . "' 
		and DATE_FORMAT(enc.fecha_nc, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' 
		and '" . date("Y/m/d", strtotime($hasta)) . "' and enc.id_registro_contable='0' $opcion_cliente  
		and enc.estado_sri='AUTORIZADO' group by enc.serie_nc, enc.secuencial_nc) ");//and enc.estado_sri='AUTORIZADO'
	
	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	actualizar_iva($con, $ruc_empresa);
	actualizar_total($con, $ruc_empresa);
	$ultimo_registro=0;

	$query_documentos = traer_info_documentos($con, $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
		$nombre_cliente = strtoupper($row['nombre_cli_pro']);

		if ($row['id_registro'] != $ultimo_registro){
			numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
				$numero_asiento++;
			}else{
				$numero_asiento++;
			}
		$ultimo_registro=$row['id_registro'];
		
		//para el subtotal de nc si esta asignado una cuenta a un cliente
		$sql_cuenta_cliente_individual = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_programados WHERE id_pro_cli = '".$row['id_cli_pro']."' and tipo_asiento = 'cliente' and ruc_empresa ='".$ruc_empresa."' ");
		$row_cuenta_cliente_individual = mysqli_fetch_array($sql_cuenta_cliente_individual);
		$cuenta_cliente_individual = $row_cuenta_cliente_individual['numrows'];

		//para el subtotal de nc si esta tarifa de iva
		$sql_cuenta_tarifa_iva = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_programados WHERE tipo_asiento = 'tarifa_iva_ventas' and ruc_empresa ='".$ruc_empresa."' ");
		$row_cuenta_tarifa_iva = mysqli_fetch_array($sql_cuenta_tarifa_iva);
		$cuenta_cliente_tarifa_iva = $row_cuenta_tarifa_iva['numrows'];

		//para cuando no esta asignado un cliente ni una tarifa de iva
		$sql_contable_subtotal_venta_general = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCSV' ");
		$row_contable_subtotal_venta_general = mysqli_fetch_array($sql_contable_subtotal_venta_general);
		$id_subtotal_venta_general = $row_contable_subtotal_venta_general['id_asiento_tipo'];
		
		$array_datos_subtotal = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Nota de crédito en venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => number_format($row['subtotal'], 2, '.', ''), 'valor_haber' => '0', 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
		if($cuenta_cliente_individual>0){
			$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'cliente', $array_datos_subtotal, $row['id_cli_pro']);
		}else if($cuenta_cliente_tarifa_iva>0){
		$sql_tipo_tarifa_iva = mysqli_query($con, "SELECT * FROM asientos_programados WHERE tipo_asiento = 'tarifa_iva_ventas' and ruc_empresa ='".$ruc_empresa."' and id_pro_cli='".$row['tipo_iva']."' ");
		$row_tipo_tarifa_iva = mysqli_fetch_array($sql_tipo_tarifa_iva);
		$id_tarifa_iva = $row_tipo_tarifa_iva['id_pro_cli'];	
			$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'tarifa_iva_ventas', $array_datos_subtotal, $id_tarifa_iva);
		}else{
			$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'ventas', $array_datos_subtotal, $id_subtotal_venta_general);
		}

		//PARA LA CUENTA CONTABLE DE OTROS VALORES COMO PROPINA O TASA
		if(($row['otro_val_uno'] + $row['otro_val_dos'])>0){
			$sql_subtotal_otros = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCOV' ");
			$row_subtotal_otros = mysqli_fetch_array($sql_subtotal_otros);
			$id_subtotal_otros = $row_subtotal_otros['id_asiento_tipo'];
			$array_subtotal_otros = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Nota de crédito en venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => number_format($row['otro_val_uno'] + $row['otro_val_dos'], 2, '.', ''), 'valor_haber' => number_format(0, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
			$cuenta_subtotal_otros = generar_asiento($con, $ruc_empresa, $action, $array_subtotal_otros, $id_subtotal_otros);
			}

		
		if ($row['valor_iva'] > 0) {
			$sql_cuenta_iva = mysqli_query($con, "SELECT * FROM asientos_programados WHERE tipo_asiento = 'iva_ventas' and ruc_empresa ='".$ruc_empresa."' and id_pro_cli='".$row['tipo_iva']."' ");
			$row_cuenta_iva = mysqli_fetch_array($sql_cuenta_iva);
			$id_cuenta_iva = $row_cuenta_iva['id_pro_cli'];
			$array_datos_iva = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Iva ventas en nota de crédito en ventas N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => number_format($row['valor_iva'], 2, '.', ''), 'valor_haber' => '0', 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
			$cuenta_iva = generar_asiento($con, $ruc_empresa, 'iva_ventas', $array_datos_iva, $id_cuenta_iva);
		}	

			//para la cuenta por cobrar
			$sql_contable_cxc = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCXCC' ");
			$row_contable_cxc = mysqli_fetch_array($sql_contable_cxc);
			$id_cxc = $row_contable_cxc['id_asiento_tipo'];
			$array_datos_por_cobrar = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Nota de crédito en venta N. ' . $row['documento'] . ' ' . $nombre_cliente, 'valor_debe' => '0', 'valor_haber' => number_format($row['total'], 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
			$cuenta_porcobrar = generar_asiento($con, $ruc_empresa, 'ventas', $array_datos_por_cobrar, $id_cxc);
	
	}
		echo mostrar_resultados_asientos_nc_ventas($con, $ruc_empresa);
}



//para traer documentos de retenciones de ventas
if ($action == 'retenciones_ventas') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));

	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_cliente = "";
	} else {
		$opcion_cliente = "and erv.id_cliente='" . $id_cliente_proveedor . "'";
	}
	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, 
	documento, subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, 
	total, fecha_documento, tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
	(SELECT null, erv.ruc_empresa, crv.codigo_impuesto, concat_ws('-', erv.serie_retencion, 
	LPAD(erv.secuencial_retencion,9,'0')) ,sum(crv.valor_retenido), crv.impuesto, '0', '0', '0', '0', 
	sum(crv.valor_retenido), erv.fecha_emision, '07', 'RETENCIONES_VENTAS', 
	erv.id_encabezado_retencion, '" . $codigo_unico . "','0', cli.nombre FROM cuerpo_retencion_venta as crv INNER JOIN encabezado_retencion_venta as erv 
	ON erv.serie_retencion = crv.serie_retencion and erv.secuencial_retencion = crv.secuencial_retencion INNER JOIN clientes as cli ON erv.id_cliente=cli.id
	WHERE crv.ruc_empresa = '" . $ruc_empresa . "' and erv.ruc_empresa = '" . $ruc_empresa . "' and 
	DATE_FORMAT(erv.fecha_emision, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' 
	and '" . date("Y/m/d", strtotime($hasta)) . "' and erv.id_registro_contable='0' and 
	crv.valor_retenido > 0 $opcion_cliente group by erv.serie_retencion, erv.secuencial_retencion, crv.impuesto, crv.porcentaje_retencion) ");

	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	$ultimo_registro=0;
	$query_documentos = traer_info_documentos($con,  $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
		$busca_documento = mysqli_query($con, "SELECT enc_ret.numero_documento as numero_documento, enc_ret.id_cliente as id_cliente 
		FROM encabezado_retencion_venta as enc_ret 
		WHERE enc_ret.id_encabezado_retencion = '" . $row['id_registro'] . "' ");
		$row_documento = mysqli_fetch_array($busca_documento);
		$numero_documento = $row_documento['numero_documento'];
		$id_cliente = $row_documento['id_cliente'];
		$nombre_cliente = strtoupper($row['nombre_cli_pro']);

		if ($row['id_registro'] != $ultimo_registro){
			numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
				$numero_asiento++;
			}else{
				$numero_asiento++;
			}
		$ultimo_registro=$row['id_registro'];

		$busca_conceptos_retencion = mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret = '" . $row['id_cli_pro'] . "' ");
		$row_conceptos_retencion = mysqli_fetch_array($busca_conceptos_retencion);
		$concepto_retencion = $row_conceptos_retencion['concepto_ret'];

		$array_datos_retencion = array('fecha_documento' => $row['fecha_documento'], 
		'detalle' => 'Retención en ventas N. ' . $row['documento'] . " " . $nombre_cliente . " Documento " . $numero_documento . " " . $concepto_retencion, 
		'valor_debe' => number_format($row['total'], 2, '.', ''), 'valor_haber' => '0', 'id_registro' => $row['id_registro'], 
		'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $id_cliente, 
		'transaccion' => $row['transaccion']);
		$cuenta_subtotal = generar_asiento($con, $ruc_empresa, $action, $array_datos_retencion, $row['id_cli_pro']);

		$sql_contable_cxc = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCXCC' ");
		$row_contable_cxc = mysqli_fetch_array($sql_contable_cxc);
		$id_cxc = $row_contable_cxc['id_asiento_tipo'];
		$array_datos_por_cobrar = array('fecha_documento' => $row['fecha_documento'], 
		'detalle' => 'Retención en ventas N. ' . $row['documento'] . " " . $nombre_cliente . " Documento " . $numero_documento, 
		'valor_debe' => '0', 'valor_haber' => number_format($row['total'], 2, '.', ''), 
		'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 
		'id_relacion' => $row['id'], 'id_cli_pro' => $id_cliente, 
		'transaccion' => $row['transaccion']);
		$cuenta_porcobrar = generar_asiento($con, $ruc_empresa, 'ventas', $array_datos_por_cobrar, $id_cxc);
	}
	
	echo mostrar_resultados_asientos_retenciones_ventas($con, $ruc_empresa, $action);
}

//para traer documentos de retenciones de compras
if ($action == 'retenciones_compras') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));

	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_cliente = "";
	} else {
		$opcion_cliente = "and erc.id_proveedor='" . $id_cliente_proveedor . "'";
	}
	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, 
	documento, subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, total, 
	fecha_documento, tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
		(SELECT null, erc.ruc_empresa, crc.codigo_impuesto, concat_ws('-', erc.serie_retencion, 
		LPAD(erc.secuencial_retencion,9,'0')), sum(crc.valor_retenido), if(crc.impuesto='RENTA','1', if(crc.impuesto='IVA','2','3')), '0', '0', '0', '0', 
		sum(crc.valor_retenido), erc.fecha_emision, '07', 'RETENCIONES_COMPRAS', erc.id_encabezado_retencion,
		'" . $codigo_unico . "','0', pro.razon_social FROM cuerpo_retencion as crc INNER JOIN encabezado_retencion as erc INNER JOIN proveedores as pro ON pro.id_proveedor=erc.id_proveedor
		ON erc.serie_retencion = crc.serie_retencion and erc.secuencial_retencion = crc.secuencial_retencion 
		WHERE crc.ruc_empresa = '" . $ruc_empresa . "' and erc.ruc_empresa = '" . $ruc_empresa . "' 
		and DATE_FORMAT(erc.fecha_emision, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' 
		and '" . date("Y/m/d", strtotime($hasta)) . "' and erc.id_registro_contable='0' $opcion_cliente 
		and crc.valor_retenido > 0 and erc.estado_sri='AUTORIZADO' group by erc.serie_retencion, erc.secuencial_retencion, crc.impuesto, crc.porcentaje_retencion)");//and erc.estado_sri='AUTORIZADO'
	
	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	$ultimo_registro=0;
	$query_documentos = traer_info_documentos($con,  $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
		$busca_proveedores = mysqli_query($con, "SELECT enc_ret.numero_comprobante as numero_comprobante, enc_ret.id_proveedor as id_proveedor
		FROM encabezado_retencion as enc_ret WHERE enc_ret.id_encabezado_retencion = '" . $row['id_registro'] . "' ");
		$row_proveedores = mysqli_fetch_array($busca_proveedores);
		$nombre_proveedor = strtoupper($row['razon_social']);
		$numero_comprobante = $row_proveedores['numero_comprobante'];
		$id_proveedor = $row_proveedores['id_proveedor'];

		$busca_conceptos_retencion = mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret = '" . $row['id_cli_pro'] . "' ");
		$row_conceptos_retencion = mysqli_fetch_array($busca_conceptos_retencion);
		$concepto_retencion = $row_conceptos_retencion['concepto_ret'];

		if ($row['id_registro'] != $ultimo_registro){
			numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
				$numero_asiento++;
			}else{
				$numero_asiento++;
			}
		$ultimo_registro=$row['id_registro'];

		$sql_contable_cxp = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCXPP' ");
		$row_contable_cxp = mysqli_fetch_array($sql_contable_cxp);
		$id_cxp = $row_contable_cxp['id_asiento_tipo'];
		$array_datos_por_pagar = array('fecha_documento' => $row['fecha_documento'], 
		'detalle' => 'Retención en compras N. ' . $row['documento'] . " " . $nombre_proveedor . " documento " . $numero_comprobante, 
		'valor_debe' => number_format($row['total'], 2, '.', ''), 'valor_haber' => '0', 
		'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 
		'id_relacion' => $row['id'], 'id_cli_pro' => $id_proveedor, 'transaccion' => $row['transaccion']);
		$cuenta_porcobrar = generar_asiento($con, $ruc_empresa, 'compras_servicios', $array_datos_por_pagar, $id_cxp);

		$array_datos_retencion = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 
		'Retención en compras N. ' . $row['documento'] . " " . $nombre_proveedor . " documento " . $numero_comprobante . " " . $concepto_retencion, 
		'valor_debe' => '0', 'valor_haber' => number_format($row['total'], 2, '.', ''), 
		'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 
		'id_relacion' => $row['id'], 'id_cli_pro' => $id_proveedor, 'transaccion' => $row['transaccion']);
		$cuenta_subtotal = generar_asiento($con, $ruc_empresa, $action, $array_datos_retencion, $row['id_cli_pro']);
	}
	
	echo mostrar_resultados_asientos_retenciones_compras($con, $ruc_empresa, $action);
}

//para traer documentos de compras 
if ($action == 'compras_servicios') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));

	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_proveedor = "";
	} else {
		$opcion_proveedor = "and enc.id_proveedor='" . $id_cliente_proveedor . "'";
	}

	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, 
	documento, subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, total, 
	fecha_documento, tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
		(SELECT null, enc.ruc_empresa, enc.id_proveedor, enc.numero_documento ,sum(cuc.subtotal), 
		cuc.det_impuesto, '0',	sum(cuc.descuento),	enc.propina, enc.otros_val, enc.total_compra, enc.fecha_compra, enc.id_comprobante, 'COMPRAS_SERVICIOS', 
		enc.id_encabezado_compra, '" . $codigo_unico . "', '0', pro.razon_social FROM cuerpo_compra cuc INNER JOIN 
		encabezado_compra enc ON enc.codigo_documento = cuc.codigo_documento INNER JOIN proveedores as pro ON 
		pro.id_proveedor=enc.id_proveedor WHERE cuc.ruc_empresa = '" . $ruc_empresa . "' and enc.ruc_empresa = '" . $ruc_empresa . "' 
		and DATE_FORMAT(enc.fecha_compra, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' 
		and '" . date("Y/m/d", strtotime($hasta)) . "' and enc.id_registro_contable='0' $opcion_proveedor 
		group by cuc.codigo_documento, cuc.det_impuesto) ");

	//actualizar el total de las compras
	actualizar_iva($con, $ruc_empresa);
	actualizar_total($con, $ruc_empresa);
	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	$ultimo_registro=0;
	$query_documentos = traer_info_documentos($con,  $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
		$sql_cuenta_comprobantes = mysqli_query($con, "SELECT * FROM comprobantes_autorizados WHERE id_comprobante = '".$row['tipo_documento']."' ");
		$row_comprobantes = mysqli_fetch_array($sql_cuenta_comprobantes);
		$comprobante = $row_comprobantes['comprobante'];
		$nombre_proveedor = strtoupper($row['nombre_cli_pro']);

		if ($row['id_registro'] != $ultimo_registro){
			numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
				$numero_asiento++;
			}else{
				$numero_asiento++;
			}
		$ultimo_registro=$row['id_registro'];

	//para el subtotal de compras si esta asignado una cuenta a un proveedor
	$sql_cuenta_proveedor_individual = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_programados WHERE id_pro_cli = '".$row['id_cli_pro']."' and tipo_asiento = 'proveedor' and ruc_empresa ='".$ruc_empresa."' ");
	$row_cuenta_proveedor_individual = mysqli_fetch_array($sql_cuenta_proveedor_individual);
	$cuenta_proveedor_individual = $row_cuenta_proveedor_individual['numrows'];

	//para el subtotal de compras si esta tarifa de iva
	$sql_cuenta_tarifa_iva = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_programados WHERE tipo_asiento = 'tarifa_iva_compras' and ruc_empresa ='".$ruc_empresa."' ");
	$row_cuenta_tarifa_iva = mysqli_fetch_array($sql_cuenta_tarifa_iva);
	$cuenta_proveedor_tarifa_iva = $row_cuenta_tarifa_iva['numrows'];

	//para cuando no esta asignado un proveedor ni una tarifa de iva
	$sql_contable_subtotal_venta_general = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCCGP' ");
	$row_contable_subtotal_venta_general = mysqli_fetch_array($sql_contable_subtotal_venta_general);
	$id_subtotal_compra_general = $row_contable_subtotal_venta_general['id_asiento_tipo'];
	
	if ($row['tipo_documento'] != 4) {//para facturas y demas documentos exepto nota de credito
		$valor_debe_subtotal=number_format($row['subtotal'], 2, '.', '');
		$valor_haber_subtotal=number_format(0, 2, '.', '');
		$valor_debe_iva=number_format($row['valor_iva'], 2, '.', '');
		$valor_haber_iva=number_format(0, 2, '.', '');
		$valor_debe_pagar=number_format(0, 2, '.', '');
		$valor_haber_pagar=number_format($row['total'], 2, '.', '');
	}

	if ($row['tipo_documento'] == 4) { //4 es nota de credito
		$valor_debe_subtotal=number_format(0, 2, '.', '');
		$valor_haber_subtotal=number_format($row['subtotal'], 2, '.', '');
		$valor_debe_iva=number_format(0, 2, '.', '');
		$valor_haber_iva=number_format($row['valor_iva'], 2, '.', '');
		$valor_debe_pagar=number_format($row['total'], 2, '.', '');
		$valor_haber_pagar=number_format(0, 2, '.', '');
	}

			$array_datos_subtotal = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Compra/Servicio con '.$comprobante ." ". $row['documento'] . " " . $nombre_proveedor, 'valor_debe' => $valor_debe_subtotal, 'valor_haber' => $valor_haber_subtotal, 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
			
			if($cuenta_proveedor_individual>0){
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'proveedor', $array_datos_subtotal, $row['id_cli_pro']);
			}else if($cuenta_proveedor_tarifa_iva>0){
			$sql_tipo_tarifa_iva = mysqli_query($con, "SELECT * FROM asientos_programados WHERE tipo_asiento = 'tarifa_iva_compras' and ruc_empresa ='".$ruc_empresa."' and id_pro_cli='".$row['tipo_iva']."' ");
			$row_tipo_tarifa_iva = mysqli_fetch_array($sql_tipo_tarifa_iva);
			$id_tarifa_iva = $row_tipo_tarifa_iva['id_pro_cli'];	
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'tarifa_iva_compras', $array_datos_subtotal, $id_tarifa_iva);
			}else{
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, $action, $array_datos_subtotal, $id_subtotal_compra_general);
			}


			//PARA LA CUENTA CONTABLE DE OTROS VALORES COMO PROPINA O TASA
		if(($row['otro_val_uno'] + $row['otro_val_dos'])>0){
			$sql_subtotal_otros = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCOP' ");
			$row_subtotal_otros = mysqli_fetch_array($sql_subtotal_otros);
			$id_subtotal_otros = $row_subtotal_otros['id_asiento_tipo'];
			$array_subtotal_otros = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Compra/Servicio con '.$comprobante ." ". $row['documento'] . ' ' . $nombre_proveedor, 'valor_debe' => number_format($row['otro_val_uno'] + $row['otro_val_dos'], 2, '.', ''), 'valor_haber' => number_format(0, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
			$cuenta_subtotal_otros = generar_asiento($con, $ruc_empresa, $action, $array_subtotal_otros, $id_subtotal_otros);
			}

			if ($row['valor_iva'] > 0) {
				$sql_cuenta_iva = mysqli_query($con, "SELECT * FROM asientos_programados WHERE tipo_asiento = 'iva_compras' and ruc_empresa ='".$ruc_empresa."' and id_pro_cli='".$row['tipo_iva']."' ");
				$row_cuenta_iva = mysqli_fetch_array($sql_cuenta_iva);
				$id_cuenta_iva = $row_cuenta_iva['id_pro_cli'];
				$array_datos_iva = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'IVA en Compra/Servicio con ' .$comprobante." ". $row['documento'] . " " . $nombre_proveedor, 'valor_debe' => $valor_debe_iva, 'valor_haber' => $valor_haber_iva, 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_iva = generar_asiento($con, $ruc_empresa, 'iva_compras', $array_datos_iva, $id_cuenta_iva);
			}	

				//para la cuenta por pagar
				$sql_contable_cxp = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo = 'CCXPP' ");
				$row_contable_cxp = mysqli_fetch_array($sql_contable_cxp);
				$id_cxp = $row_contable_cxp['id_asiento_tipo'];
				$array_datos_por_pagar = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Compra/Servicio con ' .$comprobante." ". $row['documento'] . " " . $nombre_proveedor, 'valor_debe' => $valor_debe_pagar, 'valor_haber' => $valor_haber_pagar, 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_porpagar = generar_asiento($con, $ruc_empresa, $action, $array_datos_por_pagar, $id_cxp);		
	}
		
	echo mostrar_resultados_asientos_compras($con, $ruc_empresa, 'compras_servicios');
}

//para traer los documentos de ingreso
if ($action == 'ingresos') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));

	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_cliente = "";
	} else {
		$opcion_cliente = "and ing.id_cli_pro='" . $id_cliente_proveedor . "'";
	}
	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, 
	documento, subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, total, 
	fecha_documento, tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
	(SELECT null, '" . $ruc_empresa . "', ing.id_cli_pro, ing.numero_ing_egr, '0','0','0','0','0','0', 
	ing.valor_ing_egr, ing.fecha_ing_egr, '', 'INGRESOS', ing.id_ing_egr , '" . $codigo_unico . "','0', ing.nombre_ing_egr 
	FROM ingresos_egresos as ing WHERE ing.ruc_empresa = '" . $ruc_empresa . "' and 
	DATE_FORMAT(ing.fecha_ing_egr, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' and '" . date("Y/m/d", strtotime($hasta)) . "' and
	 ing.codigo_contable='0' and ing.tipo_ing_egr='INGRESO' and ing.estado='OK' $opcion_cliente and ing.valor_ing_egr>0) ");
	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	$ultimo_registro=0;
	$query_documentos = traer_info_documentos($con,  $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
			$nombre_cliente_proveedor = strtoupper($row['nombre_cli_pro']);
			
			$busca_detalle_pago = mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE id_ing_egr = '" . $row['id_registro'] . "' ");
			$row_detalle_pago = mysqli_fetch_array($busca_detalle_pago);
			$codigo_unico_documento = $row_detalle_pago['codigo_documento'];

			if ($row['id_registro'] != $ultimo_registro){
				numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
					$numero_asiento++;
				}else{
					$numero_asiento++;
				}
			$ultimo_registro=$row['id_registro'];

			//detalle de los cobros		
			$busca_pago_ingreso_egreso = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr WHERE codigo_documento = '" . $codigo_unico_documento . "' ");
			while ($row_pago_ingresos_egresos = mysqli_fetch_array($busca_pago_ingreso_egreso)) {
				$codigo_forma_pago = $row_pago_ingresos_egresos['codigo_forma_pago'];
				$id_cuenta = $row_pago_ingresos_egresos['id_cuenta'];
				$valor_forma_pago = $row_pago_ingresos_egresos['valor_forma_pago'];
				
				//cuendo esta pagado con la cuenta bancaria
				if ($id_cuenta > 0) {
					$cuentas = mysqli_query($con, "SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.id_cuenta ='" . $id_cuenta . "'");
					$row_cuenta = mysqli_fetch_array($cuentas);
					$cuenta_bancaria = strtoupper($row_cuenta['cuenta_bancaria']);
					$forma_pago = $row_pago_ingresos_egresos['detalle_pago'];
					switch ($forma_pago) {
						case "D":
							$tipo = 'Depósito';
							break;
						case "T":
							$tipo = 'Transferencia';
							break;
					}
					$detalle_pago = $tipo . " " . $cuenta_bancaria;
					$array_pago_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de ingreso N. ' . $row['documento'] . " " . $nombre_cliente_proveedor . " cobrado con: " . $detalle_pago, 'valor_debe' => number_format($valor_forma_pago, 2, '.', ''), 'valor_haber' => '0', 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
					$cuenta_pago_ingreso = generar_asiento($con, $ruc_empresa, 'bancos', $array_pago_ingreso_egreso, $id_cuenta);
				} 

				//cuando esta pagado con otras formas de pagos
				if($codigo_forma_pago>0) {
					$opciones_pagos = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE id ='" . $codigo_forma_pago . "'");
					$row_opciones_pagos = mysqli_fetch_array($opciones_pagos);
					$forma_pago = strtoupper($row_opciones_pagos['descripcion']);
					$detalle_pago = $forma_pago;
					$array_pago_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de ingreso N. ' . $row['documento'] . " " . $nombre_cliente_proveedor . " cobrado con: " . $detalle_pago, 'valor_debe' => number_format($valor_forma_pago, 2, '.', ''), 'valor_haber' => '0', 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
					$cuenta_pago_ingreso = generar_asiento($con, $ruc_empresa, 'opcion_cobro', $array_pago_ingreso_egreso, $codigo_forma_pago);
				}			
			}

			//detalle del ingreso
			$busca_detalle_ingreso_egreso = mysqli_query($con, "SELECT * FROM detalle_ingresos_egresos WHERE codigo_documento = '" . $codigo_unico_documento . "' ");
			while ($row_detalle_ingresos_egresos = mysqli_fetch_array($busca_detalle_ingreso_egreso)) {
				$tipo_ing_egr = $row_detalle_ingresos_egresos['tipo_ing_egr'];
				$detalle = $row_detalle_ingresos_egresos['detalle_ing_egr'];
				$valor_ing_egr = $row_detalle_ingresos_egresos['valor_ing_egr'];
			
				if(!is_numeric($tipo_ing_egr)){
				$tipo_asiento = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo='" . $tipo_ing_egr . "' ");
				$row_asiento = mysqli_fetch_assoc($tipo_asiento);
				$transaccion = $row_asiento['tipo_asiento'];
				$id_asiento = $row_asiento['id_asiento_tipo'];
				$detalle= " Concepto: ".$transaccion. " Detalle: ". $detalle;
				$array_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de ingreso N. ' . $row['documento'] . " " . $detalle, 'valor_debe' => '0', 'valor_haber' => number_format($valor_ing_egr, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'ventas', $array_ingreso_egreso, $id_asiento);
				}else{
				$tipo_pago = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE id='" . $tipo_ing_egr . "' and tipo_opcion ='1' ");
				$row_tipo_pago = mysqli_fetch_assoc($tipo_pago);
				$transaccion = $row_tipo_pago['descripcion'];
				$id_asiento = $row_tipo_pago['id'];
				$detalle= " Concepto: ".$transaccion. " Detalle: ". $detalle;
				$array_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de ingreso N. ' . $row['documento'] . " " . $detalle, 'valor_debe' => '0', 'valor_haber' => number_format($valor_ing_egr, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'opcion_ingreso', $array_ingreso_egreso, $id_asiento);
				}

			}	
	}
	
	echo mostrar_resultados_asientos_ingresos($con, $ruc_empresa, $action);
}

//para traer los documentos de egreso
if ($action == 'egresos') {
	$desde = mysqli_real_escape_string($con, (strip_tags($_REQUEST['desde'], ENT_QUOTES)));
	$hasta = mysqli_real_escape_string($con, (strip_tags($_REQUEST['hasta'], ENT_QUOTES)));
	$id_cliente_proveedor = mysqli_real_escape_string($con, (strip_tags($_REQUEST['cliente_proveedor'], ENT_QUOTES)));

	limpiar_tablas_tmp($con, $ruc_empresa);
	//buscar los documentos a contabilizar
	if (empty($id_cliente_proveedor)) {
		$opcion_cliente = "";
	} else {
		$opcion_cliente = "and ing.id_cli_pro='" . $id_cliente_proveedor . "'";
	}
	$resumen = mysqli_query($con, "INSERT INTO contabilizar_documentos_tmp (id, ruc_empresa, id_cli_pro, 
	documento, subtotal, tipo_iva, valor_iva, descuento, otro_val_uno, otro_val_dos, total, 
	fecha_documento, tipo_documento, transaccion, id_registro, codigo_unico, numero_asiento, nombre_cli_pro) 
	(SELECT null, '" . $ruc_empresa . "', ing.id_cli_pro, ing.numero_ing_egr, '0','0','0','0','0','0', 
	ing.valor_ing_egr, ing.fecha_ing_egr, '', 'EGRESOS', ing.id_ing_egr , '" . $codigo_unico . "','0', ing.nombre_ing_egr 
	FROM ingresos_egresos as ing WHERE ing.ruc_empresa = '" . $ruc_empresa . "' and 
	DATE_FORMAT(ing.fecha_ing_egr, '%Y/%m/%d') between '" . date("Y/m/d", strtotime($desde)) . "' and '" . date("Y/m/d", strtotime($hasta)) . "' and
	 ing.codigo_contable='0' and ing.tipo_ing_egr='EGRESO' and ing.estado='OK' $opcion_cliente and ing.valor_ing_egr>0) ");
	$numero_asiento=siguiente_numero_asiento($con, $ruc_empresa);
	$ultimo_registro=0;
	$query_documentos = traer_info_documentos($con,  $ruc_empresa, $action);
	while ($row = mysqli_fetch_array($query_documentos)) {
			$nombre_cliente_proveedor = strtoupper($row['nombre_cli_pro']);
			
			$busca_detalle_pago = mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE id_ing_egr = '" . $row['id_registro'] . "' ");
			$row_detalle_pago = mysqli_fetch_array($busca_detalle_pago);
			$codigo_unico_documento = $row_detalle_pago['codigo_documento'];

			if ($row['id_registro'] != $ultimo_registro){
				numero_asiento_contable($con, $numero_asiento, $row['id_registro']);
					$numero_asiento++;
				}else{
					$numero_asiento++;
				}
			$ultimo_registro=$row['id_registro'];

			//detalle del egreso
			$busca_detalle_ingreso_egreso = mysqli_query($con, "SELECT * FROM detalle_ingresos_egresos WHERE codigo_documento = '" . $codigo_unico_documento . "' ");
			while ($row_detalle_ingresos_egresos = mysqli_fetch_array($busca_detalle_ingreso_egreso)) {
				$tipo_ing_egr = $row_detalle_ingresos_egresos['tipo_ing_egr'];
				$detalle = $row_detalle_ingresos_egresos['detalle_ing_egr'];
				$valor_ing_egr = $row_detalle_ingresos_egresos['valor_ing_egr'];
			
				if(!is_numeric($tipo_ing_egr)){
				$tipo_asiento = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo='" . $tipo_ing_egr . "' ");
				$row_asiento = mysqli_fetch_assoc($tipo_asiento);
				$transaccion = $row_asiento['tipo_asiento'];
				$id_asiento = $row_asiento['id_asiento_tipo'];
				$detalle= " Concepto: ".$transaccion. " Detalle: ". $detalle;
				$array_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de egreso N. ' . $row['documento'] . " " . $detalle, 'valor_debe' => number_format($valor_ing_egr, 2, '.', ''), 'valor_haber' => number_format(0, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'compras_servicios', $array_ingreso_egreso, $id_asiento);
				}else{
				$tipo_pago = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE id='" . $tipo_ing_egr . "' and tipo_opcion ='2' ");
				$row_tipo_pago = mysqli_fetch_assoc($tipo_pago);
				$transaccion = $row_tipo_pago['descripcion'];
				$id_asiento = $row_tipo_pago['id'];
				$detalle= " Concepto: ".$transaccion. " Detalle: ". $detalle;
				$array_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de egreso N. ' . $row['documento'] . " " . $detalle, 'valor_debe' => number_format($valor_ing_egr, 2, '.', ''), 'valor_haber' => number_format(0, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
				$cuenta_subtotal = generar_asiento($con, $ruc_empresa, 'opcion_egreso', $array_ingreso_egreso, $id_asiento);
				}

			}

			//detalle de los pagos		
			$busca_pago_ingreso_egreso = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr WHERE codigo_documento = '" . $codigo_unico_documento . "' ");
			while ($row_pago_ingresos_egresos = mysqli_fetch_array($busca_pago_ingreso_egreso)) {
				$codigo_forma_pago = $row_pago_ingresos_egresos['codigo_forma_pago'];
				$id_cuenta = $row_pago_ingresos_egresos['id_cuenta'];
				$valor_forma_pago = $row_pago_ingresos_egresos['valor_forma_pago'];
				$cheque = $row_pago_ingresos_egresos['cheque'];
				
				//cuendo esta pagado con la cuenta bancaria
				if ($id_cuenta > 0) {
					$cuentas = mysqli_query($con, "SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.id_cuenta ='" . $id_cuenta . "'");
					$row_cuenta = mysqli_fetch_array($cuentas);
					$cuenta_bancaria = strtoupper(' cta: '.$row_cuenta['cuenta_bancaria']);
					$forma_pago = $row_pago_ingresos_egresos['detalle_pago'];
					switch ($forma_pago) {
						case "C":
							$tipo = 'Cheque #'. $cheque;
							break;
						case "D":
							$tipo = 'Débito';
							break;
						case "T":
							$tipo = 'Transferencia';
							break;
					}
					$detalle_pago = $tipo . " " . $cuenta_bancaria;
					$array_pago_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de egreso N. ' . $row['documento'] . " " . $nombre_cliente_proveedor . " pagado con: " . $detalle_pago, 'valor_debe' => number_format(0, 2, '.', ''), 'valor_haber' => number_format($valor_forma_pago, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
					$cuenta_pago_ingreso = generar_asiento($con, $ruc_empresa, 'bancos', $array_pago_ingreso_egreso, $id_cuenta);
				} 

				//cuando esta pagado con otras formas de pagos
				if($codigo_forma_pago>0) {
					$opciones_pagos = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE id ='" . $codigo_forma_pago . "'");
					$row_opciones_pagos = mysqli_fetch_array($opciones_pagos);
					$forma_pago = strtoupper($row_opciones_pagos['descripcion']);
					$detalle_pago = $forma_pago;
					$array_pago_ingreso_egreso = array('fecha_documento' => $row['fecha_documento'], 'detalle' => 'Comprobante de egreso N. ' . $row['documento'] . " " . $nombre_cliente_proveedor . " pagado con: " . $detalle_pago, 'valor_debe' => number_format(0, 2, '.', ''), 'valor_haber' => number_format($valor_forma_pago, 2, '.', ''), 'id_registro' => $row['id_registro'], 'codigo_unico' => $row['codigo_unico'], 'id_relacion' => $row['id'], 'id_cli_pro' => $row['id_cli_pro'], 'transaccion' => $row['transaccion']);
					$cuenta_pago_ingreso = generar_asiento($con, $ruc_empresa, 'opcion_pago', $array_pago_ingreso_egreso, $codigo_forma_pago);
				}			
			}
	}
	
	echo mostrar_resultados_asientos_egresos($con, $ruc_empresa, $action);
}


//FUNCIONES

function siguiente_numero_asiento($con, $ruc_empresa){
	$query_numero_asiento = mysqli_query($con, "SELECT max(numero_asiento) as numero_asiento FROM encabezado_diario WHERE ruc_empresa='".$ruc_empresa."'");
	$row = mysqli_fetch_array($query_numero_asiento);
	return $row['numero_asiento']+1;
}

function limpiar_tablas_tmp($con, $ruc_empresa){
	$eliminar_documentos = mysqli_query($con, "DELETE FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' ");
	$eliminar_asientos = mysqli_query($con, "DELETE FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' ");
}

function traer_info_documentos($con, $ruc_empresa, $action){
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' and transaccion='" . $action . "' order by fecha_documento asc");
return $query_documentos;
}

//para mostrar los resultados de ventas		
function mostrar_resultados_asientos_ventas($con, $ruc_empresa)
{
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");//and transaccion='" . $action . "'
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de facturas de venta</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Cliente</th>
								<th>Factura</th>
								<th>Subtotal</th>
								<th>Iva</th>
								<th>Por cobrar</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$id_cliente = $row['id_cli_pro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$factura = $row['documento'];
								$descuento = $row['descuento'];
								$subtotal = $row['subtotal'] + $row['otro_val_uno'] + $row['otro_val_dos'];
								$tipo_iva = $row['tipo_iva'];
								$iva = $row['valor_iva'];
								$total = $row['total'];
								$busca_cliente = mysqli_query($con, "SELECT * FROM clientes WHERE id = '" . $id_cliente . "' ");
								$row_cliente = mysqli_fetch_array($busca_cliente);
								$nombre_cliente = $row_cliente['nombre'];

							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_cliente); ?></td>
									<td><?php echo $factura; ?></td>
									<td><?php echo number_format($subtotal, 2, '.', ''); ?></td>
									<td><?php echo number_format($iva, 2, '.', ''); ?></td>
									<td><?php echo number_format($subtotal+ $iva, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>', 'ventas');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");//and tipo_asiento='" . $action . "'
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','ventas');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos de ventas</button>
	</div>

<?php
}

//para mostrar los resultados de nc ventas		
function mostrar_resultados_asientos_nc_ventas($con, $ruc_empresa)
{
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de notas de crédito en ventas</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Cliente</th>
								<th>NC</th>
								<th>Subtotal</th>
								<th>Iva</th>
								<th>Por descontar</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$id_cliente = $row['id_cli_pro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$factura = $row['documento'];
								$descuento = $row['descuento'];
								$subtotal = $row['subtotal'] + $row['otro_val_uno'] + $row['otro_val_dos'];
								$tipo_iva = $row['tipo_iva'];
								$iva = $row['valor_iva'];
								$total = $row['total'];
								$busca_cliente = mysqli_query($con, "SELECT * FROM clientes WHERE id = '" . $id_cliente . "' ");
								$row_cliente = mysqli_fetch_array($busca_cliente);
								$nombre_cliente = $row_cliente['nombre'];
	
							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_cliente); ?></td>
									<td><?php echo $factura; ?></td>
									<td><?php echo number_format($subtotal, 2, '.', ''); ?></td>
									<td><?php echo number_format($iva, 2, '.', ''); ?></td>
									<td><?php echo number_format($total, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>','nc_ventas');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','nc_ventas');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos de NC en ventas</button>
	</div>

<?php
}

//para mostrar los resultados de retenciones de ventas		
function mostrar_resultados_asientos_retenciones_ventas($con, $ruc_empresa)
{

	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de retenciones de ventas</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Cliente/concepto</th>
								<th>No. Documento</th>
								<th>Valor retenido</th>
								<th>Tipo retención</th>
								<th>Valor cobrado</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$codigo_ret = $row['id_cli_pro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$retencion = $row['documento'];
								$subtotal = $row['subtotal'];
								$tipo_retencion = $row['tipo_iva'];
								$total = $row['total'];

								switch ($tipo_retencion) {
									case "1":
										$tipo_retencion = 'RENTA';
										break;
									case "2":
										$tipo_retencion = 'IVA';
										break;
									case "3":
										$tipo_retencion = 'ISD';
										break;
								}

								$busca_clientes = mysqli_query($con, "SELECT cli.nombre as nombre, enc_ret.numero_documento as numero_documento FROM encabezado_retencion_venta as enc_ret INNER JOIN clientes as cli ON enc_ret.id_cliente=cli.id WHERE enc_ret.id_encabezado_retencion = '" . $id_registro . "' ");
								$row_clientes = mysqli_fetch_array($busca_clientes);
								$nombre_cliente = $row_clientes['nombre'];

								$busca_conceptos_retencion = mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret = '" . $codigo_ret . "' ");
								$row_conceptos_retencion = mysqli_fetch_array($busca_conceptos_retencion);
								$concepto_retencion = $row_conceptos_retencion['concepto_ret'];

							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_cliente . " " . $concepto_retencion); ?></td>
									<td><?php echo $retencion; ?></td>
									<td><?php echo number_format($subtotal, 2, '.', ''); ?></td>
									<td><?php echo $tipo_retencion; ?></td>
									<td><?php echo number_format($total, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>','retenciones_ventas');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','retenciones_ventas');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos contables por retenciones de ventas</button>
	</div>

<?php
}

//para mostrar los resultados de retenciones de compras	
function mostrar_resultados_asientos_retenciones_compras($con, $ruc_empresa)
{
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de retenciones de compras</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Proveedor/concepto</th>
								<th>No. Documento</th>
								<th>Valor retenido</th>
								<th>Tipo retención</th>
								<th>Valor pagado</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$codigo_ret = $row['id_cli_pro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$retencion = $row['documento'];
								$subtotal = $row['subtotal'];
								$tipo_retencion = $row['tipo_iva'];
								$total = $row['total'];

								switch ($tipo_retencion) {
									case "1":
										$tipo_retencion = 'RENTA';
										break;
									case "2":
										$tipo_retencion = 'IVA';
										break;
									case "3":
										$tipo_retencion = 'ISD';
										break;
								}

								$busca_proveedores = mysqli_query($con, "SELECT pro.razon_social as razon_social, enc_ret.numero_comprobante as numero_comprobante FROM encabezado_retencion as enc_ret INNER JOIN proveedores as pro ON enc_ret.id_proveedor=pro.id_proveedor WHERE enc_ret.id_encabezado_retencion = '" . $id_registro . "' ");
								$row_proveedores = mysqli_fetch_array($busca_proveedores);
								$nombre_proveedor = $row_proveedores['razon_social'];

							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_proveedor); ?></td>
									<td><?php echo $retencion; ?></td>
									<td><?php echo number_format($subtotal, 2, '.', ''); ?></td>
									<td><?php echo $tipo_retencion; ?></td>
									<td><?php echo number_format($total, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>','retenciones_compras');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','retenciones_compras');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos contables por retenciones de compras</button>
	</div>

<?php
}


//para mostrar los resultados de compras	
function mostrar_resultados_asientos_compras($con, $ruc_empresa)
{
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de comprobantes de adquisiciones</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Proveedor</th>
								<th>Documento</th>
								<th>Subtotal</th>
								<th>Iva</th>
								<th>Por pagar</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$id_proveedor = $row['id_cli_pro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$factura = $row['documento'];
								$subtotal = $row['subtotal'] + $row['otro_val_uno'] + $row['otro_val_dos'];
								$iva = $row['valor_iva'];
								$total = $subtotal + $iva;

								$busca_proveedor = mysqli_query($con, "SELECT * FROM proveedores WHERE id_proveedor = '" . $id_proveedor . "' ");
								$row_proveedor = mysqli_fetch_array($busca_proveedor);
								$nombre_proveedor = $row_proveedor['razon_social'];

							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_proveedor); ?></td>
									<td><?php echo $factura; ?></td>
									<td><?php echo number_format($subtotal, 2, '.', ''); ?></td>
									<td><?php echo number_format($iva, 2, '.', ''); ?></td>
									<td><?php echo number_format($total, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>','compras_servicios');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','compras_servicios');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos contables por adquisiones de compras y/o servicios</button>
	</div>

<?php
}

//para mostrar los resultados de ingresos	
function mostrar_resultados_asientos_ingresos($con, $ruc_empresa)
{
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de comprobantes de ingreso</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Recibido de</th>
								<th>No. Ingreso</th>
								<th>Total</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$numero_ingreso = $row['documento'];
								$total = $row['total'];

								$busca_nombre_ingreso_egreso = mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE id_ing_egr = '" . $id_registro . "' ");
								$row_ingresos = mysqli_fetch_array($busca_nombre_ingreso_egreso);
								$nombre_cliente_proveedor = $row_ingresos['nombre_ing_egr'];

							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_cliente_proveedor); ?></td>
									<td><?php echo $numero_ingreso; ?></td>
									<td><?php echo number_format($total, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>','ingresos');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','ingresos');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos contables de ingresos</button>
	</div>

<?php
}

//para mostrar los resultados de egresos	
function mostrar_resultados_asientos_egresos($con, $ruc_empresa)
{
	$query_documentos = mysqli_query($con, "SELECT * FROM contabilizar_documentos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' order by fecha_documento asc");
?>
	<div class="panel-group" id="accordion">
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de comprobantes de egreso</a>
			<div id="collapse1" class="panel-collapse collapse">

				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<th>Fecha</th>
								<th>Pagado a</th>
								<th>No. egreso</th>
								<th>Total</th>
								<th class='text-right'>Opciones</th>
							</tr>
							<?php
							while ($row = mysqli_fetch_array($query_documentos)) {
								$id_registro = $row['id_registro'];
								$fecha_documento = date("d-m-Y", strtotime($row['fecha_documento']));
								$numero_egreso = $row['documento'];
								$total = $row['total'];
								$busca_nombre_ingreso_egreso = mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE id_ing_egr = '" . $id_registro . "' ");
								$row_ingresos = mysqli_fetch_array($busca_nombre_ingreso_egreso);
								$nombre_cliente_proveedor = $row_ingresos['nombre_ing_egr'];
							?>
								<tr>
									<td><?php echo $fecha_documento; ?></td>
									<td><?php echo strtoupper($nombre_cliente_proveedor); ?></td>
									<td><?php echo $numero_egreso; ?></td>
									<td><?php echo number_format($total, 2, '.', ''); ?></td>
									<td><span class="pull-right">
											<a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_registro('<?php echo $id_registro; ?>','egresos');"><i class="glyphicon glyphicon-trash"></i></a>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		$query_asientos = mysqli_query($con, "SELECT id_registro as id_registro, id as id, fecha as fecha, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, detalle as detalle, sum(debe) as debe, sum(haber) as haber FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' group by detalle, id_cuenta order by id asc");
		?>
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Asientos contables</a>
			<div id="collapse2" class="panel-collapse collapse">
				<table class="table">
					<tr class="info">
						<th>Fecha</th>
						<th>Código</th>
						<th>Cuenta</th>
						<th>Debe</th>
						<th>Haber</th>
						<th>Detalle</th>
						<th class='text-right'>Opciones</th>
					</tr>
					<?php
					while ($row_asiento = mysqli_fetch_array($query_asientos)) {
						$id_registro = $row_asiento['id_registro'];
					?>
						<tr>
							<td><?php echo $fecha_documento = date("d-m-Y", strtotime($row_asiento['fecha']));; ?></td>
							<td><?php echo $row_asiento['codigo_cuenta']; ?></td>
							<td><?php echo strtoupper($row_asiento['nombre_cuenta']); ?></td>
							<td><?php echo number_format($row_asiento['debe'], 2, '.', ''); ?></td>
							<td><?php echo number_format($row_asiento['haber'], 2, '.', ''); ?></td>
							<td><?php echo $row_asiento['detalle']; ?></td>
							<td><span class="pull-right">
									<a href="#" class='btn btn-danger btn-sm' title='Eliminar item' onclick="eliminar_registro('<?php echo $id_registro; ?>','egresos');"><i class="glyphicon glyphicon-trash"></i></a>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-1">
		<button type="button" id="guardar_asiento_automatico" title="Guardar " class="btn btn-info btn-md" onclick="guardar_asientos_automaticos()"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar asientos contables por egresos</button>
	</div>
<?php
}

if (isset($errors)) {

?>
	<div class="alert alert-danger" role="alert">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong>Error!</strong>
		<?php
		foreach ($errors as $error) {
			echo $error;
		}
		?>
	</div>
<?php
}
if (isset($messages)) {

?>
	<div class="alert alert-success" role="alert">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong>¡Bien hecho!</strong>
		<?php
		foreach ($messages as $message) {
			echo $message;
		}
		?>
	</div>
<?php
}

//actualiza iva en todos los registros
function actualizar_iva($con, $ruc_empresa){
	$actualizar_iva=mysqli_query($con, "UPDATE contabilizar_documentos_tmp as tmp INNER JOIN tarifa_iva as tar_iva ON tar_iva.codigo=tmp.tipo_iva SET tmp.valor_iva = round(tmp.subtotal * (tar_iva.porcentaje_iva/100),2) WHERE tmp.ruc_empresa= '" . $ruc_empresa . "' ");
}

	//actualizar el total de las facturas de ventas
function actualizar_total($con, $ruc_empresa){
	$update_total = mysqli_query($con, "UPDATE contabilizar_documentos_tmp SET total= round((subtotal + valor_iva + otro_val_uno + otro_val_dos),2)  WHERE ruc_empresa = '" . $ruc_empresa . "' ");
}

//actualizar numero de asiento por cada registro de docuemntos

function numero_asiento_contable($con, $numero_asiento, $id_registro){
		$update_numero_asiento = mysqli_query($con, "UPDATE contabilizar_documentos_tmp SET numero_asiento='".$numero_asiento."' WHERE id_registro = '" . $id_registro . "' ");
}

//para generar el asiento	
function generar_asiento($con, $ruc_empresa, $tipo_asiento, $arreglo_datos, $id_cli_pro)
{
	$asiento_tipo = mysqli_query($con, "SELECT * FROM asientos_programados as asi_pro INNER JOIN plan_cuentas as plan ON plan.id_cuenta=asi_pro.id_cuenta WHERE asi_pro.ruc_empresa = '" . $ruc_empresa . "' and asi_pro.tipo_asiento='" . $tipo_asiento . "' and asi_pro.id_pro_cli ='" . $id_cli_pro . "' ");
	$row_asiento_tipo = mysqli_fetch_array($asiento_tipo);
	$id_cuenta = $row_asiento_tipo['id_cuenta'];
	$codigo_cuenta = $row_asiento_tipo['codigo_cuenta'];
	$nombre_cuenta = $row_asiento_tipo['nombre_cuenta'];

	$buscar_asiento_existente = mysqli_query($con, "SELECT count(*) as numrows FROM asientos_automaticos_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' and id_cuenta='" . $id_cuenta . "' and id_registro ='" . $arreglo_datos['id_registro'] . "' ");
	$row_asiento_existente = mysqli_fetch_array($buscar_asiento_existente);
	$existente = $row_asiento_existente['numrows'];
	if($existente>0){
		$actualizar_asiento_tmp=mysqli_query($con, "UPDATE asientos_automaticos_tmp SET debe = round(debe + '".$arreglo_datos['valor_debe']."',2), haber = round(haber + '".$arreglo_datos['valor_haber']."',2)  WHERE id_cuenta= '" . $id_cuenta . "' and id_registro= '" . $arreglo_datos['id_registro'] . "' and ruc_empresa='".$ruc_empresa."'");
	}else{
	$guardar_asiento_tmp = mysqli_query($con, "INSERT INTO asientos_automaticos_tmp VALUE (null, '" . $ruc_empresa . "', '" . $arreglo_datos['fecha_documento'] . "', '" . $id_cuenta . "', '" . $codigo_cuenta . "', '" . $nombre_cuenta . "', '" . $arreglo_datos['detalle'] . "', '" . $arreglo_datos['valor_debe'] . "', '" . $arreglo_datos['valor_haber'] . "', '" . $arreglo_datos['id_registro'] . "','" . $arreglo_datos['transaccion'] . "','" . $arreglo_datos['id_cli_pro'] . "', '" . $arreglo_datos['codigo_unico'] . "', '" . $arreglo_datos['id_relacion'] . "')");
	}
}

?>