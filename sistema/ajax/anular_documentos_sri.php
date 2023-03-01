<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	include("../clases/anular_registros.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	ini_set('date.timezone','America/Guayaquil');
	$fecha_registro=date("Y-m-d H:i:s");
	$anular_asiento_contable = new anular_registros();
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
//para anular una liquidacion
if($action == 'anular_liquidacion'){
if (!isset($_POST['id_documento_modificar'])){
		$errors[] = "Seleccione un documento para anular.";
		} else if (!empty($_POST['id_documento_modificar'])){

				$id_usuario = $_SESSION['id_usuario'];
				$id_liquidacion=$_POST['id_documento_modificar'];
				$estado_autorizacion=$_POST['estado_sri_consultado'];
								
				$busca_datos_liquidacion = "SELECT * FROM encabezado_liquidacion WHERE id_encabezado_liq = '".$id_liquidacion."' ";
				$result = $con->query($busca_datos_liquidacion);
				$datos_liquidacion = mysqli_fetch_array($result);
				$serie_liquidacion =$datos_liquidacion['serie_liquidacion'];
				$secuencial =$datos_liquidacion['secuencial_liquidacion'];
				$id_registro_contable=$datos_liquidacion['id_registro_contable'];
				$anio_documento=date("Y", strtotime($datos_liquidacion['fecha_liquidacion']));
				
				$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
				if ($resultado_anular_documento=="NO"){
				echo "<script>
					$.notify('Primero se debe anular el asiento contable','error');
					$('#anular_sri').attr('disabled', false);
					setTimeout(function () {location.reload()}, 60 * 20);
					</script>";
				exit;
				}

				//anular la factura y los datos de la factura
				if ($estado_autorizacion=="AUTORIZADO"){
						echo "<script>
						$.notify('Primero se debe anular el documento en el SRI','error');
						$('#anular_sri').attr('disabled', false);
						setTimeout(function () {location.reload()}, 60 * 20);
						</script>";
					}else{
						$delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion = '".$serie_liquidacion."' and secuencial_liquidacion = '".$secuencial."'");
						$delete_pago=mysqli_query($con,"DELETE FROM formas_pago_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion = '".$serie_liquidacion."' and secuencial_liquidacion = '".$secuencial."'");
						$delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_liquidacion WHERE ruc_empresa = '".$ruc_empresa."' and serie_liquidacion = '".$serie_liquidacion."' and secuencial_liquidacion = '".$secuencial."'");
						$anular=mysqli_query($con,"UPDATE encabezado_liquidacion SET estado_sri='ANULADA', total_liquidacion ='0.00', id_usuario= '".$id_usuario."' WHERE id_encabezado_liq = '".$id_liquidacion."' ");		
																	
						if ($delete_detalle && $delete_pago && $delete_adicional && $anular){
								echo "<script>
							$.notify('Liquidación anulada exitosamente','success');
							$('#anular_sri').attr('disabled', false);
							setTimeout(function () {location.reload()}, 60 * 20);
							</script>";
						}else {
							$errors []= "Lo siento algo ha salido mal intenta nuevamente.";
						}
					}
		} else {
		$errors []= "Error desconocido, intente de nuevo.";
		}
	}
	
	
//para anular una factura
if($action == 'anular_factura'){
if (!isset($_POST['id_documento_modificar'])){
		$errors[] = "Seleccione un documento para anular.";
		} else if (!empty($_POST['id_documento_modificar'])){
				$id_usuario = $_SESSION['id_usuario'];
				$id_factura=$_POST['id_documento_modificar'];
				$estado_autorizacion=$_POST['estado_sri_consultado'];
				
				$datos_encabezado=mysqli_query($con,"SELECT * FROM encabezado_factura WHERE id_encabezado_factura = '".$id_factura."' ");
				$row_encabezado=mysqli_fetch_array($datos_encabezado);
				$serie_factura=$row_encabezado['serie_factura'];
				$secuencial=$row_encabezado['secuencial_factura'];
				$numero_documento=str_ireplace("-","",$serie_factura).str_pad($secuencial,9,"000000000",STR_PAD_LEFT);
				$id_registro_contable=$row_encabezado['id_registro_contable'];
				$anio_documento=date("Y", strtotime($row_encabezado['fecha_factura']));
				$id_cliente=$row_encabezado['id_cliente'];
				
				$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
				if ($resultado_anular_documento=="NO"){
				echo "<script>
					$.notify('Primero se debe anular el asiento contable','error');
					$('#anular_sri').attr('disabled', false);
					</script>";
				exit;
				}
				if ($estado_autorizacion=="AUTORIZADO"){
				echo "<script>
					$.notify('Primero se debe anular el documento en el SRI','error');
					$('#anular_sri').attr('disabled', false);
					setTimeout(function () {location.reload()}, 60 * 20);
					</script>";
				}else{
					$id_documento_venta=$serie_factura."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT);
					//$referencia="Venta según factura: ".$serie_factura."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT);
					$referencia_modificada="Factura anulada: ".$serie_factura."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT);
					//anular la factura y los datos de la factura
					$datos_encabezado_ret=mysqli_query($con,"SELECT * FROM encabezado_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and id_cliente='".$id_cliente."' and numero_documento = '".$numero_documento."'");
					$row_encabezado_ret=mysqli_fetch_array($datos_encabezado_ret);
					$registro_contable=$row_encabezado_ret['id_registro_contable'];
					$resultado_anular_contable_ret_venta=$anular_asiento_contable->anular_asiento_contable($con, $registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
					
						$delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial."'");
						$delete_pago=mysqli_query($con,"DELETE FROM formas_pago_ventas WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial."'");
						$delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial."'");
						$update_inventario=mysqli_query($con,"UPDATE inventarios SET cantidad_salida='0', precio='0', fecha_registro='".$fecha_registro."',referencia='".$referencia_modificada."',id_usuario='".$id_usuario."', fecha_agregado='".$fecha_registro."' WHERE ruc_empresa = '".$ruc_empresa."' and operacion='SALIDA' and id_documento_venta='".$id_documento_venta."'");
						$anular=mysqli_query($con,"UPDATE encabezado_factura SET observaciones_factura='ANULADA', estado_sri='ANULADA', total_factura ='0.00', id_usuario= '".$id_usuario."' WHERE id_encabezado_factura = '".$id_factura."' ");	
						$delete_enc_retencion_venta=mysqli_query($con,"DELETE FROM encabezado_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and id_cliente='".$id_cliente."' and numero_documento = '".$numero_documento."'");
						$delete_cue_retencion_venta=mysqli_query($con,"DELETE FROM cuerpo_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and numero_documento = '".$numero_documento."'");
						
						$buscar_ingresos = mysqli_query($con, "SELECT ing_egr.fecha_ing_egr as fecha_ingreso, det_ing_egr.codigo_documento as codigo_documento, ing_egr.codigo_contable as codigo_contable FROM detalle_ingresos_egresos as det_ing_egr INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=det_ing_egr.codigo_documento WHERE det_ing_egr.codigo_documento_cv = '".$id_factura."' and det_ing_egr.tipo_documento='INGRESO'");
						while ($det_ingresos = mysqli_fetch_array($buscar_ingresos)){
						//para anular el asiento contable del ingreso
						$codigo_contable = $det_ingresos['codigo_contable'];
						$codigo_unico = $det_ingresos['codigo_documento'];
						$anio_ingreso = date("Y", strtotime($det_ingresos['fecha_ingreso']));
						$resultado_anular_documento = $anular_asiento_contable->anular_asiento_contable($con, $codigo_contable, $ruc_empresa, $id_usuario, $anio_ingreso);
						//para anular los registros de ingresos
						$anular_ingreso = mysqli_query($con, "UPDATE ingresos_egresos SET detalle_adicional='ANULADO', valor_ing_egr='0.00' WHERE codigo_documento = '" . $codigo_unico . "' and tipo_ing_egr='INGRESO'");
						$delete_detalle_ingreso = mysqli_query($con, "DELETE FROM detalle_ingresos_egresos WHERE codigo_documento = '" . $codigo_unico . "' and tipo_documento='INGRESO'");
						$delete_pagos_ingreso = mysqli_query($con, "DELETE FROM formas_pagos_ing_egr WHERE codigo_documento = '" . $codigo_unico . "' and tipo_documento='INGRESO' ");
						}
						
						if ($delete_detalle && $delete_pago && $delete_adicional && $update_inventario && $anular){
							echo "<script>
							$.notify('Factura anulada exitosamente','success');
							$('#anular_sri').attr('disabled', false);
							setTimeout(function () {location.reload()}, 60 * 20);
							</script>";
					}else {
						$errors []= "Lo siento algo ha salido mal intenta nuevamente.";
					}
				}
		}else{
		$errors []= "Error desconocido, intente de nuevo.";
		}
	}

//para anular una nota de credito
if($action == 'anular_nc'){
if (!isset($_POST['id_documento_modificar'])){
		$errors[] = "Seleccione un documento para anular.";
		} else if (!empty($_POST['id_documento_modificar'])){

				$id_usuario = $_SESSION['id_usuario'];
				$id_nc=$_POST['id_documento_modificar'];
				$estado_autorizacion=$_POST['estado_sri_consultado'];
				
				$busca_datos_nc = "SELECT * FROM encabezado_nc WHERE id_encabezado_nc = '".$id_nc."' ";
				$result = $con->query($busca_datos_nc);
				$datos_nc = mysqli_fetch_array($result);
				$serie_nc =$datos_nc['serie_nc'];
				$secuencial =$datos_nc['secuencial_nc'];
				$id_registro_contable=$datos_nc['id_registro_contable'];
				$anio_documento=date("Y", strtotime($datos_nc['fecha_nc']));
				
				$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
				if ($resultado_anular_documento=="NO"){
				echo "<script>
					$.notify('Primero se debe anular el asiento contable','error');
					$('#anular_sri').attr('disabled', false);
					</script>";
				exit;
				}
				
				if ($estado_autorizacion=="AUTORIZADO"){
					echo "<script>
					$.notify('Primero se debe anular el documento en el SRI','error');
					$('#anular_sri').attr('disabled', false);
					setTimeout(function () {location.reload()}, 60 * 20);
					</script>";
				}else{
				//anular la nc y los datos de la factura
					$delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_nc WHERE ruc_empresa = '".$ruc_empresa."' and serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial."'");
					$delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_nc WHERE ruc_empresa = '".$ruc_empresa."' and serie_nc = '".$serie_nc."' and secuencial_nc = '".$secuencial."'");
					$anular=mysqli_query($con,"UPDATE encabezado_nc SET estado_sri='ANULADA', total_nc ='0.00', id_usuario= '".$id_usuario."' WHERE id_encabezado_nc = '".$id_nc."' ");	
						if ($delete_detalle && $delete_adicional && $anular){
						echo "<script>
						$.notify('Nota de crédito anulada exitosamente','success');
						$('#anular_sri').attr('disabled', false);
						setTimeout(function () {location.reload()}, 60 * 20);
						</script>";
					}else {
						$errors []= "Lo siento algo ha salido mal intenta nuevamente.";
					}
				}
			} else {
			$errors []= "Error desconocido, intente de nuevo.";
			}
	}

