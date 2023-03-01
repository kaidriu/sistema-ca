<?php
include("../validadores/generador_codigo_unico.php");
include("../clases/control_caracteres_especiales.php");
include("consulta.comprobantes.sri.class.php");
require_once("../helpers/helpers.php");

class rides_sri{

public function lee_clave_acceso($clave, $ruc_empresa, $id_usuario, $con){
	$object_xml = $this->lee_ride($clave);
	ini_set('date.timezone','America/Guayaquil');
	
	if (is_object($object_xml)) {
		$tipo_documento = $object_xml->infoTributaria->codDoc;
		$serie = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi;
		$secuencial = $object_xml->infoTributaria->secuencial;
		$aut_sri = $object_xml->infoTributaria->claveAcceso;

		
		if ($tipo_documento=="03"){ //para liquidacion
		$ruc_comprador = substr($object_xml->infoTributaria->ruc,0,10)."001";	
		$fecha_emision = $object_xml->infoLiquidacionCompra->fechaEmision;
		$numero_lcs = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
		$ruc_proveedor = $object_xml->infoLiquidacionCompra->identificacionProveedor;
		$nombre_proveedor = $object_xml->infoLiquidacionCompra->razonSocialProveedor;

			//para registrar liquidacion de compras que hayan sido realizadas en otro sistema
			//y que vamos a registrar en nuestras liquidaciones de compras
			if (substr($ruc_comprador,0,12)==substr($ruc_empresa,0,12)){//para ver si quien nos emite la lc nos emite a esta empresa
				$id_proveedor =	$this->proveedor_cliente("proveedor", $con, $ruc_empresa, $object_xml, $ruc_proveedor);
					$registros_liquidacion = comprueba_registrados($con, $ruc_empresa, 'liquidacion', $serie, $secuencial, $id_proveedor);
					if ($registros_liquidacion==0){
						$guarda_lc = $this->guarda_lcs($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor);				
						return "La LCS ".$numero_lcs." de ".strtoupper($nombre_proveedor)." fue registrada con éxito en liquidaciones de compras y servicios."."</br>";
					}else{
						echo "La LCS ".$numero_lcs." de ".strtoupper($nombre_proveedor)." fue registrada anteriormente en liquidaciones de compras y servicios."."</br>";
					}
					
					//para registrar como compra
					$id_proveedor =	$this->proveedor_cliente("proveedor", $con, $ruc_empresa, $object_xml, $ruc_proveedor);
					$registros_compras = comprueba_registrados($con, $ruc_empresa, 'factura_compra', $serie, $secuencial, $id_proveedor);
					if ($registros_compras==0){
						$guarda_lc = $this->guarda_lcs_compras($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor);				
						return "La LCS ".$numero_lcs." de ".strtoupper($nombre_proveedor)." fue registrada con éxito en compras."."</br>";
					}else{					
						return "La LCS ".$numero_lcs." de ".strtoupper($nombre_proveedor)." fue registrada anteriormente en compras y servicios."."</br>";
					}
			}else{
				return "La LCS ".$numero_lcs." de ".strtoupper($nombre_proveedor)." fue rechazada, está emitida a otro contribuyente."."</br>";
			}
		}
		
		if ($tipo_documento=="01"){ //para factura 01
		$ruc_comprador = substr($object_xml->infoFactura->identificacionComprador,0,10)."001";	
		$fecha_emision = $object_xml->infoFactura->fechaEmision;
		$numero_factura = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
		$total_factura = $object_xml->infoFactura->importeTotal;
		$razon_social = $object_xml->infoFactura->razonSocialComprador;
		$nombre_comercial = $object_xml->infoTributaria->nombreComercial;
		$ruc_proveedor = $object_xml->infoTributaria->ruc;
		$nombre_proveedor = $object_xml->infoTributaria->razonSocial;

		if (substr($ruc_comprador,0,12)==substr($ruc_empresa,0,12)){//para ver si quien nos emite la factura nos emite a esta empresa
					$id_proveedor =	$this->proveedor_cliente("proveedor", $con, $ruc_empresa, $object_xml, $ruc_proveedor);
					$registros = comprueba_registrados($con, $ruc_empresa, 'factura_compra', $serie, $secuencial, $id_proveedor);
					if ($registros==0){
					$guarda_factura_compra = $this->guarda_factura_compra($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor);				
					return "La factura de compra ".$numero_factura." de ".strtoupper($nombre_proveedor)." fue registrada con éxito."."</br>";
					}else{
					return "La factura de compra ".$numero_factura." de ".strtoupper($nombre_proveedor)." ha sido registrada anteriormente."."</br>";
					}
			}else if(substr($ruc_proveedor,0,12)==substr($ruc_empresa,0,12)){
					$id_cliente =	$this->proveedor_cliente("cliente", $con, $ruc_empresa, $object_xml, $ruc_comprador);
					$registros = comprueba_registrados($con, $ruc_empresa, 'factura_venta', $serie, $secuencial, $id_cliente);
					if ($registros==0){
					$guarda_factura_venta = $this->guarda_factura_venta($con, $ruc_empresa, $object_xml, $id_usuario, $id_cliente);				
					return "La factura de venta ".$numero_factura." de ".strtoupper($razon_social)." fue registrada con éxito."."</br>";
					}else{
					return "La factura de venta ".$numero_factura." de ".strtoupper($razon_social)." ha sido registrada anteriormente."."</br>";	
					}
			
			}else if (substr($ruc_comprador,0,12)!=substr($ruc_empresa,0,12)){
				return "La factura ".$numero_factura." de ".strtoupper($nombre_proveedor)." fue rechazada, está emitida a otro contribuyente."."</br>";
			}else if (substr($ruc_proveedor,0,12)!=substr($ruc_empresa,0,12)){
				return "La factura ".$numero_factura." de ".strtoupper($razon_social)." fue rechazada, está emitida a otro contribuyente."."</br>";
			}
		return false;
		}
		
		//nota de credito 04 en compras
		if ($tipo_documento=="04"){ 
		$ruc_comprador = substr($object_xml->infoNotaCredito->identificacionComprador,0,10)."001";	
		$fecha_emision = $object_xml->infoNotaCredito->fechaEmision;
		$total_nc = $object_xml->infoNotaCredito->valorModificacion;
		$numero_nc = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
		$numero_documento_modificado = $object_xml->infoNotaCredito->numDocModificado;
		$razon_social = $object_xml->infoTributaria->razonSocial;
		$nombre_comercial = $object_xml->infoTributaria->nombreComercial;
		$ruc_proveedor = $object_xml->infoTributaria->ruc;
		$nombre_proveedor = $object_xml->infoTributaria->razonSocial;
	
		
		if (substr($ruc_comprador,0,12)==substr($ruc_empresa,0,12)){//para ver si quien nos emite la factura nos emite a esta empresa
			$id_proveedor =	$this->proveedor_cliente("proveedor", $con, $ruc_empresa, $object_xml, $ruc_proveedor);
				$registros = comprueba_registrados($con, $ruc_empresa, 'factura_compra', $serie, $secuencial, $id_proveedor);
				if ($registros==0){
				$guarda_nc = $this->guarda_nc($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor);				
				return "La NC ".$numero_nc." de ".strtoupper($nombre_proveedor)." fue registrada con éxito."."</br>";
				}else{
				return "La NC ".$numero_nc." de ".strtoupper($nombre_proveedor)." fue registrada anteriormente."."</br>";	
				}
			}else{
			return "La NC ".$numero_nc." de ".strtoupper($nombre_proveedor)." fue rechazada, está emitida a otro contribuyente."."</br>";
			}
			return false;
		}
		
		//nota de debito 05
		if ($tipo_documento=="05"){ 
		$ruc_comprador = substr($object_xml->infoNotaDebito->identificacionComprador,0,10)."001";	
		$fecha_emision = $object_xml->infoNotaDebito->fechaEmision;
		$total_nc = $object_xml->infoNotaDebito->valorTotal;
		$numero_nc = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
		$numero_documento_modificado = $object_xml->infoNotaDebito->numDocModificado;
		$razon_social = $object_xml->infoTributaria->razonSocial;
		$nombre_comercial = $object_xml->infoTributaria->nombreComercial;
		$ruc_proveedor = $object_xml->infoTributaria->ruc;
		$nombre_proveedor = $object_xml->infoTributaria->razonSocial;
		
		
		if (substr($ruc_comprador,0,12)==substr($ruc_empresa,0,12)){//para ver si quien nos emite la nd nos emite a esta empresa
			$id_proveedor =	$this->proveedor_cliente("proveedor", $con, $ruc_empresa, $object_xml, $ruc_proveedor);
				$registros = comprueba_registrados($con, $ruc_empresa, 'factura_compra', $serie, $secuencial, $id_proveedor);
				if ($registros==0){
				$guarda_nd = $this->guarda_nd($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor);				
				return "La ND ".$numero_nc." de ".strtoupper($nombre_proveedor)." fue registrada con éxito."."</br>";
				}else{
				return "La ND ".$numero_nc." de ".strtoupper($nombre_proveedor)." fue registrada anteriormente."."</br>";	
				}
			}else{
			return "La ND ".$numero_nc." de ".strtoupper($nombre_proveedor)." fue rechazada, está emitida a otro contribuyente."."</br>";
			}
			return false;
		}
		//guia de remision 06
			
		//si es retencion 07
		if ($tipo_documento=="07"){ 
		$ruc_retenido = substr($object_xml->infoCompRetencion->identificacionSujetoRetenido,0,10)."001";	
		$numero_retencion = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
		$ruc_cliente = $object_xml->infoTributaria->ruc;
		$nombre_cliente = $object_xml->infoTributaria->razonSocial;
		$nombre_proveedor = $object_xml->infoCompRetencion->razonSocialSujetoRetenido;

			if (substr($ruc_retenido,0,12)==substr($ruc_empresa,0,12)){//para ver si quien nos emite la retencion nos emite a esta empresa
						$id_cliente =	$this->proveedor_cliente("cliente", $con, $ruc_empresa, $object_xml, $ruc_cliente);
						$registros = comprueba_registrados($con, $ruc_empresa, 'retencion_venta', $serie, $secuencial, $id_cliente);
						if ($registros==0){
						$guarda_retencion_venta = $this->guarda_retencion_venta($con, $ruc_empresa, $object_xml, $id_usuario, $id_cliente);				
						return "La retención de venta ".$numero_retencion." de ".strtoupper($nombre_cliente)." fue registrada con éxito."."</br>";
						}else{
						return "La retención de venta ".$numero_retencion." de ".strtoupper($nombre_cliente)." fue registrada anteriormente."."</br>";	
						}
				}else if (substr($ruc_cliente,0,12)==substr($ruc_empresa,0,12)){
						$id_proveedor =	$this->proveedor_cliente("proveedor", $con, $ruc_empresa, $object_xml, $ruc_retenido);
						$registros = comprueba_registrados($con, $ruc_empresa, 'retencion_compra', $serie, $secuencial, $id_proveedor);
						if ($registros==0){
						$guarda_retencion_compra = $this->guarda_retencion_compra($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor);				
						return "La retención de compra ".$numero_retencion." de ".strtoupper($nombre_proveedor)." fue registrada con éxito."."</br>";
						}else{
						return "La retención de compra ".$numero_retencion." de ".strtoupper($nombre_proveedor)." fue registrada anteriormente."."</br>";	
						}
				}else if (substr($ruc_retenido,0,12)!=substr($ruc_empresa,0,12)){
						return "La retención ".$numero_retencion." de ".strtoupper($nombre_cliente)." fue rechazada, está emitido a otro contribuyente."."</br>";
				}else if (substr($ruc_cliente,0,12)!=substr($ruc_empresa,0,12)){
						return "La retención ".$numero_retencion." de ".strtoupper($nombre_proveedor)." fue rechazada, está emitido a otro contribuyente."."</br>";
				}
				return false;
		}
		
	}else{
		return "No hay respuesta del SRI. Documentos con mayor a 90 días de emisión no son posible de registrar."."</br>";
	}
}


public function guarda_lcs($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoLiquidacionCompra->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoLiquidacionCompra->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoLiquidacionCompra->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$numero_lcs = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
	$serie_lcs = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi;
	$secuencial_lcs = $object_xml->infoTributaria->secuencial;
	$codigo_documento=codigo_unico(20);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$total_lcs = $object_xml->infoLiquidacionCompra->importeTotal;
	$nombre_proveedor = $object_xml->infoLiquidacionCompra->razonSocialProveedor;
	$version_xml = $object_xml['version'];

	//guardar encabezado
	$sql_guarda_encabezado=mysqli_query($con,"INSERT INTO encabezado_liquidacion VALUES (null, '".$ruc_empresa."','".$fecha_emision."','".$serie_lcs."', '".$secuencial_lcs."','".$id_proveedor."','".$fecha_registro."','AUTORIZADO','".$total_lcs."','".$id_usuario."','2','0','".$aut_sri."','ENVIADO','".$codigo_documento."')");

	//if ($version_xml=='1.1.0'){
		if (isset($object_xml->detalles)) {
			foreach ($object_xml->detalles->detalle as $detalle) {
			  $codigo_detalle= $detalle->codigoPrincipal;
			  //$descripcion_detalle= $sanitize->string_sanitize($detalle->descripcion,$force_lowercase = false, $anal = false);
			  $descripcion_detalle= strClean($detalle->descripcion);
			  $cantidad_detalle = $detalle->cantidad;
			  $precio_detalle= $detalle->precioUnitario;
			  $descuento_detalle= $detalle->descuento;
			  		  
			  foreach ($detalle->impuestos->impuesto as $detalle_impuestos ){
				 $codigo_impuesto= $detalle_impuestos->codigo;
				 $codigo_porcentaje= $detalle_impuestos->codigoPorcentaje;
				 $tarifa= $detalle_impuestos->tarifa;
				 $base_imponible= $detalle_impuestos->baseImponible;
				//guardar detalle de lcs
				$sql_guarda_detalle=mysqli_query($con,"INSERT INTO cuerpo_liquidacion VALUES 
				(null,'".$ruc_empresa."', '".$serie_lcs."','".$secuencial_lcs."',
				'".$cantidad_detalle."','".$precio_detalle."','".$base_imponible."',
				'".$tarifa."','".$descuento_detalle."','".$codigo_detalle."',
				'".$descripcion_detalle."','".$codigo_documento."')");
			  }
		  }
	  }
	//}

	//guardar info adicional de factura
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   //$nombre_detalle= $sanitize->string_sanitize(${"infoAd" . $i},$force_lowercase = false, $anal = false);
		   //$valor_detalle= $sanitize->string_sanitize(${"valueInfoAd" . $i},$force_lowercase = false, $anal = false);
		   $nombre_detalle= strClean(${"infoAd" . $i});
		   $valor_detalle= strClean(${"valueInfoAd" . $i});
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional= mysqli_query($con,"INSERT INTO detalle_adicional_liquidacion VALUES (null,'".$ruc_empresa."', '".$serie_lcs."','".$secuencial_lcs."','".$nombre_detalle."','".$valor_detalle."','".$codigo_documento."')");
		}
	}	  
}


