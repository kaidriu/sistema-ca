<?php
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		include("../validadores/generador_codigo_unico.php");
		$con = conenta_login();
	if (empty($_POST['fecha_liquidacion'])) {
           $errors[] = "Ingrese fecha para la liquidación electrónica.";
		}else if (!date($_POST['fecha_liquidacion'])) {
           $errors[] = "Ingrese fecha correcta.";
		}else if (empty($_POST['serie_liquidacion'])) {
           $errors[] = "Seleccione serie para la liquidación electrónica.";
		}else if (empty($_POST['secuencial_liquidacion'])) {
           $errors[] = "Ingrese un número de liquidación electrónica.";
		}else if (!is_numeric($_POST['secuencial_liquidacion'])) {
           $errors[] = "Ingrese un número de liquidación electrónica.";
		}else if (empty($_POST['id_proveedor_lc'])) {
           $errors[] = "Seleccione un cliente para la liquidación electrónica.";
		}else if (empty($_POST['forma_pago_lc'])) {
           $errors[] = "Seleccione una forma de pago.";							
        } else if (!empty($_POST['fecha_liquidacion']) && !empty($_POST['serie_liquidacion'])  && !empty($_POST['secuencial_liquidacion']) 
		&& !empty($_POST['id_proveedor_lc'])&& !empty($_POST['forma_pago_lc']))
		{
			ini_set('date.timezone','America/Guayaquil');
			$fecha_liquidacion=date('Y-m-d H:i:s', strtotime($_POST['fecha_liquidacion']));
			$serie_liquidacion=mysqli_real_escape_string($con,(strip_tags($_POST["serie_liquidacion"],ENT_QUOTES)));
			$secuencial_liquidacion=mysqli_real_escape_string($con,(strip_tags($_POST["secuencial_liquidacion"],ENT_QUOTES)));
			$id_proveedor_lc=mysqli_real_escape_string($con,(strip_tags($_POST["id_proveedor_lc"],ENT_QUOTES)));
			$forma_pago_lc=mysqli_real_escape_string($con,(strip_tags($_POST["forma_pago_lc"],ENT_QUOTES)));	
			$total_lc=mysqli_real_escape_string($con,(strip_tags($_POST["total_lc"],ENT_QUOTES)));
			$adicional_concepto="";
			$adicional_descripcion="";
			session_start();
			$id_usuario = $_SESSION['id_usuario'];
			$ruc_empresa = $_SESSION['ruc_empresa'];
				//para ver si la liquidación que queremos registrar ya esta registrada	
				 $busca_liquidacion = mysqli_query($con, "SELECT * FROM encabezado_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion = '".$serie_liquidacion."' and secuencial_liquidacion ='".$secuencial_liquidacion."' ");
				 $count = mysqli_num_rows($busca_liquidacion);
				 if ($count == 1){
				$errors []= "El número de liquidación que intenta guardar ya se encuentra registrado en el sistema.".mysqli_error($con);
				}else{
						$sql_lc_temporal=mysqli_query($con,"select * from factura_tmp where id_usuario = '".$id_usuario."'");
						$count_tmp=mysqli_num_rows($sql_lc_temporal);
						if ($count_tmp==0){
						$errors []= "No hay detalle de productos o servicios agregados a la liquidación.".mysqli_error($con);
						}else{
					//para guardar el encabezado de la liquidación
				//compruebo el total de la liquidacion con el total que viene
				$sql_subtotal=mysqli_query($con, "select round(sum((cantidad_tmp * precio_tmp)-descuento),2) as subtotal FROM factura_tmp WHERE id_usuario = '". $id_usuario ."' group by id_usuario ");
				$row_subtotal=mysqli_fetch_array($sql_subtotal);
				$subtotal=$row_subtotal['subtotal'];

				$sql_iva=mysqli_query($con, "select round(sum(((ft.cantidad_tmp * ft.precio_tmp) - ft.descuento) * (ti.tarifa /100)),2) as total_iva FROM factura_tmp ft INNER JOIN tarifa_iva ti ON ti.codigo = ft.tarifa_iva WHERE ft.id_usuario = '". $id_usuario ."' and ti.codigo = '2' group by ft.tarifa_iva " );
				$row_iva=mysqli_fetch_array($sql_iva);
				$total_iva=$row_iva['total_iva'];
				$total_liquidacion=number_format($subtotal+$total_iva,2,'.','');

				if($total_liquidacion != $total_lc){
				$errors []= "El total de la liquidación no coincide con el total de los items calculados, agregue o elimine un item para recalcular.".mysqli_error($con);
				}else{
				$codigo_unico=codigo_unico(20);
					$guarda_encabezado_liquidacion= mysqli_query($con,"INSERT INTO encabezado_liquidacion VALUES (null, '".$ruc_empresa."','".$fecha_liquidacion."','".$serie_liquidacion."','".$secuencial_liquidacion."','".$id_proveedor_lc."','".date('Y-m-d')."','PENDIENTE', '".$total_lc."', '".$id_usuario."','0','0','0','PENDIENTE','".$codigo_unico."')");
					
					//para guardar la forma de pago de la liquidación
					$guarda_forma_pago= mysqli_query($con, "INSERT INTO formas_pago_liquidacion VALUES (null, '".$ruc_empresa."','".$serie_liquidacion."','".$secuencial_liquidacion."', '".$forma_pago_lc."', '".$total_lc."','".$codigo_unico."')");
					
					//para guardar detalle adicional de la liquidación
					$busca_adicional_tmp = mysqli_query($con, "SELECT * FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_liquidacion."' and secuencial_factura = '".$secuencial_liquidacion."'");
														
					while ($row_detalle_adicional=mysqli_fetch_array($busca_adicional_tmp)){
					$concepto=$row_detalle_adicional['concepto'];
					$detalle=$row_detalle_adicional['detalle'];
					$query_guarda_detalle_adicional_lc = mysqli_query($con, "INSERT INTO detalle_adicional_liquidacion VALUES (null, '".$ruc_empresa."','".$serie_liquidacion."','".$secuencial_liquidacion."','".$concepto."','".$detalle."','".$codigo_unico."')");
					}
					
					//para guardar el detalle de la liquidación
					while ($row_detalle=mysqli_fetch_array($sql_lc_temporal)){
							$cantidad_lc=str_replace(",",".",$row_detalle["cantidad_tmp"]);
							$precio_lc=str_replace(",",".",$row_detalle['precio_tmp']);
							$subtotal_lc=number_format(str_replace(",",".",$precio_lc*$cantidad_lc),2,'.','');//Precio total formateado
							$tarifa_iva=$row_detalle['tarifa_iva'];
							$descuento=$row_detalle['descuento'];
							$codigo=$row_detalle['id_producto'];
							$detalle=$row_detalle['lote'];
							$guarda_detalle_liquidacion=mysqli_query($con, "INSERT INTO cuerpo_liquidacion VALUES (null, '".$ruc_empresa."','".$serie_liquidacion."','".$secuencial_liquidacion."','".$cantidad_lc."','".$precio_lc."','".$subtotal_lc."','".$tarifa_iva."','".$descuento."','".$codigo."','".$detalle."','".$codigo_unico."')");
						}
						if ($guarda_encabezado_liquidacion && $guarda_detalle_liquidacion && $guarda_forma_pago && $query_guarda_detalle_adicional_lc ){
							echo "<script>
							$.notify('Liquidación guardada con éxito','success');
							setTimeout(function (){location.href ='../modulos/liquidacion_compra_servicio.php'}, 1000);
							</script>";									
							} else
								{
								echo "<script>
							$.notify('Lo siento algo ha salido mal intenta nuevamente','error');
							</script>";	
								}
				}
			}
	}		
		
		}else{
			$errors []= "Error desconocido.";
			}
			
		if (isset($errors))
			{
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>Atención! </strong> 
					<?php
						foreach ($errors as $error) 
						{
							echo $error;
						}
					?>
			</div>
			<?php
			}
			if (isset($messages))
			{
				
			?>
			<div class="alert alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>¡Bien hecho! </strong>
					<?php
						foreach ($messages as $message) 
						{
							echo $message;
						}
					?>
			</div>
			<?php
			}
			?>
