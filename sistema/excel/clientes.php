<?php
	include("../conexiones/conectalogin.php");
	$conexion = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

	$consulta = "SELECT clientes.ruc as ruc, clientes.nombre as nombre, clientes.telefono as telefono,
	clientes.email as email, clientes.direccion as direccion, clientes.plazo as plazo,
	provincia.nombre as provincia, ciudad.nombre as ciudad, iden_comprador.nombre as tipo 
	FROM clientes LEFT JOIN iden_comprador ON iden_comprador.codigo=clientes.tipo_id LEFT JOIN provincia ON provincia.codigo=clientes.provincia LEFT JOIN ciudad ON ciudad.codigo=clientes.ciudad WHERE ruc_empresa='".$ruc_empresa."' order by nombre asc";
	$resultado = $conexion->query($consulta);	

		
		if($resultado->num_rows > 0 ){			
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
								 ->setTitle("Clientes")
								 ->setSubject("Clientes")
								 ->setDescription("Clientes")
								 ->setKeywords("Clientes")
								 ->setCategory("Clientes");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($conexion,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre'];
			$tituloReporte = "Listado de Clientes";
			$titulosColumnas = array('Tipo','Identificación','Nombre','Dirección','Teléfono','Mail','Plazo','Provincia','Ciudad');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:C1')
						->mergeCells('A2:C2')
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
						;
			
			//para sacar subtotales de todas las tarifa iva de cada una de las facturas, si aumentara una tarifa no importaria
						
			$i = 4;
			
			while ($fila = $resultado->fetch_array()) {
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  $fila['tipo'])
			->setCellValue('B'.$i,  "=\"" . $fila['ruc'] . "\"")
			->setCellValue('C'.$i,  strtoupper($fila['nombre']))
			->setCellValue('D'.$i,  strtoupper($fila['direccion']))
			->setCellValue('E'.$i,  "=\"" . $fila['telefono'] . "\"")
			->setCellValue('F'.$i,  $fila['email'])
			->setCellValue('G'.$i,  $fila['plazo']." Días")
			->setCellValue('H'.$i,  $fila['provincia'])
			->setCellValue('I'.$i,  $fila['ciudad'])
			;
						$i++;
			}
								
			for($i = 'A'; $i <= 'I'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Clientes');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="Clientes.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}

?>