public function guarda_lcs_compras($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoLiquidacionCompra->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoLiquidacionCompra->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoLiquidacionCompra->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$numero_lcs = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
	$secuencial_lcs = $object_xml->infoTributaria->secuencial;
	$codigo_documento=codigo_unico(20);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$total_lcs = $object_xml->infoLiquidacionCompra->importeTotal;
	$nombre_proveedor = $object_xml->infoLiquidacionCompra->razonSocialProveedor;
	$version_xml = $object_xml['version'];
	$tipoIdentificacionComprador = $object_xml->infoLiquidacionCompra->tipoIdentificacionComprador;

	//guardar encabezado
	$sql_guarda_encabezado=mysqli_query($con,"INSERT INTO encabezado_compra VALUES (null,'".$fecha_emision."','".$ruc_empresa."','".$numero_lcs."', '".$codigo_documento."','".$id_proveedor."','".$id_documento."','1','".$aut_sri."','".$fecha_emision."','".$secuencial_lcs."','".$secuencial_lcs."','".$fecha_registro."', '".$id_usuario."', '".$total_lcs."','','0','ELECTRÓNICA','".$tipoIdentificacionComprador."','0','0','0')");

	//if ($version_xml=='1.1.0'){
		if (isset($object_xml->detalles)) {
			foreach ($object_xml->detalles->detalle as $detalle) {
			  $codigo_detalle= $detalle->codigoPrincipal;
			  $descripcion_detalle= strClean($detalle->descripcion);
			  $cantidad_detalle = $detalle->cantidad;
			  $precio_detalle= $detalle->precioUnitario;
			  $descuento_detalle= $detalle->descuento;
			  		  
			  foreach ($detalle->impuestos->impuesto as $detalle_impuestos ){
				 $codigo_impuesto= $detalle_impuestos->codigo;
				 $codigo_porcentaje= $detalle_impuestos->codigoPorcentaje;
				 $tarifa= $detalle_impuestos->tarifa;
				 $base_imponible= $detalle_impuestos->baseImponible;
				//guardar detalle de lcs
				$sql_guarda_detalle=mysqli_query($con,"INSERT INTO cuerpo_compra VALUES 
				(null,'".$ruc_empresa."', '".$codigo_documento."','".$codigo_detalle."',
				'".$descripcion_detalle."','".$cantidad_detalle."','".$precio_detalle."',
				'".$descuento_detalle."','".$codigo_impuesto."','".$codigo_porcentaje."',
				'".$base_imponible."',0)");
			  }
		  }
	  }
	//}

	//guardar info adicional de factura
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= $sanitize->string_sanitize(${"infoAd" . $i},$force_lowercase = false, $anal = false);
		   $valor_detalle= $sanitize->string_sanitize(${"valueInfoAd" . $i},$force_lowercase = false, $anal = false);
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional= mysqli_query($con,"INSERT INTO detalle_adicional_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$nombre_detalle."','".$valor_detalle."')");
		}
	}
//para guardar una forma de pago de la liquidacion
				$sql_guarda_pago=mysqli_query($con,"INSERT INTO formas_pago_compras VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','20','".$total_lcs."','1','dias')");	
}

