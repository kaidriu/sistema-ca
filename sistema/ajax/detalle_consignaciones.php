<?PHP
include("../conexiones/conectalogin.php");
require_once("../helpers/helpers.php");
session_start();
$con = conenta_login();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];

$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

//para agregar nuevo iten del detalle de consignacion cargada a la nueva factura a generar

if ($action == 'agregar_detalle_facturacion_consignacion_venta') {
	$id_detalle_consignacion = intval($_POST['id']);
	$cantidad = $_POST["cantidad"];
	$precio = $_POST["precio"];
	$descuento = $_POST["descuento"];
	$numero_consignacion = $_POST["numero_consignacion"];
	$serie_factura = $_POST["serie_factura"];

			$detalle_item = mysqli_query($con, "SELECT * FROM detalle_consignacion as det INNER JOIN productos_servicios as pro ON pro.id=det.id_producto WHERE det.id_det_consignacion = '" . $id_detalle_consignacion . "' ");
			$row_detalle = mysqli_fetch_array($detalle_item);
			$id_producto = $row_detalle['id_producto'];
			$lote = $row_detalle['lote'];
		
			$busca_vencimiento = mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '" . $id_producto . "' and lote= '" . $lote . "' and operacion='ENTRADA'");
			$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
			$vencimiento = date('y-m-d', strtotime($row_vencimiento['fecha_vencimiento']));

			$arrayItemFacturar = array();
			$arrayDatos = array('id' => $id_detalle_consignacion, 'numero_consignacion' => $numero_consignacion,
			 'serie_factura' => $serie_factura, 'cantidad' => $cantidad, 'precio' => $precio, 'descuento' => $descuento,
			 'id_producto' => $row_detalle['id_producto'], 'codigo_producto' => $row_detalle['codigo_producto'],
			 'nombre_producto' => $row_detalle['nombre_producto'], 'bodega' => $row_detalle['id_bodega'],
			 'medida' => $row_detalle['id_medida'], 'lote' => $row_detalle['lote'], 'nup' => $row_detalle['nup'],
			'tarifa_iva' => $row_detalle['tarifa_iva'], 'vencimiento'=> $vencimiento);
			if (isset($_SESSION['arrayItemFacturar'])) {
				$on = true;
				$arrayItemFacturar = $_SESSION['arrayItemFacturar'];
				for ($pr = 0; $pr < count($arrayItemFacturar); $pr++) {
					if ($arrayItemFacturar[$pr]['id'] == $id_detalle_consignacion) {
						$arrayItemFacturar[$pr]['cantidad']= $cantidad;
						$arrayItemFacturar[$pr]['precio']= $precio;
						$arrayItemFacturar[$pr]['descuento']= $descuento;
						$on = false;
					}
				}
				if ($on) {
					array_push($arrayItemFacturar, $arrayDatos);
				}
				$_SESSION['arrayItemFacturar'] = $arrayItemFacturar;
				
			} else {
				array_push($arrayItemFacturar, $arrayDatos);
				$_SESSION['arrayItemFacturar'] = $arrayItemFacturar;
			}
}

if ($action == 'items_a_facturar') {
	$numero_cv = $_POST['numero_consignacion'];
	$serie_cv = $_POST['serie_factura'];

	$busca_info_sucursal = mysqli_query($con, "SELECT * FROM sucursales WHERE ruc_empresa = '" . $ruc_empresa . "' and serie = '" . $serie_cv . "' ");
	$info_sucursal = mysqli_fetch_array($busca_info_sucursal);
	$decimal_precio = intval($info_sucursal['decimal_doc']);
	$decimal_cant = intval($info_sucursal['decimal_cant']);

	if (isset($_SESSION['arrayItemFacturar'])) {
	foreach ($_SESSION['arrayItemFacturar'] as $detalle) {
		if ($detalle['cantidad'] > 0 && $detalle['precio'] >= 0) {
			$agregar_consignacion = mysqli_query($con, "INSERT INTO factura_tmp VALUES (null, '" . $detalle['id_producto'] . "', '" . number_format($detalle['cantidad'], $decimal_cant, '.', '') . "', '" . number_format($detalle['precio'], $decimal_precio, '.', '') . "', '" . $detalle['descuento'] . "','1', '" .$detalle['tarifa_iva']. "', '" . $detalle['nup'] . "','" . $detalle['numero_consignacion'] . "','" . $id_usuario . "', '" . $detalle['bodega'] . "','" . $detalle['medida'] . "','" . $detalle['lote'] . "','" . $detalle['vencimiento'] . "')");
		}
	}
	unset($_SESSION['arrayItemFacturar']);
	echo "<script>$.notify('Agregado a factura','success');
		</script>";
	}else{
		echo "<script>$.notify('No hay items con valores para agregar.','error');
		</script>";
	}
	detalle_nueva_factura_consignacion_ventas();
}

//para cuando es nueva devolucion
if ($action == 'nueva_devolucion') {
	unset($_SESSION['arrayItemDevolver']);
}

if ($action == 'agregar_item_a_devolver') {
	$id_detalle_consignacion = intval($_POST['id']);
	$cantidad = $_POST["cantidad"];
	$numero_consignacion = $_POST["numero_consignacion"];
	$serie_factura = $_POST["serie_factura"];

			$detalle_item = mysqli_query($con, "SELECT * FROM detalle_consignacion as det INNER JOIN productos_servicios as pro on pro.id=det.id_producto WHERE det.id_det_consignacion = '" . $id_detalle_consignacion . "' ");
			$row_detalle = mysqli_fetch_array($detalle_item);
			$id_producto = $row_detalle['id_producto'];
			$lote = $row_detalle['lote'];
		
			$busca_vencimiento = mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '" . $id_producto . "' and lote= '" . $lote . "' and operacion='ENTRADA'");
			$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
			$vencimiento = date('y-m-d', strtotime($row_vencimiento['fecha_vencimiento']));

			$arrayItemDevolver = array();
			$arrayDatos = array('id' => $id_detalle_consignacion, 'numero_consignacion' => $numero_consignacion,
			 'serie_factura' => $serie_factura, 'cantidad' => $cantidad, 'id_producto' => $row_detalle['id_producto'], 
			 'codigo_producto' => $row_detalle['codigo_producto'],
			 'nombre_producto' => $row_detalle['nombre_producto'], 'bodega' => $row_detalle['id_bodega'],
			 'medida' => $row_detalle['id_medida'], 'lote' => $row_detalle['lote'], 'nup' => $row_detalle['nup'],
			  'vencimiento'=> $vencimiento);

			  if ($cantidad>0){
				if (isset($_SESSION['arrayItemDevolver'])) {
					$on = true;
					$arrayItemDevolver = $_SESSION['arrayItemDevolver'];
					for ($pr = 0; $pr < count($arrayItemDevolver); $pr++) {
							if ($arrayItemDevolver[$pr]['id'] == $id_detalle_consignacion) {
								unset($arrayItemDevolver[$pr]);
								$on = true;
						}
					}
					if ($on) {
						array_push($arrayItemDevolver, $arrayDatos);
					}
					$_SESSION['arrayItemDevolver'] = $arrayItemDevolver;
				} else {
					array_push($arrayItemDevolver, $arrayDatos);
					$_SESSION['arrayItemDevolver'] = $arrayItemDevolver;
				}
		}
}


