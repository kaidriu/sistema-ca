<?php
/* Connect To Database*/
include("../conexiones/conectalogin.php");
include("../clases/empresas.php");
include("../clases/anular_registros.php");
include("../validadores/generador_codigo_unico.php");
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$fecha_registro = date("Y-m-d H:i:s");
$anular_asiento_contable = new anular_registros();
ini_set('date.timezone', 'America/Guayaquil');
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

if ($action == 'nuevo_pago_compra') {
	unset($_SESSION['arrayFormaPagoEgresoCompra']);
}

//para eliminar un iten de formas de pago del egreso temporal 
if($action == 'eliminar_pago_egreso'){
	$intid = $_GET['id_fp_tmp'];
	$arrData = $_SESSION['arrayFormaPagoEgresoCompra'];
	for ($i = 0; $i < count($arrData); $i++) {
		if ($arrData[$i]['id'] == $intid) {
			unset($arrData[$i]);
			echo "<script>
            $.notify('Eliminado','error');
            </script>";
		}
	}
	sort($arrData); //para reordenar el array
	$_SESSION['arrayFormaPagoEgresoCompra'] = $arrData;
	detalle_pago_compra();
}

//para agregar la forma de pago al pagos tmp
if ($action == 'forma_de_pago_egreso') {
	$forma_pago_egreso = $_POST['forma_pago_egreso'];
	$valor_pago_egreso = $_POST['valor_pago_egreso'];
	$numero_cheque_egreso = $_POST['numero_cheque_egreso'];
	$origen = $_POST['origen'];
	$tipo = $_POST['tipo'];
	$fecha_cobro_egreso = date('Y-m-d H:i:s', strtotime($_POST['fecha_cobro_egreso']));
	//para ver si el cheque ya esta agregado al tmp o egresos anteriores

	//para ver si has cheques en los pagos ya registrados con anterioridad
	if ($numero_cheque_egreso > 0) {
		$busca_cheque_registrado = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr WHERE tipo_documento='EGRESO' and id_cuenta='" . $forma_pago_egreso . "' and cheque='" . $numero_cheque_egreso . "'");
		$cheque_registrado = mysqli_num_rows($busca_cheque_registrado);
		if ($cheque_registrado > 0) {
			echo "
				<script src='../js/notify.js'></script>
				<script>
				$.notify('El número de cheque ya esta registrado.','error');
				</script>";
		} else {
			//para guardar en PAGOS temporal
			formas_pago_egreso($forma_pago_egreso, $valor_pago_egreso, $tipo, $origen, $numero_cheque_egreso, $fecha_cobro_egreso);
		}
	} else {

		formas_pago_egreso($forma_pago_egreso, $valor_pago_egreso, $tipo, $origen, $numero_cheque_egreso, $fecha_cobro_egreso);
	}
}

function formas_pago_egreso($forma_pago, $valor_pago, $tipo, $origen, $cheque, $fecha_cheque)
{
	$arrayFormaPago = array();
	$arrayDatos = array('id' => rand(5, 500), 'id_forma' => $forma_pago, 'tipo' => $tipo, 'valor' => $valor_pago, 'origen' => $origen, 'cheque' => $cheque, 'fecha_cheque' => $fecha_cheque);
	if (isset($_SESSION['arrayFormaPagoEgresoCompra'])) {
		$on = true;
		$arrayFormaPago = $_SESSION['arrayFormaPagoEgresoCompra'];
		for ($pr = 0; $pr < count($arrayFormaPago); $pr++) {
			if ($arrayFormaPago[$pr]['id_forma'] == $forma_pago && $origen == $arrayFormaPago[$pr]['origen']) {
				$arrayFormaPago[$pr]['valor'] += $valor_pago;
				$on = false;
			}
		}
		if ($on) {
			array_push($arrayFormaPago, $arrayDatos);
		}
		$_SESSION['arrayFormaPagoEgresoCompra'] = $arrayFormaPago;
	} else {
		array_push($arrayFormaPago, $arrayDatos);
		$_SESSION['arrayFormaPagoEgresoCompra'] = $arrayFormaPago;
	}
	echo "<script src='../js/notify.js'></script>
				<script>
					$.notify('Agregado','success');
					</script>";
	detalle_pago_compra();
}