public function guarda_factura_venta($con, $ruc_empresa, $object_xml, $id_usuario, $id_cliente){
			
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoFactura->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoFactura->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoFactura->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$serie_factura = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi;
	$secuencial_factura = intval($object_xml->infoTributaria->secuencial);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$total_factura = $object_xml->infoFactura->importeTotal;
	$propina = $object_xml->infoFactura->propina;
	$otros_val = isset($object_xml->otrosRubrosTerceros->rubro->total)?$object_xml->otrosRubrosTerceros->rubro->total:0;
	$version_xml = $object_xml['version'];
	$fecha_agregado=date("Y-m-d H:i:s");

	//guardar encabezado
	$sql_guarda_encabezado= mysqli_query($con,"INSERT INTO encabezado_factura VALUES (null,'".$ruc_empresa."','".$fecha_emision."','".$serie_factura."','".$secuencial_factura."', '".$id_cliente."','Cargada desde xml','','".$fecha_agregado."','ok','ELECTRÓNICA','AUTORIZADO','".$total_factura."','".$id_usuario."','2','0','".$aut_sri."','ENVIADO','".$propina."', '".$otros_val."')");
	
	//if ($version_xml=='1.1.0'){
		if (isset($object_xml->detalles)){
			foreach ($object_xml->detalles->detalle as $detalle) {
			  $codigo_detalle= $detalle->codigoPrincipal;
			  $nombre_producto= $detalle->descripcion;
			  $precio_producto= $detalle->precioUnitario;
			  $descripcion_detalle= strClean($detalle->descripcion);
			  $cantidad_detalle = $detalle->cantidad;
			  $precio_detalle= $detalle->precioUnitario;
			  $descuento_detalle= $detalle->descuento;
			  $subtotal= $detalle->precioTotalSinImpuesto;
			  		  
			  foreach ($detalle->impuestos->impuesto as $detalle_impuestos ){
				 $codigo_impuesto= $detalle_impuestos->codigo;
				 $impuesto_iva = $detalle_impuestos->codigo=="2"?"2":"0";
				 $impuesto_ice = $detalle_impuestos->codigo=="3"?"3":"0";
				 $codigo_porcentaje= $detalle_impuestos->codigoPorcentaje;
				 $tarifa= $detalle_impuestos->tarifa;
				 $base_imponible= $detalle_impuestos->baseImponible;
				 $busca_producto = mysqli_query($con, "SELECT * FROM productos_servicios WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and codigo_producto='".$codigo_detalle."'");
				  $row_producto=mysqli_fetch_array($busca_producto);
				  $id_producto=$row_producto['id'];
				  $tipo_producion=isset($row_producto['tipo_producion'])?$row_producto['tipo_producion']:"02";
				  if (!isset($id_producto)){
					$sql_guarda_producto= mysqli_query($con,"INSERT INTO productos_servicios VALUES (null,'".$ruc_empresa."','".$codigo_detalle."','".$nombre_producto."','cargado desde xml','".$precio_producto."','02', '".$impuesto_iva."','".$impuesto_ice."','0','".$fecha_agregado."','0','OK')");  
				  $busca_producto = mysqli_query($con, "SELECT * FROM productos_servicios WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and codigo_producto='".$codigo_detalle."'");
				  $row_producto=mysqli_fetch_array($busca_producto);
				  $id_producto=$row_producto['id'];
				  $tipo_producion=isset($row_producto['tipo_producion'])?$row_producto['tipo_producion']:"02";
				  $codigo_detalle=$row_producto['codigo_producto'];
				  }

				//guardar detalle de factura
				$sql_guarda_detalle= mysqli_query($con, "INSERT INTO cuerpo_factura VALUES 
				(null,'".$ruc_empresa."', '".$serie_factura."','".$secuencial_factura."',
				'".$id_producto."','".$cantidad_detalle."','".$precio_detalle."',
				'".$subtotal."','".$tipo_producion."','".$impuesto_iva."',
				'".$impuesto_ice."','0','".$descuento_detalle."','".$codigo_detalle."','".$nombre_producto."','0','0','0','0')");
				}
			  }
		  }
	  //}
	//}
	  
	  
	//guardar info adicional de factura
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= $sanitize->string_sanitize(${"infoAd" . $i},$force_lowercase = false, $anal = false);
		   $valor_detalle= $sanitize->string_sanitize(${"valueInfoAd" . $i},$force_lowercase = false, $anal = false);
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional=mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null,'".$ruc_empresa."', '".$serie_factura."','".$secuencial_factura."','".$nombre_detalle."','".$valor_detalle."')");
		}
	}
	//guardar formas de pago
	  if (isset($object_xml->infoFactura->pagos)) {
			foreach ($object_xml->infoFactura->pagos->pago as $pagos) {
			  $forma_pago= $pagos->formaPago;
			  $total_pago= $pagos->total;
			  $plazo_pago = $pagos->plazo;
			  $tiempo_pago = $pagos->unidadTiempo;
			//guardar detalle de forma de pago
			$sql_guarda_pago=mysqli_query($con, "INSERT INTO formas_pago_ventas VALUES (null,'".$ruc_empresa."', '".$serie_factura."','".$secuencial_factura."','".$forma_pago."','".$total_pago."')");
		  }
	  } 
}