//para agregar nuevo detalle a la consignacion de ventas
if ($action == 'agregar_detalle_consignacion_venta') {
	$fecha_agregado = date("Y-m-d H:i:s");
	$id_producto = mysqli_real_escape_string($con, (strip_tags($_GET["id_producto"], ENT_QUOTES)));
	$cantidad_agregar = mysqli_real_escape_string($con, (strip_tags($_GET["cantidad_agregar"], ENT_QUOTES)));
	$nup_agregar = mysqli_real_escape_string($con, (strip_tags($_GET["nup"], ENT_QUOTES)));
	$lote_agregar = mysqli_real_escape_string($con, (strip_tags($_GET["lote_agregar"], ENT_QUOTES)));
	$bodega_agregar = mysqli_real_escape_string($con, (strip_tags($_GET["bodega_agregar"], ENT_QUOTES)));
	$medida_agregar = mysqli_real_escape_string($con, (strip_tags($_GET["medida_agregar"], ENT_QUOTES)));
	$caducidad_agregar = mysqli_real_escape_string($con, (strip_tags($_GET["caducidad_agregar"], ENT_QUOTES)));
	$inventario = mysqli_real_escape_string($con, (strip_tags($_GET["inventario"], ENT_QUOTES)));
	$id_detalle_pedido = isset($_GET["id"])?$_GET["id"]:0;

	$buscar_item_repetido = mysqli_query($con, "SELECT * FROM factura_tmp WHERE id_producto='" . $id_producto . "' and lote='" . $lote_agregar . "' and tarifa_ice='" . $nup_agregar . "' and id_usuario='" . $id_usuario . "'");
	$items_repetidos = mysqli_num_rows($buscar_item_repetido);
	if ($items_repetidos > 0) {
		echo "<script>
		$.notify('Producto ya agregado con este lote y NUP','error');
		</script>";
	} else {
		$agregar_consignacion = mysqli_query($con, "INSERT INTO factura_tmp VALUES (null, '" . $id_producto . "', '" . $cantidad_agregar . "', '0', '0','1', '0', '" . $nup_agregar . "','0','" . $id_usuario . "', '" . $bodega_agregar . "','" . $medida_agregar . "','" . $lote_agregar . "','" . $caducidad_agregar . "')");
		$lastid = mysqli_insert_id($con);
		if($id_detalle_pedido>0){
		add_detalle_pedido_tmp($lastid, $id_detalle_pedido, $cantidad_agregar);
		}
	}
	detalle_nueva_consignacion_venta();
}

//para almacenar los id del pedido y luego guardar las cantidades usadas en la consignacion y conciliar en el pedido
function add_detalle_pedido_tmp($id_item, $id_detalle_pedido, $cantidad) {
	$conciliacion_pedido = array();
    $arrayDatosItems = array();
        $arrayDatosItems = array('id' => $id_item, 'id_detalle' => $id_detalle_pedido, 'cantidad' => $cantidad);
        if (isset($_SESSION['conciliacion_pedido'])) {
            $conciliacion_pedido = $_SESSION['conciliacion_pedido'];
            array_push($conciliacion_pedido, $arrayDatosItems);
            $_SESSION['conciliacion_pedido'] = $conciliacion_pedido;
        } else {
            array_push($conciliacion_pedido, $arrayDatosItems);
            $_SESSION['conciliacion_pedido'] = $conciliacion_pedido;
        }
}

//para editar detalle a la consignacion de ventas
if ($action == 'editar_detalle_consignacion_venta') {
	$codigo_unico = mysqli_real_escape_string($con, (strip_tags($_GET["codigo_unico"], ENT_QUOTES)));
	$agregar_consignacion = mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice, tarifa_botellas, id_usuario, id_bodega, id_medida, lote, vencimiento )
	SELECT null, id_producto, cant_consignacion, precio, descuento,'1', (select tarifa_iva from productos_servicios where id=id_producto ) as tarifa_iva, nup,0,'" . $id_usuario . "', id_bodega, id_medida, lote, vencimiento FROM detalle_consignacion WHERE codigo_unico='" . $codigo_unico . "'");
	detalle_nueva_consignacion_venta();
}

//resetea los datos de la tabla temp de factura tmp
if ($action == 'limpiar_info_entrada') {
	unset($_SESSION['conciliacion_pedido']);//limpia la sesion que tiene los datos del detalle del pedido
	unset($_SESSION['arrayItemFacturar']);
	$limpiar_tabla = mysqli_query($con, "DELETE FROM factura_tmp WHERE id_usuario='" . $id_usuario . "'");
}


//para eliminar la consignacion
if ($action == 'eliminar_consignacion_ventas') {
	$codigo_unico = $_GET['codigo_unico'];
	$consultar_encabezado = mysqli_query($con, "SELECT * FROM encabezado_consignacion WHERE codigo_unico='" . $codigo_unico . "'");
	$row_encabezado = mysqli_fetch_array($consultar_encabezado);
	$numero_consignacion = $row_encabezado['numero_consignacion'];

	$consultar_utilizada = mysqli_query($con, "SELECT * FROM detalle_consignacion WHERE numero_orden_entrada='" . $numero_consignacion . "' and ruc_empresa='" . $ruc_empresa . "'");
	$entradas = mysqli_num_rows($consultar_utilizada);
	if ($entradas == 0) {
		$actualiza_encabezado = mysqli_query($con, "UPDATE encabezado_consignacion SET observaciones='ANULADA' WHERE codigo_unico='" . $codigo_unico . "' ");
		$elimina_detalle_consignacion = mysqli_query($con, "DELETE FROM detalle_consignacion WHERE codigo_unico='" . $codigo_unico . "'");
		//$eliminar_registros_inventario = mysqli_query($con, "DELETE FROM inventarios WHERE id_documento_venta = '" . $codigo_unico . "'");
		echo "<script>
		$.notify('Consignación anulada','success');
		setTimeout(function (){location.href ='../modulos/consignacion_venta.php'}, 1000);
		</script>";
	} else {
		echo "<script>
		$.notify('No es posible eliminar, exiten registros de retornos y/o facturas.','error');
		setTimeout(function (){location.href ='../modulos/consignacion_venta.php'}, 1000);
		</script>";
	}
}

