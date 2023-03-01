<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

//PARA BUSCAR LAS FACTURAS de ventas	
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
$tipo_reporte=$_POST['action'];
$id_proveedor=$_POST['id_proveedor'];
$desde=$_POST['desde'];
$hasta=$_POST['hasta'];
ini_set('date.timezone','America/Guayaquil');

if($action == '1'){
	if (empty($id_proveedor)){
	$condicion_proveedor="";
	}else{
	$condicion_proveedor=" and enc_com.id_proveedor=".$id_proveedor;
	}
	$condicion_documento=" and enc_com.id_comprobante !=4";
	reporte_compras($con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento);
}
if($action == '2'){
	if (empty($id_proveedor)){
	$condicion_proveedor="";
	}else{
	$condicion_proveedor=" and enc_com.id_proveedor=".$id_proveedor;
	}
	$condicion_documento=" and enc_com.id_comprobante=4";
	reporte_compras($con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento);
}

if($action == '3'){
	echo "El reporte solo puede sacarlo en excel.";
}
if($action == '4'){
	echo "El reporte solo puede sacarlo en excel.";
}

function reporte_compras($con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento){
	$resultado = mysqli_query($con, "SELECT * FROM cuerpo_compra as cue_com 
				INNER JOIN encabezado_compra as enc_com ON enc_com.codigo_documento=cue_com.codigo_documento 
				LEFT JOIN proveedores as pro ON pro.id_proveedor=enc_com.id_proveedor 
				LEFT JOIN comprobantes_autorizados as com_aut ON com_aut.id_comprobante=enc_com.id_comprobante 
				WHERE enc_com.ruc_empresa = '".$ruc_empresa."' and cue_com.ruc_empresa = '".$ruc_empresa."' 
				and DATE_FORMAT(enc_com.fecha_compra, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' 
				and '".date("Y/m/d", strtotime($hasta))."' $condicion_proveedor $condicion_documento 
				group by enc_com.codigo_documento order by enc_com.fecha_compra asc");

	if(mysqli_num_rows($resultado) > 0 ){
	?>	
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>Fecha</th>
					<th>Proveedor</th>
					<th>Ruc</th>
					<th>Documento</th>
					<th>Número</th>
					<th>Base 0</th>
					<th>Base 12</th>
					<th>Base No iva</th>
					<th>Base Exento</th>
					<th>Iva 12</th>
					<th>ICE</th>
					<th>Descuento</th>
					<th>Propina</th>
					<th>Otros</th>
					<th>Total</th>
				</tr>
				<?php
				$total_compra=0;
				$suma_compras=0;
				$suma_base_cero=0;
				$suma_base_doce=0;
				$suma_base_catorce=0;
				$suma_base_noimp=0;
				$suma_base_exento=0;
				$suma_base_descuento=0;
				$suma_propina=0;
				$suma_otros_val=0;
				$suma_ice=0;
							
				while ($row=mysqli_fetch_array($resultado)){
						$suma_compras+= $row['total_compra'];
						$id_encabezado_compra=$row['id_encabezado_compra'];
						$fecha_compra=$row['fecha_compra'];
						$numero_documento=$row['numero_documento'];
						$nombre_proveedor=$row['razon_social'];
						$total_compra=$row['total_compra'];
						$ruc_proveedor=$row['ruc_proveedor'];
						$codigo_documento=$row['codigo_documento'];
						$tipo_documento=$row['comprobante'];						
					?>
					<tr>
						<td><?php echo date("d-m-Y", strtotime($fecha_compra)); ?></td>
						<td><?php echo $nombre_proveedor; ?></td>
						<td><?php echo $ruc_proveedor; ?></td>
						<td><?php echo $tipo_documento; ?></td>
						<td><?php echo $numero_documento; ?></td>
						
					
					<?php
					//para sacar el detalle de cada compras base cero
					$sql_detalle_compra = "SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and impuesto=2 and det_impuesto=0";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$subtotales=mysqli_fetch_array($resultado_detalle_compra);
					$base_cero= $subtotales['subtotal'];
					?>
						<td><?php echo number_format($base_cero,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base doce
					$sql_detalle_compra = "SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and impuesto=2 and det_impuesto=2";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$subtotales=mysqli_fetch_array($resultado_detalle_compra);
					$base_doce= $subtotales['subtotal'];
					
					?>
						<td><?php echo number_format($base_doce,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base no obj imp
					$sql_detalle_compra = "SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and impuesto=2 and det_impuesto=6";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$subtotales=mysqli_fetch_array($resultado_detalle_compra);
					$base_noimp= $subtotales['subtotal'];
					?>
						<td><?php echo number_format($base_noimp,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base exento
					$sql_detalle_compra = "SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and impuesto=2 and det_impuesto=7";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$subtotales=mysqli_fetch_array($resultado_detalle_compra);
					$base_exento= $subtotales['subtotal'];
					?>
						<td><?php echo number_format($base_exento,2,'.',''); ?></td>
						<td><?php echo number_format($base_doce * 0.12,2,'.',''); ?></td>
					<?php
					//para sacar el detalle de base ice
					$sql_detalle_compra = "SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and impuesto=3 ";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$subtotales=mysqli_fetch_array($resultado_detalle_compra);
					$base_ice= $subtotales['subtotal'];
					?>
						<td><?php echo number_format($base_ice,2,'.',''); ?></td>
					<?php
					
					//para sacar el detalle de descuento
					$sql_detalle_compra = "SELECT sum(descuento) as descuento FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$subtotales=mysqli_fetch_array($resultado_detalle_compra);
					$base_descuento= $subtotales['descuento'];
					
					//para sacar la propina y otros valores
					$sql_detalle_compra = "SELECT * FROM encabezado_compra WHERE codigo_documento ='".$codigo_documento."'  and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";      
					$resultado_detalle_compra = mysqli_query($con,$sql_detalle_compra);					
					$adicionales=mysqli_fetch_array($resultado_detalle_compra);
					$propina= $adicionales['propina'];
					$otros_val= $adicionales['otros_val'];
					
					?>	
						
						<td><?php echo number_format($base_descuento,2,'.',''); ?></td>
						<td><?php echo number_format($propina,2,'.',''); ?></td>
						<td><?php echo number_format($otros_val,2,'.',''); ?></td>
						<td><?php echo number_format($total_compra,2,'.',''); ?></td>
					</tr>
					<?php
					$suma_base_cero+= $base_cero;
					$suma_base_doce+= $base_doce;
					$suma_base_noimp+= $base_noimp;
					$suma_base_exento+= $base_exento;
					$suma_base_descuento+= $base_descuento;
					$suma_propina+= $propina;
					$suma_otros_val+= $otros_val;
					$suma_ice+= $base_ice;
					
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
						<td><?php echo number_format($suma_ice,2,'.',''); ?></td>
						<td><?php echo number_format($suma_base_descuento,2,'.',''); ?></td>
						<td><?php echo number_format($suma_propina,2,'.',''); ?></td>
						<td><?php echo number_format($suma_otros_val,2,'.',''); ?></td>
						<td><?php echo number_format($suma_compras,2,'.',''); ?></td>
					</tr>
										
				</table>
				</div>
			</div>
			<?php
	}else{
		echo "No hay datos para mostrar";
	}
}
?>