public function guarda_factura_compra($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoFactura->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoFactura->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoFactura->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$numero_factura = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
	$secuencial_factura = $object_xml->infoTributaria->secuencial;
	$codigo_documento=codigo_unico(20);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$total_factura = $object_xml->infoFactura->importeTotal;
	$propina = $object_xml->infoFactura->propina;
	$otros_val = isset($object_xml->otrosRubrosTerceros->rubro->total)?$object_xml->otrosRubrosTerceros->rubro->total:0;
	$nombre_proveedor = $object_xml->infoTributaria->razonSocial;
	$tipoIdentificacionComprador = $object_xml->infoFactura->tipoIdentificacionComprador;
	
	$version_xml = $object_xml['version'];

	//guardar encabezado
	$sql_guarda_encabezado= mysqli_query($con,"INSERT INTO encabezado_compra VALUES (null,'".$fecha_emision."','".$ruc_empresa."','".$numero_factura."', '".$codigo_documento."','".$id_proveedor."','".$id_documento."','1','".$aut_sri."','".$fecha_emision."','".$secuencial_factura."','".$secuencial_factura."','".$fecha_registro."', '".$id_usuario."', '".$total_factura."','','0','ELECTRÓNICA','".$tipoIdentificacionComprador ."','".$propina."','".$otros_val."','0')");
	

		if (isset($object_xml->detalles)) {
			foreach ($object_xml->detalles->detalle as $detalle) {
			  $codigo_detalle= $detalle->codigoPrincipal;
			  $descripcion_detalle= strClean($detalle->descripcion);
			  $cantidad_detalle = $detalle->cantidad;
			  $precio_detalle= $detalle->precioUnitario;
			  $descuento_detalle= $detalle->descuento;
			  		  
			  foreach ($detalle->impuestos->impuesto as $detalle_impuestos ){
				 $codigo_impuesto= $detalle_impuestos->codigo;
				 $codigo_porcentaje= $detalle_impuestos->codigoPorcentaje;
				 $tarifa= $detalle_impuestos->tarifa;
				 $base_imponible= $detalle_impuestos->baseImponible;
				 $valor_impuesto= $detalle_impuestos->valor;
				//guardar detalle de factura
				if ($codigo_impuesto=="2"){
				$sql_guarda_detalle=mysqli_query($con,"INSERT INTO cuerpo_compra VALUES 
				(null,'".$ruc_empresa."', '".$codigo_documento."','".$codigo_detalle."',
				'".$descripcion_detalle."','".$cantidad_detalle."','".$precio_detalle."',
				'".$descuento_detalle."','".$codigo_impuesto."','".$codigo_porcentaje."',
				'".$base_imponible."',0)");
				}
				if ($codigo_impuesto=="3"){
				$sql_actualiza_encabezado= mysqli_query($con,"UPDATE encabezado_compra SET otros_val=otros_val + '".$valor_impuesto."' WHERE codigo_documento='".$codigo_documento."'");
				}
				
			 }
		  }
	  }
	//}
	  
	  
	//guardar info adicional de factura
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= $sanitize->string_sanitize(${"infoAd" . $i},$force_lowercase = false, $anal = false);
		   $valor_detalle= $sanitize->string_sanitize(${"valueInfoAd" . $i},$force_lowercase = false, $anal = false);
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional="INSERT INTO detalle_adicional_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$nombre_detalle."','".$valor_detalle."')";
				$query_guarda_detalle_adicional = mysqli_query($con,$sql_guarda_detalle_adicional);
				if (!$query_guarda_detalle_adicional){
				return "No se guardo la información adicional de la factura: ".$numero_factura." proveedor: ".$nombre_proveedor. " clave de accesso: ". $aut_sri ."</br>";		
				}
		}
	}
	//guardar formas de pago
	  if (isset($object_xml->infoFactura->pagos)) {
			foreach ($object_xml->infoFactura->pagos->pago as $pagos) {
			  $forma_pago= $pagos->formaPago;
			  $total_pago= $pagos->total;
			  $plazo_pago = $pagos->plazo;
			  $tiempo_pago = $pagos->unidadTiempo;
			//guardar detalle de forma de pago
			$sql_guarda_pago=mysqli_query($con,"INSERT INTO formas_pago_compras VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$forma_pago."','".$total_pago."','".$plazo_pago."','".$tiempo_pago."')");
		  }
	  } else{
		$forma_pago= "20";
		$total_pago= $total_factura;
		$plazo_pago = "1";
		$tiempo_pago = "Dias";
	  //guardar detalle de forma de pago
	  $sql_guarda_pago=mysqli_query($con,"INSERT INTO formas_pago_compras VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$forma_pago."','".$total_pago."','".$plazo_pago."','".$tiempo_pago."')");

	  }
}

public function guarda_retencion_compra($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoCompRetencion->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoCompRetencion->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoCompRetencion->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$serie_retencion = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi;
	$secuencial_retencion = intval($object_xml->infoTributaria->secuencial);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$nombre_cliente = $object_xml->infoTributaria->razonSocial;
	$ejercicio_fiscal = $object_xml->infoCompRetencion->periodoFiscal;
	$version_xml = $object_xml['version'];
		
		foreach ($object_xml->impuestos->impuesto as $detalle_retenido) {
		$fecha_year_documento =substr($detalle_retenido->fechaEmisionDocSustento,6,4);
		$fecha_mes_documento =substr($detalle_retenido->fechaEmisionDocSustento,3,2);
		$fecha_dia_documento =substr($detalle_retenido->fechaEmisionDocSustento,0,2);
		$fecha_documento = date($fecha_year_documento."-".$fecha_mes_documento."-".$fecha_dia_documento);
		
		$tipo_comprobante= $detalle_retenido->codDocSustento;
		$numero_comprobante= substr($detalle_retenido->numDocSustento,0,3)."-".substr($detalle_retenido->numDocSustento,3,3)."-".substr($detalle_retenido->numDocSustento,6,9);
		}
		
	
	//guardar encabezado de retencion
	$total_retencion = 0;
	$sql_guarda_encabezado= mysqli_query($con,"INSERT INTO encabezado_retencion VALUES (null,'".$ruc_empresa."','".$id_proveedor."','".$serie_retencion."', '".$secuencial_retencion."','".$total_retencion."','".$aut_sri."','AUTORIZADO','".$fecha_emision."','".$fecha_documento."','".$id_usuario."','".$tipo_comprobante."','".$numero_comprobante."','0','0','2','ENVIADO')");

	//$sql_consulta_id= mysqli_query($con, "SELECT * FROM encabezado_retencion WHERE aut_sri = '".$aut_sri."' ");
	//$row_consulta=mysqli_fetch_array($sql_consulta_id);
	//$id_retencion=$row_consulta['id_encabezado_retencion'];
	
	//para la version 1.0.0
	if ($version_xml=='1.0.0'){
	 if (isset($object_xml->impuestos)) {
			foreach ($object_xml->impuestos->impuesto as $detalle) {
			  $impuesto = $detalle->codigo;
			  switch ($impuesto) {
				case "1":
					$impuesto='RENTA';
					break;
				case "2":
					$impuesto='IVA';
					break;
				case "6":
					$impuesto='ISD';
					break;
					}
			  
			  $codigo_retencion= $detalle->codigoRetencion;
			  $base_imponible = $detalle->baseImponible;
			  $porcentaje_retenido= $detalle->porcentajeRetener;
			  $valor_retenido= $detalle->valorRetenido;
			  			  
			    $sql_consulta_nombre= mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret = '".$codigo_retencion."' ");
				$row_consulta_nombre=mysqli_fetch_array($sql_consulta_nombre);
				$concepto_retencion=$row_consulta_nombre['concepto_ret'];
				$id_retencion=$row_consulta_nombre['id_ret'];

			$fecha_year_documento =substr($detalle->fechaEmisionDocSustento,6,4);
			$fecha_mes_documento =substr($detalle->fechaEmisionDocSustento,3,2);
			$fecha_dia_documento =substr($detalle->fechaEmisionDocSustento,0,2);
			$fecha_documento = date($fecha_year_documento."-".$fecha_mes_documento."-".$fecha_dia_documento);
			  
			//guardar detalle de retencion
			$sql_guarda_detalle= mysqli_query($con,"INSERT INTO cuerpo_retencion VALUES (null,'".$serie_retencion."','".$secuencial_retencion."', '".$ruc_empresa."','".$id_retencion."','".$ejercicio_fiscal."','".$base_imponible."','".$codigo_retencion."','".$impuesto."','".$porcentaje_retenido."','".$valor_retenido."','".$concepto_retencion."')");
		  }
	  }
	}
	
	//para la version 2.0.0
	if ($version_xml=='2.0.0'){
	 if (isset($object_xml->docsSustento)) {
		 
		 foreach ($object_xml->docsSustento->docSustento as $detalle_documento) {
			$fecha_year_documento =substr($detalle_documento->fechaEmisionDocSustento,6,4);
			$fecha_mes_documento =substr($detalle_documento->fechaEmisionDocSustento,3,2);
			$fecha_dia_documento =substr($detalle_documento->fechaEmisionDocSustento,0,2);
			$fecha_documento = date($fecha_year_documento."-".$fecha_mes_documento."-".$fecha_dia_documento);
			
				foreach ($object_xml->docsSustento->docSustento->retenciones->retencion as $detalle) {
				  $impuesto = $detalle->codigo;
				  switch ($impuesto) {
					case "1":
						$impuesto='RENTA';
						break;
					case "2":
						$impuesto='IVA';
						break;
					case "6":
						$impuesto='ISD';
						break;
						}
				  $codigo_retencion= $detalle->codigoRetencion;
				  $base_imponible = $detalle->baseImponible;
				  $porcentaje_retenido= $detalle->porcentajeRetener;
				  $valor_retenido= $detalle->valorRetenido;
				
				$sql_consulta_nombre= mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret = '".$codigo_retencion."' ");
				$row_consulta_nombre=mysqli_fetch_array($sql_consulta_nombre);
				$concepto_retencion=$row_consulta_nombre['concepto_ret'];
				$id_retencion=$row_consulta_nombre['id_ret'];
	  
					//guardar detalle de retencion
				$sql_guarda_detalle= mysqli_query($con,"INSERT INTO cuerpo_retencion VALUES (null,'".$serie_retencion."','".$secuencial_retencion."', '".$ruc_empresa."','".$id_retencion."','".$ejercicio_fiscal."','".$base_imponible."','".$codigo_retencion."','".$impuesto."','".$porcentaje_retenido."','".$valor_retenido."','".$concepto_retencion."')");
				}
			}
	  }
	}
	
	//actualizar total retencion
	$sql_consulta_encabezado= mysqli_query($con,"SELECT sum(valor_retenido) as valor_retenido FROM cuerpo_retencion WHERE ruc_empresa='".$ruc_empresa."' group by serie_retencion='".$serie_retencion."' and secuencial_retencion='".$secuencial_retencion."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
	$row_total_retencion=mysqli_fetch_array($sql_consulta_encabezado);
	$total_retenido=$row_total_retencion['valor_retenido'];

	$sql_actualiza_encabezado= mysqli_query($con,"UPDATE encabezado_retencion SET total_retencion = '".$total_retenido."' WHERE ruc_empresa = '".$ruc_empresa."' and serie_retencion='".$serie_retencion."' and secuencial_retencion='".$secuencial_retencion."' ");

	//guardar info adicional de retencion
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= strClean(${"infoAd" . $i});
		   $valor_detalle= strClean(${"valueInfoAd" . $i});
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional= mysqli_query($con,"INSERT INTO detalle_adicional_retencion VALUES (null,'".$ruc_empresa."', '".$serie_retencion."','".$secuencial_retencion."','".$nombre_detalle."','".$valor_detalle."')");
		}
	}

}

