<?php
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];	
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	
	if($action == 'existencia_consignacion_ventas'){
		$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."'");
		$tipo_existencia = mysqli_real_escape_string($con,(strip_tags($_POST['tipo_existencia'], ENT_QUOTES)));
		$id_nombre_buscar = mysqli_real_escape_string($con,(strip_tags($_POST['id_nombre_buscar'], ENT_QUOTES)));
		$nombre_buscar = mysqli_real_escape_string($con,(strip_tags($_POST['nombre_buscar'], ENT_QUOTES)));
		
		if(empty($nombre_buscar)){
			echo "Ingrese dato para buscar.";
			exit;
		}
					//para sacar el nombre de la empresa
				$sql_empresa = "SELECT * FROM empresas where ruc= '".$ruc_empresa."'";      
				$resultado_empresa = mysqli_query($con,$sql_empresa);
				$empresa_info=mysqli_fetch_array($resultado_empresa);
				$tituloEmpresa= $empresa_info['nombre_comercial'];
				$tituloReporte = "Consignaciones en ventas";
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
				 ->setDescription("Consignaciones en ventas")
				 ->setKeywords("Consignaciones en ventas")
				 ->setCategory("Consignaciones en ventas");
				 
		switch ($tipo_existencia) {
			case "1":
				$tituloBusqueda='Cliente: '.$nombre_buscar;
				$resultado=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and id_cli_pro='".$id_nombre_buscar."' and tipo_consignacion='VENTA' and operacion='ENTRADA' order by numero_consignacion desc");
				
				$titulosColumnas = array('#CV','Fecha','Código','Producto','Lote','NUP','Cantidad','Facturado','No. Factura','Devuelto','Saldo');
				$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:K1')
						->mergeCells('A2:K2')
						->mergeCells('A3:K3')
						;			
				// Se agregan los titulos del reporte
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1',  $tituloEmpresa)
				->setCellValue('A2',  $tituloReporte)
				->setCellValue('A3',  $tituloBusqueda)
				->setCellValue('A4',  $titulosColumnas[0])
				->setCellValue('B4',  $titulosColumnas[1])
				->setCellValue('C4',  $titulosColumnas[2])
				->setCellValue('D4',  $titulosColumnas[3])
				->setCellValue('E4',  $titulosColumnas[4])
				->setCellValue('F4',  $titulosColumnas[5])
				->setCellValue('G4',  $titulosColumnas[6])
				->setCellValue('H4',  $titulosColumnas[7])
				->setCellValue('I4',  $titulosColumnas[8])
				->setCellValue('J4',  $titulosColumnas[9])
				->setCellValue('K4',  $titulosColumnas[10])
				;
				$saldo_subtotal=array();
				$i = 5;
		while ($row=mysqli_fetch_array($resultado)){
		$fecha_consignacion=$row['fecha_consignacion'];
		$codigo_unico=strtoupper ($row['codigo_unico']);
		$observaciones=strtoupper ($row['observaciones']);
		$ncv=$row['numero_consignacion'];

		$detalle_consignacion=mysqli_query($con,"SELECT * FROM detalle_consignacion WHERE codigo_unico='".$codigo_unico."' ");
		$cantidad_suma=array();
		$total_facturado_suma=array();
		$total_devuelto_suma=array();
				
				while ($row_detalle = mysqli_fetch_array($detalle_consignacion)) {
					$codigo_producto=$row_detalle['codigo_producto'];
					$id_producto=$row_detalle['id_producto'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$lote=$row_detalle['lote'];
					$nup=$row_detalle['nup'];
					$cantidad=$row_detalle['cant_consignacion'];
					$cantidad_suma[]=$row_detalle['cant_consignacion'];
					
					$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturas=mysqli_fetch_array($facturas);
					$todas_facturas=$row_facturas['factura'];
					
					$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturado=mysqli_fetch_array($facturado);
					$total_facturado=$row_facturado['facturado'];
					$total_facturado_suma[]=$row_facturado['facturado'];
					
					$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_devuelto=mysqli_fetch_array($devuelto);
					$total_devuelto=$row_devuelto['devuelto'];
					$total_devuelto_suma[]=$row_devuelto['devuelto'];

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i,  $ncv)
				->setCellValue('B'.$i,  date("d-m-Y", strtotime($fecha_consignacion)))
				->setCellValue('C'.$i,  "=\"" . $codigo_producto . "\"")
				->setCellValue('D'.$i,  "=\"" . $nombre_producto . "\"")
				->setCellValue('E'.$i,  "=\"" . $lote . "\"")
				->setCellValue('F'.$i,  "=\"" . $nup . "\"")
				->setCellValue('G'.$i,  number_format($cantidad,2,'.',''))
				->setCellValue('H'.$i,  number_format($total_facturado,2,'.',''))
				->setCellValue('I'.$i,  $todas_facturas)
				->setCellValue('J'.$i,  number_format($total_devuelto,2,'.',''))
				->setCellValue('K'.$i,  number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''))
				;
				$i++;				
				}
				$saldo_subtotal[]=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
				}
				$saldo_final=array_sum($saldo_subtotal);
				break;
			case "2":
				$resultado=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and numero_consignacion='".$nombre_buscar."' and tipo_consignacion='VENTA' and operacion='ENTRADA'");
				$query=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and numero_consignacion='".$nombre_buscar."' and tipo_consignacion='VENTA' and operacion='ENTRADA'");
				$detalle=mysqli_fetch_array($query);
				$id_cliente=$detalle['id_cli_pro'];
				$fecha_consignacion=date("d-m-Y", strtotime($detalle['fecha_consignacion']));
				$codigo_unico=strtoupper ($detalle['codigo_unico']);
				$observaciones=strtoupper ($detalle['observaciones']);

				$clientes=mysqli_query($con,"SELECT * FROM clientes WHERE id='".$id_cliente."' ");
				$row_cliente=mysqli_fetch_array($clientes);
				$nombre_cliente=$row_cliente['nombre'];
				$tituloBusqueda='#CV:'.$nombre_buscar.' Fecha: '. $fecha_consignacion .' Cliente: '.$nombre_cliente.' Observaciones: '.$observaciones;

				$titulosColumnas = array('Código','Producto','Lote','NUP','Cantidad','Facturado','No. Factura','Devuelto','Saldo');
				$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:I1')
						->mergeCells('A2:I2')
						->mergeCells('A3:I3')
						;			
				// Se agregan los titulos del reporte
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1',  $tituloEmpresa)
				->setCellValue('A2',  $tituloReporte)
				->setCellValue('A3',  $tituloBusqueda)
				->setCellValue('A4',  $titulosColumnas[0])
				->setCellValue('B4',  $titulosColumnas[1])
				->setCellValue('C4',  $titulosColumnas[2])
				->setCellValue('D4',  $titulosColumnas[3])
				->setCellValue('E4',  $titulosColumnas[4])
				->setCellValue('F4',  $titulosColumnas[5])
				->setCellValue('G4',  $titulosColumnas[6])
				->setCellValue('H4',  $titulosColumnas[7])
				->setCellValue('I4',  $titulosColumnas[8])
				;
				
				$saldo_subtotal=array();
				$i = 5;
		while ($row=mysqli_fetch_array($resultado)){
		$fecha_consignacion=$row['fecha_consignacion'];
		$codigo_unico=strtoupper ($row['codigo_unico']);
		$observaciones=strtoupper ($row['observaciones']);
		$ncv=$row['numero_consignacion'];

		$detalle_consignacion=mysqli_query($con,"SELECT * FROM detalle_consignacion WHERE codigo_unico='".$codigo_unico."' order by nombre_producto asc");
		$cantidad_suma=array();
		$total_facturado_suma=array();
		$total_devuelto_suma=array();
				
				while ($row_detalle = mysqli_fetch_array($detalle_consignacion)) {
					$codigo_producto=$row_detalle['codigo_producto'];
					$id_producto=$row_detalle['id_producto'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$lote=$row_detalle['lote'];
					$nup=$row_detalle['nup'];
					$cantidad=$row_detalle['cant_consignacion'];
					$cantidad_suma[]=$row_detalle['cant_consignacion'];
					
					$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturas=mysqli_fetch_array($facturas);
					$todas_facturas=$row_facturas['factura'];
					
					$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturado=mysqli_fetch_array($facturado);
					$total_facturado=$row_facturado['facturado'];
					$total_facturado_suma[]=$row_facturado['facturado'];
					
					$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$ncv."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_devuelto=mysqli_fetch_array($devuelto);
					$total_devuelto=$row_devuelto['devuelto'];
					$total_devuelto_suma[]=$row_devuelto['devuelto'];

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i,  "=\"" . $codigo_producto . "\"")
				->setCellValue('B'.$i,  "=\"" . $nombre_producto . "\"")
				->setCellValue('C'.$i,  "=\"" . $lote . "\"")
				->setCellValue('D'.$i,  "=\"" . $nup . "\"")
				->setCellValue('E'.$i,  number_format($cantidad,2,'.',''))
				->setCellValue('F'.$i,  number_format($total_facturado,2,'.',''))
				->setCellValue('G'.$i,  $todas_facturas)
				->setCellValue('H'.$i,  number_format($total_devuelto,2,'.',''))
				->setCellValue('I'.$i,  number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''))
				;
				$i++;				
				}
				$saldo_subtotal[]=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
				}
				$saldo_final=array_sum($saldo_subtotal);
				break;
			
