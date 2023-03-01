<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	
	if($action == 'existencia_consignacion_ventas'){
		$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."'");
		
			$tipo_existencia = mysqli_real_escape_string($con,(strip_tags($_GET['tipo_existencia'], ENT_QUOTES)));
			$id_nombre_buscar = mysqli_real_escape_string($con,(strip_tags($_GET['id_nombre_buscar'], ENT_QUOTES)));
			$nombre_buscar = mysqli_real_escape_string($con,(strip_tags($_GET['nombre_buscar'], ENT_QUOTES)));
		
		if ($tipo_existencia=='1'){//buscar por clientes
		$query=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and id_cli_pro='".$id_nombre_buscar."' and tipo_consignacion='VENTA' and operacion='ENTRADA' order by numero_consignacion desc");
			?>
		<div class="panel-group" id="accordiones">
		<div class="panel panel-info">
		<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordiones" href="#n"><span class="caret"></span> Detalle de concignaciones del cliente seleccionado</a>
		<div id="n" class="panel-collapse collapse">
		<div class="table-responsive">
			<div class="panel panel-info">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_consignacion");'>No.CV</button></th>
					<th class='col-xs-4' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_consignacion");'>Fecha</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote");'>Lote</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nup");'>Nup</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Consignado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Facturado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Devuelto</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Saldo</button></th>
				</tr>
				<?php
		$saldo_subtotal=array();
		while ($row=mysqli_fetch_array($query)){
		$fecha_consignacion=$row['fecha_consignacion'];
		$codigo_unico=strtoupper ($row['codigo_unico']);
		$observaciones=strtoupper ($row['observaciones']);
		$ncv=$row['numero_consignacion'];

				$detalle_consignacion=mysqli_query($con,"SELECT * FROM detalle_consignacion WHERE codigo_unico='".$codigo_unico."' ");
					$cantidad_suma=array();
					$total_facturado_suma=array();
					$total_devuelto_suma=array();
					while ($row_detalle=mysqli_fetch_array($detalle_consignacion)){
					$codigo_producto=$row_detalle['codigo_producto'];
					$id_producto=$row_detalle['id_producto'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$lote=$row_detalle['lote'];
					$nup=$row_detalle['nup'];
					$cantidad=$row_detalle['cant_consignacion'];
					$cantidad_suma[]=$row_detalle['cant_consignacion'];
					
					$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturas=mysqli_fetch_array($facturas);
					$todas_facturas=$row_facturas['factura'];
					
					$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturado=mysqli_fetch_array($facturado);
					$total_facturado=$row_facturado['facturado'];
					$total_facturado_suma[]=$row_facturado['facturado'];
					
					$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_devuelto=mysqli_fetch_array($devuelto);
					$total_devuelto=$row_devuelto['devuelto'];
					$total_devuelto_suma[]=$row_devuelto['devuelto'];
					?>
					
						<tr>
						<td><?php echo $ncv; ?></td>
						<td class='col-xs-2'><?php echo date("d-m-Y", strtotime($fecha_consignacion)); ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($codigo_producto); ?></td>
						<td class='col-xs-3'><?php echo strtoupper ($nombre_producto); ?></td>
						<td><?php echo strtoupper ($lote); ?></td>
						<td><?php echo strtoupper ($nup); ?></td>
						<td align="center"><?php echo number_format($cantidad,0,'.','');?></td>
						<td align="center"><?php echo number_format($total_facturado,0,'.',''); ?>
						<?php
						if ($total_facturado>0){
						?>	
						<a href="#" data-toggle="tooltip" data-placement="top" title="<?php echo $todas_facturas ?>"><span class="glyphicon glyphicon-question-sign"></span></a>
						<?php
						}
						?>	
						</td>
						<td align="center"><?php echo number_format($total_devuelto,0,'.','');?></td>
						<td align="center"><?php echo number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''); ?></td>
						</tr>
					<?php
					}
					$saldo_subtotal[]=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
		}

		$saldo_final=array_sum($saldo_subtotal);
		?>
		
		</div>
		 </table>
			</div>
			</div>
			</div>
			</div>
			</div>
			<li style="margin-bottom: 10px; margin-top: -10px;" class="list-group-item list-group-item-info" align="right"><h5><b><?php echo number_format($saldo_final,0,'.','');?> productos en consignación</b></h5></li>
		<?php
		
		}
		
		//busqueda por numero cv	
		if ($tipo_existencia=='2'){
		$query=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and numero_consignacion='".$nombre_buscar."' and tipo_consignacion='VENTA' and operacion='ENTRADA'");
						$row=mysqli_fetch_array($query);
						$id_cliente=$row['id_cli_pro'];
						$fecha_consignacion=$row['fecha_consignacion'];
						$codigo_unico=strtoupper ($row['codigo_unico']);
						$observaciones=strtoupper ($row['observaciones']);

						$clientes=mysqli_query($con,"SELECT * FROM clientes WHERE id='".$id_cliente."' ");
						$row_cliente=mysqli_fetch_array($clientes);
						$nombre_cliente=$row_cliente['nombre'];
						?>
		<li style="margin-bottom: 10px; margin-top: -10px;" class="list-group-item list-group-item-info"><h5><b>Fecha:</b> <?php echo date("d-m-Y", strtotime($fecha_consignacion));?> <b>Cliente: </b><?php echo strtoupper ($nombre_cliente);?> <b>Observaciones: </b><?php echo strtoupper ($observaciones);?> </h5></li>
		<div class="table-responsive">
			<div class="panel panel-info">
			  <table class="table table-hover">
				<tr  class="info">
					<th class='col-xs-4' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
					<th class='col-xs-4' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote");'>Lote</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nup");'>Nup</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Consignado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Facturado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Devuelto</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Saldo</button></th>
				</tr>
				<?php
						
						$detalle_consignacion=mysqli_query($con,"SELECT * FROM detalle_consignacion WHERE codigo_unico='".$codigo_unico."' order by nombre_producto asc");
						$cantidad_suma=array();
						$total_facturado_suma=array();
						$total_devuelto_suma=array();
					
						while ($row_detalle=mysqli_fetch_array($detalle_consignacion)){
						$id_producto=$row_detalle['id_producto'];
						$codigo_producto=$row_detalle['codigo_producto'];
						$nombre_producto=$row_detalle['nombre_producto'];
						$lote=$row_detalle['lote'];
						$nup=$row_detalle['nup'];
						$cantidad=$row_detalle['cant_consignacion'];
						$cantidad_suma[]=$row_detalle['cant_consignacion'];
						
						$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$nombre_buscar."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
						$row_facturas=mysqli_fetch_array($facturas);
						$todas_facturas=$row_facturas['factura'];
						
						$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$nombre_buscar."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
						$row_facturado=mysqli_fetch_array($facturado);
						$total_facturado=$row_facturado['facturado'];
						$total_facturado_suma[]=$row_facturado['facturado'];

						$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$nombre_buscar."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
						$row_devuelto=mysqli_fetch_array($devuelto);
						$total_devuelto=$row_devuelto['devuelto'];
						$total_devuelto_suma[]=$row_devuelto['devuelto'];

						?>
						<tr>
						<td class='col-xs-2'><?php echo strtoupper ($codigo_producto); ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($nombre_producto); ?></td>
						<td><?php echo strtoupper ($lote); ?></td>
						<td><?php echo strtoupper ($nup); ?></td>
						<td align="center"><?php echo number_format($cantidad,0,'.','');?></td>
						<td align="center"><?php echo number_format($total_facturado,0,'.',''); ?>	
						<?php
						if ($total_facturado>0){
						?>	
						<a href="#" data-toggle="tooltip" data-placement="top" title="<?php echo $todas_facturas ?>"><span class="glyphicon glyphicon-question-sign"></span></a>
						<?php
						}
						?>						
						</td>
						<td align="center"><?php echo number_format($total_devuelto,0,'.','');?></td>
						<td align="center"><?php echo number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''); ?></td>
						</tr>
						<?php
						}
						$saldo_final=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);//-array_sum($total_devuelto_suma)
						?>
			  </table>
			</div>
		</div>
		<li style="margin-bottom: 10px; margin-top: -10px;" class="list-group-item list-group-item-info" align="right"><h5><b><?php echo number_format($saldo_final,0,'.','');?> productos en consignación</b></h5></li>
		<?php
		
		}
		
	if ($tipo_existencia == '3'){//buscar consignaciones por productos
		$detalle_consignacion=mysqli_query($con,"SELECT * FROM detalle_consignacion det_con INNER JOIN encabezado_consignacion as enc_con ON det_con.codigo_unico=enc_con.codigo_unico WHERE det_con.id_producto='".$id_nombre_buscar."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' order by enc_con.numero_consignacion desc");
			$cantidad_suma=array();
			$total_facturado_suma=array();
			$total_devuelto_suma=array();
		?>
		<div class="panel-group" id="accordiones">
		<div class="panel panel-info">
		<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordiones" href="#n"><span class="caret"></span> Detalle de concignaciones del producto seleccionado</a>
		<div id="n" class="panel-collapse collapse">
		<div class="table-responsive">
			<div class="panel panel-info">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >No.CV</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Fecha</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Cliente</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Lote</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Nup</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Consignado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Facturado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Devuelto</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Saldo</button></th>
				</tr>
				<?php
				while ($row_detalle=mysqli_fetch_array($detalle_consignacion)){
				$codigo_producto=$row_detalle['codigo_producto'];
				$id_producto=$row_detalle['id_producto'];
				$codigo_unico=$row_detalle['codigo_unico'];
				$nombre_producto=$row_detalle['nombre_producto'];
				$lote=$row_detalle['lote'];
				$nup=$row_detalle['nup'];
				$cantidad=$row_detalle['cant_consignacion'];
				$cantidad_suma[]=$row_detalle['cant_consignacion'];
				
				$encabezado=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE codigo_unico='".$codigo_unico."' ");
				$row_encabezado=mysqli_fetch_array($encabezado);
				$numero_consignacion=$row_encabezado['numero_consignacion'];
				$fecha_consignacion=$row_encabezado['fecha_consignacion'];
				$id_cliente=$row_encabezado['id_cli_pro'];
				
				$clientes=mysqli_query($con,"SELECT * FROM clientes WHERE id='".$id_cliente."' ");
				$row_cliente=mysqli_fetch_array($clientes);
				$cliente=$row_cliente['nombre'];

				$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
				$row_facturas=mysqli_fetch_array($facturas);
				$todas_facturas=$row_facturas['factura'];
					
				$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
				$row_facturado=mysqli_fetch_array($facturado);
				$total_facturado=$row_facturado['facturado'];
				$total_facturado_suma[]=$row_facturado['facturado'];
				
				$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
				$row_devuelto=mysqli_fetch_array($devuelto);
				$total_devuelto=$row_devuelto['devuelto'];
				$total_devuelto_suma[]=$row_devuelto['devuelto'];
				
				?>
						<tr>
						<td class='col-xs-2'><?php echo $numero_consignacion; ?></td>
						<td class='col-xs-2'><?php echo date("d-m-Y", strtotime($fecha_consignacion)); ?></td>
						<td class='col-xs-2'><?php echo $cliente; ?></td>
						<td><?php echo strtoupper ($lote); ?></td>
						<td><?php echo strtoupper ($nup); ?></td>
						<td align="center"><?php echo number_format($cantidad,0,'.','');?></td>
						<td align="center"><?php echo number_format($total_facturado,0,'.',''); ?>
						<?php
						if ($total_facturado>0){
						?>	
						<a href="#" data-toggle="tooltip" data-placement="top" title="<?php echo $todas_facturas ?>"><span class="glyphicon glyphicon-question-sign"></span></a>
						<?php
						}
						?>	
						</td>
						<td align="center"><?php echo number_format($total_devuelto,0,'.','');?></td>
						<td align="center"><?php echo number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''); ?></td>
						</tr>
						<?php
				
				}
				?>
			 </table>
			</div>
		</div>
		</div>
		</div>
		</div>
		<?php
		$saldo_final=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
		?>
				<li style="margin-bottom: 10px; margin-top: -10px;" class="list-group-item list-group-item-info" align="right"><h5><b><?php echo number_format($saldo_final,0,'.','');?> productos en consignación</b></h5></li>
		<?php
		}

		//busqueda por nup	
		if ($tipo_existencia == '4'){//buscar consignaciones por productos
		$detalle_consignacion=mysqli_query($con,"SELECT * FROM detalle_consignacion det_con INNER JOIN encabezado_consignacion as enc_con ON det_con.codigo_unico=enc_con.codigo_unico WHERE det_con.nup='".$nombre_buscar."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' order by enc_con.numero_consignacion desc");
			$cantidad_suma=array();
			$total_facturado_suma=array();
			$total_devuelto_suma=array();
		?>
		<div class="panel-group" id="accordiones">
		<div class="panel panel-info">
		<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordiones" href="#n"><span class="caret"></span> Detalle de concignaciones del producto seleccionado</a>
		<div id="n" class="panel-collapse collapse">
		<div class="table-responsive">
			<div class="panel panel-info">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >No.CV</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Fecha</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Cliente</button></th>
					<th class='col-xs-2' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Lote</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Consignado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Facturado</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Devuelto</button></th>
					<th class='text-right' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cant_consignacion");'>Saldo</button></th>
				</tr>
				<?php
				while ($row_detalle=mysqli_fetch_array($detalle_consignacion)){
				$codigo_producto=$row_detalle['codigo_producto'];
				$id_producto=$row_detalle['id_producto'];
				$codigo_unico=$row_detalle['codigo_unico'];
				$nombre_producto=$row_detalle['nombre_producto'];
				$lote=$row_detalle['lote'];
				$nup=$row_detalle['nup'];
				$cantidad=$row_detalle['cant_consignacion'];
				$cantidad_suma[]=$row_detalle['cant_consignacion'];
				
				$encabezado=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE codigo_unico='".$codigo_unico."' ");
				$row_encabezado=mysqli_fetch_array($encabezado);
				$numero_consignacion=$row_encabezado['numero_consignacion'];
				$fecha_consignacion=$row_encabezado['fecha_consignacion'];
				$id_cliente=$row_encabezado['id_cli_pro'];
				
				$clientes=mysqli_query($con,"SELECT * FROM clientes WHERE id='".$id_cliente."' ");
				$row_cliente=mysqli_fetch_array($clientes);
				$cliente=$row_cliente['nombre'];

				$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
				$row_facturas=mysqli_fetch_array($facturas);
				$todas_facturas=$row_facturas['factura'];
					
				$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
				$row_facturado=mysqli_fetch_array($facturado);
				$total_facturado=$row_facturado['facturado'];
				$total_facturado_suma[]=$row_facturado['facturado'];
				
				$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
				$row_devuelto=mysqli_fetch_array($devuelto);
				$total_devuelto=$row_devuelto['devuelto'];
				$total_devuelto_suma[]=$row_devuelto['devuelto'];
				
				?>
						<tr>
						<td class='col-xs-2'><?php echo $numero_consignacion; ?></td>
						<td class='col-xs-2'><?php echo date("d-m-Y", strtotime($fecha_consignacion)); ?></td>
						<td class='col-xs-2'><?php echo $cliente; ?></td>
						<td><?php echo strtoupper ($lote); ?></td>
						<td align="center"><?php echo number_format($cantidad,0,'.','');?></td>
						<td align="center"><?php echo number_format($total_facturado,0,'.',''); ?>
						<?php
						if ($total_facturado>0){
						?>	
						<a href="#" data-toggle="tooltip" data-placement="top" title="<?php echo $todas_facturas ?>"><span class="glyphicon glyphicon-question-sign"></span></a>
						<?php
						}
						?>	
						</td>
						<td align="center"><?php echo number_format($total_devuelto,0,'.','');?></td>
						<td align="center"><?php echo number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''); ?></td>
						</tr>
						<?php
				
				}
				?>
			 </table>
			</div>
		</div>
		</div>
		</div>
		</div>
		<?php
		$saldo_final=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
		?>
				<li style="margin-bottom: 10px; margin-top: -10px;" class="list-group-item list-group-item-info" align="right"><h5><b><?php echo number_format($saldo_final,0,'.','');?> productos en consignación</b></h5></li>
		<?php
		}
	}
	
?>