public function guarda_retencion_venta($con, $ruc_empresa, $object_xml, $id_usuario, $id_cliente){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoCompRetencion->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoCompRetencion->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoCompRetencion->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$serie_retencion = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi;
	$secuencial_retencion = $object_xml->infoTributaria->secuencial;
	$codigo_documento=codigo_unico(20);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$nombre_cliente = $object_xml->infoTributaria->razonSocial;
	$ejercicio_fiscal = $object_xml->infoCompRetencion->periodoFiscal;
	$version_xml = $object_xml['version'];

	//para guardar el detalle de la retencion de ventas
	$documentos=array();
	
	//para la version 1.0.0
	if ($version_xml=='1.0.0'){
	 if (isset($object_xml->impuestos)) {
			foreach ($object_xml->impuestos->impuesto as $detalle) {
			  $codigo_impuesto= $detalle->codigo;
			  $codigo_retencion= $detalle->codigoRetencion;
			  $base_imponible = $detalle->baseImponible;
			  $porcentaje_retenido= $detalle->porcentajeRetener;
			  $valor_retenido= $detalle->valorRetenido;
			  $tipo_documento= $detalle->codDocSustento;
			  $numero_documento= $detalle->numDocSustento;
			  $documentos[]= $detalle->numDocSustento;
		  
			$fecha_year_documento =substr($detalle->fechaEmisionDocSustento,6,4);
			$fecha_mes_documento =substr($detalle->fechaEmisionDocSustento,3,2);
			$fecha_dia_documento =substr($detalle->fechaEmisionDocSustento,0,2);
			$fecha_documento = date($fecha_year_documento."-".$fecha_mes_documento."-".$fecha_dia_documento);
			  
			//guardar detalle de retencion
			$sql_guarda_detalle="INSERT INTO cuerpo_retencion_venta VALUES (null,'".$serie_retencion."','".$secuencial_retencion."', '".$ruc_empresa."','".$ejercicio_fiscal."','".$base_imponible."','".$codigo_retencion."','".$codigo_impuesto."','".$porcentaje_retenido."','".$valor_retenido."','".$codigo_documento."','".$tipo_documento."','".$numero_documento."')";
			$query_guarda_detalle = mysqli_query($con,$sql_guarda_detalle);
			if (!$query_guarda_detalle){
			return "No se guardo el detalle de la retención: ".$serie_retencion."-".$secuencial_retencion." cliente: ".$nombre_cliente. " clave de accesso: ". $aut_sri ."</br>".mysqli_error($con);		
			}
		  }
	  }
	}
	
	//para la version 2.0.0
	if ($version_xml=='2.0.0'){
	 if (isset($object_xml->docsSustento)) {
		 
		 foreach ($object_xml->docsSustento->docSustento as $detalle_documento) {
			$tipo_documento= $detalle_documento->codDocSustento;
			$numero_documento= $detalle_documento->numDocSustento;
			$fecha_year_documento =substr($detalle_documento->fechaEmisionDocSustento,6,4);
			$fecha_mes_documento =substr($detalle_documento->fechaEmisionDocSustento,3,2);
			$fecha_dia_documento =substr($detalle_documento->fechaEmisionDocSustento,0,2);
			$fecha_documento = date($fecha_year_documento."-".$fecha_mes_documento."-".$fecha_dia_documento);
			$documentos[]= $detalle_documento->numDocSustento;

				foreach ($object_xml->docsSustento->docSustento->retenciones->retencion as $detalle) {
				  $codigo_impuesto= $detalle->codigo;
				  $codigo_retencion= $detalle->codigoRetencion;
				  $base_imponible = $detalle->baseImponible;
				  $porcentaje_retenido= $detalle->porcentajeRetener;
				  $valor_retenido= $detalle->valorRetenido;
	  
					//guardar detalle de retencion
					$sql_guarda_detalle="INSERT INTO cuerpo_retencion_venta VALUES (null,'".$serie_retencion."','".$secuencial_retencion."', '".$ruc_empresa."','".$ejercicio_fiscal."','".$base_imponible."','".$codigo_retencion."','".$codigo_impuesto."','".$porcentaje_retenido."','".$valor_retenido."','".$codigo_documento."','".$tipo_documento."','".$numero_documento."')";
					$query_guarda_detalle = mysqli_query($con,$sql_guarda_detalle);
					if (!$query_guarda_detalle){
					return "No se guardo el detalle de la retención: ".$serie_retencion."-".$secuencial_retencion." cliente: ".$nombre_cliente. " clave de accesso: ". $aut_sri ."</br>".mysqli_error($con);		
					}
				}
				
				
			}
	  }
	}
	  $documentos_unicos=array_unique($documentos);
	  $facturas= implode("//", $documentos_unicos);
	  
	  //guardar encabezado de retencion
	$sql_guarda_encabezado="INSERT INTO encabezado_retencion_venta VALUES (null,'".$ruc_empresa."','".$id_cliente."','".$serie_retencion."', '".$secuencial_retencion."','".$aut_sri."','".$fecha_emision."','".$id_usuario."','0','".$codigo_documento."','".$facturas."')";
	$query_guarda_encabezado = mysqli_query($con,$sql_guarda_encabezado);
	
	if (!$query_guarda_encabezado){
	return "No se guardo el encabezado de la retención: ".$serie_retencion."-".$secuencial_retencion." cliente: ".$nombre_cliente. " clave de accesso: ". $aut_sri ."</br>";	
	}
	//guardar info adicional de retencion
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= strClean(${"infoAd" . $i});
		   $valor_detalle= strClean(${"valueInfoAd" . $i});
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional="INSERT INTO detalle_adicional_retencion_venta VALUES (null,'".$ruc_empresa."', '".$serie_retencion."','".$secuencial_retencion."','".$nombre_detalle."','".$valor_detalle."','".$codigo_documento."')";
				$query_guarda_detalle_adicional = mysqli_query($con,$sql_guarda_detalle_adicional);
				if (!$query_guarda_detalle_adicional){
				return "No se guardo la información adicional de la retención: ".$serie_retencion."-".$secuencial_retencion." cliente: ".$nombre_cliente. " clave de accesso: ". $aut_sri ."</br>";		
				}
		}
	}

}


