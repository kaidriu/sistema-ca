<?php
//include("../conexiones/conectalogin.php");
include("../ajax/imprime_documento.php");

class enviar_documentos_sri{
	
	public function envia_mail($id_documento, $tipo_documento, $mail_receptor){
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	
	//datos de mi empresa
		$busca_datos_empresa = "SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."' ";
		$result_empresa = $con->query($busca_datos_empresa);
		$datos_empresa = mysqli_fetch_array($result_empresa);
		$nombre_comercial =utf8_encode($datos_empresa['nombre_comercial']);
		$razon_social =utf8_encode($datos_empresa['nombre']);
		$fecha_hoy = date_create(date("Y-m-d H:i:s"));
					
	$servidor="internet";//aqui se cambia local o internet

	switch ($tipo_documento) {
		case "factura":
		$tabla="encabezado_".$tipo_documento;
		$where="WHERE id_encabezado_".$tipo_documento;
		break;
		case "retencion": 
		$tabla="encabezado_".$tipo_documento;
		$where="WHERE id_encabezado_".$tipo_documento;
		break;
		case "nc":
		$tabla="encabezado_".$tipo_documento;
		$where="WHERE id_encabezado_".$tipo_documento;
		break;
		case "gr": 
		$tabla="encabezado_".$tipo_documento;
		$where="WHERE id_encabezado_".$tipo_documento;
		break;
		case "liquidacion":
		$tabla="encabezado_".$tipo_documento;
		$where="WHERE id_encabezado_liq";
		break;
		case "egreso":
		$tabla="ingresos_egresos";
		$where="WHERE id_ing_egr";
		break;
		case "cxc_individual":
		$tabla="saldo_porcobrar_porpagar";
		$where="WHERE id_saldo";
		break;
		case "cxc_todos":
		$tabla="saldo_porcobrar_porpagar";
		$where="WHERE id_cli_pro ";
		break;
		case "solicitar_retencion":
			$tabla="encabezado_factura";
			$where="WHERE id_encabezado_factura";
		break;
	}
		//consulta datos de los encabezados
		$busca_datos_encabezados = "SELECT * FROM $tabla $where = '".$id_documento."' ";
		$resultado_encabezado = $con->query($busca_datos_encabezados);
		$datos_encabezados = mysqli_fetch_array($resultado_encabezado);
		//para sacar las consultas de variables dependiendo de cada tipo de documento consulta en encabezado_
		switch ($tipo_documento) {
		case "factura":
				$serie =$datos_encabezados['serie_factura'];
				$aut_sri =$datos_encabezados['aut_sri'];
				$secuencial =$datos_encabezados['secuencial_factura'];
				$id_cliente =$datos_encabezados['id_cliente'];
				$tipo_comprobante="FACTURA";
				$valor_total =$datos_encabezados['total_factura'];
				$correo_asunto = "Nueva factura electrónica";
			break;
		case "retencion":
				$serie =$datos_encabezados['serie_retencion'];
				$aut_sri =$datos_encabezados['aut_sri'];
				$secuencial =$datos_encabezados['secuencial_retencion'];
				$id_proveedor =$datos_encabezados['id_proveedor'];
				$tipo_comprobante="RETENCIÓN";
				$valor_total =$datos_encabezados['total_retencion'];
				$correo_asunto = "Nueva retención electrónica";
			break;
		case "nc":
				$serie =$datos_encabezados['serie_nc'];
				$aut_sri =$datos_encabezados['aut_sri'];
				$secuencial =$datos_encabezados['secuencial_nc'];
				$id_cliente =$datos_encabezados['id_cliente'];
				$tipo_comprobante="NOTA DE CRÉDITO";
				$valor_total =$datos_encabezados['total_nc'];
				$correo_asunto = "Nueva nota de crédito electrónica";
			break;
		case "gr":
				$serie =$datos_encabezados['serie_gr'];
				$aut_sri =$datos_encabezados['aut_sri'];
				$secuencial =$datos_encabezados['secuencial_gr'];
				$id_cliente =$datos_encabezados['id_cliente'];
				$id_transportista =$datos_encabezados['id_transportista'];
				$tipo_comprobante="GUÍA DE REMISIÓN";
				$valor_total ="0.00";
				$correo_asunto = "Nueva guía de remisión electrónica";
			break;
		case "liquidacion":
				$serie =$datos_encabezados['serie_liquidacion'];
				$aut_sri =$datos_encabezados['aut_sri'];
				$secuencial =$datos_encabezados['secuencial_liquidacion'];
				$id_proveedor =$datos_encabezados['id_proveedor'];
				$tipo_comprobante="LIQUIDACIÓN DE COMPRA DE BIENES Y PRESTACIÓN DE SERVICIOS";
				$valor_total =$datos_encabezados['total_liquidacion'];
				$correo_asunto = "Nueva liquidación de compras electrónica";
			break;
		case "egreso":
				$serie ="";
				$secuencial =$datos_encabezados['numero_ing_egr'];
				$id_proveedor =$datos_encabezados['id_cli_pro'];
				$beneficiario =$datos_encabezados['nombre_ing_egr'];
				$tipo_comprobante="EGRESO";
				$valor_total =$datos_encabezados['valor_ing_egr'];
				$correo_asunto = "Nuevo pago procesado";
			break;
			case "cxc_individual"://para enviar el reporte de cxc individual de cada facturas del mismo cliente
				$serie ="";
				$secuencial =$datos_encabezados['numero_documento'];
				$id_cliente =$datos_encabezados['id_cli_pro'];
				$cliente =$datos_encabezados['nombre_cli_pro'];
				$tipo_comprobante="FACTURA PENDIENTE DE PAGO";
				$valor_total =$datos_encabezados['total_factura']-$datos_encabezados['total_nc']-$datos_encabezados['total_ing']-$datos_encabezados['ing_tmp']-$datos_encabezados['total_ret'];
				$correo_asunto = "Factura de venta pendiente de pago";
			break;
			case "cxc_todos"://para enviar el reporte de cxc de todas las facturas del mismo cliente
				$serie ="";
				$secuencial ="DETALLE ADJUNTO";
				$id_cliente =$datos_encabezados['id_cli_pro'];
				$cliente =$datos_encabezados['nombre_cli_pro'];
				$tipo_comprobante="FACTURAS PENDIENTES DE PAGO";
				$busca_suma_saldo=mysqli_query($con, "SELECT * FROM saldo_porcobrar_porpagar WHERE id_cli_pro = '".$id_documento."' and ruc_empresa='".$ruc_empresa."' and tipo='POR_COBRAR' group by id_documento");
				//$busca_suma_saldo = mysqli_query($con, "SELECT * FROM saldo_porcobrar_porpagar WHERE id_cli_pro= '".$id_cliente."' ");
				$suma_total=0;
				while ($datos_suma = mysqli_fetch_array($busca_suma_saldo)){
				$suma_total += $datos_suma['total_factura']-$datos_suma['total_nc']-$datos_suma['total_ing']-$datos_suma['ing_tmp']-$datos_suma['total_ret'];
				}	
				$valor_total=$suma_total;
				$correo_asunto = "Facturas de venta pendientes de pago";
			break;
			case "solicitar_retencion"://para solicitar retencion a un cliente
				$serie =$datos_encabezados['serie_factura'];
				$secuencial =$datos_encabezados['secuencial_factura'];
				$id_cliente =$datos_encabezados['id_cliente'];
				$tipo_comprobante="RETENCIÓN PENDIENTE";
				$valor_total ="";
				$correo_asunto = "Retenciones Pendientes";
			break;
			}
		
			if ($serie==""){
			$condicion_serie="";
			}else{
			$condicion_serie="and serie=".$serie;	
			}
			//datos de la sucursal
				$busca_datos_sucursales = "SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' $condicion_serie";
				$result_sucursales = $con->query($busca_datos_sucursales);
				$datos_sucursal = mysqli_fetch_array($result_sucursales);
				$nombre_sucursal =$datos_sucursal['nombre_sucursal'];	
			
			if ($tipo_documento=="factura" || $tipo_documento=="nc" || $tipo_documento=="solicitar_retencion" ){
			//para buscar datos del cliente
				$busca_datos_cliente = "SELECT * FROM clientes WHERE id = '".$id_cliente."' ";
				$resultado_cliente = $con->query($busca_datos_cliente);
				$datos_cliente = mysqli_fetch_array($resultado_cliente);
				$ruc_cliente =$datos_cliente['ruc'];
				$nombre_receptor = $datos_cliente['nombre'];
			}
			//datos del proveedor
			if ($tipo_documento=="retencion" || $tipo_documento=="liquidacion" ){
				$busca_datos_proveedor = "SELECT * FROM proveedores WHERE id_proveedor= '".$id_proveedor."' ";
				$resultado_proveedor = $con->query($busca_datos_proveedor);
				$datos_proveedor = mysqli_fetch_array($resultado_proveedor);
				$ruc_proveedor =$datos_proveedor['ruc_proveedor'];
				$nombre_receptor =$datos_proveedor['razon_social'];
			}
			
			//datos del egreso
			if ($tipo_documento=="egreso"){
				$nombre_receptor = $beneficiario;
			}
			
			//datos de la cuenta por cobrar
			if ($tipo_documento=="cxc_individual" || $tipo_documento=="cxc_todos"){
				$nombre_receptor = $cliente;
			}
			
			
			//datos del transportista
			if ($tipo_documento=="gr"){
				//busca el documento en base al ruc del chofer
				$busca_datos_transportista = "SELECT * FROM clientes WHERE id = '".$id_transportista."' ";
				$resultado_transportista = $con->query($busca_datos_transportista);
				$datos_transportista = mysqli_fetch_array($resultado_transportista);
				$ruc_transportista =$datos_transportista['ruc'];
				//$nombre_receptor =$datos_transportista['nombre'];
				
				//buscar el nombre del cliente a quien fue enviada la gr
				$busca_datos_cliente = "SELECT * FROM clientes WHERE id = '".$id_cliente."' ";
				$resultado_cliente = $con->query($busca_datos_cliente);
				$datos_cliente = mysqli_fetch_array($resultado_cliente);
				//$ruc_cliente =$datos_cliente['ruc'];
				$nombre_receptor = $datos_cliente['nombre'];				
			}
			
		
		$carpeta_ftp = new imprime_documentos();
	//para ver las direcciones de donde estan los documentos electronicos
		switch ($tipo_documento) {
		case "factura":
		$pdf_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'facturas_autorizadas', $ruc_empresa, $ruc_cliente, 'FAC', $serie, $secuencial, '.pdf');
		$xml_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'facturas_autorizadas', $ruc_empresa, $ruc_cliente, 'FAC', $serie, $secuencial, '.xml');
		$numero_factura = $serie . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
		$pdf="http://64.225.69.65:8000/facturas_autorizadas/".$ruc_empresa."/".$ruc_cliente."/FAC". $numero_factura.".pdf";
		$xml="http://64.225.69.65:8000/facturas_autorizadas/".$ruc_empresa."/".$ruc_cliente."/FAC". $numero_factura.".xml";
		break;
		case "retencion":
		$pdf_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'retenciones_autorizadas', $ruc_empresa, $ruc_proveedor, 'CR', $serie, $secuencial, '.pdf');
		$xml_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'retenciones_autorizadas', $ruc_empresa, $ruc_proveedor, 'CR', $serie, $secuencial, '.xml');
		$numero_ret = $serie . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
		$pdf="http://64.225.69.65:8000/retenciones_autorizadas/".$ruc_empresa."/".$ruc_proveedor."/CR". $numero_ret.".pdf";
		$xml="http://64.225.69.65:8000/retenciones_autorizadas/".$ruc_empresa."/".$ruc_proveedor."/CR". $numero_ret.".xml";
		break;
		case "nc":
		$pdf_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'nc_autorizadas', $ruc_empresa, $ruc_cliente, 'NC', $serie, $secuencial, '.pdf');
		$xml_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'nc_autorizadas', $ruc_empresa, $ruc_cliente, 'NC', $serie, $secuencial, '.xml');
		$numero_nc = $serie . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
		$pdf="http://64.225.69.65:8000/nc_autorizadas/".$ruc_empresa."/".$ruc_cliente."/NC". $numero_nc.".pdf";
		$xml="http://64.225.69.65:8000/nc_autorizadas/".$ruc_empresa."/".$ruc_cliente."/NC". $numero_nc.".xml";
		break;
		case "gr":
		$pdf_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'guias_autorizadas', $ruc_empresa, $ruc_transportista, 'GR', $serie, $secuencial, '.pdf');
		$xml_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'guias_autorizadas', $ruc_empresa, $ruc_transportista, 'GR', $serie, $secuencial, '.xml');
		$numero_gr = $serie . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
		$pdf="http://64.225.69.65:8000/guias_autorizadas/".$ruc_empresa."/".$ruc_transportista."/GR". $numero_gr.".pdf";
		$xml="http://64.225.69.65:8000/guias_autorizadas/".$ruc_empresa."/".$ruc_transportista."/GR". $numero_gr.".xml";
		break;
		case "liquidacion":
		$pdf_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'liquidaciones_autorizadas', $ruc_empresa, $ruc_proveedor, 'LIQ', $serie, $secuencial, '.pdf');
		$xml_a_enviar = $carpeta_ftp->copia_documento_tmp($servidor, 'liquidaciones_autorizadas', $ruc_empresa, $ruc_proveedor, 'LIQ', $serie, $secuencial, '.xml');
		$numero_lc = $serie . "-" . str_pad($secuencial, 9, "000000000", STR_PAD_LEFT);
		$pdf="http://64.225.69.65:8000/liquidaciones_autorizadas/".$ruc_empresa."/".$ruc_proveedor."/LIQ". $numero_lc.".pdf";
		$xml="http://64.225.69.65:8000/liquidaciones_autorizadas/".$ruc_empresa."/".$ruc_proveedor."/LIQ". $numero_lc.".xml";
		break;
		}

		//para el detalle del cuerpo del mail
		switch ($tipo_documento) {
		case "factura":
		$linea_tres="Esta es una notificación automática de un documento tributario electrónico emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)."<p>";
		$linea_cinco .="Clave de acceso: ".$aut_sri."<p>";
		$linea_siete="Los detalles generales del comprobante pueden ser consultados en el archivo pdf adjunto en este correo."."<p>";
		$linea_siete .='<a href="'.$pdf.'" title="Descargar pdf"><button style="border-color:red;">Descargar PDF</button></a> <a href="'.$xml.'" title="Descargar xml"><button style="border-color:black;">Descargar XML</button></a>'; 
		$linea_ocho="Esta factura fue generada desde www.CaMaGaRe.com"."<p>";
		break;
		case "retencion": 
		$linea_tres="Esta es una notificación automática de un documento tributario electrónico emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)."<p>";
		$linea_cinco .="Clave de acceso: ".$aut_sri."<p>";
		$linea_siete="Los detalles generales del comprobante pueden ser consultados en el archivo pdf adjunto en este correo."."<p>";
		$linea_siete .='<a href="'.$pdf.'" title="Descargar pdf"><button style="border-color:red;">Descargar PDF</button></a> <a href="'.$xml.'" title="Descargar xml"><button style="border-color:black;">Descargar XML</button></a>'; 
		$linea_ocho="Esta retención fue generada desde www.CaMaGaRe.com"."<p>";
		break;
		case "nc":
		$linea_tres="Esta es una notificación automática de un documento tributario electrónico emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)."<p>";
		$linea_cinco .="Clave de acceso: ".$aut_sri."<p>";
		$linea_siete="Los detalles generales del comprobante pueden ser consultados en el archivo pdf adjunto en este correo."."<p>";
		$linea_siete .='<a href="'.$pdf.'" title="Descargar pdf"><button style="border-color:red;">Descargar PDF</button></a> <a href="'.$xml.'" title="Descargar xml"><button style="border-color:black;">Descargar XML</button></a>'; 
		$linea_ocho="Esta nota de crédito fue generada desde www.CaMaGaRe.com"."<p>";
		break;
		case "gr":
		$linea_tres="Esta es una notificación automática de un documento tributario electrónico emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)."<p>";
		$linea_cinco .="Clave de acceso: ".$aut_sri."<p>";
		$linea_siete="Los detalles generales del comprobante pueden ser consultados en el archivo pdf adjunto en este correo."."<p>";
		$linea_siete .='<a href="'.$pdf.'" title="Descargar pdf"><button style="border-color:red;">Descargar PDF</button></a> <a href="'.$xml.'" title="Descargar xml"><button style="border-color:black;">Descargar XML</button></a>'; 
		$linea_ocho="Esta guía de remisión fue generada desde www.CaMaGaRe.com"."<p>";
		break;
		case "liquidacion":
		$linea_tres="Esta es una notificación automática de un documento tributario electrónico emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)."<p>";
		$linea_cinco .="Clave de acceso: ".$aut_sri."<p>";
		$linea_siete="Los detalles generales del comprobante pueden ser consultados en el archivo pdf adjunto en este correo."."<p>";
		$linea_siete .='<a href="'.$pdf.'" title="Descargar pdf"><button style="border-color:red;">Descargar PDF</button></a> <a href="'.$xml.'" title="Descargar xml"><button style="border-color:black;">Descargar XML</button></a>'; 
		$linea_ocho="Esta liquidación de compras fue generada desde www.CaMaGaRe.com"."<p>";
		break;
		case "egreso":
		$busca_encabezado_ingreso=mysqli_query($con, "SELECT * FROM ingresos_egresos WHERE id_ing_egr = '".$id_documento."' ");
		$encabezado_ingresos = mysqli_fetch_array($busca_encabezado_ingreso);
		$codigo_unico=$encabezado_ingresos['codigo_documento'];
		$busca_detalle=mysqli_query($con, "SELECT * FROM detalle_ingresos_egresos WHERE codigo_documento = '".$codigo_unico."' ");
		$busca_pagos=mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr as fpei INNER JOIN formas_de_pago as fp ON fpei.codigo_forma_pago=fp.codigo_pago WHERE fpei.codigo_documento = '".$codigo_unico."' and fp.aplica_a='EGRESO'");
		$linea_siete ='<table border><tr><th>Detalle del pago</th><th>Valor</th></tr>';
		while($detalle = mysqli_fetch_array($busca_detalle)){
		$linea_siete .= '<tr><td>'. $detalle['detalle_ing_egr'].'</td>';
		$linea_siete .= '<td>'.number_format($detalle['valor_ing_egr'],2,'.','').'</td>';
		}
		$linea_siete .='</table><br>';
		$linea_siete .='<table border><tr><th>Forma de pago</th><th>Valor</th></tr>';
		while($detalle_pago = mysqli_fetch_array($busca_pagos)){
		$linea_siete .= '<tr><td>'. $detalle_pago['nombre_pago'].'</td>';
		$linea_siete .= '<td>'.number_format($detalle_pago['valor_forma_pago'],2,'.','').'</td>';
		}
		$linea_siete .='</table>';
		$linea_tres="Esta es una notificación automática de un documento emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)."<p>";
		$linea_ocho="Este comprobante de egreso fue generado desde www.CaMaGaRe.com"."<p>";
		break;
		
		case "cxc_individual":
		$busca_encabezado_cxc=mysqli_query($con, "SELECT * FROM saldo_porcobrar_porpagar WHERE id_saldo = '".$id_documento."' ");
		$linea_siete ='<table border><tr><th>Fecha</th><th>Factura</th><th>Total</th><th>NC</th><th>Abonos</th><th>Retenciones</th><th>Saldo</th><th>D�as</th></tr>';
		while($detalle = mysqli_fetch_array($busca_encabezado_cxc)){
				$saldo=$detalle['total_factura']-$detalle['total_nc']-$detalle['total_ing']-$detalle['ing_tmp']-$detalle['total_ret'];
				$fecha_vencimiento = date_create($detalle['fecha_documento']);
				$diferencia_dias = date_diff($fecha_hoy, $fecha_vencimiento);
				$total_dias=$diferencia_dias->format('%a');
		$linea_siete .= '<tr><td>'.date("d-m-Y", strtotime($detalle['fecha_documento'])).'</td>';
		$linea_siete .= '<td>'.$detalle['numero_documento'].'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_factura'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_nc'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_ing']+$detalle['ing_tmp'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_ret'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($saldo,2,'.','').'</td>';
		$linea_siete .= '<td>'.$total_dias.'</td>';
		}
		$linea_siete .='</table><br>';
		$linea_tres="Esta es una notificación de cobro de un documento emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: ".$secuencial."<p>";
		$linea_ocho="Este documento fue generado desde www.CaMaGaRe.com"."<p>";
		break;
		
		case "cxc_todos":
		$busca_encabezado_cxc=mysqli_query($con, "SELECT * FROM saldo_porcobrar_porpagar WHERE id_cli_pro = '".$id_documento."' and ruc_empresa='".$ruc_empresa."' and tipo='POR_COBRAR' group by id_documento");
		$linea_siete ='<table border><tr><th>No.</th><th>Fecha</th><th>Factura</th><th>Total</th><th>NC</th><th>Abonos</th><th>Retenciones</th><th>Saldo</th><th>D�as</th></tr>';
		$numero=1;
		while($detalle = mysqli_fetch_array($busca_encabezado_cxc)){
				$saldo=$detalle['total_factura']-$detalle['total_nc']-$detalle['total_ing']-$detalle['ing_tmp']-$detalle['total_ret'];
				$fecha_vencimiento = date_create($detalle['fecha_documento']);
				$diferencia_dias = date_diff($fecha_hoy, $fecha_vencimiento);
				$total_dias=$diferencia_dias->format('%a');
		$linea_siete .= '<tr><td>'.$numero.'</td>';
		$linea_siete .= '<td>'.date("d-m-Y", strtotime($detalle['fecha_documento'])).'</td>';
		$linea_siete .= '<td>'.$detalle['numero_documento'].'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_factura'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_nc'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_ing']+$detalle['ing_tmp'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($detalle['total_ret'],2,'.','').'</td>';
		$linea_siete .= '<td align="right">'.number_format($saldo,2,'.','').'</td>';
		$linea_siete .= '<td>'.$total_dias.'</td>';
		$numero = $numero+1;
		}
		$linea_siete .='</table><br>';
		$linea_tres="Esta es una notificación de cobro emitido por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco="Nro de Comprobante: DETALLE ADJUNTO<p>";
		$linea_ocho="Este documento fue generado desde www.CaMaGaRe.com"."<p>";
		break;
		case "solicitar_retencion":
		$linea_tres="Solicitamos nos emita la retención pendiente de la factura ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT)." emitida por ".strtoupper($nombre_comercial)."<p>";
		$linea_cinco=""."<p>";
		$linea_siete=""."<p>";
		$linea_ocho="Esta solicitud fue generada desde www.CaMaGaRe.com"."<p>";
		break;
		}
		
			$correo_host = "smtp.camagare.com";
			$correo_pass = "CmGr1980";
			$correo_port = "587";
			$correo_remitente = "facturacion@camagare.com";
		
		include ("../documentos_mail/phpmailer.php");
		include ("../documentos_mail/smtp.php");
		include ("../documentos_mail/exception.php");
		$email_user = $correo_remitente;
		$email_password = $correo_pass;
		$the_subject = utf8_decode($correo_asunto);
		$address_to = explode(', ', $mail_receptor);	
		$from_name = $nombre_sucursal;
		$phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
		
		// ---------- datos de la cuenta de Gmail -------------------------------
		$phpmailer->Username = $email_user;
		$phpmailer->Password = $email_password; 
		//-----------------------------------------------------------------------
		$phpmailer->SMTPDebug = 0;
		$phpmailer->SMTPSecure = false;//'ssl';
		$phpmailer->Host = $correo_host; // GMail
		$phpmailer->Port = $correo_port;
		$phpmailer->IsSMTP(); // use SMTP
		$phpmailer->SMTPAuth = true;
		$phpmailer->setFrom($phpmailer->Username,$from_name);
		for($i = 0; $i < count($address_to); $i++) {
		$phpmailer->AddAddress($address_to[$i]);
		}
		$phpmailer->Subject = $the_subject;
		if (isset($pdf_a_enviar)){
		$phpmailer->addAttachment($pdf_a_enviar);	
		}
		if (isset($xml_a_enviar)){
		$phpmailer->addAttachment($xml_a_enviar);	
		}	
		$phpmailer->Body .= "Estimado(a),";
		$phpmailer->Body .= "<p>".strtoupper($nombre_receptor)."</p>";
		$phpmailer->Body .= "<p>".utf8_decode($linea_tres);
		$phpmailer->Body .= "<p>"."Tipo de Comprobante: ".$tipo_comprobante."<p>";
		$phpmailer->Body .= "<p>".$linea_cinco;
		$phpmailer->Body .= "<p>".$valor_total>0?"Valor Total: ".$valor_total."<p>":"";
		$phpmailer->Body .= "<p>".utf8_decode($linea_siete);
		$phpmailer->Body .= "<p>".utf8_decode($linea_ocho);
		$phpmailer->Body .= "<p>"."Atentamente,"."<p>";
		$phpmailer->Body .= "<p>".strtoupper($razon_social)."</p>";
		$phpmailer->IsHTML(true);
//actualizar estado mail en cada encabezado_

	if ($phpmailer->Send()){
		if (isset($pdf_a_enviar) && isset($xml_a_enviar)){
		unlink($pdf_a_enviar);
		unlink($xml_a_enviar);
		$query_update=mysqli_query($con,"UPDATE $tabla SET estado_mail='ENVIADO' $where = '".$id_documento."'");
		}
				?>
				<div class="alert alert-success" role="alert">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong><?php echo utf8_encode("!Bien hecho!")?></strong> <?php echo utf8_decode("Documento enviado con éxito.")?> 
				</div>
				<?php
		}else{
				?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Error!</strong> No se puede enviar, intente otra vez. <?php echo $phpmailer->ErrorInfo ?>
			</div>
			<?php
		}
	
	}

}
?>