//para eliminar devolucion de la consignacion ventas
if ($action == 'eliminar_devolucion_consignacion_ventas') {
	$codigo_unico = $_GET['codigo_unico'];
	/*
	$sql_encabezado = mysqli_query($con, "SELECT * FROM encabezado_consignacion WHERE codigo_unico='" . $codigo_unico . "'");
	$row_encabezado = mysqli_fetch_array($sql_encabezado);
	$tipo_consignacion = $row_encabezado['tipo_consignacion'];
	$operacion = $row_encabezado['operacion'];
	$serie = $row_encabezado['serie_sucursal'];
	$factura = $row_encabezado['factura_venta'];
	$empresa_ruc = $row_encabezado['ruc_empresa'];
	*/

	//if ($tipo_consignacion == "VENTA" && $operacion == "DEVOLUCIÓN") {
		//$eliminar_registros_inventario = mysqli_query($con, "DELETE FROM inventarios WHERE id_documento_venta = '" . $codigo_unico . "'");
	//}

	$actualiza_encabezado = mysqli_query($con, "UPDATE encabezado_consignacion SET observaciones='ANULADA' WHERE codigo_unico='" . $codigo_unico . "'");
	$elimina_detalle_consignacion = mysqli_query($con, "DELETE FROM detalle_consignacion WHERE codigo_unico='" . $codigo_unico . "'");
	echo "<script>
		$.notify('Registro anulado','success');
		setTimeout(function (){location.href ='../modulos/devolucion_consignacion_venta.php'}, 1000);
		</script>";
}

//para eliminar factura de la consignacion ventas
if ($action == 'eliminar_factura_consignacion_venta') {
	$codigo_unico = $_GET['codigo_unico'];
	$sql_encabezado = mysqli_query($con, "SELECT * FROM encabezado_consignacion WHERE codigo_unico='" . $codigo_unico . "' ");
	$row_encabezado = mysqli_fetch_array($sql_encabezado);
	$tipo_consignacion = $row_encabezado['tipo_consignacion'];
	$operacion = $row_encabezado['operacion'];
	$serie = $row_encabezado['serie_sucursal'];
	$factura = $row_encabezado['factura_venta'];
	$empresa_ruc = $row_encabezado['ruc_empresa'];
	$factura_venta = $row_encabezado['factura_venta'];
	$observaciones = $row_encabezado['observaciones'];
	if ($factura_venta == "") {
		echo "<script>
		$.notify('$observaciones','error');
		</script>";
		exit;
	}

	if ($tipo_consignacion == "VENTA" && $operacion == "FACTURA") {
		$sql_factura = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE serie_factura='" . $serie . "' and secuencial_factura='" . $factura . "' and ruc_empresa='" . $empresa_ruc . "'");
		$row_factura = mysqli_fetch_array($sql_factura);
		$estado_sri = $row_factura['estado_sri'];
		if ($estado_sri == "AUTORIZADO") {
			echo "<script>
		$.notify('Primero debe anular la factura en el SRI y luego en el sistema.','error');
		</script>";
			exit;
		}
		if ($estado_sri == "PENDIENTE") {
			$eliminar_encabezado_factura = mysqli_query($con, "DELETE FROM encabezado_factura WHERE ruc_empresa = '" . $empresa_ruc . "' and serie_factura='" . $serie . "' and secuencial_factura='" . $factura . "'");
			$delete_detalle_factura = mysqli_query($con, "DELETE FROM cuerpo_factura WHERE ruc_empresa = '" . $empresa_ruc . "' and serie_factura='" . $serie . "' and secuencial_factura='" . $factura . "'");
			$delete_pago_factura = mysqli_query($con, "DELETE FROM formas_pago_ventas WHERE ruc_empresa = '" . $empresa_ruc . "' and serie_factura='" . $serie . "' and secuencial_factura='" . $factura . "'");
			$delete_adicional_factura = mysqli_query($con, "DELETE FROM detalle_adicional_factura WHERE ruc_empresa = '" . $empresa_ruc . "' and serie_factura='" . $serie . "' and secuencial_factura='" . $factura . "'");
			echo "<script>
			$.notify('Factura eliminada.','success')
			</script>";
		}
	}

	$actualiza_encabezado = mysqli_query($con, "UPDATE encabezado_consignacion SET observaciones='ANULADA' WHERE codigo_unico='" . $codigo_unico . "' ");
	$elimina_detalle_consignacion = mysqli_query($con, "DELETE FROM detalle_consignacion WHERE codigo_unico='" . $codigo_unico . "'");
	echo "<script>
		$.notify('Registro anulado','success');
		setTimeout(function (){location.href ='../modulos/facturacion_consignacion_venta.php'}, 1000);
		</script>";
}


//eliminar detalle de la consignacion nueva que se esta generando
if ($action == 'eliminar_item') {
	$id_registro = $_GET['id_registro'];
	$elimina_detalle_factura_tmp = mysqli_query($con, "DELETE FROM factura_tmp WHERE id='" . $id_registro . "'");
		if (isset($_SESSION['conciliacion_pedido'])){
			eliminar_detalle_pedido_tmp($id_registro);
		}
	detalle_nueva_consignacion_venta();
}

//para eliminar los items del detalle del pedido que se almacenan en una sesion y luego se concilian en la tabla pedidos
function eliminar_detalle_pedido_tmp($id){
    $intid = $id;
    $arrData = $_SESSION['conciliacion_pedido'];
    for ($i = 0; $i < count($arrData); $i++) {
        if ($arrData[$i]['id'] == $intid) {
            unset($arrData[$i]);
        }
    }
    sort($arrData); //para reordenar el array
    $_SESSION['conciliacion_pedido'] = $arrData;
}

if ($action == 'eliminar_item_factura_consignacion') {
	$id_registro = $_GET['id_registro'];
	$elimina_detalle_factura_tmp = mysqli_query($con, "DELETE FROM factura_tmp WHERE id='" . $id_registro . "'");
	detalle_nueva_factura_consignacion_ventas();
}