public function guarda_nc($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoNotaCredito->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoNotaCredito->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoNotaCredito->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$numero_nc = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
	$numero_documento_modificado = $object_xml->infoNotaCredito->numDocModificado;
	$secuencial_factura = $object_xml->infoTributaria->secuencial;
	$codigo_documento=codigo_unico(20);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$total_nc = $object_xml->infoNotaCredito->valorModificacion;
	$motivo = $object_xml->infoNotaCredito->motivo;
	$codigo_documento_modificado = $object_xml->infoNotaCredito->codDocModificado;
	$nombre_proveedor = $object_xml->infoTributaria->razonSocial;
	$tipoIdentificacionComprador = $object_xml->infoNotaCredito->tipoIdentificacionComprador;
	//guardar encabezado
	$sql_guarda_encabezado="INSERT INTO encabezado_compra VALUES (null,'".$fecha_emision."','".$ruc_empresa."','".$numero_nc."', '".$codigo_documento."','".$id_proveedor."','".$id_documento."','1','".$aut_sri."','".$fecha_emision."','".$secuencial_factura."','".$secuencial_factura."','".$fecha_registro."', '".$id_usuario."', '".$total_nc."','".$numero_documento_modificado."','0','ELECTRÓNICA','". $tipoIdentificacionComprador. "',0,0,'".$codigo_documento_modificado."')";
	$query_guarda_encabezado = mysqli_query($con,$sql_guarda_encabezado);
	if (!$query_guarda_encabezado){
	return "No se guardo el encabezado de la nc ".$numero_nc." proveedor: ".$nombre_proveedor. " clave de acceso: ". $aut_sri ."</br>";
	}

	 if (isset($object_xml->detalles)) {
			foreach ($object_xml->detalles->detalle as $detalle) {
			  $codigo_detalle= $detalle->codigoInterno;
			  $descripcion_detalle= strClean($detalle->descripcion);
			  $cantidad_detalle = $detalle->cantidad;
			  $precio_detalle= $detalle->precioUnitario;
			  $descuento_detalle= $detalle->descuento;
			  $subtotal_detalle= $detalle->precioTotalSinImpuesto;
			  $tipo_impuesto= $detalle->impuestos->impuesto->codigo;
			  $impuesto_detalle= $detalle->impuestos->impuesto->codigoPorcentaje;
			  
			//guardar detalle de nc
			$sql_guarda_detalle="INSERT INTO cuerpo_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$codigo_detalle."','".$descripcion_detalle."','".$cantidad_detalle."','".$precio_detalle."','".$descuento_detalle."','".$tipo_impuesto."','".$impuesto_detalle."','".$subtotal_detalle."',0)";
			$query_guarda_detalle = mysqli_query($con,$sql_guarda_detalle);
			if (!$query_guarda_detalle){
			return "No se guardo el detalle de la nc: " .$numero_nc." proveedor ". $nombre_proveedor." clave de acceso: ".$aut_sri."</br>";	
			}
		  }
	  }
	//guardar info adicional de nc
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= $sanitize->string_sanitize(${"infoAd" . $i},$force_lowercase = false, $anal = false);
		   $valor_detalle= $sanitize->string_sanitize(${"valueInfoAd" . $i},$force_lowercase = false, $anal = false);
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional="INSERT INTO detalle_adicional_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$nombre_detalle."','".$valor_detalle."')";
				$query_guarda_detalle_adicional = mysqli_query($con,$sql_guarda_detalle_adicional);
				if (!$query_guarda_detalle_adicional){
				return "No se guardo la información adicional de la nc: " .$numero_nc." proveedor ". $nombre_proveedor." clave de acceso: ".$aut_sri."</br>";					
				}
		}
	}
	//guardar motivo
				$sql_guarda_motivo="INSERT INTO detalle_adicional_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','MOTIVO','".$motivo."')";
				$query_guarda_motivo = mysqli_query($con,$sql_guarda_motivo);
				if (!$query_guarda_motivo){
				return "No se guardo el motivo de la nc: " .$numero_nc." proveedor ". $nombre_proveedor." clave de acceso: ".$aut_sri."</br>";									
				}	
}


