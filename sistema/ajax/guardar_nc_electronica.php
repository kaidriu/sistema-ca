<?php
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
	if (empty($_POST['fecha_nc_e'])) {
           $errors[] = "Ingrese fecha para la nota de crédito electrónica.";
		}else if (!date($_POST['fecha_nc_e'])) {
           $errors[] = "Ingrese fecha de nota de crédito correcta";
		}else if (empty($_POST['fecha_factura'])) {
           $errors[] = "Ingrese fecha de emisión de la factura.";
		}else if (!date($_POST['fecha_factura'])) {
           $errors[] = "Ingrese fecha de emisión de la factura correcta";
		}else if (empty($_POST['serie_nc_e'])) {
           $errors[] = "Seleccione serie para la nota de crédito electrónica.";
		}else if (empty($_POST['numero_factura'])) {
           $errors[] = "Ingrese factura a modificar por la nota de crédito electrónica.";
		}else if (empty($_POST['secuencial_nc_e'])) {
           $errors[] = "Ingrese un número de nota de crédito electrónica.";
		}else if (!is_numeric($_POST['secuencial_nc_e'])) {
           $errors[] = "Ingrese un número de nota de crédito electrónica.";
		}else if (empty($_POST['id_cliente'])) {
           $errors[] = "Ingrese cliente.";
		}else if (empty($_POST['motivo'])) {
           $errors[] = "Ingrese el motivo por el cual registra la nota de crédito.";		   
        } else if (!empty($_POST['fecha_nc_e']) && !empty($_POST['serie_nc_e'])  && !empty($_POST['secuencial_nc_e']) 
		&& !empty($_POST['motivo']) && !empty($_POST['numero_factura']) && !empty($_POST['id_cliente']))
		{

			$fecha_nc=date('Y-m-d H:i:s', strtotime($_POST['fecha_nc_e']));
			$fecha_factura=date('Y-m-d H:i:s', strtotime($_POST['fecha_factura']));
			$serie_nc=mysqli_real_escape_string($con,(strip_tags($_POST["serie_nc_e"],ENT_QUOTES)));
			$secuencial_nc=mysqli_real_escape_string($con,(strip_tags($_POST["secuencial_nc_e"],ENT_QUOTES)));
			$numero_factura=mysqli_real_escape_string($con,(strip_tags($_POST["numero_factura"],ENT_QUOTES)));
			$motivo=mysqli_real_escape_string($con,(strip_tags($_POST["motivo"],ENT_QUOTES)));
			$id_cliente=mysqli_real_escape_string($con,(strip_tags($_POST["id_cliente"],ENT_QUOTES)));
			$total_nc=mysqli_real_escape_string($con,(strip_tags($_POST["total_nc_e"],ENT_QUOTES)));
			$adicional_concepto="";//mysqli_real_escape_string($con,(strip_tags($_POST["adicional_concepto"],ENT_QUOTES)));
			$adicional_descripcion="";//mysqli_real_escape_string($con,(strip_tags($_POST["adicional_descripcion"],ENT_QUOTES)));
			$fecha_registro=date("Y-m-d H:i:s");
			session_start();
			$id_usuario = $_SESSION['id_usuario'];
			$ruc_empresa = $_SESSION['ruc_empresa'];
			
			$serie_factura = substr($numero_factura,0,7);
			$secuencial_factura =  substr($numero_factura,8,9);

			$tipo_nc = "ELECTRÓNICA";
			$estado_sri = "PENDIENTE";
			$referencia="Venta según factura: ".$serie_factura."-".str_pad($secuencial_factura,9,"000000000",STR_PAD_LEFT);

			//paraa buscar el cliente 
				$sql_cliente=mysqli_query($con, "SELECT * FROM clientes WHERE id = '".$id_cliente."' ");
				$row_cliente=mysqli_fetch_array($sql_cliente);
				$mail_cliente = $row_cliente['email'];
				$direccion_cliente = $row_cliente['direccion'];
			
				/*
				$busca_regimen = mysqli_query($con, "SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ");
				$datos_regimen = mysqli_fetch_array($busca_regimen);
				$negocio_popular=$datos_regimen['negocio_popular'];
				$regimen_rimpe=$datos_regimen['regimen_rimpe'];
				*/
		
		if (!empty($numero_factura) && ((strlen( $numero_factura)!=17) or (!is_numeric(substr($numero_factura,0,3))) or (!is_numeric(substr($numero_factura,4,3))) or (!is_numeric(substr($numero_factura,8,9))) or (substr($numero_factura,3,1) != "-") or (substr($numero_factura,7,1) !="-") )) {
			$errors []='Ingrese un número de nota de crédito correcto. ej: 001-001-000000001 '.mysqli_error($con);			
		}else{
									//paraa ver si la nota de credito que queremos registrar ya esta registrada	
									 $busca_empresa = "SELECT * FROM encabezado_nc WHERE ruc_empresa = '".$ruc_empresa."' and serie_nc = '".$serie_nc."' and secuencial_nc ='".$secuencial_nc."' and tipo_nc = 'ELECTRÓNICA'";
									 $result = $con->query($busca_empresa);
									 $count = mysqli_num_rows($result);
									 if ($count == 1){
									$errors []= "El número de nota de crédito que intenta guardar ya se encuentra registrado en el sistema.".mysqli_error($con);
									}else{
											$sql_nc_temporal=mysqli_query($con,"select * from factura_tmp where id_usuario = '".$id_usuario."'");
											$count=mysqli_num_rows($sql_nc_temporal);
											if ($count==0){
											$errors []= "No hay detalle de productos agregados a la nota de crédito.".mysqli_error($con);
											}else{
												
												//para guardar el encabezado de la nc
												$guarda_encabezado_nc="INSERT INTO encabezado_nc VALUES (null, '".$ruc_empresa."','".$fecha_nc."','".$serie_nc."','".$secuencial_nc."','".$numero_factura."','".$id_cliente."','".$fecha_registro."','".$tipo_nc."','".$estado_sri."', '".$total_nc."', '".$id_usuario."','0','0','','".$motivo."','PENDIENTE','".$fecha_factura."')";
												$query_encabezado_nc = mysqli_query($con,$guarda_encabezado_nc);
																							
												//para guardar detalles adicionales
												$query_guarda_detalle_adicional_nc = mysqli_query($con, "INSERT INTO detalle_adicional_nc VALUES (null, '".$ruc_empresa."','".$serie_nc."','".$secuencial_nc."','Email','".$mail_cliente."')");
												$query_guarda_detalle_adicional_nc = mysqli_query($con, "INSERT INTO detalle_adicional_nc VALUES (null, '".$ruc_empresa."','".$serie_nc."','".$secuencial_nc."','Dirección','".$direccion_cliente."')");
													
												/*
												if($negocio_popular=='SI'){ 
													$detalle_adicional_uno = mysqli_query($con, "INSERT INTO detalle_adicional_nc VALUES (null, '".$ruc_empresa."','".$serie_nc."','".$secuencial_nc."', 'Contribuyente','Negocio Popular - Régimen RIMPE')");
													}
													if($regimen_rimpe=='SI'){
													$detalle_adicional_uno = mysqli_query($con, "INSERT INTO detalle_adicional_nc VALUES (null, '".$ruc_empresa."','".$serie_nc."','".$secuencial_nc."', 'Contribuyente','Régimen RIMPE')");
													}
													*/
										
										//para guardar el detalle de la nc
										while ($row_detalle=mysqli_fetch_array($sql_nc_temporal)){
												$cantidad_nc=str_replace(",",".",$row_detalle["cantidad_tmp"]);
												$precio_venta=str_replace(",",".",$row_detalle['precio_tmp']);
												$subtotal_nc=number_format(str_replace(",",".",$precio_venta*$cantidad_nc),2,'.','');//Precio total formateado
												$tipo_produccion=$row_detalle['tipo_produccion'];
												$tarifa_iva=$row_detalle['tarifa_iva'];
												$tarifa_ice=$row_detalle['tarifa_ice'];
												$tarifa_bp=$row_detalle['tarifa_botellas'];
												$descuento=$row_detalle['descuento'];
												$id_producto=$row_detalle['id_producto'];
										//para ver el nombre del producto
												$busca_nombre_producto = "SELECT * FROM productos_servicios WHERE id=$id_producto ";
												$result_nombre_producto = $con->query($busca_nombre_producto);
												$datos_nombre_producto = mysqli_fetch_array($result_nombre_producto);
												$codigo_producto= strtoupper ($datos_nombre_producto['codigo_producto']);
												$nombre_producto= strtoupper ($datos_nombre_producto['nombre_producto']);

												$guarda_detalle_nc=mysqli_query($con, "INSERT INTO cuerpo_nc VALUES (null, '".$ruc_empresa."','".$serie_nc."','".$secuencial_nc."','".$id_producto."','".$cantidad_nc."','".$precio_venta."','".$subtotal_nc."','".$tipo_produccion."','".$tarifa_iva."','".$tarifa_ice."','".$tarifa_bp."','".$descuento."','".$codigo_producto."','".$nombre_producto."')");
											}
								
												if ($query_encabezado_nc && $guarda_detalle_nc && $query_guarda_detalle_adicional_nc ){
													echo "<script>
													$.notify('Nota de crédito guardada.','success');
													setTimeout(function (){location.href ='../modulos/notas_de_credito.php'}, 1000);
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
		
		}
		else{
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