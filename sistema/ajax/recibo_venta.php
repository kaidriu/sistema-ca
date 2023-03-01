<?php
require_once("../conexiones/conectalogin.php");
require_once("../ajax/pagination.php");
require_once("../helpers/helpers.php");
include("../clases/guardar_recibo_venta.php");
include("../validadores/generador_codigo_unico.php");
include("../clases/secuencial_electronico.php");
$secuencial_electronico = new secuencial_electronico();

$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
ini_set('date.timezone', 'America/Guayaquil');
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';


if ($action == 'agregar_forma_pago_ingreso_recibo') {
	$forma_pago = $_GET["forma_pago"];
	$valor_pago = $_GET["valor_pago"];
	$tipo = $_GET["tipo"];
	$origen = $_GET["origen"];

	$arrayFormaPago = array();
	$arrayDatos = array('id' => rand(5, 500), 'id_forma' => $forma_pago, 'tipo' => $tipo, 'valor' => $valor_pago, 'origen' => $origen);
	if (isset($_SESSION['arrayFormaPagoIngresorecibo'])) {
		$on = true;
		$arrayFormaPago = $_SESSION['arrayFormaPagoIngresorecibo'];
		for ($pr = 0; $pr < count($arrayFormaPago); $pr++) {
			if ($arrayFormaPago[$pr]['id_forma'] == $forma_pago && $origen == $arrayFormaPago[$pr]['origen']) {
				$arrayFormaPago[$pr]['valor'] += $valor_pago;
				$on = false;
			}
		}
		if ($on) {
			array_push($arrayFormaPago, $arrayDatos);
		}
		$_SESSION['arrayFormaPagoIngresorecibo'] = $arrayFormaPago;
	} else {
		array_push($arrayFormaPago, $arrayDatos);
		$_SESSION['arrayFormaPagoIngresorecibo'] = $arrayFormaPago;
	}
	detalle_pago_recibo();
}

