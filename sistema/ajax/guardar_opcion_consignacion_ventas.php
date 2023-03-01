<?php
include("../validadores/generador_codigo_unico.php");
	if (empty($_POST['fecha_opcion_consignacion_salida'])) {
           $errors[] = "Ingrese fecha.";
		}else if (!date($_POST['fecha_opcion_consignacion_salida'])) {
           $errors[] = "Ingrese una fecha correcta.";
		}else if (empty($_POST['id_cliente_opcion_consignacion_venta'])) {
           $errors[] = "Seleccione un cliente.";
        }else if ( (!empty($_POST['fecha_opcion_consignacion_salida'])) && (!empty($_POST['id_cliente_opcion_consignacion_venta']))){
					
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		
		$fecha_consignacion_salida=date('Y-m-d H:i:s', strtotime(mysqli_real_escape_string($con,(strip_tags($_POST["fecha_opcion_consignacion_salida"],ENT_QUOTES)))));
		$id_cliente_consignacion_venta=mysqli_real_escape_string($con,(strip_tags($_POST["id_cliente_opcion_consignacion_venta"],ENT_QUOTES)));
		$opcion_salida=mysqli_real_escape_string($con,(strip_tags($_POST["opcion_salida"],ENT_QUOTES)));
		$observacion_consignacion_venta=mysqli_real_escape_string($con,(strip_tags($_POST["observacion_opcion_consignacion_venta"],ENT_QUOTES)));
		$inventario=mysqli_real_escape_string($con,(strip_tags($_POST["inventario"],ENT_QUOTES)));
		$serie_sucursal=mysqli_real_escape_string($con,(strip_tags($_POST["serie_opcion_consignacion"],ENT_QUOTES)));
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date("Y-m-d H:i:s");
		$codigo_unico=codigo_unico(20);

		$sql_factura_temporal=mysqli_query($con, "SELECT fat_tmp.tarifa_iva as numero_consignacion, pro_ser.precio_producto as precio, fat_tmp.id_producto as id_producto, pro_ser.codigo_producto as codigo_producto, fat_tmp.cantidad_tmp as cantidad, pro_ser.nombre_producto as nombre_producto, fat_tmp.id_bodega as bodega, fat_tmp.vencimiento as vencimiento, fat_tmp.lote as lote, fat_tmp.id_medida as medida FROM factura_tmp as fat_tmp INNER JOIN productos_servicios as pro_ser ON fat_tmp.id_producto = pro_ser.id WHERE fat_tmp.id_usuario = '".$id_usuario."' ");
		$count=mysqli_num_rows($sql_factura_temporal);
		if ($count==0){
		$errors []= "No hay detalle de productos.".mysqli_error($con);
		}else{
		$consulta_ultima_orden=mysqli_query($con, "SELECT max(numero_consignacion) as ultimo FROM encabezado_consignacion WHERE ruc_empresa='".$ruc_empresa."' and tipo_consignacion='VENTA' and operacion !='ENTRADA'");
		$row_ultimo=mysqli_fetch_array($consulta_ultima_orden);
		$siguiente_orden=$row_ultimo['ultimo']+1;
		$opcion_salida=$opcion_salida=='1'?"DEVOLUCIÓN":"FACTURA";
		
		if ($opcion_salida=='FACTURA'){
		$numero_factura = siguiente_factura($con, $ruc_empresa, $serie_sucursal);
		}else{
		$numero_factura ='';
		}		
		
		$encabezado_consignacion=mysqli_query($con, "INSERT INTO encabezado_consignacion VALUES (null,'".$fecha_consignacion_salida."','".$ruc_empresa."','".$codigo_unico."','".$id_cliente_consignacion_venta."','VENTA','".$siguiente_orden."','".$observacion_consignacion_venta."','".$fecha_registro."','".$id_usuario."','','','', '".$opcion_salida."','".$serie_sucursal."','".$numero_factura."')");
			while ($row_detalle=mysqli_fetch_array($sql_factura_temporal)){
				$id_producto=$row_detalle['id_producto'];
				$codigo_producto=$row_detalle['codigo_producto'];
				$nombre_producto=$row_detalle['nombre_producto'];
				$cantidad=$row_detalle['cantidad'];	
				$bodega=$row_detalle['bodega'];
				$medida=$row_detalle['medida'];				
				$lote=$row_detalle['lote'];
				$precio=$row_detalle['precio'];
				$numero_consignacion=$row_detalle['numero_consignacion'];
						
				$busca_vencimiento=mysqli_query($con, "SELECT * FROM inventarios WHERE id_producto = '".$id_producto."' and lote= '".$lote."' and operacion='ENTRADA'");
				$row_vencimiento = mysqli_fetch_array($busca_vencimiento);
				$vencimiento=date('y-m-d', strtotime($row_vencimiento['fecha_vencimiento']));

				$detalle_consignacion=mysqli_query($con, "INSERT INTO detalle_consignacion VALUES (null,'".$id_producto."','".$codigo_producto."','".$nombre_producto."','".$lote."','".$vencimiento."','".$bodega."','".$medida."','".$ruc_empresa."','".$codigo_unico."','".$cantidad."','".$numero_consignacion."')");
			}
			
			
			echo "<script>$.notify('Registro guardado.','success');
				setTimeout(function (){location.reload()}, 1000);
				</script>";
		
			if ($opcion_salida=='FACTURA'){
			echo generar_factura($con, $ruc_empresa, $id_usuario, $serie_sucursal, $codigo_unico, $id_cliente_consignacion_venta, $observacion_consignacion_venta, $numero_factura);
			}
			
			if ($opcion_salida=='DEVOLUCIÓN' && $inventario=="SI"){
			echo regresar_inventario($con, $ruc_empresa, $codigo_unico, $id_usuario);
			}
			
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
			
	function siguiente_factura($con, $ruc_empresa, $serie_facturar){
			//traer el numero de factura que continua 
			$busca_factura = "SELECT MAX(secuencial_factura) as maximo FROM encabezado_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura = '".$serie_facturar."' and tipo_factura = 'ELECTRÓNICA'";
			$result = $con->query($busca_factura);
			$res_sql = mysqli_fetch_assoc($result);
			$factura_final = $res_sql['maximo']+1;
			return $factura_final;
	}
	
	
	function generar_factura($con, $ruc_empresa, $id_usuario, $serie_facturar, $codigo_unico, $id_cliente, $observaciones, $numero_factura){

			ini_set('date.timezone','America/Guayaquil');
			$fecha_registro=date("Y-m-d H:i:s");
			$sql_detalle_por_facturar=mysqli_query($con,"SELECT * FROM detalle_consignacion WHERE codigo_unico = '".$codigo_unico."'");
			
			$iva=array();
			$subtotal=array();
			$numero_consignacion=array();
			while ($row_detalle=mysqli_fetch_array($sql_detalle_por_facturar)){
					$id_producto=$row_detalle['id_producto'];
					$codigo_producto=$row_detalle['codigo_producto'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$cantidad=$row_detalle['cant_consignacion'];	
					$id_bodega=$row_detalle['id_bodega'];
					$id_medida=$row_detalle['id_medida'];				
					$lote=$row_detalle['lote'];
					$vencimiento=$row_detalle['vencimiento'];
					
					$numero_consignacion[]=$row_detalle['numero_orden_entrada'];

						//para traer tipo de tarifas
						$sql_tarifas=mysqli_query($con, "SELECT * FROM productos_servicios WHERE id= '".$id_producto."' ");
						$row_tarifas=mysqli_fetch_array($sql_tarifas);
						$tipo_produccion=$row_tarifas['tipo_produccion'];
						$tarifa_iva=$row_tarifas['tarifa_iva'];
						$tarifa_ice=$row_tarifas['tarifa_ice'];
						$tarifa_bp=$row_tarifas['tarifa_botellas'];
						$precio=$row_tarifas['precio_producto'];
						$subtotal_item=$cantidad*$precio;
						$subtotal[]=$cantidad*$precio;
						if ($tarifa_iva=='2'){
							$iva[]=$subtotal_item*0.12;
						}
						
						
						$guarda_detalle_factura=mysqli_query($con, "INSERT INTO cuerpo_factura VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','".$id_producto."','".$cantidad."','".$precio."','".$subtotal_item."','".$tipo_produccion."','".$tarifa_iva."','".$tarifa_ice."','".$tarifa_bp."','0','".$codigo_producto."','".$nombre_producto."','".$id_medida."','".$lote."','".$vencimiento."','".$id_bodega."')");
			
				}
				
				$total_factura=array_sum($subtotal) + array_sum($iva);

				$quitar_repetidos = array_unique($numero_consignacion);
				$ordenes_consignacion = implode("-", $quitar_repetidos);

				//para guardar encabezado de factura
				$guarda_encabezado_factura=mysqli_query($con, "INSERT INTO encabezado_factura VALUES (null, '".$ruc_empresa."','".$fecha_registro."','".$serie_facturar."','".$numero_factura."','".$id_cliente."','','','".$fecha_registro."','POR COBRAR','ELECTRÓNICA','PENDIENTE', '".$total_factura."', '".$id_usuario."','0','0','','PENDIENTE',0,0)");

				//para guardar la forma de pago de la factura
				$query_forma_pago_factura= mysqli_query($con,"INSERT INTO formas_pago_ventas VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','20', '".$total_factura."')");

				$sql_cliente=mysqli_query($con, "SELECT * FROM clientes WHERE id= '".$id_cliente."' ");
				$row_clientes=mysqli_fetch_array($sql_cliente);
				// para guardar detalle adicional factura
				$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','Email','".$row_clientes['email']."')");
				$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','Dirección','".$row_clientes['direccion']."')");
				$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','Teléfono','".$row_clientes['telefono']."')");
				$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','No. CV','".$ordenes_consignacion."')");
				$query_guarda_detalle_adicional_factura = mysqli_query($con, "INSERT INTO detalle_adicional_factura VALUES (null, '".$ruc_empresa."','".$serie_facturar."','".$numero_factura."','Observaciones','".$observaciones."')");
		

		if ($guarda_encabezado_factura && $guarda_detalle_factura && $query_forma_pago_factura && $query_guarda_detalle_adicional_factura){
		echo "<script>
		$.notify('Factura guardada.','success');
		</script>";	
		} else
			{
		echo "<script>
		$.notify('Lo siento la factura no se guardo.','error');
		</script>";
		}
	
	}	

function regresar_inventario($con, $ruc_empresa, $codigo_unico, $id_usuario){

			ini_set('date.timezone','America/Guayaquil');
			$fecha_registro=date("Y-m-d H:i:s");
			$sql_regresa_inventario=mysqli_query($con,"SELECT * FROM detalle_consignacion WHERE codigo_unico = '".$codigo_unico."'");
			
			while ($row_detalle=mysqli_fetch_array($sql_regresa_inventario)){
					$id_producto=$row_detalle['id_producto'];
					$codigo_producto=$row_detalle['codigo_producto'];
					$nombre_producto=$row_detalle['nombre_producto'];
					$cantidad=$row_detalle['cant_consignacion'];	
					$id_bodega=$row_detalle['id_bodega'];
					$id_medida=$row_detalle['id_medida'];				
					$lote=$row_detalle['lote'];
					$vencimiento=$row_detalle['vencimiento'];
					$numero_consignacion=$row_detalle['numero_orden_entrada'];
					
					$sql_producto=mysqli_query($con,"SELECT * FROM productos_servicios WHERE id = '".$id_producto."'");
					$row_productos=mysqli_fetch_array($sql_producto);
					$precio=$row_productos['precio_producto'];
						
					$guarda_entrada_inventario=mysqli_query($con, "INSERT INTO inventarios VALUES (null, '".$ruc_empresa."','".$id_producto."','".$precio."','".$cantidad."','0','".$fecha_registro."','".$vencimiento."','Devolución en consignación por ventas','".$id_usuario."','".$id_medida."','".$fecha_registro."','A','".$id_bodega."','ENTRADA','".$codigo_producto."','".$nombre_producto."','0','OK','".$lote."','".$codigo_unico."')");
				}
					

		if ($guarda_entrada_inventario ){
		echo "<script>
		$.notify('Devolución realizada.','success');
		</script>";	
		} else
			{
		echo "<script>
		$.notify('Lo siento la devolución no se registro.','error');
		</script>";
		}
	
	}	
?>