//reporte por productos	
	case "3":
				$tituloBusqueda='Producto: '.$nombre_buscar;
				$resultado=mysqli_query($con,"SELECT * FROM detalle_consignacion as det_con INNER JOIN encabezado_consignacion as enc_con ON det_con.codigo_unico=enc_con.codigo_unico WHERE det_con.id_producto='".$id_nombre_buscar."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' order by enc_con.numero_consignacion desc");
				$titulosColumnas = array('#CV','Fecha','Cliente','Lote','NUP','Cantidad','Facturado','No. Factura','Devuelto','Saldo');
				$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:J1')
						->mergeCells('A2:J2')
						->mergeCells('A3:J3')
						;			
				// Se agregan los titulos del reporte
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1',  $tituloEmpresa)
				->setCellValue('A2',  $tituloReporte)
				->setCellValue('A3',  $tituloBusqueda)
				->setCellValue('A4',  $titulosColumnas[0])
				->setCellValue('B4',  $titulosColumnas[1])
				->setCellValue('C4',  $titulosColumnas[2])
				->setCellValue('D4',  $titulosColumnas[3])
				->setCellValue('E4',  $titulosColumnas[4])
				->setCellValue('F4',  $titulosColumnas[5])
				->setCellValue('G4',  $titulosColumnas[6])
				->setCellValue('H4',  $titulosColumnas[7])
				->setCellValue('I4',  $titulosColumnas[8])
				->setCellValue('J4',  $titulosColumnas[9])
				;
				
		$saldo_subtotal=array();
		$cantidad_suma=array();
		$total_facturado_suma=array();
		$total_devuelto_suma=array();
				$i = 5;
				while ($row_detalle = mysqli_fetch_array($resultado)) {
					$codigo_producto=$row_detalle['codigo_producto'];
					$id_producto=$row_detalle['id_producto'];
					$codigo_unico=$row_detalle['codigo_unico'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$lote=$row_detalle['lote'];
					$nup=$row_detalle['nup'];
					$cantidad=$row_detalle['cant_consignacion'];
					$cantidad_suma[]=$row_detalle['cant_consignacion'];
				
					$encabezado=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE codigo_unico='".$codigo_unico."' ");
					$row_encabezado=mysqli_fetch_array($encabezado);
					$numero_consignacion=$row_encabezado['numero_consignacion'];
					$fecha_consignacion=$row_encabezado['fecha_consignacion'];
					$id_cliente=$row_encabezado['id_cli_pro'];
					
					$clientes=mysqli_query($con,"SELECT * FROM clientes WHERE id='".$id_cliente."' ");
					$row_cliente=mysqli_fetch_array($clientes);
					$cliente=$row_cliente['nombre'];
					
					$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturas=mysqli_fetch_array($facturas);
					$todas_facturas=$row_facturas['factura'];
					
					$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturado=mysqli_fetch_array($facturado);
					$total_facturado=$row_facturado['facturado'];
					$total_facturado_suma[]=$row_facturado['facturado'];
					
					$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_devuelto=mysqli_fetch_array($devuelto);
					$total_devuelto=$row_devuelto['devuelto'];
					$total_devuelto_suma[]=$row_devuelto['devuelto'];

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i,  $numero_consignacion)
				->setCellValue('B'.$i,  date("d-m-Y", strtotime($fecha_consignacion)))
				->setCellValue('C'.$i,  "=\"" . $cliente . "\"")
				->setCellValue('D'.$i,  "=\"" . $lote . "\"")
				->setCellValue('E'.$i,  "=\"" . $nup . "\"")
				->setCellValue('F'.$i,  number_format($cantidad,2,'.',''))
				->setCellValue('G'.$i,  number_format($total_facturado,2,'.',''))
				->setCellValue('H'.$i,  $todas_facturas)
				->setCellValue('I'.$i,  number_format($total_devuelto,2,'.',''))
				->setCellValue('J'.$i,  number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''))
				;
				$i++;
				}
				
				$saldo_subtotal[]=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
				$saldo_final=array_sum($saldo_subtotal);
			break;
			case "4":
				$tituloBusqueda='NUP: '.$nombre_buscar;
				$resultado=mysqli_query($con,"SELECT * FROM detalle_consignacion as det_con INNER JOIN encabezado_consignacion as enc_con ON det_con.codigo_unico=enc_con.codigo_unico WHERE det_con.nup='".$nombre_buscar."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='ENTRADA' order by enc_con.numero_consignacion desc");
				
				$titulosColumnas = array('#CV','Fecha','Cliente','Lote','Cantidad','Facturado','No. Factura','Devuelto','Saldo');
				$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A1:I1')
						->mergeCells('A2:I2')
						->mergeCells('A3:I3')
						;			
				// Se agregan los titulos del reporte
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1',  $tituloEmpresa)
				->setCellValue('A2',  $tituloReporte)
				->setCellValue('A3',  $tituloBusqueda)
				->setCellValue('A4',  $titulosColumnas[0])
				->setCellValue('B4',  $titulosColumnas[1])
				->setCellValue('C4',  $titulosColumnas[2])
				->setCellValue('D4',  $titulosColumnas[3])
				->setCellValue('E4',  $titulosColumnas[4])
				->setCellValue('F4',  $titulosColumnas[5])
				->setCellValue('G4',  $titulosColumnas[6])
				->setCellValue('H4',  $titulosColumnas[7])
				->setCellValue('I4',  $titulosColumnas[8])
				;
				
		$saldo_subtotal=array();
		$cantidad_suma=array();
		$total_facturado_suma=array();
		$total_devuelto_suma=array();
				$i = 5;
				while ($row_detalle = mysqli_fetch_array($resultado)) {
					$codigo_producto=$row_detalle['codigo_producto'];
					$id_producto=$row_detalle['id_producto'];
					$codigo_unico=$row_detalle['codigo_unico'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$lote=$row_detalle['lote'];
					$nup=$row_detalle['nup'];
					$cantidad=$row_detalle['cant_consignacion'];
					$cantidad_suma[]=$row_detalle['cant_consignacion'];
				
					$encabezado=mysqli_query($con,"SELECT * FROM encabezado_consignacion WHERE codigo_unico='".$codigo_unico."' ");
					$row_encabezado=mysqli_fetch_array($encabezado);
					$numero_consignacion=$row_encabezado['numero_consignacion'];
					$fecha_consignacion=$row_encabezado['fecha_consignacion'];
					$id_cliente=$row_encabezado['id_cli_pro'];
					
					$clientes=mysqli_query($con,"SELECT * FROM clientes WHERE id='".$id_cliente."' ");
					$row_cliente=mysqli_fetch_array($clientes);
					$cliente=$row_cliente['nombre'];
					
					$facturas=mysqli_query($con,"SELECT concat(serie_sucursal,'-',factura_venta) as factura FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturas=mysqli_fetch_array($facturas);
					$todas_facturas=$row_facturas['factura'];
					
					$facturado=mysqli_query($con,"SELECT sum(cant_consignacion) as facturado FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='FACTURA' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_facturado=mysqli_fetch_array($facturado);
					$total_facturado=$row_facturado['facturado'];
					$total_facturado_suma[]=$row_facturado['facturado'];
					
					$devuelto=mysqli_query($con,"SELECT sum(cant_consignacion) as devuelto FROM encabezado_consignacion enc_con INNER JOIN detalle_consignacion det_con ON enc_con.codigo_unico=det_con.codigo_unico WHERE enc_con.ruc_empresa='".$ruc_empresa."' and det_con.ruc_empresa='".$ruc_empresa."' and enc_con.tipo_consignacion='VENTA' and enc_con.operacion='DEVOLUCIÓN' and det_con.numero_orden_entrada='".$numero_consignacion."' and det_con.id_producto='".$id_producto."' and det_con.lote='".$lote."' and det_con.nup='".$nup."'");
					$row_devuelto=mysqli_fetch_array($devuelto);
					$total_devuelto=$row_devuelto['devuelto'];
					$total_devuelto_suma[]=$row_devuelto['devuelto'];

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i,  $numero_consignacion)
				->setCellValue('B'.$i,  date("d-m-Y", strtotime($fecha_consignacion)))
				->setCellValue('C'.$i,  "=\"" . $cliente . "\"")
				->setCellValue('D'.$i,  "=\"" . $lote . "\"")
				->setCellValue('E'.$i,  number_format($cantidad,2,'.',''))
				->setCellValue('F'.$i,  number_format($total_facturado,2,'.',''))
				->setCellValue('G'.$i,  $todas_facturas)
				->setCellValue('H'.$i,  number_format($total_devuelto,2,'.',''))
				->setCellValue('I'.$i,  number_format($cantidad-$total_facturado-$total_devuelto,0,'.',''))
				;
				$i++;
				}
				
				$saldo_subtotal[]=array_sum($cantidad_suma)-array_sum($total_facturado_suma)-array_sum($total_devuelto_suma);
				$saldo_final=array_sum($saldo_subtotal);
				
			break;
			}
				
			$t=$i+1;
			$objPHPExcel->setActiveSheetIndex(0)
						->mergeCells('A'.$t.':C'.$t)
						->setCellValue('A'.$t,  'Productos en consignación')
						->setCellValue('D'.$t,  number_format($saldo_final,0,'.',''))
						;
					
			for($i = 'A'; $i <= 'K'; $i++){
				$objPHPExcel->setActiveSheetIndex(0)			
					->getColumnDimension($i)->setAutoSize(TRUE);
			}
			
			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('CV');

			// inmovilizar paneles
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,5);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ConsignacionVentas.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
				
	}//final de todo
?>