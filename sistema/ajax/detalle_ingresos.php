<?PHP
include("../ajax/detalle_documento.php");
//include("../conexiones/conectalogin.php");
//session_start();
$con = conenta_login();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];

$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

if ($action == 'saldos_cuentas_por_cobrar') {
	generar_cuentas_por_cobrar();
}

if ($action == 'actualiza_ingreso_tmp') {
	actualiza_ingreso_tmp();
}

//ACTUALIZA EL REGISTRO que se agrego al egreso actual que se esta haciendo

function actualiza_ingreso_tmp()
{
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	//para borrar las que tienen saldo cero
	//$query_actualiza_ventas_por_cobrar = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sxcp SET sxcp.ing_tmp=(SELECT iet.valor FROM ingresos_egresos_tmp as iet WHERE iet.id_documento=sxcp.id_documento) WHERE sxcp.ruc_empresa='".$ruc_empresa."'");
	$update_ingresos_tmp = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, (SELECT iet.id_documento as registro, sum(iet.valor) as suma_ingreso_tmp FROM ingresos_egresos_tmp as iet WHERE iet.tipo_documento='INGRESO' group by iet.id_documento) as total_ingreso_tmp SET sal_tmp.ing_tmp = total_ingreso_tmp.suma_ingreso_tmp WHERE total_ingreso_tmp.registro=sal_tmp.id_documento");//sal_tmp.ing_tmp +
	//$eliminar_saldos_cero = mysqli_query($con, "DELETE FROM saldo_porcobrar_porpagar WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and total_factura <= (total_nc + total_ing  + ing_tmp + total_ret) ");
}


//cuentas por pagar del ingreso que se esta haciendo

