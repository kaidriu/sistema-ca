<?php
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
//PARA BUSCAR LAS FACTURAS de ventas	
$tipo_reporte=$_POST['tipo_reporte'];
$id_proveedor=$_POST['id_proveedor'];
$desde=$_POST['fecha_desde'];
$hasta=$_POST['fecha_hasta'];
ini_set('date.timezone','America/Guayaquil');
//para sacar el nombre de la empresa
$sql_empresa =  mysqli_query($con, "SELECT * FROM empresas where ruc= '".$ruc_empresa."'");      
$empresa_info=mysqli_fetch_array($sql_empresa);
$tituloEmpresa= $empresa_info['nombre_comercial']."-".substr($empresa_info['ruc'],10,3);

if($tipo_reporte == '1'){
	if (empty($id_proveedor)){
	$condicion_proveedor="";
	}else{
	$condicion_proveedor=" and enc_com.id_proveedor=".$id_proveedor;	
	}
	$condicion_documento=" and enc_com.id_comprobante !=4";
	reporte_adquisiciones_general($tituloEmpresa, $con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento,"Adquisiciones");
}
if($tipo_reporte == '2'){
	if (empty($id_proveedor)){
	$condicion_proveedor="";
	}else{
	$condicion_proveedor=" and enc_com.id_proveedor=".$id_proveedor;	
	}
	$condicion_documento=" and enc_com.id_comprobante=4";
	reporte_adquisiciones_general($tituloEmpresa, $con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento, "NC-Adquisiciones");
}

if($tipo_reporte == '3'){
	if (empty($id_proveedor)){
	$condicion_proveedor="";
	}else{
	$condicion_proveedor=" and enc_com.id_proveedor=".$id_proveedor;	
	}
	$condicion_documento=" and enc_com.id_comprobante !=4";
	reporte_adquisiciones_detalle($tituloEmpresa, $con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento, "Detalle-Adquisiciones");
}

if($tipo_reporte == '4'){
	if (empty($id_proveedor)){
	$condicion_proveedor="";
	}else{
	$condicion_proveedor=" and enc_com.id_proveedor=".$id_proveedor;	
	}
	$condicion_documento=" and enc_com.id_comprobante=4";
	reporte_adquisiciones_detalle($tituloEmpresa, $con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento, "Detalle-NC-Adquisiciones");
}


