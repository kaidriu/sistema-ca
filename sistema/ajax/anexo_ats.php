<?php
/* Connect To Database*/
include("../conexiones/conectalogin.php");
$con = conenta_login();
session_start();
$id_usuario = $_SESSION['id_usuario'];
$ruc_empresa = $_SESSION['ruc_empresa'];

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
if($action == 'anexo_ats'){
		if (empty($_POST['mes'])) {
           $errors[] = "Seleccione mes.";
        } else if (!empty($_POST['mes'])){
$mes=mysqli_real_escape_string($con,(strip_tags($_POST["mes"],ENT_QUOTES)));
$anio=mysqli_real_escape_string($con,(strip_tags($_POST["anio_periodo"],ENT_QUOTES)));
$microempresa=mysqli_real_escape_string($con,(strip_tags($_POST["microempresa"],ENT_QUOTES)));

$informante = informante($ruc_empresa, $mes, $anio, $con, $microempresa);
$compras = compras($ruc_empresa, $mes, $anio, $con, $microempresa);

$string = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><iva>'.$informante.$compras.'</iva>';

$nombre="AT-".$mes.$anio.".xml";
$file=fopen("../xml/".$nombre,"w") or die("Problemas en la creacion");//En esta linea lo que hace PHP es crear el archivo, si ya existe lo sobreescribe
fwrite($file,$string);
fclose($file);
$dir = '../xml/';
//Si no existe la carpeta la creamos
if (!file_exists($dir))
	mkdir($dir);
	
        //Declaramos la ruta y nombre del archivo a generar
	$archivo = $dir.$nombre;


?>
<div class="col-md-4 col-md-offset-4">
		<div class="panel-heading">	
			<h4><a class="list-group-item list-group-item-success text-center" href="<?php echo $archivo ?>" download><span class="glyphicon glyphicon-download-alt"></span> Descargar xml</a></h4>
		</div>
</div>		
<?php
}else {
			$errors []= "Error desconocido.";
		}
}

 function informante($ruc_empresa, $mes, $anio, $con, $microempresa){
	$datos_empresa=mysqli_query($con,"select * from empresas WHERE mid(ruc,1,12) = '".substr($ruc_empresa,0,12)."'"); 
	$row_empresas=mysqli_fetch_array($datos_empresa);
	$razon_social= depurar_nombre(strtoupper($row_empresas['nombre']),$force_lowercase = false, $anal = false);
	$datos_sucursales=mysqli_query($con,"select * from sucursales WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'"); 
	$total_sucursales=str_pad(mysqli_num_rows($datos_sucursales),3,"000",STR_PAD_LEFT);
if ($microempresa=="1"){
$leyenda_microempresa="<regimenMicroempresa>SI</regimenMicroempresa>";
}else{
	$leyenda_microempresa="";	
}

$encabezado = '<TipoIDInformante>R</TipoIDInformante><IdInformante>'.$ruc_empresa.'</IdInformante>
<razonSocial>'.$razon_social.'</razonSocial><Anio>'.$anio.'</Anio><Mes>'.$mes.'</Mes>'.$leyenda_microempresa.'<numEstabRuc>'.$total_sucursales.'</numEstabRuc>
<totalVentas>0.00</totalVentas><codigoOperativo>IVA</codigoOperativo>';
	return $encabezado;
	}
	