function detalle_pago_compra()
{
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];

?>
	<div class="col-md-12" style="margin-bottom: -25px; margin-top: -10px;">
			<div class="panel panel-info">
						<div class="table-responsive">
							<table class="table table-bordered">
								<tr class="info">
									<td style="padding: 2px;">Forma</td>
									<td style="padding: 2px;" class="text-center">Tipo</td>
									<td style="padding: 2px;" class="text-center">Valor</td>
									<td style="padding: 2px;" class="text-center">Cheque</td>
									<td style="padding: 2px;" class="text-center">Fecha</td>
									<td style="padding: 2px;" class="text-center">Eliminar</td>
								</tr>
								<?php
								$valor_total_pago = 0;
								if (isset($_SESSION['arrayFormaPagoEgresoCompra'])) {
									foreach ($_SESSION['arrayFormaPagoEgresoCompra'] as $detalle) {
										$id = $detalle['id'];
										$id_forma = $detalle['id_forma'];
										$tipo = $detalle['tipo'];
										switch ($tipo) {
											case "0":
												$tipo = 'N/A';
												break;
											case "C":
												$tipo = 'Cheque';
												break;
											case "D":
												$tipo = 'Débito';
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
											<td style="padding: 2px;" class='col-xs-2'><?php echo $forma_pago; ?></td>
											<td style="padding: 2px;" class='col-xs-2'><?php echo $tipo; ?></td>
											<td style="padding: 2px;" class='col-xs-1 text-right'><?php echo $valor_pago; ?></td>
											<td style="padding: 2px;" class='col-xs-1 text-center'><?php echo $detalle['cheque'] > 0 ? $detalle['cheque'] : ""; ?></td>
											<td style="padding: 2px;" class='col-xs-2 text-center'><?php echo $detalle['fecha_cheque'] != 0 ? date('d-m-Y', strtotime($detalle['fecha_cheque'])) : ""; ?></td>
											<td style="padding: 2px;" class='col-xs-1 text-center'>
												<a href="#" class="btn btn-danger btn-xs" onclick="eliminar_fila_pago_egreso('<?php echo $id; ?>')" title="Eliminar item"><i class="glyphicon glyphicon-trash"></i></a>
											</td>
										</tr>
								<?php
									}
								}
								?>
								<input type="hidden" id="suma_pagos_egreso" value="<?php echo number_format($valor_total_pago, 2, '.', ''); ?>">
							</table>
						</div>
					</div>
			</div>
	<?php

}


//PARA ELIMINAR COMPRAS
if ($action == 'eliminar_compra') {
	$codigo_documento = $_POST['codigo_documento'];
	//eliminar la compra y los datos de la compra
	$datos_encabezado = mysqli_query($con, "SELECT * FROM encabezado_compra WHERE codigo_documento = '" . $codigo_documento . "' ");
	$row_encabezado = mysqli_fetch_array($datos_encabezado);
	$id_registro_contable = $row_encabezado['id_registro_contable'];
	$anio_documento = date("Y", strtotime($row_encabezado['fecha_compra']));

	//para comprobar si esta en un egreso
	$datos_egreso = mysqli_query($con, "SELECT * FROM detalle_ingresos_egresos WHERE codigo_documento_cv = '" . $codigo_documento . "' and estado ='ok'");
	$row_egreso = mysqli_num_rows($datos_egreso);

	if ($row_egreso > 0) {
		echo "<script>
			$.notify('Este documento tiene pagos, anule el egreso primero','error');
			</script>";
		exit;
	}

	$resultado_anular_documento = $anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);

	if ($resultado_anular_documento == "NO") {
		echo "<script>
			$.notify('Primero se debe anular el asiento contable','error');
			</script>";
		exit;
	}


	$delete_encabezado_compra = mysqli_query($con, "DELETE FROM encabezado_compra WHERE ruc_empresa='" . $ruc_empresa . "' and codigo_documento = '" . $codigo_documento . "'");
	$delete_cuerpo_compra = mysqli_query($con, "DELETE FROM cuerpo_compra WHERE ruc_empresa='" . $ruc_empresa . "' and codigo_documento = '" . $codigo_documento . "'");
	$delete_detalle_adicional__compra = mysqli_query($con, "DELETE FROM detalle_adicional_compra WHERE ruc_empresa='" . $ruc_empresa . "' and codigo_documento = '" . $codigo_documento . "'");
	$delete_formas_pago_compras = mysqli_query($con, "DELETE FROM formas_pago_compras WHERE ruc_empresa='" . $ruc_empresa . "' and codigo_documento = '" . $codigo_documento . "'");

	if ($delete_encabezado_compra && $delete_cuerpo_compra && $delete_detalle_adicional__compra && $delete_formas_pago_compras) {
		echo "<script>
				$.notify('Registro eliminado exitosamente','success')
				</script>";
	} else {
		echo "<script>
				$.notify('Lo siento algo ha salido mal intenta nuevamente','error')
				</script>";
	}
}

