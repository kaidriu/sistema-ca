<?php
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		include("../validadores/generador_codigo_unico.php");
		include("../clases/asientos_contables.php");
		session_start();
		$id_usuario = $_SESSION['id_usuario'];
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$con = conenta_login();
	if (empty($_POST['fecha_egreso'])) {
           $errors[] = "Ingrese fecha para el egreso.";
		}else if (!date($_POST['fecha_egreso'])) {
           $errors[] = "Ingrese fecha correcta";
		}else if (empty($_POST['nombre_beneficiario'])) {
           $errors[] = "Ingrese o seleccione un proveedor o el nombre de un beneficiario.";
		}else if (empty($_POST['total_egreso'])) {
           $errors[] = "Ingrese detalles en el egreso.";
		}else if (empty($_POST['total_pagos_egreso'])) {
           $errors[] = "Ingrese valores en formas de pago";
		}else if ($_POST['total_pagos_egreso'] != $_POST['total_egreso']) {
           $errors[] = "El total del egreso no es igual al total de formas de pago";		   
        } else if (!empty($_POST['fecha_egreso']) && !empty($_POST['nombre_beneficiario'])	&& !empty($_POST['total_egreso']) && !empty($_POST['total_pagos_egreso'])){

			$fecha_egreso=date('Y-m-d H:i:s', strtotime($_POST['fecha_egreso']));
			$id_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["id_proveedor"],ENT_QUOTES)));
			$nombre_beneficiario=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_beneficiario"],ENT_QUOTES)));
			$total_egreso=mysqli_real_escape_string($con,(strip_tags($_POST["total_egreso"],ENT_QUOTES)));
			$pagos_egreso=mysqli_real_escape_string($con,(strip_tags($_POST["total_pagos_egreso"],ENT_QUOTES)));
			$detalle_adicional=mysqli_real_escape_string($con,(strip_tags($_POST["detalle_adicional"],ENT_QUOTES)));			
			$codigo_documento=codigo_unico(20);
			
			$fecha_registro=date("Y-m-d H:i:s");
			
					$sql_ingresos_egresos_tmp=mysqli_query($con,"select * from ingresos_egresos_tmp where id_usuario = '".$id_usuario."' and tipo_documento='EGRESO'");
					$count=mysqli_num_rows($sql_ingresos_egresos_tmp);
					if ($count==0){
					$errors []= "No hay documentos o detalles agregados al egreso.".mysqli_error($con);
					}else{
					//para ver si hay informacion en el asiento contable											
					$sql_diario_temporal=mysqli_query($con,"select count(id_cuenta) as id_cuenta, sum(debe) as debe, sum(haber) as haber from detalle_diario_tmp where id_usuario = '".$id_usuario."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
					$row_asiento_contable=mysqli_fetch_array($sql_diario_temporal);
					$debe=$row_asiento_contable['debe'];
					$haber=$row_asiento_contable['haber'];
					$count_asientos=$row_asiento_contable['id_cuenta'];

					//if ($count_asientos>0 && ($debe != $total_egreso || $haber !=$total_egreso)){
					if ($count_asientos>0 && ($debe != $haber)){
					$errors []= "El asiento contable no cumple con partida doble.".mysqli_error($con);
					}else{								
						//para buscar el numero de egreso que continua
						$busca_siguiente_egreso = mysqli_query($con,"SELECT max(numero_ing_egr) as numero FROM ingresos_egresos WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr = 'EGRESO'");
						$row_siguiente_egreso = mysqli_fetch_array($busca_siguiente_egreso);
						$numero_egreso=$row_siguiente_egreso['numero']+1;
						
						//para guardar el encabezado del Egreso
						$query_encabezado_egreso= mysqli_query($con,"INSERT INTO ingresos_egresos VALUES (null, '".$ruc_empresa."','".$fecha_egreso."','".$nombre_beneficiario."','".$numero_egreso."','".$total_egreso."','EGRESO','".$id_usuario."','".$fecha_registro."','0','".$codigo_documento."','".$detalle_adicional."','OK','".$id_proveedor."')");
						
						$busca_ultimo_registro = mysqli_query($con,"SELECT * FROM ingresos_egresos WHERE id_ing_egr= LAST_INSERT_ID()");
						$row_ultimo_registro = mysqli_fetch_array($busca_ultimo_registro);
						$id_ing_egr=$row_ultimo_registro['id_ing_egr'];
						
						if($count_asientos>0 && ($debe ==  $haber )){
						//guardar asiento contable
						$asiento_contable=new asientos_contables();
						$numero_asiento=$asiento_contable->numero_asiento($con, $ruc_empresa);
						$guarda_asiento=$asiento_contable->guarda_asiento($con, $fecha_egreso, 'EGRESO N.'.$numero_egreso." ".$nombre_beneficiario, 'EGRESOS', $id_ing_egr, $ruc_empresa, $id_usuario, $numero_asiento, $id_proveedor);
						$actualizar_asiento_egreso = mysqli_query($con,"UPDATE ingresos_egresos SET codigo_contable='".$numero_asiento."' WHERE id_ing_egr= '".$id_ing_egr."'");
						}
						//para guardar laS formaS de pago del egreso
						//$detalle_formas_pago=mysqli_query($con, "INSERT INTO formas_pagos_ing_egr (id_fp, ruc_empresa, tipo_documento, numero_ing_egr, valor_forma_pago, codigo_forma_pago, id_cuenta, detalle_pago, codigo_documento, fecha_emision, fecha_entrega, fecha_pago, estado_pago, cheque, estado)
						//SELECT null, '".$ruc_empresa."', 'EGRESO', '".$numero_egreso."', valor_pago, id_forma_pago, id_cuenta, detalle_pago, '".$codigo_documento."', '".$fecha_egreso."','', fecha_cobro, if(cheque >0,'ENTREGAR','PAGADO') as estado_pago, cheque, 'OK' FROM formas_pagos_tmp WHERE id_usuario = '".$id_usuario."' and tipo_documento = 'EGRESO'");
						
						if (isset($_SESSION['arrayFormaPagoEgreso'])) {
							foreach ($_SESSION['arrayFormaPagoEgreso'] as $detalle) {
								$origen = $detalle['origen'];
								if($origen=='1'){
									$codigo_forma_pago = $detalle['id_forma'];
									$id_cuenta='0';
								}else{
									$id_cuenta = $detalle['id_forma'];
									$codigo_forma_pago='0';
								}
								$valor_pago = number_format($detalle['valor'], 2, '.', '');
								$tipo = $detalle['tipo'];
								$cheque = $tipo=='C'?$detalle['cheque']:0;
								$fecha_cheque = $detalle['fecha_cheque'];
								$estado_pago=$cheque>0?"ENTREGAR":"PAGADO";
								
								$detalle_formas_pago = mysqli_query($con, "INSERT INTO formas_pagos_ing_egr VALUES (null, '" . $ruc_empresa . "', 'EGRESO', '" . $numero_egreso . "', '" . $valor_pago . "', '" . $codigo_forma_pago . "', '" . $id_cuenta . "', '".$tipo."', '" . $codigo_documento . "', '".$fecha_egreso."', '', '".$fecha_cheque."','".$estado_pago."','".$cheque."','OK')");
							}
						}else{
							$detalle_formas_pago=true;//cuando no hay valores a guardar
						}

						//para guardar el detalle del egreso							
						$detalle_egreso=mysqli_query($con, "INSERT INTO detalle_ingresos_egresos (id_detalle_ing_egr, ruc_empresa, beneficiario_cliente, valor_ing_egr, detalle_ing_egr, numero_ing_egr, tipo_ing_egr, tipo_documento, codigo_documento_cv, estado, codigo_documento)
						SELECT null, '".$ruc_empresa."', beneficiario_cliente, valor, detalle, '".$numero_egreso."', tipo_transaccion, 'EGRESO', id_documento,'OK', '".$codigo_documento."'  FROM ingresos_egresos_tmp where id_usuario = '".$id_usuario."' and tipo_documento='EGRESO'");

								if ($query_encabezado_egreso && $detalle_formas_pago && $detalle_egreso){
									unset($_SESSION['arrayFormaPagoEgreso']);
									echo "<script>
								$.notify('Egreso guardado con éxito','success');
								setTimeout(function () {location.reload()}, 60 * 20); 
								</script>";													
								} else{
								$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
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