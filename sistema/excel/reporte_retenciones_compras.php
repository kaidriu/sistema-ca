<?php
	include("../conexiones/conectalogin.php");
	$conexion = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

	if(isset($_POST['parametro'])){
		$parametro =$_POST['parametro'];
		$busqueda = str_replace("/", "/", $parametro);
		
	$consulta = "SELECT * FROM cuerpo_retencion WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and ejercicio_fiscal = '".$busqueda."' ";
	$resultado = $conexion->query($consulta);	

		
		
		if($resultado->num_rows>0 ){			
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
								 ->setDescription("Reporte retenciones")
								 ->setKeywords("reporte retenciones")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($conexion,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre'];
			$tituloReporte = "Reporte de Retenciones en Compras";
			$titulosColumnas = array('N Retención','N Documento','Fecha emisión','Fecha documento','Base imponible','Impuesto','Código','% retención','Valor Retenido', 'Ruc proveedor', 'Proveedor','Aut. SRI');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:L1')
						->mergeCells('A2:L2')
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
						;
			
			//Se agregan los datos de las retenciones
									
			$i = 4;
		
			while ($fila = $resultado->fetch_array()) {
						$serie=$fila['serie_retencion'];
						$secuencial=$fila['secuencial_retencion'];
			
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i,  $fila['serie_retencion']."-". $fila['secuencial_retencion']);
				
				$sql_encabezado = "SELECT * FROM encabezado_retencion where serie_retencion = '".$serie."' and secuencial_retencion = '".$secuencial."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' ";      
				$result_encabezado = mysqli_query($conexion,$sql_encabezado);
				
				while ($row_encabezado=mysqli_fetch_array($result_encabezado)){
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B'.$i,  $row_encabezado['numero_comprobante'])
				->setCellValue('C'.$i,  date("d/m/Y", strtotime($row_encabezado['fecha_emision'])))
				->setCellValue('D'.$i,  date("d/m/Y", strtotime($row_encabezado['fecha_documento'])))
				->setCellValue('E'.$i,  $fila['base_imponible'])
				->setCellValue('F'.$i,  $fila['impuesto'])
				->setCellValue('G'.$i,  $fila['codigo_impuesto'])
				->setCellValue('H'.$i,  $fila['porcentaje_retencion'])
				->setCellValue('I'.$i,  $fila['valor_retenido']);
				
				
				$sql_proveedor = "SELECT * FROM proveedores where id_proveedor = '".$row_encabezado['id_proveedor']."' ";      
				$result_proeveedor = mysqli_query($conexion,$sql_proveedor);
				$row_proveedor=mysqli_fetch_array($result_proeveedor);
				
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('J'.$i,  "=\"" . $row_proveedor['ruc_proveedor'] . "\"")
				->setCellValue('K'.$i,  $row_proveedor['razon_social'])
				->setCellValue('L'.$i,  "=\"" . $row_encabezado['aut_sri']. "\"");
				}
			
						$i++;
				}
			}//fin del while
	
			for($i = 'A'; $i <= 'L'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('DetalleRet');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			// Inmovilizar paneles 
			//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReporteRetenciones.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			print_r('No hay resultados para mostrar');
		}
?>