if ($action == 'detalle_consignacion') {
	$codigo_unico = $_GET['codigo_unico'];
	detalle_consignacion($codigo_unico);
}

if ($action == 'detalle_factura') {
	$codigo_unico = $_GET['codigo_unico'];
	detalle_factura($codigo_unico);
}

if ($action == 'mostrar_detalle_devolucion_consignacion') {
	$codigo_unico = $_GET['codigo_unico'];
	detalle_devolucion_consignacion($codigo_unico);
}

if ($action == 'muestra_detalle_consignacion_para_devolucion') {
	$numero_cv = $_GET['numero_cv'];
	detalle_consignacion_para_devolucion($numero_cv);
}

if ($action == 'muestra_detalle_consignacion_para_facturacion') {
	$numero_cv = $_GET['numero_consignacion'];
	$serie_cv = $_GET['serie_factura'];
	detalle_consignacion_para_facturacion($numero_cv, $serie_cv);
}

//muestra el detalle de la consignacion que se ingresa y queremos facturar
function detalle_consignacion_para_facturacion($numero_cv, $serie_cv)
{
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$busca_codigo_unico = mysqli_query($con, "SELECT * FROM encabezado_consignacion enc_con INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id WHERE enc_con.numero_consignacion = '" . $numero_cv . "' and enc_con.ruc_empresa='" . $ruc_empresa . "' ");
	$encabezado_consignacion = mysqli_fetch_array($busca_codigo_unico);
	$codigo_unico = $encabezado_consignacion['codigo_unico'];
	$busca_consignacion = mysqli_query($con, "SELECT * FROM detalle_consignacion WHERE codigo_unico = '" . $codigo_unico . "' ");
?>
	<div style="padding: 1px; margin-bottom: 2px; margin-top: -10px;" class="alert alert-info" role="alert">
		<b>No. CV:</b> <?php echo $numero_cv; ?> <b>Consignado a: </b><?php echo $encabezado_consignacion['nombre']; ?>
	</div>
	<div class="panel panel-info" style="height: 280px; overflow-y: auto; margin-bottom: 5px;">
	<div class="table-responsive">
		<table class="table table-hover">
			<tr class="info">
				<th style="padding: 2px;">N</th>
				<th style="padding: 2px;">Código</th>
				<th style="padding: 2px;">Producto</th>
				<th style="padding: 2px;">Saldo</th>
				<th style="padding: 2px;">Lote</th>
				<th style="padding: 2px;">NUP</th>
				<th style="padding: 2px;">Precio</th>
				<th style="padding: 2px;">Cantidad</th>
				<th style="padding: 2px;">Descuento</th>
				<th style="padding: 2px;">Subtotal</th>
			</tr>
			<?php
			$contador=0;
			while ($detalle = mysqli_fetch_array($busca_consignacion)) {
				$id_det_consignacion = $detalle['id_det_consignacion'];
				$codigo_producto = $detalle['codigo_producto'];
				$id_producto = $detalle['id_producto'];
				$nombre_producto = $detalle['nombre_producto'];
				$nup = $detalle['nup'];
				$lote = $detalle['lote'];
				//buscar precio
				$busca_precio = mysqli_query($con, "SELECT * FROM productos_servicios WHERE id = '" . $id_producto . "' ");
				$row_precio = mysqli_fetch_array($busca_precio);
				$precio = $row_precio['precio_producto'];
				
				//busca saldo temp
				$busca_saldo_tmp = mysqli_query($con, "SELECT sum(cantidad_tmp) as suma FROM factura_tmp WHERE id_usuario = '" . $id_usuario . "' and id_producto = '" . $id_producto . "' and tarifa_ice='" . $nup . "' and tarifa_botellas='" . $numero_cv . "'");
				$saldo_producto_tmp = mysqli_fetch_array($busca_saldo_tmp);
				$cantidad_tmp = $saldo_producto_tmp['suma'];
				//buscar entradas
				$busca_entradas = mysqli_query($con, "SELECT sum(det.cant_consignacion) as entradas FROM encabezado_consignacion as enc INNER JOIN detalle_consignacion as det ON enc.codigo_unico=det.codigo_unico WHERE det.id_producto = '" . $id_producto . "' and enc.numero_consignacion='" . $numero_cv . "' and det.nup='" . $nup . "' and enc.ruc_empresa='" . $ruc_empresa . "' and enc.tipo_consignacion='VENTA' and enc.operacion = 'ENTRADA' and det.lote='" . $lote . "'");
				$row_entradas = mysqli_fetch_array($busca_entradas);
				$entradas = $row_entradas['entradas'];
				//buscar salidas
				$busca_salidas = mysqli_query($con, "SELECT sum(det.cant_consignacion) as salidas FROM encabezado_consignacion as enc INNER JOIN detalle_consignacion as det ON enc.codigo_unico=det.codigo_unico WHERE det.id_producto = '" . $id_producto . "' and det.numero_orden_entrada='" . $numero_cv . "' and det.nup='" . $nup . "' and enc.ruc_empresa='" . $ruc_empresa . "' and enc.tipo_consignacion='VENTA' and enc.operacion != 'ENTRADA' and det.lote='" . $lote . "'");
				$row_salidas = mysqli_fetch_array($busca_salidas);
				$saldo = number_format($entradas - $row_salidas['salidas'] - $cantidad_tmp, 4, '.', '');
				$contador = $contador+1;
			?>
				<tr>
					<input type="hidden" name="serie_cv" value="<?php echo $serie_cv; ?>">
					<input type="hidden" name="numero_cv" value="<?php echo $numero_cv; ?>">
					<input type="hidden" id="saldo<?php echo $id_det_consignacion; ?>" value="<?php echo $saldo; ?>">
					<td style="padding: 2px;"><?php echo $contador; ?></td>
					<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
					<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
					<td style="padding: 2px;"><?php echo $saldo; ?></td>
					<td style="padding: 2px;"><?php echo $lote; ?></td>
					<td style="padding: 2px;"><?php echo $nup; ?></td>
					<td style="padding: 2px;" class="col-sm-1"><input type="text" class="form-control input-sm text-right" name="precio[<?php echo $id_det_consignacion; ?>]" id="precio<?php echo $id_det_consignacion; ?>" onchange="precio_facturacion('<?php echo $id_det_consignacion; ?>');" value="<?php echo $precio; ?>"></td>
					<td style="padding: 2px;" class="col-sm-1"><input type="text" class="form-control input-sm text-right" name="cantidad[<?php echo $id_det_consignacion; ?>]" id="cantidad<?php echo $id_det_consignacion; ?>" onchange="cantidad_facturacion('<?php echo $id_det_consignacion; ?>');"></td>
					<td style="padding: 2px;" class="col-sm-1"><input type="text" class="form-control input-sm text-right" name="descuento[<?php echo $id_det_consignacion; ?>]" id="descuento<?php echo $id_det_consignacion; ?>" onchange="descuento_facturacion('<?php echo $id_det_consignacion; ?>');"></td>
					<td style="padding: 2px;" class="col-sm-1"><input type="text" class="form-control input-sm text-right" name="subtotal[<?php echo $id_det_consignacion; ?>]" id="subtotal<?php echo $id_det_consignacion; ?>" readonly></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
	</div>
<?php
}

function detalle_consignacion_para_devolucion($numero_cv)
{
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$busca_codigo_unico = mysqli_query($con, "SELECT * FROM encabezado_consignacion enc_con INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id 
	WHERE enc_con.numero_consignacion = '" . $numero_cv . "' and enc_con.ruc_empresa='" . $ruc_empresa . "' ");
	$encabezado_consignacion = mysqli_fetch_array($busca_codigo_unico);
	$codigo_unico = $encabezado_consignacion['codigo_unico'];
	$busca_consignacion = mysqli_query($con, "SELECT * FROM detalle_consignacion WHERE codigo_unico = '" . $codigo_unico . "' ");
?>
	<div style="padding: 1px; margin-bottom: 2px; margin-top: -10px;" class="alert alert-info" role="alert">
		<b>Cliente: </b><?php echo $encabezado_consignacion['nombre']; ?>
	</div>
	<div class="panel panel-info" style="height: 300px; overflow-y: auto; margin-bottom: 5px;">
	<div class="table-responsive">
		<table class="table table-hover">
			<tr class="info">
				<th style="padding: 2px;">Código</th>
				<th style="padding: 2px;">Producto</th>
				<th style="padding: 2px;">Saldo</th>
				<th style="padding: 2px;">Lote</th>
				<th style="padding: 2px;">NUP</th>
				<th style="padding: 2px;">Dev.</th>

			</tr>
			<?php
			while ($detalle = mysqli_fetch_array($busca_consignacion)) {
				$id_det_consignacion = $detalle['id_det_consignacion'];
				$codigo_producto = $detalle['codigo_producto'];
				$id_producto = $detalle['id_producto'];
				$nombre_producto = $detalle['nombre_producto'];
				$nup = $detalle['nup'];
				$lote = $detalle['lote'];
				//buscar salidas
				$busca_entradas = mysqli_query($con, "SELECT sum(det.cant_consignacion) as entradas FROM encabezado_consignacion as enc INNER JOIN detalle_consignacion as det ON enc.codigo_unico=det.codigo_unico WHERE det.id_producto = '" . $id_producto . "' and enc.numero_consignacion='" . $numero_cv . "' and det.nup='" . $nup . "' and enc.ruc_empresa='" . $ruc_empresa . "' and enc.tipo_consignacion='VENTA' and enc.operacion = 'ENTRADA' and det.lote='" . $lote . "'");
				$row_entradas = mysqli_fetch_array($busca_entradas);
				$entradas = $row_entradas['entradas'];
				//buscar salidas
				$busca_salidas = mysqli_query($con, "SELECT sum(det.cant_consignacion) as salidas FROM encabezado_consignacion as enc INNER JOIN detalle_consignacion as det ON enc.codigo_unico=det.codigo_unico WHERE det.id_producto = '" . $id_producto . "' and det.numero_orden_entrada='" . $numero_cv . "' and det.nup='" . $nup . "' and enc.ruc_empresa='" . $ruc_empresa . "' and enc.tipo_consignacion='VENTA' and enc.operacion != 'ENTRADA' and det.lote='" . $lote . "'");
				$row_salidas = mysqli_fetch_array($busca_salidas);
				$saldo = number_format($entradas - $row_salidas['salidas'], 4, '.', '');
			?>
				<tr>
					<input type="hidden" id="saldo<?php echo $id_det_consignacion; ?>" value="<?php echo $saldo; ?>">
					<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
					<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
					<td style="padding: 2px;"><?php echo $saldo; ?></td>
					<td style="padding: 2px;"><?php echo $lote; ?></td>
					<td style="padding: 2px;"><?php echo $nup; ?></td>
					<td style="padding: 2px;" class="col-sm-2"><input type="text" class="form-control input-sm" name="devolucion[<?php echo $id_det_consignacion; ?>]" id="devolucion<?php echo $id_det_consignacion; ?>" onchange="cantidad_devolucion('<?php echo $id_det_consignacion; ?>');"></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
	</div>
<?php
}

function detalle_nueva_factura_consignacion_ventas()
{
	$con = conenta_login();
	$id_usuario = $_SESSION['id_usuario'];
	$busca_detalle = mysqli_query($con, "SELECT fat_tmp.descuento as descuento, 
	fat_tmp.id_producto as id_producto, fat_tmp.precio_tmp as precio_tmp, 
	fat_tmp.id as id_tmp, fat_tmp.tarifa_ice as nup, pro_ser.codigo_producto as codigo_producto, 
	fat_tmp.cantidad_tmp as cantidad, pro_ser.nombre_producto as nombre_producto, uni_med.abre_medida as medida, 
	fat_tmp.tarifa_botellas as num_con, fat_tmp.lote as lote, bod.nombre_bodega as bodega 
	FROM factura_tmp as fat_tmp INNER JOIN productos_servicios as pro_ser ON fat_tmp.id_producto = pro_ser.id INNER JOIN 
	unidad_medida as uni_med ON fat_tmp.id_medida=uni_med.id_medida 
	INNER JOIN bodega as bod ON fat_tmp.id_bodega=bod.id_bodega WHERE fat_tmp.id_usuario = '" . $id_usuario . "' ");
?>
	<div class="panel panel-info" style="height: 280px; overflow-y: auto; margin-bottom: 5px;">
	<div class="table-responsive">
		<table class="table table-hover">
			<tr class="info">
				<th style="padding: 2px;">No.CV</th>
				<th style="padding: 2px;">Código</th>
				<th style="padding: 2px;">Producto</th>
				<th style="padding: 2px;">Cant</th>
				<th style="padding: 2px;">Bodega</th>
				<th style="padding: 2px;">Lote</th>
				<th style="padding: 2px;">Nup</th>
				<th style="padding: 2px;">Precio</th>
				<th style="padding: 2px;">Descuento</th>
				<th style="padding: 2px;">Subtotal</th>
				<th style="padding: 2px;">Vencimiento</th>
				<th style="padding: 2px;" class='text-right'>Eliminar</th>
			</tr>
			<?php
			$subtotal_general = 0;
			$total_descuento = 0;
			while ($detalle = mysqli_fetch_array($busca_detalle)) {
				$id_detalle = $detalle['id_tmp'];
				$id_producto = $detalle['id_producto'];
				$codigo_producto = $detalle['codigo_producto'];
				$nombre_producto = $detalle['nombre_producto'];
				$cantidad = $detalle['cantidad'];
				$numero_consignacion = $detalle['num_con'];
				$bodega = $detalle['bodega'];
				$lote = $detalle['lote'];
				$nup = $detalle['nup'];
				$precio = $detalle['precio_tmp'];
				$descuento = $detalle['descuento'];
				$total_descuento += $descuento;
				//buscar salidas
				$busca_vencimiento = mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '" . $id_producto . "' and lote='" . $lote . "' and operacion='ENTRADA'");
				$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
				$vencimiento = date('d-m-Y', strtotime($row_vencimiento['fecha_vencimiento']));
				$subtotal_general += $cantidad * $precio;
			?>
				<tr>
					<td style="padding: 2px;"><?php echo $numero_consignacion; ?></td>
					<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
					<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
					<td style="padding: 2px;"><?php echo $cantidad; ?></td>
					<td style="padding: 2px;"><?php echo $bodega; ?></td>
					<td style="padding: 2px;"><?php echo $lote; ?></td>
					<td style="padding: 2px;"><?php echo $nup; ?></td>
					<td style="padding: 2px;"><?php echo number_format($precio, 4, '.', ''); ?></td>
					<td style="padding: 2px;"><?php echo number_format($descuento, 4, '.', ''); ?></td>
					<td style="padding: 2px;"><?php echo number_format($cantidad * $precio - $descuento, 2, '.', ''); ?></td>
					<td style="padding: 2px;"><?php echo $vencimiento; ?></td>
					<td style="padding: 2px;" class='text-right'><a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_item_factura_consignacion('<?php echo $id_detalle; ?>')"><i class="glyphicon glyphicon-remove"></i></a></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
	</div>
	<div class="row" style="margin-bottom: -20px; margin-top: -5px; height: 10%">
		<div class="col-xs-6">
		</div>
		<div class="col-xs-6">
		<!--<div class="panel-group" id="accordion">
				<div class="panel panel-info">
					<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><span class="caret"></span> Detalle de subtotales</a>
					<div id="collapse1" class="panel-collapse">
						-->
						<div class="panel panel-info">
							<div class="table-responsive">
								<table class="table">
									<tr class="info">
										<td style="padding: 2px;" class='text-right'>SUBTOTAL GENERAL: </td>
										<td style="padding: 2px;" class='text-center'><?php echo number_format($subtotal_general, 2, '.', ''); ?></td>
										<td style="padding: 2px;"></td>
										<td style="padding: 2px;"></td>
									</tr>
									<?php
									//PARA MOSTRAR LOS NOMBRES DE CADA TARIFA DE IVA Y LOS VALORES DE CADA SUBTOTAL
									$subtotal_tarifa_iva = 0;
									$sql = mysqli_query($con, "select ti.tarifa as tarifa, sum(round(ft.cantidad_tmp * ft.precio_tmp - ft.descuento,2)) as suma_tarifa_iva FROM factura_tmp as ft INNER JOIN tarifa_iva as ti ON ti.codigo = ft.tarifa_iva WHERE ft.id_usuario= '" . $id_usuario . "' group by ft.tarifa_iva ");
									while ($row = mysqli_fetch_array($sql)) {
										$nombre_tarifa_iva = strtoupper($row["tarifa"]);
										$subtotal_tarifa_iva = number_format($row['suma_tarifa_iva'], 2, '.', '');
									?>
										<tr class="info">
											<td style="padding: 2px;" class='text-right'>SUBTOTAL <?php echo ($nombre_tarifa_iva); ?>:</td>
											<td style="padding: 2px;" class='text-center'><?php echo number_format($subtotal_tarifa_iva, 2, '.', ''); ?></td>
											<td style="padding: 2px;"></td>
											<td style="padding: 2px;"></td>
										</tr>

									<?php
									}
									?>
									<tr class="info">
										<td style="padding: 2px;" class='text-right'>TOTAL DESCUENTO: </td>
										<td style="padding: 2px;" class='text-center'><?php echo number_format($total_descuento, 2, '.', ''); ?></td>
										<td style="padding: 2px;"></td>
										<td style="padding: 2px;"></td>
									</tr>
									<?php
									//PARA MOSTRAR LOS IVAS
									$total_iva = 0;
									$subtotal_porcentaje_iva = 0;
									$sql = mysqli_query($con, "select ti.tarifa as tarifa, (sum(ft.cantidad_tmp * ft.precio_tmp - ft.descuento) * ti.tarifa /100)  as porcentaje FROM factura_tmp as ft INNER JOIN tarifa_iva as ti ON ti.codigo = ft.tarifa_iva WHERE ft.id_usuario= '" . $id_usuario . "' and ti.tarifa > 0 group by ft.tarifa_iva ");
									while ($row = mysqli_fetch_array($sql)) {
										$nombre_porcentaje_iva = strtoupper($row["tarifa"]);
										$porcentaje_iva = $row['porcentaje'];
										$subtotal_porcentaje_iva = $porcentaje_iva;
										$total_iva += $subtotal_porcentaje_iva;
									?>
										<tr class="info">
											<td style="padding: 2px;" class='text-right'>IVA <?php echo ($nombre_porcentaje_iva); ?>:</td>
											<td style="padding: 2px;" class='text-center'><?php echo number_format($subtotal_porcentaje_iva, 2, '.', ''); ?></td>
											<td style="padding: 2px;"></td>
											<td style="padding: 2px;"></td>
										</tr>
									<?php
									}
									?>
									<tr class="info">
										<td style="padding: 2px;" class='text-right'>TOTAL: </td>
										<td style="padding: 2px;" class='text-center'><?php echo number_format($subtotal_general + $total_iva - $total_descuento, 2, '.', ''); ?></td>
										<td style="padding: 2px;"></td>
										<td style="padding: 2px;"></td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<!--
				</div>
			</div>
		</div>
								-->


	<?php
}

function detalle_nueva_consignacion_venta()
{
	$con = conenta_login();
	$id_usuario = $_SESSION['id_usuario'];
	$busca_detalle = mysqli_query($con, "SELECT fat_tmp.tarifa_ice as nup, fat_tmp.id as id_tmp, pro_ser.codigo_producto as codigo_producto, fat_tmp.cantidad_tmp as cantidad, pro_ser.nombre_producto as nombre_producto, uni_med.abre_medida as medida, bod.nombre_bodega as bodega, fat_tmp.vencimiento as vencimiento, fat_tmp.lote as lote FROM factura_tmp as fat_tmp INNER JOIN productos_servicios as pro_ser ON fat_tmp.id_producto = pro_ser.id INNER JOIN bodega as bod ON fat_tmp.id_bodega=bod.id_bodega INNER JOIN unidad_medida as uni_med ON fat_tmp.id_medida=uni_med.id_medida WHERE fat_tmp.id_usuario = '" . $id_usuario . "' ");
	?>
		<div class="panel panel-info" style="height: 300px; overflow-y: auto; margin-bottom: 5px;">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th style="padding: 2px;">Código</th>
						<th style="padding: 2px;">Producto</th>
						<th style="padding: 2px;">Cant</th>
						<th style="padding: 2px;">Bodega</th>
						<th style="padding: 2px;">Lote</th>
						<th style="padding: 2px;">Nup</th>
						<th style="padding: 2px;" class='text-right'>Eliminar</th>
					</tr>
					<?php
					while ($detalle = mysqli_fetch_array($busca_detalle)) {
						$id_detalle = $detalle['id_tmp'];
						$codigo_producto = $detalle['codigo_producto'];
						$nombre_producto = $detalle['nombre_producto'];
						$cantidad = $detalle['cantidad'];
						$bodega = $detalle['bodega'];
						$lote = $detalle['lote'];
						$nup = $detalle['nup'];
						$vencimiento = date('d-m-Y', strtotime($detalle['vencimiento']));
					?>
						<tr>
							<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
							<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
							<td style="padding: 2px;"><?php echo $cantidad; ?></td>
							<td style="padding: 2px;"><?php echo $bodega; ?></td>
							<td style="padding: 2px;"><?php echo $lote; ?></td>
							<td style="padding: 2px;"><?php echo $nup; ?></td>
							<td style="padding: 2px;" class='text-right'><a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_detalle_consignacion('<?php echo $id_detalle; ?>')"><i class="glyphicon glyphicon-remove"></i></a></td>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	<?php
}

function detalle_consignacion($codigo_unico)
{
	$con = conenta_login();
	$busca_encabezado = mysqli_query($con, "SELECT * FROM encabezado_consignacion as enc_con INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id WHERE enc_con.codigo_unico = '" . $codigo_unico . "' ");
	$encabezado_consignacion = mysqli_fetch_array($busca_encabezado);
	$busca_detalle = mysqli_query($con, "SELECT * FROM detalle_consignacion as det_con INNER JOIN bodega as bod ON det_con.id_bodega=bod.id_bodega INNER JOIN unidad_medida as uni_med ON det_con.id_medida=uni_med.id_medida WHERE det_con.codigo_unico = '" . $codigo_unico . "' ");
	?>
	<div style="padding: 1px; margin-bottom: 2px; margin-top: -10px;" class="alert alert-info" role="alert">
		<b>No:</b> <?php echo $encabezado_consignacion['numero_consignacion']; ?> <b>Fecha:</b> <?php echo date("d/m/Y", strtotime($encabezado_consignacion['fecha_consignacion'])); ?> <b>Cliente: </b><?php echo $encabezado_consignacion['nombre']; ?>
		<b>Punto salida: </b><?php echo $encabezado_consignacion['punto_partida']; ?> <b>Punto llegada: </b><?php echo $encabezado_consignacion['punto_llegada']; ?> <b>Responsable: </b><?php echo $encabezado_consignacion['responsable']; ?>
		<b>Observaciones: </b><?php echo $encabezado_consignacion['observaciones']; ?>
	</div>
				
		<div class="panel panel-info" style="height: 400px; overflow-y: auto; margin-bottom: 5px;">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr class="info">
						<th style="padding: 2px;">Código</th>
						<th style="padding: 2px;">Producto</th>
						<th style="padding: 2px;">Cant</th>
						<th style="padding: 2px;">Bodega</th>
						<th style="padding: 2px;">Lote</th>
						<th style="padding: 2px;">NUP</th>
						<th style="padding: 2px;">Caducidad</th>
					</tr>
					<?php
					$total_consignaciones = 0;
					while ($detalle = mysqli_fetch_array($busca_detalle)) {
						$codigo_producto = $detalle['codigo_producto'];
						$id_producto = $detalle['id_producto'];
						$nombre_producto = $detalle['nombre_producto'];
						$cantidad = $detalle['cant_consignacion'];
						$total_consignaciones += $cantidad;
						$bodega = $detalle['nombre_bodega'];
						$lote = $detalle['lote'];
						$nup = $detalle['nup'];
						$ncv = $detalle['numero_orden_entrada'];
						$busca_vencimiento = mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '" . $id_producto . "' and lote= '" . $lote . "' and operacion='ENTRADA'");
						$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
						$vencimiento = date('d-m-Y', strtotime($row_vencimiento['fecha_vencimiento']));
					?>
						<tr>
							<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
							<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
							<td style="padding: 2px;"><?php echo number_format($cantidad, 0, '.', '') ?></td>
							<td style="padding: 2px;"><?php echo $bodega; ?></td>
							<td style="padding: 2px;"><?php echo $lote; ?></td>
							<td style="padding: 2px;"><?php echo $nup; ?></td>
							<td style="padding: 2px;"><?php echo $vencimiento; ?></td>
						</tr>
					<?php
					}
					?>
					<tr>
						<td style="padding: 2px;" colspan="2" class="text-right">Total productos consignados:</td>
						<td style="padding: 2px;"><?php echo $total_consignaciones; ?></td>
						<td style="padding: 2px;" colspan="4"></td>
					</tr>
				</table>
			</div>
		</div>

	<?php
}

function detalle_factura($codigo_unico)
{
	$con = conenta_login();
	$busca_encabezado = mysqli_query($con, "SELECT * FROM encabezado_consignacion as enc_con INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id WHERE enc_con.codigo_unico = '" . $codigo_unico . "' ");
	$encabezado_consignacion = mysqli_fetch_array($busca_encabezado);
	$busca_detalle = mysqli_query($con, "SELECT * FROM detalle_consignacion as det_con INNER JOIN bodega as bod ON det_con.id_bodega=bod.id_bodega INNER JOIN unidad_medida as uni_med ON det_con.id_medida=uni_med.id_medida WHERE det_con.codigo_unico = '" . $codigo_unico . "' ");
	?>
		<div style="padding: 1px; margin-bottom: 2px; margin-top: -10px;" class="alert alert-info" role="alert">
			<b>No:</b> <?php echo $encabezado_consignacion['numero_consignacion']; ?> <b>Fecha:</b> <?php echo date("d/m/Y", strtotime($encabezado_consignacion['fecha_consignacion'])); ?> <b>Cliente: </b><?php echo $encabezado_consignacion['nombre']; ?>
			<b>Punto salida: </b><?php echo $encabezado_consignacion['punto_partida']; ?> <b>Punto llegada: </b><?php echo $encabezado_consignacion['punto_llegada']; ?> <b>Responsable: </b><?php echo $encabezado_consignacion['responsable']; ?>
			<b>Observaciones: </b><?php echo $encabezado_consignacion['observaciones']; ?>
		</div>
		<div class="panel panel-info" style="height: 400px; overflow-y: auto; margin-bottom: 5px;">
		<div class="table-responsive">
			<table class="table table-hover">
				<tr class="info">
					<th style="padding: 2px;">Código</th>
					<th style="padding: 2px;">Producto</th>
					<th style="padding: 2px;">Cant</th>
					<th style="padding: 2px;">Bodega</th>
					<th style="padding: 2px;">Lote</th>
					<th style="padding: 2px;">NUP</th>
					<th style="padding: 2px;">Caducidad</th>
					<th style="padding: 2px;">No. CV</th>
				</tr>
				<?php
				while ($detalle = mysqli_fetch_array($busca_detalle)) {
					$codigo_producto = $detalle['codigo_producto'];
					$id_producto = $detalle['id_producto'];
					$nombre_producto = $detalle['nombre_producto'];
					$cantidad = $detalle['cant_consignacion'];
					$bodega = $detalle['nombre_bodega'];
					$lote = $detalle['lote'];
					$nup = $detalle['nup'];
					$ncv = $detalle['numero_orden_entrada'];
					$busca_vencimiento = mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '" . $id_producto . "' and lote= '" . $lote . "' and operacion='ENTRADA'");
					$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
					$vencimiento = date('d-m-Y', strtotime($row_vencimiento['fecha_vencimiento']));
				?>
					<tr>
						<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
						<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
						<td style="padding: 2px;"><?php echo number_format($cantidad, 4, '.', '') ?></td>
						<td style="padding: 2px;"><?php echo $bodega; ?></td>
						<td style="padding: 2px;"><?php echo $lote; ?></td>
						<td style="padding: 2px;"><?php echo $nup; ?></td>
						<td style="padding: 2px;"><?php echo $vencimiento; ?></td>
						<td style="padding: 2px;"><?php echo $ncv; ?></td>
					</tr>
				<?php
				}
				?>
			</table>
		</div>
		</div>
	<?php
}

function detalle_devolucion_consignacion($codigo_unico)
{
	$con = conenta_login();
	$busca_encabezado = mysqli_query($con, "SELECT * FROM encabezado_consignacion as enc_con INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id WHERE enc_con.codigo_unico = '" . $codigo_unico . "' ");
	$encabezado_consignacion = mysqli_fetch_array($busca_encabezado);
	$busca_detalle = mysqli_query($con, "SELECT * FROM detalle_consignacion as det_con INNER JOIN bodega as bod ON det_con.id_bodega=bod.id_bodega INNER JOIN unidad_medida as uni_med ON det_con.id_medida=uni_med.id_medida WHERE det_con.codigo_unico = '" . $codigo_unico . "' ");
	if ($encabezado_consignacion['operacion'] == "FACTURA") {
		$numero_factura = " No." . $encabezado_consignacion['serie_sucursal'] . "-" . str_pad($encabezado_consignacion['factura_venta'], 9, "000000000", STR_PAD_LEFT);
	} else {
		$numero_factura = "";
	}
	?>
		<div style="padding: 1px; margin-bottom: 2px; margin-top: -10px;" class="alert alert-info" role="alert">
			<b>No:</b> <?php echo $encabezado_consignacion['numero_consignacion']; ?> <b>Fecha:</b> <?php echo date("d/m/Y", strtotime($encabezado_consignacion['fecha_consignacion'])); ?> <b>Cliente: </b><?php echo $encabezado_consignacion['nombre']; ?>
			<b>Tipo: </b><?php echo 'Retorno' . $numero_factura; ?> <b>Observaciones: </b><?php echo $encabezado_consignacion['observaciones']; ?>	
		</div>
		
		<div class="panel panel-info" style="height: 400px; overflow-y: auto; margin-bottom: 5px;">
		<div class="table-responsive">
			<table class="table table-hover">
				<tr class="info">
					<th style="padding: 2px;">No. CV</th>
					<th style="padding: 2px;">Código</th>
					<th style="padding: 2px;">Producto</th>
					<th style="padding: 2px;">Cant</th>
					<th style="padding: 2px;">Bodega</th>
					<th style="padding: 2px;">Lote</th>
					<th style="padding: 2px;">nup</th>
					<th style="padding: 2px;">Caducidad</th>
				</tr>
				<?php
				while ($detalle = mysqli_fetch_array($busca_detalle)) {
					$codigo_producto = $detalle['codigo_producto'];
					$id_producto = $detalle['id_producto'];
					$nombre_producto = $detalle['nombre_producto'];
					$cantidad = $detalle['cant_consignacion'];
					$bodega = $detalle['nombre_bodega'];
					$lote = $detalle['lote'];
					$nup = $detalle['nup'];
					$busca_vencimiento = mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '" . $id_producto . "' and lote= '" . $lote . "' and operacion='ENTRADA'");
					$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
					$vencimiento = date('d-m-Y', strtotime($row_vencimiento['fecha_vencimiento']));
					$numero_orden_entrada = $detalle['numero_orden_entrada'];
				?>
					<tr>
						<td style="padding: 2px;"><?php echo $numero_orden_entrada; ?></td>
						<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
						<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
						<td style="padding: 2px;"><?php echo number_format($cantidad, 4, '.', '') ?></td>
						<td style="padding: 2px;"><?php echo $bodega; ?></td>
						<td style="padding: 2px;"><?php echo $lote; ?></td>
						<td style="padding: 2px;"><?php echo $nup; ?></td>
						<td style="padding: 2px;"><?php echo $vencimiento; ?></td>

					</tr>
				<?php
				}
				?>
			</table>
		</div>
		</div>
	<?php
}
	?>