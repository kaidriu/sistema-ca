<?php
	include("../conexiones/conectalogin.php");
	require_once("../helpers/helpers.php"); 
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

	$tipo_reporte=$_POST['tipo_reporte'];
	//$id_cliente=$_POST['id_cliente'];
	//$id_producto=$_POST['id_producto'];
	$desde=$_POST['fecha_desde'];
	$hasta=$_POST['fecha_hasta'];
	//$id_marca=$_POST['id_marca'];

//para facturas
	if($tipo_reporte=='1'){

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


	//$resultado = mysqli_query($con, "SELECT * FROM cuerpo_factura as cue_fac INNER JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and enc_fac.secuencial_factura=cue_fac.secuencial_factura INNER JOIN clientes as cli ON cli.id=enc_fac.id_cliente LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=cue_fac.id_producto LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=cue_fac.id_producto WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' $condicion_cliente $condicion_producto $condicion_marca group by cue_fac.serie_factura, cue_fac.secuencial_factura");
	$total_registros=0;
	foreach ($resultado_consolidado as $registros){
	$total_registros += mysqli_num_rows($registros);
	}

		if(($total_registros) > 0 ){			
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
								 ->setDescription("Reporte de ventas")
								 ->setKeywords("Reporte ventas")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
			$tituloReporte = "Reporte de ventas consolidado de todas las sucursales";
			$titulosColumnas = array('Fecha','Cliente','Ruc','Factura','Base 0','Base 12','Base No iva','Base Exento','Iva 12','Base ice','Descuento','Propina','Otros','Total','Usuario');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:D1')
						->mergeCells('A2:D2')
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
						;	
			$i = 4;
			$suma_base_cero=0;
			$suma_base_doce=0;
			$suma_base_noimp=0;
			$suma_base_exento=0;
			$suma_base_descuento=0;
			$suma_propina=0;
			$suma_tasa_turistica=0;
			$suma_total_factura=0;
			$suma_iva_base_doce=0;
			foreach($resultado_consolidado as $resultado){
			while ($row = mysqli_fetch_array($resultado)) {
				$suma_factura+= $row['total_factura'];
				$id_encabezado_factura=$row['id_encabezado_factura'];
				$fecha_factura=$row['fecha_factura'];
				$serie=$row['serie_factura'];
				$secuencial=$row['secuencial_factura'];
				$nombre_cliente_factura=$row['nombre'];
				$total_factura=$row['total_factura'];
				$ruc_cliente=$row['ruc'];
				$tasa_turistica=$row['tasa_turistica'];
				$propina=$row['propina'];
				$nombre_usuario = $row['usuario'];
				$base_cero = $row['subtotal_cero']-$row['descuento_cero'];
				$base_doce = $row['subtotal_doce']-$row['descuento_doce'];
				$base_noimp = $row['subtotal_no']-$row['descuento_no'];
				$base_exento = $row['subtotal_exento']-$row['descuento_exento'];
				$base_descuento=$row['descuento_cero']+$row['descuento_doce']+$row['descuento_no']+$row['descuento_exento'];
				$suma_base_cero+= $base_cero;
				$suma_base_doce+= number_format($base_doce,2,'.','');
				$suma_iva_base_doce+= number_format($base_doce*0.12,2,'.','');
				$suma_base_noimp+=$base_noimp;
				$suma_base_exento+= $base_exento;
				$suma_base_descuento+= $base_descuento;
										
						$suma_propina+=$row['propina'];
						$suma_tasa_turistica+=$row['tasa_turistica'];
						$suma_total_factura+=$row['total_factura'];

			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  date("d/m/Y", strtotime($row['fecha_factura'])))
			->setCellValue('B'.$i,  strtoupper($row['nombre']))
			->setCellValue('C'.$i,  "=\"" . $row['ruc'] . "\"")
			->setCellValue('D'.$i,  $row['serie_factura'].'-'.$row['secuencial_factura'])
			->setCellValue('E'.$i,  number_format($base_cero,2,'.',''))
			->setCellValue('F'.$i,  number_format($base_doce,2,'.',''))
			->setCellValue('G'.$i,  number_format($base_noimp,2,'.',''))
			->setCellValue('H'.$i,  number_format($base_exento,2,'.',''))
			->setCellValue('I'.$i,  number_format($base_doce*0.12,2,'.',''))
			->setCellValue('J'.$i,  number_format('0',2,'.',''))
			->setCellValue('K'.$i,  number_format($base_descuento,2,'.',''))
			->setCellValue('L'.$i,  number_format($row['propina'],2,'.',''))
			->setCellValue('M'.$i,  number_format($row['tasa_turistica'],2,'.',''))
			->setCellValue('N'.$i,  number_format($row['total_factura'],2,'.',''))
			->setCellValue('O'.$i,  strtoupper($nombre_usuario))
			;

			$objPHPExcel->getActiveSheet()->getStyle('E'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('I'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('J'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('K'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('L'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('M'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$objPHPExcel->getActiveSheet()->getStyle('N'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
	
			$i++;
							
			}
		}
			$t=$i+1;
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('D'.$t,  'Totales')
						->setCellValue('E'.$t,  number_format($suma_base_cero,2,'.',''))
						->setCellValue('F'.$t,  number_format($suma_base_doce,2,'.',''))
						->setCellValue('G'.$t,  number_format($suma_base_noimp,2,'.',''))
						->setCellValue('H'.$t,  number_format($suma_base_exento,2,'.',''))
						->setCellValue('I'.$t,  number_format($suma_iva_base_doce,2,'.',''))
						->setCellValue('J'.$t,  number_format(0,2,'.',''))
						->setCellValue('K'.$t,  number_format($suma_base_descuento,2,'.',''))
						->setCellValue('L'.$t,  number_format($suma_propina,2,'.',''))
						->setCellValue('M'.$t,  number_format($suma_tasa_turistica,2,'.',''))
						->setCellValue('N'.$t,  number_format($suma_total_factura,2,'.',''))
						;
								
			for($i = 'A'; $i <= 'P'; $i++){
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
			header('Content-Disposition: attachment;filename="Reportedeventas.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}
	}
	
	//para notas de credito
	if($tipo_reporte=='2'){
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

	$resultado = mysqli_query($con, "SELECT * FROM cuerpo_nc as cue_nc INNER JOIN encabezado_nc as enc_nc ON enc_nc.serie_nc=cue_nc.serie_nc and enc_nc.secuencial_nc=cue_nc.secuencial_nc INNER JOIN clientes as cli ON cli.id=enc_nc.id_cliente LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=cue_nc.id_producto LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=cue_nc.id_producto WHERE mid(enc_nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(cue_nc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and DATE_FORMAT(enc_nc.fecha_nc, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' $condicion_cliente $condicion_producto $condicion_marca group by cue_nc.serie_nc, cue_nc.secuencial_nc");

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
								 ->setDescription("Reporte de notas de crédito")
								 ->setKeywords("reporte de notas de crédito")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
			$tituloReporte = "Reporte de notas de crédito";
			$titulosColumnas = array('Fecha','Cliente','Ruc','NC','Factura Afectada','Base 0','Base 12','Base 14','Base No iva','Base Exento','Iva 12','Iva 14','Base ice','Descuento','Total');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:O1')
						->mergeCells('A2:O2')
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
						;
					
			$i = 4;
			
			while ($fila = mysqli_fetch_array($resultado)) {
					$serie=$fila['serie_nc'];
					$secuencial=$fila['secuencial_nc'];
		
						//para sacar el detalle de base cero
						$sql_cero = mysqli_query($con, "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 0 and serie_nc = '".$serie."' and secuencial_nc = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");      
						$subtotales=mysqli_fetch_array($sql_cero);
						$base_cero= $subtotales['subtotal'];
						
						//para sacar el detalle de base doce
						$sql_doce = mysqli_query($con,"SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 2 and serie_nc = '".$serie."' and secuencial_nc = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
						$subtotales=mysqli_fetch_array($sql_doce);
						$base_doce= $subtotales['subtotal'];
						//para sacar el detalle de base catorce
						$sql_catorce = mysqli_query($con, "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 3 and serie_nc = '".$serie."' and secuencial_nc = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
						$subtotales=mysqli_fetch_array($sql_catorce);
						$base_catorce= $subtotales['subtotal'];
						//para sacar el detalle de base no obj imp
						$sql_noimp = mysqli_query($con, "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 6 and serie_nc = '".$serie."' and secuencial_nc = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
						$subtotales=mysqli_fetch_array($sql_noimp);
						$base_noimp= $subtotales['subtotal'];
						//para sacar el detalle de base no obj imp
						$sql_exento = mysqli_query($con, "SELECT sum(subtotal_nc-descuento) as subtotal FROM cuerpo_nc where tarifa_iva = 7 and serie_nc = '".$serie."' and secuencial_nc = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
						$subtotales=mysqli_fetch_array($sql_exento);
						$base_exento= $subtotales['subtotal'];
						
						//para sacar el detalle de descuento
						$sql_descuento = mysqli_query($con,"SELECT sum(descuento) as descuento FROM cuerpo_nc where serie_nc = '".$serie."' and secuencial_nc = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");      
						$subtotales=mysqli_fetch_array($sql_descuento);
						$base_descuento = $subtotales['descuento'];
						
						
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  date("d/m/Y", strtotime($fila['fecha_nc'])))
			->setCellValue('B'.$i,  strtoupper($fila['nombre']))
			->setCellValue('C'.$i,  "=\"" . $fila['ruc'] . "\"")
			->setCellValue('D'.$i,  $fila['serie_nc'].'-'.$fila['secuencial_nc'])
			->setCellValue('E'.$i,  $fila['factura_modificada'])
			->setCellValue('F'.$i,  $base_cero)
			->setCellValue('G'.$i,  $base_doce)
			->setCellValue('H'.$i,  $base_catorce)
			->setCellValue('I'.$i,  $base_noimp)
			->setCellValue('J'.$i,  $base_exento)
			->setCellValue('K'.$i,  $base_doce*0.12)
			->setCellValue('L'.$i,  $base_catorce*0.14)
			->setCellValue('M'.$i,  '0')
			->setCellValue('N'.$i,  $base_descuento)
			->setCellValue('O'.$i,  $fila['total_nc'])
			;
						$i++;
			}
			$t=$i+1;
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('C'.$t,  'Totales');
								
			for($i = 'A'; $i <= 'O'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('nc');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReportedeNC.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}
	}
	
	
//reporte detalle de ventas
if($tipo_reporte=='3'){
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

$resultado = mysqli_query($con, "SELECT enc_fac.fecha_factura as fecha_factura, 
				cue_fac.serie_factura as serie_factura, cue_fac.secuencial_factura as secuencial_factura,
				cli.nombre as nombre_cliente, enc_fac.total_factura as total_factura, 
				cli.ruc as ruc, cue_fac.cantidad_factura as cantidad_factura,
				cue_fac.nombre_producto as nombre_producto, cue_fac.codigo_producto as codigo_producto,
				cue_fac.valor_unitario_factura as valor_unitario_factura, 
				cue_fac.descuento as descuento, cue_fac.subtotal_factura as subtotal_factura,
				tip_pro.nombre as nombre_produccion, tar_iva.tarifa as tarifa, tar_iva.porcentaje_iva as porcentaje_iva, cue_fac.lote as lote, 
				cue_fac.vencimiento as vencimiento, med.nombre_medida as medida, bod.nombre_bodega as bodega FROM cuerpo_factura as cue_fac 
				LEFT JOIN encabezado_factura as enc_fac ON enc_fac.serie_factura=cue_fac.serie_factura and enc_fac.secuencial_factura=cue_fac.secuencial_factura 
				LEFT JOIN clientes as cli ON cli.id=enc_fac.id_cliente 
				LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=cue_fac.id_producto 
				LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=cue_fac.id_producto 
				LEFT JOIN tipo_produccion as tip_pro ON tip_pro.codigo=cue_fac.tipo_produccion 
				LEFT JOIN tarifa_iva as tar_iva ON tar_iva.codigo=cue_fac.tarifa_iva LEFT JOIN unidad_medida as med ON med.id_medida = cue_fac.id_medida_salida LEFT JOIN bodega as bod ON bod.id_bodega=cue_fac.id_bodega WHERE mid(enc_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and mid(cue_fac.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and DATE_FORMAT(enc_fac.fecha_factura, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' $condicion_cliente $condicion_producto $condicion_marca ");//group by cue_fac.serie_factura, cue_fac.secuencial_factura
				$suma_total_factura=0;
				$suma_cantidad =0;
				$suma_valor_unitario =0;
				$suma_subtotal_factura =0;
				$suma_descuento =0;
				$suma_iva=0;

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
								 ->setDescription("Reporte de ventas")
								 ->setKeywords("reporte ventas")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
			$tituloReporte = "Reporte de ventas detallado";
			$titulosColumnas = array('Fecha','Cliente','Ruc','Factura','Código','Detalle','Tipo','Tarifa','Cantidad','Valor unitario','Descuento','Subtotal','IVA','Total','Lote','Medida','vencimiento','Bodega');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:R1')
						->mergeCells('A2:R2')
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
						;
			$i = 4;
		
			while ($fila = mysqli_fetch_array($resultado)) {
					$fecha_factura=$fila['fecha_factura'];
					$serie_factura=$fila['serie_factura'];
					$secuencial_factura=$fila['secuencial_factura'];
					$nombre_cliente_factura=$fila['nombre_cliente'];
					$ruc_cliente=$fila['ruc'];
					$cantidad= $fila['cantidad_factura'];
					$suma_cantidad += $fila['cantidad_factura'];
					$producto= preg_replace('/"/', "", $fila['nombre_producto']);
					$codigo= $fila['codigo_producto'];
					$valor_unitario= $fila['valor_unitario_factura'];
					$suma_valor_unitario += $fila['valor_unitario_factura'];
					$descuento= $fila['descuento'];
					$suma_descuento += $fila['descuento'];
					$subtotal_factura= $fila['subtotal_factura']-$descuento;
					$suma_subtotal_factura += $fila['subtotal_factura']-$descuento;
					$tipo_produccion= $fila['nombre_produccion'];
					$tarifa_iva= $fila['tarifa'];
					$porcentaje_iva= $fila['porcentaje_iva']/100;
					$suma_iva +=number_format(($subtotal_factura * $porcentaje_iva),2,'.','');
					$lote= $fila['lote'];
					$medida= $fila['medida'];
					$vencimiento= $fila['vencimiento'];
					$bodega= $fila['bodega'];
					$total_factura= ($fila['subtotal_factura'] - $fila['descuento'] + ($subtotal_factura * $porcentaje_iva));
					$suma_total_factura +=$total_factura;
		
		
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  date("d/m/Y", strtotime($fila['fecha_factura'])))
			->setCellValue('B'.$i,  $nombre_cliente_factura)
			->setCellValue('C'.$i,  "=\"" . $fila['ruc'] . "\"")
			->setCellValue('D'.$i,  $fila['serie_factura'].'-'.str_pad($fila['secuencial_factura'],9,"000000000",STR_PAD_LEFT))
			->setCellValue('E'.$i,  "=\"" .$codigo. "\"")
			->setCellValue('F'.$i,  "=\"" .$producto. "\"")
			->setCellValue('G'.$i,  $tipo_produccion)
			->setCellValue('H'.$i,  $tarifa_iva)
			->setCellValue('I'.$i,  number_format($cantidad,4,'.',''))
			->setCellValue('J'.$i,  number_format($valor_unitario,4,'.',''))
			->setCellValue('K'.$i,  number_format($descuento,2,'.',''))
			->setCellValue('L'.$i,  number_format($subtotal_factura,2,'.',''))
			->setCellValue('M'.$i,  number_format(($subtotal_factura * $porcentaje_iva),2,'.',''))
			->setCellValue('N'.$i,  number_format($total_factura,2,'.',''))
			->setCellValue('O'.$i,  $lote)
			->setCellValue('P'.$i,  $medida)
			->setCellValue('Q'.$i,  date("d-m-Y", strtotime($vencimiento)))
			->setCellValue('R'.$i,  $bodega)
			;
			$i=$i+1;
			}//fin del while

			$t=$i+1;
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('H'.$t,  'Totales')
				->setCellValue('I'.$t,  number_format($suma_cantidad,4,'.',''))
				->setCellValue('J'.$t,  number_format($suma_valor_unitario,4,'.',''))
				->setCellValue('K'.$t,  number_format($suma_descuento,2,'.',''))
				->setCellValue('L'.$t,  number_format($suma_subtotal_factura,2,'.',''))
				->setCellValue('M'.$t,  number_format($suma_iva,2,'.',''))
				->setCellValue('N'.$t,  number_format($suma_total_factura,2,'.',''))
				;
			
			for($i = 'A'; $i <= 'R'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('DetalleVentas');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			// Inmovilizar paneles 
			//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReporteDetalleVentas.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			print_r('No hay resultados para mostrar');
		}
	}
	
	
	//reporte detalle de NC
if($tipo_reporte=='4'){
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
								 ->setDescription("Reporte de NC")
								 ->setKeywords("Reporte NC")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
			$tituloReporte = "Reporte de NC detallado";
			$titulosColumnas = array('Fecha','Cliente','Ruc','NC','Factura Modificada','Motivo','Código','Detalle','Tipo','Tarifa','Cantidad','Valor unitario','Descuento','Subtotal','IVA','Total');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:P1')
						->mergeCells('A2:P2')
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
						;
			$i = 4;
	
			while ($row = mysqli_fetch_array($resultado)) {
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
						
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  date("d/m/Y", strtotime($fecha_nc)))
			->setCellValue('B'.$i,  $nombre_cliente_nc)
			->setCellValue('C'.$i,  "=\"" . $ruc_cliente . "\"")
			->setCellValue('D'.$i,  $serie_nc.'-'.str_pad($secuencial_nc,9,"000000000",STR_PAD_LEFT))
			->setCellValue('E'.$i,  $factura_modificada)
			->setCellValue('F'.$i,  $motivo)
			->setCellValue('G'.$i,  "=\"" .$codigo. "\"")
			->setCellValue('H'.$i,  "=\"" .$producto. "\"")
			->setCellValue('I'.$i,  $tipo_produccion)
			->setCellValue('J'.$i,  $tarifa_iva)		
			->setCellValue('K'.$i,  number_format($cantidad,4,'.',''))
			->setCellValue('L'.$i,  number_format($valor_unitario,4,'.',''))
			->setCellValue('M'.$i,  number_format($descuento,2,'.',''))
			->setCellValue('N'.$i,  number_format($subtotal_nc,2,'.',''))
			->setCellValue('O'.$i,  number_format(($subtotal_nc * $porcentaje_iva),2,'.',''))
			->setCellValue('P'.$i,  number_format($total_nc,2,'.',''))
			;
			$i=$i+1;
			}//fin del while

			$t=$i+1;
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('J'.$t,  'Totales')
				->setCellValue('K'.$t,  number_format($suma_cantidad,4,'.',''))
				->setCellValue('L'.$t,  number_format($suma_valor_unitario,4,'.',''))
				->setCellValue('M'.$t,  number_format($suma_descuento,2,'.',''))
				->setCellValue('N'.$t,  number_format($suma_subtotal_nc,2,'.',''))
				->setCellValue('O'.$t,  number_format($suma_iva,2,'.',''))
				->setCellValue('P'.$t,  number_format($suma_total_nc,2,'.',''))
				;
			
			for($i = 'A'; $i <= 'P'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('DetalleNC');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			// Inmovilizar paneles 
			//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReporteDetalleNC.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			print_r('No hay resultados para mostrar');
		}
	}
?>