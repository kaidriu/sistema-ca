<?php 
if (!isset($_SESSION['ruc_empresa'])){
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
}
require "../facturacion_electronica/ProcesarComprobanteElectronico.php";
//para todos los documentos	
if (isset($_POST['id_documento_sri'])&& isset($_POST['tipo_documento_sri'])){
$id_documento= $_POST['id_documento_sri'];
$tipo_documento= $_POST['tipo_documento_sri'];
$modo_envio= $_POST['modo_envio'];//online-offline
$envia_documento= new enviarComprobantesSri();

switch ($tipo_documento) {
				case "factura":
				echo $envia_documento->EnviarFactura($id_documento, $modo_envio);
					break;
				case "retencion":
					echo $envia_documento->EnviarRetencion($id_documento, $modo_envio);
					break;
				case "nc":
					echo $envia_documento->EnviarNc($id_documento, $modo_envio);
					break;
				case "gr":
					echo $envia_documento->EnviarGr($id_documento, $modo_envio);
					break;
				case "nd":
					echo $envia_documento->EnviarNd($id_documento, $modo_envio);
					break;
				case "liquidacion":
					echo $envia_documento->EnviarLc($id_documento, $modo_envio);
					break;
				case "proforma":
					echo $envia_documento->EnviarProforma($id_documento, $modo_envio);
					break;
			}
}


/**
* Envia comprobantes a los servidores del SRI
*/
class enviarComprobantesSri{

	private $db;
	public $ruc_empresa;
	
	public function __construct(){
		include("../core/db.php");
		$this->db = new db();
		if ( !$this->db->connect() ) {
			$this->db = null;
		}
	}
	
	public function config_app($documento, $serie_sucursal){
	$servidor="internet";//aqui se cambia local o internet
	//$servidor="local";//aqui se cambia local o internet
	
	$configuracion_app= array();
		if ($servidor=="local"){
		$dir_firma="C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\firma_digital\\".$this->confi_empresa()['archivo_firma'];
		$dir_logo = "C:\\xampp\\htdocs\\sistema\\logos_empresas\\".$this->info_sucursal($serie_sucursal)['logo_sucursal'];
			switch ($documento) {
				case "factura":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\facturas_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
				case "retencion":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\retenciones_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
				case "nc":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\nc_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
				case "gr":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\guias_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
				case "nd":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\nd_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
				case "liquidacion":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\liquidaciones_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
				case "proforma":
					$dir_documento = "C:\\xampp\\htdocs\\sistema\\facturacion_electronica\\proformas_autorizadas\\".$this->info_sucursal($serie_sucursal)['ruc_empresa'];
					if (!file_exists($dir_documento)) {
						mkdir($dir_documento, 0777, true);
					}
					break;
			}
			$configuracion_app= array('dir_firma' => $dir_firma, 'dir_logo' => $dir_logo, 'dir_documento' => $dir_documento);
		}
		
		if ($servidor=="internet"){
			$dir_firma="/home/char/ftp_documentos/firma_digital/".$this->confi_empresa()['archivo_firma'];
			$dir_logo = "/home/char/ftp_documentos/logos_empresa/".$this->info_sucursal($serie_sucursal)['logo_sucursal'];
			switch ($documento) {
				case "factura":
					$dir_documento = "/home/char/ftp_documentos/facturas_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
				case "retencion":
					$dir_documento = "/home/char/ftp_documentos/retenciones_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
				case "nc":
					$dir_documento = "/home/char/ftp_documentos/nc_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
				case "gr":
					$dir_documento = "/home/char/ftp_documentos/guias_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
				case "nd":
					$dir_documento = "/home/char/ftp_documentos/nd_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
				case "liquidacion":
					$dir_documento = "/home/char/ftp_documentos/liquidaciones_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
				case "proforma":
					$dir_documento = "/home/char/ftp_documentos/proformas_autorizadas/".$this->info_sucursal($serie_sucursal)['ruc_empresa']."/";
					break;
			}
			$configuracion_app= array('dir_firma' => $dir_firma,'dir_logo' => $dir_logo,'dir_documento' => $dir_documento);
		}
			$configApp = new \configAplicacion();
			$configApp->dirFirma = $configuracion_app['dir_firma'];
			$configApp->passFirma = $this->confi_empresa()['pass_firma'];
			$configApp->dirAutorizados =  $configuracion_app['dir_documento'];
			$configApp->dirLogo = $configuracion_app['dir_logo'];
			return $configApp;
	}
		
	public function confi_mail($modo_envio){
	//consultar informacion de sobre la configuracion del mail
	$configCorreo = new \configCorreo();
	$correo_asunto=$this->confi_empresa()['correo_asunto'];
	$correo_host=$this->confi_empresa()['correo_host'];
	$correo_pass=$this->confi_empresa()['correo_pass'];
	$correo_port=$this->confi_empresa()['correo_port'];
	$correo_remitente=$this->confi_empresa()['correo_remitente'];
	$correo_empresa=$this->info_empresa()['mail'];

		if ($modo_envio=='offline'){
			$configCorreo->correoAsunto = "";
			$configCorreo->correoHost = "";
			$configCorreo->correoPass = "";
			$configCorreo->correoPort = "";
			$configCorreo->correoRemitente = "";
			$configCorreo->correoEmpresa = $correo_empresa;
			$configCorreo->sslHabilitado = FALSE;
		}else{

			if ($correo_asunto != "" && $correo_host != "" && $correo_pass != "" && $correo_port != "" && $correo_remitente != ""){
			$configCorreo->correoAsunto = $correo_asunto;
			$configCorreo->correoHost = $correo_host;
			$configCorreo->correoPass = $correo_pass;
			$configCorreo->correoPort = $correo_port;
			$configCorreo->correoRemitente = $correo_remitente;
			$configCorreo->correoEmpresa = $correo_empresa;
			$configCorreo->sslHabilitado = FALSE;
			}else{
			$configCorreo->correoAsunto = $correo_asunto;
			$configCorreo->correoHost = "smtp.camagare.com";
			$configCorreo->correoPass = "CmGr1980";
			$configCorreo->correoPort = "587";
			$configCorreo->correoRemitente = "facturacion@camagare.com";
			$configCorreo->correoEmpresa = $correo_empresa;
			$configCorreo->sslHabilitado = FALSE;
			}
		return $configCorreo;
		}
	}
	
	
	public function confi_empresa(){
	//consultar informacion de la empresa
			$ruc_empresa = $_SESSION['ruc_empresa'];	
			$busca_confi_empresa = $this->db->select("SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ");
			return $busca_confi_empresa[0];
	}
	
