<?php
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
include("../conexiones/conectalogin.php");
$con = conenta_login();
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

	if($action == 'mejor_cliente'){
		 $desde = $_REQUEST['desde'];
		 $hasta = $_REQUEST['hasta'];
		 $cantidad = mysqli_real_escape_string($con,(strip_tags($_REQUEST['cantidad'], ENT_QUOTES)));
	
		$buscar_mas_vendidos = mysqli_query($con, "SELECT cli.nombre as cliente, sum(encfac.total_factura) as total 
		FROM encabezado_factura as encfac INNER JOIN clientes as cli ON cli.id = encfac.id_cliente 
		WHERE encfac.ruc_empresa='".$ruc_empresa."' and DATE_FORMAT(encfac.fecha_factura, '%Y/%m/%d') 
		between '" . date("Y/m/d", strtotime($desde)) . "' 
		and '" . date("Y/m/d", strtotime($hasta)) . "' group by encfac.id_cliente order by sum(encfac.total_factura) desc LIMIT 0, $cantidad");
//INNER JOIN clientes as cli ON cli.id=encfac.id_cliente LEFT JOIN unidad_medida as med ON med.id_medida=cuefac.id_medida_salida WHERE encfac.ruc_empresa='".$ruc_empresa."' and cuefac.ruc_empresa='".$ruc_empresa."'and encfac.fecha_factura BETWEEN '".$desde."' AND '".$hasta."' $condicion_cliente $condicion_producto group by cuefac.codigo_producto, encfac.id_cliente order by sum(cuefac.cantidad_factura) desc, encfac.id_cliente desc LIMIT 0, $cantidad");
			?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>Cliente</th>
					<th>Total</th>					
				</tr>
				<?php
				while ($row=mysqli_fetch_array($buscar_mas_vendidos)){
						$total_factura=$row['total'];
						$cliente=$row['cliente'];
					?>
					<tr>
						<td><?php echo $cliente; ?></td>					
						<td><?php echo $total_factura; ?></td>
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