<?php
/* Connect To Database*/
require_once("../conexiones/conectalogin.php");
require_once("../helpers/helpers.php");
include("../ajax/pagination.php"); //include pagination file
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';


if ($action == 'buscar_consignacion_venta') {
	$q = mysqli_real_escape_string($con, (strip_tags($_REQUEST['q'], ENT_QUOTES)));
	$ordenado = mysqli_real_escape_string($con, (strip_tags($_GET['ordenado'], ENT_QUOTES)));
	$por = mysqli_real_escape_string($con, (strip_tags($_GET['por'], ENT_QUOTES)));
	$aColumns = array('fecha_consignacion', 'numero_consignacion', 'observaciones', 'numero_consignacion', 'nombre'); //Columnas de busqueda
	$sTable = "encabezado_consignacion as enc_con INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id";
	$sWhere = "WHERE enc_con.ruc_empresa ='" . $ruc_empresa . " ' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' ";
	if ($_GET['q'] != "") {
		$sWhere = "WHERE (enc_con.ruc_empresa ='" . $ruc_empresa . " ' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' AND ";

		for ($i = 0; $i < count($aColumns); $i++) {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $q . "%' AND enc_con.ruc_empresa ='" . $ruc_empresa . " ' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' OR ";
		}

		$sWhere = substr_replace($sWhere, "AND enc_con.ruc_empresa ='" . $ruc_empresa . " ' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' ", -3);
		$sWhere .= ')';
	}
	$sWhere .= " order by $ordenado $por";

	//pagination variables
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 20; //how much records you want to show
	$adjacents  = 4; //gap between pages after number of adjacents
	$offset = ($page - 1) * $per_page;
	//Count the total number of row in your table*/
	$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
	$row = mysqli_fetch_array($count_query);
	$numrows = $row['numrows'];
	$total_pages = ceil($numrows / $per_page);
	$reload = '../consignacion_venta.php';
	//main query to fetch the data
	$sql = "SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
	$query = mysqli_query($con, $sql);
	//loop through fetched data
	if ($numrows > 0) {
?>
		<div class="table-responsive">
			<div class="panel panel-info">
				<table class="table table-hover">
					<tr class="info">
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_consignacion");'>Fecha</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("id_cli_pro");'>Cliente</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_consignacion");'>Número</button></th>
						<th style="padding: 0px;"><button style="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("observaciones");'>Observaciones</button></th>
						<th class="text-right">Opciones</th>
					</tr>
					<?php

					while ($row = mysqli_fetch_array($query)) {
						$id_consignacion = $row['id_consignacion'];
						$fecha_consignacion = date('d-m-Y', strtotime($row['fecha_consignacion']));
						$cliente = strtoupper($row['nombre']);
						$numero = $row['numero_consignacion'];
						$id_cliente = $row['id_cli_pro'];
						$observaciones = $row['observaciones'];
						$codigo_unico = $row['codigo_unico'];
						$operacion = $row['operacion'];
						$punto_partida = $row['punto_partida'];
						$punto_llegada = $row['punto_llegada'];
						$responsable = $row['responsable'];
						$serie = $row['serie_sucursal'];
						$fecha_entrega=date('d-m-Y', strtotime($row['fecha_entrega']));
						$hora_entrega=date('H:i', strtotime($row['hora_entrega']));
						$traslado_por=$row['traslado_por'];
					?>
						<input type="hidden" value="<?php echo $fecha_consignacion; ?>" id="mod_fecha_consignacion<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $cliente; ?>" id="mod_nombre_cliente<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $id_cliente; ?>" id="mod_id_cliente<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $codigo_unico; ?>" id="mod_codigo_unico<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $punto_partida; ?>" id="mod_punto_partida<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $punto_llegada; ?>" id="mod_punto_llegada<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $responsable; ?>" id="mod_responsable<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $serie; ?>" id="mod_serie<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $observaciones; ?>" id="mod_observaciones<?php echo $id_consignacion; ?>">
						<input type="hidden" value="<?php echo $fecha_entrega;?>" id="mod_fecha_entrega<?php echo $id_consignacion;?>">	
						<input type="hidden" value="<?php echo $hora_entrega;?>" id="mod_hora_entrega<?php echo $id_consignacion;?>">	
						<input type="hidden" value="<?php echo $traslado_por;?>" id="mod_traslado_por<?php echo $id_consignacion;?>">	
						<tr>
							<td><?php echo $fecha_consignacion; ?></td>
							<td class="col-md-4"><?php echo strtoupper($cliente); ?></td>
							<td><?php echo $numero ?></td>
							<td class="col-md-4"><?php echo strtoupper($observaciones); ?></td>
							<td class='text-right'>
								<a title='Imprimir pdf' href="../pdf/pdf_consignacion_ventas.php?action=consignacion_ventas&codigo_unico=<?php echo $codigo_unico ?>" class='btn btn-default btn-xs' title='Pdf' target="_blank">Pdf</a>
								<a href="#" class='btn btn-info btn-xs' title='Editar consignación' onclick="obtener_datos('<?php echo $id_consignacion; ?>');" data-toggle="modal" data-target="#nueva_consignacion_venta"><i class="glyphicon glyphicon-edit"></i></a>
								<a href="#" class='btn btn-info btn-xs' title='Detalle consignación' onclick="mostrar_detalle_consignacion('<?php echo $codigo_unico; ?>');" data-toggle="modal" data-target="#detalleConsignacion"><i class="glyphicon glyphicon-list"></i></a>
								<a href="#" class='btn btn-danger btn-xs' title='Eliminar consignación' onclick="eliminar_consignacion_ventas('<?php echo $codigo_unico; ?>');"><i class="glyphicon glyphicon-trash"></i></a>
							</td>
						</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="6"><span class="pull-right">
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

//para eliminar una consignacion en venta
if ($action == 'eliminar_consignacion_venta') {
	if (!empty($_GET['id_entrada'])) {
		$id_entrada = mysqli_real_escape_string($con, (strip_tags($_GET["id_entrada"], ENT_QUOTES)));
		//buscar ese producto para saber si ya hay salidas y si hay mas salidas que entradas no se puede eliminar		
		$busca_datos_producto = "SELECT * FROM inventarios WHERE ruc_empresa='" . $ruc_empresa . "' and id_inventario='" . $id_entrada . "'";
		$result_datos_producto = $con->query($busca_datos_producto);
		$datos_producto = mysqli_fetch_array($result_datos_producto);
		$codigo_producto = $datos_producto['codigo_producto'];
		$nombre_producto = $datos_producto['nombre_producto'];
		$id_bodega = $datos_producto['id_bodega'];
		$cantidad_entrada = $datos_producto['cantidad_entrada'];
		$tipo_operacion = $datos_producto['operacion'];
		$id_registro_compra = $datos_producto['id_documento_venta'];
		$id_producto = $datos_producto['id_producto'];
		$tipo_registro = $datos_producto['tipo_registro'];
		$codigo_registro = $datos_producto['id_documento_venta'];
		//contar salidas de este producto

		include_once("../clases/saldo_producto_y_conversion.php");
		$saldo_producto_factura = new saldo_producto_y_conversion();
		$saldo_final = $saldo_producto_factura->existencias_productos($id_bodega, $id_producto, $con);


		if ($saldo_final >= $cantidad_entrada) {
			if ($tipo_operacion == 'ENTRADA') {
				$sql_actualiza_saldo_compra = mysqli_query($con, "UPDATE cuerpo_compra SET cantidad_inv=cantidad_inv-'" . $cantidad_entrada . "' WHERE id_cuerpo_compra='" . $id_registro_compra . "'");
			}

			if ($tipo_registro == "T") {
				if ($delete_uno = mysqli_query($con, "DELETE FROM inventarios WHERE id_documento_venta = '" . $codigo_registro . "'")) {
					echo "<script>
			$.notify('Todos los registros relacionados a la transferencia, han sido eliminados.','success');
			setTimeout(function (){location.reload()}, 1000);
			</script>";
				} else {
					$errors[] = "Lo siento algo ha salido mal intenta nuevamente." . mysqli_error($con);
				}
			} else {
				if ($delete_dos = mysqli_query($con, "DELETE FROM inventarios WHERE id_inventario = '" . $id_entrada . "'")) {
					echo "<script>
			$.notify('La entrada ha sido eliminada satisfactoriamente.','success');
			setTimeout(function (){location.reload()}, 1000);
			</script>";
				} else {
					$errors[] = "Lo siento algo ha salido mal intenta nuevamente." . mysqli_error($con);
				}
			}
		} else {
			$errors[] = "No es posible eliminar la entrada, hay más salidas registradas de este producto." . mysqli_error($con);
		}
	} else {
		$errors[] = "Algo ha salido mal intente de nuevo." . mysqli_error($con);
	}
}


//para mostrar el detalle del pedido en la consignacion y agregar a la consignacion
if ($action == 'detalle_pedido') {
	$pedido = intval($_GET['pedido']);
	$bodega = intval($_GET['bodega']);
	$consulta_pedido = mysqli_query($con, "SELECT 
	det.id as id, enc.numero_pedido as numero_pedido,
	det.id_producto as id_producto,
	det.codigo_producto as codigo_producto,
	det.producto as producto,
	det.cantidad as cantidad,
	det.despachado as despachado,
	pro.id_unidad_medida as medida,
	enc.id_cliente as id_cliente, 
	cli.nombre as cliente, 
	enc.observaciones as observaciones, enc.hora_entrega as hora_entrega,
	enc.fecha_entrega as fecha_entrega, enc.responsable as responsable
	FROM detalle_pedido as det 
	INNER JOIN encabezado_pedido as enc ON enc.id=det.id_pedido 
	INNER JOIN productos_servicios as pro ON pro.id=det.id_producto 
	INNER JOIN clientes as cli ON cli.id=enc.id_cliente
	WHERE numero_pedido='".$pedido."' and enc.ruc_empresa= '".$ruc_empresa."' and enc.status !=3");
	?>
	<div class="panel panel-info">
	<div class="table-responsive">
		<table class="table table-hover">
			<tr class="info">
				<th style="padding: 2px;">Código</th>
				<th style="padding: 2px;">Producto</th>
				<th style="padding: 2px;" title="Cantidad del pedido">Pedido</th>
				<th style="padding: 2px;" title="Cantidad agregada a la consignación">Agregado</th>
				<th style="padding: 2px;" class="text-center">lote</th>
				<th style="padding: 2px;" class="text-right">Cant</th>
				<th style="padding: 2px;" class="text-center">NUP</th>
				<th style="padding: 2px;">Existencia</th>
				<th style="padding: 2px;" class='text-right'>Agregar</th>
			</tr>
			<?php
			while ($detalle = mysqli_fetch_array($consulta_pedido)) {
				$id_detalle = $detalle['id'];
				$id_cliente = $detalle['id_cliente'];
				$cliente = $detalle['cliente'];
				$numero_pedido = $detalle['numero_pedido'];
				$id_producto = $detalle['id_producto'];
				$codigo_producto = $detalle['codigo_producto'];
				$nombre_producto = $detalle['producto'];
				$cantidad = $detalle['cantidad'];
				$despachado = $detalle['despachado'];
				$medida = $detalle['medida'];
				$observaciones = $detalle['observaciones'];
				$hora_entrega = date('H:i', strtotime($detalle['hora_entrega']));
				$fecha_entrega = date('d-m-Y', strtotime($detalle['fecha_entrega']));
				$responsable = $detalle['responsable'];
				
				$consulta_agregado = mysqli_query($con, "SELECT sum(cantidad_tmp) as agregado FROM factura_tmp WHERE id_producto='".$id_producto."' and id_usuario= '".$id_usuario."' group by id_producto");
				$row_agregado = mysqli_fetch_array($consulta_agregado);
				$agregado = $row_agregado['agregado'];
				if($agregado>($cantidad-$despachado)){
				$agregado = '<span class="label label-danger">'.number_format($agregado, 0, '.', '').'</span>';
				}else{
					$agregado = number_format($agregado, 0, '.', '');
				}

			?>
			<input type="hidden" value="<?php echo $hora_entrega; ?>" id="hora_entrega<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $fecha_entrega; ?>" id="fecha_entrega<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $responsable; ?>" id="responsable<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $observaciones; ?>" id="observaciones_pedido<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $numero_pedido; ?>" id="numero_pedido<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $id_producto; ?>" id="id_producto_pedido<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $medida; ?>" id="id_medida_pedido<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $id_cliente; ?>" id="id_cliente_pedido<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo $cliente; ?>" id="nombre_cliente_pedido<?php echo $id_detalle; ?>">
			<input type="hidden" value="<?php echo number_format($cantidad-$despachado, 2, '.', ''); ?>" id="saldo_entrante<?php echo $id_detalle; ?>">
				<tr>
					<td style="padding: 2px;"><?php echo $codigo_producto; ?></td>
					<td style="padding: 2px;"><?php echo $nombre_producto; ?></td>
					<td style="padding: 2px;"><?php echo number_format($cantidad-$despachado, 0, '.', ''); ?></td>
					<td style="padding: 2px;"><?php echo $agregado; ?></td>
					<td style="padding: 2px;" class="col-xs-1">
					<select class="form-control" style="text-align:right; width: auto; height:30px;" title="Seleccione lote" name="lote_pedido" id="lote_pedido<?php echo $id_detalle; ?>" onChange="saldo_producto_pedido('<?php  echo $id_detalle;?>');">
					<option value="0" selected>Seleccione</option>
						<?php
						$lotes_encontrados=lotes($id_producto, $bodega, $con, $ruc_empresa, $id_usuario);
						foreach ($lotes_encontrados as $lotes) {
						?>
							<option value="<?php echo $lotes['lote']; ?>"><?php echo $lotes['lote']; ?></option>
						<?php
						}
						?>
					</select>
					</td>
					<td style="padding: 2px;" class="col-xs-1">
						<input type="text" class="form-control input-sm" style="text-align:right;" title="Ingrese cantidad" name="cantidad_pedido" id="cantidad_pedido<?php echo $id_detalle; ?>" placeholder="Cant" value="1">
					</td>
					<td style="padding: 2px;" class="col-xs-1">
						<div class="pull-right">
							<input type="text" class="form-control input-sm" style="text-align:right;" title="Ingrese número único de producto" name="nup_pedido" id="nup_pedido<?php echo $id_detalle; ?>" placeholder="nup">
						</div>
					</td>
					<td style="padding: 2px;" class="col-xs-1">
						<input type="text" style="text-align:right;" class="form-control input-sm" name="existencia_pedido" id="existencia_pedido<?php echo $id_detalle; ?>" readonly>
					</td>
					<td style="padding: 2px;" class="text-right"><a href="#" class="btn btn-info btn-xs" title="Agregar" onclick="agregar_item_pedido('<?php echo $id_detalle; ?>')"><i class="glyphicon glyphicon-plus"></i></a></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>
	</div>
<?php
}


function lotes($id_producto, $bodega, $con, $ruc_empresa, $id_usuario){
include_once("../clases/saldo_producto_y_conversion.php");
$saldo_producto = new saldo_producto_y_conversion();
//borrar las existencias temporales
$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."';");
$query_guarda_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
SELECT null, id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_vencimiento,ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida),lote, '".$id_usuario."'  FROM inventarios WHERE ruc_empresa ='". $ruc_empresa ."' and id_producto='".$id_producto."' and id_bodega='".$bodega."' group by lote");
//selet para buscar linea por linea y ver si la medida es igual al producto o sino modificar esa linea
	$resultado= array();
	$sql_filas = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."' and cantidad_salida>0 ");
	while ($row_temporales = mysqli_fetch_array($sql_filas)){
	$id_producto=$row_temporales["id_producto"];
	$codigo_producto=$row_temporales["codigo_producto"];
	$nombre_producto=$row_temporales["nombre_producto"];
		//obtener medida del producto
		$sql_medida_producto = mysqli_query($con,"SELECT * FROM productos_servicios WHERE id = '".$id_producto."'");
		$row_producto = mysqli_fetch_array($sql_medida_producto);
		$id_medida_salida= $row_producto['id_unidad_medida'];
		$id_medida_entrada=$row_temporales["id_medida"];
		$cantidad_entrada_tmp = $row_temporales['cantidad_entrada'];
		$id_bodega = $row_temporales['id_bodega'];
		$caducidad = $row_temporales['fecha_caducidad'];
		$lote = $row_temporales['lote'];
	
		if ($id_medida_entrada != $id_medida_salida){
		$id_tmp=$row_temporales["id_existencia_tmp"];
		$cantidad_a_transformar = $row_temporales['cantidad_salida'];
		$total_saldo_producto= $saldo_producto->conversion($id_medida_entrada, $id_medida_salida, $id_producto, '0', $cantidad_a_transformar, $con, 'saldo');	
		$resultado[]= array('id_tmp'=>$id_tmp, 'id_producto'=>$id_producto, 'codigo_producto'=>$codigo_producto, 'nombre_producto'=>$nombre_producto, 'entrada'=>$cantidad_entrada_tmp, 'salida'=>$cantidad_a_transformar, 'id_bodega'=>$id_bodega, 'id_medida'=>$id_medida_salida, 'caducidad'=>$caducidad, 'saldo_convertido'=> $total_saldo_producto, 'lote'=> $lote );
		}
	}
	foreach ($resultado as $valor){
		$delete_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$valor['id_tmp']."';");
		$sql_actualizar=mysqli_query($con,"INSERT INTO existencias_inventario_tmp VALUES (null,'".$valor['id_producto']."','".$valor['codigo_producto']."','".$valor['nombre_producto']."','".$valor['entrada']."','".$valor['saldo_convertido']."','".$valor['id_bodega']."','".$valor['id_medida']."','".$valor['caducidad']."', '".$ruc_empresa."', '".$valor['saldo_convertido']."','".$valor['lote']."','".$id_usuario."' )");
	}
	//todos los id temporales traidos para luego borrarlos
	$ides= array();
	$sql_filas_borrar = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."'");
	while ($row_ides_temporales = mysqli_fetch_array($sql_filas_borrar)){
	$id_temp_iniciales=$row_ides_temporales["id_existencia_tmp"];
	$ides[]= array('id_tmp_iniciales'=>$id_temp_iniciales);
	}
$query_actualiza_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
SELECT null,id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_caducidad, ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida),lote, '".$id_usuario."'  FROM existencias_inventario_tmp WHERE ruc_empresa ='". $ruc_empresa ."' group by lote");
//eliminar los ides tmp iniciales
	foreach ($ides as $id_tm){
	$delete_ides_tmp_iniciales = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$id_tm['id_tmp_iniciales']."';");
	}
		
	$sql_filas = mysqli_query($con,"SELECT lote FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."' and saldo_producto>0");
	return $sql_filas;
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

?>