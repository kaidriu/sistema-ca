<?php
/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();

		
	if (empty($_POST['razon_social'])) {
           $errors[] = "Ingrese razón social";
		}else if (empty($_POST['direccion'])) {
           $errors[] = "Ingrese dirección de la empresa";
		}else if (empty($_POST['tipo'])) {
           $errors[] = "Seleccione tipo de empresa";
		} elseif (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Su dirección de correo electrónico no está en un formato de correo electrónico válida";
		}else if (empty($_POST['provincia'])) {
           $errors[] = "Seleccione una provincia";
		}else if (empty($_POST['ciudad'])) {
           $errors[] = "Seleccione una ciudad";
        }else if (!empty($_POST['razon_social'])&& !empty($_POST['direccion'])&& !empty($_POST['tipo'])&& !empty($_POST['provincia'])&& !empty($_POST['ciudad'])
		){

		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		// escaping, additionally removing everything that could be (html/javascript-) code
		$id_empresa=mysqli_real_escape_string($con,(strip_tags($_POST["id_empresa"],ENT_QUOTES)));
		$razon_social=mysqli_real_escape_string($con,(strip_tags($_POST["razon_social"],ENT_QUOTES)));
		$nombre_comercial=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_comercial"],ENT_QUOTES)));
		$direccion=mysqli_real_escape_string($con,(strip_tags($_POST["direccion"],ENT_QUOTES)));
		$telefono=mysqli_real_escape_string($con,(strip_tags($_POST["telefono"],ENT_QUOTES)));
		$tipo=mysqli_real_escape_string($con,(strip_tags($_POST["tipo"],ENT_QUOTES)));
		$representante_legal=mysqli_real_escape_string($con,(strip_tags($_POST["representante_legal"],ENT_QUOTES)));
		$id_representante_legal=mysqli_real_escape_string($con,(strip_tags($_POST["id_representante_legal"],ENT_QUOTES)));
		$mail=mysqli_real_escape_string($con,(strip_tags($_POST["mail"],ENT_QUOTES)));
		$provincia=mysqli_real_escape_string($con,(strip_tags($_POST["provincia"],ENT_QUOTES)));
		$ciudad=mysqli_real_escape_string($con,(strip_tags($_POST["ciudad"],ENT_QUOTES)));
		$nombre_contador=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_contador"],ENT_QUOTES)));
		$ruc_contador=mysqli_real_escape_string($con,(strip_tags($_POST["ruc_contador"],ENT_QUOTES)));

			$query_update= mysqli_query($con,"UPDATE empresas SET nombre='".$razon_social."', nombre_comercial='".$nombre_comercial."',direccion='".$direccion."', telefono='".$telefono."', tipo='".$tipo."', nom_rep_legal='".$representante_legal."', ced_rep_legal='".$id_representante_legal."', mail='".$mail."',cod_prov='".$provincia."',cod_ciudad='".$ciudad."',id_usuario='".$id_usuario."', nombre_contador='".$nombre_contador."', ruc_contador='".$ruc_contador."' WHERE id='".$id_empresa."'");
			if ($query_update){
				echo "<script>
				$.notify('Los datos se actualizaron correctamente.','success');
				setTimeout(function () {location.reload()}, 60 * 20)</script>";
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
					<strong>Error! </strong> 
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