//PARA actualizar detalle tribuario
if ($action == 'actualiza_detalle_tributario') {
	if (isset($_POST['deducible_en']) && isset($_POST['sustento_tributario'])) {
		$deducible_en = $_POST['deducible_en'];
		$id_sustento = $_POST['sustento_tributario'];
		$id_encabezado_compra = $_POST['id_encabezado_compra'];
		$cod_doc_mod = isset($_POST['documento_modificado']) ? $_POST['documento_modificado'] : "";
		//actualiza la compra y los datos de la compra
		$update_encabezado_compra = mysqli_query($con, "UPDATE encabezado_compra SET id_sustento='" . $id_sustento . "', deducible_en='" . $deducible_en . "', cod_doc_mod='" . $cod_doc_mod . "' WHERE ruc_empresa='" . $ruc_empresa . "' and id_encabezado_compra = '" . $id_encabezado_compra . "'");

		if ($update_encabezado_compra) {
			echo "<script>
				$.notify('Registro actualizado.','success');
				</script>";
		} else {
			echo "<script>
				$.notify('Lo siento algo ha salido mal intenta nuevamente','error');
				</script>";
		}
	}
}

//PARA BUSCAR LAS COMPRAS
if ($action == 'compras') {
	$info_empresa = new empresas();
	$tipo_empresa = $info_empresa->datos_empresas($ruc_empresa)['tipo'];
	$q = mysqli_real_escape_string($con, (strip_tags($_REQUEST['q'], ENT_QUOTES)));
	$ordenado = mysqli_real_escape_string($con, (strip_tags($_GET['ordenado'], ENT_QUOTES)));
	$por = mysqli_real_escape_string($con, (strip_tags($_GET['por'], ENT_QUOTES)));
	$aColumns = array('fecha_compra', 'numero_documento', 'razon_social', 'nombre_comercial', 'factura_aplica_nc_nd', 'comprobante'); //Columnas de busqueda
	$sTable = "encabezado_compra as ec LEFT JOIN proveedores as pr ON pr.id_proveedor=ec.id_proveedor LEFT JOIN sustento_tributario as sus_tri ON sus_tri.id_sustento = ec.id_sustento LEFT JOIN comprobantes_autorizados as com_aut ON com_aut.id_comprobante=ec.id_comprobante ";
	$sWhere = "WHERE ec.ruc_empresa = '" . $ruc_empresa . "' ";
	if ($_GET['q'] != "") {
		$sWhere = "WHERE (ec.ruc_empresa = '" . $ruc_empresa . "' AND ";

		for ($i = 0; $i < count($aColumns); $i++) {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $q . "%' AND ec.ruc_empresa = '" . $ruc_empresa . "' OR ";
		}

		$sWhere = substr_replace($sWhere, "AND ec.ruc_empresa = '" . $ruc_empresa . "' ", -3);
		$sWhere .= ')';
	}
	$sWhere .= " order by $ordenado $por";
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
	$reload = '../compras.php';
	//main query to fetch the data
	$sql = "SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
	$query = mysqli_query($con, $sql);
	//loop through fetched data
	if ($numrows > 0) {
	?>
		<div class="panel panel-info">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_compra");'>Fecha</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("razon_social");'>Proveedor</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("ec.id_comprobante");'>Comprobante</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_documento");'>Número</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("factura_aplica_nc_nd");'>F/mod</button></th>
						<?php
						if (intval($tipo_empresa != 1)) {
						?>
							<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info">Retención</button></th>
						<?php
						}
						?>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("total_compra");'>Total</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("total_compra");'>Saldo</button></th>
						<th class='text-right'>Opciones</th>
						<input type="hidden" value="<?php echo $page; ?>" id="pagina">
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$id_encabezado_compra = $row['id_encabezado_compra'];
						$fecha_compra = $row['fecha_compra'];
						$id_proveedor = $row['id_proveedor'];
						$proveedor = $row['razon_social'];
						$numero_documento = $row['numero_documento'];
						$codigo_documento = $row['codigo_documento'];
						$documento_modificado = $row['factura_aplica_nc_nd'];
						$codigo_deducible = $row['deducible_en'];
						$cod_doc_mod = $row['cod_doc_mod'];
						$aplica = $row['factura_aplica_nc_nd'];
						$total_compra = empty($row['total_compra']) ? 0 : number_format($row['total_compra'], 2, '.', '');
						$codigo_sustento = $row['codigo_sustento'];
						$nombre_comprobante = $row['comprobante'];
						$codigo_comprobante = $row['codigo_comprobante'];
						$tipoIdentificacionComprador = $row['deducible_en'];

						if ($tipoIdentificacionComprador == "05") {
							$tipoIdentificacionComprador = ' <span class="badge" title="Documento emitido con cédula, deducible para gasto personal">GP</span>';
						} else {
							$tipoIdentificacionComprador = "";
						}

						$detalle_pagos = mysqli_query($con, "SELECT sum(valor_ing_egr) as valor_ing_egr, numero_ing_egr as numero_ing_egr, codigo_documento as codigo_documento_egreso FROM detalle_ingresos_egresos WHERE ruc_empresa= '" . $ruc_empresa . "' and codigo_documento_cv = '" . $codigo_documento . "' and tipo_documento='EGRESO' and estado='OK' group by codigo_documento_cv");
						$row_pagos = mysqli_fetch_array($detalle_pagos);
						$total_abonos = empty($row_pagos['valor_ing_egr']) ? 0 : number_format($row_pagos['valor_ing_egr'], 2, '.', '');

						$detalle_retenciones = mysqli_query($con, "SELECT sum(total_retencion) as total_retencion FROM encabezado_retencion WHERE numero_comprobante = '" . $numero_documento . "' and mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' and id_proveedor='" . $id_proveedor . "' group by numero_comprobante");
						$row_retenciones = mysqli_fetch_array($detalle_retenciones);
						$total_retencion = empty($row_retenciones['total_retencion']) ? 0 : number_format($row_retenciones['total_retencion'], 2, '.', '');

						

					?>
						<input type="hidden" value="<?php echo $id_encabezado_compra; ?>" id="id_documento<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $codigo_documento; ?>" id="codigo_documento<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $proveedor; ?>" id="proveedor_documento<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $numero_documento; ?>" id="numero_documento<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $id_comprobante; ?>" id="id_comprobante<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $codigo_sustento; ?>" id="codigo_sustento<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $codigo_deducible; ?>" id="codigo_deducible<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $cod_doc_mod; ?>" id="cod_doc_mod<?php echo $id_encabezado_compra; ?>">
						<input type="hidden" value="<?php echo $codigo_comprobante; ?>" id="codigo_comprobante<?php echo $id_encabezado_compra; ?>">
						<tr>
							<td><?php echo date("d/m/Y", strtotime($fecha_compra)); ?></td>
							<td class='col-md-4'><?php echo strtoupper($proveedor); ?></td>
							<td><?php echo $nombre_comprobante . $tipoIdentificacionComprador; ?></td>
							<td><?php echo $numero_documento; ?></td>
							<td><?php echo $documento_modificado; ?></td>
							<?php
							if (intval($tipo_empresa != 1)) {
								//para consultar si tiene retencion autorizada a pesar que haya anulada
								$busca_retencion_autorizada = mysqli_query($con, "SELECT * FROM encabezado_retencion WHERE ruc_empresa= '" . $ruc_empresa . "' and numero_comprobante='" . $numero_documento . "' and id_proveedor='" . $id_proveedor . "' and estado_sri != 'ANULADA'");
								$count_ret_autorizada = mysqli_num_rows($busca_retencion_autorizada);

								$row_retenciones = mysqli_fetch_array($busca_retencion_autorizada);
								$estado_ret = empty($row_retenciones['estado_sri']) ? "" : $row_retenciones['estado_sri'];

								if ($codigo_comprobante == "04") {
							?>
									<td class="text-center">
									</td>
								<?php
								} else {
									if ($count_ret_autorizada > 0) {
										$retenciones = "SI";
									} else {
										$retenciones = "NO";
									}

								?>
									<td class="text-center">
										<?php

										if ($retenciones == "NO") {
										?>
											<a href="#" class="btn btn-danger btn-xs" title='Sin retención.' onclick="detalle_retencion_compra('<?php echo $id_encabezado_compra; ?>')" data-toggle="modal" data-target="#detalleDocumento"><?php echo $retenciones; ?></a>
										<?php
										}
										if ($retenciones == "SI" && $estado_ret == "AUTORIZADO") {
										?>
											<a href="#" class='btn btn-success btn-xs' title='Retención autorizada.' onclick="detalle_retencion_compra('<?php echo $id_encabezado_compra; ?>')" data-toggle="modal" data-target="#detalleDocumento"><?php echo $retenciones; ?></a>
										<?php
										}
										if ($retenciones == "SI" && $estado_ret == "PENDIENTE") {
										?>
											<a href="#" class='btn btn-warning btn-xs' title='Retención pendiente de autorizar.' onclick="detalle_retencion_compra('<?php echo $id_encabezado_compra; ?>')" data-toggle="modal" data-target="#detalleDocumento"><?php echo $retenciones; ?></a>
										<?php
										}
										if ($retenciones == "SI" && $estado_ret == "ANULADA") {
										?>
											<a href="#" class='btn btn-warning btn-xs' title='Retención anulada.' onclick="detalle_retencion_compra('<?php echo $id_encabezado_compra; ?>')" data-toggle="modal" data-target="#detalleDocumento">NO</a>
										<?php
										}
										?>
									</td>
							<?php
								}
							}
							?>

							<td><?php echo $total_compra; ?></td>
							<td>
								<?php
								$saldo = number_format(abs($total_compra - $total_abonos - $total_retencion), 2, '.', '');
								if ($codigo_comprobante == '04') {
									echo $saldo="0.00";
								} else {
									echo $saldo;
								}
								?>
							</td>
							<td class='col-md-2'><span class="pull-right">
									<a href="../pdf/pdf_adquisicion.php?action=adquisicion&codigo_documento=<?php echo $codigo_documento; ?>" class='btn btn-default btn-xs' title='Pdf' target="_blank">Pdf</a>
									<a href="#" class='btn btn-info btn-xs' onclick="detalle_factura_compra('<?php echo $codigo_documento; ?>')" title="Detalle documento" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list-alt"></i></a>
									<a href="#" class="btn btn-info btn-xs" onclick="carga_modal_registrar_pago('<?php echo $id_encabezado_compra; ?>','<?php echo $saldo; ?>','<?php echo strtoupper($proveedor); ?>','<?php echo $numero_documento; ?>');" title="registrar pago" data-toggle="modal" data-target="#cobroFacturaCompra"><i class="glyphicon glyphicon-usd"></i> </a>
									<a href="#" class='btn btn-danger btn-xs' title='Eliminar compra' onclick="eliminar_registro('<?php echo $id_encabezado_compra; ?>')"><i class="glyphicon glyphicon-trash"></i> </a>
								</span></td>

						</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="10"><span class="pull-right">
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


if ($action == 'guardar_pago_compra') {
	$id_compra = $_POST['id_compra'];
	$fecha_egreso=date('Y-m-d H:i:s', strtotime($_POST['fecha_egreso']));
	$saldo_compra = $_POST['saldo'];
	$fecha_registro=date("Y-m-d H:i:s");
	$total_formas_pagos = 0;
	$codigo_unico=codigo_unico(20);
	$sql_detalle_compra = mysqli_query($con,"SELECT * FROM encabezado_compra as enc INNER JOIN proveedores as pro ON pro.id_proveedor=enc.id_proveedor INNER JOIN comprobantes_autorizados as doc ON doc.id_comprobante=enc.id_comprobante WHERE enc.id_encabezado_compra = '".$id_compra."'");
	$row_detalle_compra=	mysqli_fetch_array($sql_detalle_compra);
	$nombre_proveedor=$row_detalle_compra['razon_social'];
	$comprobante=$row_detalle_compra['comprobante'];
	$codigo_compra=$row_detalle_compra['codigo_documento'];
	$fecha_compra =	date('Y-m-d H:i:s', strtotime($row_detalle_compra['fecha_compra']));
	$id_proveedor_egreso=$row_detalle_compra['id_proveedor'];
	$detalle_egreso=$comprobante." ". $row_detalle_compra['numero_documento']." ".$nombre_proveedor;

	$arrayValorPago = $_SESSION['arrayFormaPagoEgresoCompra'];
		foreach($arrayValorPago as $valor){
			$total_formas_pagos+=$valor['valor'];
		}

	if($saldo_compra==0){
		echo "<script>
			$.notify('El saldo del documento es cero','error');
			</script>";
	}else if (!isset($_SESSION['arrayFormaPagoEgresoCompra'])) {
		echo "<script>
			$.notify('Agregar formas de pago y valores','error');
			</script>";
	}else if($total_formas_pagos > $saldo_compra ){
		echo "<script>
		$.notify('El total agregado es mayor al saldo pendiente','error');
		</script>";
	}else if(!date($fecha_egreso)){
		echo "<script>
		$.notify('Ingrese fecha correcta','error');
		</script>";
	}else if($fecha_egreso < $fecha_compra){
		echo "<script>
		$.notify('La fecha del egreso no puede ser menor que la fecha del documento','error');
		</script>";
	}else{

		$busca_siguiente_egreso = mysqli_query($con,"SELECT max(numero_ing_egr) as numero FROM ingresos_egresos WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr = 'EGRESO'");
		$row_siguiente_egreso = mysqli_fetch_array($busca_siguiente_egreso);
		$numero_egreso=$row_siguiente_egreso['numero']+1;

		$query_encabezado_egreso = mysqli_query($con, "INSERT INTO ingresos_egresos VALUES (null, '".$ruc_empresa."','".$fecha_egreso."','".$nombre_proveedor."','".$numero_egreso."','".$total_formas_pagos."','EGRESO','".$id_usuario."','".$fecha_registro."','0','".$codigo_unico."','','OK','".$id_proveedor_egreso."')");
		
		foreach ($_SESSION['arrayFormaPagoEgresoCompra'] as $detalle) {
				$origen = $detalle['origen'];
				if($origen=='1'){
					$codigo_forma_pago = $detalle['id_forma'];
					$id_cuenta='0';
				}else{
					$id_cuenta = $detalle['id_forma'];
					$codigo_forma_pago='0';
				}
				$valor_pago = number_format($detalle['valor'], 2, '.', '');
				$tipo = $detalle['tipo'];
				$cheque = $detalle['cheque'];
				$fecha_cheque = $detalle['fecha_cheque'];
				$estado_pago=$cheque>0?"ENTREGAR":"PAGADO";
				$detalle_formas_pago = mysqli_query($con, "INSERT INTO formas_pagos_ing_egr VALUES (null, '" . $ruc_empresa . "', 'EGRESO', '" . $numero_egreso . "', '" . $valor_pago . "', '" . $codigo_forma_pago . "', '" . $id_cuenta . "', '".$tipo."', '" . $codigo_unico . "', '".$fecha_egreso."', '', '".$fecha_cheque."','".$estado_pago."','".$cheque."','OK')");
			}

		$detalle_ingreso=mysqli_query($con, "INSERT INTO detalle_ingresos_egresos VALUES (NULL, '".$ruc_empresa."', '".$nombre_proveedor."', '".$total_formas_pagos."', '".$detalle_egreso."', '".$numero_egreso."', 'CCXPP', 'EGRESO', '".$codigo_compra."','OK', '".$codigo_unico."')");

		unset($_SESSION['arrayFormaPagoEgresoCompra']);
		echo "<script>
		$.notify('Egreso registrado','success');
		setTimeout(function (){location.href ='../modulos/compras.php'}, 1000);
		</script>";
	}

}
?>