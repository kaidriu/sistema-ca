<?php
require_once("../conexiones/conectalogin.php");
require_once("../ajax/pagination.php");
require_once("../helpers/helpers.php");
include("../clases/guardar_factura_electronica.php");
include("../validadores/generador_codigo_unico.php");
include("../clases/secuencial_electronico.php");

$secuencial_electronico = new secuencial_electronico();
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
ini_set('date.timezone', 'America/Guayaquil');
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';


if ($action == 'agregar_forma_pago_ingreso_factura') {
	$forma_pago = $_GET["forma_pago"];
	$valor_pago = $_GET["valor_pago"];
	$tipo = $_GET["tipo"];
	$origen = $_GET["origen"];

	$arrayFormaPago = array();
	$arrayDatos = array('id' => rand(5, 500), 'id_forma' => $forma_pago, 'tipo' => $tipo, 'valor' => $valor_pago, 'origen' => $origen);
	if (isset($_SESSION['arrayFormaPagoIngresoFactura'])) {
		$on = true;
		$arrayFormaPago = $_SESSION['arrayFormaPagoIngresoFactura'];
		for ($pr = 0; $pr < count($arrayFormaPago); $pr++) {
			if ($arrayFormaPago[$pr]['id_forma'] == $forma_pago && $origen == $arrayFormaPago[$pr]['origen']) {
				$arrayFormaPago[$pr]['valor'] += $valor_pago;
				$on = false;
			}
		}
		if ($on) {
			array_push($arrayFormaPago, $arrayDatos);
		}
		$_SESSION['arrayFormaPagoIngresoFactura'] = $arrayFormaPago;
	} else {
		array_push($arrayFormaPago, $arrayDatos);
		$_SESSION['arrayFormaPagoIngresoFactura'] = $arrayFormaPago;
	}
	detalle_pago_factura();
}

function detalle_pago_factura()
{
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];

?>
	<div class="col-md-12" style="margin-bottom: -25px; margin-top: -10px;">
		<div class="panel panel-info">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<td style="padding: 2px;">Forma cobro</td>
						<td style="padding: 2px;">Tipo</td>
						<td style="padding: 2px;">Valor</td>
						<td style="padding: 2px;" class='text-right'>Eliminar</td>
					</tr>
					<?php
					$valor_total_pago = 0;
					if (isset($_SESSION['arrayFormaPagoIngresoFactura'])) {
						foreach ($_SESSION['arrayFormaPagoIngresoFactura'] as $detalle) {
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
								<td style="padding: 2px;"><?php echo $tipo; ?></td>
								<td style="padding: 2px;"><?php echo number_format($valor_pago, 2, '.', ''); ?></td>
								<td style="padding: 2px;" class='text-right'><a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_item_pago('<?php echo $id; ?>')"><i class="glyphicon glyphicon-remove"></i></a></td>
							</tr>
					<?php
						}
					}
					?>
					<input type="hidden" value="<?php echo number_format($valor_total_pago, 2, '.', ''); ?>" id="valor_total_pago">
					<tr class="info">
						<td style="padding: 2px;" colspan="12" class="text-center">Total pagos agregados  <?php echo number_format($valor_total_pago, 2, '.', ''); ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<?php
}

//eliminar detalle pago del ingreso
if ($action == 'eliminar_item_pago') {
	$intid = $_GET['id_registro'];
	$arrData = $_SESSION['arrayFormaPagoIngresoFactura'];
	for ($i = 0; $i < count($arrData); $i++) {
		if ($arrData[$i]['id'] == $intid) {
			unset($arrData[$i]);
			echo "<script>
            $.notify('Eliminado','error');
            </script>";
		}
	}
	sort($arrData); //para reordenar el array
	$_SESSION['arrayFormaPagoIngresoFactura'] = $arrData;
	detalle_pago_factura();
}

if ($action == 'nueva_factura') {
	nueva_factura($con, $id_usuario);
}

if ($action == 'nuevo_pago_factura') {
	unset($_SESSION['arrayFormaPagoIngresoFactura']);
}

if ($action == 'guardar_pago_factura') {
	$id_factura = $_POST['id_factura'];
	$fecha_ingreso=date('Y-m-d H:i:s', strtotime($_POST['fecha_ingreso']));
	$fecha_registro=date("Y-m-d H:i:s");
	$saldo_factura=saldo_factura($con, $ruc_empresa, $id_factura);
	$total_formas_pagos = 0;
	$codigo_unico=codigo_unico(20);

	$sql_detalle_factura = mysqli_query($con,"SELECT * FROM encabezado_factura as enc INNER JOIN clientes as cli ON cli.id=enc.id_cliente WHERE enc.id_encabezado_factura = '".$id_factura."'");
	$row_detalle_factura=	mysqli_fetch_array($sql_detalle_factura);
	$nombre_cliente=$row_detalle_factura['nombre'];
	$fecha_factura=	date('Y-m-d H:i:s', strtotime($row_detalle_factura['fecha_factura']));
	$id_cliente_ingreso=$row_detalle_factura['id_cliente'];
	$detalle_ingreso=$nombre_cliente." ".$row_detalle_factura['serie_factura'] . "-" . str_pad($row_detalle_factura['secuencial_factura'], 9, "000000000", STR_PAD_LEFT);

	$arrayValorPago = $_SESSION['arrayFormaPagoIngresoFactura'];
		foreach($arrayValorPago as $valor){
			$total_formas_pagos+=$valor['valor'];
		}

	if($saldo_factura==0){
		echo "<script>
			$.notify('El saldo de la factura es cero','error');
			</script>";
	}else if (!isset($_SESSION['arrayFormaPagoIngresoFactura'])) {
		echo "<script>
			$.notify('Agregar formas de pago y valores','error');
			</script>";
	}else if($total_formas_pagos > $saldo_factura ){
		echo "<script>
		$.notify('El total agregado es mayor al saldo pendiente','error');
		</script>";
	}else if(!date($fecha_ingreso)){
		echo "<script>
		$.notify('Ingrese fecha correcta','error');
		</script>";
	}else if($fecha_ingreso < $fecha_factura){
		echo "<script>
		$.notify('La fecha del ingreso no puede ser menor que la fecha de la factura','error');
		</script>";
	}else{

		$busca_siguiente_ingreso = mysqli_query($con,"SELECT max(numero_ing_egr) as numero FROM ingresos_egresos WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr = 'INGRESO'");
		$row_siguiente_ingreso = mysqli_fetch_array($busca_siguiente_ingreso);
		$numero_ingreso=$row_siguiente_ingreso['numero']+1;

		$query_encabezado_ingreso = mysqli_query($con, "INSERT INTO ingresos_egresos VALUES (null, '".$ruc_empresa."','".$fecha_ingreso."','".$nombre_cliente."','".$numero_ingreso."','".$total_formas_pagos."','INGRESO','".$id_usuario."','".$fecha_registro."','0','".$codigo_unico."','','OK','".$id_cliente_ingreso."')");
		
		foreach ($_SESSION['arrayFormaPagoIngresoFactura'] as $detalle) {
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
				
				$detalle_formas_pago = mysqli_query($con, "INSERT INTO formas_pagos_ing_egr VALUES (null, '" . $ruc_empresa . "', 'INGRESO', '" . $numero_ingreso . "', '" . $valor_pago . "', '" . $codigo_forma_pago . "', '" . $id_cuenta . "', '".$tipo."', '" . $codigo_unico . "', '".$fecha_ingreso."', '".$fecha_ingreso."', '".$fecha_ingreso."','PAGADO','0','OK')");
			}
			//beneficiario_cliente, valor_ing_egr, detalle_ing_egr, numero_ing_egr, tipo_ing_egr, tipo_documento, codigo_documento_cv, estado, codigo_documento)
		$detalle_ingreso=mysqli_query($con, "INSERT INTO detalle_ingresos_egresos VALUES (NULL, '".$ruc_empresa."', '".$nombre_cliente."', '".$total_formas_pagos."', '".$detalle_ingreso."', '".$numero_ingreso."', 'CCXCC', 'INGRESO', '".$id_factura."','OK', '".$codigo_unico."')");

		unset($_SESSION['arrayFormaPagoIngresoFactura']);
		echo "<script>
		$.notify('Ingreso registrado','success');
		setTimeout(function (){location.href ='../modulos/facturas.php'}, 1000);
		</script>";
	}

}

function nueva_factura($con, $id_usuario)
{
	unset($_SESSION['arrayCliente']);
	unset($_SESSION['arrayInfoAdicional']);
	unset($_SESSION['arrayFormaPago']);
	unset($_SESSION['arrayServicio']);
	unset($_SESSION['arrayTasa']);
	$delete_factura_tmp = mysqli_query($con, "DELETE FROM factura_tmp WHERE id_usuario = '" . $id_usuario . "'");
}

if ($action == 'editar_factura') {
	$id_factura = $_POST['id_factura'];
	$serie_factura = $_POST['serie_factura'];
	$secuencial_factura = $_POST['secuencial_factura'];
	nueva_factura($con, $id_usuario);
	informacion_factura($con, $id_usuario, $id_factura, $serie_factura, $secuencial_factura, $ruc_empresa);
}