//para anular una retencion
if($action == 'anular_retencion'){
if (!isset($_POST['id_documento_modificar'])){
		$errors[] = "Seleccione un documento para anular.";
		} else if (!empty($_POST['id_documento_modificar'])){
				$id_usuario = $_SESSION['id_usuario'];
				$id_retencion=$_POST['id_documento_modificar'];
				$estado_autorizacion=$_POST['estado_sri_consultado'];
				
				$busca_datos_retencion = "SELECT * FROM encabezado_retencion WHERE id_encabezado_retencion = '".$id_retencion."' ";
				$result = $con->query($busca_datos_retencion);
				$datos_retencion = mysqli_fetch_array($result);
				$serie_retencion =$datos_retencion['serie_retencion'];
				$secuencial =$datos_retencion['secuencial_retencion'];
				$id_registro_contable=$datos_retencion['id_registro_contable'];
				$anio_documento=date("Y", strtotime($datos_retencion['fecha_emision']));
				
				$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
				if ($resultado_anular_documento=="NO"){
				echo "<script>
					$.notify('Primero se debe anular el asiento contable','error');
					$('#anular_sri').attr('disabled', false);
					</script>";
				exit;
				}

				if ($estado_autorizacion=="AUTORIZADO"){
					echo "<script>
					$.notify('Primero se debe anular el documento en el SRI','error');
					$('#anular_sri').attr('disabled', false);
					setTimeout(function () {location.reload()}, 60 * 20);
					</script>";
				}else{
				//anular la retencion y los datos de la factura
						$delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_retencion WHERE ruc_empresa = '".$ruc_empresa."' and serie_retencion = '".$serie_retencion."' and secuencial_retencion = '".$secuencial."'");
						$delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_retencion WHERE ruc_empresa = '".$ruc_empresa."' and serie_retencion = '".$serie_retencion."' and secuencial_retencion = '".$secuencial."'");
						$anular=mysqli_query($con,"UPDATE encabezado_retencion SET estado_sri='ANULADA', total_retencion ='0.00', id_usuario= '".$id_usuario."' WHERE id_encabezado_retencion = '".$id_retencion."' ");		
							if ($delete_detalle && $delete_adicional && $anular){
								echo "<script>
								$.notify('Retención anulada exitosamente','success');
								setTimeout(function () {location.reload()}, 60 * 20);
								</script>";
						}else {
							$errors []= "Lo siento algo ha salido mal intenta nuevamente.";
						}
				}
			} else {
			$errors []= "Error desconocido, intente de nuevo.";
			}
	}

	//para anular una guia de remision
