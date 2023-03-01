<?php

	if (empty($_POST['mod_id_cuenta'])) {
           $errors[] = "Seleccione una cuenta para modificar.";
		}else if (empty($_POST['mod_nombre_cuenta'])) {
           $errors[] = "Ingrese un nombre de cuenta contable.";
        }else if ( (!empty($_POST['mod_id_cuenta'])) && (!empty($_POST['mod_nombre_cuenta']))){
					
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		// escaping, additionally removing everything that could be (html/javascript-) code
		$id_cuenta=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_cuenta"],ENT_QUOTES)));
		$nombre_cuenta=mysqli_real_escape_string($con,(strip_tags($_POST["mod_nombre_cuenta"],ENT_QUOTES)));
		$codigo_sri=mysqli_real_escape_string($con,(strip_tags($_POST["mod_codigo_sri"],ENT_QUOTES)));
		$codigo_supercias=mysqli_real_escape_string($con,(strip_tags($_POST["mod_codigo_supercias"],ENT_QUOTES)));
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date('Y-m-d H:i:s');

		$actualizar_cuenta=mysqli_query($con, "UPDATE plan_cuentas SET nombre_cuenta ='".$nombre_cuenta."', codigo_sri='".$codigo_sri."', codigo_supercias ='".$codigo_supercias."', id_usuario='".$id_usuario."', fecha_registro='".$fecha_registro."' WHERE id_cuenta ='".$id_cuenta."'");
			if ($actualizar_cuenta){
				echo "<script>$.notify('La cuenta ha sido actualizada.','success');
				//setTimeout(function (){location.href ='../modulos/plan_de_cuentas.php'}, 1000);
				$('.close:visible').click();
				</script>";
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
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