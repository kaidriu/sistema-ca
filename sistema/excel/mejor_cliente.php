<?php
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

		$desde = $_POST['desde'];
		 $hasta = $_POST['hasta'];
		 $cantidad = $_POST['cantidad'];
		 $action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
		 
		$consulta = mysqli_query($con, "SELECT cli.nombre as cliente, sum(encfac.total_factura) as total 
		FROM encabezado_factura as encfac INNER JOIN clientes as cli ON cli.id = encfac.id_cliente 
		WHERE encfac.ruc_empresa='".$ruc_empresa."' and 
		DATE_FORMAT(encfac.fecha_factura, '%Y/%m/%d') 
		between '" . date("Y/m/d", strtotime($desde)) . "' 
		and '" . date("Y/m/d", strtotime($hasta)) . "' group by encfac.id_cliente order by sum(encfac.total_factura) desc LIMIT 0, $cantidad");
	 
		if(mysqli_num_rows($consulta)>0){			
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
								 ->setTitle("Mejor cliente")
								 ->setSubject("Mejor cliente")
								 ->setDescription("Mejor cliente")
								 ->setKeywords("Mejor cliente")
								 ->setCategory("Mejor cliente");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre'];
			$tituloReporte = "Listado de los mejores clientes";
			$titulosColumnas = array('Cliente','Total');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:B1')
						->mergeCells('A2:B2')
						;
							
			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1',  $tituloEmpresa)
						->setCellValue('A2',  $tituloReporte)
						->setCellValue('A3',  $titulosColumnas[0])
						->setCellValue('B3',  $titulosColumnas[1])
						;
						
			$i = 4;

			while ($fila = mysqli_fetch_array($consulta)) {
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  $fila['cliente'])
			->setCellValue('B'.$i,  number_format($fila['total'],4,'.',''))
			;
			$objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
				$i++;
			}
			

	//REFERENCIA DEL ARCHIVO DONDE ESTAN LOS FORMATOS C:\xampp\htdocs\sistema\excel\lib\PHPExcel\PHPExcel\Style\NumberFormat.php
				
			for($i = 'A'; $i <= 'B'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Mejor cliente');
			
			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="MejorCliente.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}

?>