function detalle_pago_recibo()
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
					if (isset($_SESSION['arrayFormaPagoIngresorecibo'])) {
						foreach ($_SESSION['arrayFormaPagoIngresorecibo'] as $detalle) {
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
	$arrData = $_SESSION['arrayFormaPagoIngresorecibo'];
	for ($i = 0; $i < count($arrData); $i++) {
		if ($arrData[$i]['id'] == $intid) {
			unset($arrData[$i]);
			echo "<script>
            $.notify('Eliminado','error');
            </script>";
		}
	}
	sort($arrData); //para reordenar el array
	$_SESSION['arrayFormaPagoIngresorecibo'] = $arrData;
	detalle_pago_recibo();
}

if ($action == 'nuevo_recibo') {
	nuevo_recibo($con, $id_usuario);
}

if ($action == 'nuevo_pago_recibo') {
	unset($_SESSION['arrayFormaPagoIngresorecibo']);
}

if ($action == 'guardar_pago_recibo') {
	$id_recibo = $_POST['id_recibo'];
	$fecha_ingreso=date('Y-m-d H:i:s', strtotime($_POST['fecha_ingreso']));
	$fecha_registro=date("Y-m-d H:i:s");
	$saldo_recibo=saldo_recibo($con, $id_recibo);
	$total_formas_pagos = 0;
	$codigo_unico=codigo_unico(20);

	$sql_detalle_recibo = mysqli_query($con,"SELECT * FROM encabezado_recibo as enc INNER JOIN clientes as cli ON cli.id=enc.id_cliente WHERE enc.id_encabezado_recibo = '".$id_recibo."'");
	$row_detalle_recibo=	mysqli_fetch_array($sql_detalle_recibo);
	$nombre_cliente=$row_detalle_recibo['nombre'];
	$fecha_recibo=	date('Y-m-d H:i:s', strtotime($row_detalle_recibo['fecha_recibo']));
	$id_cliente_ingreso=$row_detalle_recibo['id_cliente'];
	$detalle_ingreso=$nombre_cliente." "." Recibo de venta ".$row_detalle_recibo['serie_recibo'] . "-" . str_pad($row_detalle_recibo['secuencial_recibo'], 9, "000000000", STR_PAD_LEFT);

	$arrayValorPago = $_SESSION['arrayFormaPagoIngresorecibo'];
		foreach($arrayValorPago as $valor){
			$total_formas_pagos+=$valor['valor'];
		}

	if($saldo_recibo==0){
		echo "<script>
			$.notify('El saldo del recibo es cero','error');
			</script>";
	}else if (!isset($_SESSION['arrayFormaPagoIngresorecibo'])) {
		echo "<script>
			$.notify('Agregar formas de pago y valores','error');
			</script>";
	}else if($total_formas_pagos > $saldo_recibo ){
		echo "<script>
		$.notify('El total agregado es mayor al saldo pendiente','error');
		</script>";
	}else if(!date($fecha_ingreso)){
		echo "<script>
		$.notify('Ingrese fecha correcta','error');
		</script>";
	}else if($fecha_ingreso < $fecha_recibo){
		echo "<script>
		$.notify('La fecha del ingreso no puede ser menor que la fecha del recibo','error');
		</script>";
	}else{

		$busca_siguiente_ingreso = mysqli_query($con,"SELECT max(numero_ing_egr) as numero FROM ingresos_egresos WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr = 'INGRESO'");
		$row_siguiente_ingreso = mysqli_fetch_array($busca_siguiente_ingreso);
		$numero_ingreso=$row_siguiente_ingreso['numero']+1;

		$query_encabezado_ingreso = mysqli_query($con, "INSERT INTO ingresos_egresos VALUES (null, '".$ruc_empresa."','".$fecha_ingreso."','".$nombre_cliente."','".$numero_ingreso."','".$total_formas_pagos."','INGRESO','".$id_usuario."','".$fecha_registro."','0','".$codigo_unico."','','OK','".$id_cliente_ingreso."')");
		$id_documento="RV".$id_recibo;
		foreach ($_SESSION['arrayFormaPagoIngresorecibo'] as $detalle) {
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
			
		$detalle_ingreso=mysqli_query($con, "INSERT INTO detalle_ingresos_egresos VALUES (NULL, '".$ruc_empresa."', '".$nombre_cliente."', '".$total_formas_pagos."', '".$detalle_ingreso."', '".$numero_ingreso."', 'CCXCRV', 'INGRESO', '".$id_documento."','OK', '".$codigo_unico."')");
//actualizar estado del recibo
		$saldo_recibo = saldo_recibo($con, $id_recibo);
		if($saldo_recibo==0){
		$update_status = mysqli_query($con, "UPDATE encabezado_recibo SET status='3', id_usuario='".$id_usuario."' WHERE id_encabezado_recibo = '" . $id_recibo . "'");
		}

		unset($_SESSION['arrayFormaPagoIngresorecibo']);
		echo "<script>
		$.notify('Ingreso registrado','success');
		setTimeout(function (){location.href ='../modulos/recibo_venta.php'}, 1000);
		</script>";
	}

}

function nuevo_recibo($con, $id_usuario)
{
	$delete_factura_tmp = mysqli_query($con, "DELETE FROM factura_tmp WHERE id_usuario = '" . $id_usuario . "'");
	unset($_SESSION['arrayCliente']);
	unset($_SESSION['arrayInfoAdicional']);
	unset($_SESSION['arrayServicio']);
	unset($_SESSION['arrayTasa']);
	
}

if ($action == 'editar_recibo') {
	$id_recibo = $_POST['id_recibo'];
	$serie_recibo = $_POST['serie_recibo'];
	$secuencial_recibo = $_POST['secuencial_recibo'];
	nuevo_recibo($con, $id_usuario);
	informacion_recibo($con, $id_usuario, $id_recibo);
}

function informacion_recibo($con, $id_usuario, $id_recibo)
{
	//traer informacion y llenar en los arreglos de informacion adicional y clientes
	$info_adicional = mysqli_query($con, "SELECT * FROM detalle_adicional_recibo WHERE id_encabezado_recibo = '" . $id_recibo . "' ");
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

	//traer informacion para llenar el cuerpo de la recibo
	$query_pasa_datos_recibo = mysqli_query($con, "INSERT INTO factura_tmp(id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice, tarifa_botellas, id_usuario, id_bodega, id_medida, lote, vencimiento) 
		SELECT null,id_producto,cantidad,valor_unitario, descuento,tipo_produccion,tarifa_iva,tarifa_ice,adicional,'" . $id_usuario . "',id_bodega,id_medida,lote,vencimiento FROM cuerpo_recibo WHERE id_encabezado_recibo = '" . $id_recibo . "' ");

	//para mostrar tasa y servicio
	$sql_encabezado_recibo =  mysqli_query($con, "SELECT * FROM encabezado_recibo WHERE id_encabezado_recibo = '" . $id_recibo . "' ");
	$row_datos_encabezados = mysqli_fetch_array($sql_encabezado_recibo);
	$propina = $row_datos_encabezados['propina'];
	$tasa = $row_datos_encabezados['tasa_turistica'];
	if ($propina > 0) {
		$_SESSION['arrayServicio']['0']['servicio'] = $propina;
	}
	if ($tasa > 0) {
		$_SESSION['arrayTasa']['0']['tasa'] = $tasa;
	}

}

//para muestrr la informacion del cliente y adicionales
if ($action == 'muestra_cliente_adicionales_editar_recibo') {
	informacion_adicional();
	detalle_informacion_adicional();
	informacion_cliente();
}

if ($action == 'muestra_cuerpo_editar_recibo') {
	$serie_recibo = $_POST['serie_recibo'];
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
}


if ($action == 'muestra_subtotales_editar_recibo') {
	$serie_recibo = $_POST['serie_recibo'];
	subtotales_recibo($con, $ruc_empresa, $serie_recibo, $id_usuario);
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


if ($action == 'subtotales_recibo') {
	$serie_recibo = $_POST['serie_recibo'];
	subtotales_recibo($con, $ruc_empresa, $serie_recibo, $id_usuario);
}


//para agregar un producto a la lista de la recibo cuando lee el codigo de barras
if ($action == 'bar_code') {
	$codigo_producto = $_POST['codigo_producto'];
	$inventario = $_POST['inventario'];
	$id_bodega = $_POST['id_bodega'];
	$serie_recibo = $_POST['serie_recibo'];
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

		//if ($inventario == "NO" && $tipo_producto == "01") {
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

	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
}

function decimales($con, $ruc_empresa, $serie_recibo)
{
	$sql_decimales = mysqli_query($con, "SELECT * FROM sucursales WHERE ruc_empresa = '" . $ruc_empresa . "' and serie = '" . $serie_recibo . "' ");
	$row_decimales = mysqli_fetch_array($sql_decimales);
	$decimal_precio = intval($row_decimales['decimal_doc'] = "" ? "2" : $row_decimales['decimal_doc']);
	$decimal_cant = intval($row_decimales['decimal_cant'] = "" ? "2" : $row_decimales['decimal_cant']);
	$impuestos_recibo = $row_decimales['impuestos_recibo'];
	$array_decimales = array('decimal_precio' => $decimal_precio, 'decimal_cantidad' => $decimal_cant, 'impuestos_recibo'=>$impuestos_recibo);
	return $array_decimales;
}


//agregar item a recibo
if ($action == 'agregar_item_recibo') {
	$id_producto = $_POST['id_producto'];
	$precio = $_POST['precio'];
	$cantidad = $_POST['cantidad'];
	$id_bodega = $_POST['id_bodega'];
	$lote = $_POST['lote'];
	$caducidad = $_POST['caducidad'];
	$id_medida = $_POST['id_medida'];
	$serie_recibo = $_POST['serie_recibo'];
	$decimal_cant = decimales($con, $ruc_empresa, $serie_recibo)['decimal_cantidad'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_recibo)['decimal_precio'];
	$insert_tmp = mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
	SELECT null, '" . $id_producto . "', '" . number_format($cantidad, $decimal_cant, '.', '') . "','" . number_format($precio, $decimal_precio, '.', '') . "','0', tipo_produccion, tarifa_iva , '', '','" . $id_usuario . "', if(tipo_produccion='02', 0,'" . $id_bodega . "'), if(tipo_produccion='02', 0,'" . $id_medida . "'), if(tipo_produccion='02', 0,'" . $lote . "'), if(tipo_produccion='02', 0,'" . $caducidad . "') FROM productos_servicios WHERE id='" . $id_producto . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
}


function detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo)
{
	$decimal_cant = decimales($con, $ruc_empresa, $serie_recibo)['decimal_cantidad'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_recibo)['decimal_precio'];

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

			// PARA MOSTRAR LOS ITEMS DEL recibo
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

				$cantidad = number_format($row['cantidad_tmp'], $decimal_cant, '.', '');
				$precio_venta = number_format($row['precio_tmp'], $decimal_precio, '.', '');
				$descuento = number_format($row['descuento'], 2, '.', '');
				$subtotal = number_format($cantidad * $precio_venta - $descuento, 2, '.', '');
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
						<button type="button" style="height:20px;" class="btn btn-danger btn-xs" title="Eliminar item" onclick="eliminar_item_recibo('<?php echo $id_tmp; ?>')">X</button>
					</td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
<?php
}


function subtotales_recibo($con, $ruc_empresa, $serie_recibo, $id_usuario)
{
	$impuestos_recibo= decimales($con, $ruc_empresa, $serie_recibo)['impuestos_recibo'];

	$sql_subtotales = mysqli_query($con, "SELECT sum(round(((cantidad_tmp * precio_tmp)- descuento),2)) as subtotal, sum(round(descuento,2)) as descuento FROM factura_tmp WHERE id_usuario = '" . $id_usuario . "' group by id_usuario ");
	$row_subtotales = mysqli_fetch_array($sql_subtotales);
	//$row_count = mysqli_num_rows($sql_subtotales);
	$subtotal_general = $row_subtotales['subtotal'];
	$total_descuento = $row_subtotales['descuento'];
?>
<div class="table-responsive">
		<table class="table table-bordered" style="padding: 0px; margin-bottom: 0px;">
		<tr class="info">
				<td class="text-right" style="padding: 2px;">Subtotal general: </td>
				<td class="text-right" style="padding: 2px;"><?php echo number_format($subtotal_general, 2, '.', ''); ?></td>
			</tr>
			<tr class="info">
				<td class="text-right" style="padding: 2px;">Total descuento: </td>
				<td class="text-right" style="padding: 2px;"><?php echo number_format($total_descuento, 2, '.', ''); ?></td>
			</tr>
			<?php
			//PARA MOSTRAR LOS IVAS
			$total_iva = 0;
			$subtotal_iva = 0;
			
			if ($impuestos_recibo=='2'){
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
		}
				
			//para mostrar la casilla de propina dependiendo si esta asignada o no
			$propina = mysqli_query($con, "SELECT * FROM configuracion_facturacion where ruc_empresa ='" . $ruc_empresa . "' and serie_sucursal ='" . $serie_recibo . "'");
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
				<input type="hidden" id="total_recibo" value="<?php echo number_format($subtotal_general + $total_iva+ $servicio + $tasa, 2, '.', ''); ?>">
			</tr>
		</table>
	</div>
<?php
}

if ($action == 'eliminar_item_recibo') {
	$id_tmp = intval($_POST['id']);
	$serie_recibo = $_POST['serie_recibo'];
	$delete = mysqli_query($con, "DELETE FROM factura_tmp WHERE id='" . $id_tmp . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
	echo "<script>
	$.notify('Eliminado','error');
	</script>";
}

//para calcular precio con iva y precio sin iva
if ($action == 'calculo_precio_item') {
	$id_tmp = intval($_POST['id']);
	$serie_recibo = $_POST['serie_recibo'];
	$precio = $_POST['precio'];
	$decimal_precio = decimales($con, $ruc_empresa, $serie_recibo)['decimal_precio'];
	$update = mysqli_query($con, "UPDATE factura_tmp SET precio_tmp='" . number_format($precio, $decimal_precio, '.', '') . "' WHERE id='" . $id_tmp . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
	echo "<script>
	$.notify('Precio actualizado','info');
	</script>";
}

//para agregar informacion adicional en cada item
if ($action == 'info_adicional_item') {
	$info_adicional = strClean($_POST['info_adicional']);
	$id = $_POST['id'];
	$serie_recibo = $_POST['serie_recibo'];

	$update = mysqli_query($con, "UPDATE factura_tmp SET tarifa_botellas='" . $info_adicional . "' WHERE id='" . $id . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
	echo "<script>
	$.notify('Adicional actualizado','info');
	</script>";
}

//para actualizar la cantidad de cada item
if ($action == 'actualiza_cantidad') {
	$cantidad_producto = $_POST['cantidad_producto'];
	$id = $_POST['id'];
	$serie_recibo = $_POST['serie_recibo'];
	$decimal_cant = decimales($con, $ruc_empresa, $serie_recibo)['decimal_cantidad'];

	$update = mysqli_query($con, "UPDATE factura_tmp SET cantidad_tmp='" . number_format($cantidad_producto, $decimal_cant, '.', '') . "' WHERE id='" . $id . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
	echo "<script>
	$.notify('Cantidad actualizada','info');
	</script>";
}

//para actualizar el descuento de cada item
if ($action == 'actualiza_descuento_item') {
	$descuento_item = $_POST['descuento_item'];
	$id = $_POST['id'];
	$serie_recibo = $_POST['serie_recibo'];
	$update = mysqli_query($con, "UPDATE factura_tmp SET descuento= round('" . $descuento_item . "',2) WHERE id='" . $id . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
	echo "<script>
	$.notify('Descuento actualizado','info');
	</script>";
}

//para actualizar el descuento de todos los item
if ($action == 'aplicar_descuento_todos') {
	$porcentaje_descuento = $_POST['porcentaje_descuento'];
	$serie_recibo = $_POST['serie_recibo'];
	$update = mysqli_query($con, "UPDATE factura_tmp SET descuento = round((cantidad_tmp*precio_tmp) * '" . $porcentaje_descuento . "' /100,2) WHERE id_usuario='" . $id_usuario . "'");
	detalle_recibo($con, $id_usuario, $ruc_empresa, $serie_recibo);
	echo "<script>
	$.notify('Descuento aplicado a todos los items','info');
	</script>";
}


if ($action == 'agrega_propina') {
	$propina = $_POST['propina'];
	$serie_recibo = $_POST['serie_recibo'];
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
	subtotales_recibo($con, $ruc_empresa, $serie_recibo, $id_usuario);
	echo "<script>
			$.notify('Servicio agregado','info');
			</script>";
}


if ($action == 'agrega_tasa') {
	$tasa = $_POST['tasa'];
	$serie_recibo = $_POST['serie_recibo'];
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
	subtotales_recibo($con, $ruc_empresa, $serie_recibo, $id_usuario);
	echo "<script>
				$.notify('Tasa agregada','info');
				</script>";
}


//guardar o modificar recibo
if ($action == 'guardar_recibo') {
	$id_recibo = intval($_POST['id_recibo']);
	$id_cliente = $_POST['id_cliente_recibo'];
	$fecha_recibo = date('Y/m/d', strtotime($_POST['fecha_recibo']));
	$serie_recibo = $_POST['serie_recibo'];
	$secuencial_recibo = $_POST['secuencial_recibo'];
	$propina = $_POST['propina'];
	$tasa = $_POST['tasa'];
	$total_recibo = $_POST['suma_recibo'];
	$total_formas_pago = $_POST['total_formas_pago'];
	$codigo_forma_pago = $_POST['codigo_forma_pago'];
	$referencia_salida_inventario = $serie_recibo . "-" . str_pad($secuencial_recibo, 9, "000000000", STR_PAD_LEFT);
	$guia_recibo = "";

	//buscar detalle de recibo
	$sql_recibo_temporal = mysqli_query($con, "SELECT fac_tmp.id_producto as id_producto,
	fac_tmp.cantidad_tmp as cantidad_tmp, fac_tmp.precio_tmp as precio_tmp, fac_tmp.descuento as descuento,
	fac_tmp.tipo_produccion as tipo_produccion, fac_tmp.tarifa_iva as tarifa_iva, fac_tmp.tarifa_ice as tarifa_ice, 
	fac_tmp.tarifa_botellas as tarifa_botellas, fac_tmp.id_usuario as id_usuario, fac_tmp.id_bodega as id_bodega,
	fac_tmp.id_medida as id_medida, fac_tmp.lote as lote, fac_tmp.vencimiento as vencimiento, pro.codigo_producto as codigo_producto,
	pro.nombre_producto as nombre_producto, med.abre_medida as abre_medida, bod.nombre_bodega as nombre_bodega from factura_tmp as fac_tmp LEFT JOIN productos_servicios as pro ON fac_tmp.id_producto=pro.id LEFT JOIN unidad_medida as med ON med.id_medida=fac_tmp.id_medida LEFT JOIN bodega as bod ON bod.id_bodega=fac_tmp.id_bodega WHERE fac_tmp.id_usuario = '" . $id_usuario . "'");
	$count_items = mysqli_num_rows($sql_recibo_temporal);

	if (empty($id_cliente)) {
		echo "<script>
            $.notify('Ingrese cliente','error');
            </script>";
	} else if (empty($fecha_recibo)) {
		echo "<script>
        $.notify('Ingrese fecha de emisión','error');
        </script>";
	} else if (empty($serie_recibo)) {
		echo "<script>
        $.notify('Seleccione una serie','error');
        </script>";
	} else if (empty($secuencial_recibo)) {
		echo "<script>
        $.notify('Seleccione serie','error');
        </script>";
	} else if ($count_items == 0) {
		echo "<script>
        $.notify('Ingrese productos o servicios al recibo','error');
        </script>";
	} else {
		if (empty($id_recibo)) {
			$busca_recibo = mysqli_query($con, "SELECT * FROM encabezado_recibo WHERE ruc_empresa  = '" . $ruc_empresa . "' and serie_recibo = '" . $serie_recibo . "' and secuencial_recibo ='" . $secuencial_recibo . "' ");
			$count_recibo = mysqli_num_rows($busca_recibo);
			if ($count_recibo > 0) {
				echo "<script>
                $.notify('El número de recibo de venta ya existe','error');
                </script>";
			} else {
				//para guardar el encabezado de la recibo
				$id_encabezado_recibo = guardar_encabezado_recibo($con, $ruc_empresa, $fecha_recibo, $serie_recibo, $secuencial_recibo, $id_cliente, $total_recibo, $id_usuario, $propina, $tasa);
				//para guardar la forma de pago de la recibo
				$query_guarda_detalle_adicional_recibo = guarda_adicionales_recibo($con, $id_encabezado_recibo);
				//para guardar el detalle de la recibo 
				$guarda_detalle_recibo = guarda_detalle_recibo($con, $sql_recibo_temporal, $ruc_empresa, $serie_recibo, $referencia_salida_inventario, $fecha_recibo, $id_encabezado_recibo);

				if ($id_encabezado_recibo && $query_guarda_detalle_adicional_recibo && $guarda_detalle_recibo) {
					nuevo_recibo($con, $id_usuario);
					echo "<script>
                $.notify('Recibo de venta guardado','success');
				setTimeout(function (){location.href ='../modulos/recibo_venta.php'}, 1000);
                </script>";
				} else {
					echo "<script>
                $.notify('Revisar información, no se ha guardado el recibo','error');
                </script>";
				}
			}
		} else {
			//modificar el recibo
			$busca_recibo = mysqli_query($con, "SELECT * FROM encabezado_recibo WHERE id_encabezado_recibo != '" . $id_recibo . "' and ruc_empresa = '" . $ruc_empresa . "' and serie_recibo = '" . $serie_recibo . "' and secuencial_recibo ='" . $secuencial_recibo . "' ");
			$count_recibos = mysqli_num_rows($busca_recibo);
			if ($count_recibos > 0) {
				echo "<script>
                $.notify('El recibo ya esta registrado','error');
                </script>";
			} else {
				$delete_encabezado_recibo = mysqli_query($con, "DELETE FROM encabezado_recibo WHERE id_encabezado_recibo= '" . $id_recibo . "' ");
				$delete_cuerpo_recibo = mysqli_query($con, "DELETE FROM cuerpo_recibo WHERE id_encabezado_recibo= '" . $id_recibo . "' ");
				$delete_adicionales_recibo = mysqli_query($con, "DELETE FROM detalle_adicional_recibo WHERE id_encabezado_recibo= '" . $id_recibo . "' ");
				//$delete_registro_inventario = mysqli_query($con, "DELETE FROM inventarios WHERE ruc_empresa = '" . $ruc_empresa . "' and id_documento_venta='" . "RV".$ruc_empresa.$referencia_salida_inventario . "'");
				
				$id_encabezado_recibo = guardar_encabezado_recibo($con, $ruc_empresa, $fecha_recibo, $serie_recibo, $secuencial_recibo, $id_cliente, $total_recibo, $id_usuario, $propina, $tasa);
				$guarda_detalle_recibo = guarda_detalle_recibo($con, $sql_recibo_temporal, $ruc_empresa, $serie_recibo, $referencia_salida_inventario, $fecha_recibo, $id_encabezado_recibo);
				$query_guarda_detalle_adicional_recibo = guarda_adicionales_recibo($con, $id_encabezado_recibo);

				if ($id_encabezado_recibo && $query_guarda_detalle_adicional_recibo && $guarda_detalle_recibo) {
					echo "<script>
                    $.notify('Recibo de venta actualizado','success');
					setTimeout(function (){location.href ='../modulos/recibo_venta.php'}, 1000);
                        </script>";
				} else {
					echo "<script>
					$.notify('Revisar información, no se ha guardado el recibo','error');
                        </script>";
				}
			}
		}
	}
}


function guarda_adicionales_recibo($con, $id_encabezado_recibo)
{
	if (isset($_SESSION['arrayInfoAdicional'])) {
		foreach ($_SESSION['arrayInfoAdicional'] as $detalle) {
			$concepto = $detalle['concepto'];
			$detalle = $detalle['detalle'];
			if(!empty($concepto) && !empty($detalle)){
			$query_guarda_detalle_adicional_recibo = mysqli_query($con, "INSERT INTO detalle_adicional_recibo VALUES (null, '" . $id_encabezado_recibo . "','" . $concepto . "', '" . $detalle . "')");
			}
		}
	}
	if (isset($_SESSION['arrayCliente'])) {
		foreach ($_SESSION['arrayCliente'] as $detalle) {
			$concepto = $detalle['concepto'];
			$detalle = $detalle['detalle'];
			if(!empty($concepto) && !empty($detalle)){
			$query_guarda_detalle_adicional_recibo = mysqli_query($con, "INSERT INTO detalle_adicional_recibo VALUES (null, '" . $id_encabezado_recibo . "','" . $concepto . "', '" . $detalle . "')");
			}
		}
	}
	return ($query_guarda_detalle_adicional_recibo);
}

//PARA ELIMINAR reciboS ECHAS
if ($action == 'anular_recibo') {
	$id_recibo = intval($_POST['id_recibo']);
	anular_recibo($con, $id_recibo, $ruc_empresa, $id_usuario);
}


	function anular_recibo($con, $id_recibo, $ruc_empresa, $id_usuario){
	$fecha_registro=date("Y-m-d H:i:s");
	$busca_datos_recibo = "SELECT ef.id_registro_contable as id_registro_contable, ef.serie_recibo as serie_recibo, ef.secuencial_recibo as secuencial_recibo, ef.fecha_recibo as fecha_recibo, cl.ruc as ruc_cliente, ef.ruc_empresa as ruc_empresa FROM encabezado_recibo ef INNER JOIN clientes as cl ON ef.id_cliente=cl.id WHERE ef.id_encabezado_recibo = '".$id_recibo."' ";
	$result = $con->query($busca_datos_recibo);
	$datos_recibo = mysqli_fetch_array($result);
	$serie_recibo = $datos_recibo['serie_recibo'];
	$secuencial = $datos_recibo['secuencial_recibo'];
	$mes_periodo = date("m", strtotime($datos_recibo['fecha_recibo']));
	$anio_periodo = date("Y", strtotime($datos_recibo['fecha_recibo']));
	$ruc_empresa_periodo = $datos_recibo['ruc_empresa'];
	$ruc_del_cliente = $datos_recibo['ruc_cliente'];
	$id_documento_venta = "RV".$ruc_empresa.$serie_recibo . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
	$referencia = "Recibo de venta: " . $serie_recibo . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
	$referencia_modificada = "Recibo de venta eliminado: " . $serie_recibo . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);

	$id_registro_contable = $datos_recibo['id_registro_contable'];
	$anio_documento = date("Y", strtotime($datos_recibo['fecha_recibo']));
	if($id_registro_contable>0){
	eliminar_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
	}
	//eliminar la recibo y los datos del recibo
	$update_encabezado = mysqli_query($con, "UPDATE encabezado_recibo SET status='2', id_usuario='".$id_usuario."' WHERE id_encabezado_recibo = '" . $id_recibo . "'");
	//$update_inventario = mysqli_query($con, "UPDATE inventarios SET cantidad_salida='0', precio='0', fecha_registro='" . $fecha_registro . "',referencia='" . $referencia_modificada . "',id_usuario='" . $id_usuario . "', fecha_agregado='" . $fecha_registro . "' WHERE ruc_empresa = '" . $ruc_empresa . "' and id_documento_venta='" . $id_documento_venta . "' ");
	
	$codigo_documento_cv="RV".$id_recibo;
	eliminar_ingreso($con, $codigo_documento_cv, $ruc_empresa, $id_usuario);
	
	if ($update_encabezado) {
		echo "<script>
			$.notify('Recibo de venta anulado','success');
			setTimeout(function (){location.href ='../modulos/recibo_venta.php'}, 1000);
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
}


//PARA BUSCAR LAS reciboS
if ($action == 'buscar_recibos') {
	// escaping, additionally removing everything that could be (html/javascript-) code
	$q = mysqli_real_escape_string($con, (strip_tags($_REQUEST['q'], ENT_QUOTES)));
	$ordenado = mysqli_real_escape_string($con, (strip_tags($_GET['ordenado'], ENT_QUOTES)));
	$por = mysqli_real_escape_string($con, (strip_tags($_GET['por'], ENT_QUOTES)));
	$aColumns = array('fecha_recibo', 'secuencial_recibo', 'serie_recibo', 'nombre', 'ruc', 'total_recibo'); //Columnas de busqueda
	$sTable = "encabezado_recibo as ef LEFT JOIN clientes as cl ON cl.id=ef.id_cliente";
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
	$reload = '../recibo_venta.php';
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
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_recibo");'>Fecha</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre");'>Cliente</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_recibo");'>Número</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info">Total</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info">Saldo</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("status");'>Estado</button></th>
						<th class='text-right'>Opciones</th>
						<input type="hidden" value="<?php echo $page; ?>" id="pagina">
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$id_encabezado_recibo = $row['id_encabezado_recibo'];
						$fecha_recibo = $row['fecha_recibo'];
						$serie_recibo = $row['serie_recibo'];
						$secuencial_recibo = $row['secuencial_recibo'];
						$nombre_cliente_recibo = $row['nombre'];
						$ruc_cliente = $row['ruc'];
						$estado = $row['status'];
						$total_recibo = $row['total_recibo'];
						$id_cliente = $row['id'];

						//estado del recibo
						switch ($estado) {
							case "1":
								$label_class = 'label-warning';
								break;
							case "2":
								$label_class = 'label-danger';
								break;
							case "3":
								$label_class = 'label-success';
								break;
							case "4":
								$label_class = 'label-info';
								break;
						}

						switch ($estado) {
							case "1":
								$estado_recibo = 'ABIERTO';
								break;
							case "2":
								$estado_recibo = 'ANULADO';
								break;
							case "3":
								$estado_recibo = 'CERRADO';
								break;
							case "4":
								$estado_recibo = 'FACTURADO';
								break;
						}

						$numero_recibo = $serie_recibo . "-" . str_pad($secuencial_recibo, 9, "000000000", STR_PAD_LEFT);
					?>
						<input type="hidden" value="<?php echo $id_cliente; ?>" id="id_cliente<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo $nombre_cliente_recibo; ?>" id="nombre_cliente<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo $ruc_cliente; ?>" id="ruc_cliente<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo $id_encabezado_recibo; ?>" id="id_encabezado_recibo<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo $serie_recibo; ?>" id="serie_recibo<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo $secuencial_recibo; ?>" id="secuencial_recibo<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_recibo)); ?>" id="fecha_recibo<?php echo $id_encabezado_recibo; ?>">
						<input type="hidden" value="<?php echo $total_recibo ?>" id="total_recibo<?php echo $id_encabezado_recibo; ?>">
						<tr>
							<td><?php echo date("d/m/Y", strtotime($fecha_recibo)); ?></td>
							<td class='col-md-4'><?php echo strtoupper($nombre_cliente_recibo); ?></td>
							<td><?php echo $numero_recibo; ?> <?php echo $tiene_nc ?></td>
							<td><?php echo number_format($total_recibo, 2, '.', ''); ?></td>
							<td><?php echo saldo_recibo($con, $id_encabezado_recibo); ?></td>
							<td><span class="label <?php echo $label_class; ?>"><?php echo $estado_recibo; ?></span></td>

							<td class='col-md-2'><span class="pull-right">

									<?php
									$valor_por_cobrar = saldo_recibo($con, $id_encabezado_recibo);

									//para cuando la recibo esta anulada
									switch ($estado) {
										case "1";
										case "3";
										?>
										<div class="btn-group">									
										<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title='Opciones de pdf'> Pdf <span class="caret"></span></button>
											<ul class="dropdown-menu" style="padding: 1px; border-radius: 2px; margin-top: 2px; text-align:center; ">
												<li><a onmouseover="this.style.color='green';" onmouseout="this.style.color='black';" href="../pdf/pdf_recibo_venta.php?id_documento=<?php echo base64_encode($id_encabezado_recibo) ?>&action=recibo_venta_a2" target="_blank" class='btn btn-default btn-xs' title='Descargar pdf formato A2'><span class='glyphicon glyphicon-list-alt'></span> Pdf A2</i> </a></li>
												<li><a onmouseover="this.style.color='green';" onmouseout="this.style.color='black';" href="../pdf/pdf_recibo_venta.php?id_documento=<?php echo base64_encode($id_encabezado_recibo) ?>&action=recibo_venta_a4" target="_blank" class='btn btn-default btn-xs' title='Descargar pdf formato A4'><span class='glyphicon glyphicon-list-alt'></span> Pdf A4</i> </a></li>
												<li><a onmouseover="this.style.color='green';" onmouseout="this.style.color='black';" href="../pdf/pdf_recibo_venta.php?id_documento=<?php echo base64_encode($id_encabezado_recibo) ?>&action=nota_entrega" target="_blank" class='btn btn-default btn-xs' title='Descargar nota de entrega'><span class='glyphicon glyphicon-list-alt'></span> Nota de entrega</i> </a></li>			
											</ul>
										</div>
										<?php
										break;
									}

									//PARA editar la recibo
									switch ($estado) {
										case "1";
										case "2";
										?>
											<a href="#" class='btn btn-info btn-xs' title='Editar recibo' data-toggle="modal" data-target="#recibo" onclick="editar_recibo('<?php echo $id_encabezado_recibo; ?>')"><i class="glyphicon glyphicon-edit"></i> </a>
										<?php
											break;
									}
									//PARA mostrar detalle de la recibo
									switch ($estado) {
										case "3";
										case "2";
										case "1";
										?>
											<a href="#" class='btn btn-info btn-xs' title='Detalle recibo' onclick="detalle_recibo('<?php echo $id_encabezado_recibo; ?>')" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list"></i> </a>
									<?php
											break;
									}

									if ($estado != "2") {
									?>
										<a href="#" class="btn btn-info btn-xs" onclick="carga_modal_registrar_pago('<?php echo $id_encabezado_recibo; ?>','<?php echo $valor_por_cobrar; ?>','<?php echo strtoupper($nombre_cliente_recibo); ?>','<?php echo $numero_recibo; ?>');" title="registrar pago" data-toggle="modal" data-target="#cobroReciboVenta"><i class="glyphicon glyphicon-usd"></i> </a>
									<?php
									}
									if ($estado != "2") {
										?>
											<a href="#" class='btn btn-danger btn-xs' title='Anular recibo' onclick="anular_recibo('<?php echo $id_encabezado_recibo; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
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
function saldo_recibo($con, $id_encabezado_recibo)
{
	$detalle_documento = mysqli_query($con, "SELECT * FROM encabezado_recibo WHERE id_encabezado_recibo = '" . $id_encabezado_recibo . "'");
	$row_documento = mysqli_fetch_array($detalle_documento);
	$total_documento = $row_documento['total_recibo'];

	$id_encabezado_recibo= "RV".$id_encabezado_recibo;
	$detalle_pagos = mysqli_query($con, "SELECT round(sum(det.valor_ing_egr),2) as ingresos FROM detalle_ingresos_egresos as det WHERE det.codigo_documento_cv = '" . $id_encabezado_recibo . "' and det.tipo_documento='INGRESO' and det.estado='OK' group by det.codigo_documento_cv");
	$row_ingresos = mysqli_fetch_array($detalle_pagos);
	$total_ingresos = $row_ingresos['ingresos'];

	$saldo = number_format(abs($total_documento - $total_ingresos), 2, '.', '');
	return $saldo;

}


	//crear factura
	if ($action == 'generar_factura') {
		$id_recibo = $_GET['id_recibo'];
		$fecha_registro=date("Y-m-d H:i:s");

		$sql_encabezado_factura = mysqli_query($con,"SELECT * FROM encabezado_recibo WHERE id_encabezado_recibo = '".$id_recibo."'");
		$row_ef=mysqli_fetch_array($sql_encabezado_factura);
		$serie=$row_ef['serie_recibo'];
		$propina=$row_ef['propina'];
		$tasa_turistica=$row_ef['tasa_turistica'];

		$siguiente_numero_factura = $secuencial_electronico->consecutivo_siguiente($con, $ruc_empresa, 'factura', $serie);
			
			$sql_subtotal = mysqli_query($con, "select round(sum(subtotal),2) as subtotal FROM cuerpo_recibo WHERE id_encabezado_recibo= '" . $id_recibo . "' group by id_encabezado_recibo");
			$row_subtotal = mysqli_fetch_array($sql_subtotal);
			$subtotal_recibo = number_format($row_subtotal['subtotal'], 2, '.', '');

			$sql_iva = mysqli_query($con, "select round(sum(cr.subtotal * (ti.porcentaje_iva / 100)),2) as iva FROM cuerpo_recibo as cr INNER JOIN 
			tarifa_iva as ti ON ti.codigo = cr.tarifa_iva WHERE cr.id_encabezado_recibo= '" . $id_recibo . "' 
			and ti.porcentaje_iva > 0 group by cr.tarifa_iva ");
			$row_iva = mysqli_fetch_array($sql_iva);
			$total_iva = number_format($row_iva['iva'], 2, '.', '');
			
			$total_recibo=number_format($subtotal_recibo + $total_iva, 2, '.', '');
			
		$query_guarda_encabezado_factura = mysqli_query($con, "INSERT INTO encabezado_factura (ruc_empresa, fecha_factura, serie_factura,
	 secuencial_factura, id_cliente, fecha_registro, estado_pago, tipo_factura, estado_sri, total_factura, id_usuario,
	 ambiente, id_registro_contable, estado_mail, propina, tasa_turistica) 
	SELECT ruc_empresa, '".$fecha_registro."', '".$serie."', '".$siguiente_numero_factura."', id_cliente, '".$fecha_registro."',
	 'NINGUNO','ELECTRÓNICA','PENDIENTE', '".$total_recibo."', '".$id_usuario."', '0', '0','PENDIENTE', propina, tasa_turistica FROM encabezado_recibo WHERE id_encabezado_recibo = '".$id_recibo."'");

	 $query_guarda_detalle_factura = mysqli_query($con, "INSERT INTO cuerpo_factura (ruc_empresa, serie_factura , secuencial_factura , 
	 id_producto, cantidad_factura, valor_unitario_factura, subtotal_factura, tipo_produccion, tarifa_iva, 
	 tarifa_ice, tarifa_bp, descuento, codigo_producto, nombre_producto, id_medida_salida, lote, vencimiento, id_bodega) 
	SELECT '".$ruc_empresa."', '".$serie."' , '".$siguiente_numero_factura."', id_producto, cantidad, valor_unitario, 
	subtotal, tipo_produccion, tarifa_iva, tarifa_ice, adicional, descuento, codigo_producto, nombre_producto, id_medida, lote, vencimiento, id_bodega  
	FROM cuerpo_recibo WHERE id_encabezado_recibo = '".$id_recibo."' ");

	$query_guarda_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura (ruc_empresa, serie_factura, secuencial_factura, 
	adicional_concepto, adicional_descripcion) 
   SELECT '".$ruc_empresa."', '".$serie."' , '".$siguiente_numero_factura."', adicional_concepto, adicional_descripcion
    FROM detalle_adicional_recibo WHERE id_encabezado_recibo = '".$id_recibo."' ");

	$query_guarda_pagos_factura = mysqli_query($con, "INSERT INTO formas_pago_ventas VALUES (null, '".$ruc_empresa."', '".$serie."', '".$siguiente_numero_factura."', '20', '".$total_recibo."')");
   
	anular_recibo($con, $id_recibo, $ruc_empresa, $id_usuario);

			echo "<script>
			$.notify('Factura creada exitosamente','success');
			setTimeout(function (){location.href ='../modulos/facturas.php'}, 1000);
			</script>";
		}
?>