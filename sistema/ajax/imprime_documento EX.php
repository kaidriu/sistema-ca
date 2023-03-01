<?php
include("../conexiones/conectalogin.php");

if((isset($_GET['id_documento'])) && (isset($_GET['tipo_documento'])) && (isset($_GET['tipo_archivo'])) ){
	if (empty(base64_decode($_GET['id_documento']))) {
           $errors[] = "Seleccione un documento electrónico para imprimir";
		}else if (empty($_GET['tipo_documento'])) {
           $errors[] = "Seleccione un documento electrónico para imprimir";
		}else if (empty($_GET['tipo_archivo'])) {
           $errors[] = "Seleccione un documento electrónico para imprimir";
		} else if (!empty(base64_decode($_GET['id_documento'])) && (!empty($_GET['tipo_documento'])) && (!empty($_GET['tipo_archivo']))){
		
		$id_documento=base64_decode($_GET['id_documento']);
		$tipo_documento=$_GET['tipo_documento'];
		$extension_archivo=".".$_GET['tipo_archivo'];
		
			$imprime_documentos = new imprime_documentos();
			$archivo_a_imprimir = $imprime_documentos->descarga_documento($id_documento, $tipo_documento, $extension_archivo);
		
		header("Content-disposition: attachment; filename=$archivo_a_imprimir");
		header("Content-type: application/$extension_archivo");
		readfile($archivo_a_imprimir);
		unlink($archivo_a_imprimir);
		}else{
			echo "<script>
			$.notify('No se pudo imprimir el documento, intentelo de nuevo','error');
			</script>";	
		}	
}


//para imprimir varios pdf
if (isset($_GET['descarga']) && ($_GET['descarga']=="varios" )) {
		$documentos =  explode(',', $_GET["documentos"]);
		$tipo_documento=$_GET['tipo_documento'];
		$imprime_documentos = new imprime_documentos();

			foreach ($documentos as $id_documento){	
				$pdf_a_imprimir[] = $imprime_documentos->descarga_documento($id_documento, $tipo_documento, '.pdf');
			}
			foreach ($documentos as $id_documento){	
				$xml_a_imprimir[] = $imprime_documentos->descarga_documento($id_documento, $tipo_documento, '.xml');
			}
			
				$zip_nombre = $tipo_documento.'_CaMaGaRe.zip';
				$zip = new ZipArchive();
				$zip->open($zip_nombre, ZipArchive::CREATE);

				foreach ($pdf_a_imprimir as $pdf){
					$zip->addFile($pdf, str_replace('', '', $pdf));
				}
				foreach ($xml_a_imprimir as $xml){
					$zip->addFile($xml, str_replace('', '', $xml));
				}

				$zip->close();

				// Forzamos la descarga
				header('Content-Type: application/zip');
				header('Content-disposition: attachment; filename='.$zip_nombre);
				header('Content-Length: ' . filesize($zip_nombre));
				readfile($zip_nombre);

				// Eliminamos el archivo que se creo en nuestro host
				unlink($zip_nombre);
				
				foreach ($pdf_a_imprimir as $eliminar_pdf){
					unlink($eliminar_pdf);
				}
				foreach ($xml_a_imprimir as $eliminar_xml){
					unlink($eliminar_xml);
				}
		}else{
			echo "<script>
			$.notify('No se pudo generar el documento, intentelo de nuevo','error');
			</script>";	
	}	


//clase para descargar archivos
class imprime_documentos{
	