function informacion_factura($con, $id_usuario, $id_factura, $serie_factura, $secuencial_factura, $ruc_empresa)
{
	//traer informacion y llenar en los arreglos de informacion adicional y clientes
	$info_adicional = mysqli_query($con, "SELECT * FROM detalle_adicional_factura WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura='" . $serie_factura . "' and secuencial_factura='" . $secuencial_factura . "'");
	$arrayInfoAdicional = array();
	$arrayCliente = array();
	while ($row_info_adicional = mysqli_fetch_array($info_adicional)) {
		if ($row_info_adicional['adicional_concepto'] == 'Email') {
			$arrayMail = array('concepto' => 'Email', 'detalle' => $row_info_adicional['adicional_descripcion']);
			array_push($arrayCliente, $arrayMail);
		} else if ($row_info_adicional['adicional_concepto'] == 'Dirección') {
			$arrayDireccion = array('concepto' => 'Dirección', 'detalle' => $row_info_adicional['adicional_descripcion']);
			array_push($arrayCliente, $arrayDireccion);
		} else if ($row_info_adicional['adicional_concepto'] == 'Teléfono') {
			$arrayTelefono = array('concepto' => 'Teléfono', 'detalle' => $row_info_adicional['adicional_descripcion']);
			array_push($arrayCliente, $arrayTelefono);
		} else {
			$arrayDatos = array('id' => rand(5, 50), 'concepto' => $row_info_adicional['adicional_concepto'], 'detalle' => $row_info_adicional['adicional_descripcion']);
			array_push($arrayInfoAdicional, $arrayDatos);
		}
	}
	$_SESSION['arrayInfoAdicional'] = $arrayInfoAdicional;
	$_SESSION['arrayCliente'] = $arrayCliente;

	//traer informacion para llenar el cuerpo de la factura
	$query_pasa_datos_factura = mysqli_query($con, "INSERT INTO factura_tmp(id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice, tarifa_botellas, id_usuario, id_bodega, id_medida, lote, vencimiento) 
		SELECT null,id_producto,cantidad_factura,valor_unitario_factura, descuento,tipo_produccion,tarifa_iva,tarifa_ice,tarifa_bp,'" . $id_usuario . "',id_bodega,id_medida_salida,lote,vencimiento  FROM cuerpo_factura WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura = '" . $secuencial_factura . "'");

	//para mostrar tasa y servicio
	$sql_encabezado_factura =  mysqli_query($con, "SELECT * FROM encabezado_factura WHERE id_encabezado_factura = '" . $id_factura . "' ");
	$row_datos_encabezados = mysqli_fetch_array($sql_encabezado_factura);
	$propina = $row_datos_encabezados['propina'];
	$tasa = $row_datos_encabezados['tasa_turistica'];
	if ($propina > 0) {
		$_SESSION['arrayServicio']['0']['servicio'] = $propina;
	}
	if ($tasa > 0) {
		$_SESSION['arrayTasa']['0']['tasa'] = $tasa;
	}

	//para mostrar subtotales
	$sql_forma_pago =  mysqli_query($con, "SELECT * FROM formas_pago_ventas WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura = '" . $secuencial_factura . "' ");
	//$count_items = mysqli_num_rows($sql_forma_pago);
	//if ($count_items > 0) {
	$arrayFormaPago = array();
	while ($row_forma_pago = mysqli_fetch_array($sql_forma_pago)) {
		$codigo_forma_pago = $row_forma_pago['id_forma_pago'];
		$valor_pago = $row_forma_pago['valor_pago'];
		$arrayDatos = array('id' => rand(5, 500), 'codigo' => $codigo_forma_pago, 'valor' => $valor_pago);
		array_push($arrayFormaPago, $arrayDatos);
	}
	$_SESSION['arrayFormaPago'] = $arrayFormaPago;
	//}

}

//para muestrr la informacion del cliente y adicionales
if ($action == 'muestra_cliente_adicionales_editar_factura') {
	informacion_adicional();
	detalle_informacion_adicional();
	informacion_cliente();
}

if ($action == 'muestra_cuerpo_editar_factura') {
	$serie_factura = $_POST['serie_factura'];
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
}

if ($action == 'muestra_formas_pago_editar_factura') {
	$total_factura = $_POST['total_factura'];
	formas_pago($total_factura);
	//traer formas de pago
	detalle_formas_pago($con, $total_factura);
}

if ($action == 'muestra_subtotales_editar_factura') {
	$serie_factura = $_POST['serie_factura'];
	subtotales_factura($con, $ruc_empresa, $serie_factura, $id_usuario);
}



if ($action == 'tipo_medida_producto') {
	$id_medida = $_POST["id_medida"];
	//para saber el tipo de unidad de medida
	$busca_tipo_medida = "SELECT * FROM unidad_medida WHERE id_medida='" . $id_medida . "' ";
	$resultado_tipo_medida = $con->query($busca_tipo_medida);
	$row_tipo_medida = mysqli_fetch_array($resultado_tipo_medida);
	$id_tipo_medida = $row_tipo_medida['id_tipo_medida'];
	$busca_unidad_medida = "SELECT * FROM unidad_medida WHERE id_tipo_medida= '" . $id_tipo_medida . "'";
	$resultado_unidad_medida = $con->query($busca_unidad_medida);
	while ($row_unidad_medida = mysqli_fetch_array($resultado_unidad_medida)) {
		if ($row_unidad_medida['id_medida'] == $id_medida) {
	?>
			<option value="<?php echo $id_medida; ?>" selected><?php echo $row_unidad_medida['nombre_medida']; ?></option>
		<?php
		} else {
		?>
			<option value="<?php echo $row_unidad_medida['id_medida']; ?>"><?php echo $row_unidad_medida['nombre_medida']; ?></option>
	<?php
		}
	}
}


if ($action == 'informacion_cliente') {
	$id_cliente = intval($_POST['id_cliente']);
	$busca_cliente_detalle = mysqli_query($con, "SELECT * FROM clientes WHERE id = '" . $id_cliente . "' ");
	$datos_detalle = mysqli_fetch_array($busca_cliente_detalle);
	$email = $datos_detalle['email'];
	$direccion = $datos_detalle['direccion'];
	$telefono = $datos_detalle['telefono'];

	unset($_SESSION['arrayCliente']);
	$arrayCliente = array();

	$arrayMail = array('concepto' => 'Email', 'detalle' => $email);
	$arrayDireccion = array('concepto' => 'Dirección', 'detalle' => $direccion);
	if (!empty($telefono)) {
		$arrayTelefono = array('concepto' => 'Teléfono', 'detalle' => $telefono);
	}
	if (isset($_SESSION['arrayCliente'])) {
		$arrayCliente = $_SESSION['arrayCliente'];
		array_push($arrayCliente, $arrayMail);
		array_push($arrayCliente, $arrayDireccion);
		array_push($arrayCliente, $arrayTelefono);
		$_SESSION['arrayCliente'] = $arrayCliente;
	} else {
		array_push($arrayCliente, $arrayMail);
		array_push($arrayCliente, $arrayDireccion);
		array_push($arrayCliente, $arrayTelefono);
		$_SESSION['arrayCliente'] = $arrayCliente;
	}

	informacion_adicional();
	detalle_informacion_adicional();
	informacion_cliente();
}

if ($action == 'agrega_info_adicional') {
	$concepto = strClean($_POST['adicional_concepto']);
	$detalle = strClean($_POST['adicional_detalle']);

	if (!empty($concepto) && !empty($detalle)) {
		if ((strlen($concepto) + strlen($detalle) <= 300)) {
			$arrayInfoAdicional = array();
			$arrayDatos = array('id' => rand(5, 50), 'concepto' => $concepto, 'detalle' => $detalle);
			if (isset($_SESSION['arrayInfoAdicional'])) {
				$arrayInfoAdicional = $_SESSION['arrayInfoAdicional'];
				array_push($arrayInfoAdicional, $arrayDatos);
				$_SESSION['arrayInfoAdicional'] = $arrayInfoAdicional;
			} else {
				array_push($arrayInfoAdicional, $arrayDatos);
				$_SESSION['arrayInfoAdicional'] = $arrayInfoAdicional;
			}
		} else {
			echo "<script>
			$.notify('No se admite mas de 300 caracteres','error');
			</script>";
		}
	} else {
		echo "<script>
		$.notify('Ingrese concepto y detalle','error');
		</script>";
	}

	informacion_adicional();
	detalle_informacion_adicional();
	informacion_cliente();
}

function informacion_cliente()
{
	?>
	<table class="table table-hover" style="padding: 0px; margin-bottom: 0px;">
		<?php
		if (isset($_SESSION['arrayCliente'])) {
			foreach ($_SESSION['arrayCliente'] as $detalle) {
				$concepto = $detalle['concepto'];
				$detalle = $detalle['detalle'];
		?>
				<tr>
					<td style="padding: 2px;" class="col-xs-4"><?php echo $concepto; ?></td>
					<td style="padding: 2px;" class="col-xs-8"><?php echo $detalle; ?></td>
				</tr>
		<?php
			}
		}
		?>
	</table>
<?php
}

function informacion_adicional()
{
?>
	<div class="table-responsive">
		<table class="table table-bordered" style="padding: 0px; margin-bottom: 0px;">
			<tr class="info">
				<td class="col-xs-4" style="padding: 2px;">
					<input type="text" style="height:25px;" class="form-control input-sm" id="adicional_concepto" placeholder="Concepto">
				</td>
				<td class="col-xs-7" style="padding: 2px;">
					<input type="text" style="height:25px;" class="form-control input-sm" id="adicional_detalle" placeholder="Descripción del detalle">
				</td>
				<td class="col-xs-1" style="padding: 2px;"><button type="button" style="height:25px;" class="btn btn-info btn-sm" title="Agregar información adicional" onclick="agrega_info_adicional()"><span class="glyphicon glyphicon-plus"></span></button></td>
			</tr>
		</table>
	</div>
<?php
}

function detalle_informacion_adicional()
{
?>
	<table class="table table-hover" style="padding: 0px; margin-bottom: 0px;">
		<?php
		if (isset($_SESSION['arrayInfoAdicional'])) {
			foreach ($_SESSION['arrayInfoAdicional'] as $detalle) {
				$id = $detalle['id'];
				$concepto = $detalle['concepto'];
				$detalle = $detalle['detalle'];
		?>
				<tr>
					<td style="padding: 2px;" class="col-xs-4"><?php echo $concepto; ?></td>
					<td style="padding: 2px;" class="col-xs-7"><?php echo $detalle; ?></td>
					<td style="padding: 2px;" class="col-xs-1"><button type="button" style="height:17px;" class="btn btn-danger btn-xs" title="Eliminar" onclick="eliminar_info_adicional('<?php echo $id; ?>')"><span class="glyphicon glyphicon-remove"></span></button></td>
				</tr>
		<?php
			}
		}
		?>
	</table>
<?php
}

if ($action == 'eliminar_info_adicional') {
	$intid = $_POST['id'];
	$arrData = $_SESSION['arrayInfoAdicional'];
	for ($i = 0; $i < count($arrData); $i++) {
		if ($arrData[$i]['id'] == $intid) {
			unset($arrData[$i]);
			echo "<script>
            $.notify('Eliminado','error');
            </script>";
		}
	}
	sort($arrData); //para reordenar el array
	$_SESSION['arrayInfoAdicional'] = $arrData;

	informacion_adicional();
	detalle_informacion_adicional();
	informacion_cliente();
}


if ($action == 'subtotales_factura') {
	$serie_factura = $_POST['serie_factura'];
	subtotales_factura($con, $ruc_empresa, $serie_factura, $id_usuario);
}


//para agregar un producto a la lista de la factura cuando lee el codigo de barras
if ($action == 'bar_code') {
	$codigo_producto = $_POST['codigo_producto'];
	$inventario = $_POST['inventario'];
	$id_bodega = $_POST['id_bodega'];
	$serie_factura = $_POST['serie_factura'];

	$sql_producto = mysqli_query($con, "SELECT * FROM productos_servicios WHERE codigo_producto= '" . $codigo_producto . "' and ruc_empresa='" . $ruc_empresa . "'");
	$row_producto = mysqli_fetch_array($sql_producto);
	$id_producto = $row_producto["id"];
	$tipo_producto = $row_producto["tipo_produccion"];
	$id_medida = $row_producto["id_unidad_medida"];

	if (isset($id_producto)) {
		/*
		if ($inventario == "SI" && $tipo_producto == "01") {
			include_once("../clases/saldo_producto_y_conversion.php");
			$saldo_producto_y_conversion = new saldo_producto_y_conversion();
			$saldo_producto = number_format($saldo_producto_y_conversion->existencias_productos($id_bodega, $id_producto, $con), 4, '.', '');
			if ($saldo_producto <= 0) {
				echo "<script>$.notify('Saldo del producto es insuficiente.','error')</script>";
				exit;
			} else {
				$insert_tmp_barcode = mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
				SELECT null, id, 1, precio_producto,'0', tipo_produccion, tarifa_iva , '', '','" . $id_usuario . "', '" . $id_bodega . "', '" . $id_medida . "', 0, 0 FROM productos_servicios WHERE id='" . $id_producto . "' ");
			}
		}
		*/

		if ($tipo_producto == "01") {
			$insert_tmp_barcode = mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
			SELECT null, id, 1, precio_producto,'0', tipo_produccion, tarifa_iva , '', '','" . $id_usuario . "', '" . $id_bodega . "', '" . $id_medida . "', 0, 0 FROM productos_servicios WHERE id='" . $id_producto . "' ");
		}

		if ($tipo_producto == "02") {
			$insert_tmp_barcode = mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
			SELECT null, id, 1, precio_producto,'0', tipo_produccion, tarifa_iva , '', '','" . $id_usuario . "', 0, 0, 0, 0 FROM productos_servicios WHERE id='" . $id_producto . "' ");
		}
	} else {
		echo "<script>$.notify('Producto no encontrado.','error')</script>";
	}

	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
}

function decimales($con, $ruc_empresa, $serie_factura)
{
	$sql_decimales = mysqli_query($con, "SELECT * FROM sucursales WHERE ruc_empresa = '" . $ruc_empresa . "' and serie = '" . $serie_factura . "' ");
	$row_decimales = mysqli_fetch_array($sql_decimales);
	$decimal_precio = intval($row_decimales['decimal_doc'] = "" ? "2" : $row_decimales['decimal_doc']);
	$decimal_cant = intval($row_decimales['decimal_cant'] = "" ? "2" : $row_decimales['decimal_cant']);
	$array_decimales = array('decimal_precio' => $decimal_precio, 'decimal_cantidad' => $decimal_cant);
	return $array_decimales;
}


//agregar item a factura
if ($action == 'agregar_item_factura') {
	unset($_SESSION['arrayFormaPago']);
	$id_producto = $_POST['id_producto'];
	$precio = $_POST['precio'];
	$cantidad = $_POST['cantidad'];
	$id_bodega = $_POST['id_bodega'];
	$lote = $_POST['lote'];
	$caducidad = $_POST['caducidad'];
	$id_medida = $_POST['id_medida'];
	$serie_factura = $_POST['serie_factura'];
	$decimal_cant = decimales($con, $ruc_empresa, $serie_factura)['decimal_cantidad'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_factura)['decimal_precio'];
	$insert_tmp = mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
	SELECT null, '" . $id_producto . "', '" . number_format($cantidad, $decimal_cant, '.', '') . "','" . number_format($precio, $decimal_precio, '.', '') . "','0', tipo_produccion, tarifa_iva , '', '','" . $id_usuario . "', if(tipo_produccion='02', 0,'" . $id_bodega . "'), if(tipo_produccion='02', 0,'" . $id_medida . "'), if(tipo_produccion='02', 0,'" . $lote . "'), if(tipo_produccion='02', 0,'" . $caducidad . "') FROM productos_servicios WHERE id='" . $id_producto . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
}


function detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura)
{
	$decimal_cant = decimales($con, $ruc_empresa, $serie_factura)['decimal_cantidad'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_factura)['decimal_precio'];

?>
	<div class="table-responsive">
		<table class="table table-bordered" style="padding: 0px; margin-bottom: 0px;">
			<tr class="info">
				<th class="text-center" style="padding: 2px;">Código</th>
				<th style="padding: 2px;">Descripción</th>
				<th style="padding: 2px;">Adicional</th>
				<th class="text-center" style="padding: 2px;">Cant.</th>
				<th class="text-right" style="padding: 2px;">PsinIVA</th>
				<th class="text-right" style="padding: 2px;">PconIVA</th>
				<th class="text-center" style="padding: 2px;">Descuento</th>
				<th class="text-right" style="padding: 2px;">IVA</th>
				<th class="text-right" style="padding: 2px;">Subtotal</th>
				<th class="text-right" style="padding: 2px;">Opciones</th>
			</tr>
			<?php

			// PARA MOSTRAR LOS ITEMS DE LA FACTURA
			$sql = mysqli_query($con, "SELECT ft.id_producto as id_producto, ft.tarifa_botellas as adicional, ps.tarifa_iva as tarifa, ft.id as id_tmp, ps.codigo_producto as codigo_producto, ft.cantidad_tmp as cantidad_tmp, ps.nombre_producto as nombre_producto, ft.precio_tmp as precio_tmp, ft.descuento as descuento, uni_med.abre_medida as abre_medida, bod.nombre_bodega as nombre_bodega, ft.vencimiento as vencimiento, ft.lote as lote, ps.tipo_produccion as tipo_produccion FROM factura_tmp as ft INNER JOIN productos_servicios as ps ON ps.id=ft.id_producto LEFT JOIN unidad_medida as uni_med ON uni_med.id_medida=ft.id_medida LEFT JOIN bodega as bod ON bod.id_bodega=ft.id_bodega WHERE ft.id_usuario = '" . $id_usuario . "' ");
			while ($row = mysqli_fetch_array($sql)) {
				$id_tmp = $row["id_tmp"];
				$codigo_producto = $row['codigo_producto'];
				$nombre_producto = $row['nombre_producto'];
				$nombre_lote = $row['lote'];
				$vencimiento = $row['vencimiento'];
				$tipo_produccion = $row['tipo_produccion'];
				$medida = $row['abre_medida'];
				$bodega = $row['nombre_bodega'];
				$adicional = $row['adicional'];
				$id_producto = $row['id_producto'];
				//para saber si quiere que se imprima lote, bodega, vencimiento, 
				$sql_impresion = mysqli_query($con, "SELECT * FROM configuracion_facturacion where ruc_empresa ='" . $ruc_empresa . "' and serie_sucursal ='" . $serie_factura . "'");
				$row_impresion = mysqli_fetch_array($sql_impresion);
				$resultado_lote = $row_impresion['lote_impreso'];
				$resultado_medida = $row_impresion['medida_impreso'];
				$resultado_bodega = $row_impresion['bodega_impreso'];
				$resultado_vencimiento = $row_impresion['vencimiento_impreso'];

				if ($tipo_produccion == "01") {
					if ($resultado_lote == "SI") {
						$nombre_producto = $nombre_producto . " Lt " . $nombre_lote;
					}
					if ($resultado_medida == "SI") {
						$nombre_producto = $nombre_producto . " Md " . $medida;
					}

					if ($resultado_bodega == "SI") {
						$nombre_producto = $nombre_producto . " Bg " . $bodega;
					}

					if ($resultado_vencimiento == "SI") {
						$nombre_producto = $nombre_producto . " Vto " . date('d-m-Y', strtotime($vencimiento));;
					}
				}

				$cantidad = number_format($row['cantidad_tmp'], $decimal_cant, '.', '');
				$precio_venta = number_format($row['precio_tmp'], $decimal_precio, '.', '');
				$descuento = number_format($row['descuento'], 2, '.', '');
				$subtotal = number_format(($cantidad * $precio_venta) - $descuento, 2, '.', '');
				$codigo_tarifa = $row['tarifa'];
				//PARA MOStrar el nombre de la tarifa de iva
				$nombre_tarifa_iva = mysqli_query($con, "select * from tarifa_iva where codigo = '" . $codigo_tarifa . "'");
				$row_tarifa = mysqli_fetch_array($nombre_tarifa_iva);
				$nombre_tarifa = $row_tarifa['tarifa'];
				$tarifa = number_format($row_tarifa['porcentaje_iva'] + 100, 2, '.', '');
				$porcentaje_tarifa = number_format($row_tarifa['porcentaje_iva'] / 100, 2, '.', '');
				$precio_con_iva = number_format($precio_venta + ($precio_venta * $porcentaje_tarifa), $decimal_precio, '.', '');
				$precio_sin_iva = number_format($precio_venta, $decimal_precio, '.', '');
			?>
				<input type="hidden" id="subtotal_item<?php echo $id_tmp; ?>" value="<?php echo number_format($subtotal + $descuento, 2, '.', ''); ?>">
				<input type="hidden" id="tarifa_item<?php echo $id_tmp; ?>" value="<?php echo $tarifa; ?>">
				<input type="hidden" id="descuento_inicial<?php echo $id_tmp; ?>" value="<?php echo $descuento; ?>">
				<input type="hidden" id="porcentaje_item<?php echo $id_tmp; ?>" value="<?php echo $porcentaje_tarifa; ?>">
				<input type="hidden" id="precio_sin_iva_inicial<?php echo $id_tmp; ?>" value="<?php echo $precio_sin_iva; ?>">
				<input type="hidden" id="precio_con_iva_inicial<?php echo $id_tmp; ?>" value="<?php echo $precio_con_iva; ?>">
				<input type="hidden" id="cantidad_inicial<?php echo $id_tmp; ?>" value="<?php echo $cantidad; ?>">
				<input type="hidden" id="id_producto<?php echo $id_tmp; ?>" value="<?php echo $id_producto; ?>">
				<input type="hidden" id="tipo_produccion<?php echo $id_tmp; ?>" value="<?php echo $tipo_produccion; ?>">
				<input type="hidden" id="lote<?php echo $id_tmp; ?>" value="<?php echo $nombre_lote; ?>">
				<tr>
					<td class="text-left" style="padding: 2px;"><?php echo strtoupper($codigo_producto); ?></td>
					<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
					<td class="col-sm-2" style="padding: 2px;">
						<div class="input-group">
							<input type="text" style="text-align:left; height:20px;" class="form-control input-sm" title="Información adicional" id="info_adicional_item<?php echo $id_tmp; ?>" onchange="info_adicional_item('<?php echo $id_tmp; ?>');" value="<?php echo $adicional; ?>">
						</div>
					</td>
					<td class="col-sm-1" style="padding: 2px;">
						<div class="input-group">
							<input type="text" style="text-align:right; height:20px;" class="form-control input-sm" title="Cantidad del producto" id="cantidad_producto<?php echo $id_tmp; ?>" onchange="actualiza_cantidad('<?php echo $id_tmp; ?>');" value="<?php echo $cantidad; ?>">
						</div>
					</td>
					<td class="col-sm-1" style="padding: 2px;">
						<div class="input-group">
							<input type="text" style="text-align:right; height:20px;" class="form-control input-sm" title="Precio del producto sin IVA" id="precio_item_sin_iva<?php echo $id_tmp; ?>" onchange="precio_item_sin_iva('<?php echo $id_tmp; ?>');" value="<?php echo $precio_sin_iva; ?>">
						</div>
					</td>
					<td class="col-sm-1" style="padding: 2px;">
						<div class="input-group">
							<input type="text" style="text-align:right; height:20px;" class="form-control input-sm" title="Precio del producto con IVA" id="precio_item_con_iva<?php echo $id_tmp; ?>" onchange="precio_item_con_iva('<?php echo $id_tmp; ?>');" value="<?php echo $precio_con_iva; ?>">
						</div>
					</td>
					<td class="col-sm-1" style="padding: 2px;">
						<div class="input-group">
							<input type="text" style="text-align:right; height:20px;" class="form-control input-sm" title="Descuento" id="descuento_item<?php echo $id_tmp; ?>" onchange="descuento_item('<?php echo $id_tmp; ?>');" value="<?php echo $descuento; ?>">
						</div>
					</td>
					<td class="text-right" style="padding: 2px;"><?php echo $nombre_tarifa; ?></td>
					<td class="text-right" style="padding: 2px;"><?php echo $subtotal; ?></td>
					<td class="text-right" style="padding: 2px;">
						<button type="button" style="height:20px;" class="btn btn-info btn-xs" title="Opciones de descuentos" onclick="opciones_descuentos('<?php echo $id_tmp; ?>')" data-toggle="modal" data-target="#aplicarDescuento">D</button>
						<button type="button" style="height:20px;" class="btn btn-danger btn-xs" title="Eliminar item" onclick="eliminar_item_factura('<?php echo $id_tmp; ?>')">X</button>
					</td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
<?php
}


function subtotales_factura($con, $ruc_empresa, $serie_factura, $id_usuario)
{
	$decimal_cant = decimales($con, $ruc_empresa, $serie_factura)['decimal_cantidad'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_factura)['decimal_precio'];

	$sql_subtotales = mysqli_query($con, "SELECT sum(round((cantidad_tmp * precio_tmp) - descuento,2)) as subtotal, sum(round(descuento,2)) as descuento FROM factura_tmp WHERE id_usuario = '" . $id_usuario . "' group by id_usuario ");
	$row_subtotales = mysqli_fetch_array($sql_subtotales);
	//$row_count = mysqli_num_rows($sql_subtotales);
	$subtotal_general = $row_subtotales['subtotal'];
	$total_descuento = $row_subtotales['descuento'];
	//if($row_count>0){
?>
	<div class="table-responsive">
		<table class="table table-bordered" style="padding: 0px; margin-bottom: 0px;">
			<tr class="info">
				<td class="text-right" style="padding: 2px;">Subtotal general: </td>
				<td class="text-right" style="padding: 2px;"><?php echo number_format($subtotal_general, 2, '.', ''); ?></td>
			</tr>
			<?php
			//PARA MOSTRAR LOS NOMBRES DE CADA TARIFA DE IVA Y LOS VALORES DE CADA SUBTOTAL
			$sql_tarifas_iva = mysqli_query($con, "select sum(round((ft.cantidad_tmp * ft.precio_tmp) - ft.descuento,2)) as suma_tarifa_iva, ti.tarifa as tarifa FROM factura_tmp as ft INNER JOIN tarifa_iva as ti ON ti.codigo = ft.tarifa_iva WHERE ft.id_usuario = '" . $id_usuario . "' group by ft.tarifa_iva ");
			while ($row = mysqli_fetch_array($sql_tarifas_iva)) {
				$nombre_tarifa_iva = strtoupper($row["tarifa"]);
				$subtotal_tarifa_iva = number_format($row['suma_tarifa_iva'], 2, '.', '');
			?>
				<tr class="info">
					<td class="text-right" style="padding: 2px;">Subtotal <?php echo (strtolower($nombre_tarifa_iva)); ?>:</td>
					<td class="text-right" style="padding: 2px;"><?php echo number_format($subtotal_tarifa_iva, 2, '.', ''); ?></td>
				</tr>

			<?php
			}
			?>
			<tr class="info">
				<td class="text-right" style="padding: 2px;">Total descuento: </td>
				<td class="text-right" style="padding: 2px;"><?php echo number_format($total_descuento, 2, '.', ''); ?></td>
			</tr>
			<?php
			//PARA MOSTRAR LOS IVAS
			$total_iva = 0;
			$subtotal_iva = 0;
			//$sql_iva = mysqli_query($con, "select ti.tarifa as tarifa, (sum(round(ft.cantidad_tmp,'" . $decimal_cant . "') * round(ft.precio_tmp,'" . $decimal_precio . "') - descuento) * ti.tarifa /100)  as porcentaje FROM factura_tmp ft, tarifa_iva ti where ti.codigo = ft.tarifa_iva and ft.id_usuario = '" . $id_usuario . "' and ti.tarifa > 0 group by ft.tarifa_iva ");
			$sql_iva = mysqli_query($con, "select ti.tarifa as tarifa_iva, sum(round(((ft.cantidad_tmp * ft.precio_tmp) - descuento) * (ti.tarifa /100),2)) as valor_iva FROM factura_tmp as ft INNER JOIN tarifa_iva as ti ON ti.codigo = ft.tarifa_iva WHERE ft.id_usuario = '" . $id_usuario . "' and ti.tarifa > 0 group by ft.tarifa_iva ");
			while ($row = mysqli_fetch_array($sql_iva)) {
				$nombre_porcentaje_iva = strtoupper($row["tarifa_iva"]);
				$subtotal_iva = $row['valor_iva'];
				$total_iva += $row['valor_iva'];
			?>
				<tr class="info">
					<td class="text-right" style="padding: 2px;">IVA <?php echo ($nombre_porcentaje_iva); ?>:</td>
					<td class="text-right" style="padding: 2px;"><?php echo number_format($subtotal_iva, 2, '.', ''); ?></td>
				</tr>
			<?php
			}
			//para mostrar la casilla de propina dependiendo si esta asignada o no
			$propina = mysqli_query($con, "SELECT * FROM configuracion_facturacion where ruc_empresa ='" . $ruc_empresa . "' and serie_sucursal ='" . $serie_factura . "'");
			$row_propina = mysqli_fetch_array($propina);
			$resultado_propina = $row_propina['propina'];
			$resultado_tasa = $row_propina['tasa_turistica'];
			$servicio = 0;
			$tasa = 0;
			if (isset($_SESSION['arrayServicio'])) {
				$servicio = $_SESSION['arrayServicio']['0']['servicio'];
			}
			if (isset($_SESSION['arrayTasa'])) {
				$tasa = $_SESSION['arrayTasa']['0']['tasa'];
			}

			if ($resultado_propina == "SI") {
			?>
				<tr class="info">
					<td class="text-right" style="padding: 2px;">Servicio: </td>
					<td class="col-sm-3" style="padding: 2px;">
						<div class="input-group">
							<input class="form-control text-center input-sm" style="text-align:right; height:20px;" type="text" id="propina" value="<?php echo number_format($servicio, 2, '.', ''); ?>" onchange="agrega_propina();" title="Ingrese el valor del servicio o propina y presione enter">
						</div>
					</td>
				</tr>
			<?php
			}
			if ($resultado_tasa == "SI") {
			?>
				<tr class="info">
					<td class="text-right" style="padding: 2px;">Tasa turistica: </td>
					<td class="col-sm-1" style="padding: 2px;">
						<input class="form-control text-center input-sm" style="text-align:right; height:20px;" type="text" id="tasa" value="<?php echo number_format($tasa, 2, '.', ''); ?>" onchange="agrega_tasa();" title="Ingrese el valor de la tasa y presione enter">
					</td>
				</tr>
			<?php
			}
			?>

			<tr class="info">
				<td class="text-right" style="padding: 2px;">Total: </td>
				<td class="text-right" style="padding: 2px;"><?php echo number_format($subtotal_general + $total_iva + $servicio + $tasa, 2, '.', ''); ?></td>
				<input type="hidden" id="total_factura" value="<?php echo number_format($subtotal_general + $total_iva + $servicio + $tasa, 2, '.', ''); ?>">
			</tr>
		</table>
	</div>
<?php
	//}
}

if ($action == 'forma_pago') {
	$total_factura = $_POST['total_factura'];
	formas_pago($total_factura);
	detalle_formas_pago($con, $total_factura);
}


if ($action == 'agrega_forma_pago') {
	$forma_pago = $_POST['forma_pago'];
	$valor_pago = $_POST['valor_pago'];
	$total_factura = $_POST['suma_factura'];

	if ($valor_pago < 0 || ($valor_pago > $total_factura)) {
		echo "<script>
		$.notify('El valor ingresado no puede ser mayor al total facturado o menor a cero','error');
		</script>";
	} else {
		$arrayFormaPago = array();
		$arrayDatos = array('id' => rand(5, 500), 'codigo' => $forma_pago, 'valor' => $valor_pago);
		if (isset($_SESSION['arrayFormaPago'])) {
			$on = true;
			$arrayFormaPago = $_SESSION['arrayFormaPago'];
			for ($pr = 0; $pr < count($arrayFormaPago); $pr++) {
				if ($arrayFormaPago[$pr]['codigo'] == $forma_pago) {
					$arrayFormaPago[$pr]['valor'] += $valor_pago;
					$on = false;
				}
			}
			if ($on) {
				array_push($arrayFormaPago, $arrayDatos);
			}
			$_SESSION['arrayFormaPago'] = $arrayFormaPago;
		} else {
			array_push($arrayFormaPago, $arrayDatos);
			$_SESSION['arrayFormaPago'] = $arrayFormaPago;
		}
	}

	formas_pago($total_factura);
	detalle_formas_pago($con, $total_factura);
}


function formas_pago($total_factura)
{
?>
	<div class="table-responsive">
		<table class="table table-bordered" style="padding: 0px; margin-bottom: 0px;">
			<tr class="info">
				<td class="col-xs-8" style="padding: 2px;">
					<select class="form-control input-sm" id="codigo_forma_pago" style="text-align:left; height:25px; padding-top:3px;">
						<?php
						$con = conenta_login();
						$sql_forma_pago = mysqli_query($con, "SELECT * FROM formas_de_pago WHERE aplica_a ='VENTAS' order by nombre_pago asc");
						while ($row_forma_pago = mysqli_fetch_assoc($sql_forma_pago)) {
							if ($row_forma_pago['codigo_pago'] == '20') {
						?>
								<option value="20" selected>OTROS CON UTILIZACION DEL SISTEMA FINANCIERO</option>
							<?php
							} else {
							?>
								<option value="<?php echo $row_forma_pago['codigo_pago'] ?>"><?php echo $row_forma_pago['nombre_pago']; ?></option>
						<?php
							}
						}
						?>
					</select>
				</td>
				<td class="col-xs-3" style="padding: 2px;">
					<input type="text" style="height:25px;" class="form-control input-sm text-right" id="valor_forma_pago" value="<?php echo $total_factura; ?>" placeholder="Valor">
				</td>
				<td class="col-xs-1" style="padding: 2px;"><button type="button" style="height:25px;" class="btn btn-info btn-sm" title="Agregar forma de pago" onclick="agrega_forma_pago()"><span class="glyphicon glyphicon-plus"></span></button></td>
			</tr>
		</table>
	</div>
<?php
}

function detalle_formas_pago($con, $total_factura)
{
?>
	<table class="table table-hover" style="padding: 0px; margin-bottom: 0px;">
		<?php
		$total = 0;
		if (isset($_SESSION['arrayFormaPago'])) {
			foreach ($_SESSION['arrayFormaPago'] as $detalle) {
				$id = $detalle['id'];
				$codigo = $detalle['codigo'];
				$valor = number_format($detalle['valor'], 2, '.', '');
				$total += $valor;
				$sql_forma_pago = mysqli_query($con, "SELECT * FROM formas_de_pago WHERE aplica_a ='VENTAS' and codigo_pago='" . $codigo . "'");
				$row_forma_pago = mysqli_fetch_array($sql_forma_pago);
		?>
				<tr>
					<td style="padding: 2px;" class="col-xs-7">
						<font size="1"><?php echo $row_forma_pago['nombre_pago']; ?></font>
					</td>
					<td style="padding: 2px;" class="col-xs-2">
						<font size="2"><?php echo $valor; ?></font>
					</td>
					<td style="padding: 2px;" class="col-xs-1"><button type="button" style="height:17px;" class="btn btn-danger btn-xs" title="Eliminar" onclick="eliminar_forma_pago('<?php echo $id; ?>')"><span class="glyphicon glyphicon-remove"></span></button></td>
				</tr>
		<?php
			}
		}
		?>
		<tr class="info">
			<td style="padding: 2px;" class="col-xs-7 text-left">Total </td>
			<td style="padding: 2px;" class="col-xs-2"><?php echo number_format($total, 2, '.', ''); ?></td>
			<td style="padding: 2px;" class="col-xs-1">
				<input type="hidden" id="total_formas_pago" value="<?php echo number_format($total_factura - $total, 2, '.', ''); ?>">
				<font color="red" size="1"><?php echo number_format($total_factura - $total, 2, '.', ''); ?></font>
			</td>
		</tr>
	</table>
	<?php
}

if ($action == 'eliminar_forma_pago') {
	$intid = $_POST['id'];
	$total_factura = $_POST['suma_factura'];
	$arrData = $_SESSION['arrayFormaPago'];
	for ($i = 0; $i < count($arrData); $i++) {
		if ($arrData[$i]['id'] == $intid) {
			unset($arrData[$i]);
			echo "<script>
            $.notify('Eliminado','error');
            </script>";
		}
	}
	sort($arrData); //para reordenar el array
	$_SESSION['arrayFormaPago'] = $arrData;
	formas_pago($total_factura);
	detalle_formas_pago($con, $total_factura);
}

if ($action == 'eliminar_item_factura') {
	$id_tmp = intval($_POST['id']);
	$serie_factura = $_POST['serie_factura'];
	$delete = mysqli_query($con, "DELETE FROM factura_tmp WHERE id='" . $id_tmp . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
	echo "<script>
	$.notify('Eliminado','error');
	</script>";
}

//para calcular precio con iva y precio sin iva
if ($action == 'calculo_precio_item') {
	unset($_SESSION['arrayFormaPago']);
	$id_tmp = intval($_POST['id']);
	$serie_factura = $_POST['serie_factura'];
	$precio = $_POST['precio'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_factura)['decimal_precio'];
	$update = mysqli_query($con, "UPDATE factura_tmp SET precio_tmp='" . number_format($precio, $decimal_precio, '.', '') . "' WHERE id='" . $id_tmp . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
	echo "<script>
	$.notify('Precio actualizado','info');
	</script>";
}

//para agregar informacion adicional en cada item
if ($action == 'info_adicional_item') {
	$info_adicional = strClean($_POST['info_adicional']);
	$id = $_POST['id'];
	$serie_factura = $_POST['serie_factura'];

	$update = mysqli_query($con, "UPDATE factura_tmp SET tarifa_botellas='" . $info_adicional . "' WHERE id='" . $id . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
	echo "<script>
	$.notify('Adicional actualizado','info');
	</script>";
}

//para actualizar la cantidad de cada item
if ($action == 'actualiza_cantidad') {
	unset($_SESSION['arrayFormaPago']);
	$cantidad_producto = $_POST['cantidad_producto'];
	$id = $_POST['id'];
	$serie_factura = $_POST['serie_factura'];
	$decimal_cant = decimales($con, $ruc_empresa, $serie_factura)['decimal_cantidad'];

	$update = mysqli_query($con, "UPDATE factura_tmp SET cantidad_tmp='" . number_format($cantidad_producto, $decimal_cant, '.', '') . "' WHERE id='" . $id . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
	echo "<script>
	$.notify('Cantidad actualizada','info');
	</script>";
}

//para actualizar el descuento de cada item
if ($action == 'actualiza_descuento_item') {
	unset($_SESSION['arrayFormaPago']);
	$descuento_item = $_POST['descuento_item'];
	$id = $_POST['id'];
	$serie_factura = $_POST['serie_factura'];
	$update = mysqli_query($con, "UPDATE factura_tmp SET descuento='" . $descuento_item . "' WHERE id='" . $id . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
	echo "<script>
	$.notify('Descuento actualizado','info');
	</script>";
}

//para actualizar el descuento de todos los item
if ($action == 'aplicar_descuento_todos') {
	unset($_SESSION['arrayFormaPago']);
	$porcentaje_descuento = $_POST['porcentaje_descuento'];
	$serie_factura = $_POST['serie_factura'];
	$update = mysqli_query($con, "UPDATE factura_tmp SET descuento=(cantidad_tmp*precio_tmp)* '" . $porcentaje_descuento . "' /100 WHERE id_usuario='" . $id_usuario . "'");
	detalle_factura($con, $id_usuario, $ruc_empresa, $serie_factura);
	echo "<script>
	$.notify('Descuento aplicado a todos los items','info');
	</script>";
}


if ($action == 'agrega_propina') {
	unset($_SESSION['arrayFormaPago']);
	$propina = $_POST['propina'];
	$serie_factura = $_POST['serie_factura'];
	unset($_SESSION['arrayServicio']);
	$arrayServicio = array();
	$arrayDatos = array('id' => rand(5, 50), 'servicio' => $propina);
	if (isset($_SESSION['arrayServicio'])) {
		$arrayInfoarrayServicioAdicional = $_SESSION['arrayServicio'];
		array_push($arrayServicio, $arrayDatos);
		$_SESSION['arrayServicio'] = $arrayServicio;
	} else {
		array_push($arrayServicio, $arrayDatos);
		$_SESSION['arrayServicio'] = $arrayServicio;
	}
	subtotales_factura($con, $ruc_empresa, $serie_factura, $id_usuario);
	echo "<script>
			$.notify('Servicio agregado','info');
			</script>";
}


if ($action == 'agrega_tasa') {
	unset($_SESSION['arrayFormaPago']);
	$tasa = $_POST['tasa'];
	$serie_factura = $_POST['serie_factura'];
	unset($_SESSION['arrayTasa']);
	$arrayTasa = array();
	$arrayDatos = array('id' => rand(5, 50), 'tasa' => $tasa);
	if (isset($_SESSION['arrayTasa'])) {
		$arrayInfoarrayServicioAdicional = $_SESSION['arrayTasa'];
		array_push($arrayTasa, $arrayDatos);
		$_SESSION['arrayTasa'] = $arrayTasa;
	} else {
		array_push($arrayTasa, $arrayDatos);
		$_SESSION['arrayTasa'] = $arrayTasa;
	}
	subtotales_factura($con, $ruc_empresa, $serie_factura, $id_usuario);
	echo "<script>
				$.notify('Tasa agregada','info');
				</script>";
}


//guardar o modificar factura
if ($action == 'guardar_factura') {
	$id_factura = intval($_POST['id_factura']);
	$id_cliente = $_POST['id_cliente_factura'];
	$fecha_factura = date('Y/m/d', strtotime($_POST['fecha_factura']));
	$serie_factura = $_POST['serie_factura'];
	$secuencial_factura = $_POST['secuencial_factura'];
	$propina = $_POST['propina'];
	$tasa = $_POST['tasa'];
	$total_factura = $_POST['suma_factura'];
	$total_formas_pago = $_POST['total_formas_pago'];
	$codigo_forma_pago = $_POST['codigo_forma_pago'];
	$referencia_salida_inventario = $serie_factura . "-" . str_pad($secuencial_factura, 9, "000000000", STR_PAD_LEFT);
	$guia_factura = "";
	$busca_consumidor_final = mysqli_query($con, "SELECT * FROM clientes WHERE id = '" . $id_cliente . "' and ruc='9999999999999' ");
	$count_consumidor_final = mysqli_num_rows($busca_consumidor_final);

	//buscar detalle de factura
	$sql_factura_temporal = mysqli_query($con, "SELECT fac_tmp.id_producto as id_producto,
	fac_tmp.cantidad_tmp as cantidad_tmp, fac_tmp.precio_tmp as precio_tmp, fac_tmp.descuento as descuento,
	fac_tmp.tipo_produccion as tipo_produccion, fac_tmp.tarifa_iva as tarifa_iva, fac_tmp.tarifa_ice as tarifa_ice, 
	fac_tmp.tarifa_botellas as tarifa_botellas, fac_tmp.id_usuario as id_usuario, fac_tmp.id_bodega as id_bodega,
	fac_tmp.id_medida as id_medida, fac_tmp.lote as lote, fac_tmp.vencimiento as vencimiento, pro.codigo_producto as codigo_producto,
	pro.nombre_producto as nombre_producto, med.abre_medida as abre_medida, bod.nombre_bodega as nombre_bodega from factura_tmp as fac_tmp LEFT JOIN productos_servicios as pro ON fac_tmp.id_producto=pro.id LEFT JOIN unidad_medida as med ON med.id_medida=fac_tmp.id_medida LEFT JOIN bodega as bod ON bod.id_bodega=fac_tmp.id_bodega WHERE fac_tmp.id_usuario = '" . $id_usuario . "'");
	$count_items = mysqli_num_rows($sql_factura_temporal);

	if (empty($id_cliente)) {
		echo "<script>
            $.notify('Ingrese cliente','error');
            </script>";
	} else if (empty($fecha_factura)) {
		echo "<script>
        $.notify('Ingrese fecha de emisión','error');
        </script>";
	} else if (empty($serie_factura)) {
		echo "<script>
        $.notify('Seleccione una serie','error');
        </script>";
	} else if (empty($secuencial_factura)) {
		echo "<script>
        $.notify('Seleccione serie','error');
        </script>";
	} else if ($count_consumidor_final>0 && $total_factura > 50) {
		echo "<script>
        $.notify('El límite máximo para consumidor final es $ 50.00 dólares','error');
        </script>";
	} else if ($total_factura < 0 ) {
		echo "<script>
        $.notify('El valor de la factura no puede ser negativo','error');
        </script>";
	} else if (isset($_SESSION['arrayFormaPago']) && $total_formas_pago != 0) {
		echo "<script>
        $.notify('Las formas de pago no coinciden con el total de la factura','error');
        </script>";
	} else if (!isset($_SESSION['arrayFormaPago']) && $total_factura != $total_formas_pago) {
		echo "<script>
        $.notify('Las formas de pago no coinciden con el total de la factura','error');
        </script>";
	} else if ($count_items == 0) {
		echo "<script>
        $.notify('Ingrese productos o servicios a la factura','error');
        </script>";
	} else {
		if (empty($id_factura)) {
			$busca_factura = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura ='" . $secuencial_factura . "' and tipo_factura = '1'");
			$count_factura = mysqli_num_rows($busca_factura);
			if ($count_factura > 0) {
				echo "<script>
                $.notify('El número de factura ya existe','error');
                </script>";
			} else {
				//para guardar el encabezado de la factura
				$query_encabezado_factura = guardar_encabezado_factura($con, $ruc_empresa, $fecha_factura, $serie_factura, $secuencial_factura, $id_cliente, $guia_factura, $total_factura, $id_usuario, $propina, $tasa);
				//para guardar la forma de pago de la factura
				$query_forma_pago_factura = guarda_formas_de_pago_factura_bd($con, $ruc_empresa, $serie_factura, $secuencial_factura, $codigo_forma_pago, $total_factura);
				//para guardar detalle adicional de la factura
				$query_guarda_detalle_adicional_factura = guarda_adicionales_factura_bd($con, $ruc_empresa, $serie_factura, $secuencial_factura);
				//para guardar el detalle de la factura y en el inventario
				$guarda_detalle_factura = detalle_factura_inventario($con, $sql_factura_temporal, $ruc_empresa, $serie_factura, $secuencial_factura, $referencia_salida_inventario, $fecha_factura);

				if ($query_encabezado_factura && $query_forma_pago_factura && $query_guarda_detalle_adicional_factura && $guarda_detalle_factura) {
					nueva_factura($con, $id_usuario);
					echo "<script>
                $.notify('Factura guardada','success');
				setTimeout(function (){location.href ='../modulos/facturas.php'}, 1000);
                </script>";
				} else {
					echo "<script>
                $.notify('Revisar información, no se ha guardado la factura','error');
                </script>";
				}
			}
		} else {
			//modificar la factura
			$busca_factura = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE id_encabezado_factura != '" . $id_factura . "' and ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura ='" . $secuencial_factura . "' and tipo_factura = '1'");
			$count_facturas = mysqli_num_rows($busca_factura);

			$buscar_registros_consignacion = mysqli_query($con, "SELECT * from encabezado_consignacion where ruc_empresa='" . $ruc_empresa . "' and serie_sucursal='" . $serie_factura . "' and factura_venta='" . $secuencial_factura . "' and operacion ='FACTURA'");
			$contar_consignacion = mysqli_num_rows($buscar_registros_consignacion);

			if ($count_facturas > 0) {
				echo "<script>
                $.notify('La factura ya esta registrada','error');
                </script>";
			} else {
				//cuando no hay registros de consignaciones
				if ($contar_consignacion == 0) {
					//para eliminar el encabezado de factura, cuerpo factura, adicionales factura
					$delete_encabezado_factura = mysqli_query($con, "DELETE FROM encabezado_factura WHERE ruc_empresa= '" . $ruc_empresa . "' and serie_factura= '" . $serie_factura . "' and secuencial_factura= '" . $secuencial_factura . "' ");
					$delete_cuerpo_factura = mysqli_query($con, "DELETE FROM cuerpo_factura WHERE ruc_empresa= '" . $ruc_empresa . "' and serie_factura= '" . $serie_factura . "' and secuencial_factura= '" . $secuencial_factura . "'");
					$query_encabezado_factura = guardar_encabezado_factura($con, $ruc_empresa, $fecha_factura, $serie_factura, $secuencial_factura, $id_cliente, $guia_factura, $total_factura, $id_usuario, $propina, $tasa);
					//para guardar el detalle de la factura y en el inventario
					$guarda_detalle_factura = detalle_factura_inventario($con, $sql_factura_temporal, $ruc_empresa, $serie_factura, $secuencial_factura, $referencia_salida_inventario, $fecha_factura);
				} else {
					//actualizar encabezado factura
					$query_encabezado_factura = actualizar_encabezado_factura($con, $ruc_empresa, $fecha_factura, $serie_factura, $secuencial_factura, $id_cliente, $guia_factura, $id_usuario);
					//para guardar el detalle de la factura y en el inventario
					$guarda_detalle_factura = true;
				}

				$delete_adicionales_factura = mysqli_query($con, "DELETE FROM detalle_adicional_factura WHERE ruc_empresa= '" . $ruc_empresa . "' and serie_factura= '" . $serie_factura . "' and secuencial_factura= '" . $secuencial_factura . "'");
				$delete_formas_pago_factura = mysqli_query($con, "DELETE FROM formas_pago_ventas WHERE ruc_empresa= '" . $ruc_empresa . "' and serie_factura= '" . $serie_factura . "' and secuencial_factura= '" . $secuencial_factura . "'");
				//$delete_registro_inventario = mysqli_query($con, "DELETE FROM inventarios WHERE ruc_empresa = '" . $ruc_empresa . "' and id_documento_venta='" . $referencia_salida_inventario . "'");

				//para guardar la forma de pago de la factura
				$query_forma_pago_factura = guarda_formas_de_pago_factura_bd($con, $ruc_empresa, $serie_factura, $secuencial_factura, $codigo_forma_pago, $total_factura);
				//para guardar detalle adicional de la factura
				$query_guarda_detalle_adicional_factura = guarda_adicionales_factura_bd($con, $ruc_empresa, $serie_factura, $secuencial_factura);

				//para actualizar el cliente en la facturacion de consignacion
				$update_facturacion_consignacion = mysqli_query($con, "UPDATE encabezado_consignacion as enc_con SET enc_con.id_cli_pro = '" . $id_cliente . "' WHERE enc_con.operacion='FACTURA' and enc_con.serie_sucursal='" . $serie_factura . "' and enc_con.factura_venta='" . $secuencial_factura . "' ");

				if ($query_encabezado_factura && $guarda_detalle_factura && $query_forma_pago_factura && $query_guarda_detalle_adicional_factura) {
					echo "<script>
                    $.notify('Factura actualizada','success');
					setTimeout(function (){location.href ='../modulos/facturas.php'}, 1000);
                        </script>";
				} else {
					echo "<script>
					$.notify('Revisar información, no se ha guardado la factura','error');
                        </script>";
				}
			}
		}
	}
}



function guarda_formas_de_pago_factura_bd($con, $ruc_empresa, $serie_factura, $secuencial_factura, $codigo_forma_pago, $total_factura)
{
	if (isset($_SESSION['arrayFormaPago'])) {
		foreach ($_SESSION['arrayFormaPago'] as $detalle) {
			$codigo = $detalle['codigo'];
			$valor = $detalle['valor'];
			$query_forma_pago_factura = guarda_forma_de_pago($con, $ruc_empresa, $serie_factura, $secuencial_factura, $codigo, $valor);
		}
	} else {
		$query_forma_pago_factura = guarda_forma_de_pago($con, $ruc_empresa, $serie_factura, $secuencial_factura, $codigo_forma_pago, $total_factura);
	}
	return ($query_forma_pago_factura);
}

function guarda_adicionales_factura_bd($con, $ruc_empresa, $serie_factura, $secuencial_factura)
{
	if (isset($_SESSION['arrayInfoAdicional'])) {
		foreach ($_SESSION['arrayInfoAdicional'] as $detalle) {
			$concepto = $detalle['concepto'];
			$detalle = $detalle['detalle'];
			$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '" . $ruc_empresa . "','" . $serie_factura . "','" . $secuencial_factura . "','" . $concepto . "', '" . $detalle . "')");
		}
	}
	if (isset($_SESSION['arrayCliente'])) {
		foreach ($_SESSION['arrayCliente'] as $detalle) {
			$concepto = $detalle['concepto'];
			$detalle = $detalle['detalle'];
			$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '" . $ruc_empresa . "','" . $serie_factura . "','" . $secuencial_factura . "','" . $concepto . "', '" . $detalle . "')");
		}
	}
	return ($query_guarda_detalle_adicional_factura);
}

//PARA ELIMINAR FACTURAS ECHAS
if ($action == 'eliminar_factura') {
	$id_factura = intval($_POST['id_factura']);
	eliminar_factura($con, $id_factura, $ruc_empresa, $id_usuario);
}


	function eliminar_factura($con, $id_factura, $ruc_empresa, $id_usuario){
	$fecha_registro=date("Y-m-d H:i:s");
	$busca_datos_factura = "SELECT ef.id_registro_contable as id_registro_contable, ef.serie_factura as serie_factura, ef.secuencial_factura as secuencial_factura, ef.fecha_factura as fecha_factura, cl.ruc as ruc_cliente, ef.ruc_empresa as ruc_empresa FROM encabezado_factura as ef INNER JOIN clientes as cl ON ef.id_cliente=cl.id WHERE ef.id_encabezado_factura = '".$id_factura."' ";
	$result = $con->query($busca_datos_factura);
	$datos_factura = mysqli_fetch_array($result);
	$serie_factura = $datos_factura['serie_factura'];
	$secuencial = $datos_factura['secuencial_factura'];
	$mes_periodo = date("m", strtotime($datos_factura['fecha_factura']));
	$anio_periodo = date("Y", strtotime($datos_factura['fecha_factura']));
	$ruc_empresa_periodo = $datos_factura['ruc_empresa'];
	$ruc_del_cliente = $datos_factura['ruc_cliente'];
	$id_documento_venta = $serie_factura . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
	$referencia = "Factura: " . $serie_factura . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
	$referencia_modificada = "Factura eliminada: " . $serie_factura . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);

	$buscar_registros_consignacion = mysqli_query($con, "SELECT * from encabezado_consignacion where ruc_empresa='" . $ruc_empresa . "' and serie_sucursal='" . $serie_factura . "' and factura_venta='" . $secuencial . "' and operacion ='FACTURA' and observaciones !='ANULADA'");
	$row_consignacion = mysqli_fetch_array($buscar_registros_consignacion);
	$codigo_consignacion = empty($row_consignacion['codigo_unico']) ? "" : $row_consignacion['codigo_unico'];

	$id_registro_contable = $datos_factura['id_registro_contable'];
	$anio_documento = date("Y", strtotime($datos_factura['fecha_factura']));
	if($id_registro_contable>0){
	eliminar_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
	}

	$actualiza_encabezado_consignacion = mysqli_query($con, "UPDATE encabezado_consignacion SET observaciones='ANULADA' WHERE codigo_unico='" . $codigo_consignacion . "' ");
	$elimina_detalle_consignacion = mysqli_query($con, "DELETE FROM detalle_consignacion WHERE codigo_unico='" . $codigo_consignacion . "' ");
	
	eliminar_ingreso($con, $id_factura, $ruc_empresa, $id_usuario);

	//eliminar la factura y los datos de la factura
	if ($delete = mysqli_query($con, "DELETE FROM encabezado_factura WHERE id_encabezado_factura = '" . $id_factura . "'")
		&& $delete_detalle = mysqli_query($con, "DELETE FROM cuerpo_factura WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura = '" . $secuencial . "'")
		&& $delete_pago = mysqli_query($con, "DELETE FROM formas_pago_ventas WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura = '" . $secuencial . "'")
		&& $delete_adicional = mysqli_query($con, "DELETE FROM detalle_adicional_factura WHERE ruc_empresa = '" . $ruc_empresa . "' and serie_factura = '" . $serie_factura . "' and secuencial_factura = '" . $secuencial . "'")
	//&& $update_inventario = mysqli_query($con, "UPDATE inventarios SET cantidad_salida='0', precio='0', fecha_registro='" . $fecha_registro . "', referencia='" . $referencia_modificada . "',id_usuario='" . $id_usuario . "', fecha_agregado='" . $fecha_registro . "' WHERE ruc_empresa = '" . $ruc_empresa . "' and id_documento_venta='" . $id_documento_venta . "' ")
		) {
		echo "<script>
			$.notify('Factura eliminada exitosamente','success')
			</script>";
	} else {
		echo "<script>
			$.notify('Lo siento algo ha salido mal intenta nuevamente','error')
			</script>";
	}
}

function eliminar_ingreso($con, $codigo_documento_cv, $ruc_empresa, $id_usuario){
	$buscar_ingresos = mysqli_query($con, "SELECT ing_egr.fecha_ing_egr as fecha_ingreso, det_ing_egr.codigo_documento as codigo_documento, ing_egr.codigo_contable as codigo_contable FROM detalle_ingresos_egresos as det_ing_egr INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=det_ing_egr.codigo_documento WHERE det_ing_egr.codigo_documento_cv = '".$codigo_documento_cv."' and det_ing_egr.tipo_documento='INGRESO'");
	while ($det_ingresos = mysqli_fetch_array($buscar_ingresos)){
	//para anular el asiento contable del ingreso
	$codigo_contable = $det_ingresos['codigo_contable'];
	$codigo_unico = $det_ingresos['codigo_documento'];
	$anio_ingreso = date("Y", strtotime($det_ingresos['fecha_ingreso']));
	if($codigo_contable>0){
	eliminar_asiento_contable($con, $codigo_contable, $ruc_empresa, $id_usuario, $anio_ingreso);
	}
	//para anular los registros de ingresos
	$anular_ingreso = mysqli_query($con, "UPDATE ingresos_egresos SET detalle_adicional='ANULADO', valor_ing_egr='0.00' WHERE codigo_documento = '" . $codigo_unico . "' and tipo_ing_egr='INGRESO'");
	$delete_detalle_ingreso = mysqli_query($con, "DELETE FROM detalle_ingresos_egresos WHERE codigo_documento = '" . $codigo_unico . "' and tipo_documento='INGRESO'");
	$delete_pagos_ingreso = mysqli_query($con, "DELETE FROM formas_pagos_ing_egr WHERE codigo_documento = '" . $codigo_unico . "' and tipo_documento='INGRESO' ");
	}	
}

function eliminar_asiento_contable($con, $codigo_contable, $ruc_empresa, $id_usuario, $anio_ingreso){
	include("../clases/anular_registros.php");
	$anular_asiento_contable = new anular_registros();
	$resultado_anular_documento = $anular_asiento_contable->anular_asiento_contable($con, $codigo_contable, $ruc_empresa, $id_usuario, $anio_ingreso);
return $resultado_anular_documento;
}


//PARA BUSCAR LAS FACTURAS
if ($action == 'buscar_facturas') {
	// escaping, additionally removing everything that could be (html/javascript-) code
	$q = mysqli_real_escape_string($con, (strip_tags($_REQUEST['q'], ENT_QUOTES)));
	$ordenado = mysqli_real_escape_string($con, (strip_tags($_GET['ordenado'], ENT_QUOTES)));
	$por = mysqli_real_escape_string($con, (strip_tags($_GET['por'], ENT_QUOTES)));
	$aColumns = array('fecha_factura', 'secuencial_factura', 'serie_factura', 'nombre', 'ruc', 'total_factura'); //Columnas de busqueda
	$sTable = "encabezado_factura as ef LEFT JOIN clientes as cl ON cl.id=ef.id_cliente";
	$sWhere = "WHERE ef.ruc_empresa ='" . $ruc_empresa . "' ";
	if ($_GET['q'] != "") {
		$sWhere = "WHERE (ef.ruc_empresa ='" . $ruc_empresa . "' AND ";

		for ($i = 0; $i < count($aColumns); $i++) {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $q . "%' AND ef.ruc_empresa = '" . $ruc_empresa . "' OR ";
		}
		$sWhere = substr_replace($sWhere, "AND ef.ruc_empresa = '" . $ruc_empresa . "' ", -3);
		$sWhere .= ')';
	}
	$sWhere .= " order by $ordenado $por";
	//include ("../ajax/pagination.php"); //include pagination file
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
		<div class="panel panel-info">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_factura");'>Fecha</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre");'>Cliente</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_factura");'>Número</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info">Total</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info">Saldo</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado_sri");'>Estado SRI</button></th>
						<th class='text-right'>Opciones</th>
						<input type="hidden" value="<?php echo $page; ?>" id="pagina">
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$id_encabezado_factura = $row['id_encabezado_factura'];
						$fecha_factura = $row['fecha_factura'];
						$serie_factura = $row['serie_factura'];
						$secuencial_factura = $row['secuencial_factura'];
						$nombre_cliente_factura = $row['nombre'];
						$ruc_cliente = $row['ruc'];
						$estado_pago = $row['estado_pago'];
						$tipo_factura = $row['tipo_factura'] == "ELECTRÓNICA" ? "1" : "2";
						$estado_sri = $row['estado_sri'];
						$total_factura = $row['total_factura'];
						$id_cliente = $row['id'];
						$ambiente = $row['ambiente'];
						$mail = $row['email'];
						$estado_mail = $row['estado_mail'];
						$aut_sri = $row['aut_sri'];

						//para consultar si hay notas de credito y descontar del total
						$factura_modificada = $serie_factura . "-" . str_pad($secuencial_factura, 9, "000000000", STR_PAD_LEFT);
						$busca_datos_nc = mysqli_query($con, "SELECT round(sum(total_nc),2) as total_nc FROM encabezado_nc WHERE factura_modificada = '" . $factura_modificada . "' and ruc_empresa= '" . $ruc_empresa . "' ");
						$row_nc = mysqli_fetch_array($busca_datos_nc);
						$total_nc= $row_nc['total_nc'];
						if ($total_nc > 0) {
							$tiene_nc = '<span class="badge" title="'.$total_nc.'">NC</span>';
						} else {
							$tiene_nc = '';
						}

						//estado del pago
						switch ($estado_pago) {
							case "POR COBRAR":
								$label_class = 'label-warning';
								break;
							case "ANULADA":
								$label_class = 'label-danger';
								break;
							case "PAGADA":
								$label_class = 'label-success';
								break;
							case "NOTA CRÉDITO":
								$label_class = 'label-success';
								break;
						}

						//estado sri
						switch ($estado_sri) {
							case "PENDIENTE":
								$label_class_sri = 'label-warning';
								break;
							case "ANULADA":
								$label_class_sri = 'label-danger';
								break;
							case "NO APLICA":
								$label_class_sri = 'label-info';
								break;
							case "AUTORIZADO":
								$label_class_sri = 'label-success';
								break;
							default:
								$label_class_sri = 'label-warning';
						}


						//estado mail
						switch ($estado_mail) {
							case "PENDIENTE":
								$estado_mail_final = 'btn btn-default btn-xs';
								break;
							case "ENVIADO":
								$estado_mail_final = 'btn btn-info btn-xs';
								break;
						}

						$numero_factura = $serie_factura . "-" . str_pad($secuencial_factura, 9, "000000000", STR_PAD_LEFT);
					?>
						<input type="hidden" value="<?php echo $id_cliente; ?>" id="id_cliente<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $nombre_cliente_factura; ?>" id="nombre_cliente<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $ruc_cliente; ?>" id="ruc_cliente<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $aut_sri; ?>" id="aut_sri<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $mail; ?>" id="mail_cliente<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $id_encabezado_factura; ?>" id="id_encabezado_factura<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $serie_factura; ?>" id="serie_factura<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $secuencial_factura; ?>" id="secuencial_factura<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_factura)); ?>" id="fecha_factura<?php echo $id_encabezado_factura; ?>">
						<input type="hidden" value="<?php echo $total_factura ?>" id="total_factura<?php echo $id_encabezado_factura; ?>">
						<tr>
							<td><?php echo date("d/m/Y", strtotime($fecha_factura)); ?></td>
							<td class='col-md-4'><?php echo strtoupper($nombre_cliente_factura); ?></td>
							<td><?php echo $numero_factura; ?> <?php echo $tiene_nc ?></td>
							<td><?php echo number_format($total_factura, 2, '.', ''); ?></td>
							<td><?php echo saldo_factura($con, $ruc_empresa, $id_encabezado_factura); ?></td>
							<td><span class="label <?php echo $label_class_sri; ?>"><?php echo $estado_sri; ?></span></td>

							<td class='col-md-2'><span class="pull-right">

									<?php
									$valor_por_cobrar = saldo_factura($con, $ruc_empresa, $id_encabezado_factura);
									//PARA ENVIAR AL SRI
									if ($tipo_factura == "1") {
										switch ($estado_sri) {
											case "PENDIENTE";
											case "DEVUELTA";
									?>
												<a href="#" class='btn btn-success btn-xs' onclick="enviar_factura_sri('<?php echo $id_encabezado_factura; ?>');" title='Enviar al SRI' data-toggle="modal" data-target="#EnviarDocumentosSri" ><i class="glyphicon glyphicon-send"></i></a>
										<?php
												break;
										}
									}

									//para cuando la factura esta anulada y para descargar
									switch ($estado_sri) {
										case "AUTORIZADO";
										case "ANULADA";
										$status_servidor=status_servidor(); //esto le puse que venga de helpers cuando el servidor no quiere descargar las facturas
										if($status_servidor){
										?>
										<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_factura) ?>&tipo_documento=factura&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Pdf</i> </a>
										<?php
										}else{
											$link_descarga="http://64.225.69.65:8000/facturas_autorizadas/".$ruc_empresa."/".$ruc_cliente."/FAC".$numero_factura.".pdf";
										?>
										<a href="<?php echo $link_descarga;?>" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Pdf</i> </a>
										<?php
										}
										break;
									}


									//PARA editar la factura
									switch ($estado_sri) {
										case "PENDIENTE";
										case "DEVUELTA";
										?>
											<a href="#" class='btn btn-info btn-xs' title='Editar factura' data-toggle="modal" data-target="#factura" onclick="editar_factura('<?php echo $id_encabezado_factura; ?>')"><i class="glyphicon glyphicon-edit"></i> </a>
										<?php
											break;
									}
									//PARA mostrar detalle de la factura
									switch ($estado_sri) {
										case "PENDIENTE";
										case "DEVUELTA";
										case "AUTORIZADO";
										case "ANULADA";
										?>
											<a href="#" class='btn btn-info btn-xs' title='Detalle factura' onclick="detalle_factura('<?php echo $id_encabezado_factura; ?>')" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list"></i> </a>
									<?php
											break;
									}
									switch ($estado_sri) {
										case "PENDIENTE";
										case "AUTORIZADO";
									?>
										<a href="#" class="btn btn-info btn-xs" onclick="carga_modal_registrar_pago('<?php echo $id_encabezado_factura; ?>','<?php echo $valor_por_cobrar; ?>','<?php echo strtoupper($nombre_cliente_factura); ?>','<?php echo $numero_factura; ?>');" title="registrar pago" data-toggle="modal" data-target="#cobroFacturaVenta"><i class="glyphicon glyphicon-usd"></i> </a>
									<?php
									}

									if ($tipo_factura == "1" && $estado_sri == "PENDIENTE") {
									?>
										<a href="#" class='btn btn-danger btn-xs' title='Eliminar factura' onclick="eliminar_factura('<?php echo $id_encabezado_factura; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
									<?php
									}
									//para anular una factura autorizada por el sri
									//if ($tipo_factura == "1" && $estado_sri == "ANULADA") {
										switch ($estado_sri) {
											case "ANULADA";
											case "AUTORIZADO";
										?>
										<a href="#" class='btn btn-warning btn-xs' title='Anular factura' data-toggle="modal" data-target="#AnularDocumentosSri" onclick="pasa_codigo_anular_factura_e('<?php echo $id_encabezado_factura; ?>')"><i class="glyphicon glyphicon-remove"></i> </a>
									<?php
									}
									?>
								</span></td>

						</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="9"><span class="pull-right">
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



// para ver el detalle del pago del documento de venta	
function saldo_factura($con, $ruc_empresa, $id_encabezado_factura)
{

	$detalle_documento = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE id_encabezado_factura = '" . $id_encabezado_factura . "'");
	$row_documento = mysqli_fetch_array($detalle_documento);
	$numero_documento = $row_documento['serie_factura'] . "-" . str_pad($row_documento['secuencial_factura'], 9, "000000000", STR_PAD_LEFT);
	$total_documento = $row_documento['total_factura'];

	$detalle_pagos = mysqli_query($con, "SELECT round(sum(det.valor_ing_egr),2) as ingresos FROM detalle_ingresos_egresos as det WHERE det.codigo_documento_cv = '" . $id_encabezado_factura . "' and det.tipo_documento='INGRESO' and det.estado='OK' group by det.codigo_documento_cv");
	$row_ingresos = mysqli_fetch_array($detalle_pagos);
	$total_ingresos = $row_ingresos['ingresos'];

	//det.ruc_empresa= '" . $ruc_empresa . "' and

	$detalle_nc = mysqli_query($con, "SELECT round(sum(total_nc),2) as total_nc FROM encabezado_nc WHERE factura_modificada = '" . $numero_documento . "' and mid(ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' group by factura_modificada");
	$row_nc = mysqli_fetch_array($detalle_nc);
	$total_nc = $row_nc['total_nc'];

	$detalle_retenciones = mysqli_query($con, "SELECT round(sum(valor_retenido),2) as valor_retenido FROM cuerpo_retencion_venta as cue_ret INNER JOIN encabezado_retencion_venta as enc_ret ON enc_ret.codigo_unico=cue_ret.codigo_unico WHERE enc_ret.numero_documento = '" . str_replace("-", "", $numero_documento) . "' and mid(enc_ret.ruc_empresa,1,12) = '" . substr($ruc_empresa, 0, 12) . "' group by enc_ret.numero_documento");
	$row_retenciones = mysqli_fetch_array($detalle_retenciones);
	$total_retencion = $row_retenciones['valor_retenido'];

	$saldo = number_format(abs($total_documento - $total_ingresos - $total_nc - $total_retencion), 2, '.', '');
	return $saldo;

}


if ($action == 'buscar_detalle_facturas') {
	// escaping, additionally removing everything that could be (html/javascript-) code
	$d = mysqli_real_escape_string($con, (strip_tags($_REQUEST['d'], ENT_QUOTES)));
	$aColumns = array('nombre_producto', 'codigo_producto', 'secuencial_factura', 'tarifa_bp'); //Columnas de busqueda
	$sTable = "cuerpo_factura";
	$sWhere = "WHERE ruc_empresa ='" .  $ruc_empresa . " ' ";
	if ($_GET['d'] != "") {
		$sWhere = "WHERE (ruc_empresa ='" .  $ruc_empresa . " ' AND ";
		for ($i = 0; $i < count($aColumns); $i++) {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $d . "%' and ruc_empresa ='" .  $ruc_empresa . " ' OR ";
		}
		$sWhere = substr_replace($sWhere, "AND ruc_empresa = '" .  $ruc_empresa . "' ", -3);
		$sWhere .= ')';
	}
	$sWhere .= " order by nombre_producto desc";
	//include ("../ajax/pagination.php"); //include pagination file
	//pagination variables
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10; //how much records you want to show
	$adjacents  = 4; //gap between pages after number of adjacents
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
		<div class="panel panel-info">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th>Producto</th>
						<th>Adicional</th>
						<th>Cantidad</th>
						<th>Precio</th>
						<th>Factura</th>
						<th>Cliente</th>
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$nombre_producto = $row['nombre_producto'];
						$cantidad_factura = $row['cantidad_factura'];
						$valor_unitario_factura = $row['valor_unitario_factura'];
						$serie_factura = $row['serie_factura'];
						$secuencial_factura = $row['secuencial_factura'];
						$tarifa_bp = $row['tarifa_bp'] == "0" ? "" : $row['tarifa_bp'];
						//buscar el id cliente en encabezado de facturas	
						$busca_datos_factura = "SELECT * FROM encabezado_factura WHERE ruc_empresa='$ruc_empresa' and serie_factura = '$serie_factura' and secuencial_factura = $secuencial_factura ";
						$result_factura = $con->query($busca_datos_factura);
						$datos_id_cliente = mysqli_fetch_array($result_factura);
						$id_cliente = $datos_id_cliente['id_cliente'];
						//buscar el id cliente en encabezado de facturas	
						$busca_datos_cliente = "SELECT * FROM clientes WHERE id = '" . $id_cliente . "' ";
						$result_cliente = $con->query($busca_datos_cliente);
						$datos_cliente = mysqli_fetch_array($result_cliente);
						$cliente = $datos_cliente['nombre'];

					?>
						<tr>
							<td><?php echo $nombre_producto; ?></td>
							<td><?php echo $tarifa_bp; ?></td>
							<td><?php echo $cantidad_factura; ?></td>
							<td><?php echo $valor_unitario_factura; ?></td>
							<td><?php echo $serie_factura; ?>-<?php echo str_pad($secuencial_factura, 9, "000000000", STR_PAD_LEFT); ?></td>
							<td><?php echo $cliente; ?></td>
						</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="9"><span class="pull-right">
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


if ($action == 'buscar_detalle_adicionales_facturas') {
	// escaping, additionally removing everything that could be (html/javascript-) code
	$a = mysqli_real_escape_string($con, (strip_tags($_REQUEST['a'], ENT_QUOTES)));
	$aColumns = array('adicional_concepto', 'adicional_descripcion'); //Columnas de busqueda
	$sTable = "detalle_adicional_factura ";//as det_adi INNER JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=det_adi.serie_factura and enc_fac.secuencial_factura=det_adi.secuencial_factura
	$sWhere = "WHERE ruc_empresa ='" .  $ruc_empresa . " ' ";
	if ($_GET['a'] != "") {
		$sWhere = "WHERE (ruc_empresa ='" .  $ruc_empresa . " ' AND ";
		for ($i = 0; $i < count($aColumns); $i++) {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $a . "%' and ruc_empresa ='" .  $ruc_empresa . " ' OR ";
		}
		$sWhere = substr_replace($sWhere, "AND ruc_empresa = '" .  $ruc_empresa . "' ", -3);
		$sWhere .= ')';
	}
	$sWhere .= " order by id_detalle desc";
	//include ("../ajax/pagination.php"); //include pagination file
	//pagination variables
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10; //how much records you want to show
	$adjacents  = 4; //gap between pages after number of adjacents
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
		<div class="panel panel-info">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th>Concepto</th>
						<th>Detalle</th>
						<th>Factura</th>
						<th>Cliente</th>
						<th>Detalle</th>
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$adicional_concepto = $row['adicional_concepto'];
						$adicional_descripcion = $row['adicional_descripcion'];
						$serie_factura = $row['serie_factura'];
						$secuencial_factura = $row['secuencial_factura'];
						//buscar el id cliente en encabezado de facturas	
						/*
						$busca_datos_factura = "SELECT * FROM encabezado_factura WHERE ruc_empresa='".$ruc_empresa."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."' ";
						$result_factura = $con->query($busca_datos_factura);
						$datos_id_cliente = mysqli_fetch_array($result_factura);
						$id_cliente = $datos_id_cliente['id_cliente'];
						$id_encabezado_factura = $datos_id_cliente['id_encabezado_factura'];
						*/
						$busca_datos_factura =  mysqli_query($con, "SELECT * FROM encabezado_factura WHERE serie_factura = '".$serie_factura."' and secuencial_factura= '".$secuencial_factura."' and ruc_empresa='".$ruc_empresa."'");
						$row_datos_factura = mysqli_fetch_array($busca_datos_factura);
						$id_cliente = $row_datos_factura['id_cliente'];
						$id_factura = $row_datos_factura['id_encabezado_factura'];
						//buscar el id cliente en encabezado de facturas	
						$busca_datos_cliente = mysqli_query($con, "SELECT * FROM clientes WHERE id = '".$id_cliente."' ");
						$datos_cliente = mysqli_fetch_array($busca_datos_cliente);
						$cliente = $datos_cliente['nombre'];

					?>
						<tr>
							<td><?php echo $adicional_concepto; ?></td>
							<td><?php echo $adicional_descripcion; ?></td>
							<td><?php echo $serie_factura; ?>-<?php echo str_pad($secuencial_factura, 9, "000000000", STR_PAD_LEFT); ?></td>
							<td><?php echo $cliente; ?></td>
							<td><a href="#" class='btn btn-info btn-xs' title='Detalle factura' onclick="detalle_factura('<?php echo $id_factura; ?>')" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list"></i> </a></td>
						</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="9"><span class="pull-right">
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


//duplicar facturas
if ($action == 'duplicar_factura') {
	$id_factura = $_GET['id_factura'];
	$fecha_registro=date("Y-m-d H:i:s");

	$sql_encabezado_factura = mysqli_query($con,"SELECT * FROM encabezado_factura WHERE id_encabezado_factura = '".$id_factura."'");
	$row_ef=mysqli_fetch_array($sql_encabezado_factura);
	$serie=$row_ef['serie_factura'];
	$secuencial=$row_ef['secuencial_factura'];
	$siguiente_numero_factura = $secuencial_electronico->consecutivo_siguiente($con, $ruc_empresa, 'factura', $serie);

	$query_guarda_encabezado_factura = mysqli_query($con, "INSERT INTO encabezado_factura (ruc_empresa, fecha_factura, serie_factura,
	 secuencial_factura, id_cliente, fecha_registro, estado_pago, tipo_factura, estado_sri, total_factura, id_usuario,
	 ambiente, id_registro_contable, estado_mail, propina, tasa_turistica) 
	SELECT ruc_empresa, '".$fecha_registro."', '".$serie."', '".$siguiente_numero_factura."', id_cliente, '".$fecha_registro."',
	 'NINGUNO','ELECTRÓNICA','PENDIENTE', total_factura, '".$id_usuario."', '0', '0','PENDIENTE', propina, tasa_turistica FROM encabezado_factura WHERE id_encabezado_factura = '".$id_factura."'");

	 $query_guarda_detalle_factura = mysqli_query($con, "INSERT INTO cuerpo_factura (ruc_empresa, serie_factura , secuencial_factura , 
	 id_producto, cantidad_factura, valor_unitario_factura, subtotal_factura, tipo_produccion, tarifa_iva, 
	 tarifa_ice, tarifa_bp, descuento, codigo_producto, nombre_producto, id_medida_salida, lote, vencimiento, id_bodega) 
	SELECT ruc_empresa, '".$serie."' , '".$siguiente_numero_factura."', id_producto, cantidad_factura, valor_unitario_factura, 
	subtotal_factura, tipo_produccion, tarifa_iva, tarifa_ice, tarifa_bp, descuento, codigo_producto, nombre_producto, id_medida_salida, lote, vencimiento, id_bodega  
	FROM cuerpo_factura WHERE serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."' and ruc_empresa='".$ruc_empresa."'");

	$query_guarda_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura (ruc_empresa, serie_factura, secuencial_factura, 
	adicional_concepto, adicional_descripcion) 
   SELECT ruc_empresa, '".$serie."' , '".$siguiente_numero_factura."', adicional_concepto, adicional_descripcion
    FROM detalle_adicional_factura WHERE serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."' and ruc_empresa='".$ruc_empresa."'");

	$query_guarda_pagos_factura = mysqli_query($con, "INSERT INTO formas_pago_ventas (ruc_empresa, serie_factura, secuencial_factura, 
	id_forma_pago, valor_pago) 
   SELECT ruc_empresa, '".$serie."' , '".$siguiente_numero_factura."', id_forma_pago, valor_pago
    FROM formas_pago_ventas WHERE serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."' and ruc_empresa='".$ruc_empresa."'");

		echo "<script>
		$.notify('Factura duplicada','success');
		setTimeout(function (){location.href ='../modulos/facturas.php'}, 1000);
		</script>";
	}


	//crear recibo de venta
if ($action == 'recibo_venta') {
	$id_factura = $_GET['id_factura'];
	$fecha_registro=date("Y-m-d H:i:s");

	$sql_encabezado_factura = mysqli_query($con,"SELECT * FROM encabezado_factura WHERE id_encabezado_factura = '".$id_factura."'");
	$row_ef=mysqli_fetch_array($sql_encabezado_factura);
	$serie=$row_ef['serie_factura'];
	$secuencial=$row_ef['secuencial_factura'];

	$sql_impuestos_recibo = mysqli_query($con,"SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' and serie='".$serie."' ");
	$row_impuestos_recibo=mysqli_fetch_array($sql_impuestos_recibo);
	$impuestos_recibo=$row_impuestos_recibo['impuestos_recibo'];

	$siguiente_numero_recibo = $secuencial_electronico->consecutivo_siguiente($con, $ruc_empresa, 'recibo_venta', $serie);

	$query_guarda_encabezado_recibo = mysqli_query($con, "INSERT INTO encabezado_recibo (ruc_empresa, fecha_recibo, serie_recibo,
	secuencial_recibo, id_cliente, fecha_registro, total_recibo, id_usuario, id_registro_contable, propina, tasa_turistica) 
	SELECT ruc_empresa, fecha_factura, '".$serie."', '".$siguiente_numero_recibo."', id_cliente, '".$fecha_registro."', if('".$impuestos_recibo."' = '2', total_factura, (SELECT sum(subtotal_factura - descuento) FROM cuerpo_factura WHERE ruc_empresa='".$ruc_empresa."' and serie_factura='".$serie."'
	  and secuencial_factura='".$secuencial."' group by serie_factura, secuencial_factura, ruc_empresa)) as total_recibo, 
	 '".$id_usuario."', '0', propina, tasa_turistica FROM encabezado_factura WHERE id_encabezado_factura = '".$id_factura."'");
	 $lastid = mysqli_insert_id($con);

	 $query_guarda_detalle_recibo = mysqli_query($con, "INSERT INTO cuerpo_recibo (id_encabezado_recibo, id_producto, cantidad, 
	 valor_unitario, subtotal, tipo_produccion, tarifa_iva, tarifa_ice, adicional, descuento,
	 codigo_producto, nombre_producto, id_medida, lote, vencimiento, id_bodega) 
	SELECT '".$lastid."', id_producto, cantidad_factura, valor_unitario_factura, 
	subtotal_factura, tipo_produccion, tarifa_iva, tarifa_ice, tarifa_bp, descuento, codigo_producto, 
	nombre_producto, id_medida_salida, lote, vencimiento, id_bodega  
	FROM cuerpo_factura WHERE serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."' and ruc_empresa='".$ruc_empresa."'");

	$query_guarda_adicional_recibo = mysqli_query($con, "INSERT INTO detalle_adicional_recibo (id_encabezado_recibo, adicional_concepto, adicional_descripcion) 
   SELECT '".$lastid."', adicional_concepto, adicional_descripcion
    FROM detalle_adicional_factura WHERE serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."' and ruc_empresa='".$ruc_empresa."'");

	eliminar_factura($con, $id_factura, $ruc_empresa, $id_usuario);

		echo "<script>
		$.notify('Recibo de venta creado exitosamente','success');
		setTimeout(function (){location.href ='../modulos/recibo_venta.php'}, 1000);
		</script>";
	}


?>