	public function info_empresa(){
	//consultar informacion de la empresa	
			$ruc_empresa = $_SESSION['ruc_empresa'];	
			$busca_empresa = $this->db->select("SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."' ");
			return $busca_empresa[0];
	}
	
	public function info_sucursal($serie_sucursal){
			$ruc_empresa = $_SESSION['ruc_empresa'];
		//traer la informacion de la sucursal
			$busca_info_sucursal = $this->db->select("SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' and serie = '".$serie_sucursal."' ");
          	return $busca_info_sucursal[0];
	}
	
	public function info_documento($id_documento, $tipo){
			switch ($tipo) {
				case "factura":
					$tabla = "encabezado_factura";
					$id_encabezado_documento = "id_encabezado_factura";
					break;
				case "retencion":
					$tabla = "encabezado_retencion";
					$id_encabezado_documento = "id_encabezado_retencion";
					break;
				case "nc":
					$tabla = "encabezado_nc";
					$id_encabezado_documento = "id_encabezado_nc";
					break;
				case "gr":
					$tabla = "encabezado_gr";
					$id_encabezado_documento = "id_encabezado_gr";
					break;
				case "nd":
					$tabla = "encabezado_nd";
					$id_encabezado_documento = "id_encabezado_nd";
					break;
				case "liquidacion":
					$tabla = "encabezado_liquidacion";
					$id_encabezado_documento = "id_encabezado_liq";
					break;
				case "proforma":
					$tabla = "encabezado_proforma";
					$id_encabezado_documento = "id_encabezado_proforma";
					break;
			}
			$result_info_documento = $this->db->select("SELECT * FROM $tabla WHERE $id_encabezado_documento = '".$id_documento."'");
			return $result_info_documento[0];
	}
	
	public function info_cliente($id_cliente){
		//traer informacion de la factura y cliente
			$busca_info_cliente = $this->db->select("SELECT * FROM clientes WHERE id = '".$id_cliente."' ");
			return $busca_info_cliente[0];
	}
		
	public function info_proveedor($id_proveedor){
			$busca_info_proveedor = $this->db->select("SELECT * FROM proveedores WHERE id_proveedor = '".$id_proveedor."' ");
			return $busca_info_proveedor[0];
	}
	
	public function confi_facturacion($serie_sucursal){
		//traer informacion de la configuracion de la facturacion
		$ruc_empresa = $_SESSION['ruc_empresa'];
			$confi_facturacion = $this->db->select("SELECT * FROM configuracion_facturacion where ruc_empresa ='".$ruc_empresa."' and serie_sucursal ='".$serie_sucursal."' ");
			if ($confi_facturacion){
			return $confi_facturacion[0];
			}
	}

//para enviar proformas
	public function EnviarProforma($id_proforma, $modo_envio){
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$estado_proforma = $this->info_documento($id_proforma,"proforma")['estado_proforma'];
		$serie_sucursal=$this->info_documento($id_proforma,"proforma")['serie_proforma'];
		$codigo_unico=$this->info_documento($id_proforma,"proforma")['codigo_unico'];
		$numero_proforma=$this->info_documento($id_proforma,"proforma")['secuencial_proforma'];
		$id_cliente=$this->info_documento($id_proforma,"proforma")['id_cliente'];
		$decimal_cantidad=($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'];
		$decimal_precio=$this->info_sucursal($serie_sucursal)['decimal_doc'];
		$documento='proforma';

		$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();

		$proforma = new proforma();
		$proforma->tipoEmision ="1";
		$proforma->numero = $serie_sucursal."-".str_pad($numero_proforma,9,"000000000",STR_PAD_LEFT);
		$proforma->dirProformas = $this->config_app($documento, $serie_sucursal)->dirAutorizados;
		$proforma->dirLogo =$this->config_app($documento, $serie_sucursal)->dirLogo;
		$proforma->configCorreo = $this->confi_mail($modo_envio);
		$proforma->razonSocial = strtoupper($this->info_empresa()['nombre']); //[Razon Social]
		$proforma->nombreComercial = strtoupper($this->info_sucursal($serie_sucursal)['nombre_sucursal']);  //[Nombre Comercial, si hay]*
		$proforma->ruc = substr($ruc_empresa,0,12).'1'; //[Ruc]
		$proforma->fechaEmision = date("d/m/Y", strtotime($this->info_documento($id_proforma,"proforma")['fecha_proforma'])); //[Fecha (dd/mm/yyyy)]
		$proforma->dirMatriz = strtoupper($this->info_empresa()['direccion']); //[Direccion de la Matriz ->SRI]
		$proforma->dirEstablecimiento = strtoupper($this->info_sucursal($serie_sucursal)['direccion_sucursal']); //[Direccion de Establecimiento ->SRI]
		$proforma->razonSocialComprador = strtoupper($this->info_cliente($id_cliente)['nombre']); //Razon social o nombres y apellidos comprador
		$proforma->identificacionComprador = $this->info_cliente($id_cliente)['ruc']; // Identificacion Comprador
		$proforma->direccionComprador = $this->info_cliente($id_cliente)['direccion']; // Identificacion Comprador
		
		//suma subtotales
		$subtotales_generales=$this->db->select("select sum(round(subtotal-descuento,2)) as subtotal_general, sum(round(descuento,2)) as descuento_general from cuerpo_proforma where ruc_empresa = '".$ruc_empresa."' and codigo_unico ='".$codigo_unico."' ");
		$subtotal_general=$subtotales_generales[0]['subtotal_general'];//Sumador subtotal general
		$total_descuento=$subtotales_generales[0]['descuento_general'];//Sumador total descuento

		$subtotales_doce=$this->db->select("select sum(round(subtotal-descuento,2)) as subtotal_doce from cuerpo_proforma where ruc_empresa = '".$ruc_empresa."' and codigo_unico ='".$codigo_unico."' and tarifa_iva='2'");
		$subtotal_doce=$subtotales_doce[0]['subtotal_doce'];//Sumador subtotal general
		
		$proforma->subTotal0 = number_format($subtotal_general-$subtotal_doce,2,'.','');
		$proforma->subTotal12 = number_format($subtotal_doce,2,'.','');
		$proforma->subTotalSinImpuesto = number_format($subtotal_general,2,'.','');
		$proforma->iva = number_format($subtotal_doce*0.12,2,'.','');
		$proforma->totalDescuento = number_format($total_descuento,2,'.','');
		$proforma->importeTotal = number_format($this->info_documento($id_proforma,"proforma")['total_proforma'],2,'.','');

		$detalle_proforma=$this->db->select("select * from cuerpo_proforma WHERE ruc_empresa = '".$ruc_empresa."' and codigo_unico ='".$codigo_unico."' ");
		$detalle_proforma_item = array();
		
		foreach ($detalle_proforma as $detalle_final) {
			$detalleProforma = new detalleProforma();
			$detalleProforma->codigo = $detalle_final['codigo_producto']; // Codigo del Producto
			$detalleProforma->descripcion = ucwords($detalle_final['nombre_producto']); // Nombre del producto
			$detalleProforma->cantidad = number_format($detalle_final['cantidad'],($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'],'.',''); // Cantidad
			$detalleProforma->precioUnitario = number_format($detalle_final['valor_unitario'],$this->info_sucursal($serie_sucursal)['decimal_doc'],'.',''); // Valor unitario
			$detalleProforma->descuento = number_format($detalle_final['descuento'],2,'.',''); // Descuento u
			$detalleProforma->precioTotalSinImpuesto = number_format(($detalleProforma->cantidad * $detalleProforma->precioUnitario) - $detalle_final['descuento'],2,'.',''); // Valor sin impuesto  (cantidad * precioUnitario) - descuento
			$detalle_proforma_item[] = $detalleProforma;
			}

	$proforma->detalles = $detalle_proforma_item;

	$eliminar_adicional_proforma=$this->db->exec("DELETE from detalle_adicional_proforma WHERE ruc_empresa = '".$ruc_empresa."' and codigo_unico ='".$codigo_unico."' and adicional_concepto='Emisor'");
	//$agregar_adicional_proforma=$this->db->exec("INSERT INTO detalle_adicional_proforma VALUES (null, '".$ruc_empresa."', '".$codigo_unico."', '".$numero_proforma."','Emisor','".$this->info_empresa()['mail']."') ");
	
	$detalle_adicional_proforma=$this->db->select("select * from detalle_adicional_proforma WHERE ruc_empresa = '".$ruc_empresa."' and codigo_unico ='".$codigo_unico."' ");
			$camposAdicionales = array();
			foreach ($detalle_adicional_proforma as $detalle_adicional) {
			$nombre_adicional=$detalle_adicional['adicional_concepto'];
			$descripcion_adicional=$detalle_adicional['adicional_descripcion'];
				if ($nombre_adicional !=null && $descripcion_adicional !=null){
				$campoAdicional = new campoAdicional();
				$campoAdicional->nombre = $nombre_adicional;
				$campoAdicional->valor = $descripcion_adicional;
				$camposAdicionales[] = $campoAdicional;
				}
			}

	$proforma->infoAdicional = $camposAdicionales;
	$procesar = $this->procesar_proforma($proforma, 'proforma', $serie_sucursal, $numero_proforma);
	return $procesar;
	}
	//hasta aqui la proforma
	
//enviar liquidaciones al sri
	public function EnviarLc($id_liquidacion, $modo_envio){
		$ruc_empresa = $_SESSION['ruc_empresa'];
		//solo si esta pendiente se puede enviar al sri	
		$estado_liquidacion_sri = $this->info_documento($id_liquidacion,"liquidacion")['estado_sri'];
		$serie_sucursal=$this->info_documento($id_liquidacion,"liquidacion")['serie_liquidacion'];
		$serie_liquidacion=$this->info_documento($id_liquidacion,"liquidacion")['serie_liquidacion'];
		$numero_liquidacion=$this->info_documento($id_liquidacion,"liquidacion")['secuencial_liquidacion'];
		$id_proveedor=$this->info_documento($id_liquidacion,"liquidacion")['id_proveedor'];
		$decimal_cantidad=($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'];
		$decimal_precio=$this->info_sucursal($serie_sucursal)['decimal_doc'];
		$documento='liquidacion';
		
		//if($estado_liquidacion_sri !="PENDIENTE"){
		//	return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se puede enviar al SRI la liquidación, ".$serie_liquidacion."-".$numero_liquidacion.", su estado es ".$estado_liquidacion_sri."</div>"."<br>";
		//}else{				
		$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();

			$liquidacionCompra = new liquidacionCompra();
			$liquidacionCompra->configAplicacion = $this->config_app($documento, $serie_sucursal);
			$liquidacionCompra->configCorreo = $this->confi_mail($modo_envio);
			$liquidacionCompra->ambiente = $this->confi_empresa()['tipo_ambiente']; //[1,Prueba][2,Produccion] 
			$liquidacionCompra->tipoEmision = $this->confi_empresa()['tipo_emision']; //[1,Emision Normal][2,Emision Por Indisponibilidad del sistema
			$liquidacionCompra->razonSocial = strtoupper($this->info_empresa()['nombre']); //[Razon Social]
			$liquidacionCompra->nombreComercial = strtoupper($this->info_sucursal($serie_sucursal)['nombre_sucursal']);  //[Nombre Comercial, si hay]*
			$liquidacionCompra->ruc = substr($ruc_empresa,0,12).'1'; //[Ruc]
			$liquidacionCompra->codDoc = '03'; //[03 liq, 01, Factura] [04, Nota Credito] [05, Nota Debito] [06, Guia Remision] [07, Retencion]
			$liquidacionCompra->establecimiento = substr($serie_sucursal,0,3); //[pto de emision ] **
			$liquidacionCompra->fechaEmision = date("d/m/Y", strtotime($this->info_documento($id_liquidacion, $documento)['fecha_liquidacion'])); //[Fecha (dd/mm/yyyy)]
			$liquidacionCompra->ptoEmision = substr($serie_sucursal,4,3); // [Numero Establecimiento SRI]
			$liquidacionCompra->secuencial = str_pad($numero_liquidacion,9,"000000000",STR_PAD_LEFT); // [Secuencia desde 1 (9)]
			$liquidacionCompra->dirMatriz = strtoupper($this->info_empresa()['direccion']); //[Direccion de la Matriz ->SRI]
			$liquidacionCompra->dirEstablecimiento = strtoupper($this->info_sucursal($serie_sucursal)['direccion_sucursal']); //[Direccion de Establecimiento ->SRI]
			$liquidacionCompra->contribuyenteEspecial =$this->confi_empresa()['resol_cont'];
			$liquidacionCompra->regimenRIMPE = $this->confi_empresa()['regimen_rimpe']!="SI"?"":"SI";
			$liquidacionCompra->agenteRetencion = $this->confi_empresa()['agente_ret']>0?$this->confi_empresa()['agente_ret']:"";
			$ruc_cliente_proveedor=$ruc_empresa;//ruc donde va a guardarse la liquidacion emitida
			
			$tipo_empresa =$this->info_empresa()['tipo'];	
						switch ($tipo_empresa) {
							case "01":
								$lleva_contabilidad = "NO";
								break;
							case "02" or "03" or "04" or "05":
								$lleva_contabilidad = "SI";
								break;
						};

			$liquidacionCompra->obligadoContabilidad = $lleva_contabilidad; // [SI]	
			$liquidacionCompra->tipoIdentificacionProveedor = $this->info_proveedor($id_proveedor)['tipo_id_proveedor']; //Info proveedor [04, RUC][05,Cedula][06, Pasaporte][07, Consumidor final][08, Exterior][09, Placa]
			$liquidacionCompra->razonSocialProveedor = strtoupper($this->info_proveedor($id_proveedor)['razon_social']); //Razon social o nombres y apellidos proveedor
			$liquidacionCompra->identificacionProveedor = $this->info_proveedor($id_proveedor)['ruc_proveedor']; // Identificacion proveedor
			$liquidacionCompra->direccionProveedor = $this->info_proveedor($id_proveedor)['dir_proveedor']; // direccion proveedor
			//suma subtotales
				$subtotales_generales=$this->db->select("select sum(round(subtotal-descuento,2)) as subtotal_general, sum(round(descuento,2)) as descuento_general from cuerpo_liquidacion where ruc_empresa = '".$ruc_empresa."' and serie_liquidacion ='".$serie_liquidacion."' and secuencial_liquidacion='".$numero_liquidacion."'");
				$subtotal_general=$subtotales_generales[0]['subtotal_general'];//Sumador subtotal general
				$total_descuento=$subtotales_generales[0]['descuento_general'];//Sumador total descuento
			
			$liquidacionCompra->totalSinImpuestos = number_format($subtotal_general,2,'.',''); // Total sin aplicar impuestos
			$liquidacionCompra->totalDescuento = number_format($total_descuento,2,'.',''); // Total Dtos

			//consulta de la tabla de tarifa iva
							$codigo_impuesto_en_totales = "2";//$totales_detalle_impuestos_ventas['codigo_impuesto'];	
							if ($codigo_impuesto_en_totales =="2"){
									$subtotales_liquidacion = array();
									$subtotales_tarifa_iva=$this->db->select("select sum(round((cl.subtotal - cl.descuento) * ti.porcentaje_iva /100,4)) as total_iva, ti.codigo as codigo_porcentaje, sum(round(cl.subtotal - cl.descuento,4)) as subtotal_liquidacion FROM cuerpo_liquidacion cl, tarifa_iva ti WHERE cl.ruc_empresa ='".$ruc_empresa."' and cl.serie_liquidacion='".$serie_liquidacion."' and cl.secuencial_liquidacion ='".$numero_liquidacion."' and ti.codigo = cl.tarifa_iva group by cl.tarifa_iva ");
								foreach ($subtotales_tarifa_iva as $sub_tarifa_iva){
			$totalImpuesto = new totalImpuesto();
			$totalImpuesto->codigo = "2";//[2, IVA][3,ICE][5, IRBPNR]						
			$totalImpuesto->codigoPorcentaje = $sub_tarifa_iva['codigo_porcentaje']; // IVA -> [0, 0%][2, 12%][6, No objeto de impuesto][7, Exento de IVA] ICE->[Tabla 19]
			$totalImpuesto->baseImponible = number_format($sub_tarifa_iva['subtotal_liquidacion'],2,'.',''); // Suma de los impuesto del mismo cod y % (0.00)										
			$totalImpuesto->valor =number_format($sub_tarifa_iva['total_iva'],2,'.','');; // Suma de los impuesto del mismo cod y % aplicado el % (0.00)
									$subtotales_liquidacion [] = $totalImpuesto;
								}
							}					
			$liquidacionCompra->totalConImpuesto = $subtotales_liquidacion; //Agrega el impuesto a la liquidacion NO TOCAR ESTA LINEA
			$liquidacionCompra->importeTotal = number_format($this->info_documento($id_liquidacion,"liquidacion")['total_liquidacion'],2,'.',''); // Total de Productos + impuestos
			$liquidacionCompra->moneda = $this->info_sucursal($serie_sucursal)['moneda_sucursal']; //DOLAR

			//desde aqui detalle de la liquidacion y productos
					$detalle_liquidacion=$this->db->select("select * from cuerpo_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion ='".$serie_liquidacion."' and secuencial_liquidacion='".$numero_liquidacion."' ");
					$detalle_liquidacion_item = array();
					
					foreach ($detalle_liquidacion as $detalle_final) {
			$detalleLiquidacionCompra = new detalleLiquidacionCompra();
			$detalleLiquidacionCompra->codigoPrincipal = $detalle_final['codigo_producto']; // Codigo del Producto
			$detalleLiquidacionCompra->codigoAuxiliar = $detalle_final['codigo_producto']; // Opcional
			$detalleLiquidacionCompra->descripcion = ucwords($detalle_final['nombre_producto']); // Nombre del producto		
			$detalleLiquidacionCompra->cantidad = number_format($detalle_final['cantidad'],($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'],'.',''); // Cantidad
			$detalleLiquidacionCompra->precioUnitario = number_format($detalle_final['valor_unitario'],$this->info_sucursal($serie_sucursal)['decimal_doc'],'.',''); // Valor unitario
			$detalleLiquidacionCompra->descuento = number_format($detalle_final['descuento'],2,'.',''); // Descuento u
			$detalleLiquidacionCompra->precioTotalSinImpuesto = number_format(($detalleLiquidacionCompra->cantidad * $detalleLiquidacionCompra->precioUnitario) - $detalle_final['descuento'],2,'.','');// Valor sin impuesto

			$impuesto = new impuesto(); // Impuesto del detalle
			$impuesto->codigo = "2"; //del impuesto, iva, ice, bp	
			$impuesto->codigoPorcentaje =$detalle_final['tarifa_iva'];
					//para traer la tarifa de iva para el detalle de cada item de la liquidacion
					$tarifa_iva_del_detalle=$this->db->select("select * from tarifa_iva WHERE codigo='".$detalle_final['tarifa_iva']."' ");
					$tarifa_de_iva=$tarifa_iva_del_detalle[0]['porcentaje_iva'];

			$impuesto->tarifa =$tarifa_de_iva;
			$impuesto->baseImponible = number_format($detalleLiquidacionCompra->precioTotalSinImpuesto,2,'.',''); // subtotal o base
			$impuesto->valor = number_format((($detalleLiquidacionCompra->precioTotalSinImpuesto * $tarifa_de_iva) / 100),2,'.',''); // valor o sea el 12 por ciento de la base
			$detalleLiquidacionCompra->impuestos = $impuesto;
					$detalle_liquidacion_item [] = $detalleLiquidacionCompra;
							}

			$liquidacionCompra->detalles = $detalle_liquidacion_item;
			//hasta aqui detalle de la liquidacion

					// trae formas de pago
					$busca_fp_liquidacion=$this->db->select("SELECT * FROM formas_pago_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion ='".$serie_liquidacion."' and secuencial_liquidacion='".$numero_liquidacion."'");
			
			$detalle_pago= array();
					foreach ($busca_fp_liquidacion as $info_fp_factura) {
			$pago = new pagos();
					$codigo_fp =$info_fp_factura['id_forma_pago'];
					$valor_pago =$info_fp_factura['valor_pago'];
			$pago->formaPago = $codigo_fp;
			$pago->total =number_format($valor_pago,2,'.','');
			$pago->plazo = $this->info_proveedor($id_proveedor)['plazo'];
			$pago->unidadTiempo = "Días";
			$detalle_pago[]= $pago;
					}
			$liquidacionCompra->pagos = $detalle_pago;

			//desde aqui detalle de adicionales de la liquidacion
					$detalle_adicional_liquidacion=$this->db->select("select * from detalle_adicional_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion ='".$serie_liquidacion."' and secuencial_liquidacion='".$numero_liquidacion."' ");
					
			$camposAdicionales = array();
				foreach ($detalle_adicional_liquidacion as $detalle_adicional) {
				$nombre_adicional=$detalle_adicional['adicional_concepto'];
				$descripcion_adicional=$detalle_adicional['adicional_descripcion'];
					if ($nombre_adicional !=null && $descripcion_adicional !=null){
					$campoAdicional = new campoAdicional();
					$campoAdicional->nombre = $nombre_adicional;
					$campoAdicional->valor = $descripcion_adicional;
					$camposAdicionales[] = $campoAdicional;
					}
				}
			$liquidacionCompra->infoAdicional = $camposAdicionales;
			
			$procesar = $this->procesar_comprobante($liquidacionCompra, 'liquidacion', $modo_envio, $serie_liquidacion, $numero_liquidacion, $ruc_cliente_proveedor);
			return $procesar;
		//}
	}

//para enviar facturas
	public function EnviarFactura($id_factura, $modo_envio){
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$estado_factura_sri = $this->info_documento($id_factura,"factura")['estado_sri'];
		$serie_sucursal=$this->info_documento($id_factura,"factura")['serie_factura'];
		$serie_factura=$this->info_documento($id_factura,"factura")['serie_factura'];
		$numero_factura=$this->info_documento($id_factura,"factura")['secuencial_factura'];
		$id_cliente=$this->info_documento($id_factura,"factura")['id_cliente'];
		$decimal_cantidad=($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'];
		$decimal_precio=$this->info_sucursal($serie_sucursal)['decimal_doc'];
		$documento='factura';
		
		//if($estado_factura_sri !="PENDIENTE"){//PENDIENTE
		//	return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se puede enviar al SRI la factura, ".$serie_factura."-".$numero_factura.", su estado es ".$estado_factura_sri."</div>"."<br>";
		//}else{				
			$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();

			$factura = new factura();
			$factura->configAplicacion = $this->config_app($documento, $serie_sucursal);
			$factura->configCorreo = $this->confi_mail($modo_envio);
			$factura->ambiente = $this->confi_empresa()['tipo_ambiente']; //[1,Prueba][2,Produccion] 
			$factura->tipoEmision = $this->confi_empresa()['tipo_emision']; //[1,Emision Normal][2,Emision Por Indisponibilidad del sistema
			$factura->razonSocial = strtoupper($this->info_empresa()['nombre']); //[Razon Social]
			//$nombre_sucursal=empty($this->info_sucursal($serie_sucursal)['nombre_sucursal'])?$this->info_empresa()['nombre']:$this->info_sucursal($serie_sucursal)['nombre_sucursal'];
			$factura->nombreComercial = strtoupper($this->info_sucursal($serie_sucursal)['nombre_sucursal']);  //[Nombre Comercial, si hay]*
			$factura->ruc = substr($ruc_empresa,0,12).'1'; //[Ruc]
			$factura->codDoc = "01"; //[01, Factura] [04, Nota Credito] [05, Nota Debito] [06, Guia Remision] [07, Retencion]
			$factura->establecimiento = substr($serie_factura,0,3); //[pto de emision ] **
			$factura->ptoEmision = substr($serie_factura,4,3); // [Numero Establecimiento SRI]
			$factura->secuencial = str_pad($numero_factura,9,"000000000",STR_PAD_LEFT); // [Secuencia desde 1 (9)]
			$factura->fechaEmision = date("d/m/Y", strtotime($this->info_documento($id_factura,"factura")['fecha_factura'])); //[Fecha (dd/mm/yyyy)]
			$factura->dirMatriz = strtoupper($this->info_empresa()['direccion']); //[Direccion de la Matriz ->SRI]
			//$direccion_sucursal=empty($this->info_sucursal($serie_sucursal)['direccion_sucursal'])?$this->info_empresa()['direccion']:$this->info_sucursal($serie_sucursal)['direccion_sucursal'];
			$factura->dirEstablecimiento = strtoupper($this->info_sucursal($serie_sucursal)['direccion_sucursal']); //[Direccion de Establecimiento ->SRI]
			$factura->contribuyenteEspecial =$this->confi_empresa()['resol_cont'];
			$factura->regimenRIMPE = $this->confi_empresa()['regimen_rimpe']!="SI"?"":"SI";
			$factura->agenteRetencion = $this->confi_empresa()['agente_ret']>0?$this->confi_empresa()['agente_ret']:"";
			$ruc_cliente_proveedor=$this->info_cliente($id_cliente)['ruc'];
			
			$tipo_empresa =$this->info_empresa()['tipo'];	
						switch ($tipo_empresa) {
							case "01":
								$lleva_contabilidad = "NO";
								break;
							case "02" or "03" or "04" or "05":
								$lleva_contabilidad = "SI";
								break;
						};

			$factura->obligadoContabilidad = $lleva_contabilidad; // [SI]
			$factura->tipoIdentificacionComprador = $this->info_cliente($id_cliente)['tipo_id']; //Info comprador [04, RUC][05,Cedula][06, Pasaporte][07, Consumidor final][08, Exterior][09, Placa]
			$factura->razonSocialComprador = strtoupper($this->info_cliente($id_cliente)['nombre']); //Razon social o nombres y apellidos comprador
			$factura->identificacionComprador = $this->info_cliente($id_cliente)['ruc']; // Identificacion Comprador
			
			//suma subtotales
				$subtotales_generales=$this->db->select("select sum(round(subtotal_factura-descuento,2)) as subtotal_general, sum(round(descuento,2)) as descuento_general from cuerpo_factura where ruc_empresa = '".$ruc_empresa."' and serie_factura ='".$serie_factura."' and secuencial_factura='".$numero_factura."'");
				$subtotal_general=$subtotales_generales[0]['subtotal_general'];//Sumador subtotal general
				$total_descuento=$subtotales_generales[0]['descuento_general'];//Sumador total descuento
			
			$factura->totalSinImpuestos = number_format($subtotal_general,2,'.',''); // Total sin aplicar impuestos
			$factura->totalDescuento = number_format($total_descuento,2,'.',''); // Total Dtos
			$factura->guiaRemision = $this->info_documento($id_factura,"factura")['guia_remision']; // guia de remision

			//consulta de la tabla de tarifa iva
							$codigo_impuesto_en_totales = "2";//$totales_detalle_impuestos_ventas['codigo_impuesto'];	
							if ($codigo_impuesto_en_totales =="2"){
									$subtotales_factura = array();
									$subtotales_tarifa_iva=$this->db->select("select sum(round((cf.subtotal_factura - cf.descuento) * ti.porcentaje_iva /100,4)) as total_iva, ti.codigo as codigo_porcentaje, sum(round(cf.subtotal_factura - cf.descuento,4)) as subtotal_factura FROM cuerpo_factura cf, tarifa_iva ti WHERE cf.ruc_empresa ='".$ruc_empresa."' and cf.serie_factura='".$serie_factura."' and cf.secuencial_factura ='".$numero_factura."' and ti.codigo = cf.tarifa_iva group by cf.tarifa_iva ");
								foreach ($subtotales_tarifa_iva as $sub_tarifa_iva){
			$totalImpuesto = new totalImpuesto();
			$totalImpuesto->codigo = "2";//[2, IVA][3,ICE][5, IRBPNR]						
			$totalImpuesto->codigoPorcentaje = $sub_tarifa_iva['codigo_porcentaje']; // IVA -> [0, 0%][2, 12%][6, No objeto de impuesto][7, Exento de IVA] ICE->[Tabla 19]
			$totalImpuesto->baseImponible = number_format($sub_tarifa_iva['subtotal_factura'],2,'.',''); // Suma de los impuesto del mismo cod y % (0.00)										
			$totalImpuesto->valor =number_format($sub_tarifa_iva['total_iva'],2,'.','');; // Suma de los impuesto del mismo cod y % aplicado el % (0.00)
									$subtotales_factura [] = $totalImpuesto;
								}
							}
												
			$factura->totalConImpuesto = $subtotales_factura; //Agrega el impuesto a la factura NO TOCAR ESTA LINEA
			$factura->propina = number_format($this->info_documento($id_factura,"factura")['propina'],2,'.',''); // Propina 
			$factura->importeTotal = number_format($this->info_documento($id_factura,"factura")['total_factura'],2,'.',''); // Total de Productos + impuestos
			$factura->moneda = $this->info_sucursal($serie_sucursal)['moneda_sucursal']; //DOLAR

			//desde aqui detalle de la factura y productos
					$detalle_factura=$this->db->select("select * from cuerpo_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura ='".$serie_factura."' and secuencial_factura='".$numero_factura."' ");
					
			$detalle_factura_item = array();
			$detallesAdicionales = array();
					foreach ($detalle_factura as $detalle_final) {
						$detalle_producto=$this->db->select("select * from productos_servicios WHERE id ='".$detalle_final['id_producto']."' ");
						$codigo_auxiliar=$detalle_producto[0]['codigo_auxiliar'];

						$detalleFactura = new detalleFactura();
			$detalleFactura->codigoPrincipal = $detalle_final['codigo_producto']; // Codigo del Producto
			$detalleFactura->codigoAuxiliar = $codigo_auxiliar; // Opcional
			$detalleFactura->descripcion = ucwords($detalle_final['nombre_producto']); // Nombre del producto		
			$detalleFactura->cantidad = number_format($detalle_final['cantidad_factura'],($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'],'.',''); // Cantidad
			$detalleFactura->precioUnitario = number_format($detalle_final['valor_unitario_factura'],$this->info_sucursal($serie_sucursal)['decimal_doc'],'.',''); // Valor unitario
			$detalleFactura->descuento = number_format($detalle_final['descuento'],2,'.',''); // Descuento u
			//$detalleFactura->detalleAdicional = array('detalleAdicional' => $detalle_final['tarifa_bp'] !="0"?$detalle_final['tarifa_bp']:""); // detalle adicional
			$detalleFactura->precioTotalSinImpuesto = number_format(($detalleFactura->cantidad * $detalleFactura->precioUnitario) - $detalle_final['descuento'],2,'.','');// Valor sin impuesto

			$info_adicional=$detalle_final['tarifa_bp']=='0'?"":$detalle_final['tarifa_bp'];
			if (!empty($info_adicional)){
			$detAdicional = new detalleAdicional();
			//$info_adicional=$detalle_final['tarifa_bp']=='0'?"":$detalle_final['tarifa_bp'];
			$detAdicional->nombre = "infoAdicional";
			$detAdicional->valor = $detalle_final['tarifa_bp']=='0'?"":$detalle_final['tarifa_bp'];
			$detalleFactura->detalleAdicional = $detAdicional;
			}


			$impuesto = new impuesto(); // Impuesto del detalle
			$impuesto->codigo = "2"; //del impuesto, iva, ice, bp	
			$impuesto->codigoPorcentaje =$detalle_final['tarifa_iva'];
					//para traer la tarifa de iva para el detalle de cada item de la factura
					$tarifa_iva_del_detalle=$this->db->select("select * from tarifa_iva WHERE codigo='".$detalle_final['tarifa_iva']."' ");
					$tarifa_de_iva=$tarifa_iva_del_detalle[0]['porcentaje_iva'];

			$impuesto->tarifa =$tarifa_de_iva;
			$impuesto->baseImponible = number_format($detalleFactura->precioTotalSinImpuesto,2,'.',''); // subtotal o base
			$impuesto->valor = number_format((($detalleFactura->precioTotalSinImpuesto * $tarifa_de_iva) / 100),2,'.',''); // valor o sea el 12 por ciento de la base
			$detalleFactura->impuestos = $impuesto;
					$detalle_factura_item [] = $detalleFactura;
							}

			$factura->detalles = $detalle_factura_item;
			//hasta aqui detalle de la factura

					// trae formas de pago
					$busca_fp_factura=$this->db->select("SELECT * FROM formas_pago_ventas WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura ='".$serie_factura."' and secuencial_factura='".$numero_factura."'");
			
			$detalle_pago= array();
					foreach ($busca_fp_factura as $info_fp_factura) {
			$pago = new pagos();
					$codigo_fp =$info_fp_factura['id_forma_pago'];
					$valor_pago =$info_fp_factura['valor_pago'];
			$pago->formaPago = $codigo_fp;
			$pago->total =number_format($valor_pago,2,'.','');
			$pago->plazo = $this->info_cliente($id_cliente)['plazo'];
			$pago->unidadTiempo = "Días";
			$detalle_pago[]= $pago;
					}
			$factura->pagos = $detalle_pago;

			//para mostrar la casilla de propina dependiendo si esta asignada o no
					$resultado_tasa = $this->confi_facturacion($serie_sucursal)['tasa_turistica'];
					if ($resultado_tasa=="SI"){
						$concepto_tasa="TASA TURISTICA";
						}else{
						$concepto_tasa="OTROS";
					}

			$rubro = new rubro();
			$rubro->concepto = $concepto_tasa;
			$rubro->total = number_format($this->info_documento($id_factura,"factura")['tasa_turistica'],2,'.','');
			$factura->otrosRubros = $rubro;

			//desde aqui detalle de adicionales de la factura
					$detalle_adicional_factura=$this->db->select("select * from detalle_adicional_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura ='".$serie_factura."' and secuencial_factura='".$numero_factura."' ");
					
			$camposAdicionales = array();
					foreach ($detalle_adicional_factura as $detalle_adicional) {
					$nombre_adicional=$detalle_adicional['adicional_concepto'];
					$descripcion_adicional=$detalle_adicional['adicional_descripcion'];
			if ($nombre_adicional !=null && $descripcion_adicional !=null){
			$campoAdicional = new campoAdicional();
			$campoAdicional->nombre = $nombre_adicional;
			$campoAdicional->valor = $descripcion_adicional;
			$camposAdicionales[] = $campoAdicional;
			}
					}
			$factura->infoAdicional = $camposAdicionales;
		
			$procesar = $this->procesar_comprobante($factura, 'factura', $modo_envio, $serie_factura, $numero_factura, $ruc_cliente_proveedor);
			return $procesar;			
		//}
	}
	//hasta aqui la factura
		
public function EnviarRetencion($id_retencion, $modo_envio){
	$ruc_empresa = $_SESSION['ruc_empresa'];
		//solo si esta pendiente se puede enviar al sri	
		$tipo_documento="retencion";
		$estado_documento_sri = $this->info_documento($id_retencion,$tipo_documento)['estado_sri'];
		$serie_sucursal=$this->info_documento($id_retencion,$tipo_documento)['serie_retencion'];
		$numero_documento=$this->info_documento($id_retencion,$tipo_documento)['secuencial_retencion'];
		$id_proveedor=$this->info_documento($id_retencion,$tipo_documento)['id_proveedor'];
		$documento='retencion';
		
		//if($estado_documento_sri !="PENDIENTE"){
		//	return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se puede enviar al SRI la retención, ".$serie_sucursal."-".$numero_documento.", su estado es ".$estado_documento_sri."</div>"."<br>";
		//}else{				
		$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();

			$retencion = new \comprobanteRetencion();
			$retencion->configAplicacion = $this->config_app($documento, $serie_sucursal);
			$retencion->configCorreo = $this->confi_mail($modo_envio);
            $retencion->ambiente = $this->confi_empresa()['tipo_ambiente']; //[1,Prueba][2,Produccion] 
            $retencion->tipoEmision = $this->confi_empresa()['tipo_emision']; //[1,Emision Normal][2,Emision Por Indisponibilidad del sistema
            $retencion->razonSocial = strtoupper($this->info_empresa()['nombre']); //[Razon Social]
            $retencion->nombreComercial = strtoupper($this->info_sucursal($serie_sucursal)['nombre_sucursal']);  //[Nombre Comercial, si hay]*
            $retencion->ruc = substr($ruc_empresa,0,12).'1'; //[Ruc]
            $retencion->codDoc = "07";
            $retencion->establecimiento = substr($serie_sucursal,0,3); //[pto de emision ] **
            $retencion->ptoEmision = substr($serie_sucursal,4,3);
            $retencion->secuencial = str_pad($numero_documento,9,"000000000",STR_PAD_LEFT); // [Secuencia desde 1 (9)];
            $retencion->fechaEmision =date("d/m/Y", strtotime($this->info_documento($id_retencion,$tipo_documento)['fecha_emision'])); //[Fecha (dd/mm/yyyy)]
            $retencion->dirMatriz = strtoupper($this->info_empresa()['direccion']);
            $retencion->dirEstablecimiento = strtoupper($this->info_sucursal($serie_sucursal)['direccion_sucursal']);
            $retencion->contribuyenteEspecial = $this->confi_empresa()['resol_cont'];
			$retencion->regimenRIMPE = $this->confi_empresa()['regimen_rimpe']!="SI"?"":"SI";
			$retencion->agenteRetencion = $this->confi_empresa()['agente_ret']>0?$this->confi_empresa()['agente_ret']:"";
			$ruc_cliente_proveedor=$this->info_proveedor($id_proveedor)['ruc_proveedor'];
			
			$tipo_empresa =$this->info_empresa()['tipo'];	
						switch ($tipo_empresa) {
							case "01":
								$lleva_contabilidad = "NO";
								break;
							case "02" or "03" or "04" or "05":
								$lleva_contabilidad = "SI";
								break;
						};
            $retencion->obligadoContabilidad = $lleva_contabilidad;
            $retencion->tipoIdentificacionSujetoRetenido = $this->info_proveedor($id_proveedor)['tipo_id_proveedor'];
            $retencion->razonSocialSujetoRetenido = strtoupper ($this->info_proveedor($id_proveedor)['razon_social']);
            $retencion->identificacionSujetoRetenido = $this->info_proveedor($id_proveedor)['ruc_proveedor'];
			
				$detalle_retencion=$this->db->select("select * from cuerpo_retencion cr, retenciones_sri rs WHERE cr.ruc_empresa = '".$ruc_empresa."' and cr.serie_retencion ='".$serie_sucursal."' and cr.secuencial_retencion='".$numero_documento."' and cr.id_retencion = rs.id_ret ");
				$impuestoArray = array();
				
				foreach ($detalle_retencion as $detalle_retencion_compras) {
				$impuesto=$detalle_retencion_compras['impuesto'];
				// se saca de la tabla 19 del sri
				switch ($impuesto) {
				case "RENTA":
					$codigo_impuesto="1";
					break;
				case "IVA":
					$codigo_impuesto="2";
					break;
				case "ISD":
					$codigo_impuesto="6";
					break;
				};
				
				//$codigo_retencion = $detalle_retencion_compras['codigo_impuesto'];
				//$base_imponible = $detalle_retencion_compras['base_imponible'];
				//$porcentaje_retencion = $detalle_retencion_compras['porcentaje_retencion'];
				//$valor_retenido = $detalle_retencion_compras['valor_retenido'];
				//$ejercicio_fiscal = $detalle_retencion_compras['ejercicio_fiscal'];
					
				
				$impuesto = new \impuestoComprobanteRetencion(); // Impuesto del detalle
                $impuesto->codigo = $codigo_impuesto;
                $impuesto->codigoRetencion = $detalle_retencion_compras['codigo_impuesto'];
                $impuesto->baseImponible = number_format($detalle_retencion_compras['base_imponible'],2,'.','');
                $impuesto->porcentajeRetener = $detalle_retencion_compras['porcentaje_retencion'];
                $impuesto->valorRetenido = number_format($detalle_retencion_compras['valor_retenido'],2,'.','');
                $impuesto->codDocSustento = $this->info_documento($id_retencion,$tipo_documento)['tipo_comprobante'];//tipo de documento
				$impuesto->numDocSustento = str_replace("-","",$this->info_documento($id_retencion,$tipo_documento)['numero_comprobante']);//numero de factura a retener
                $impuesto->fechaEmisionDocSustento = date("d/m/Y", strtotime($this->info_documento($id_retencion,$tipo_documento)['fecha_documento']));

                $impuestoArray[] = $impuesto;
				}
				
            $retencion->periodoFiscal = $detalle_retencion_compras['ejercicio_fiscal'];
            $retencion->impuestos = $impuestoArray;
           
		   //desde aqui detalle de adicionales de la retencion   
		   
		$detalle_adicional_retencion=$this->db->select("select * from detalle_adicional_retencion WHERE ruc_empresa = '".$ruc_empresa."' and serie_retencion ='".$serie_sucursal."' and secuencial_retencion='".$numero_documento."' ");
		
		$camposAdicionales = array();
				foreach ($detalle_adicional_retencion as $detalle_adicional) {
				$nombre_adicional=$detalle_adicional['adicional_concepto'];
				$descripcion_adicional=$detalle_adicional['adicional_descripcion'];
		//if ($nombre_adicional !=null && $descripcion_adicional !=null){
		$campoAdicional = new campoAdicional();
		$campoAdicional->nombre = $nombre_adicional;
		$campoAdicional->valor = $descripcion_adicional;
		$camposAdicionales[] = $campoAdicional;
		//}
				}
		$retencion->infoAdicional = $camposAdicionales;
		$procesar = $this->procesar_comprobante($retencion, 'retencion', $modo_envio, $serie_sucursal, $numero_documento, $ruc_cliente_proveedor);
		return $procesar;
	  //}	
	}
	//hasta aqui la retencion
	
public function EnviarNc($id_nc, $modo_envio){
	$ruc_empresa = $_SESSION['ruc_empresa'];
		//solo si esta pendiente se puede enviar al sri	
		$tipo_documento="nc";
		$estado_documento_sri = $this->info_documento($id_nc,$tipo_documento)['estado_sri'];
		$serie_sucursal=$this->info_documento($id_nc,$tipo_documento)['serie_nc'];
		$numero_documento=$this->info_documento($id_nc,$tipo_documento)['secuencial_nc'];
		$id_cliente=$this->info_documento($id_nc,"nc")['id_cliente'];
		$documento='nc';
		
		//if($estado_documento_sri !="PENDIENTE"){
		//	return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se puede enviar al SRI la nota de crédito, ".$serie_sucursal."-".$numero_documento.", su estado es ".$estado_documento_sri."</div>"."<br>";
		//}else{				
		$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();

		$notaCredito = new notaCredito();
		$notaCredito->configAplicacion = $this->config_app($documento, $serie_sucursal);
		$notaCredito->configCorreo = $this->confi_mail($modo_envio);
		$notaCredito->ambiente =  $this->confi_empresa()['tipo_ambiente']; //[1,Prueba][2,Produccion]
		$notaCredito->tipoEmision = $this->confi_empresa()['tipo_emision']; //[1,Emision Normal][2,Emision Por Indisponibilidad del sistema
		$notaCredito->razonSocial =  strtoupper($this->info_empresa()['nombre']); //[Razon Social]
		$notaCredito->nombreComercial = strtoupper($this->info_sucursal($serie_sucursal)['nombre_sucursal']);  //[Nombre Comercial, si hay]*
		$notaCredito->ruc = substr($ruc_empresa,0,12).'1'; //[Ruc]
		$notaCredito->codDoc = "04"; //[01, Factura] [04, Nota Credito] [05, Nota Debito] [06, Guia Remision] [07, Guia de Retencion]
		$notaCredito->establecimiento = substr($serie_sucursal,0,3); //[pto de emision ] **
		$notaCredito->ptoEmision = substr($serie_sucursal,4,3);
		$notaCredito->secuencial = str_pad($numero_documento,9,"000000000",STR_PAD_LEFT); // [Secuencia desde 1 (9)];
		$notaCredito->fechaEmision = date("d/m/Y", strtotime($this->info_documento($id_nc,$tipo_documento)['fecha_nc'])); //[Fecha (dd/mm/yyyy)]
		$notaCredito->dirMatriz = strtoupper($this->info_empresa()['direccion']);
		$notaCredito->dirEstablecimiento = strtoupper($this->info_sucursal($serie_sucursal)['direccion_sucursal']);
		$notaCredito->contribuyenteEspecial = $this->confi_empresa()['resol_cont'];
		$notaCredito->regimenRIMPE = $this->confi_empresa()['regimen_rimpe']!="SI"?"":"SI";
		$notaCredito->agenteRetencion = $this->confi_empresa()['agente_ret']>0?$this->confi_empresa()['agente_ret']:"";
		$ruc_cliente_proveedor=$this->info_cliente($id_cliente)['ruc'];

		$tipo_empresa =$this->info_empresa()['tipo'];	
						switch ($tipo_empresa) {
							case "01":
								$lleva_contabilidad = "NO";
								break;
							case "02" or "03" or "04" or "05":
								$lleva_contabilidad = "SI";
								break;
						};
	$notaCredito->obligadoContabilidad = $lleva_contabilidad; // [SI]
	$notaCredito->tipoIdentificacionComprador = $this->info_cliente($id_cliente)['tipo_id']; //Info comprador [04, RUC][05,Cedula][06, Pasaporte][07, Consumidor final][08, Exterior][09, Placa]
	$notaCredito->razonSocialComprador = strtoupper($this->info_cliente($id_cliente)['nombre']); //Razon social o nombres y apellidos comprador
	$notaCredito->rise = "";
	$notaCredito->identificacionComprador = $this->info_cliente($id_cliente)['ruc']; // Identificacion Comprador
	$notaCredito->codDocModificado = "01";
	$documento_modificado=$this->info_documento($id_nc,$tipo_documento)['factura_modificada'];
	$notaCredito->numDocModificado = $documento_modificado;
	//traer la fecha de la factura modificada
	$fecha_factura_modificada = $this->info_documento($id_nc,$tipo_documento)['fecha_factura'];
	$notaCredito->fechaEmisionDocSustento = date("d/m/Y", strtotime($fecha_factura_modificada));
	//suma subtotales
	$subtotales_nc=$this->db->select("select sum(subtotal_nc-descuento) as subtotal_nota, sum(descuento) as descuento_nc from cuerpo_nc where ruc_empresa = '".$ruc_empresa."' and serie_nc ='".$serie_sucursal."' and secuencial_nc='".$numero_documento."'");
	$notaCredito->totalSinImpuestos = number_format($subtotales_nc[0]['subtotal_nota'],2,'.','');
	$notaCredito->valorModificacion = number_format($this->info_documento($id_nc,$tipo_documento)['total_nc'],2,'.','');
	$notaCredito->moneda = $this->info_sucursal($serie_sucursal)['moneda_sucursal']; //DOLAR;

//consulta de la tabla de impuestos de ventas

		$codigo_impuesto_en_totales = "2";//$totales_detalle_impuestos_ventas['codigo_impuesto'];	
		if ($codigo_impuesto_en_totales =="2"){
				$totales_nc = array();						
				$subtotales_tarifa_iva=$this->db->select("select ti.porcentaje_iva as porcentaje_iva,(sum(cnc.subtotal_nc-descuento) * ti.porcentaje_iva /100) as subtotal_iva, ti.codigo as codigo_porcentaje, (sum(cnc.subtotal_nc-descuento)) as subtotal_nc from cuerpo_nc cnc, tarifa_iva ti where cnc.ruc_empresa ='".$ruc_empresa."' and cnc.serie_nc='".$serie_sucursal."' and cnc.secuencial_nc ='".$numero_documento."' and ti.codigo = cnc.tarifa_iva group by cnc.tarifa_iva " );
				foreach ($subtotales_tarifa_iva as $totales_subtotal_tarifa){
					$totalImpuesto = new totalImpuesto();
					$totalImpuesto->codigo = "2";//[2, IVA][3,ICE][5, IRBPNR]
					$totalImpuesto->codigoPorcentaje = $totales_subtotal_tarifa['codigo_porcentaje']; // IVA -> [0, 0%][2, 12%][6, No objeto de impuesto][7, Exento de IVA] ICE->[Tabla 19]
					$totalImpuesto->baseImponible = number_format($totales_subtotal_tarifa['subtotal_nc'],2,'.','') ; // Suma de los impuesto del mismo cod y % (0.00)										
					$totalImpuesto->valor = number_format($totales_subtotal_tarifa['subtotal_iva'],2,'.',''); // Suma de los impuesto del mismo cod y % aplicado el % (0.00)
					$totales_nc [] = $totalImpuesto;
				}
		}
						
$notaCredito->totalConImpuesto = $totales_nc;
//desde aqui detalle de la nc y productos
		$detalle_nc=$this->db->select("select * from cuerpo_nc WHERE ruc_empresa = '".$ruc_empresa."' and serie_nc ='".$serie_sucursal."' and secuencial_nc='".$numero_documento."' ");
		$detalle_nc_item = array();
		foreach ($detalle_nc as $detalle_final){
			$detalle = new detalleNotaCredito();
			$detalle->codigoInterno = $detalle_final['codigo_producto']; // Codigo del Producto
			$detalle->codigoAdicional = $detalle_final['codigo_producto']; // Opcional
			$detalle->descripcion = ucwords($detalle_final['nombre_producto']); // Nombre del producto
			$detalle->cantidad = number_format($detalle_final['cantidad_nc'],($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'],'.',''); // Cantidad
			$detalle->precioUnitario = number_format($detalle_final['valor_unitario_nc'],$this->info_sucursal($serie_sucursal)['decimal_doc'],'.',''); // Valor unitario; // Valor unitario
			$detalle->descuento = number_format($detalle_final['descuento'],2,'.',''); // Descuento u
			$detalle->precioTotalSinImpuesto = number_format($detalle_final['subtotal_nc']-$detalle_final['descuento'],2,'.','');// Valor sin impuesto
				$impuesto = new impuesto(); // Impuesto del detalle
				$impuesto->codigo = "2"; //del impuesto, iva, ice, bp	
				$impuesto->codigoPorcentaje =$detalle_final['tarifa_iva'];
				//para traer la tarifa de iva para el detalle de cada item de la nc
				$detalle_tarifa_iva=$this->db->select("select * from tarifa_iva WHERE codigo='".$detalle_final['tarifa_iva']."' ");
				$impuesto->tarifa =$detalle_tarifa_iva[0]['porcentaje_iva'];
				$impuesto->baseImponible = number_format($detalle_final['subtotal_nc']-$detalle_final['descuento'],2,'.',''); // subtotal o base
				$impuesto->valor = number_format(((($detalle_final['subtotal_nc']-$detalle_final['descuento']) * $detalle_tarifa_iva[0]['porcentaje_iva']) / 100),2,'.',''); // valor o sea el 12 por ciento de la base
			$detalle->impuestos = $impuesto;
			$detalle_nc_item [] = $detalle;
		}

		$notaCredito->detalles = $detalle_nc_item;
		//hasta aqui detalle de la nc
		$notaCredito->motivo = strtoupper($this->info_documento($id_nc,$tipo_documento)['motivo']);

		//desde aqui detalle de adicionales de la nc
				$detalle_adicional_nc=$this->db->select("select * from detalle_adicional_nc WHERE ruc_empresa = '".$ruc_empresa."' and serie_nc ='".$serie_sucursal."' and secuencial_nc='".$numero_documento."' ");
				
		$camposAdicionales = array();
				foreach ($detalle_adicional_nc as $detalle_adicional){
				$nombre_adicional=$detalle_adicional['adicional_concepto'];
				$descripcion_adicional=$detalle_adicional['adicional_descripcion'];
		if ($nombre_adicional !=null && $descripcion_adicional !=null){
		$campoAdicional = new campoAdicional();
		$campoAdicional->nombre = $nombre_adicional;
		$campoAdicional->valor = $descripcion_adicional;
		$camposAdicionales[] = $campoAdicional;
		}
				}
		$notaCredito->infoAdicional = $camposAdicionales;	
		$procesar = $this->procesar_comprobante($notaCredito, 'nc', $modo_envio, $serie_sucursal, $numero_documento, $ruc_cliente_proveedor);
		return $procesar;
		//}	
	}
	//para enviar gr
	
public function EnviarGr($id_gr, $modo_envio){
	$ruc_empresa = $_SESSION['ruc_empresa'];
		//solo si esta pendiente se puede enviar al sri	
		$tipo_documento="gr";
		$estado_documento_sri = $this->info_documento($id_gr,$tipo_documento)['estado_sri'];
		$serie_sucursal=$this->info_documento($id_gr,$tipo_documento)['serie_gr'];
		$numero_documento=$this->info_documento($id_gr,$tipo_documento)['secuencial_gr'];
		$id_transportista=$this->info_documento($id_gr,"gr")['id_transportista'];
		$id_cliente=$this->info_documento($id_gr,"gr")['id_cliente'];
		$documento='gr';
		
		//if($estado_documento_sri !="PENDIENTE"){
		//	return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se puede enviar al SRI la guía de remisión, ".$serie_sucursal."-".$numero_documento.", su estado es ".$estado_documento_sri."</div>"."<br>";
		//}else{				
		$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();

		$guiaRemision = new guiaRemision();
		$guiaRemision->configAplicacion = $this->config_app($documento, $serie_sucursal);
		$guiaRemision->configCorreo = $this->confi_mail($modo_envio);
		$guiaRemision->ambiente =  $this->confi_empresa()['tipo_ambiente']; //[1,Prueba][2,Produccion]
		$guiaRemision->tipoEmision = $this->confi_empresa()['tipo_emision']; //[1,Emision Normal][2,Emision Por Indisponibilidad del sistema
		$guiaRemision->razonSocial =  strtoupper($this->info_empresa()['nombre']); //[Razon Social]
		$guiaRemision->nombreComercial = strtoupper($this->info_sucursal($serie_sucursal)['nombre_sucursal']);  //[Nombre Comercial, si hay]*
		$guiaRemision->ruc = substr($ruc_empresa,0,12).'1'; //[Ruc]
		$guiaRemision->codDoc = "06"; //[01, Factura] [04, Nota Credito] [05, Nota Debito] [06, Guia Remision] [07, Guia de Retencion]
		$guiaRemision->establecimiento = substr($serie_sucursal,0,3); //[pto de emision ] **
		$guiaRemision->ptoEmision = substr($serie_sucursal,4,3);
		$guiaRemision->secuencial = str_pad($numero_documento,9,"000000000",STR_PAD_LEFT); // [Secuencia desde 1 (9)];
		$guiaRemision->fechaEmision = date("d/m/Y", strtotime($this->info_documento($id_gr,$tipo_documento)['fecha_gr'])); //[Fecha (dd/mm/yyyy)]
		$guiaRemision->dirMatriz = strtoupper($this->info_empresa()['direccion']);
		$guiaRemision->dirEstablecimiento = strtoupper($this->info_sucursal($serie_sucursal)['direccion_sucursal']);
		$guiaRemision->contribuyenteEspecial = $this->confi_empresa()['resol_cont'];
		$guiaRemision->regimenRIMPE = $this->confi_empresa()['regimen_rimpe']!="SI"?"":"SI";
		$guiaRemision->agenteRetencion = $this->confi_empresa()['agente_ret']>0?$this->confi_empresa()['agente_ret']:"";
		$ruc_cliente_proveedor=$this->info_cliente($id_cliente)['ruc'];
		
		$tipo_empresa =$this->info_empresa()['tipo'];
		
						switch ($tipo_empresa) {
							case "01":
								$lleva_contabilidad = "NO";
								break;
							case "02" or "03" or "04" or "05":
								$lleva_contabilidad = "SI";
								break;
						};
		$guiaRemision->obligadoContabilidad = $lleva_contabilidad; // [SI]
		$destinatarios = array();
	$destinatario = new destinatario();
	$destinatario->codEstabDestino = $this->info_documento($id_gr,$tipo_documento)['cod_est_destino'];
		//trae el detalle de la guia de remision
			$busca_detalle_guia=$this->db->select("SELECT * FROM cuerpo_gr WHERE ruc_empresa = '".$ruc_empresa."' and serie_gr ='".$serie_sucursal."' and secuencial_gr='".$numero_documento."'");
			$detalles = array();
					foreach ($busca_detalle_guia as $info_detalle_guia){
					$detalleGuiaRemision = new detalleGuiaRemision();	
					
					$detalle_producto=$this->db->select("select * from productos_servicios WHERE id ='".$info_detalle_guia['id_producto']."' ");
						$codigo_auxiliar=$detalle_producto[0]['codigo_auxiliar'];

					$detalleGuiaRemision->cantidad = number_format($info_detalle_guia['cantidad_gr'],($this->info_sucursal($serie_sucursal)['decimal_cant']=='1')?0:$this->info_sucursal($serie_sucursal)['decimal_cant'],'.',''); // Codigo del Producto
					$detalleGuiaRemision->codigoAdicional = strtoupper($codigo_auxiliar); // Opcional
					$detalleGuiaRemision->codigoInterno = strtoupper($info_detalle_guia['codigo_producto']); // codigo
					$detalleGuiaRemision->descripcion = strtoupper($info_detalle_guia['nombre_producto']); // Nombre del producto
					$detalles[] = $detalleGuiaRemision;
					}
		//HASTA AQUI EL DETALLE DE LA GUIA
	$destinatario->detalles = $detalles;
	$destinatario->dirDestinatario = strtoupper($this->info_documento($id_gr,$tipo_documento)['destino']);
	$destinatario->docAduaneroUnico = $this->info_documento($id_gr,$tipo_documento)['cod_aduanero'];
	$documento_modificado=$this->info_documento($id_gr,$tipo_documento)['factura_aplica'];
	if (!empty($documento_modificado)){
	$destinatario->codDocSustento = "01";
	$destinatario->numDocSustento = $this->info_documento($id_gr,$tipo_documento)['factura_aplica'];
	$datos_factura_modificada = $this->db->select("SELECT * FROM encabezado_factura WHERE ruc_empresa='".$ruc_empresa."' and serie_factura = '".substr($documento_modificado,0,7)."' and secuencial_factura='".substr($documento_modificado,8,9)."'");
	$destinatario->numAutDocSustento = $datos_factura_modificada[0]['aut_sri'];
	$destinatario->fechaEmisionDocSustento = date("d/m/Y", strtotime($datos_factura_modificada[0]['fecha_factura']));
	}
	$destinatario->identificacionDestinatario = $this->info_cliente($id_cliente)['ruc'];
	$destinatario->motivoTraslado = strtoupper($this->info_documento($id_gr,$tipo_documento)['motivo']);
	$destinatario->razonSocialDestinatario = strtoupper($this->info_cliente($id_cliente)['nombre']);
	$destinatario->ruta = strtoupper($this->info_documento($id_gr,$tipo_documento)['ruta']);
	$destinatarios [] = $destinatario;

$guiaRemision->destinatarios = $destinatarios;//destinatario;
$guiaRemision->dirPartida = strtoupper($this->info_documento($id_gr,$tipo_documento)['origen']);
$guiaRemision->fechaFinTransporte = date("d/m/Y", strtotime($this->info_documento($id_gr,$tipo_documento)['fecha_llegada']));
$guiaRemision->fechaIniTransporte = date("d/m/Y", strtotime($this->info_documento($id_gr,$tipo_documento)['fecha_salida']));

//desde aqui detalle de adicionales de la guia
			$detalle_adicional_guia=$this->db->select("select * from detalle_adicional_gr WHERE ruc_empresa = '".$ruc_empresa."' and serie_gr='".$serie_sucursal."' and secuencial_gr='".$numero_documento."' ");
					
			$camposAdicionales = array();
					foreach ($detalle_adicional_guia as $detalle_adicional) {
					$nombre_adicional=$detalle_adicional['adicional_concepto'];
					$descripcion_adicional=$detalle_adicional['adicional_descripcion'];
			if ($nombre_adicional !=null && $descripcion_adicional !=null){
			$campoAdicional = new campoAdicional();
			$campoAdicional->nombre = $nombre_adicional;
			$campoAdicional->valor = $descripcion_adicional;
			$camposAdicionales[] = $campoAdicional;
			}
					}
	$guiaRemision->infoAdicional = $camposAdicionales;

	$guiaRemision->placa = strtoupper($this->info_documento($id_gr,$tipo_documento)['placa']);
	$guiaRemision->razonSocialTransportista = strtoupper($this->info_cliente($id_transportista)['nombre']); //Razon social o nombres y apellidos comprador
	$guiaRemision->rise = "";
	$guiaRemision->rucTransportista = $this->info_cliente($id_transportista)['ruc']; // Identificacion Comprador
	$guiaRemision->tipoIdentificacionTransportista = $this->info_cliente($id_transportista)['tipo_id']; //Info comprador [04, RUC][05,Cedula][06, Pasaporte][07, Consumidor final][08, Exterior][09, Placa]

		$procesar = $this->procesar_comprobante($guiaRemision, 'gr', $modo_envio, $serie_sucursal, $numero_documento, $ruc_cliente_proveedor);
		return $procesar;

		//}	
	}
	
	
		//procesar comprobante
	public function procesar_comprobante($documento_a_procesar, $documento, $modo_envio, $serie_documento, $secuencial_documento, $ruc_cliente_proveedor){
			$encabezado_tabla="encabezado_".$documento;
			$serie_encabezado="serie_".$documento;
			$secuencial_encabezado="secuencial_".$documento;
			$ruc_empresa = $_SESSION['ruc_empresa'];
						
			//para generar el pdf y xml sin enviar mail
			if ($modo_envio=='offline'){
			$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();
			$procesarComprobante = new generarXMLPDF();
			//$respuestaPdf = new procesarXMLResponse();
			$procesarComprobante->comprobante = $documento_a_procesar;
			$procesarComprobante->envioEmail = false;// false para que NO se envie el correo
			$respuesta = $procesarComprobanteElectronico->generarXMLPDF($procesarComprobante);
			echo "<script>
				$.notify('Pdf y xml Generados con éxito','success');
				</script>";
				//var_dump($documento_a_procesar);
			}
			
			//para enviar a autorizar al sri y luego enviar el correo
			if ($modo_envio=='online'){
			$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();
			$procesarComprobante = new procesarComprobante();
			$procesarComprobante->comprobante = $documento_a_procesar;
			$procesarComprobante->envioSRI = false;// false para que NO se envie directamente al sri y solo firme
			$respuesta = $procesarComprobanteElectronico->procesarComprobante($procesarComprobante);
			
			if($respuesta->return->estadoComprobante == "FIRMADO"){
			$procesarComprobante = new procesarComprobante();
			$procesarComprobante->comprobante = $documento_a_procesar;
			$procesarComprobante->envioSRI = true; // true envia el comprobante ya firmado al sri  y false es para que me bote solo el pdf
			$respuesta = $procesarComprobanteElectronico->procesarComprobante($procesarComprobante);
			}
			  $estado_sri=$respuesta->return->estadoComprobante;
			  $aut_sri=$respuesta->return->claveAcceso;
				if($estado_sri == "AUTORIZADO" ){
				//para PONER EN EL ESTADO DEL SRI COMO AUTORIZADA
					$actualiza_estado_autorizado = $this->db->exec("UPDATE $encabezado_tabla SET estado_sri='AUTORIZADO', ambiente = '".$this->confi_empresa()['tipo_ambiente']."', aut_sri='".$aut_sri."', estado_mail='ENVIADO' WHERE ruc_empresa = '".$ruc_empresa."' and $serie_encabezado= '".$serie_documento."' and $secuencial_encabezado='".$secuencial_documento."'");
					
					if ( $actualiza_estado_autorizado ) {
							return "<div class='alert alert-success' role='alert'><span class='glyphicon glyphicon-ok'></span> " .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT)." AUTORIZADA</div>"."<br>";
						}else{
							return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se registro la " .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT).",vuelva a intentarlo.</div>"."<br>";
						}
						
				}else{
					$error_sri=$respuesta->return->mensajes->mensaje;
					$detalle_error_sri=$respuesta->return->mensajes->informacionAdicional;
						if ($estado_sri == "DEVUELTA" && ($error_sri=="CLAVE ACCESO REGISTRADA" or $error_sri=="ERROR SECUENCIAL REGISTRADO")){
						$actualiza_estado_devuelto = $this->db->exec("UPDATE $encabezado_tabla SET estado_sri='AUTORIZADO', ambiente = '".$this->confi_empresa()['tipo_ambiente']."', aut_sri='".$aut_sri."', estado_mail='ENVIADO' WHERE ruc_empresa = '".$ruc_empresa."' and $serie_encabezado= '".$serie_documento."' and $secuencial_encabezado='".$secuencial_documento."'");
						return "<div class='alert alert-success' role='alert'><span class='glyphicon glyphicon-ok'></span>" .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT)." AUTORIZADA</div>"."<br>";
						}else{
						return "<div class='alert alert-danger' role='alert'> ".$estado_sri.' - '.$error_sri. "<br>".$detalle_error_sri."<br></div>";
						}
				}
				return false;
			}
			
	}	
	
			//procesar proformas
	public function procesar_proforma($documento_a_procesar, $documento, $serie_documento, $secuencial_documento){
			$encabezado_tabla="encabezado_".$documento;
			$serie_encabezado="serie_".$documento;
			$secuencial_encabezado="secuencial_".$documento;
			$ruc_empresa = $_SESSION['ruc_empresa'];
						
			$procesarComprobanteElectronico = new ProcesarComprobanteElectronico();						
			$procesarProforma = new procesarProforma();
			$procesarProforma->proforma = $documento_a_procesar;
			$respuesta = $procesarComprobanteElectronico->procesarProforma($procesarProforma);

			$estado_proforma=$respuesta->return->estadoComprobante;
			//$estado_correo = isset($respuesta->return->mensajes->mensaje)?$respuesta->return->mensajes->mensaje:'NO ENVIADO';
			$estado_correo = $respuesta->return->mensajes->mensaje;

			if($estado_proforma == "CREADA" ){//&& $estado_correo !=""
					$actualiza_estado = $this->db->exec("UPDATE $encabezado_tabla SET estado_proforma='ENVIADA', estado_mail='ENVIADO' WHERE ruc_empresa = '".$ruc_empresa."' and $serie_encabezado= '".$serie_documento."' and $secuencial_encabezado='".$secuencial_documento."'");
						if ( $actualiza_estado ) {
							return "<div class='alert alert-success' role='alert'><span class='glyphicon glyphicon-ok'></span> " .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT). $estado_correo. "</div><br>";
						}else{
							return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se pudo enviar la " .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT).",vuelva a intentarlo.</div>"."<br>";
						}
				}
				
			if($estado_proforma == "CREADA" && $estado_correo ==""){
					$actualiza_estado = $this->db->exec("UPDATE $encabezado_tabla SET estado_proforma='CREADA', estado_mail='PENDIENTE' WHERE ruc_empresa = '".$ruc_empresa."' and $serie_encabezado= '".$serie_documento."' and $secuencial_encabezado='".$secuencial_documento."'");
						if ( $actualiza_estado ) {
							return "<div class='alert alert-warning' role='alert'><span class='glyphicon glyphicon-ok'></span> " .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT)." CREADA - CORREO NO ENVIADO</div>"."<br>";
						}else{
							return "<div class='alert alert-danger' role='alert'><span class='glyphicon glyphicon-ban-circle'></span> No se pudo enviar la " .strtoupper($documento). " ".$serie_documento."-".str_pad($secuencial_documento,9,"000000000",STR_PAD_LEFT).",vuelva a intentarlo.</div>"."<br>";
						}
				}
	}
	
}

?>