	public function descarga_documento($id_documento, $tipo_documento, $extension_archivo){
		$con = conenta_login();
		$tabla="encabezado_".$tipo_documento;
		if ($tipo_documento=='liquidacion'){
		$where="WHERE id_encabezado_liq";
		}else{
		$where="WHERE id_encabezado_".$tipo_documento;
		}
		//consulta datos de los encabezados
		$busca_datos_encabezados = "SELECT * FROM $tabla $where = '".$id_documento."' ";
		$resultado_encabezado = $con->query($busca_datos_encabezados);
		$datos_encabezados = mysqli_fetch_array($resultado_encabezado);
		
		//para sacar las consultas de variables dependiendo de cada tipo de documento consulta en encabezado_
		switch ($tipo_documento) {
		case "factura":
				$serie =$datos_encabezados['serie_factura'];
				$secuencial =$datos_encabezados['secuencial_factura'];
				$id_cliente =$datos_encabezados['id_cliente'];
			break;
		case "retencion":
				$serie =$datos_encabezados['serie_retencion'];
				$secuencial =$datos_encabezados['secuencial_retencion'];
				$id_proveedor =$datos_encabezados['id_proveedor'];
			break;
		case "nc":
				$serie =$datos_encabezados['serie_nc'];
				$secuencial =$datos_encabezados['secuencial_nc'];
				$id_cliente =$datos_encabezados['id_cliente'];
			break;
		case "gr":
				$serie =$datos_encabezados['serie_gr'];
				$secuencial =$datos_encabezados['secuencial_gr'];
				$id_cliente =$datos_encabezados['id_cliente'];
				$id_transportista =$datos_encabezados['id_transportista'];
			break;
		case "liquidacion":
				$serie =$datos_encabezados['serie_liquidacion'];
				$secuencial =$datos_encabezados['secuencial_liquidacion'];
				$id_proveedor =$datos_encabezados['id_proveedor'];
			break;
			}
				
			//para buscar datos del cliente
				$busca_datos_cliente = "SELECT * FROM clientes WHERE id = '".$id_cliente."' ";
				$resultado_cliente = $con->query($busca_datos_cliente);
				$datos_cliente = mysqli_fetch_array($resultado_cliente);
				$ruc_cliente =$datos_cliente['ruc'];

			//datos del proveedor
			if ($tipo_documento=="retencion" || $tipo_documento=="liquidacion"){
				$busca_datos_proveedor = "SELECT * FROM proveedores WHERE id_proveedor= '".$id_proveedor."' ";
				$resultado_proveedor = $con->query($busca_datos_proveedor);
				$datos_proveedor = mysqli_fetch_array($resultado_proveedor);
				$ruc_proveedor =$datos_proveedor['ruc_proveedor'];
				$nombre_proveedor =$datos_proveedor['proveedor'];
			}
			
			//datos del transportista
			if ($tipo_documento=="gr"){
				$busca_datos_transportista = "SELECT * FROM clientes WHERE id = '".$id_transportista."' ";
				$resultado_transportista = $con->query($busca_datos_transportista);
				$datos_transportista = mysqli_fetch_array($resultado_transportista);
				$ruc_transportista =$datos_transportista['ruc'];
			}
			
			//$direccion_servidor="C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\";//local
			$direccion_servidor="http://www.facturacionelectronicaec.com/sistema/";//servidor
	//para ver las direcciones de donde estan los documentos electronicos
		switch ($tipo_documento) {
		case "factura":
		$dir_documento = $direccion_servidor."facturas_autorizadas/".$ruc_cliente."/FAC".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
		copy($dir_documento, $ruc_cliente."-FAC".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
		$archivo_a_imprimir = $ruc_cliente."-FAC".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;	
			break;
		case "retencion":
		$dir_documento = $direccion_servidor."retenciones_autorizadas/".$ruc_proveedor."/CR".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
		copy($dir_documento,$ruc_proveedor."-CR".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
		$archivo_a_imprimir = $ruc_proveedor."-CR".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
			break;
		case "nc":
		$dir_documento = $direccion_servidor."nc_autorizadas/".$ruc_cliente."/NC".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
		copy($dir_documento,$ruc_cliente."-NC".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
		$archivo_a_imprimir = $ruc_cliente."-NC".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
			break;
		case "gr":
		$dir_documento = $direccion_servidor."guias_autorizadas/".$ruc_transportista."/GR".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
		copy($dir_documento,$ruc_transportista."-GR".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
		$archivo_a_imprimir = $ruc_transportista."-GR".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
			break;
		case "liquidacion":
		$dir_documento = $direccion_servidor."liquidaciones_autorizadas/".$ruc_proveedor."/LIQ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
		copy($dir_documento,$ruc_proveedor."-LIQ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
		$archivo_a_imprimir = $ruc_proveedor."-LIQ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
			break;
			}
		
		return $archivo_a_imprimir;

	}
}

?>




