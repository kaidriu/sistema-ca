<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

//PARA BUSCAR LAS FACTURAS de ventas	
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
$tipo_reporte=$_POST['action'];
//$id_cliente=$_POST['id_cliente'];
//$id_producto=$_POST['id_producto'];
$desde=$_POST['desde'];
$hasta=$_POST['hasta'];
//$id_marca=$_POST['id_marca'];
ini_set('date.timezone','America/Guayaquil');

if($action == '1'){

	/*
if (empty($id_cliente)){
$condicion_cliente="";
}else{
$condicion_cliente=" and enc_fac.id_cliente=".$id_cliente;	
}

if (empty($id_producto)){
$condicion_producto="";
}else{
$condicion_producto=" and cue_fac.id_producto=".$id_producto;	
}

if (empty($id_marca)){
$condicion_marca="";
}else{
$condicion_marca=" and mar_pro.id_marca=".$id_marca;	
}
*/
			?>	
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>#</th>
					<th>Fecha</th>
					<th>Cliente</th>
					<th>Ruc</th>
					<th>Secuencial</th>
					<th>Base 0</th>
					<th>Base 12</th>
					<th>Base No iva</th>
					<th>Base Exento</th>
					<th>Iva 12</th>
					<th>Base ice</th>
					<th>Descuento</th>
					<th>Propina</th>
					<th>Otros</th>
					<th>Total</th>
					<th>Usuario</th>
				</tr>
				<?php
				$suma_factura=0;
				$suma_base_cero=0;
				$suma_base_doce=0;
				$suma_base_noimp=0;
				$suma_base_exento=0;
				$suma_base_descuento=0;
				$suma_propina=0;
				$suma_tasa_turistica=0;
				$n=0;
				$resultado_consolidado[] = mysqli_query($con, "SELECT sum(cue_fac.subtotal_factura) as subtotal_cero, sum(cue_fac.descuento) as descuento_cero, enc_fac.total_factura as total_factura, enc_fac.id_encabezado_factura as id_encabezado_factura,
				 enc_fac.fecha_factura as fecha_factura, enc_fac.serie_factura as serie_factura, enc_fac.secuencial_factura as secuencial_factura,
				 cli.nombre as nombre, cli.ruc as ruc, enc_fac.propina as propina, enc_fac.tasa_turistica as tasa_turistica, usu.nombre as usuario
				 FROM cuerpo_factura as cue_fac LEFT JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and 
				enc_fac.secuencial_factura=cue_fac.secuencial_factura 
				LEFT JOIN clientes as cli ON cli.id=enc_fac.id_cliente LEFT JOIN usuarios as usu ON usu.id=enc_fac.id_usuario
				WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and 
				mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and
				 DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and
				  '".date("Y/m/d", strtotime($hasta))."' and cue_fac.tarifa_iva='0' and cue_fac.subtotal_factura > 0 group by cue_fac.serie_factura, cue_fac.secuencial_factura");//group by cue_fac.serie_factura, cue_fac.secuencial_factura
			
				  $resultado_consolidado[] = mysqli_query($con, "SELECT sum(cue_fac.subtotal_factura) as subtotal_doce, sum(cue_fac.descuento) as descuento_doce, enc_fac.total_factura as total_factura, enc_fac.id_encabezado_factura as id_encabezado_factura,
				  enc_fac.fecha_factura as fecha_factura, enc_fac.serie_factura as serie_factura, enc_fac.secuencial_factura as secuencial_factura,
				  cli.nombre as nombre, cli.ruc as ruc, enc_fac.propina as propina, enc_fac.tasa_turistica as tasa_turistica, usu.nombre as usuario
				  FROM cuerpo_factura as cue_fac LEFT JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and 
				 enc_fac.secuencial_factura=cue_fac.secuencial_factura 
				 LEFT JOIN clientes as cli ON cli.id=enc_fac.id_cliente LEFT JOIN usuarios as usu ON usu.id=enc_fac.id_usuario
				 WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and 
				 mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and
				  DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and
				   '".date("Y/m/d", strtotime($hasta))."' and cue_fac.tarifa_iva='2' and cue_fac.subtotal_factura > 0 group by cue_fac.serie_factura, cue_fac.secuencial_factura");


				   $resultado_consolidado[] = mysqli_query($con, "SELECT sum(cue_fac.subtotal_factura) as subtotal_no, sum(cue_fac.descuento) as descuento_no, enc_fac.total_factura as total_factura, enc_fac.id_encabezado_factura as id_encabezado_factura,
				  enc_fac.fecha_factura as fecha_factura, enc_fac.serie_factura as serie_factura, enc_fac.secuencial_factura as secuencial_factura,
				  cli.nombre as nombre, cli.ruc as ruc, enc_fac.propina as propina, enc_fac.tasa_turistica as tasa_turistica, usu.nombre as usuario
				  FROM cuerpo_factura as cue_fac LEFT JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and 
				 enc_fac.secuencial_factura=cue_fac.secuencial_factura 
				 LEFT JOIN clientes as cli ON cli.id=enc_fac.id_cliente LEFT JOIN usuarios as usu ON usu.id=enc_fac.id_usuario
				 WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and 
				 mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and
				  DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and
				   '".date("Y/m/d", strtotime($hasta))."' and cue_fac.tarifa_iva='6' and cue_fac.subtotal_factura > 0 group by cue_fac.serie_factura, cue_fac.secuencial_factura");

				   $resultado_consolidado[] = mysqli_query($con, "SELECT sum(cue_fac.subtotal_factura) as subtotal_exento, sum(cue_fac.descuento) as descuento_exento, enc_fac.total_factura as total_factura, enc_fac.id_encabezado_factura as id_encabezado_factura,
				  enc_fac.fecha_factura as fecha_factura, enc_fac.serie_factura as serie_factura, enc_fac.secuencial_factura as secuencial_factura,
				  cli.nombre as nombre, cli.ruc as ruc, enc_fac.propina as propina, enc_fac.tasa_turistica as tasa_turistica, usu.nombre as usuario
				  FROM cuerpo_factura as cue_fac LEFT JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and 
				 enc_fac.secuencial_factura=cue_fac.secuencial_factura 
				 LEFT JOIN clientes as cli ON cli.id=enc_fac.id_cliente LEFT JOIN usuarios as usu ON usu.id=enc_fac.id_usuario
				 WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and 
				 mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and
				  DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and
				   '".date("Y/m/d", strtotime($hasta))."' and cue_fac.tarifa_iva='7' and cue_fac.subtotal_factura > 0 group by cue_fac.serie_factura, cue_fac.secuencial_factura");


					foreach($resultado_consolidado as $resultado){
				  while ($row=mysqli_fetch_array($resultado)){
						$suma_factura+= $row['total_factura'];
						$id_encabezado_factura=$row['id_encabezado_factura'];
						$fecha_factura=$row['fecha_factura'];
						$serie_factura=$row['serie_factura'];
						$secuencial_factura=$row['secuencial_factura'];
						$nombre_cliente_factura=$row['nombre'];
						$total_factura=$row['total_factura'];
						$ruc_cliente=$row['ruc'];
						$tasa_turistica=$row['tasa_turistica'];
						$propina=$row['propina'];
						$nombre_usuario = $row['usuario'];
						$base_cero = $row['subtotal_cero']-$row['descuento_cero'];
						$base_doce = $row['subtotal_doce']-$row['descuento_doce'];
						$base_no = $row['subtotal_no']-$row['descuento_no'];
						$base_exento = $row['subtotal_exento']-$row['descuento_exento'];
						$base_descuento=$row['descuento_cero']+$row['descuento_doce']+$row['descuento_no']+$row['descuento_exento'];
						$n=$n+1;
					?>
					<tr>
						<td><?php echo $n; ?></td>
						<td><?php echo date("d/m/Y", strtotime($fecha_factura)); ?></td>
						<td><?php echo $nombre_cliente_factura; ?></td>
						<td><?php echo $ruc_cliente; ?></td>
						<td><?php echo $serie_factura; ?>-<?php echo str_pad($secuencial_factura,9,"000000000",STR_PAD_LEFT); ?></td>
						<td><?php echo number_format($base_cero,2,'.',''); ?></td>
						<td><?php echo number_format($base_doce,2,'.',''); ?></td>
						<td><?php echo number_format($base_no,2,'.',''); ?></td>
						<td><?php echo number_format($base_exento,2,'.',''); ?></td>
						<td><?php echo number_format($base_doce * 0.12,2,'.',''); ?></td>
						<td>0.00</td>
						<td><?php echo number_format($base_descuento,2,'.',''); ?></td>
						<td><?php echo number_format($propina,2,'.',''); ?></td>
						<td><?php echo number_format($tasa_turistica,2,'.',''); ?></td>
						<td><?php echo number_format($total_factura,2,'.',''); ?></td>
					<td><?php echo $nombre_usuario;?></td>
					</tr>
					<?php
					$suma_base_cero+= $base_cero;
					$suma_base_doce+= $base_doce;
					$suma_base_noimp+= $base_noimp;
					$suma_base_exento+= $base_exento;
					$suma_base_descuento+= $base_descuento;
					$suma_propina+=$propina;
					$suma_tasa_turistica+=$tasa_turistica;
					
				}
			}
				?>	
					<tr  class="info">
					<th colspan="4">Totales</th>
					<td><span id="loader_excel"></span></td>
						<td><?php echo number_format($suma_base_cero,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_doce,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_noimp,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_exento,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_doce *0.12,2,'.',''); ?></td>
						<td>0.00</td>
						<td><?php echo number_format($suma_base_descuento,2,'.',''); ?></td>
						<td><?php echo number_format($suma_propina,2,'.',''); ?></td>
						<td><?php echo number_format($suma_tasa_turistica,2,'.',''); ?></td>
						<td><?php echo number_format($suma_factura,2,'.',''); ?></td>
						<td></td>
					</tr>
										
				</table>
				</div>
			</div>
		<?php
}

if($action == '2'){//notas de credito
if (empty($id_cliente)){
$condicion_cliente="";
}else{
$condicion_cliente=" and enc_nc.id_cliente=".$id_cliente;	
}

if (empty($id_producto)){
$condicion_producto="";
}else{
$condicion_producto=" and cue_nc.id_producto=".$id_producto;	
}

if (empty($id_marca)){
$condicion_marca="";
}else{
$condicion_marca=" and mar_pro.id_marca=".$id_marca;	
}
	?>	
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>#</th>
					<th>Fecha</th>
					<th>Cliente</th>
					<th>Ruc</th>
					<th>NC</th>
					<th>Factura</th>
					<th>Base 0</th>
					<th>Base 12</th>
					<th>Base No iva</th>
					<th>Base Exento</th>
					<th>Iva 12</th>
					<th>Base ice</th>
					<th>Descuento</th>
					<th>Total</th>
					<th>Usuario</th>
				</tr>
				<?php
				$suma_nc=0;
				$suma_base_cero=0;
				$suma_base_doce=0;
				$suma_base_catorce=0;
				$suma_base_noimp=0;
				$suma_base_exento=0;
				$suma_base_descuento=0;
				$n=0;
				
				$resultado = mysqli_query($con, "SELECT * FROM cuerpo_nc as cue_nc INNER JOIN encabezado_nc as enc_nc ON enc_nc.serie_nc=cue_nc.serie_nc and enc_nc.secuencial_nc=cue_nc.secuencial_nc INNER JOIN clientes as cli ON cli.id=enc_nc.id_cliente LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=cue_nc.id_producto LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=cue_nc.id_producto WHERE mid(enc_nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(cue_nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and DATE_FORMAT(enc_nc.fecha_nc, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' $condicion_cliente $condicion_producto $condicion_marca group by cue_nc.serie_nc, cue_nc.secuencial_nc");

				while ($row=mysqli_fetch_array($resultado)){
						$suma_nc+= $row['total_nc'];
						$id_encabezado_nc=$row['id_encabezado_nc'];
						$fecha_nc=$row['fecha_nc'];
						$serie_nc=$row['serie_nc'];
						$secuencial_nc=$row['secuencial_nc'];
						$nombre_cliente_nc=$row['nombre'];
						$factura_afectada=$row['factura_modificada'];
						$total_nc=$row['total_nc'];
						$ruc_cliente=$row['ruc'];
						$n=$n+1;
					
					?>
					<tr>
						<td><?php echo $n; ?></td>
						<td><?php echo date("d/m/Y", strtotime($fecha_nc)); ?></td>
						<td><?php echo $nombre_cliente_nc; ?></td>
						<td><?php echo $ruc_cliente; ?></td>
						<td><?php echo $serie_nc; ?>-<?php echo str_pad($secuencial_nc,9,"000000000",STR_PAD_LEFT); ?></td>
						<td><?php echo $factura_afectada; ?></td>
					
					<?php
					//para sacar el detalle de base cero
					$sql_cero = "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 0 and serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial_nc."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_subtotales = mysqli_query($con,$sql_cero);
					$subtotales=mysqli_fetch_array($resultado_subtotales);
					$base_cero= $subtotales['subtotal'];				
					?>
						<td><?php echo number_format($base_cero,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base doce
					$sql_doce = "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 2 and serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial_nc."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_subtotales = mysqli_query($con,$sql_doce);
					$subtotales=mysqli_fetch_array($resultado_subtotales);
					$base_doce= $subtotales['subtotal'];
					?>
						<td><?php echo number_format($base_doce,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base no obj imp
					$sql_no = "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 6 and serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial_nc."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_subtotales = mysqli_query($con,$sql_no);
					$subtotales=mysqli_fetch_array($resultado_subtotales);
					$base_noimp= $subtotales['subtotal'];
					
					?>
						<td><?php echo number_format($base_noimp,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base exento
					$sql_detalle = "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 7 and serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial_nc."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_subtotales = mysqli_query($con,$sql_detalle);
					$subtotales=mysqli_fetch_array($resultado_subtotales);
					$base_exento= $subtotales['subtotal'];
					?>
						<td><?php echo number_format($base_exento,2,'.',''); ?></td>
						<td><?php echo number_format($base_doce * 0.12,2,'.',''); ?></td>
						<td>0.00</td>
					<?php
					//para sacar el detalle de descuento
					$sql_descuento = "SELECT sum(descuento) as descuento FROM cuerpo_nc where serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial_nc."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_subtotales = mysqli_query($con,$sql_descuento);
					$subtotales=mysqli_fetch_array($resultado_subtotales);
					$base_descuento= $subtotales['descuento'];
					
					?>	
						<td><?php echo number_format($base_descuento,2,'.',''); ?></td>
						<td><?php echo number_format($total_nc,2,'.',''); ?></td>
						
					<?php
					$sql_usuario = "SELECT usu.nombre as nombre_usuario FROM usuarios usu, encabezado_nc enc where usu.id=enc.id_usuario and enc.id_encabezado_nc='".$id_encabezado_nc."'";      
					$resultado_usuario = mysqli_query($con,$sql_usuario);
					$usuario_nombres= mysqli_fetch_array($resultado_usuario);
					$nombre_usuario = $usuario_nombres['nombre_usuario'];
					?>
					<td><?php echo substr($nombre_usuario, 0,6);?></td>
					</tr>
					<?php
					$suma_base_cero+= $base_cero;
					$suma_base_doce+= $base_doce;
					$suma_base_noimp+= $base_noimp;
					$suma_base_exento+= $base_exento;
					$suma_base_descuento+= $base_descuento;
					
				}
				?>	
					<tr  class="info">
					<th colspan="5">Totales</th>
					<td><span id="loader_nc"></span></td>
						<td><?php echo number_format($suma_base_cero,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_doce,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_noimp,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_exento,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_doce *0.12,2,'.',''); ?></td>
						<td>0.00</td>
						<td><?php echo number_format($suma_base_descuento,2,'.',''); ?></td>
						<td><?php echo number_format($suma_nc,2,'.',''); ?></td>
						<td></td>
						<td></td>
					</tr>
										
				</table>
				</div>
			</div>
			<?php
}
// para buscar las facturas en detalle
if($action == '3'){
	if (empty($id_cliente)){
$condicion_cliente="";
}else{
$condicion_cliente=" and enc_fac.id_cliente=".$id_cliente;	
}

if (empty($id_producto)){
$condicion_producto="";
}else{
$condicion_producto=" and cue_fac.id_producto=".$id_producto;	
}

if (empty($id_marca)){
$condicion_marca="";
}else{
$condicion_marca=" and mar_pro.id_marca=".$id_marca;	
}
			?>	
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
						<tr class="info">
							<td>#</td>
							<td>Fecha</td>
							<td>Cliente</td>
							<td>Factura</td>
							<td>Código</td>
							<td>Detalle</td>
							<td>Tipo</td>
							<td>Tarifa</td>
							<td>Cantidad</td>
							<td>Valor Uni.</td>
							<td>Descuento</td>
							<td>Subtotal</td>
							<td>IVA 12%</td>
							<td>Total</td>
						</tr>
				<?php
				$n=0;
				
				$resultado = mysqli_query($con, "SELECT enc_fac.fecha_factura as fecha_factura, 
				cue_fac.serie_factura as serie_factura, cue_fac.secuencial_factura as secuencial_factura,
				cli.nombre as nombre_cliente, enc_fac.total_factura as total_factura, 
				cli.ruc as ruc, cue_fac.cantidad_factura as cantidad_factura,
				cue_fac.nombre_producto as nombre_producto, cue_fac.codigo_producto as codigo_producto,
				cue_fac.valor_unitario_factura as valor_unitario_factura, 
				cue_fac.descuento as descuento, cue_fac.subtotal_factura as subtotal_factura,
				tip_pro.nombre as nombre_produccion, tar_iva.tarifa as tarifa, tar_iva.porcentaje_iva as porcentaje_iva 
				FROM cuerpo_factura as cue_fac 
				INNER JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and enc_fac.secuencial_factura=cue_fac.secuencial_factura 
				INNER JOIN clientes as cli ON cli.id=enc_fac.id_cliente 
				LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=cue_fac.id_producto 
				LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=cue_fac.id_producto 
				INNER JOIN tipo_produccion as tip_pro ON tip_pro.codigo=cue_fac.tipo_produccion 
				INNER JOIN tarifa_iva as tar_iva ON tar_iva.codigo=cue_fac.tarifa_iva WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' $condicion_cliente $condicion_producto $condicion_marca ");//group by cue_fac.serie_factura, cue_fac.secuencial_factura
				$suma_total_factura=0;
				$suma_cantidad =0;
				$suma_valor_unitario =0;
				$suma_subtotal_factura =0;
				$suma_descuento =0;
				$suma_iva=0;
				while ($row=mysqli_fetch_array($resultado)){
					$n=$n+1;
						$fecha_factura=$row['fecha_factura'];
						$serie_factura=$row['serie_factura'];
						$secuencial_factura=$row['secuencial_factura'];
						$nombre_cliente_factura=$row['nombre_cliente'];
						$ruc_cliente=$row['ruc'];
						$cantidad= $row['cantidad_factura'];
						$suma_cantidad += $row['cantidad_factura'];
						$producto= $row['nombre_producto'];
						$codigo= $row['codigo_producto'];
						$valor_unitario= $row['valor_unitario_factura'];
						$suma_valor_unitario += $row['valor_unitario_factura'];
						$descuento= $row['descuento'];
						$suma_descuento += $row['descuento'];
						$subtotal_factura= $row['subtotal_factura']-$descuento;
						$suma_subtotal_factura += $row['subtotal_factura']-$descuento;
						$tipo_produccion= $row['nombre_produccion'];
						$tarifa_iva= $row['tarifa'];
						$porcentaje_iva= $row['porcentaje_iva']/100;
						$suma_iva +=number_format(($subtotal_factura * $porcentaje_iva),2,'.','');
						$total_factura= ($row['subtotal_factura'] - $row['descuento'] + ($subtotal_factura * $porcentaje_iva));
						$suma_total_factura +=$total_factura;
					?>				
					<tr>
					<td><?php echo $n; ?></td>
					<td><?php echo date("d/m/Y", strtotime($fecha_factura)); ?></td>
					<td><?php echo $nombre_cliente_factura; ?></td>
					<td><?php echo $serie_factura; ?>-<?php echo str_pad($secuencial_factura,9,"000000000",STR_PAD_LEFT); ?></td>
					<td><?php echo $codigo?></td>
					<td><?php echo $producto?></td>
					<td><?php echo $tipo_produccion?></td>
					<td><?php echo $tarifa_iva?></td>
					<td><?php echo $cantidad?></td>
					<td><?php echo number_format($valor_unitario,4,'.','')?></td>
					<td><?php echo number_format($descuento,2,'.','')?></td>
					<td><?php echo  number_format($subtotal_factura,2,'.','')?></td>
					<td><?php echo  number_format(($subtotal_factura * $porcentaje_iva),2,'.','')?></td>
					<td><?php echo number_format($total_factura,2,'.','')?></td>
					</tr>
					<?php
				}
				?>	
					<tr class="info">
						<td colspan="8" class='text-right'>Totales</td>
						<td ><?php echo number_format($suma_cantidad,4,'.','')?></td>
						<td ><?php echo number_format($suma_valor_unitario,4,'.','')?></td>
						<td ><?php echo number_format($suma_descuento,2,'.','')?></td>
						<td ><?php echo number_format($suma_subtotal_factura,2,'.','')?></td>
						<td ><?php echo number_format($suma_iva,2,'.','')?></td>
						<td ><?php echo number_format($suma_total_factura,2,'.','')?></td>
					</tr>
				</table>
				</div>
			</div>
			<?php
}

// para buscar las nc en detalle
if($action == '4'){
	if (empty($id_cliente)){
$condicion_cliente="";
}else{
$condicion_cliente=" and enc_nc.id_cliente=".$id_cliente;	
}

if (empty($id_producto)){
$condicion_producto="";
}else{
$condicion_producto=" and cue_nc.id_producto=".$id_producto;	
}

if (empty($id_marca)){
$condicion_marca="";
}else{
$condicion_marca=" and mar_pro.id_marca=".$id_marca;	
}
			?>	
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
						<td>#</td>
						<td>Fecha</td>
						<td>Cliente</td>
						<td>Nc</td>
						<td>Factura</td>
						<td>Motivo</td>
						<td>Código</td>
						<td>Detalle</td>
						<td>Tipo</td>
						<td>Tarifa</td>
						<td>Cantidad</td>
						<td>Valor Uni.</td>
						<td>Descuento</td>
						<td>Subtotal</td>
						<td>IVA</td>
						<td>Total</td>						
				</tr>
				<?php
				$n=0;
				$resultado = mysqli_query($con, "SELECT enc_nc.fecha_nc as fecha_nc, 
				cue_nc.serie_nc as serie_nc, cue_nc.secuencial_nc as secuencial_nc,
				cli.nombre as nombre_cliente, enc_nc.total_nc as total_nc, 
				cli.ruc as ruc, cue_nc.cantidad_nc as cantidad_nc,
				cue_nc.nombre_producto as nombre_producto, cue_nc.codigo_producto as codigo_producto,
				cue_nc.valor_unitario_nc as valor_unitario_nc, 
				cue_nc.descuento as descuento, cue_nc.subtotal_nc as subtotal_nc,
				tip_pro.nombre as nombre_produccion, tar_iva.tarifa as tarifa, tar_iva.porcentaje_iva as porcentaje_iva,
				enc_nc.factura_modificada as factura_modificada, enc_nc.motivo as motivo
				FROM cuerpo_nc as cue_nc 
				INNER JOIN encabezado_nc as enc_nc ON enc_nc.serie_nc=cue_nc.serie_nc and enc_nc.secuencial_nc=cue_nc.secuencial_nc 
				INNER JOIN clientes as cli ON cli.id=enc_nc.id_cliente 
				LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=cue_nc.id_producto 
				LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=cue_nc.id_producto 
				INNER JOIN tipo_produccion as tip_pro ON tip_pro.codigo=cue_nc.tipo_produccion 
				INNER JOIN tarifa_iva as tar_iva ON tar_iva.codigo=cue_nc.tarifa_iva WHERE mid(enc_nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(cue_nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and DATE_FORMAT(enc_nc.fecha_nc, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' $condicion_cliente $condicion_producto $condicion_marca order by enc_nc.secuencial_nc desc");
				$suma_total_nc=0;
				$suma_cantidad =0;
				$suma_valor_unitario =0;
				$suma_subtotal_nc =0;
				$suma_descuento =0;
				$suma_iva=0;
				while ($row=mysqli_fetch_array($resultado)){
						$fecha_nc=$row['fecha_nc'];
						$serie_nc=$row['serie_nc'];
						$secuencial_nc=$row['secuencial_nc'];
						$nombre_cliente_nc=$row['nombre_cliente'];
						$ruc_cliente=$row['ruc'];
						$cantidad= $row['cantidad_nc'];
						$suma_cantidad += $row['cantidad_nc'];
						$producto= $row['nombre_producto'];
						$codigo= $row['codigo_producto'];
						$valor_unitario= $row['valor_unitario_nc'];
						$suma_valor_unitario += $row['valor_unitario_nc'];
						$descuento= $row['descuento'];
						$suma_descuento += $row['descuento'];
						$subtotal_nc= $row['subtotal_nc']-$descuento;
						$suma_subtotal_nc += $row['subtotal_nc']-$descuento;
						$tipo_produccion= $row['nombre_produccion'];
						$tarifa_iva= $row['tarifa'];
						$porcentaje_iva= $row['porcentaje_iva']/100;
						$suma_iva +=number_format(($subtotal_nc * $porcentaje_iva),2,'.','');
						$total_nc= ($row['subtotal_nc'] - $row['descuento'] + ($subtotal_nc * $porcentaje_iva));
						$suma_total_nc +=$total_nc;

						$factura_modificada=$row['factura_modificada'];
						$motivo=$row['motivo'];
						$n=$n+1;
					
					?>
					<tr>
					<td><?php echo $n; ?></td>
					<td><?php echo date("d/m/Y", strtotime($fecha_nc)); ?></td>
					<td><?php echo $nombre_cliente_nc; ?></td>
					<td><?php echo $serie_nc; ?>-<?php echo str_pad($secuencial_nc,9,"000000000",STR_PAD_LEFT); ?></td>
					<td><?php echo $factura_modificada; ?></td>
					<td><?php echo ($motivo)?></td>
					<td><?php echo ($codigo)?></td>
					<td><?php echo ($producto)?></td>
					<td><?php echo ($tipo_produccion)?></td>
					<td><?php echo ($tarifa_iva)?></td>
					<td><?php echo ($cantidad)?></td>
					<td><?php echo number_format($valor_unitario,4,'.','')?></td>
					<td><?php echo number_format($descuento,2,'.','')?></td>
					<td><?php echo  number_format($subtotal_nc,2,'.','')?></td>
					<td><?php echo  number_format(($subtotal_nc * $porcentaje_iva),2,'.','')?></td>
					<td><?php echo number_format($total_nc,2,'.','')?></td>
					</tr>
					<?php
				}
				?>	
					<tr class="info">
						<td colspan="10" class='text-right'>Totales</td>
						<td ><?php echo number_format($suma_cantidad,4,'.','')?></td>
						<td ><?php echo number_format($suma_valor_unitario,4,'.','')?></td>
						<td ><?php echo number_format($suma_descuento,2,'.','')?></td>
						<td ><?php echo number_format($suma_subtotal_nc,2,'.','')?></td>
						<td ><?php echo number_format($suma_iva,2,'.','')?></td>
						<td ><?php echo number_format($suma_total_nc,2,'.','')?></td>
					</tr>
				</table>
				</div>
			</div>
			<?php
}
?>

