<?php
	include("../conexiones/conectalogin.php");
	//include("../helpers/helpers.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

		$nombre_informe = mysqli_real_escape_string($con,(strip_tags($_REQUEST['nombre_informe'], ENT_QUOTES)));
		$desde = mysqli_real_escape_string($con,(strip_tags($_REQUEST['fecha_desde'], ENT_QUOTES)));
		$hasta = mysqli_real_escape_string($con,(strip_tags($_REQUEST['fecha_hasta'], ENT_QUOTES)));
		$pro_cli = mysqli_real_escape_string($con,(strip_tags($_REQUEST['pro_cli'], ENT_QUOTES)));
		$id_cuenta = mysqli_real_escape_string($con,(strip_tags($_REQUEST['id_cuenta_contable'], ENT_QUOTES)));
			
			//buscar en base a una cuenta seleccionada y fechas
			if ($nombre_informe=="4" && !empty($id_cuenta)){
			$sql_cuentas=mysqli_query($con,"SELECT * FROM plan_cuentas WHERE id_cuenta = '".$id_cuenta."' ");//  
			$row_cuentas = mysqli_fetch_array($sql_cuentas);
			$codigo_cuenta=$row_cuentas['codigo_cuenta'];
			$nombre_cuenta=$row_cuentas['nombre_cuenta'];
			$consulta=mysqli_query($con,"SELECT enc_dia.tipo as tipo, enc_dia.numero_asiento as asiento, enc_dia.fecha_asiento as fecha, det_dia.debe as debe, det_dia.haber as haber, det_dia.detalle_item as detalle FROM detalle_diario_contable as det_dia INNER JOIN encabezado_diario as enc_dia ON enc_dia.codigo_unico=det_dia.codigo_unico INNER JOIN plan_cuentas as plan ON plan.id_cuenta=det_dia.id_cuenta WHERE enc_dia.ruc_empresa = '".$ruc_empresa."' and DATE_FORMAT(enc_dia.fecha_asiento, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' and plan.id_cuenta = '".$id_cuenta."' and enc_dia.estado !='ANULADO' order by enc_dia.fecha_asiento asc ");  
			}
			
			//buscar en base a todas las cuentas
			if ($nombre_informe=="4" && empty($id_cuenta)){
			$consulta=mysqli_query($con,"SELECT enc_dia.tipo as tipo, enc_dia.numero_asiento as asiento, enc_dia.fecha_asiento as fecha, det_dia.debe as debe, det_dia.haber as haber, det_dia.detalle_item as detalle FROM detalle_diario_contable as det_dia INNER JOIN encabezado_diario as enc_dia ON enc_dia.codigo_unico=det_dia.codigo_unico INNER JOIN plan_cuentas as plan ON plan.id_cuenta=det_dia.id_cuenta WHERE enc_dia.ruc_empresa = '".$ruc_empresa."' and DATE_FORMAT(enc_dia.fecha_asiento, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' and enc_dia.estado !='ANULADO' order by enc_dia.fecha_asiento asc ");  
			}
	
		if(mysqli_num_fields($consulta) > 0 ){			
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
								 ->setTitle("Mayor General")
								 ->setSubject("Mayor General")
								 ->setDescription("Mayor General")
								 ->setKeywords("Mayor General")
								 ->setCategory("Mayor General");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre'];
			if ($nombre_informe=='4' && !empty($id_cuenta)){
			$tituloReporte = "MAYOR GENERAL";
			$fechaReporte = "DESDE ".date("d-m-Y", strtotime($desde))." HASTA ".date("d-m-Y", strtotime($hasta));
			$codigo_y_cuenta = "Código: ".$codigo_cuenta." Cuenta: ".$nombre_cuenta;
			$tituloHoja = "MayorGeneral";
			$titulolibro = "Cuenta ".$codigo_cuenta.".xlsx";
			}
			
			if ($nombre_informe=='4' && empty($id_cuenta)){
			$tituloReporte = "MAYOR GENERAL";
			$fechaReporte = "DESDE ".date("d-m-Y", strtotime($desde))." HASTA ".date("d-m-Y", strtotime($hasta));
			$codigo_y_cuenta = "TODAS LAS CUENTAS";
			$tituloHoja = "MayorGeneral";
			$titulolibro = "MayorGeneral.xlsx";
			}

			$titulosColumnas = array('Fecha','Detalle','Código','Cuenta','Asiento','Tipo','Debe','Haber','Saldo');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:G1')
						->mergeCells('A2:G2')
						->mergeCells('A3:G3')
						->mergeCells('A4:G4')
						;
							
			// Se agregan los titulos del reporte
			if ($nombre_informe=='4' && !empty($id_cuenta)){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1',  $tituloEmpresa)
						->setCellValue('A2',  $tituloReporte)
						->setCellValue('A3',  $fechaReporte)
						->setCellValue('A4',  $codigo_y_cuenta)
						->setCellValue('A5',  $titulosColumnas[0])
						->setCellValue('B5',  $titulosColumnas[1])
						->setCellValue('C5',  $titulosColumnas[2])
						->setCellValue('D5',  $titulosColumnas[3])
						->setCellValue('E5',  $titulosColumnas[4])
						->setCellValue('F5',  $titulosColumnas[5])
						->setCellValue('G5',  $titulosColumnas[6])
						->setCellValue('H5',  $titulosColumnas[7])
						->setCellValue('I5',  $titulosColumnas[8])
						;			
				$i = 6;
			}
			
			if ($nombre_informe=='4' && !empty($id_cuenta)){
				$saldo=0;
				while ($row_detalle_diario=mysqli_fetch_array($consulta)){
				$fecha=date('d-m-Y', strtotime($row_detalle_diario['fecha']));
				$detalle=$row_detalle_diario['detalle'];
				$debe=$row_detalle_diario['debe'];
				$haber=$row_detalle_diario['haber'];
				$saldo +=$debe-$haber;
				$asiento=$row_detalle_diario['asiento'];
				$tipo=$row_detalle_diario['tipo'];
						
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A'.$i,  $fecha)
					->setCellValue('B'.$i,  strtoupper($detalle))
					->setCellValue('C'.$i,  $codigo_cuenta)
					->setCellValue('D'.$i,  $nombre_cuenta)
					->setCellValue('E'.$i,  $asiento)
					->setCellValue('F'.$i,  $tipo)
					->setCellValue('G'.$i,  number_format($debe,2,'.',''))
					->setCellValue('H'.$i,  number_format($haber,2,'.',''))
					->setCellValue('I'.$i,  number_format($saldo,2,'.',''))
					;
						$i++;
					}
			}
			
			//para cuando son todas las cuentas
			if ($nombre_informe=='4' && empty($id_cuenta)){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1',  $tituloEmpresa)
						->setCellValue('A2',  $tituloReporte)
						->setCellValue('A3',  $fechaReporte)
						->setCellValue('A4',  $codigo_y_cuenta)
						;			
				$i = 5;
			}
			
			if ($nombre_informe=='4' && empty($id_cuenta)){
			$sql_cuentas=mysqli_query($con,"SELECT * FROM plan_cuentas WHERE ruc_empresa = '".$ruc_empresa."' and nivel_cuenta='5'");//  
			while ($row_cuentas = mysqli_fetch_array($sql_cuentas)){
			$id_cuenta=$row_cuentas['id_cuenta'];
			$codigo_cuenta=$row_cuentas['codigo_cuenta'];
			$nombre_cuenta=$row_cuentas['nombre_cuenta'];
			$consulta_cuentas=mysqli_query($con,"SELECT enc_dia.tipo as tipo, enc_dia.numero_asiento as asiento, enc_dia.fecha_asiento as fecha, det_dia.debe as debe, det_dia.haber as haber, det_dia.detalle_item as detalle FROM detalle_diario_contable as det_dia INNER JOIN encabezado_diario as enc_dia ON enc_dia.codigo_unico=det_dia.codigo_unico INNER JOIN plan_cuentas as plan ON plan.id_cuenta=det_dia.id_cuenta WHERE enc_dia.ruc_empresa = '".$ruc_empresa."' and DATE_FORMAT(enc_dia.fecha_asiento, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."' and '".date("Y/m/d", strtotime($hasta))."' and enc_dia.estado !='ANULADO' and det_dia.id_cuenta='".$id_cuenta."' order by enc_dia.fecha_asiento asc ");  
			$registros = mysqli_num_rows($consulta_cuentas);
			if ($registros>0){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  strtoupper($codigo_cuenta))
						->setCellValue('B'.$i,  strtoupper($nombre_cuenta))
						;			
					$i = $i+1;
					
					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  $titulosColumnas[0])
						->setCellValue('B'.$i,  $titulosColumnas[1])
						->setCellValue('C'.$i,  $titulosColumnas[2])
						->setCellValue('D'.$i,  $titulosColumnas[3])
						->setCellValue('E'.$i,  $titulosColumnas[4])
						->setCellValue('F'.$i,  $titulosColumnas[5])
						->setCellValue('G'.$i,  $titulosColumnas[6])
						->setCellValue('H'.$i,  $titulosColumnas[7])
						->setCellValue('I'.$i,  $titulosColumnas[8])
						;			
					$i = $i+1;
				
				$saldo=0;
				while ($row_detalle_diario=mysqli_fetch_array($consulta_cuentas)){
				$fecha=date('d-m-Y', strtotime($row_detalle_diario['fecha']));
				$detalle=$row_detalle_diario['detalle'];
				$debe=$row_detalle_diario['debe'];
				$haber=$row_detalle_diario['haber'];
				$saldo +=$debe-$haber;
				$asiento=$row_detalle_diario['asiento'];
				$tipo=$row_detalle_diario['tipo'];

					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A'.$i,  $fecha)
					->setCellValue('B'.$i,  strtoupper($detalle))
					->setCellValue('C'.$i,  $codigo_cuenta)
					->setCellValue('D'.$i,  $nombre_cuenta)
					->setCellValue('E'.$i,  $asiento)
					->setCellValue('F'.$i,  $tipo)
					->setCellValue('G'.$i,  number_format($debe,2,'.',''))
					->setCellValue('H'.$i,  number_format($haber,2,'.',''))
					->setCellValue('I'.$i,  number_format($saldo,2,'.',''))
					;
						$i++;
					}
					$i++;
			   }
			}
			}
			
			$objPHPExcel->getActiveSheet()->getStyle('G6:I'.$i) ->getNumberFormat() ->setFormatCode('#,##0.00');
			/*
			for($i = 'A'; $i <= 'G'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			*/
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle($tituloHoja);

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,6);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename='.$titulolibro);
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}
?>