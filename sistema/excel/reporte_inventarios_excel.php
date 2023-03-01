<?php
	
	include("../conexiones/conectalogin.php");
	//include_once("../clases/saldo_producto_y_conversion.php");
	//date_default_timezone_set('America/Guayaquil');
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];

	//if(isset($_POST['registro_inventario'])){
		
		$desde=date('Y/m/d', strtotime($_POST['fecha_desde']));
		$hasta=date('Y/m/d', strtotime($_POST['fecha_hasta']));
		$ordenado = mysqli_real_escape_string($con,(strip_tags($_POST['ordenado'], ENT_QUOTES)));
		$por = mysqli_real_escape_string($con,(strip_tags($_POST['por'], ENT_QUOTES)));
		$tipo = mysqli_real_escape_string($con,(strip_tags($_POST['registro_inventario'], ENT_QUOTES)));
		$producto = mysqli_real_escape_string($con,(strip_tags($_POST['id_producto'], ENT_QUOTES)));
		$marca = mysqli_real_escape_string($con,(strip_tags($_POST['id_marca'], ENT_QUOTES)));
		$lote = mysqli_real_escape_string($con,(strip_tags($_POST['lote'], ENT_QUOTES)));
		$caducidad = mysqli_real_escape_string($con,(strip_tags($_POST['caducidad'], ENT_QUOTES)));
		$referencia = mysqli_real_escape_string($con,(strip_tags($_POST['referencia'], ENT_QUOTES)));
		
		switch ($tipo) {
			case "1"://entradas
				$data=entradas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia);
				$tituloReporte = "Reporte de entradas de inventarios. Del ".$desde." al ".$hasta;
				$titulosColumnas = array('Código','Producto','Cantidad','Medida','Marca', 'Costo', 'Lote','Referencia','Bodega','Fecha de registro','Vencimiento','Usuario');
			break;
			case "2"://salidas
				$data=salidas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia);
				$tituloReporte = "Reporte de salidas de inventarios. Del ".$desde." al ".$hasta;
				$titulosColumnas = array('Código','Producto','Cantidad', 'Medida', 'Marca','Precio','Lote','Referencia','Bodega','Fecha de registro','Vencimiento','Usuario');
			break;
			
			case "3"://existencia en general
				$data=existencia_general($ordenado, $por, $ruc_empresa, $con, $marca);
				$tituloReporte = "Reporte de existencias de inventarios en general. Hasta ".$hasta;
				$titulosColumnas = array('Código','Producto','Entrada','Salida','Existencia', 'Marca', 'Medida','Bodega');
			break;
			case "4"://existencia caducidad
				$data=existencia_caducidad($ordenado, $por, $ruc_empresa, $con, $marca);
				$tituloReporte = "Reporte de existencias de inventarios por fechas de vencimiento. Hasta ".$hasta;
				$titulosColumnas = array('Código','Producto','Entrada','Salida','Existencia', 'Marca', 'Medida','Bodega','Lote','Vencimiento');
			break;
			case "5"://existencia lote
				$data=existencia_lote($ordenado, $por, $ruc_empresa, $con, $marca);
				$tituloReporte = "Reporte de existencias por lote. Hasta ".$hasta;
				$titulosColumnas = array('Código','Producto','Entrada','Salida','Existencia', 'Marca', 'Medida','Bodega','Lote','Vencimiento');
			break;
		}
		
		function entradas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia){
			if (empty($producto)){
				$condicion_producto="";
				}else{
				$condicion_producto = " and inv.id_producto =" . $producto;
			}
		
			if (empty($marca)){
				$condicion_marca="";
				}else{
				$condicion_marca=" and mar_pro.id_marca=".$marca;
			}
		
			if (empty($lote)){
				$condicion_lote="";
				}else{
				$condicion_lote=" and inv.lote LIKE '%" . $lote . "%' ";
			} 
			
			if (empty($caducidad)){
				$condicion_caducidad="";
				}else{
				$condicion_caducidad=" and inv.fecha_vencimiento LIKE '%" . $caducidad . "%' ";
			} 
		
			if (empty($referencia)){
				$condicion_referencia="";
				}else{
				$condicion_referencia = " and inv.referencia LIKE '%" . $referencia . "%' ";
			}
			/*
			if (empty($producto)){
				$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='ENTRADA' and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' and inv.cantidad_entrada > 0 $condicion_marca order by $ordenado $por" ;
				}else{
				$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='ENTRADA' and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' and inv.id_producto='".$producto."' and inv.cantidad_entrada > 0 $condicion_marca order by $ordenado $por" ;//order by $ordenado $por
			}
			*/
			$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='ENTRADA' 
			and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' 
			and inv.cantidad_entrada > 0 $condicion_producto $condicion_marca $condicion_lote 
			$condicion_caducidad $condicion_referencia 
			order by $ordenado $por" ;

			$sTable = "inventarios as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega INNER JOIN usuarios as usu ON usu.id=inv.id_usuario LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
		  
		   $count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
		   $row= mysqli_fetch_array($count_query);
		   $numrows = $row['numrows'];
		   $sql="SELECT mar.nombre_marca as marca, round(inv.costo_unitario, 4) as costo_unitario, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, round(inv.cantidad_entrada,4) as cantidad_entrada, med.nombre_medida as medida, inv.referencia as referencia, bod.nombre_bodega as bodega,
		   inv.fecha_registro as fecha_registro, inv.fecha_vencimiento as fecha_vencimiento, inv.lote as lote, usu.nombre as usuario
		   FROM $sTable $sWhere ";//LIMIT $offset, $per_page
		   $query = mysqli_query($con, $sql);
		   $data=array('query'=>$query, 'numrows'=>$numrows);
		   return $data;
		}

		function salidas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia){
			if (empty($producto)){
				$condicion_producto="";
				}else{
				$condicion_producto = " and inv.id_producto =" . $producto;
			}
		
			if (empty($marca)){
				$condicion_marca="";
				}else{
				$condicion_marca=" and mar_pro.id_marca=".$marca;
			}
		
			if (empty($lote)){
				$condicion_lote="";
				}else{
				$condicion_lote=" and inv.lote LIKE '%" . $lote . "%' ";
			} 
			
			if (empty($caducidad)){
				$condicion_caducidad="";
				}else{
				$condicion_caducidad=" and inv.fecha_vencimiento LIKE '%" . $caducidad . "%' ";
			} 
		
			if (empty($referencia)){
				$condicion_referencia="";
				}else{
				$condicion_referencia = " and inv.referencia LIKE '%" . $referencia . "%' ";
			}
/*
			if (empty($producto)){
				$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='SALIDA' and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' and inv.cantidad_salida > 0 $condicion_marca order by $ordenado $por" ;
				}else{
				$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='SALIDA' and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' and inv.id_producto='".$producto."' and inv.cantidad_salida > 0 $condicion_marca order by $ordenado $por" ;//order by $ordenado $por
			}
			*/

			$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='SALIDA' and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' and inv.cantidad_salida > 0 $condicion_producto $condicion_marca $condicion_lote 
			$condicion_caducidad $condicion_referencia order by $ordenado $por" ;//order by $ordenado $por
		
			$sTable = "inventarios as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega INNER JOIN usuarios as usu ON usu.id=inv.id_usuario LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
		   $count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
		   $row= mysqli_fetch_array($count_query);
		   $numrows = $row['numrows'];
		   $sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, round(inv.cantidad_salida, 4) as cantidad_salida, med.nombre_medida as medida, round(inv.precio,4) as precio, inv.referencia as referencia, bod.nombre_bodega as bodega,
		   inv.fecha_registro as fecha_registro, inv.fecha_vencimiento as fecha_vencimiento, inv.lote as lote, usu.nombre as usuario
		   FROM  $sTable $sWhere ";//LIMIT $offset,$per_page
		   $query = mysqli_query($con, $sql);
		   $data=array('query'=>$query, 'numrows'=>$numrows);
		   return $data;
		}

		function existencia_general($ordenado, $por, $ruc_empresa, $con, $marca){
			if (empty($marca)){
				$condicion_marca="";
				}else{
				$condicion_marca=" and mar_pro.id_marca=".$marca;
			}

				$sTable = "existencias_inventario_tmp as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
				$sWhere = "WHERE inv.ruc_empresa ='". $ruc_empresa ." ' $condicion_marca and inv.saldo_producto > 0 order by $ordenado $por" ;
			   $count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
			   $row= mysqli_fetch_array($count_query);
			   $numrows = $row['numrows'];
			   $sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, inv.cantidad_entrada as cantidad_entrada, inv.cantidad_salida as cantidad_salida, 
			   inv.saldo_producto as existencia, med.nombre_medida as medida, bod.nombre_bodega as bodega FROM $sTable $sWhere ";//LIMIT $offset, $per_page
			  $query = mysqli_query($con, $sql);
			  $data=array('query'=>$query, 'numrows'=>$numrows);
			  return $data;
			}

			function existencia_caducidad($ordenado, $por, $ruc_empresa, $con, $marca){	
				if (empty($marca)){
					$condicion_marca="";
					}else{
					$condicion_marca=" and mar_pro.id_marca=".$marca;
				}			
				$sTable = "existencias_inventario_tmp as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
				$sWhere = "WHERE inv.ruc_empresa ='". $ruc_empresa ." ' $condicion_marca and inv.saldo_producto > 0 order by $ordenado $por" ;
				$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
				$row= mysqli_fetch_array($count_query);
				$numrows = $row['numrows'];

			$sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, inv.cantidad_entrada as cantidad_entrada, inv.cantidad_salida as cantidad_salida, 
				inv.saldo_producto as existencia, med.nombre_medida as medida, bod.nombre_bodega as bodega, inv.fecha_caducidad as vencimiento, inv.lote as lote FROM $sTable $sWhere";
				$query = mysqli_query($con, $sql);
				$data=array('query'=>$query, 'numrows'=>$numrows);
				return $data;
			}

				function existencia_lote($ordenado, $por, $ruc_empresa, $con, $marca){		
					if (empty($marca)){
						$condicion_marca="";
						}else{
						$condicion_marca=" and mar_pro.id_marca=".$marca;
					}		
					$sTable = "existencias_inventario_tmp as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
					$sWhere = "WHERE inv.ruc_empresa ='". $ruc_empresa ." ' $condicion_marca and inv.saldo_producto > 0 order by $ordenado $por" ;
					$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
					$row= mysqli_fetch_array($count_query);
					$numrows = $row['numrows'];
					$sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, inv.cantidad_entrada as cantidad_entrada, inv.cantidad_salida as cantidad_salida, 
					inv.saldo_producto as existencia, med.nombre_medida as medida, bod.nombre_bodega as bodega, inv.lote as lote, inv.fecha_caducidad as vencimiento FROM $sTable $sWhere";
					$query = mysqli_query($con, $sql);
					$data=array('query'=>$query, 'numrows'=>$numrows);
					return $data;
				}

		if($data['numrows'] >0 ){			
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
								 ->setDescription("Reporte de inventarios")
								 ->setKeywords("reporte inventarios")
								 ->setCategory("Reporte excel");

			//para sacar el nombre de la empresa
			$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
			$resultado_empresa = mysqli_query($con,$sql_empresa);
			$empresa_info=mysqli_fetch_array($resultado_empresa);
			$tituloEmpresa= $empresa_info['nombre'];
	
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
						->setCellValue('M3',  $titulosColumnas[12])
						->setCellValue('N3',  $titulosColumnas[13])
						;
			$i = 4;
			
			switch ($tipo) {
				case "1"://entradas
					while ($row=mysqli_fetch_array($data['query'])){
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  "=\"" . strtoupper($row['codigo_producto']) . "\"")
						->setCellValue('B'.$i,  strtoupper($row['nombre_producto']))
						->setCellValue('C'.$i,  number_format($row['cantidad_entrada'],4,'.',''))
						->setCellValue('D'.$i,  strtoupper($row['medida']))
						->setCellValue('E'.$i,  strtoupper($row['marca']))
						->setCellValue('F'.$i,  number_format($row['costo_unitario'],4,'.',''))
						->setCellValue('G'.$i,  "=\"" . strtoupper($row['lote']) . "\"")
						->setCellValue('H'.$i,  strtoupper($row['referencia']))
						->setCellValue('I'.$i,  strtoupper($row['bodega']))
						->setCellValue('J'.$i,  date("d-m-Y", strtotime($row['fecha_registro'])))
						->setCellValue('K'.$i,  date("d-m-Y", strtotime($row['fecha_vencimiento'])))
						->setCellValue('L'.$i,  strtoupper($row['usuario']))
						;
						$i++;
						}
				break;
				case "2"://salidas
					while ($row=mysqli_fetch_array($data['query'])){
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  "=\"" . strtoupper($row['codigo_producto']) . "\"")
						->setCellValue('B'.$i,  strtoupper($row['nombre_producto']))
						->setCellValue('C'.$i,  number_format($row['cantidad_salida'],4,'.',''))
						->setCellValue('D'.$i,  strtoupper($row['medida']))
						->setCellValue('E'.$i,  strtoupper($row['marca']))
						->setCellValue('F'.$i,  number_format($row['precio'],4,'.',''))
						->setCellValue('G'.$i,  "=\"" . strtoupper($row['lote']) . "\"")
						->setCellValue('H'.$i,  strtoupper($row['referencia']))
						->setCellValue('I'.$i,  strtoupper($row['bodega']))
						->setCellValue('J'.$i,  date("d-m-Y", strtotime($row['fecha_registro'])))
						->setCellValue('K'.$i,  date("d-m-Y", strtotime($row['fecha_vencimiento'])))
						->setCellValue('L'.$i,  strtoupper($row['usuario']))
						;
						$i++;
						}				
				break;
				case "3"://existencia en general
					while ($row=mysqli_fetch_array($data['query'])){
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  "=\"" . strtoupper($row['codigo_producto']) . "\"")
						->setCellValue('B'.$i,  strtoupper($row['nombre_producto']))
						->setCellValue('C'.$i,  number_format($row['cantidad_entrada'],4,'.',''))
						->setCellValue('D'.$i,  number_format($row['cantidad_salida'],4,'.',''))
						->setCellValue('E'.$i,  number_format($row['existencia'],4,'.',''))
						->setCellValue('F'.$i,  strtoupper($row['marca']))
						->setCellValue('G'.$i,  strtoupper($row['medida']))
						->setCellValue('H'.$i,  strtoupper($row['bodega']))
						;
						$i++;
						}

				break;
				case "4"://existencia caducidad
					while ($row=mysqli_fetch_array($data['query'])){
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  "=\"" . strtoupper($row['codigo_producto']) . "\"")
						->setCellValue('B'.$i,  strtoupper($row['nombre_producto']))
						->setCellValue('C'.$i,  number_format($row['cantidad_entrada'],4,'.',''))
						->setCellValue('D'.$i,  number_format($row['cantidad_salida'],4,'.',''))
						->setCellValue('E'.$i,  number_format($row['existencia'],4,'.',''))
						->setCellValue('F'.$i,  strtoupper($row['marca']))
						->setCellValue('G'.$i,  strtoupper($row['medida']))
						->setCellValue('H'.$i,  strtoupper($row['bodega']))
						->setCellValue('I'.$i,  "=\"" . strtoupper($row['lote']) . "\"")
						->setCellValue('J'.$i,   date("d-m-Y", strtotime($row['vencimiento'])))
						;
						$i++;
						}

				break;
				case "5"://existencia lote
					while ($row=mysqli_fetch_array($data['query'])){
						$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i,  "=\"" . strtoupper($row['codigo_producto']) . "\"")
						->setCellValue('B'.$i,  strtoupper($row['nombre_producto']))
						->setCellValue('C'.$i,  number_format($row['cantidad_entrada'],4,'.',''))
						->setCellValue('D'.$i,  number_format($row['cantidad_salida'],4,'.',''))
						->setCellValue('E'.$i,  number_format($row['existencia'],4,'.',''))
						->setCellValue('F'.$i,  strtoupper($row['marca']))
						->setCellValue('G'.$i,  strtoupper($row['medida']))
						->setCellValue('H'.$i,  strtoupper($row['bodega']))
						->setCellValue('I'.$i,  "=\"" . strtoupper($row['lote']) . "\"")
						->setCellValue('J'.$i,   date("d-m-Y", strtotime($row['vencimiento'])))
						;
						$i++;
						}
	
				break;
			}

											
			for($i = 'A'; $i <= 'B'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Inventarios');

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			// Inmovilizar paneles 
			//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ReporteInventarios.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}else{
			echo('No hay resultados para mostrar, posiblemente debe primero generar el reporte dando clic en el boton ver, y luego descargar el archivo excel.');
			?>
			<a href="javascript:history.go(-1);">Atras</a>
			<?php
		}
	//}
?>