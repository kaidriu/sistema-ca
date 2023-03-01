<?php
include("../conexiones/conectalogin.php");

$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if($action == "descarga_documentos" ){
	$archivo = base64_decode($_GET['archivo']);
	$nombre = $archivo;//basename($archivo);
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary");
	header("Content-disposition: attachment; filename=$nombre");
	readfile($archivo);
}


if((isset($_GET['id_documento'])) && (isset($_GET['tipo_documento'])) && (isset($_GET['tipo_archivo'])) ){
	if (empty($_GET['id_documento'])) {
           $errors[] = "Seleccione un documento electrónico para imprimir";
		}else if (empty($_GET['tipo_documento'])) {
           $errors[] = "Seleccione un documento electrónico para imprimir";
		}else if (empty($_GET['tipo_archivo'])) {
           $errors[] = "Seleccione un documento electrónico para imprimir";
		} else if (!empty($_GET['id_documento']) && (!empty($_GET['tipo_documento'])) && (!empty($_GET['tipo_archivo']))){
		
		//$id_documento=$_GET['id_documento'];
		$id_documento=base64_decode($_GET['id_documento']);
		$tipo_documento=$_GET['tipo_documento'];
		$extension_archivo=".".$_GET['tipo_archivo'];

		$imprime_documentos = new imprime_documentos();
		$archivo_a_imprimir = $imprime_documentos->descarga_documento($id_documento, $tipo_documento, $extension_archivo);

			header("Content-disposition: attachment; filename=$archivo_a_imprimir");
			header("Content-type: application/$extension_archivo");
			readfile($archivo_a_imprimir);
			unlink($archivo_a_imprimir);
		
	}
		
}


//para imprimir varios pdf y xml
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
		}
		/*
		else{
			echo "<script>
			$.notify('No se pudo generar el documento, intentelo de nuevo','error');
			</script>";	
	}	
	*/


//clase para descargar archivos
class imprime_documentos{
	
