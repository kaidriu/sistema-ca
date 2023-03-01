<?PHP
	include("../conexiones/conectalogin.php");
	session_start();
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

//para eliminar el cambio de producto
if ($action == 'eliminar_registro_cambio_producto'){
	$codigo_unico = $_GET['codigo_unico'];
	$elimina_detalle= mysqli_query($con, "DELETE FROM factura_tmp WHERE id='".$codigo_unico."'");
	muestra_detalle_productos();
}

if ($action == 'agregar_detalle_productos'){
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$id_registro = $_GET['id_registro'];
	$cambio = $_GET['cambio'];
	$cantidad = $_GET['cantidad'];
	if ($cambio=='F'){
	$insert_tmp=mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
	SELECT id_cuerpo_factura, id_producto, '".$cantidad."','0','0', '01', '2' , '0','F','".$id_usuario."', id_bodega, id_medida_salida,lote,vencimiento FROM cuerpo_factura WHERE id_cuerpo_factura='".$id_registro."'");
	}else{
	$insert_tmp=mysqli_query($con, "INSERT INTO factura_tmp (id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice , tarifa_botellas, id_usuario ,id_bodega,id_medida, lote,vencimiento)
	SELECT id_cambio, id_nuevo_producto, '".$cantidad."','0','0', '01', '2' , '0','R','".$id_usuario."', id_bodega_anterior, id_medida_anterior, nuevo_lote, vencimiento_anterior FROM cambio_productos_facturados WHERE id_cambio='".$id_registro."'");
	}
	muestra_detalle_productos();
}

function muestra_detalle_productos(){
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	
	$busca_detalle_factura=mysqli_query($con, "SELECT fact_tmp.tarifa_botellas as tipo_cambio,fact_tmp.id as id_registro, pro_ser.codigo_producto as codigo_producto, pro_ser.nombre_producto as nombre_producto, fact_tmp.lote as lote, fact_tmp.cantidad_tmp as cantidad_factura FROM factura_tmp as fact_tmp LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=fact_tmp.id_producto WHERE fact_tmp.id_usuario = '".$id_usuario."' ");
	?>
	<div class="panel panel-info">
					<table class="table table-hover"> 
						<tr class="info">
								<th style ="padding: 2px;">Código</th>
								<th style ="padding: 2px;">Producto Facturado</th>
								<th style ="padding: 2px;">Factura</th>
								<th style ="padding: 2px;">Lote</th>
								<th style ="padding: 2px;">Cant fact</th>
								<th style ="padding: 2px;">CV</th>
								<th style ="padding: 2px;">Nuevo producto</th>
								<th style ="padding: 2px;">Cant</th>
								<th style ="padding: 2px;">Quitar</th>
						</tr>
						<?php
							while ($detalle = mysqli_fetch_array($busca_detalle_factura)){
								$id_registro=$detalle['id_registro'];
								$codigo_producto=$detalle['codigo_producto'];
								$nombre_producto=$detalle['nombre_producto'];
								$lote=$detalle['lote'];
								$tipo_cambio=$detalle['tipo_cambio'];
								
								if($tipo_cambio=='F'){
								$detalle_factura=mysqli_query($con, "SELECT * FROM cuerpo_factura WHERE id_cuerpo_factura='".$id_registro."' ");
								$row_detalle_factura=mysqli_fetch_array($detalle_factura);
								$factura=$row_detalle_factura['serie_factura']."-".$row_detalle_factura['secuencial_factura'];
								}
								if($tipo_cambio=='R'){
								$detalle_factura_r=mysqli_query($con, "SELECT * FROM cambio_productos_facturados WHERE id_cambio='".$id_registro."' ");
								$row_detalle_factura_r=mysqli_fetch_array($detalle_factura_r);
								$factura=$row_detalle_factura_r['factura'];						
								}
								$cantidad_factura=$detalle['cantidad_factura'];
							?>
							<tr>	
								<input type="hidden" name="registros[]" value="<?php echo $id_registro;?>">
								<input type="hidden" name="id_cv[<?php echo $id_registro;?>]" id="id_cv<?php echo $id_registro;?>"></td>
								<input type="hidden" name="cant_producto_cambio[<?php echo $id_registro;?>]" id="cant_producto_cambio<?php echo $id_registro;?>"></td>							
								<td style ="padding: 2px;"><?php echo $codigo_producto; ?></td>						
								<td style ="padding: 2px;"><?php echo $nombre_producto; ?></td>
								<td style ="padding: 2px;"><?php echo $factura; ?></td>
								<td style ="padding: 2px;"><?php echo $lote; ?></td>
								<td style ="padding: 2px;"><?php echo $cantidad_factura; ?></td>
								
								<td style ="padding: 2px;" class='col-xs-1' >
								  <input type="text" class="form-control input-sm" name="numero_consignacion[<?php echo $id_registro;?>]" id="numero_consignacion<?php echo $id_registro;?>" >
								</td>
								<td style ="padding: 2px;" class="col-sm-3"><input type="text" class="form-control input-sm" name="nombre_producto_cambio[<?php echo $id_registro ?>]" id="nombre_producto_cambio<?php echo $id_registro ?>" onkeyup="buscar_producto_cv('<?php echo $id_registro ?>')"></td>
								<td style ="padding: 2px;" class="col-sm-1"><input type="text" class="form-control input-sm" name="cant_cambio[<?php echo $id_registro;?>]" id="cant_cambio<?php echo $id_registro;?>" onchange="cantidad_cambio('<?php echo $id_registro ?>');"></td>							
								<td class='text-right' style ="padding: 2px;">
								<a href="#" class='btn btn-danger btn-sm' onclick="eliminar_fila('<?php echo $id_registro; ?>')" title ="Eliminar item"><i class="glyphicon glyphicon-remove"></i></a>
								</td>
							</tr>
							<?php
							}
						?>
					</table>
				</div>
				<script>
</script>
<?php	
}
?>