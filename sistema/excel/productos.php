<?php
	include("../conexiones/conectalogin.php");
	$conexion = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$consulta = mysqli_query($conexion, "SELECT pro_ser.codigo_auxiliar as codigo_auxiliar, pro_ser.id as id, pro_ser.codigo_producto, pro_ser.nombre_producto, pro_ser.precio_producto as precio_producto, if(pro_ser.tipo_produccion = '01','PRODUCTO','SERVICIO') as tipo_produccion, tar_iva.tarifa as tarifa, uni_med.nombre_medida as nombre_medida, mar.nombre_marca as nombre_marca FROM productos_servicios as pro_ser LEFT JOIN tarifa_iva as tar_iva ON tar_iva.codigo=pro_ser.tarifa_iva LEFT JOIN unidad_medida as uni_med ON uni_med.id_medida=pro_ser.id_unidad_medida LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=pro_ser.id LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca and pro_ser.id=mar_pro.id_producto WHERE pro_ser.ruc_empresa='".$ruc_empresa."' order by pro_ser.nombre_producto asc");
	//$consulta = "SELECT mar.id_marca as id_marca, pro_ser.id as id, pro_ser.codigo_producto as codigo_producto, pro_ser.nombre_producto as nombre_producto, pro_ser.precio_producto as precio_producto, pro_ser.tipo_produccion as tipo_produccion, tar_iva.tarifa as tarifa, uni_med.nombre_medida as nombre_medida FROM productos_servicios as pro_ser LEFT JOIN tarifa_iva as tar_iva ON pro_ser.tarifa_iva=tar_iva.codigo LEFT JOIN unidad_medida as uni_med ON pro_ser.id_unidad_medida=uni_med.id_medida LEFT JOIN marca_producto as mar ON pro_ser.id=mar.id_producto WHERE pro_ser.ruc_empresa='".$ruc_empresa."' order by pro_ser.nombre_producto asc";
	//$resultado = $conexion->query($consulta);	

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
								 ->setTitle("Productos Servicios")
								 ->setSubject("Productos Servicios")
								 ->setDescription("Productos Servicios")
								 ->setKeywords("Productos Servicios")
								 ->setCategory("Productos Servicios");

			//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($conexion,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre'];
			$tituloReporte = "Listado de Productos y Servicios";
			$titulosColumnas = array('Código','Auxiliar','Descripción','Precio','Tipo','Tipo IVA', 'Medida','Marca');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:H1')
						->mergeCells('A2:H2')
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
						;
						
			$i = 4;
			
			while ($fila = mysqli_fetch_array($consulta)) {
				//"=\"".$fila['codigo_producto']."\""
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  "=\"" . $fila['codigo_producto'] . "\"")
			->setCellValue('B'.$i,  "=\"" . $fila['codigo_auxiliar'] . "\"")
			->setCellValue('C'.$i,  $fila['nombre_producto'])
			->setCellValue('D'.$i,  number_format($fila['precio_producto'],4,'.',''))
			->setCellValue('E'.$i,  $fila['tipo_produccion'])
			->setCellValue('F'.$i,  $fila['tarifa'])
			->setCellValue('G'.$i,  $fila['nombre_medida'])
			->setCellValue('H'.$i,  $fila['nombre_marca'])
			;
			$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			//$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
				$i++;
			}

	//REFERENCIA DEL ARCHIVO DONDE ESTAN LOS FORMATOS C:\xampp\htdocs\sistema\excel\lib\PHPExcel\PHPExcel\Style\NumberFormat.php
				
			for($i = 'A'; $i <= 'C'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
					
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Productos Servicios');
			

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ProductosServicios.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar');
		}

?>