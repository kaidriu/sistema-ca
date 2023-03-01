<?php
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		include("../validadores/generador_codigo_unico.php");
		include("../clases/asientos_contables.php");
		$con = conenta_login();
	if (empty($_POST['fecha_ingreso'])) {
           $errors[] = "Ingrese fecha para el ingreso.";
		}else if (!date($_POST['fecha_ingreso'])) {
           $errors[] = "Ingrese fecha correcta";
		}else if (empty($_POST['cliente_ingreso'])) {
           $errors[] = "Ingrese o seleccione un cliente o el nombre de quien se recibe el ingreso.";   
        } else if (!empty($_POST['fecha_ingreso']) && !empty($_POST['cliente_ingreso'])){

			$fecha_ingreso=date('Y-m-d H:i:s', strtotime($_POST['fecha_ingreso']));
			$nombre_cliente=mysqli_real_escape_string($con,(strip_tags($_POST["cliente_ingreso"],ENT_QUOTES)));
			$id_cliente_ingreso=mysqli_real_escape_string($con,(strip_tags($_POST["id_cliente_ingreso"],ENT_QUOTES)));
			$observacion_ingreso=mysqli_real_escape_string($con,(strip_tags($_POST["observacion_ingreso"],ENT_QUOTES)));		
			$fecha_registro=date("Y-m-d H:i:s");
			session_start();
			$id_usuario = $_SESSION['id_usuario'];
			$ruc_empresa = $_SESSION['ruc_empresa'];
			$codigo_unico=codigo_unico(20);

			$sql_ingresos_egresos_tmp=mysqli_query($con,"select * from ingresos_egresos_tmp where id_usuario = '".$id_usuario."' and tipo_documento='INGRESO'");
			$count=mysqli_num_rows($sql_ingresos_egresos_tmp);
			if ($count==0){
			$errors []= "No hay detalles agregados al ingreso.".mysqli_error($con);
			}else{
					//para buscar el total de pagos
					$total_pagos=0;
					if (isset($_SESSION['arrayFormaPagoIngreso'])) {
						foreach ($_SESSION['arrayFormaPagoIngreso'] as $detalle) {
							$total_pagos += $detalle['valor'];
						}
					}
					
					//para buscar el total ingresos
					$busca_ingresos_agregados = mysqli_query($con,"SELECT sum(valor) as valor FROM ingresos_egresos_tmp WHERE id_usuario = '".$id_usuario."' and tipo_documento = 'INGRESO' group by id_usuario");
					$row_ingresos=	mysqli_fetch_array($busca_ingresos_agregados);
					$total_ingresos=$row_ingresos['valor'];
					
					if (number_format($total_pagos, 2, '.', '') != number_format($total_ingresos, 2, '.', '')){
					$errors []= "El total de ingreso no coincide con el total de formas de pagos.".mysqli_error($con);//."ti: ".$total_ingresos." tp: ".$total_pagos." dif:".$total_pagos-$total_ingresos
					}else{
						
					//para buscar el numero de ingreso que continua
					$busca_siguiente_ingreso = mysqli_query($con,"SELECT max(numero_ing_egr) as numero FROM ingresos_egresos WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr = 'INGRESO'");
					$row_siguiente_ingreso = mysqli_fetch_array($busca_siguiente_ingreso);
					$numero_ingreso=$row_siguiente_ingreso['numero']+1;

					//para ver si hay informacion en el asiento contable											
					$sql_diario_temporal=mysqli_query($con,"select count(id_cuenta) as id_cuenta, sum(debe) as debe, sum(haber) as haber from detalle_diario_tmp where id_usuario = '".$id_usuario."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
					$row_asiento_contable=mysqli_fetch_array($sql_diario_temporal);
					$debe=$row_asiento_contable['debe'];
					$haber=$row_asiento_contable['haber'];
					$count_asientos=$row_asiento_contable['id_cuenta'];

					if ($count_asientos>0 && ($debe != $haber)){
					$errors []= "El asiento contable no cumple con partida doble.".mysqli_error($con);
					}else{
						
						//para guardar el encabezado del ingreso
						$query_encabezado_ingreso = mysqli_query($con, "INSERT INTO ingresos_egresos VALUES (null, '".$ruc_empresa."','".$fecha_ingreso."','".$nombre_cliente."','".$numero_ingreso."','".$total_ingresos."','INGRESO','".$id_usuario."','".$fecha_registro."','0','".$codigo_unico."','".$observacion_ingreso."','OK','".$id_cliente_ingreso."')");
						// actualizar el ingreso tmp
						$update_ingresos_tmp = mysqli_query($con, "UPDATE saldo_porcobrar_porpagar as sal_tmp, (SELECT iet.id_documento as registro, sum(iet.valor) as suma_ingreso_tmp FROM ingresos_egresos_tmp as iet WHERE iet.tipo_documento='INGRESO' group by iet.id_documento) as total_ingreso_tmp SET sal_tmp.total_ing = sal_tmp.total_ing + total_ingreso_tmp.suma_ingreso_tmp, sal_tmp.ing_tmp='0'  WHERE total_ingreso_tmp.registro=sal_tmp.id_documento");//sal_tmp.ing_tmp +
						
						$busca_ultimo_registro = mysqli_query($con,"SELECT * FROM ingresos_egresos WHERE id_ing_egr= LAST_INSERT_ID()");
						$row_ultimo_registro = mysqli_fetch_array($busca_ultimo_registro);
						$id_ing_egr=$row_ultimo_registro['id_ing_egr'];
						
						if(($count_asientos>0 && $debe == $haber )){
						//guardar asiento contable
						$asiento_contable=new asientos_contables();
						$numero_asiento=$asiento_contable->numero_asiento($con, $ruc_empresa);
						$guarda_asiento=$asiento_contable->guarda_asiento($con, $fecha_ingreso, 'INGRESO N.'.$numero_ingreso." ".$nombre_cliente, 'INGRESOS', $id_ing_egr, $ruc_empresa, $id_usuario, $numero_asiento, $id_cliente_ingreso);
						$actualizar_asiento_egreso = mysqli_query($con,"UPDATE ingresos_egresos SET codigo_contable='".$numero_asiento."' WHERE id_ing_egr= '".$id_ing_egr."'");
						}

						if (isset($_SESSION['arrayFormaPagoIngreso'])) {
							foreach ($_SESSION['arrayFormaPagoIngreso'] as $detalle) {
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
								
								$detalle_formas_pago = mysqli_query($con, "INSERT INTO formas_pagos_ing_egr VALUES (null, '" . $ruc_empresa . "', 'INGRESO', '" . $numero_ingreso . "', '" . $valor_pago . "', '" . $codigo_forma_pago . "', '" . $id_cuenta . "', '".$tipo."', '" . $codigo_unico . "', '".$fecha_ingreso."', '".$fecha_ingreso."', '".$fecha_ingreso."','PAGADO','0','OK')");
							}
						}
						
						$detalle_ingreso=mysqli_query($con, "INSERT INTO detalle_ingresos_egresos (id_detalle_ing_egr, ruc_empresa, beneficiario_cliente, valor_ing_egr, detalle_ing_egr, numero_ing_egr, tipo_ing_egr, tipo_documento, codigo_documento_cv, estado, codigo_documento)
						SELECT null, '".$ruc_empresa."', beneficiario_cliente, valor, detalle, '".$numero_ingreso."', tipo_transaccion, 'INGRESO', id_documento,'OK', '".$codigo_unico."'  FROM ingresos_egresos_tmp where id_usuario = '".$id_usuario."' and tipo_documento='INGRESO'");

								if ($query_encabezado_ingreso && $detalle_formas_pago && $detalle_ingreso ){
									unset($_SESSION['arrayFormaPagoIngreso']);
									echo "<script>
									$.notify('Ingreso guardado con éxito','success');
									setTimeout(function () {location.reload()}, 60 * 20); 
									</script>";													
									} else{
									$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
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