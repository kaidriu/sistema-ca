<?php
include("../validadores/generador_codigo_unico.php");
	if (empty($_POST['fecha_cambio_producto'])) {
		   echo "<script>$.notify('Ingrese fecha.','error');
				</script>";
		}else if (!date($_POST['fecha_cambio_producto'])) {
		   		   echo "<script>$.notify('Ingrese una fecha correcta.','error');
				</script>";
		}else if (empty($_POST['id_cliente_cambio'])) {
		   		   echo "<script>$.notify('Seleccione un cliente.','error');
				</script>";
        }else if ( (!empty($_POST['fecha_cambio_producto']))){
					
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		
		$fecha_cambio_producto=date('Y-m-d H:i:s', strtotime(mysqli_real_escape_string($con,(strip_tags($_POST["fecha_cambio_producto"],ENT_QUOTES)))));
		$id_cliente=mysqli_real_escape_string($con,(strip_tags($_POST["id_cliente_cambio"],ENT_QUOTES)));
		$serie_sucursal=mysqli_real_escape_string($con,(strip_tags($_POST["serie_factura"],ENT_QUOTES)));
		$observaciones=mysqli_real_escape_string($con,(strip_tags($_POST["observaciones"],ENT_QUOTES)));
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date("Y-m-d H:i:s");
			
		$sql_factura_temporal=mysqli_query($con,"SELECT * from factura_tmp as fac_tmp LEFT JOIN productos_servicios as pro ON fac_tmp.id_producto=pro.id LEFT JOIN unidad_medida as med ON med.id_medida=fac_tmp.id_medida LEFT JOIN bodega as bod ON bod.id_bodega=fac_tmp.id_bodega WHERE fac_tmp.id_usuario = '".$id_usuario."'");
		$count=mysqli_num_rows($sql_factura_temporal);
		if ($count==0){			
			echo "<script>$.notify('Agregue productos y cantidades que desea reemplazar.','error');
				</script>";
		}else{
		$id_cv=$_POST["id_cv"];
		$numero_cv=$_POST["numero_consignacion"];
		$nuevas_cantidades_productos=$_POST["cant_cambio"];
		$registros=$_POST["registros"];
					
			foreach ($registros as $valor ){
				$codigo_unico=codigo_unico(19)."4";
				
				$tipo_cambio=mysqli_query($con, "SELECT * FROM factura_tmp WHERE id = '".$valor."' ");
				$row_tipo_cambio = mysqli_fetch_array($tipo_cambio);
				$tipo_cambio=$row_tipo_cambio['tarifa_botellas'];
	
				
				if($tipo_cambio=='F'){
				$detalle_item=mysqli_query($con, "SELECT * FROM cuerpo_factura WHERE id_cuerpo_factura = '".$valor."' ");
				$row_detalle_factura = mysqli_fetch_array($detalle_item);
				$serie_factura=$row_detalle_factura['serie_factura'];
				$secuencial_factura=$row_detalle_factura['secuencial_factura'];
				$id_producto_anterior=$row_detalle_factura['id_producto'];
				$factura=$serie_factura."-".$secuencial_factura;
				$id_bodega_anterior=$row_detalle_factura['id_bodega'];
				$id_medida_anterior=$row_detalle_factura['id_medida_salida'];
				$vencimiento_anterior=date('Y-m-d', strtotime($row_detalle_factura['vencimiento']));
				$lote_anterior=$row_detalle_factura['lote'];
				}
				if($tipo_cambio=='R'){
				$detalle_factura_r=mysqli_query($con, "SELECT * FROM cambio_productos_facturados WHERE id_cambio='".$valor."' ");
				$row_detalle_factura_r=mysqli_fetch_array($detalle_factura_r);
				$serie_factura=substr($row_detalle_factura_r['factura'],0,7);
				$secuencial_factura=substr($row_detalle_factura_r['factura'],8,9);
				$id_producto_anterior=$row_detalle_factura_r['id_nuevo_producto'];
				$factura=$serie_factura."-".$secuencial_factura;
				$id_bodega_anterior=$row_detalle_factura_r['id_bodega_anterior'];
				$id_medida_anterior=$row_detalle_factura_r['id_medida_anterior'];
				$vencimiento_anterior=date('Y-m-d', strtotime($row_detalle_factura_r['vencimiento_anterior']));
				$lote_anterior=$row_detalle_factura_r['lote_anterior'];				
				}
				
				$nueva_cantidad=$nuevas_cantidades_productos[$valor];
				$numero_consignacion_afecta=$numero_cv[$valor];
				
				$detalle_consignacion=mysqli_query($con, "SELECT * FROM detalle_consignacion WHERE id_det_consignacion = '".$id_cv[$valor]."' ");
				$row_detalle_cv = mysqli_fetch_array($detalle_consignacion);
				$id_nuevo_producto=$row_detalle_cv['id_producto'];
				$nuevo_lote=$row_detalle_cv['lote'];
				$nueva_bodega=$row_detalle_cv['id_bodega'];
				$codigo_producto=$row_detalle_cv['codigo_producto'];
				$nombre_producto=$row_detalle_cv['nombre_producto'];
				$vencimiento=$row_detalle_cv['vencimiento'];
				$id_bodega=$row_detalle_cv['id_bodega'];
				$id_medida=$row_detalle_cv['id_medida'];
				$nup=$row_detalle_cv['nup'];
				$precio=$row_detalle_cv['precio'];
				$descuento=$row_detalle_cv['descuento'];
				
				$detalle_cantidad=mysqli_query($con, "SELECT * FROM factura_tmp WHERE id = '".$valor."' ");
				$row_detalle_cantidad = mysqli_fetch_array($detalle_cantidad);
				$cantidad_entra=$row_detalle_cantidad['cantidad_tmp'];
				$lote_anterior=$row_detalle_cantidad['lote'];
				
				if ($nueva_cantidad>0 && $id_cv[$valor] !=""){
					$detalle_cambio_producto=mysqli_query($con, "INSERT INTO cambio_productos_facturados VALUES (null,'".$valor."','".$ruc_empresa."','".$id_producto_anterior."','".$id_nuevo_producto."','".$nuevo_lote."','".$nueva_cantidad."','".$fecha_registro."','".$id_usuario."','".$codigo_unico."','".$fecha_cambio_producto."','".$id_cliente."','".$cantidad_entra."','".$observaciones."','".$factura."','".$lote_anterior."','".$id_bodega_anterior."','".$id_medida_anterior."','".$vencimiento_anterior."' )");
				//guardar la salida del inventario como facturacion
				$consulta_ultima_orden=mysqli_query($con, "SELECT max(numero_consignacion) as ultimo FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and tipo_consignacion='VENTA' and operacion ='FACTURA'");
				$row_ultimo=mysqli_fetch_array($consulta_ultima_orden);
				$siguiente_orden=$row_ultimo['ultimo']+1;
				$observacion_consignacion_venta="Cambios de productos facturados con anterioridad";
				
				$encabezado_consignacion=mysqli_query($con, "INSERT INTO encabezado_consignacion VALUES (null,'".$fecha_cambio_producto."','".$ruc_empresa."','".$codigo_unico."','".$id_cliente."','VENTA','".$siguiente_orden."','".$observacion_consignacion_venta."','".$fecha_registro."','".$id_usuario."','','','', 'FACTURA','".$serie_factura."','".$secuencial_factura."','','','')");
				$detalle_consignacion=mysqli_query($con, "INSERT INTO detalle_consignacion VALUES (null,'".$id_nuevo_producto."','".$codigo_producto."','".$nombre_producto."','".$nuevo_lote."','".$vencimiento."','".$id_bodega."','".$id_medida."','".$ruc_empresa."','".$codigo_unico."','".$nueva_cantidad."','".$numero_consignacion_afecta."','".$nup."','".$precio."', '".$descuento."')");
				
				//echo registro_inventario($con, $ruc_empresa, $codigo_unico, $id_usuario, $valor, $cantidad_entra);
				}
			}
			
			echo "<script>$.notify('Cambios en productos registrado.','success');
				setTimeout(function (){location.reload()}, 1000);
				</script>";			
		}
		}else {
			$errors []= "Error desconocido.";
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

			/*
function registro_inventario($con, $ruc_empresa, $codigo_unico, $id_usuario, $id_cuerpo_factura, $cantidad_entra){

			ini_set('date.timezone','America/Guayaquil');
			$fecha_registro=date("Y-m-d H:i:s");
			
				$tipo_cambio=mysqli_query($con, "SELECT * FROM factura_tmp WHERE id = '".$id_cuerpo_factura."' ");
				$row_tipo_cambio = mysqli_fetch_array($tipo_cambio);
				$tipo_cambio=$row_tipo_cambio['tarifa_botellas'];
							
				//para traer datos de la factura
				if($tipo_cambio=='F'){
				$detalle_producto_anterior=mysqli_query($con, "SELECT * FROM cuerpo_factura WHERE id_cuerpo_factura = '".$id_cuerpo_factura."' ");
				$row_detalle_factura = mysqli_fetch_array($detalle_producto_anterior);
				$serie_factura=$row_detalle_factura['serie_factura'];
				$secuencial_factura=$row_detalle_factura['secuencial_factura'];
				$id_producto_entra=$row_detalle_factura['id_producto'];
				$id_medida_entra=$row_detalle_factura['id_medida_salida'];
				$precio_producto_entra=$row_detalle_factura['valor_unitario_factura'];
				$codigo_producto_entra=$row_detalle_factura['codigo_producto'];
				$bodega_entra=$row_detalle_factura['id_bodega'];		
				$lote_entra=$row_detalle_factura['lote'];
				$vencimiento_entra=date('Y-m-d', strtotime($row_tipo_cambio['vencimiento']));
				$referencia	= "Devolución por cambio de producto según factura ".$serie_factura."-".$secuencial_factura;		
				}
				if($tipo_cambio=='R'){
				$detalle_factura_r=mysqli_query($con, "SELECT * FROM cambio_productos_facturados WHERE id_cambio='".$id_cuerpo_factura."' ");
				$row_detalle_factura_r=mysqli_fetch_array($detalle_factura_r);
				$serie_factura=substr($row_detalle_factura_r['factura'],0,7);
				$secuencial_factura=substr($row_detalle_factura_r['factura'],8,9);
				$id_producto_entra=$row_detalle_factura_r['id_nuevo_producto'];
				$id_medida_entra=$row_detalle_factura_r['id_medida_anterior'];
				
				$detalle_producto=mysqli_query($con, "SELECT * FROM productos_servicios WHERE id='".$id_producto_entra."' ");
				$row_detalle_producto=mysqli_fetch_array($detalle_producto);
				$precio_producto_entra=$row_detalle_producto['precio_producto'];
				$codigo_producto_entra=$row_detalle_producto['codigo_producto'];
				
				$bodega_entra=$row_detalle_factura_r['id_bodega_anterior'];		
				$lote_entra=$row_detalle_factura_r['nuevo_lote'];
				$vencimiento_entra=date('Y-m-d', strtotime($row_detalle_factura_r['vencimiento_anterior']));
				$referencia	= "Devolución por re-cambio de producto según factura ".$serie_factura."-".$secuencial_factura;						
				}
				//para que el nombre del producto sea igual que en productos
				$nombre_producto=mysqli_query($con, "SELECT * FROM productos_servicios WHERE id = '".$id_producto_entra."' ");
				$row_nombre_producto = mysqli_fetch_array($nombre_producto);
				$nombre_producto_entra=$row_nombre_producto['nombre_producto'];
						

			$guarda_nueva_entrada_inventario=mysqli_query($con, "INSERT INTO inventarios VALUES (null, '".$ruc_empresa."','".$id_producto_entra."','".$precio_producto_entra."', '".$cantidad_entra."','0','".$fecha_registro."','".$vencimiento_entra."','".$referencia."','".$id_usuario."','".$id_medida_entra."','".$fecha_registro."','O','".$bodega_entra."','ENTRADA','".$codigo_producto_entra."','".$nombre_producto_entra."','0','OK','".$lote_entra."','".$codigo_unico."')");

		if ($guarda_nueva_entrada_inventario){
		echo "<script>
		$.notify('Registrado en inventario.','success');
		</script>";	
		} else
			{
		echo "<script>
		$.notify('Lo siento no se registro en el inventario.','error');
		</script>";
		}
			
	}	
	*/
?>