	public function descarga_documento($id_documento, $tipo_documento, $extension_archivo){
		$con = conenta_login();
		session_start();
		//para establecer la direccion de donde esta trabajando el sistema sea local o web
		$servidor="internet";//local o internet
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$tabla="encabezado_".$tipo_documento;
		if ($tipo_documento=='liquidacion'){
		$where="WHERE id_encabezado_liq";
		}else{
		$where="WHERE id_encabezado_".$tipo_documento;
		}
		//consulta datos de los encabezados
		$busca_datos_encabezados = mysqli_query($con, "SELECT * FROM $tabla $where = '".$id_documento."' ");
		$datos_encabezados = mysqli_fetch_array($busca_datos_encabezados);
		
		//para sacar las consultas de variables dependiendo de cada tipo de documento consulta en encabezado_
		switch ($tipo_documento) {
		case "factura":
				$serie =$datos_encabezados['serie_factura'];
				$secuencial = $datos_encabezados['secuencial_factura'];
				$id_cliente_proveedor = $datos_encabezados['id_cliente'];
				$ruc_cliente_proveedor = $this->clientes_proveedores($con, 'clientes', 'id', $id_cliente_proveedor)['ruc'];
				$documento_a_imprimir = $this->copia_documento_tmp($servidor, 'facturas_autorizadas', $ruc_empresa, $ruc_cliente_proveedor, 'FAC', $serie, $secuencial, $extension_archivo);
				return $documento_a_imprimir;				
			break;
		case "retencion":
				$serie =$datos_encabezados['serie_retencion'];
				$secuencial =$datos_encabezados['secuencial_retencion'];
				$id_cliente_proveedor =$datos_encabezados['id_proveedor'];
				$ruc_cliente_proveedor = $this->clientes_proveedores($con, 'proveedores', 'id_proveedor', $id_cliente_proveedor)['ruc_proveedor'];
				$documento_a_imprimir = $this->copia_documento_tmp($servidor, 'retenciones_autorizadas', $ruc_empresa, $ruc_cliente_proveedor, 'CR', $serie, $secuencial, $extension_archivo);
				return $documento_a_imprimir;
			break;
		case "nc":
				$serie =$datos_encabezados['serie_nc'];
				$secuencial =$datos_encabezados['secuencial_nc'];
				$id_cliente_proveedor =$datos_encabezados['id_cliente'];
				$ruc_cliente_proveedor = $this->clientes_proveedores($con, 'clientes', 'id', $id_cliente_proveedor)['ruc'];
				$documento_a_imprimir = $this->copia_documento_tmp($servidor, 'nc_autorizadas', $ruc_empresa, $ruc_cliente_proveedor, 'NC', $serie, $secuencial, $extension_archivo);
				return $documento_a_imprimir;
			break;
		case "gr":
				$serie =$datos_encabezados['serie_gr'];
				$secuencial =$datos_encabezados['secuencial_gr'];
				//$id_cliente_proveedor =$datos_encabezados['id_cliente'];
				$id_cliente_proveedor =$datos_encabezados['id_transportista'];
				$ruc_cliente_proveedor = $this->clientes_proveedores($con, 'clientes', 'id', $id_cliente_proveedor)['ruc'];
				$documento_a_imprimir = $this->copia_documento_tmp($servidor, 'guias_autorizadas', $ruc_empresa, $ruc_cliente_proveedor, 'GR', $serie, $secuencial, $extension_archivo);
				return $documento_a_imprimir;
			break;
		case "liquidacion":
				$serie =$datos_encabezados['serie_liquidacion'];
				$secuencial =$datos_encabezados['secuencial_liquidacion'];
				$id_cliente_proveedor =$datos_encabezados['id_proveedor'];
				$ruc_cliente_proveedor = $this->clientes_proveedores($con, 'proveedores', 'id_proveedor', $id_cliente_proveedor)['ruc_proveedor'];
				$documento_a_imprimir = $this->copia_documento_tmp($servidor, 'liquidaciones_autorizadas', $ruc_empresa, $ruc_cliente_proveedor, 'LIQ', $serie, $secuencial, $extension_archivo);
				return $documento_a_imprimir;
			break;
		case "proforma":
				$serie =$datos_encabezados['serie_proforma'];
				$secuencial =$datos_encabezados['secuencial_proforma'];
				$id_cliente_proveedor =$datos_encabezados['id_cliente'];
				$ruc_cliente_proveedor = $this->clientes_proveedores($con, 'clientes', 'id', $id_cliente_proveedor)['ruc'];
				$documento_a_imprimir = $this->copia_documento_tmp($servidor, 'proformas_autorizadas', $ruc_empresa, $ruc_cliente_proveedor, 'PROFORMA-', $serie, $secuencial, $extension_archivo);
				return $documento_a_imprimir;
			break;
			}
	}
	
	
	public function clientes_proveedores($con, $tabla, $id_tabla, $id_registro){
	//para buscar datos del cliente
				$busca_datos = mysqli_query($con, "SELECT * FROM $tabla WHERE $id_tabla = '".$id_registro."' ");
				$row_datos = mysqli_fetch_array($busca_datos);
				return $row_datos;
	}



	public function copia_documento_tmp($servidor, $nombre_carpeta, $ruc_empresa, $ruc_cliente_proveedor, $abreviatura_documento, $serie, $secuencial, $extension_archivo){
		if ($servidor=="local"){
				$direccion_documento="C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\";//local	
				$nombre_documento = $direccion_documento.$nombre_carpeta."/".$ruc_empresa."/".$ruc_cliente_proveedor."/".$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo; 
				copy($nombre_documento,$ruc_cliente_proveedor.$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
				$archivo_copiado = $ruc_cliente_proveedor.$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;		
				return $archivo_copiado;
			}else{
				//$link_descarga="http://64.225.69.65:8000/".$nombre_carpeta."/".$ruc_empresa."/".$ruc_cliente_proveedor."/".$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
				
				$ftp_server = "64.225.69.65";
				$ftp_user_name = "char";
				$ftp_user_pass = "CmGr1980";
				$conn_id = ftp_connect($ftp_server);
				if (@ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)) {
				ftp_pasv($conn_id, true);
				$server_file="/ftp_documentos/".$nombre_carpeta."/".$ruc_empresa."/".$ruc_cliente_proveedor."/".$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
				$local_file="../docs_temp/".$ruc_empresa.$ruc_cliente_proveedor.$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;
				if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
						copy($local_file,$ruc_cliente_proveedor.$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo);
						$archivo_copiado = $ruc_cliente_proveedor.$abreviatura_documento.$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT).$extension_archivo;		
						if (file_exists($archivo_copiado)){
						unlink($local_file);
						return $archivo_copiado;
						}else{
							return 'No encontrado.pdf';			
						}
					}else{
						return 'No encontrado.pdf';	
					}	
	
				}else{	
					return "No hay conexión con el servidor";
				} 
	
				ftp_close($conn_id);
			}	
		}

}
?>




