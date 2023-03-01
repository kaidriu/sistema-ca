<?php
include("../validadores/generador_codigo_unico.php");
include("../clases/asientos_contables.php");
	if (empty($_POST['fecha_diario'])) {
           $errors[] = "Ingrese fecha del diario.";
		}else if (!date($_POST['fecha_diario'])) {
           $errors[] = "Ingrese una fecha correcta.";
		}else if (empty($_POST['concepto_diario'])) {
           $errors[] = "Ingrese un concepto general relacionado al registro.";
		}else if ($_POST['subtotal_debe'] != $_POST['subtotal_haber']) {
           $errors[] = "El asiento no cumple con partida doble.";
        }else if ( (!empty($_POST['fecha_diario'])) && (!empty($_POST['concepto_diario']))){
					
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		$codigo_unico=(isset($_POST["codigo_unico"]))?$_POST["codigo_unico"]:"";
		$fecha_diario=date('Y-m-d H:i:s', strtotime(mysqli_real_escape_string($con,(strip_tags($_POST["fecha_diario"],ENT_QUOTES)))));
		$concepto_diario=mysqli_real_escape_string($con,(strip_tags($_POST["concepto_diario"],ENT_QUOTES)));
		$asiento_contable=new asientos_contables();

		if ($codigo_unico !=""){
			$sql_diario_temporal=mysqli_query($con,"select * from detalle_diario_tmp where id_usuario = '".$id_usuario."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
			$count=mysqli_num_rows($sql_diario_temporal);
			if ($count==0){
			$errors []= "No hay detalle de cuentas agregados al asiento.".mysqli_error($con);
			}else{
				$edita_asiento=$asiento_contable->edita_asiento($con, $fecha_diario, $concepto_diario, $ruc_empresa, $id_usuario, '0', $codigo_unico);
				if ($edita_asiento){
					echo "<script>$.notify('Asiento editado con éxito.','success');
						$('.close:visible').click();
						</script>";
				} else{
					echo "<script>$.notify('Lo siento algo ha salido mal intenta nuevamente.','error')</script>";
				}	
			}
		}else{
		$sql_diario_temporal=mysqli_query($con,"select * from detalle_diario_tmp where id_usuario = '".$id_usuario."' and ruc_empresa = '".$ruc_empresa."'");
			$count=mysqli_num_rows($sql_diario_temporal);
			if ($count==0){
			$errors []= "No hay detalle de cuentas agregados al asiento.".mysqli_error($con);
			}else{
				$numero_asiento=$asiento_contable->numero_asiento($con, $ruc_empresa);
				$guarda_asiento=$asiento_contable->guarda_asiento($con, $fecha_diario, $concepto_diario, 'DIARIO', '0', $ruc_empresa, $id_usuario, $numero_asiento, '0');

				if ($guarda_asiento){
					echo "<script>
					$.notify('Asiento guardado con éxito.','success');
					setTimeout(function (){location.href ='../modulos/libro_diario.php'}, 1000);
					</script>";
				} else{
					$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
				}
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
?>