<?php
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

	$tipo_reporte=$_POST['tipo_reporte'];
	$id_marca=$_POST['id_marca'];
	$id_producto=$_POST['id_producto'];
	$anio=$_POST['anio'];
	

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
	$nombre_reporte=" en unidades";
	}else{
	$condicion_datos="sum(cue_fac.subtotal_factura-descuento)  as cantidad";	
	$nombre_reporte=" en valores";
	}		
	$delete_tabla = mysqli_query($con, "DELETE FROM reportes_graficos WHERE ruc_empresa = '".$ruc_empresa."'");
	$detalle_ventas = mysqli_query($con, "INSERT INTO reportes_graficos (id_reporte, ruc_empresa, anio, mes, valor_entrada, valor_salida ) 
	(SELECT null, '".$ruc_empresa."', cue_fac.id_producto, month(enc_fac.fecha_factura) as mes, $condicion_datos, (sum(cue_fac.subtotal_factura-descuento)/sum(cue_fac.cantidad_factura)) as promedio FROM cuerpo_factura as cue_fac INNER JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and enc_fac.secuencial_factura=cue_fac.secuencial_factura WHERE cue_fac.ruc_empresa='".$ruc_empresa."' and enc_fac.ruc_empresa='".$ruc_empresa."' and year(enc_fac.fecha_factura)='".$anio."' $condicion_producto group by cue_fac.id_producto, month(enc_fac.fecha_factura))");//group by month(enc_fac.fecha_factura)

	$resultado = mysqli_query($con, "SELECT DISTINCT rep.anio, pro_ser.nombre_producto as nombre_producto, pro_ser.codigo_producto as codigo_producto FROM reportes_graficos as rep INNER JOIN productos_servicios as pro_ser ON rep.anio=pro_ser.id LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=pro_ser.id WHERE pro_ser.ruc_empresa='".$ruc_empresa."' $condicion_marca order by pro_ser.codigo_producto asc");

		if(mysqli_num_rows($resultado) > 0 ){			
			date_default_timezone_set('America/Guayaquil');
			if (PHP_SAPI == 'cli')
				die('Este archivo solo se puede ver desde un navegador web');

			/** Se agrega la libreria PHPExcel */
			require_once 'lib/PHPExcel/PHPExcel.php';

			// Se crea el objeto PHPExcel
			$objPHPExcel = new PHPExcel();

			// Se asignan las propiedades del libro
			$objPHPExcel->getProperties()->setCreator("CaMaGaRe") //Autor
								 ->setLastModifiedBy("CaMaGaRe") //Ultimo usuario que lo modificó
								 ->setTitle("Reporte Excel")
								 ->setSubject("Reporte Excel")
								 ->setDescription("Reporte ejecutivo")
								 ->setKeywords("Reporte ejecutivo")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
			$tituloReporte = "Reporte ejecutivo de ventas ". $nombre_reporte. " año ".$anio;
			$titulosColumnas = array('Código','Producto','Enero','Promedio Enero',
			'Febrero','Promedio Febrero','Marzo','Promedio Marzo','Abril','Promedio Abril',
			'Mayo','Promedio Mayo','Junio','Promedio Junio','Julio','Promedio Julio',
			'Agosto','Promedio Agosto','Septiembre','Promedio Septiembre',
			'Octubre','Promedio Octubre','Noviembre','Promedio Noviembre',
			'Diciembre','Promedio Diciembre','Total meses','Promedio General');
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:K1')
						->mergeCells('A2:K2')
						;
							
			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1',  $tituloEmpresa)
						->setCellValue('A2',  $tituloReporte)
						->setCellValue('A3',  $titulosColumnas[0])
						->setCellValue('B3',  $titulosColumnas[1])
						->setCellValue('C3',  $titulosColumnas[2])
						->setCellValue('D3',  $titulosColumnas[3])
						->setCellValue('E3',  $titulosColumnas[4])
						->setCellValue('F3',  $titulosColumnas[5])
						->setCellValue('G3',  $titulosColumnas[6])
						->setCellValue('H3',  $titulosColumnas[7])
						->setCellValue('I3',  $titulosColumnas[8])
						->setCellValue('J3',  $titulosColumnas[9])
						->setCellValue('K3',  $titulosColumnas[10])
						->setCellValue('L3',  $titulosColumnas[11])
						->setCellValue('M3',  $titulosColumnas[12])
						->setCellValue('N3',  $titulosColumnas[13])
						->setCellValue('O3',  $titulosColumnas[14])
						->setCellValue('P3',  $titulosColumnas[15])
						->setCellValue('Q3',  $titulosColumnas[16])
						->setCellValue('R3',  $titulosColumnas[17])
						->setCellValue('S3',  $titulosColumnas[18])
						->setCellValue('T3',  $titulosColumnas[19])
						->setCellValue('U3',  $titulosColumnas[20])
						->setCellValue('V3',  $titulosColumnas[21])
						->setCellValue('W3',  $titulosColumnas[22])
						->setCellValue('X3',  $titulosColumnas[23])
						->setCellValue('Y3',  $titulosColumnas[24])
						->setCellValue('Z3',  $titulosColumnas[25])
						->setCellValue('AA3',  $titulosColumnas[26])
						->setCellValue('AB3',  $titulosColumnas[27])
						;	
			$i = 4;
			
$delete_tabla = mysqli_query($con, "DELETE FROM reportes_graficos WHERE ruc_empresa = '".$ruc_empresa."'");
$detalle_ventas = mysqli_query($con, "INSERT INTO reportes_graficos (id_reporte, ruc_empresa, anio, mes, valor_entrada, valor_salida ) 
(SELECT null, '".$ruc_empresa."', cue_fac.id_producto, month(enc_fac.fecha_factura) as mes, $condicion_datos, (sum(cue_fac.subtotal_factura-descuento)/sum(cue_fac.cantidad_factura)) as promedio FROM cuerpo_factura as cue_fac INNER JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and enc_fac.secuencial_factura=cue_fac.secuencial_factura WHERE cue_fac.ruc_empresa='".$ruc_empresa."' and enc_fac.ruc_empresa='".$ruc_empresa."' and year(enc_fac.fecha_factura)='".$anio."' $condicion_producto group by cue_fac.id_producto, month(enc_fac.fecha_factura))");//group by month(enc_fac.fecha_factura)

$resultado_productos = mysqli_query($con, "SELECT DISTINCT rep.anio, pro_ser.nombre_producto as nombre_producto, pro_ser.codigo_producto as codigo_producto FROM reportes_graficos as rep INNER JOIN productos_servicios as pro_ser ON rep.anio=pro_ser.id LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=pro_ser.id WHERE pro_ser.ruc_empresa='".$ruc_empresa."' $condicion_marca order by pro_ser.codigo_producto asc");

		
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
				
				
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  "=\"" . $codigo . "\"")
			->setCellValue('B'.$i,  strtoupper($producto))
			->setCellValue('C'.$i,  $mes_enero=='1'?number_format($row_enero['cantidad'],$decimal,'.',''):"")
			->setCellValue('D'.$i,  $mes_enero=='1'?number_format($precio_ene,2,'.',''):"")
			->setCellValue('E'.$i,  $mes_febrero=='2'?number_format($row_febrero['cantidad'],$decimal,'.',''):"")
			->setCellValue('F'.$i,  $mes_febrero=='2'?number_format($precio_feb,2,'.',''):"")
			->setCellValue('G'.$i,  $mes_marzo=='3'?number_format($row_marzo['cantidad'],$decimal,'.',''):"")
			->setCellValue('H'.$i,  $mes_marzo=='3'?number_format($precio_mar,2,'.',''):"")
			->setCellValue('I'.$i,  $mes_abril=='4'?number_format($row_abril['cantidad'],$decimal,'.',''):"")
			->setCellValue('J'.$i,  $mes_abril=='4'?number_format($precio_abr,2,'.',''):"")
			->setCellValue('K'.$i,  $mes_mayo=='5'?number_format($row_mayo['cantidad'],$decimal,'.',''):"")
			->setCellValue('L'.$i,  $mes_mayo=='5'?number_format($precio_may,2,'.',''):"")
			->setCellValue('M'.$i,  $mes_junio=='6'?number_format($row_junio['cantidad'],$decimal,'.',''):"")
			->setCellValue('N'.$i,  $mes_junio=='6'?number_format($precio_jun,2,'.',''):"")
			->setCellValue('O'.$i,  $mes_julio=='7'?number_format($row_julio['cantidad'],$decimal,'.',''):"")
			->setCellValue('P'.$i,  $mes_julio=='7'?number_format($precio_jul,2,'.',''):"")
			->setCellValue('Q'.$i,  $mes_agosto=='8'?number_format($row_agosto['cantidad'],$decimal,'.',''):"")
			->setCellValue('R'.$i,  $mes_agosto=='8'?number_format($precio_ago,2,'.',''):"")
			->setCellValue('S'.$i,  $mes_septiembre=='9'?number_format($row_septiembre['cantidad'],$decimal,'.',''):"")
			->setCellValue('T'.$i,  $mes_septiembre=='9'?number_format($precio_sep,2,'.',''):"")
			->setCellValue('U'.$i,  $mes_octubre=='10'?number_format($row_octubre['cantidad'],$decimal,'.',''):"")
			->setCellValue('V'.$i,  $mes_octubre=='10'?number_format($precio_oct,2,'.',''):"")
			->setCellValue('W'.$i,  $mes_noviembre=='11'?number_format($row_noviembre['cantidad'],$decimal,'.',''):"")
			->setCellValue('X'.$i,  $mes_noviembre=='11'?number_format($precio_nov,2,'.',''):"")
			->setCellValue('Y'.$i,  $mes_diciembre=='12'?number_format($row_diciembre['cantidad'],$decimal,'.',''):"")
			->setCellValue('Z'.$i,  $mes_diciembre=='12'?number_format($precio_dic,2,'.',''):"")
			->setCellValue('AA'.$i,  number_format($suma_total,$decimal,'.',''))
			->setCellValue('AB'.$i,  number_format($suma_cantidad_por_precio/$suma_total,2,'.',''))
			;
				$i++;
				}
					
			for($i = 'A'; $i <= 'AB'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Ventas');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReportEjecutivo.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}

?>