if($action == 'anular_guia'){
if (!isset($_POST['id_documento_modificar'])){
		$errors[] = "Seleccione un documento para anular.";
		} else if (!empty($_POST['id_documento_modificar'])){
				$id_usuario = $_SESSION['id_usuario'];
				$id_guia=$_POST['id_documento_modificar'];
				$estado_autorizacion=$_POST['estado_sri_consultado'];
				
				$busca_datos_guia = "SELECT * FROM encabezado_gr WHERE id_encabezado_gr = '".$id_guia."' ";
				$result = $con->query($busca_datos_guia);
				$datos_guia = mysqli_fetch_array($result);
				$serie_guia =$datos_guia['serie_gr'];
				$secuencial =$datos_guia['secuencial_gr'];
				if ($estado_autorizacion=="AUTORIZADO"){
					echo "<script>
					$.notify('Primero se debe anular el documento en el SRI','error');
					$('#anular_sri').attr('disabled', false);
					setTimeout(function () {location.reload()}, 60 * 20);
					</script>";
				}else{
				//anular la guia y los datos de la guia
					$delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_gr WHERE ruc_empresa = '".$ruc_empresa."' and serie_gr = '".$serie_guia."' and secuencial_gr = '".$secuencial."'");
					$anular=mysqli_query($con,"UPDATE encabezado_gr SET estado_sri='ANULADA', id_usuario= '".$id_usuario."' WHERE id_encabezado_gr = '".$id_guia."' ");
						if ($delete_detalle && $anular){
							echo "<script>
								$.notify('Guía de remisión anulada exitosamente','success');
								$('#anular_sri').attr('disabled', false);
								setTimeout(function () {location.reload()}, 60 * 20);
								</script>";
						}else {
						$errors []= "Lo siento algo ha salido mal intenta nuevamente.";
						}
				}
			} else {
			$errors []= "Error desconocido, intente de nuevo.";
			}
	}


//para mostrar los mensajes 
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