function generar_cuentas_por_cobrar()
{
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$limpiar_saldos = mysqli_query($con, "DELETE FROM saldo_porcobrar_porpagar WHERE id_usuario='" . $id_usuario . "' and mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' and tipo='POR_COBRAR'");

	$query_guarda_ventas_por_cobrar = mysqli_query($con, "INSERT INTO saldo_porcobrar_porpagar (id_saldo, tipo, fecha_documento, id_cli_pro, nombre_cli_pro, numero_documento, id_usuario, ruc_empresa, total_factura, total_nc, total_ing, ing_tmp, total_ret, id_documento) 
	SELECT null, 'POR_COBRAR', enc_fac.fecha_factura , enc_fac.id_cliente, cli.nombre, 
	concat(enc_fac.serie_factura,'-', LPAD(enc_fac.secuencial_factura,9,'0')),'" . $id_usuario . "', '" . $ruc_empresa . "',
	enc_fac.total_factura,0,0,0,0, enc_fac.id_encabezado_factura FROM encabezado_factura as enc_fac INNER JOIN clientes as cli ON cli.id=enc_fac.id_cliente WHERE enc_fac.estado_sri = 'AUTORIZADO' and enc_fac.ruc_empresa = '" . $ruc_empresa . "' ");//mid(enc_fac.ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "'

	$update_nc = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, (SELECT nc.factura_modificada as codigo_registro, round(sum(nc.total_nc),2) as suma_nc FROM encabezado_nc as nc LEFT JOIN encabezado_factura as fac ON nc.factura_modificada=concat(fac.serie_factura,'-',LPAD(fac.secuencial_factura,9,'0')) and nc.id_cliente=fac.id_cliente WHERE nc.ruc_empresa = '" . $ruc_empresa . "' group by nc.factura_modificada ) as total_nc SET sal_tmp.total_nc = total_nc.suma_nc WHERE sal_tmp.numero_documento=total_nc.codigo_registro ");
	$update_ingresos = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, (SELECT detie.codigo_documento_cv as codigo_registro, round(sum(detie.valor_ing_egr),2) as suma_ingresos FROM detalle_ingresos_egresos as detie INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=detie.codigo_documento WHERE detie.estado ='OK' and detie.tipo_ing_egr='CCXCC' and detie.tipo_documento='INGRESO' and mid(detie.ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' group by detie.codigo_documento_cv ) as total_ingresos SET sal_tmp.total_ing = total_ingresos.suma_ingresos WHERE sal_tmp.id_documento=total_ingresos.codigo_registro ");
	$update_retenciones = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, (SELECT ret_ven.numero_documento as registro, round(sum(ret_ven.valor_retenido),2) as suma_retenciones FROM cuerpo_retencion_venta as ret_ven WHERE mid(ret_ven.ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' group by ret_ven.numero_documento) as total_retenciones SET sal_tmp.total_ret = total_retenciones.suma_retenciones WHERE replace(sal_tmp.numero_documento,'-','')=total_retenciones.registro");
	$eliminar_saldos_cero = mysqli_query($con, "DELETE FROM saldo_porcobrar_porpagar WHERE mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' and total_factura <= (total_nc + total_ing  + ing_tmp + total_ret)");
	//$update_ingresos_tmp=mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, (SELECT iet.id_documento as registro, sum(iet.valor) as suma_ingreso_tmp FROM ingresos_egresos_tmp as iet WHERE iet.tipo_documento='INGRESO' group by iet.id_documento) as total_ingreso_tmp SET sal_tmp.ing_tmp = total_ingreso_tmp.suma_ingreso_tmp WHERE total_ingreso_tmp.registro=sal_tmp.id_documento");
	//(select round(sum(total_nc),2) from encabezado_nc as nc where nc.factura_modificada = concat(enc_fac.serie_factura,'-',LPAD(enc_fac.secuencial_factura,9,'0')) and mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and nc.id_cliente = enc_fac.id_cliente )
}

//buscar facturas por cobrar
if ($action == 'facturas_por_cobrar') {
	$q = mysqli_real_escape_string($con, (strip_tags($_REQUEST['fv'], ENT_QUOTES)));
	$aColumns = array('fecha_documento', 'numero_documento', 'nombre_cli_pro'); //Columnas de busqueda
	$sTable = "saldo_porcobrar_porpagar ";
	$sWhere = "WHERE mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "'  AND id_usuario = '" . $id_usuario . "' and tipo='POR_COBRAR' and (total_factura - total_nc- total_ing - ing_tmp - total_ret) > 0 ";
	if ($_GET['fv'] != "") {
		$sWhere = "WHERE (mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "'  AND id_usuario = '" . $id_usuario . "' and tipo='POR_COBRAR' and (total_factura - total_nc- total_ing - ing_tmp - total_ret) > 0 AND ";

		for ($i = 0; $i < count($aColumns); $i++) {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $q . "%' AND mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "'  AND id_usuario = '" . $id_usuario . "' and tipo='POR_COBRAR' and (total_factura - total_nc- total_ing - ing_tmp - total_ret) > 0 OR ";
		}

		$sWhere = substr_replace($sWhere, "AND mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "'  AND id_usuario = '" . $id_usuario . "' and tipo='POR_COBRAR' and (total_factura - total_nc- total_ing - ing_tmp - total_ret) > 0 ", -3);
		$sWhere .= ')';
	}
	$sWhere .= " order by fecha_documento asc";
	include("../ajax/pagination.php"); //include pagination file
	//pagination variables
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 20; //how much records you want to show
	$adjacents  = 10; //gap between pages after number of adjacents
	$offset = ($page - 1) * $per_page;
	//Count the total number of row in your table*/
	$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
	$row = mysqli_fetch_array($count_query);
	$numrows = $row['numrows'];
	$total_pages = ceil($numrows / $per_page);
	$reload = '../facturas.php';
	//main query to fetch the data
	$sql = "SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
	$query = mysqli_query($con, $sql);
	//loop through fetched data
	if ($numrows > 0) {
?>
		<div class="panel panel-info" style="height: 300px;overflow-y: auto;">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th style="padding: 2px;">Fecha</th>
						<th style="padding: 2px;">Cliente</th>
						<th style="padding: 2px;">Número</th>
						<th style="padding: 2px;">Saldo</th>
						<th style="padding: 2px;"><span class="glyphicon glyphicon-copy"></span></th>
						<th style="padding: 2px;">Cobro</th>
						<input type="hidden" value="<?php echo $page; ?>" id="pagina">
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$id_saldo = $row['id_saldo'];
						$id_documento = $row['id_documento'];
						$fecha_documento = $row['fecha_documento'];
						$id_cli_pro = $row['id_cli_pro'];
						$nombre_cli_pro = strtoupper($row['nombre_cli_pro']);
						$numero_documento = $row['numero_documento'];
						$saldo = $row['total_factura'] - $row['total_nc'] - $row['total_ing'] - $row['ing_tmp'] - $row['total_ret'];
						$detalle = $nombre_cli_pro . " " . $numero_documento;
					?>
						<tr>
							<input type="hidden" value="<?php echo $nombre_cli_pro; ?>" id="nombre_cliente_seleccionado<?php echo $id_saldo; ?>">
							<input type="hidden" value="<?php echo $id_cli_pro; ?>" id="id_cliente_seleccionado<?php echo $id_saldo; ?>">
							<input type="hidden" id="saldo<?php echo $id_saldo; ?>" value="<?php echo number_format($saldo, 2, '.', ''); ?>">
							<input type="hidden" name="registros[]" value="<?php echo $id_saldo; ?>">
							<input type="hidden" name="detalle[<?php echo $id_saldo; ?>]" value="<?php echo $detalle; ?>">
							<input type="hidden" name="id_documento[<?php echo $id_saldo; ?>]" value="<?php echo $id_documento; ?>">
							<input type="hidden" name="nombre_cliente[<?php echo $id_saldo; ?>]" value="<?php echo $nombre_cli_pro; ?>">
							<td style="padding: 2px;"><?php echo date("d/m/Y", strtotime($fecha_documento)); ?></td>
							<td style="padding: 2px;" class='col-md-4'><?php echo strtoupper($nombre_cli_pro); ?></td>
							<td style="padding: 2px;"><?php echo $numero_documento; ?></td>
							<td style="padding: 2px;"><?php echo number_format($saldo, 2, '.', ''); ?></td>
							<td style="padding: 2px;"><button class="btn btn-default btn-sm" type="button" title="Pasar valor" onclick="copiar_valor('<?php echo $id_saldo; ?>')" id="linea_copia<?php echo $id_saldo; ?>"><span class="glyphicon glyphicon-arrow-right"></span></button></td>
							<td style="padding: 2px;" class='col-sm-2'><input type="text" style="text-align:right;" class="form-control input-sm" title="Cobro" name="valor_cobro[<?php echo $id_saldo; ?>]" id="valor_cobro<?php echo $id_saldo; ?>" onchange="control_cobro('<?php echo $id_saldo; ?>');"></td>
						</tr>

					<?php
					}
					?>
					<tr>
						<td colspan="7"><span class="pull-right">
						<?php
						echo paginate($reload, $page, $total_pages, $adjacents);
						?></span></td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}
}

//para agregar nuevo iten al ingreso
if ($action == 'agregar_detalle_ingreso') {
	$tipo_ingreso = $_GET["tipo_ingreso"];
	$valor_ingreso = $_GET["valor_ingreso"];
	$detalle_ingreso = $_GET["detalle_ingreso"];
	$beneficiario_ingreso = $_GET["nombre_beneficiario"];
	$agregar_ingreso = mysqli_query($con, "INSERT INTO ingresos_egresos_tmp VALUES (null, 'INGRESO', '" . $beneficiario_ingreso . "', '" . $detalle_ingreso . "', '" . $valor_ingreso . "', '" . $tipo_ingreso . "', '" . $id_usuario . "','0')");
	detalle_nuevo_ingreso();
}

//para agregar forma de pago al ingreso
if ($action == 'agregar_forma_pago_ingreso') {
	$forma_pago = $_GET["forma_pago"];
	$valor_pago = $_GET["valor_pago"];
	$tipo = $_GET["tipo"];
	$origen = $_GET["origen"];
	//$agregar_ingreso = mysqli_query($con, "INSERT INTO formas_pagos_tmp VALUES (null, '".$id_forma_pago."', '".$id_forma_pago."', '".$valor_pago."','', '".$id_usuario."','INGRESO','','')");

	$arrayFormaPago = array();
	$arrayDatos = array('id' => rand(5, 500), 'id_forma' => $forma_pago, 'tipo' => $tipo, 'valor' => $valor_pago, 'origen' => $origen);
	if (isset($_SESSION['arrayFormaPagoIngreso'])) {
		$on = true;
		$arrayFormaPago = $_SESSION['arrayFormaPagoIngreso'];
		for ($pr = 0; $pr < count($arrayFormaPago); $pr++) {
			if ($arrayFormaPago[$pr]['id_forma'] == $forma_pago && $origen == $arrayFormaPago[$pr]['origen']) {
				$arrayFormaPago[$pr]['valor'] += $valor_pago;
				$on = false;
			}
		}
		if ($on) {
			array_push($arrayFormaPago, $arrayDatos);
		}
		$_SESSION['arrayFormaPagoIngreso'] = $arrayFormaPago;
	} else {
		array_push($arrayFormaPago, $arrayDatos);
		$_SESSION['arrayFormaPagoIngreso'] = $arrayFormaPago;
	}
	detalle_nuevo_ingreso();
}

//para agregar detalle de facturas de ventas por cobrar al ingreso

if ($action == 'agregar_detalle_de_facturas') {
	$valor_cobro = $_POST["valor_cobro"];
	$detalle = $_POST["detalle"];
	$id_documento = $_POST["id_documento"];
	$registros = $_POST["registros"];
	$nombre_cliente = $_POST["nombre_cliente"];
	$fecha_agregado = date("Y-m-d H:i:s");
	$cantidad_cobros = array_sum($valor_cobro);
	if ($cantidad_cobros == 0) {
		echo "<script>$.notify('Agregar valores cobrados.','error');
				</script>";
	} else {
		foreach ($registros as $valor) {
			$cobrado = $valor_cobro[$valor];
			$detalle_cobro = $detalle[$valor];
			$documento = $id_documento[$valor];
			$beneficiario_cliente = $nombre_cliente[$valor];
			if ($cobrado > 0) {
				$agregar_detalle_ingreso = mysqli_query($con, "INSERT INTO ingresos_egresos_tmp VALUES (null, 'INGRESO','" . $beneficiario_cliente . "', '" . $detalle_cobro . "', '" . $cobrado . "', 'CCXCC','" . $id_usuario . "', '" . $documento . "')");
			}
		}
		echo "<script>$.notify('Agregado.','success');
		</script>";
	}
	detalle_nuevo_ingreso();
}


//anular ingreso
if ($action == 'anular_ingreso') {
	$id_usuario = $_SESSION['id_usuario'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$codigo_unico = $_POST['codigo_unico'];

	//para anular el registro contable
	include("../clases/anular_registros.php");
	$anular_asiento_contable = new anular_registros();
	$datos_encabezado = mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE codigo_documento = '" . $codigo_unico . "' ");
	$row_encabezado = mysqli_fetch_array($datos_encabezado);
	$id_registro_contable = $row_encabezado['codigo_contable'];
	$anio_documento = date("Y", strtotime($row_encabezado['fecha_ing_egr']));
	$resultado_anular_documento = $anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);

	if ($resultado_anular_documento == "NO") {
		echo "<script>
			$.notify('Primero se debe anular el asiento contable','error');
			</script>";
		exit;
	}

	//anular la factura y los datos de la factura
	if ($delete_detalle = mysqli_query($con, "DELETE FROM detalle_ingresos_egresos WHERE codigo_documento = '" . $codigo_unico . "' and tipo_documento='INGRESO'")
		&& $delete_pagos = mysqli_query($con, "DELETE FROM formas_pagos_ing_egr WHERE codigo_documento = '" . $codigo_unico . "' and tipo_documento='INGRESO' ")
		&& $anular = mysqli_query($con, "UPDATE ingresos_egresos SET detalle_adicional='ANULADO', valor_ing_egr='0.00' WHERE codigo_documento = '" . $codigo_unico . "' and tipo_ing_egr='INGRESO'")
	) {
		echo "<script>
				$.notify('Ingreso anulado exitosamente','success')
				</script>";
	} else {
		echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
	}
}


//eliminar detalle del ingreso
if ($action == 'eliminar_item_ingreso') {
	//$id_registro = $_GET['id_registro'];
	$id_documento = $_GET['id_documento'];
	
	$update_ingresos_tmp = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar SET ing_tmp = '0' WHERE id_documento ='" . $id_documento . "'");
	/*
	$update_ingresos_tmp = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, 
	(SELECT iet.id_documento as registro, round(sum(iet.valor),2) as suma_ingreso_tmp 
	FROM ingresos_egresos_tmp as iet WHERE iet.tipo_documento='INGRESO' 
	group by iet.id_documento) as total_ingreso_tmp SET sal_tmp.ing_tmp = sal_tmp.ing_tmp - total_ingreso_tmp.suma_ingreso_tmp 
	WHERE total_ingreso_tmp.registro=sal_tmp.id_documento");
	*/

	//WHERE total_ingreso_tmp.registro=sal_tmp.id_documento and iet.id_documento ='".$id_documento."'");
	
	$elimina_detalle_ingreso_tmp = mysqli_query($con, "DELETE FROM ingresos_egresos_tmp WHERE id_documento='" . $id_documento . "' and tipo_documento='INGRESO'");
	detalle_nuevo_ingreso();
}

//eliminar detalle pago del ingreso
if ($action == 'eliminar_item_pago') {
	$intid = $_GET['id_registro'];
	$arrData = $_SESSION['arrayFormaPagoIngreso'];
	for ($i = 0; $i < count($arrData); $i++) {
		if ($arrData[$i]['id'] == $intid) {
			unset($arrData[$i]);
			echo "<script>
            $.notify('Eliminado','error');
            </script>";
		}
	}
	sort($arrData); //para reordenar el array
	$_SESSION['arrayFormaPagoIngreso'] = $arrData;
	detalle_nuevo_ingreso();
}


function detalle_nuevo_ingreso()
{
	$con = conenta_login();
	$id_usuario = $_SESSION['id_usuario'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

	$busca_ingreso = mysqli_query($con, "SELECT * FROM ingresos_egresos_tmp WHERE id_usuario = '" . $id_usuario . "' and tipo_documento='INGRESO'");
	//$busca_pagos=mysqli_query($con, "SELECT * FROM formas_pagos_tmp WHERE id_usuario = '".$id_usuario."' and tipo_documento='INGRESO'");
	?>
	<div class="row">
		<div class="panel-group" id="accordion" style="margin-bottom: -10px; margin-top: -15px;">
			<div class="col-md-7">
				<div class="panel panel-info">
					<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de documentos agregados al ingreso</a>
					<div id="collapse1" class="panel-collapse">
						<div class="panel panel-info">
							<table class="table table-hover">
								<tr class="info">
									<td style="padding: 2px;">Nombre</td>
									<td style="padding: 2px;">Detalle</td>
									<td style="padding: 2px;" class='text-center'>Valor</td>
									<td style="padding: 2px;">Tipo</td>
									<td style="padding: 2px;" class='text-center'>Eliminar</td>
								</tr>
								<?php
								$valor_total = 0;
								while ($detalle = mysqli_fetch_array($busca_ingreso)) {
									$id_ingreso = $detalle['id_tmp'];
									$id_documento = $detalle['id_documento'];
									$detalle_ingreso = $detalle['detalle'];
									$beneficiario_cliente = $detalle['beneficiario_cliente'];
									$valor = $detalle['valor'];
									$valor_total += $valor;
									$tipo_transaccion = $detalle['tipo_transaccion'];

									if(!is_numeric($tipo_transaccion)){
										$tipo_asiento = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo='" . $tipo_transaccion . "' ");
										$row_asiento = mysqli_fetch_assoc($tipo_asiento);
										$transaccion = $row_asiento['tipo_asiento'];
									}else{
									$tipo_pago = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE id='" . $tipo_transaccion . "' ");
									$row_tipo_pago = mysqli_fetch_assoc($tipo_pago);
									$transaccion = $row_tipo_pago['descripcion'];
									}
								?>
									<tr>
										<td style="padding: 2px;"><?php echo $beneficiario_cliente; ?></td>
										<td style="padding: 2px;"><?php echo $detalle_ingreso; ?></td>
										<td style="padding: 2px;"><?php echo number_format($valor, 2, '.', ''); ?></td>
										<td style="padding: 2px;"><?php echo $transaccion; ?></td>
										<td style="padding: 2px;" class='text-right'><a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_item_ingreso('<?php echo $id_documento; ?>')"><i class="glyphicon glyphicon-remove"></i></a></td>
									</tr>
								<?php
								}
								?>
								<input type="hidden" id="suma_ingreso" value="<?php echo number_format($valor_total, 2, '.', ''); ?>">
								<tr class="info">
									<th style="padding: 2px;"></th>
									<th style="padding: 2px;">Total</th>
									<th style="padding: 2px;"><?php echo number_format($valor_total, 2, '.', ''); ?></th>
									<th style="padding: 2px;" colspan="6"></th>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel-group" id="accordion_pago" style="margin-bottom: -10px; margin-top: -15px;">
			<div class="col-md-5">
				<div class="panel panel-info">
					<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordion_pago" href="#collapse2"><span class="caret"></span> Detalle de formas de pagos</a>
					<div id="collapse2" class="panel-collapse">
						<div class="panel panel-info">
							<table class="table table-hover">
								<tr class="info">
									<td style="padding: 2px;">Forma</td>
									<td style="padding: 2px;">Valor</td>
									<td style="padding: 2px;">Tipo</td>
									<td style="padding: 2px;" class='text-right'>Eliminar</td>
								</tr>
								<?php
								$valor_total_pago = 0;
								if (isset($_SESSION['arrayFormaPagoIngreso'])) {
									foreach ($_SESSION['arrayFormaPagoIngreso'] as $detalle) {
										$id = $detalle['id'];
										$id_forma = $detalle['id_forma'];
										$tipo = $detalle['tipo'];
										switch ($tipo) {
											case "0":
												$tipo = 'N/A';
												break;
											case "D":
												$tipo = 'Depósito';
												break;
											case "T":
												$tipo = 'Transferencia';
												break;
										}
										$origen = $detalle['origen'];
										$valor_pago = number_format($detalle['valor'], 2, '.', '');
										$valor_total_pago += $valor_pago;

										if ($origen == 1) {
											$query_cobros_pagos = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE id='" . $id_forma . "' and ruc_empresa='" . $ruc_empresa . "' ");
											$row_cobros_pagos = mysqli_fetch_array($query_cobros_pagos);
											$forma_pago = strtoupper($row_cobros_pagos['descripcion']);
										} else {

											$cuentas_bancarias = mysqli_query($con, "SELECT concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.id_cuenta ='" . $id_forma . "'");
											$row_cuentas_bancarias = mysqli_fetch_array($cuentas_bancarias);
											$forma_pago = strtoupper($row_cuentas_bancarias['cuenta_bancaria']);
										}
								?>
										<tr>
											<td style="padding: 2px;"><?php echo $forma_pago; ?></td>
											<td style="padding: 2px;"><?php echo number_format($valor_pago, 2, '.', ''); ?></td>
											<td style="padding: 2px;"><?php echo $tipo; ?></td>
											<td style="padding: 2px;" class='text-right'><a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_item_pago('<?php echo $id; ?>')"><i class="glyphicon glyphicon-remove"></i></a></td>
										</tr>
								<?php
									}
								}
								?>
								<tr class="info">
									<th style="padding: 2px;">Total</th>
									<th style="padding: 2px;"><?php echo number_format($valor_total_pago, 2, '.', ''); ?></th>
									<th style="padding: 2px;" colspan="6"></th>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- desde aqui asiento contable-->
	<div class="row" style="margin-bottom: 5px; margin-top: 15px;">
		<div class="col-md-12">
			<div class="panel-group" id="accordion_contable">
				<div class="panel panel-info">
					<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordion_contable" href="#collapse_contable"><span class="caret"></span> Asiento contable (Opcional)</a>
					<div id="collapse_contable" class="panel-collapse collapse">

						<div class="table-responsive">
							<input type="hidden" name="codigo_unico" id="codigo_unico">
							<input type="hidden" name="id_cuenta" id="id_cuenta">
							<input type="hidden" name="cod_cuenta" id="cod_cuenta">
							<div class="panel panel-info" style="margin-bottom: 5px; margin-top: -0px;">
								<table class="table table-bordered">
									<tr class="info">
										<th style="padding: 2px;">Cuenta</th>
										<th class="text-center" style="padding: 2px;">Debe</th>
										<th class="text-center" style="padding: 2px;">Haber</th>
										<th style="padding: 2px;">Detalle</th>
										<th class="text-center" style="padding: 2px;">Agregar</th>
									</tr>
									<td class='col-xs-4'>
										<input type="text" class="form-control input-sm focusNext" name="cuenta_diario" id="cuenta_diario" onkeyup='buscar_cuentas();' autocomplete="off" tabindex="4">
									</td>
									<td class='col-xs-2'><input type="text" class="form-control input-sm focusNext" name="debe_diario" id="debe_diario" tabindex="5"></td>
									<td class='col-xs-2'><input type="text" class="form-control input-sm focusNext" name="haber_cuenta" id="haber_cuenta" tabindex="6"></td>
									<td class='col-xs-4'><input type="text" class="form-control input-sm focusNext" name="det_cuenta" id="det_cuenta" tabindex="7"></td>
									<td class='col-xs-1 text-center'><button type="button" class="btn btn-info btn-sm focusNext" title="Agregar detalle de diario" tabindex="8" onclick="agregar_item_diario()"><span class="glyphicon glyphicon-plus"></span></button> </td>
								</table>
							</div>
							<div id="muestra_detalle_diario"></div><!-- Carga gif animado -->
							<div class="outer_divdet"></div><!-- Datos ajax Final -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

//para mostrar en el modal el detalle del ingreso
if ($action == 'detalle_ingreso') {
	$con = conenta_login();
	$codigo_unico = $_GET['codigo_unico'];
	$busca_encabezado_ingreso = mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE codigo_documento = '" . $codigo_unico . "' ");
	$encabezado_ingresos = mysqli_fetch_array($busca_encabezado_ingreso);
	$id_registro_contable = $encabezado_ingresos['codigo_contable'];
	$busca_detalle = mysqli_query($con, "SELECT * FROM detalle_ingresos_egresos WHERE codigo_documento = '" . $codigo_unico . "' ");
	$busca_pagos = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr WHERE codigo_documento = '" . $codigo_unico . "' ");
?>
	<div style="padding: 2px; margin-bottom: 5px; margin-top: -10px;" class="alert alert-info" role="alert">
		<b>No:</b> <?php echo $encabezado_ingresos['numero_ing_egr']; ?> <b>Fecha:</b> <?php echo date("d/m/Y", strtotime($encabezado_ingresos['fecha_ing_egr'])); ?> <b>Recibido de: </b><?php echo $encabezado_ingresos['nombre_ing_egr']; ?><b> Observaciones: </b><?php echo $encabezado_ingresos['detalle_adicional']; ?>
	</div>
	<div class="panel panel-info">
		<table class="table table-hover">
			<tr class="info">
				<th style="padding: 2px;">Tipo</th>
				<th style="padding: 2px;">Detalle</th>
				<th style="padding: 2px;">Valor</th>
			</tr>
			<?php
			while ($detalle = mysqli_fetch_array($busca_detalle)) {
				$tipo_ing_egr = $detalle['tipo_ing_egr'];
				
				if(!is_numeric($tipo_ing_egr)){
				$tipo_asiento = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo='" . $tipo_ing_egr . "' ");
				$row_asiento = mysqli_fetch_assoc($tipo_asiento);
				$transaccion = $row_asiento['tipo_asiento'];
				}else{
				$tipo_pago = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE id='" . $tipo_ing_egr . "' and tipo_opcion ='1' ");
				$row_tipo_pago = mysqli_fetch_assoc($tipo_pago);
				$transaccion = $row_tipo_pago['descripcion'];
				}

				$valor_ing_egr = $detalle['valor_ing_egr'];
				$detalle_ing_egr = $detalle['detalle_ing_egr'];
			?>
				<tr>
					<td style="padding: 2px;"><?php echo $transaccion; ?></td>
					<td style="padding: 2px;"><?php echo $detalle_ing_egr; ?></td>
					<td style="padding: 2px;"><?php echo number_format($valor_ing_egr, 2, '.', '') ?></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>

	<div class="panel panel-info">
		<table class="table table-hover">
			<tr class="info">
				<th style="padding: 2px;">Forma cobro</th>
				<th style="padding: 2px;">Cuenta receptora</th>
				<th style="padding: 2px;">Valor</th>
			</tr>
			<?php
			while ($detalle_pagos = mysqli_fetch_array($busca_pagos)) {
				$codigo_forma_pago = $detalle_pagos['codigo_forma_pago'];
				$id_cuenta = $detalle_pagos['id_cuenta'];
				
				if ($id_cuenta > 0) {
					$cuentas = mysqli_query($con, "SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.id_cuenta ='" . $id_cuenta . "'");
					$row_cuenta = mysqli_fetch_array($cuentas);
					$cuenta_bancaria = strtoupper($row_cuenta['cuenta_bancaria']);
					$forma_pago = $detalle_pagos['detalle_pago'];
					switch ($forma_pago) {
						case "D":
							$tipo = 'Depósito';
							break;
						case "T":
							$tipo = 'Transferencia';
							break;
					}
					$forma_pago = $tipo;
				} 
				
				if($codigo_forma_pago>0) {
					$opciones_pagos = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE id ='" . $codigo_forma_pago . "'");
					$row_opciones_pagos = mysqli_fetch_array($opciones_pagos);
					$forma_pago = strtoupper($row_opciones_pagos['descripcion']);
					$cuenta_bancaria = "";
				}
												
				$valor_forma_pago = $detalle_pagos['valor_forma_pago'];
			?>
				<tr>
					<td style="padding: 2px;"><?php echo $forma_pago; ?></td>
					<td style="padding: 2px;"><?php echo $cuenta_bancaria; ?></td>
					<td style="padding: 2px;"><?php echo number_format($valor_forma_pago, 2, '.', '') ?></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
<?php
	echo detalle_asiento_contable($con, $ruc_empresa, $id_registro_contable);
}

?>