function reporte_adquisiciones_general($tituloEmpresa, $con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento, $nombre_archivo){
$resultado = mysqli_query($con, "SELECT * FROM cuerpo_compra as cue_com
INNER JOIN encabezado_compra as enc_com ON enc_com.codigo_documento=cue_com.codigo_documento
LEFT JOIN proveedores as pro ON pro.id_proveedor=enc_com.id_proveedor
LEFT JOIN comprobantes_autorizados as com_aut ON com_aut.id_comprobante=enc_com.id_comprobante
WHERE enc_com.ruc_empresa = '".$ruc_empresa."' and cue_com.ruc_empresa = '".$ruc_empresa."' 
and DATE_FORMAT(enc_com.fecha_compra, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."'
and '".date("Y/m/d", strtotime($hasta))."' $condicion_proveedor $condicion_documento
group by enc_com.codigo_documento order by enc_com.fecha_compra asc");

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
								 ->setDescription("Reporte de compras")
								 ->setKeywords("reporte compras")
								 ->setCategory("Reporte excel");

			$tituloReporte = "Reporte de adquisiciones en compras y servicios desde ".$desde." hasta ".$hasta;
			$titulosColumnas = array('Fecha','Proveedor','Ruc','Documento','Número','Base 0','Base 12','Base No iva','Base Exento','Iva 12','Base ice','Descuento','Propina','Otros','Total','Tipo Deducible','Sus. Tributario','Aut. SRI','Retenciones','Tipo','Código','Cuenta','Egreso');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:S1')
						->mergeCells('A2:S2')
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
						;
			$i = 4;
			
			$suma_total_retenciones =0;
			$suma_propina =0;
			$suma_otros_val =0;
			$suma_base_descuento =0;
			$suma_base_exento =0;
			$suma_base_noimp =0;
			$suma_base_ice =0;
			$suma_base_doce =0;
			$suma_base_cero =0;
			$suma_total_compra = 0;


			while ($fila = $resultado->fetch_array()) {
					$codigo_documento=$fila['codigo_documento'];
					$id_comprobante=$fila['id_comprobante'];
					$id_sustento=$fila['id_sustento'];
					$id_deducible=$fila['deducible_en'];
					$nombre_comprobante= $fila['comprobante'];
					$id_proveedor= $fila['id_proveedor'];
					$tipo_comprobante= $fila['tipo_comprobante'];
					$suma_total_compra += $fila['total_compra'];
		
						//para sacar el detalle de cada factura de compras base cero
					$sql_base_cero = mysqli_query($con,"SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."' and impuesto=2 and det_impuesto=0");      
					$subtotales_base_cero=mysqli_fetch_array($sql_base_cero);
					$base_cero= $subtotales_base_cero['subtotal'];
					$suma_base_cero +=$base_cero;

					//para sacar el detalle de base doce
					$sql_base_doce = mysqli_query($con,"SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."' and impuesto=2 and det_impuesto=2");      			
					$subtotales_base_doce=mysqli_fetch_array($sql_base_doce);
					$base_doce= $subtotales_base_doce['subtotal'];
					$suma_base_doce +=$base_doce;
					
					//para sacar el detalle de base ice
					$sql_base_ice = mysqli_query($con,"SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."' and impuesto=3 ");      			
					$subtotales_sql_base_ice=mysqli_fetch_array($sql_base_ice);
					$base_ice= $subtotales_sql_base_ice['subtotal'];
					$suma_base_ice +=$base_ice;

					//para sacar el detalle de base no obj imp
					$sql_base_noimp = mysqli_query($con,"SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."' and impuesto=2 and det_impuesto=6");			
					$subtotales_base_noimp=mysqli_fetch_array($sql_base_noimp);
					$base_noimp= $subtotales_base_noimp['subtotal'];
					$suma_base_noimp +=$base_noimp;

					//para sacar el detalle de base exento
					$sql_base_exento = mysqli_query($con,"SELECT sum(subtotal) as subtotal FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."' and impuesto=2 and det_impuesto=7");					
					$subtotales_base_exento=mysqli_fetch_array($sql_base_exento);
					$base_exento= $subtotales_base_exento['subtotal'];
					$suma_base_exento +=$base_exento;

					//para sacar el detalle de descuento
					$sql_base_descuento = mysqli_query($con,"SELECT sum(descuento) as descuento FROM cuerpo_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."'");      					
					$subtotales_base_descuento=mysqli_fetch_array($sql_base_descuento);
					$base_descuento= $subtotales_base_descuento['descuento'];
					$suma_base_descuento +=$base_descuento;
					
					//para sacar la propina y otros valores
					$sql_otros = mysqli_query($con,"SELECT * FROM encabezado_compra WHERE codigo_documento ='".$codigo_documento."'  and ruc_empresa = '".$ruc_empresa."'");      				
					$adicionales=mysqli_fetch_array($sql_otros);
					$propina= $adicionales['propina'];
					$otros_val= $adicionales['otros_val'];
					$aut_sri= $adicionales['aut_sri'];

					$suma_propina +=$propina;
					$suma_otros_val +=$otros_val;
					
					//para SABER EL nombre del SUSTENTO TRIBUTARIO
					$sql_sustento = mysqli_query($con,"SELECT * FROM sustento_tributario WHERE id_sustento ='".$id_sustento."' ");      		
					$row_sustento=mysqli_fetch_array($sql_sustento);
					$nombre_sustento= $row_sustento['nombre_sustento'];
					
					$sql_retenciones = mysqli_query($con, "SELECT sum(cue.valor_retenido) as valor_retenido FROM cuerpo_retencion as cue LEFT JOIN encabezado_retencion as enc ON enc.serie_retencion=cue.serie_retencion and enc.secuencial_retencion=cue.secuencial_retencion and cue.ruc_empresa=enc.ruc_empresa WHERE enc.numero_comprobante='".$fila['numero_documento']."' and enc.id_proveedor='".$id_proveedor."' and mid(enc.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' group by enc.numero_comprobante");      
					$row_retenciones= mysqli_fetch_array($sql_retenciones);
					$total_retenciones = $row_retenciones['valor_retenido'];
					$suma_total_retenciones +=$total_retenciones;

					$sql_cuenta = mysqli_query($con, "SELECT plan.codigo_cuenta as codigo, plan.nombre_cuenta as cuenta FROM asientos_programados as asi LEFT JOIN plan_cuentas as plan ON plan.id_cuenta=asi.id_cuenta WHERE asi.id_pro_cli='".$id_proveedor."' and asi.ruc_empresa = '".$ruc_empresa."'");      
					$row_cuenta= mysqli_fetch_array($sql_cuenta);
					$codigo_contable = $row_cuenta['codigo'];
					$cuenta_contable = $row_cuenta['cuenta'];

					$sql_eg = mysqli_query($con, "SELECT det.numero_ing_egr as egreso FROM detalle_ingresos_egresos as det INNER JOIN ingresos_egresos as egr ON egr.codigo_documento=det.codigo_documento WHERE det.codigo_documento_cv='".$codigo_documento."' and det.tipo_documento='EGRESO'");      
					$row_eg= mysqli_fetch_array($sql_eg);
					$egreso = $row_eg['egreso'];

					//para SABER EL nombre del TIPO DEDUCIBLE
					switch ($fila['deducible_en']) {
					case "01":
						$deducible='No asignado';
						break;
					case "02":
						$deducible='No asignado';
						break;
					case "03":
						$deducible='No asignado';
						break;
					case "04":
						$deducible='Deducible para impuestos';
						break;
					case "05":
						$deducible='No deducible o gasto personal';
						break;
					case "":
						$deducible='No asignado';
						break;
						}

			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  date("d/m/Y", strtotime($fila['fecha_compra'])))
			->setCellValue('B'.$i,  strtoupper($fila['razon_social']))
			->setCellValue('C'.$i,  "=\"" . $fila['ruc_proveedor'] . "\"")
			->setCellValue('D'.$i,  $nombre_comprobante)
			->setCellValue('E'.$i,  $fila['numero_documento'])
			->setCellValue('F'.$i,  $base_cero)
			->setCellValue('G'.$i,  $base_doce)
			->setCellValue('H'.$i,  $base_noimp)
			->setCellValue('I'.$i,  $base_exento)
			->setCellValue('J'.$i,  $base_doce*0.12)
			->setCellValue('K'.$i,  $base_ice)
			->setCellValue('L'.$i,  $base_descuento)
			->setCellValue('M'.$i,  $propina)
			->setCellValue('N'.$i,  $otros_val)
			->setCellValue('O'.$i,  $fila['total_compra'])
			->setCellValue('P'.$i,  $deducible)
			->setCellValue('Q'.$i,  $nombre_sustento)
			->setCellValue('R'.$i,  "=\"" .$aut_sri. "\"")
			->setCellValue('S'.$i,  $total_retenciones)
			->setCellValue('T'.$i,  $tipo_comprobante)
			->setCellValue('U'.$i,  $codigo_contable)
			->setCellValue('V'.$i,  $cuenta_contable)
			->setCellValue('W'.$i,  $egreso)
			;
						
			$i++;
			}
			$t=$i+1;

				$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('E'.$t,  'Totales')
						->setCellValue('F'.$t,  number_format($suma_base_cero,2,'.',''))
						->setCellValue('G'.$t,  number_format($suma_base_doce,2,'.',''))
						->setCellValue('H'.$t,  number_format($suma_base_noimp,2,'.',''))
						->setCellValue('I'.$t,  number_format($suma_base_exento,2,'.',''))
						->setCellValue('J'.$t,  number_format($suma_base_doce * 0.12,2,'.',''))
						->setCellValue('K'.$t,  number_format($suma_base_ice,2,'.',''))
						->setCellValue('L'.$t,  number_format($suma_base_descuento,2,'.',''))
						->setCellValue('M'.$t,  number_format($suma_propina,2,'.',''))
						->setCellValue('N'.$t,  number_format($suma_otros_val,2,'.',''))
						->setCellValue('O'.$t,  number_format($suma_total_compra,2,'.',''))
						->setCellValue('S'.$t,  number_format($suma_total_retenciones,2,'.',''))
						;
					
				$objPHPExcel->getActiveSheet()->getStyle('F4:O'.$t)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
				$objPHPExcel->getActiveSheet()->getStyle('S4:S'.$t)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			

			for($i = 'A'; $i <= 'U'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			genera_excel($objPHPExcel, $nombre_archivo, $nombre_archivo);
			
		}else{
			echo('No hay resultados para mostrar');
		}
	}	


	function reporte_adquisiciones_detalle($tituloEmpresa, $con, $ruc_empresa, $desde, $hasta, $condicion_proveedor, $condicion_documento, $nombre_archivo){	
	$resultado = mysqli_query($con, "SELECT enc_com.fecha_compra as fecha_compra, pro.razon_social as proveedor, pro.ruc_proveedor as ruc_proveedor,
	com_aut.comprobante as comprobante, enc_com.numero_documento as documento, cue_com.codigo_producto as codigo, cue_com.detalle_producto as detalle, tar.tarifa as tarifa_iva,
	cue_com.cantidad as cantidad, cue_com.precio as precio, cue_com.descuento as descuento, cue_com.subtotal as subtotal 
	 FROM cuerpo_compra as cue_com INNER JOIN encabezado_compra as enc_com ON enc_com.codigo_documento=cue_com.codigo_documento
	 INNER JOIN proveedores as pro ON pro.id_proveedor=enc_com.id_proveedor INNER JOIN comprobantes_autorizados as com_aut ON com_aut.id_comprobante= enc_com.id_comprobante
	 INNER JOIN tarifa_iva as tar ON tar.codigo=cue_com.det_impuesto WHERE  enc_com.ruc_empresa = '".$ruc_empresa."' and cue_com.ruc_empresa = '".$ruc_empresa."' 
	and DATE_FORMAT(enc_com.fecha_compra, '%Y/%m/%d') between '".date("Y/m/d", strtotime($desde))."'
	and '".date("Y/m/d", strtotime($hasta))."' $condicion_proveedor $condicion_documento order by enc_com.fecha_compra asc");
		
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
									 ->setDescription("Reporte de compras")
									 ->setKeywords("reporte compras")
									 ->setCategory("Reporte excel");
	
				$tituloReporte = "Reporte detallado. Del: ".$desde." Al: ".$hasta;
				$titulosColumnas = array('Fecha','Proveedor','Ruc','Documento','Número','Código','Detalle','Tarifa','Cantidad','Valor unitario','Descuento','Subtotal');
				
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
				$i = 4;
			
				while ($fila = mysqli_fetch_array($resultado)) {
						$fecha_compra=$fila['fecha_compra'];
						$comprobante=$fila['comprobante'];
						$documento=$fila['documento'];
						$proveedor=$fila['proveedor'];
						$ruc_proveedor=$fila['ruc_proveedor'];
						$cantidad= $fila['cantidad'];
						$codigo= $fila['codigo'];
						$detalle= $fila['detalle'];
						$precio= $fila['precio'];
						$descuento= $fila['descuento'];
						$tarifa_iva= $fila['tarifa_iva'];
						$subtotal= $fila['subtotal'];
			
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i,  date("d-m-Y", strtotime($fecha_compra)))
				->setCellValue('B'.$i,  $proveedor)
				->setCellValue('C'.$i,  "=\"" . $ruc_proveedor . "\"")
				->setCellValue('D'.$i,  $comprobante)
				->setCellValue('E'.$i,  $documento)
				->setCellValue('F'.$i,  "=\"" .$codigo. "\"")
				->setCellValue('G'.$i,  "=\"" .$detalle. "\"")
				->setCellValue('H'.$i,  $tarifa_iva)
				->setCellValue('I'.$i,  number_format($cantidad,4,'.',''))
				->setCellValue('J'.$i,  number_format($precio,4,'.',''))
				->setCellValue('K'.$i,  number_format($descuento,2,'.',''))
				->setCellValue('L'.$i,  number_format($subtotal,2,'.',''))
				;
				$objPHPExcel->getActiveSheet()->getStyle('I'.$i.':L'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
				$i=$i+1;
				}//fin del while

				
				for($i = 'A'; $i <= 'S'; $i++){
					$objPHPExcel->setActiveSheetIndex(0)			
						->getColumnDimension($i)->setAutoSize(TRUE);
				}
				
				genera_excel($objPHPExcel, $nombre_archivo, $nombre_archivo);
				
			}else{
				print_r('No hay resultados para mostrar');
			}
		}

	function genera_excel($objPHPExcel, $nombre_hoja, $nombre_archivo){
		// Se asigna el nombre a la hoja
		$nombre_archivo= $nombre_archivo.".xlsx";
		$objPHPExcel->getActiveSheet()->setTitle($nombre_hoja);

		// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

		// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$nombre_archivo.'"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

?>