function depurar_nombre($string, $force_lowercase = false, $anal = false){
	$strip = array("~", "`", "!", "#", "$", "%", "^", "&", "*", "=", "+", "[", "{", "]",
                       "}", "|", ";", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                       "â€”", "â€“", "<", ">", "?", ".", "-");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
}
	
	
function compras($ruc_empresa, $mes, $anio, $con, $microempresa){
	if ($microempresa=="1"){
			if ($mes=="06"){
				$desde=$anio."/01/01";
				$hasta=$anio."/06/30";
			}
			if ($mes=="12"){
				$desde=$anio."/07/01";
				$hasta=$anio."/12/31";
			}
			$condicion_microempresa="and enc_com.fecha_compra between '" . date("Y/m/d", strtotime($desde)) . "' and '" . date("Y/m/d", strtotime($hasta)) . "'";
		}else{
			$condicion_microempresa="and month(enc_com.fecha_compra)='".$mes."' and year(enc_com.fecha_compra)='".$anio."'";	
		}
	$detalle_compras = array();
	$datos_compras=mysqli_query($con,"select distinct sus_tri.codigo_sustento as codigo_sustento, pro.tipo_id_proveedor as tipo_id_proveedor, pro.ruc_proveedor as ruc_proveedor,
	enc_com.id_proveedor as id_proveedor, com_aut.codigo_comprobante as codigo_comprobante, pro.relacionado as parte_relacionada, enc_com.fecha_compra as fecha_compra, enc_com.numero_documento as numero_documento, 
	enc_com.aut_sri as aut_sri, enc_com.codigo_documento as codigo_documento, enc_com.cod_doc_mod as docModificado, enc_com.factura_aplica_nc_nd as factura_aplica_nc_nd  
	from encabezado_compra as enc_com INNER JOIN cuerpo_compra as cue_com ON enc_com.codigo_documento=cue_com.codigo_documento 
	INNER JOIN sustento_tributario as sus_tri ON sus_tri.id_sustento=enc_com.id_sustento 
	INNER JOIN proveedores as pro ON pro.id_proveedor=enc_com.id_proveedor
	INNER JOIN comprobantes_autorizados as com_aut ON com_aut.id_comprobante=enc_com.id_comprobante 
	WHERE mid(enc_com.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' $condicion_microempresa "); 
	$cuenta_compras=mysqli_num_rows($datos_compras);
	if ($cuenta_compras>0){
	while ($row_compras=mysqli_fetch_array($datos_compras)){
	$codSustento=$row_compras['codigo_sustento'];
	$idProv=$row_compras['ruc_proveedor'];
	$id_proveedor=$row_compras['id_proveedor'];
	$tipo_id_proveedor=$row_compras['tipo_id_proveedor'];
	switch ($tipo_id_proveedor) {
				case "04":
					$tpIdProv="01";
					break;
				case "05":
					$tpIdProv="02";
					break;
				case "06":
					$tpIdProv="03";
					break;
				case "08":
					$tpIdProv="03";
					break;
					}
	$tipoComprobante=$row_compras['codigo_comprobante'];
	if ($tipoComprobante=="04" || $tipoComprobante=="05"){
		$docModificado=$row_compras['docModificado'];
		$estabModificado=substr($row_compras['factura_aplica_nc_nd'],0,3);
		$ptoEmiModificado=substr($row_compras['factura_aplica_nc_nd'],4,3);
		$secModificado=str_pad(substr($row_compras['factura_aplica_nc_nd'],8,9),9,"000000000",STR_PAD_LEFT);
		$dato_modificado=mysqli_query($con,"select * from encabezado_compra WHERE numero_documento = '".$row_compras['factura_aplica_nc_nd']."'");
		$row_modificado=mysqli_fetch_array($dato_modificado);
		$autModificado=($row_modificado['aut_sri'])==""?"000":$row_modificado['aut_sri'];
	$documento_modificado = '
	<docModificado>'.$docModificado.'</docModificado>
	<estabModificado>'.$estabModificado.'</estabModificado>
	<ptoEmiModificado>'.$ptoEmiModificado.'</ptoEmiModificado>
	<secModificado>'.$secModificado.'</secModificado>
	<autModificado>'.$autModificado.'</autModificado>';
	}else{
	$documento_modificado ="";
	}
	
	$parteRel=($row_compras['parte_relacionada']=="2")?"SI":"NO";
	$fechaRegistro = date('d/m/Y', strtotime($row_compras['fecha_compra']));
	$establecimiento=substr($row_compras['numero_documento'],0,3);
	$puntoEmision=substr($row_compras['numero_documento'],4,3);
	$secuencial=str_pad(substr($row_compras['numero_documento'],8,9),9,"000000000",STR_PAD_LEFT);
	$autorizacion=$row_compras['aut_sri'];
	$codigo_documento=$row_compras['codigo_documento'];
	$numero_documento_retenido = $row_compras['numero_documento'];
	
	$dato_baseNoGraIva=mysqli_query($con,"select sum(subtotal) as baseNoGraIva from cuerpo_compra WHERE impuesto = '2' and det_impuesto = '6' and codigo_documento = '".$codigo_documento."'");
	$row_baseNoGraIva=mysqli_fetch_array($dato_baseNoGraIva);
	$baseNoGraIva=number_format($row_baseNoGraIva['baseNoGraIva'],2,'.','');
	
	$dato_baseice=mysqli_query($con,"select sum(subtotal) as baseIce from cuerpo_compra WHERE impuesto = '3' and codigo_documento = '".$codigo_documento."'");
	$row_baseIce=mysqli_fetch_array($dato_baseice);
	$baseIce=number_format($row_baseIce['baseIce'],2,'.','');
	
	$dato_baseImponible=mysqli_query($con,"select sum(subtotal) as baseImponible from cuerpo_compra WHERE impuesto = '2' and det_impuesto = '0' and codigo_documento = '".$codigo_documento."'");
	$row_baseImponible=mysqli_fetch_array($dato_baseImponible);
	$baseImponible=number_format($row_baseImponible['baseImponible'],2,'.','');
	
	$dato_baseImpGrav=mysqli_query($con,"select sum(subtotal) as baseImpGrav from cuerpo_compra WHERE impuesto = '2' and det_impuesto = '2' and codigo_documento = '".$codigo_documento."'");
	$row_baseImpGrav=mysqli_fetch_array($dato_baseImpGrav);
	$baseImpGrav=number_format($row_baseImpGrav['baseImpGrav'],2,'.','');
	
	$dato_montoIva=mysqli_query($con,"select * from tarifa_iva WHERE codigo = '2' ");
	$row_montoIva=mysqli_fetch_array($dato_montoIva);
	$montoIva=number_format($baseImpGrav * ($row_montoIva['porcentaje_iva']/100),2,'.','');
	
	$dato_baseImpExe=mysqli_query($con,"select sum(subtotal) as baseImpExe from cuerpo_compra WHERE impuesto = '2' and det_impuesto = '7' and codigo_documento = '".$codigo_documento."'");
	$row_baseImpExe=mysqli_fetch_array($dato_baseImpExe);
	$baseImpExe=number_format($row_baseImpExe['baseImpExe'],2,'.','');
	
	$total_bases_no_iva = number_format(($baseNoGraIva + $baseImpExe+ $baseIce),2,'.','');
	
	$dato_ret_iva10=mysqli_query($con,"select sum(valor_retenido) as total_ret10 from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and enc_ret.tipo_comprobante='".$tipoComprobante."' and cue_ret.impuesto = 'IVA' and cue_ret.porcentaje_retencion='10'");
	$row_ret_iva10=mysqli_fetch_array($dato_ret_iva10);
	$valRetBien10=number_format($row_ret_iva10['total_ret10'],2,'.','');
	
	$dato_valRetServ20=mysqli_query($con,"select sum(valor_retenido) as total_ret20 from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and enc_ret.tipo_comprobante='".$tipoComprobante."' and cue_ret.impuesto = 'IVA' and cue_ret.porcentaje_retencion='20'");
	$row_valRetServ20=mysqli_fetch_array($dato_valRetServ20);
	$valRetServ20=number_format($row_valRetServ20['total_ret20'],2,'.','');
	
	$dato_valorRetBienes=mysqli_query($con,"select sum(valor_retenido) as total_ret30 from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and enc_ret.tipo_comprobante='".$tipoComprobante."' and cue_ret.impuesto = 'IVA' and cue_ret.porcentaje_retencion='30'");
	$row_valorRetBienes=mysqli_fetch_array($dato_valorRetBienes);
	$valorRetBienes=number_format($row_valorRetBienes['total_ret30'],2,'.','');
	
	$dato_valRetServ50=mysqli_query($con,"select sum(valor_retenido) as total_ret50 from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and enc_ret.tipo_comprobante='".$tipoComprobante."' and cue_ret.impuesto = 'IVA' and cue_ret.porcentaje_retencion='50'");
	$row_valRetServ50=mysqli_fetch_array($dato_valRetServ50);
	$valRetServ50=number_format($row_valRetServ50['total_ret50'],2,'.','');
	
	$dato_valorRetServicios=mysqli_query($con,"select sum(valor_retenido) as total_ret70 from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and enc_ret.tipo_comprobante='".$tipoComprobante."' and cue_ret.impuesto = 'IVA' and cue_ret.porcentaje_retencion='70'");
	$row_valorRetServicios=mysqli_fetch_array($dato_valorRetServicios);
	$valorRetServicios=number_format($row_valorRetServicios['total_ret70'],2,'.','');
	
	$dato_valRetServ100=mysqli_query($con,"select sum(valor_retenido) as total_ret100 from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and enc_ret.tipo_comprobante='".$tipoComprobante."' and cue_ret.impuesto = 'IVA' and cue_ret.porcentaje_retencion='100'");
	$row_valRetServ100=mysqli_fetch_array($dato_valRetServ100);
	$valRetServ100=number_format($row_valRetServ100['total_ret100'],2,'.','');
	
	//para ver si el valor de de pago es mayor a 1000 
	$dato_total_pago=mysqli_query($con,"select sum(total_pago) as total_pago from formas_pago_compras WHERE codigo_documento='".$codigo_documento."' ");
	$row_total_pago=mysqli_fetch_array($dato_total_pago);
	$total_pago=number_format($row_total_pago['total_pago'],2,'.','');
	
	//para ver si hay retenciones	
	$suma_retenciones=mysqli_query($con,"select sum(valor_retenido) as total_ret from encabezado_retencion as enc_ret 
	INNER JOIN cuerpo_retencion as cue_ret ON 
	enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion 
	WHERE enc_ret.estado_sri='AUTORIZADO' and enc_ret.ruc_empresa='".$ruc_empresa."' 
	and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' 
	and enc_ret.tipo_comprobante='".$tipoComprobante."' and enc_ret.id_proveedor='".$id_proveedor."' and cue_ret.impuesto = 'RENTA' group by cue_ret.impuesto ");
	$row_sum_retenciones=mysqli_fetch_array($suma_retenciones);
	$total_retenciones=empty($row_sum_retenciones['total_ret'])?0:number_format($row_sum_retenciones['total_ret'],2,'.','');

	if ($total_retenciones>0){
	
	$info_retencion="";
	//$dato_retenciones=mysqli_query($con,"select * from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.estado_sri='AUTORIZADO' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and cue_ret.impuesto = 'RENTA' ");
	$dato_retenciones=mysqli_query($con,"select * from encabezado_retencion WHERE ruc_empresa='".$ruc_empresa."' and estado_sri='AUTORIZADO' and numero_comprobante ='".$numero_documento_retenido."' and id_proveedor='".$id_proveedor."' ");
		while ($row_retenciones=mysqli_fetch_array($dato_retenciones)){
			$serie_ret=$row_retenciones['serie_retencion'];
			$secuencial_ret=$row_retenciones['secuencial_retencion'];
			$estabRetencion1=substr($row_retenciones['serie_retencion'],0,3);
			$ptoEmiRetencion1=substr($row_retenciones['serie_retencion'],4,3);
			$secRetencion1=str_pad($row_retenciones['secuencial_retencion'],9,"000000000",STR_PAD_LEFT);
			$autRetencion1=strlen($row_retenciones['aut_sri'])!=""?$row_retenciones['aut_sri']:"1234567890";
			$fechaEmiRet1=date('d/m/Y', strtotime($row_retenciones['fecha_emision']));
			
				$detalleAir="";
				$dato_detalle_retenciones=mysqli_query($con,"select * from cuerpo_retencion WHERE ruc_empresa='".$ruc_empresa."' and serie_retencion='".$serie_ret."' and secuencial_retencion='".$secuencial_ret."' and impuesto = 'RENTA' ");
				while ($row_detalle_retenciones=mysqli_fetch_array($dato_detalle_retenciones)){
				$codRetAir=$row_detalle_retenciones['codigo_impuesto'];
				$baseImpAir=number_format($row_detalle_retenciones['base_imponible'],2,'.','');
				$porcentajeAir=number_format($row_detalle_retenciones['porcentaje_retencion'],2,'.','');
				$valRetAir=number_format($row_detalle_retenciones['valor_retenido'],2,'.','');
				$detalleAir .="<detalleAir><codRetAir>".$codRetAir."</codRetAir>
				<baseImpAir>".$baseImpAir."</baseImpAir><porcentajeAir>".$porcentajeAir."</porcentajeAir>
				<valRetAir>".$valRetAir."</valRetAir></detalleAir>";
				}
			$detalleAir=$detalleAir;
			
			$info_retencion .="<estabRetencion1>".$estabRetencion1."</estabRetencion1>
			<ptoEmiRetencion1>".$ptoEmiRetencion1."</ptoEmiRetencion1>
			<secRetencion1>".$secRetencion1."</secRetencion1>
			<autRetencion1>".$autRetencion1."</autRetencion1>
			<fechaEmiRet1>".$fechaEmiRet1."</fechaEmiRet1>";
		}
		$air="<air>".$detalleAir."</air>".$info_retencion;
	}else{
		$suma_retenciones=mysqli_query($con,"select sum(cue_ret.base_imponible) as subtotal_retencion from encabezado_retencion as enc_ret INNER JOIN cuerpo_retencion as cue_ret ON enc_ret.serie_retencion=cue_ret.serie_retencion and enc_ret.secuencial_retencion=cue_ret.secuencial_retencion WHERE enc_ret.ruc_empresa='".$ruc_empresa."' and cue_ret.ruc_empresa='".$ruc_empresa."' and enc_ret.estado_sri='AUTORIZADO' and enc_ret.numero_comprobante ='".$numero_documento_retenido."' and enc_ret.id_proveedor='".$id_proveedor."' and cue_ret.impuesto = 'RENTA' ");
		$row_retenciones=mysqli_fetch_array($suma_retenciones);
		
		$base_retenciones=number_format($row_retenciones['subtotal_retencion'],2,'.','');
		
		$subtotal_no_ret=mysqli_query($con,"select sum(subtotal) as subtotal from cuerpo_compra WHERE codigo_documento = '".$codigo_documento."'");
		$row_subtotal=mysqli_fetch_array($subtotal_no_ret);
		$subtotal=number_format($row_subtotal['subtotal'],2,'.','');
		$suma_total_bases=number_format($subtotal-$base_retenciones,2,'.','');
		
		if($suma_total_bases>0){
		$detalleAir ="<air><detalleAir><codRetAir>332</codRetAir>
		<baseImpAir>".$suma_total_bases."</baseImpAir><porcentajeAir>0</porcentajeAir>
		<valRetAir>0.00</valRetAir></detalleAir></air>";
		}else{
			$detalleAir ="";
		}
		
		if ($tipoComprobante=="04" || $tipoComprobante=="05"){
		$air="";
		}else{
		$air=$detalleAir;
		}
	}

	if ($total_pago>=1000){
	$pagos="";
	$dato_formasDePago=mysqli_query($con,"select * from formas_pago_compras WHERE codigo_documento='".$codigo_documento."' ");
		while ($row_formasDePago=mysqli_fetch_array($dato_formasDePago)){
		$pagos .= "<formaPago>".$row_formasDePago['forma_pago']."</formaPago>";
		}
		$formaPago="<formasDePago>".$pagos."</formasDePago>";
	}else{
		$formaPago="";
	}
	
$detalle_compras[] ='	
<detalleCompras>
<codSustento>'.$codSustento.'</codSustento>
<tpIdProv>'.$tpIdProv.'</tpIdProv>
<idProv>'.$idProv.'</idProv>
<tipoComprobante>'.$tipoComprobante.'</tipoComprobante>
<parteRel>'.$parteRel.'</parteRel>
<fechaRegistro>'.$fechaRegistro.'</fechaRegistro>
<establecimiento>'.$establecimiento.'</establecimiento>
<puntoEmision>'.$puntoEmision.'</puntoEmision>
<secuencial>'.$secuencial.'</secuencial>
<fechaEmision>'.$fechaRegistro.'</fechaEmision>
<autorizacion>'.$autorizacion.'</autorizacion>
<baseNoGraIva>'.$total_bases_no_iva.'</baseNoGraIva>
<baseImponible>'.$baseImponible.'</baseImponible>
<baseImpGrav>'.$baseImpGrav.'</baseImpGrav>
<baseImpExe>0.00</baseImpExe>
<montoIce>0.00</montoIce>
<montoIva>'.$montoIva.'</montoIva>
<valRetBien10>'.$valRetBien10.'</valRetBien10>
<valRetServ20>'.$valRetServ20.'</valRetServ20>
<valorRetBienes>'.$valorRetBienes.'</valorRetBienes>
<valRetServ50>'.$valRetServ50.'</valRetServ50>
<valorRetServicios>'.$valorRetServicios.'</valorRetServicios>
<valRetServ100>'.$valRetServ100.'</valRetServ100>
<totbasesImpReemb>0.00</totbasesImpReemb>
<pagoExterior>
<pagoLocExt>01</pagoLocExt>
<paisEfecPago>NA</paisEfecPago>
<aplicConvDobTrib>NA</aplicConvDobTrib>
<pagExtSujRetNorLeg>NA</pagExtSujRetNorLeg>
</pagoExterior>
'.$documento_modificado.'
'.$formaPago.'
'.$air.'
</detalleCompras>';
}
$detalle_final="";
foreach ($detalle_compras as $detalle ){
	$detalle_final .= $detalle;
}

	return "<compras>".$detalle_final."</compras>";
}else{
	return "";
}
}

	
			if (isset($errors)){
			
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Error!</strong> 
					<?php
						foreach ($errors as $error) {
								echo $error;
							}
						?>
			</div>
			<?php
			}
			if (isset($messages)){
				
				?>
				<div class="alert alert-success" role="alert">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>¡Bien hecho!</strong>
						<?php
							foreach ($messages as $message) {
									echo $message;
								}
							?>
				</div>
				<?php
			}
			
?>
