<?php
include("../ajax/conciliacion_bancaria.php");
	
	$con = conenta_login();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

if ($action=="generar_informe_excel"){
$cuenta=$_POST['cuenta'];
$fecha_desde=$_POST['fecha_desde'];
$fecha_hasta=$_POST['fecha_hasta'];

$sql_cuenta = mysqli_fetch_array(mysqli_query($con,"SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.ruc_empresa ='".$ruc_empresa."' and cue_ban.id_cuenta='".$cuenta."'"));
$nombre_cuenta=$sql_cuenta['cuenta_bancaria'];

$suma_creditos_saldo_inicial = saldo_inicial_creditos($con, $cuenta, $ruc_empresa, $fecha_desde);
	$suma_debitos_saldo_inicial = saldo_inicial_debitos($con, $cuenta, $ruc_empresa, $fecha_desde);
	$cheques_saldo_inicial = cheques_saldo_inicial($con, $cuenta, $ruc_empresa, $fecha_desde);
	$saldo_inicial=$suma_creditos_saldo_inicial-$suma_debitos_saldo_inicial-$cheques_saldo_inicial;

	$total_creditos = creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'INGRESO');
	$total_debitos = creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'EGRESO');
	$cheques_pagados = cheques_pagados($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta);

	$saldo_final=$saldo_inicial+$total_creditos-$total_debitos-$cheques_pagados;

			ini_set('date.timezone','America/Guayaquil');
			$fecha_hoy = date_create(date("Y-m-d H:i:s"));
			//if(mysqli_num_rows($resultado) > 0 ){			
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
								 ->setTitle("Conciliacion Bancaria")
								 ->setSubject("Conciliacion Bancaria")
								 ->setDescription("Conciliacion Bancaria")
								 ->setKeywords("Conciliacion Bancaria")
								 ->setCategory("Conciliacion Bancaria");

			//para sacar el nombre de la empresa
				$sql_empresa = mysqli_query($con,"SELECT * FROM empresas where ruc= '".$ruc_empresa."'");      
				$empresa_info=mysqli_fetch_array($sql_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
				$tituloReporte = "Conciliación Bancaria ".$nombre_cuenta. " del ".date("d/m/Y", strtotime($fecha_desde)). " al ".date("d/m/Y", strtotime($fecha_hasta));
				

			$titulosColumnas = array('Fecha Emisión','N.Documento','Recibido de / Beneficiario','Créditos','Débitos','Tipo', 'N.Cheque','Fecha cobro','Estado cheque','Detalle');
			
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:I1')
						->mergeCells('A2:I2')
						;
			
			$i = 7;
			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1', $tituloEmpresa)
						->setCellValue('A2',  $tituloReporte)
						->setCellValue('A3',  "Saldo Inicial")
						->setCellValue('B3',  $saldo_inicial)
						->setCellValue('A4',  "Créditos")
						->setCellValue('B4',  $total_creditos)
						->setCellValue('A5',  "Débitos")
						->setCellValue('B5',  number_format($total_debitos+$cheques_pagados,2,'.',''))
						->setCellValue('A6',  "Saldo Final")
						->setCellValue('B6',  $saldo_final)
						;
			
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
			->setCellValue('J'.$i,  $titulosColumnas[9])
			;	
			$i++;

			//CREDITOS
			$sql_ingresos = detalle_creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'INGRESO');
			while ($row_ingresos = mysqli_fetch_array($sql_ingresos)){
			$fecha_emision=$row_ingresos['fecha_emision'];
			$codigo_documento=$row_ingresos['codigo_documento'];
			$nombre_ingreso = $row_ingresos['nombre_ing_egr'];
			$numero_ing_egr=$row_ingresos['numero_ing_egr'];
			$detalle_pago=$row_ingresos['detalle_pago'];
			switch ($detalle_pago) {
				case "D":
					$detalle_pago = 'Depósito';
					break;
				case "T":
					$detalle_pago = 'Transferencia';
					break;
			}
			$valor=$row_ingresos['valor_forma_pago'];
			$sql_detalle_ingresos = detalle_ingresos_egresos($con, $codigo_documento);
			$detalle_unido="";
			
			foreach ($sql_detalle_ingresos as $detalle){ 
				$detalle_unido .= $detalle['detalle_ing_egr']." ";
			}	
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  date("d/m/Y", strtotime($fecha_emision)))
						->setCellValue('B'.$i,  "Ingreso ".$numero_ing_egr)
						->setCellValue('C'.$i,  $nombre_ingreso)
						->setCellValue('D'.$i,  number_format($valor,2,'.',''))
						->setCellValue('E'.$i,  number_format(0,2,'.',''))
						->setCellValue('F'.$i,  $detalle_pago)
						->setCellValue('G'.$i,  "")
						->setCellValue('H'.$i,  "")
						->setCellValue('I'.$i,  "")
						->setCellValue('J'.$i,  $detalle_unido)
						;
						$objPHPExcel->getActiveSheet()->getStyle('D'.$i.':E'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
						$i++;				
				}

				//DEBITOS
				$sql_egresos = detalle_creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'EGRESO');
			while ($row_egresos = mysqli_fetch_array($sql_egresos)){
				//$fecha_emision=$row_egresos['cheque']>0?$row_egresos['fecha_entrega']:$row_egresos['fecha_emision'];
				$fecha_emision=$row_egresos['fecha_emision'];
				$codigo_documento=$row_egresos['codigo_documento'];
				$nombre_egreso = $row_egresos['nombre_ing_egr'];
				$numero_ing_egr=$row_egresos['numero_ing_egr'];
				$detalle_pago=$row_egresos['detalle_pago'];
				switch ($detalle_pago) {
					case "D":
						$detalle_pago = 'Débito';
						break;
					case "T":
						$detalle_pago = 'Transferencia';
						break;
					case "C":
						$detalle_pago = 'Cheque';
						break;
				}

				$valor=$row_egresos['valor_forma_pago'];
				$numero_cheque=$row_egresos['cheque']>0?$row_egresos['cheque']:"";
				$fecha_pago=$numero_cheque>0?$row_egresos['fecha_entrega']:$fecha_emision;
				$estado_pago=$numero_cheque>0?$row_egresos['estado_pago']:"";
				$sql_detalle_egresos = detalle_ingresos_egresos($con, $codigo_documento);
			$detalle_unido="";
			
			foreach ($sql_detalle_egresos as $detalle){ 
				$detalle_unido .= $detalle['detalle_ing_egr']." ";
			}	
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  date("d/m/Y", strtotime($fecha_emision)))
						->setCellValue('B'.$i,  "Egreso ".$numero_ing_egr)
						->setCellValue('C'.$i,  $nombre_egreso)
						->setCellValue('D'.$i,  number_format(0,2,'.',''))
						->setCellValue('E'.$i,  number_format($valor,2,'.',''))
						->setCellValue('F'.$i,  $detalle_pago)
						->setCellValue('G'.$i,  $numero_cheque)
						->setCellValue('H'.$i,  date("d/m/Y", strtotime($fecha_pago)))
						->setCellValue('I'.$i,  $estado_pago)
						->setCellValue('J'.$i,  $detalle_unido)
						;
						$objPHPExcel->getActiveSheet()->getStyle('D'.$i.':E'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
						$i++;				
				}

			for($i = 'A'; $i <= 'I'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}

			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('ConciliacionBancaria');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,8);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ConciliacionBancaria.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
		}else{
			echo('No es posible generar el archivo.');
		}
?>