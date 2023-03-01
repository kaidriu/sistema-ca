<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

//$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
$tipo_reporte=$_POST['tipo_reporte'];
$anio=$_POST['anio'];
$id_marca=$_POST['id_marca'];
$id_producto=$_POST['id_producto'];
ini_set('date.timezone','America/Guayaquil');

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

if ($tipo_reporte=='1'){
$condicion_datos="sum(cue_fac.cantidad_factura) as cantidad";
$opcionUno="Unidades";
$opcionDos="Precio promedio";
}else{
$condicion_datos="sum(cue_fac.subtotal_factura-descuento) as cantidad";
$opcionUno="Venta";
$opcionDos="Precio promedio";	
}
//limpiar la tabla

$delete_tabla = mysqli_query($con, "DELETE FROM reportes_graficos WHERE ruc_empresa = '".$ruc_empresa."'");
$detalle_ventas = mysqli_query($con, "INSERT INTO reportes_graficos (id_reporte, ruc_empresa, anio, mes, valor_entrada, valor_salida ) 
(SELECT null, '".$ruc_empresa."', cue_fac.id_producto, month(enc_fac.fecha_factura) as mes, $condicion_datos, (sum(cue_fac.subtotal_factura-descuento)/sum(cue_fac.cantidad_factura)) as promedio FROM cuerpo_factura as cue_fac INNER JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and enc_fac.secuencial_factura=cue_fac.secuencial_factura WHERE cue_fac.ruc_empresa='".$ruc_empresa."' and enc_fac.ruc_empresa='".$ruc_empresa."' and year(enc_fac.fecha_factura)='".$anio."' $condicion_producto group by cue_fac.id_producto, month(enc_fac.fecha_factura))");//group by month(enc_fac.fecha_factura)

$resultado_productos = mysqli_query($con, "SELECT DISTINCT rep.anio, pro_ser.nombre_producto as nombre_producto, pro_ser.codigo_producto as codigo_producto FROM reportes_graficos as rep INNER JOIN productos_servicios as pro_ser ON rep.anio=pro_ser.id LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=pro_ser.id WHERE pro_ser.ruc_empresa='".$ruc_empresa."' $condicion_marca order by pro_ser.codigo_producto asc");

		?>	
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-bordered table-hover">
				<tr  class="info">
					<th>Código</th>
					<th>Producto</th>
					<th>Detalle</th>
					<th>Ene</th>
					<th>Feb</th>
					<th>Mar</th>
					<th>Abr</th>
					<th>May</th>
					<th>Jun</th>
					<th>Jul</th>
					<th>Ago</th>
					<th>Sep</th>
					<th>Oct</th>
					<th>Nov</th>
					<th>Dic</th>
					<th>Suma General</th>
					<th>Promedio General</th>
				</tr>
				<?php

				$suma_ene=0;
				$suma_feb=0;
				$suma_mar=0;
				$suma_abr=0;
				$suma_may=0;
				$suma_jun=0;
				$suma_jul=0;
				$suma_ago=0;
				$suma_sep=0;
				$suma_oct=0;
				$suma_nov=0;
				$suma_dic=0;
				$suma_general=0;
				$suma_cantidad_por_precio=0;

				while ($row=mysqli_fetch_array($resultado_productos)){
						$codigo= $row['codigo_producto'];
						$producto=$row['nombre_producto'];
						$id_producto=$row['anio'];
							
							$resultado_enero = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='1' group by reporte.mes");
							$row_enero=mysqli_fetch_array($resultado_enero);
							$mes_enero=$row_enero['mes'];
							$suma_ene +=$row_enero['cantidad'];
							$precio_ene=$row_enero['precio'];
														
							$resultado_febrero = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='2' group by reporte.mes");
							$row_febrero=mysqli_fetch_array($resultado_febrero);
							$mes_febrero=$row_febrero['mes'];
							$suma_feb +=$row_febrero['cantidad'];
							$precio_feb=$row_febrero['precio'];
														
							$resultado_marzo = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='3' group by reporte.mes");
							$row_marzo=mysqli_fetch_array($resultado_marzo);
							$mes_marzo=$row_marzo['mes'];
							$suma_mar +=$row_marzo['cantidad'];
							$precio_mar=$row_marzo['precio'];
							
							$resultado_abril = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='4' group by reporte.mes");
							$row_abril=mysqli_fetch_array($resultado_abril);
							$mes_abril=$row_abril['mes'];
							$suma_abr +=$row_abril['cantidad'];
							$precio_abr=$row_abril['precio'];
														
							$resultado_mayo = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='5' group by reporte.mes");
							$row_mayo=mysqli_fetch_array($resultado_mayo);
							$mes_mayo=$row_mayo['mes'];
							$suma_may +=$row_mayo['cantidad'];
							$precio_may=$row_mayo['precio'];
														
							$resultado_junio = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='6' group by reporte.mes");
							$row_junio=mysqli_fetch_array($resultado_junio);
							$mes_junio=$row_junio['mes'];
							$suma_jun +=$row_junio['cantidad'];
							$precio_jun=$row_junio['precio'];
														
							$resultado_julio = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='7' group by reporte.mes");
							$row_julio=mysqli_fetch_array($resultado_julio);
							$mes_julio=$row_julio['mes'];
							$suma_jul +=$row_julio['cantidad'];
							$precio_jul=$row_julio['precio'];
														
							$resultado_agosto = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='8' group by reporte.mes");
							$row_agosto=mysqli_fetch_array($resultado_agosto);
							$mes_agosto=$row_agosto['mes'];
							$suma_ago +=$row_agosto['cantidad'];
							$precio_ago=$row_agosto['precio'];
														
							$resultado_septiembre = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='9' group by reporte.mes");
							$row_septiembre=mysqli_fetch_array($resultado_septiembre);
							$mes_septiembre=$row_septiembre['mes'];
							$suma_sep +=$row_septiembre['cantidad'];
							$precio_sep=$row_septiembre['precio'];
														
							$resultado_octubre = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='10' group by reporte.mes");
							$row_octubre=mysqli_fetch_array($resultado_octubre);
							$mes_octubre=$row_octubre['mes'];
							$suma_oct +=$row_octubre['cantidad'];
							$precio_oct=$row_octubre['precio'];
														
							$resultado_noviembre = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='11' group by reporte.mes");
							$row_noviembre=mysqli_fetch_array($resultado_noviembre);
							$mes_noviembre=$row_noviembre['mes'];
							$suma_nov +=$row_noviembre['cantidad'];
							$precio_nov=$row_noviembre['precio'];
														
							$resultado_diciembre = mysqli_query($con, "SELECT sum(reporte.valor_entrada) as cantidad, sum(reporte.valor_salida) as precio, reporte.mes as mes FROM reportes_graficos as reporte WHERE reporte.ruc_empresa='".$ruc_empresa."' and reporte.anio='".$id_producto."' and mes ='12' group by reporte.mes");
							$row_diciembre=mysqli_fetch_array($resultado_diciembre);
							$mes_diciembre=$row_diciembre['mes'];
							$suma_dic +=$mes_diciembre['cantidad'];
							$precio_dic=$mes_diciembre['precio'];
							
							if ($tipo_reporte=='1'){
							$decimal=0;
							}else{
							$decimal=2;
							}
							
							$suma_total=$row_enero['cantidad']+ $row_febrero['cantidad'] + $row_marzo['cantidad'] + $row_abril['cantidad'] + $row_mayo['cantidad'] + $row_junio['cantidad'] + $row_julio['cantidad'] + $row_agosto['cantidad'] + $row_septiembre['cantidad'] + $row_octubre['cantidad'] + $row_noviembre['cantidad'] + $row_diciembre['cantidad'];							
							$suma_general +=$suma_total;
							
							$total_cantidad_por_precio=($row_enero['cantidad']*$row_enero['precio'])
							+ ($row_febrero['cantidad']* $row_febrero['precio'])
							+ ($row_marzo['cantidad']* $row_marzo['precio'])
							+ ($row_abril['cantidad']* $row_abril['precio'])
							+ ($row_mayo['cantidad'] * $row_mayo['precio'])
							+ ($row_junio['cantidad'] * $row_junio['precio'])
							+ ($row_julio['cantidad']* $row_julio['precio'])
							+ ($row_agosto['cantidad']* $row_agosto['precio'])
							+ ($row_septiembre['cantidad']* $row_septiembre['precio'])
							+ ($row_octubre['cantidad']* $row_octubre['precio'])
							+ ($row_noviembre['cantidad']* $row_noviembre['precio'])
							+ ($row_diciembre['cantidad']* $row_diciembre['precio']);							
							$suma_cantidad_por_precio =$total_cantidad_por_precio;
				
							?>
					<tr>
						<td rowspan="2" align="left"><?php echo $codigo; ?></td>
						<td rowspan="2" align="left"><?php echo $producto; ?></td>
						<td><?php echo $opcionUno; ?></td>
						<td align="right" title="Enero"><?php echo $mes_enero=='1'?number_format($row_enero['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Febrero"><?php echo $mes_febrero=='2'?number_format($row_febrero['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Marzo"><?php echo $mes_marzo=='3'?number_format($row_marzo['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Abril"><?php echo $mes_abril=='4'?number_format($row_abril['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Mayo"><?php echo $mes_mayo=='5'?number_format($row_mayo['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Junio"><?php echo $mes_junio=='6'?number_format($row_junio['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Julio"><?php echo $mes_julio=='7'?number_format($row_julio['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Agosto"><?php echo $mes_agosto=='8'?number_format($row_agosto['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Septiembre"><?php echo $mes_septiembre=='9'?number_format($row_septiembre['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Octubre"><?php echo $mes_octubre=='10'?number_format($row_octubre['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Noviembre"><?php echo $mes_noviembre=='11'?number_format($row_noviembre['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" title="Diciembre"><?php echo $mes_diciembre=='12'?number_format($row_diciembre['cantidad'],$decimal,'.',''):"";?></td>
						<td align="right" ><?php echo number_format($suma_total,$decimal,'.','');?></td>
						<td align="right" ></td>
					</tr>
					<tr>
					<td><?php echo $opcionDos; ?></td>
						<td align="right" title="Enero"><?php echo $mes_enero=='1'?number_format($precio_ene,2,'.',''):"";?></td>
						<td align="right" title="Febrero"><?php echo $mes_febrero=='2'?number_format($precio_feb,2,'.',''):"";?></td>
						<td align="right" title="Marzo"><?php echo $mes_marzo=='3'?number_format($precio_mar,2,'.',''):"";?></td>
						<td align="right" title="Abril"><?php echo $mes_abril=='4'?number_format($precio_abr,2,'.',''):"";?></td>
						<td align="right" title="Mayo"><?php echo $mes_mayo=='5'?number_format($precio_may,2,'.',''):"";?></td>
						<td align="right" title="Junio"><?php echo $mes_junio=='6'?number_format($precio_jun,2,'.',''):"";?></td>
						<td align="right" title="Julio"><?php echo $mes_julio=='7'?number_format($precio_jul,2,'.',''):"";?></td>
						<td align="right" title="Agosto"><?php echo $mes_agosto=='8'?number_format($precio_ago,2,'.',''):"";?></td>
						<td align="right" title="Septiembre"><?php echo $mes_septiembre=='9'?number_format($precio_sep,2,'.',''):"";?></td>
						<td align="right" title="Octubre"><?php echo $mes_octubre=='10'?number_format($precio_oct,2,'.',''):"";?></td>
						<td align="right" title="Noviembre"><?php echo $mes_noviembre=='11'?number_format($precio_nov,2,'.',''):"";?></td>
						<td align="right" title="Diciembre"><?php echo $mes_diciembre=='12'?number_format($precio_dic,2,'.',''):"";?></td>
						<td align="right" ></td>
						<td align="right" ><?php echo number_format($suma_cantidad_por_precio/$suma_total,2,'.','');?></td>
					</tr>
					<?php
				}
				?>
					<tr>
					<td colspan="3" align="right">Total <?php echo $opcionUno ?></td>
					<td align="right"><?php echo number_format($suma_ene,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_feb,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_mar,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_abr,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_may,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_jun,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_jul,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_ago,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_sep,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_oct,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_nov,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_dic,$decimal,'.','');?></td>
					<td align="right"><?php echo number_format($suma_general,$decimal,'.','');?></td>
					<td></td>
					</tr>
					
				</table>
				</div>
			</div>