public function guarda_nd($con, $ruc_empresa, $object_xml, $id_usuario, $id_proveedor){
	$sanitize= new sanitize();
	$fecha_registro=date("Y-m-d H:i:s");
	$fecha_year =substr($object_xml->infoNotaDebito->fechaEmision,6,4);
	$fecha_mes =substr($object_xml->infoNotaDebito->fechaEmision,3,2);
	$fecha_dia =substr($object_xml->infoNotaDebito->fechaEmision,0,2);
	$fecha_emision = date($fecha_year."-".$fecha_mes."-".$fecha_dia);
	$numero_nd = $object_xml->infoTributaria->estab."-".$object_xml->infoTributaria->ptoEmi."-".$object_xml->infoTributaria->secuencial;
	$numero_documento_modificado = $object_xml->infoNotaDebito->numDocModificado;
	$secuencial_nd = $object_xml->infoTributaria->secuencial;
	$codigo_documento=codigo_unico(20);
	$id_documento = $object_xml->infoTributaria->codDoc;
	$aut_sri = $object_xml->infoTributaria->claveAcceso;
	$total_nd = $object_xml->infoNotaDebito->valorTotal;
	$codigo_documento_modificado = $object_xml->infoNotaDebito->codDocModificado;
	$nombre_proveedor = $object_xml->infoTributaria->razonSocial;
	$tipoIdentificacionComprador = $object_xml->infoNotaDebito->tipoIdentificacionComprador;
	//guardar encabezado
	$sql_guarda_encabezado="INSERT INTO encabezado_compra VALUES (null,'".$fecha_emision."','".$ruc_empresa."','".$numero_nd."', '".$codigo_documento."','".$id_proveedor."','".$id_documento."','1','".$aut_sri."','".$fecha_emision."','".$secuencial_nd."','".$secuencial_nd."','".$fecha_registro."', '".$id_usuario."', '".$total_nd."','".$numero_documento_modificado."','0','ELECTRÓNICA','".$tipoIdentificacionComprador."',0,0,'".$codigo_documento_modificado."')";
	$query_guarda_encabezado = mysqli_query($con,$sql_guarda_encabezado);
	if (!$query_guarda_encabezado){
	return "No se guardo el encabezado de la nd ".$numero_nd." proveedor: ".$nombre_proveedor. " clave de acceso: ". $aut_sri ."</br>";
	}

	 if (isset($object_xml->motivos)) {
			foreach ($object_xml->motivos->motivo as $detalle) {
			  $codigo_detalle= "";
			  $descripcion_detalle= strClean($detalle->razon);
			  $cantidad_detalle = "0";
			  $precio_detalle= $detalle->valor;
			  $descuento_detalle= "0";
			  $subtotal_detalle= $detalle->valor;
			  $tipo_impuesto= $detalle->impuestos->impuesto->codigo;
			  $impuesto_detalle= $detalle->impuestos->impuesto->codigoPorcentaje;
			  
			//guardar detalle de nd
			$sql_guarda_detalle="INSERT INTO cuerpo_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$codigo_detalle."','".$descripcion_detalle."','".$cantidad_detalle."','".$precio_detalle."','".$descuento_detalle."','".$tipo_impuesto."','".$impuesto_detalle."','".$subtotal_detalle."',0)";
			$query_guarda_detalle = mysqli_query($con,$sql_guarda_detalle);
			if (!$query_guarda_detalle){
			return "No se guardo el detalle de la nd: " .$numero_nd." proveedor ". $nombre_proveedor." clave de acceso: ".$aut_sri."</br>";	
			}
		  }
	  }
	//guardar info adicional de nd
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
               ${"infoAd" . $i} = (string) $attr->attributes();
               ${"valueInfoAd" . $i} = (string) $attr[0];
		   $nombre_detalle= strClean(${"infoAd" . $i});
		   $valor_detalle= strClean(${"valueInfoAd" . $i});
			 $i++;
	//guardar detalle info adicional
				$sql_guarda_detalle_adicional="INSERT INTO detalle_adicional_compra VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$nombre_detalle."','".$valor_detalle."')";
				$query_guarda_detalle_adicional = mysqli_query($con,$sql_guarda_detalle_adicional);
				if (!$query_guarda_detalle_adicional){
				return "No se guardo la información adicional de la nc: " .$numero_nc." proveedor ". $nombre_proveedor." clave de acceso: ".$aut_sri."</br>";					
				}
		}
	}
	
	//guardar formas de pago
	 if (isset($object_xml->infoNotaDebito->pagos)) {
			foreach ($object_xml->infoNotaDebito->pagos->pago as $pagos) {
			  $forma_pago= $pagos->formaPago;
			  $total_pago= $pagos->total;
			  $plazo_pago = $pagos->plazo;
			  $tiempo_pago = $pagos->unidadTiempo;

			//guardar detalle de forma de pago
			$sql_guarda_pago="INSERT INTO formas_pago_compras VALUES (null,'".$ruc_empresa."', '".$codigo_documento."','".$forma_pago."','".$total_pago."','".$plazo_pago."','".$tiempo_pago."')";
			$query_guarda_pago = mysqli_query($con,$sql_guarda_pago);
			if (!$query_guarda_pago){
			return "No se guardo la forma de pago de la nd: ".$numero_nd." proveedor: ".$nombre_proveedor. " clave de accesso: ". $aut_sri ."</br>";		  
			}
		  }
	  }
}



public function proveedor_cliente($tipo, $con, $ruc_empresa, $object_xml, $ruc_proveedor){
	$sanitize= new sanitize();
	if (isset($object_xml->infoAdicional)) {
		$i=0;
		$direccion_proveedor_cliente="";
		$mail_proveedor_cliente="";
		$telefono_proveedor_cliente="";
		foreach ($object_xml->infoAdicional->campoAdicional as $attr) {
			   ${"infoAd" . $i} = (string) $attr->attributes();
			   ${"valueInfoAd" . $i} = (string) $attr[0];
		   
		   $nombre_detalle= strClean(${"infoAd" . $i});
		   $valor_detalle= strClean(${"valueInfoAd" . $i});
			
			if ($nombre_detalle=="Mail" || $nombre_detalle=="email" || $nombre_detalle=="Email" || $nombre_detalle=="mail"){
			$mail_proveedor_cliente=$valor_detalle;
			}
			
			if ($nombre_detalle=="Teléfono" || $nombre_detalle=="Telefono" || $nombre_detalle=="teléfono" || $nombre_detalle=="telefono"){
			$telefono_proveedor_cliente=$valor_detalle;
			}
			
			if ($nombre_detalle=="Dirección" || $nombre_detalle=="dirección" || $nombre_detalle=="Direccion" || $nombre_detalle=="direccion"){
			$direccion_proveedor_cliente=$valor_detalle;
			}
			$i++;
		}
						
	}

	//para saber el id del proveedor
	if ($tipo=="proveedor"){
	$busca_proveedor = "SELECT * FROM proveedores WHERE ruc_proveedor= '".$ruc_proveedor."' and ruc_empresa='".$ruc_empresa."' ";
	$result = $con->query($busca_proveedor);
	$contar_proveedores = mysqli_num_rows($result);
	if ($contar_proveedores>0){
	$row_proveedores = mysqli_fetch_array($result);
	$id_proveedor=$row_proveedores['id_proveedor'];
	return $id_proveedor;
	}else{
	//GUARDA EL PROVEEDOR
	$ruc_emisor = $object_xml->infoTributaria->ruc;
	$tipo_documento = $object_xml->infoTributaria->codDoc;
	
	if (substr($ruc_emisor,0,12)==substr($ruc_empresa,0,12) && $tipo_documento=="03"){
	$razon_social = $sanitize->string_sanitize($object_xml->infoLiquidacionCompra->razonSocialProveedor,$force_lowercase = false, $anal = false);
	$nombre_comercial = $razon_social;
	$lleva_contabilidad = $object_xml->infoLiquidacionCompra->obligadoContabilidad;
	$direccion_proveedor_cliente = $object_xml->infoLiquidacionCompra->dirEstablecimiento;
	}	
	
	if (substr($ruc_emisor,0,12)==substr($ruc_empresa,0,12) && $tipo_documento=="07"){
	$razon_social = $sanitize->string_sanitize($object_xml->infoCompRetencion->razonSocialSujetoRetenido,$force_lowercase = false, $anal = false);
	$nombre_comercial = $razon_social;
	$lleva_contabilidad = $object_xml->infoCompRetencion->obligadoContabilidad;
	$direccion_proveedor_cliente = $object_xml->infoCompRetencion->dirEstablecimiento;
	}
	
	if ($tipo_documento=="01"){
	$razon_social = $sanitize->string_sanitize($object_xml->infoTributaria->razonSocial,$force_lowercase = false, $anal = false);
	$nombre_comercial = $sanitize->string_sanitize($object_xml->infoTributaria->nombreComercial,$force_lowercase = false, $anal = false);
	$lleva_contabilidad = $object_xml->infoFactura->obligadoContabilidad;
	$direccion_proveedor_cliente = $object_xml->infoTributaria->dirMatriz;
	}
	
	if ($tipo_documento=="04"){
	$razon_social = $sanitize->string_sanitize($object_xml->infoTributaria->razonSocial,$force_lowercase = false, $anal = false);
	$nombre_comercial = $sanitize->string_sanitize($object_xml->infoTributaria->nombreComercial,$force_lowercase = false, $anal = false);
	$lleva_contabilidad = $object_xml->infoNotaCredito->obligadoContabilidad;
	$direccion_proveedor_cliente = $object_xml->infoTributaria->dirMatriz;
	}
	
	if ($tipo_documento=="05"){
	$razon_social = $sanitize->string_sanitize($object_xml->infoTributaria->razonSocial,$force_lowercase = false, $anal = false);
	$nombre_comercial = $sanitize->string_sanitize($object_xml->infoTributaria->nombreComercial,$force_lowercase = false, $anal = false);
	$lleva_contabilidad = $object_xml->infoNotaDebito->obligadoContabilidad;
	$direccion_proveedor_cliente = $object_xml->infoTributaria->dirMatriz;
	}
	
	$fecha_registro=date("Y-m-d H:i:s");
	$digito_verificador=substr($ruc_proveedor,2,1);

		if ($lleva_contabilidad == "SI"){
			switch ($digito_verificador) {
			case "9":
				$tipo_empresa = "03";
				break;
			case "6":
				$tipo_empresa = "05";
				break;
			case "0":
				$tipo_empresa = "02";
				break;
			case "1":
				$tipo_empresa = "02";
				break;
			case "2":
				$tipo_empresa = "02";
				break;
			case "3":
				$tipo_empresa = "02";
				break;
			case "4":
				$tipo_empresa = "02";
				break;
			case "5":
				$tipo_empresa = "02";
				break;
			case "7":
				$tipo_empresa = "02";
				break;
			case "8":
				$tipo_empresa = "02";
				break;
				}
		}else{
			$tipo_empresa="01";
		}

		$sql_guarda_proveedor=mysqli_query($con,"INSERT INTO proveedores VALUES (null,'".$razon_social."','".$nombre_comercial."','".$ruc_empresa."', '04','".$ruc_proveedor."','".$mail_proveedor_cliente."','".$direccion_proveedor_cliente."','".$telefono_proveedor_cliente."', '".$tipo_empresa."', '".$fecha_registro."','30','Días','1')");
		$busca_nuevo_proveedor = mysqli_query($con,"SELECT * FROM proveedores WHERE id_proveedor= LAST_INSERT_ID()");
		$row_nuevo_proveedores = mysqli_fetch_array($busca_nuevo_proveedor);
		$id_proveedor_cliente=$row_nuevo_proveedores['id_proveedor'];
		return $id_proveedor_cliente;
		}
	}
	
	//para buscar el id del cliente
	if ($tipo=="cliente"){
		$busca_cliente = "SELECT * FROM clientes WHERE ruc= '".$ruc_proveedor."' and ruc_empresa='".$ruc_empresa."' ";
		$result = $con->query($busca_cliente);
		$contar_clientes = mysqli_num_rows($result);
		if ($contar_clientes>0){
		$row_clientes = mysqli_fetch_array($result);
		$id_cliente=$row_clientes['id'];
		return $id_cliente;
		}else{
		//GUARDA EL cliente
		$ruc_emisor = $object_xml->infoTributaria->ruc;
		$tipo_documento = $object_xml->infoTributaria->codDoc;
		
		if (substr($ruc_emisor,0,12)==substr($ruc_empresa,0,12) && $tipo_documento=="01"){
		$razon_social = $object_xml->infoFactura->razonSocialComprador;
		$lleva_contabilidad = $object_xml->infoFactura->obligadoContabilidad;
		}
		
		if ($tipo_documento=="07"){
		$razon_social = $object_xml->infoTributaria->razonSocial;
		$lleva_contabilidad = $object_xml->infoCompRetencion->obligadoContabilidad;
		$direccion_proveedor_cliente = $object_xml->infoTributaria->dirMatriz;
		}
		
		$fecha_registro=date("Y-m-d H:i:s");
		$id_usuario = $_SESSION['id_usuario'];
		$sql_guarda_cliente=mysqli_query($con, "INSERT INTO clientes VALUES (null,'".$ruc_empresa."','".$razon_social."','04','".$ruc_proveedor."', '".$telefono_proveedor_cliente."','".$mail_proveedor_cliente."','".$direccion_proveedor_cliente."','1', '".$fecha_registro."','30','Días','".$id_usuario."')");
		
		$busca_nuevo_cliente = mysqli_query($con,"SELECT * FROM clientes WHERE id= LAST_INSERT_ID()");
		$row_nuevo_cliente = mysqli_fetch_array($busca_nuevo_cliente);
		$id_proveedor_cliente=$row_nuevo_cliente['id'];
		return $id_proveedor_cliente;
		}
	}
	
}

