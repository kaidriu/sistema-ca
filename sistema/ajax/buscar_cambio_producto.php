<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	
	if($action == 'eliminar_registro_total'){
	$codigo_unico=mysqli_real_escape_string($con,(strip_tags($_GET["codigo_unico"],ENT_QUOTES)));
	
	$delete_inventarios=mysqli_query($con,"DELETE FROM inventarios WHERE id_documento_venta = '".$codigo_unico."'");
	$delete_productos_cambiados=mysqli_query($con,"DELETE FROM cambio_productos_facturados WHERE codigo_unico = '".$codigo_unico."'");
	$actualiza_encabezado_consignacion=mysqli_query($con,"UPDATE encabezado_consignacion SET factura_venta='',observaciones='REGISTRO ANULADO DESDE CAMBIO DE PRODUCTOS' WHERE codigo_unico='".$codigo_unico."'");
	$delete_detalle_consignacion=mysqli_query($con,"DELETE FROM detalle_consignacion WHERE codigo_unico = '".$codigo_unico."'");

	echo "<script>$.notify('Registros eliminados.','success');
	setTimeout(function (){location.reload()}, 1000);
	</script>";
	
	}
	
	
	if($action == 'cambio_producto'){
		//$query_update = mysqli_query($con, "UPDATE cambio_productos_facturados as cp INNER JOIN cuerpo_factura as cf  ON cf.id_cuerpo_factura=cp.id_cuerpo_factura SET cp.factura = concat(cf.serie_factura,'-',cf.secuencial_factura ) WHERE cp.ruc_empresa='".$ruc_empresa."'");
		$q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		 $aColumns = array('cam_pro.factura', 'pro_ser.nombre_producto', 'pro_ser.codigo_producto','cam_pro.nuevo_lote','cli.nombre','cam_pro.observaciones','cam_pro.lote_anterior');//Columnas de busqueda
		 $sTable = "cambio_productos_facturados as cam_pro INNER JOIN productos_servicios as pro_ser ON cam_pro.id_producto_anterior=pro_ser.id LEFT JOIN clientes as cli ON cli.id=cam_pro.id_cliente";
		$sWhere = "WHERE cam_pro.ruc_empresa ='".$ruc_empresa."' " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (cam_pro.ruc_empresa ='".$ruc_empresa."' AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND cam_pro.ruc_empresa ='".$ruc_empresa."' OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND cam_pro.ruc_empresa ='".$ruc_empresa."' ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by $ordenado $por";
		
		include ("../ajax/pagination.php"); //include pagination file
		//pagination variables
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 20; //how much records you want to show
		$adjacents  = 4; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		//Count the total number of row in your table*/
		$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = '../cambio_producto_inventario.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			?>
		<div class="table-responsive">
			<div class="panel panel-info">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_cambio");'>Fecha</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_facturada");'>Cant factura</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto anterior</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote_anterior");'>Lote anterior</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_factura");'>Factura</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto nuevo</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_cambiada");'>Cant nueva</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nuevo_lote");'>Nuevo lote</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre");'>Cliente</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("observaciones");'>Observaciones</button></th>
					<th class='text-right'>Opciones</th>
				</tr>
				<?php
				while ($row=mysqli_fetch_array($query)){
						$id_cambio=$row['id_cambio'];
						$nombre_producto=$row['codigo_producto']." ".$row['nombre_producto'];
						$cant_facturada=$row['cant_facturada'];
						$cant_cambiada=$row['cant_cambiada'];
						$lote_anterior=strtoupper ($row['lote_anterior']);
						$nuevo_lote=strtoupper ($row['nuevo_lote']);
						$id_nuevo_producto=$row['id_nuevo_producto'];
						$codigo_unico=$row['codigo_unico'];
						$fecha_cambio=$row['fecha_cambio'];
						$cliente=$row['nombre'];
						$observaciones=$row['observaciones'];
						$factura=$row['factura'];
						//buscar productos
						$busca_producto_salida = "SELECT * FROM productos_servicios WHERE id = '".$id_nuevo_producto."'";
						 $result_produtos = $con->query($busca_producto_salida);
						 $row_producto_salida = mysqli_fetch_array($result_produtos);
						 $nombre_producto_salida=$row_producto_salida['codigo_producto']." ".$row_producto_salida['nombre_producto'];
					?>
						<td><?php echo date("d-m-Y", strtotime($fecha_cambio)); ?></td>
						<td><?php echo number_format($cant_facturada,0,'.','');?></td>
						<td class='col-xs-2'><?php echo strtoupper ($nombre_producto); ?></td>
						<td><?php echo $lote_anterior; ?></td>
						<td><?php echo $factura; ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($nombre_producto_salida); ?></td>
						<td><?php echo number_format($cant_cambiada,0,'.','');?></td>
						<td><?php echo $nuevo_lote; ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($cliente); ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($observaciones); ?></td>
						
					<td class='text-right'>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_cambio_producto('<?php echo $codigo_unico;?>');"><i class="glyphicon glyphicon-trash"></i></a> 	
					</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="12"><span class="pull-right">
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
	
?>