public function lee_ride($clave_acceso){
	$consulta = new ConsultaCompSri();
	$consulta->claveacceso = $clave_acceso;
	$comp = $consulta->consultar();
	
	if ($comp) {
		return $comp;
	}else{

		if (count($consulta->get_errores())>0) {
			foreach ($consulta->get_errores() as $err) {
				echo $err . "<br>";
			}
		}
		return false;
	}	
	return false;
  }
   
  //ESTA ES PARA LEER DESDE UN XML Y MOSTRAR LOS DATOS PARA ANULAR DOCUMENTO
  public function estado_ride($clave_acceso){
	$consulta = new ConsultaCompSri();
	$consulta->claveacceso = $clave_acceso;
	$estado_comp = $consulta->consultar_estado();
	
	if ($estado_comp) {
		return $estado_comp;
	}else{

		if (count($consulta->get_errores())>0) {
			foreach ($consulta->get_errores() as $err) {
				echo $err;
			}
		}
		return false;
	}	
	return false;
  }
  
  
  //ESTA ES PARA LEER LA FECHA DE AUTORIZACION
  public function fecha_autorizacion($clave_acceso){
	$consulta = new ConsultaCompSri();
	$consulta->claveacceso = $clave_acceso;
	$fecha_autorizacion = $consulta->consultar_fecha_autorizacion();
	
	if ($fecha_autorizacion) {
		return $fecha_autorizacion;
	}else{

		if (count($consulta->get_errores())>0) {
			foreach ($consulta->get_errores() as $err) {
				echo $err;
			}
		}
		return false;
	}	
	return false;
  }
  
}

function comprueba_registrados($con, $ruc_empresa, $registrar_en, $serie, $secuencial, $id_proveedor_cliente){
	switch ($registrar_en) {
			case "factura_compra"://para guardar facturas, nc y liquidaciones en las compras
				$sql_encabezado=mysqli_query($con,"SELECT count(*) as total FROM encabezado_compra WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and numero_documento = '".$serie."-".$secuencial."' and id_proveedor='".$id_proveedor_cliente."' ");	
				break;
			case "factura_venta"://si registro una factura mia generada en otro sistema y la quiero guardar en mi empresa
				$sql_encabezado=mysqli_query($con,"SELECT count(*) as total FROM encabezado_factura WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and serie_factura = '".$serie."' and secuencial_factura= '".intval($secuencial)."' and id_cliente='".$id_proveedor_cliente."' ");
				break;
			case "retencion_compra"://si registro una nc mia generada en otro sistema y la quiero guardar en mi empresa
				$sql_encabezado=mysqli_query($con,"SELECT count(*) as total FROM encabezado_retencion WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and serie_retencion = '".$serie."' and secuencial_retencion= '".intval($secuencial)."' and id_proveedor='".$id_proveedor_cliente."' ");
				break;
			case "retencion_venta":
				$sql_encabezado=mysqli_query($con,"SELECT count(*) as total FROM encabezado_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and serie_retencion = '".$serie."' and secuencial_retencion= '".intval($secuencial)."' and id_cliente='".$id_proveedor_cliente."' ");
				break;
			case "liquidacion"://si registro una lc mia generada en otro sistema y la quiero guardar en mi empresa
				$sql_encabezado=mysqli_query($con,"SELECT count(*) as total FROM encabezado_liquidacion WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and serie_liquidacion = '".$serie."' and secuencial_liquidacion= '".intval($secuencial)."' and id_proveedor='".$id_proveedor_cliente."' ");
				break;
			case "nota_credito"://si registro una nc mia generada en otro sistema y la quiero guardar en mi empresa
				$sql_encabezado=mysqli_query($con,"SELECT count(*) as total FROM encabezado_nc WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and serie_nc = '".$serie."' and secuencial_nc= '".intval($secuencial)."' and id_cliente='".$id_proveedor_cliente."' ");
				break;
				}
			$row_registros=mysqli_fetch_array($sql_encabezado);
			return ($row_registros['total']);
}
?>
