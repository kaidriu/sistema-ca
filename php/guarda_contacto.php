<?php
	if (empty($_POST['nombre'])) {
           $errors[] = "Ingrese un nombre";
		} else if (empty($_POST['correo'])){
			$errors[] = "Ingrese mail";
		} else if (empty($_POST['mensaje'])){
			$errors[] = "Ingrese un comentario sobre su requerimiento";
		} elseif (strlen($_POST['correo']) > 64) {
            $errors[] = "El correo electrónico no puede ser superior a 64 caracteres";
        } elseif (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Su dirección de correo electrónico no está en un formato de correo electrónico válida";
		} else if (!empty($_POST['nombre']) 
		&& !empty($_POST['correo'])
		&& !empty($_POST['mensaje'])
        && strlen($_POST['correo']) <= 64
        && filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)){
		/* Connect To Database*/
		include("../php/conecta.php");
		$con = conecta_contactos();
		$recaptcha = $_POST["g-recaptcha-response"];
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => '6LdJLE0UAAAAAE2s7Zzf8XhWHYftv6zfPaHo-ZA_',
			'response' => $recaptcha
		);
		$options = array(
			'http' => array (
				'method' => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success = json_decode($verify);
		if (!$captcha_success->success) {
			$errors []= "Debe verificar que no es un robot.";
		}else{
		
		// escaping, additionally removing everything that could be (html/javascript-) code
		$nombre=mysqli_real_escape_string($con,(strip_tags($_POST["nombre"],ENT_QUOTES)));
		$correo=mysqli_real_escape_string($con,(strip_tags($_POST["correo"],ENT_QUOTES)));
		$mensaje=mysqli_real_escape_string($con,(strip_tags($_POST["mensaje"],ENT_QUOTES)));
		$fecha_emision=date("Y-m-d H:i:s");
		//envia mensaje a mail personal
		//Titulo
		$titulo = "Preguntas desde la web";
		//cabecera
		$headers = "MIME-Version: 1.0\r\n"; 
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
		//dirección del remitente 
		$headers .= "From: camagare < $correo >\r\n";
		//Enviamos el mensaje a tu_dirección_email 
	        $bool = mail("info@camagare.com",$titulo,$mensaje,$headers);
		if($bool){
			$messages[] = "";
		}else{
			$errors []= "Mensaje no enviado.".mysqli_error($con);
			exit;
		}
				
		//guarda datos en la base de datos
				$sql = "INSERT INTO contactos VALUES(NULL,'".$nombre."','".$correo."','".$mensaje."','".$fecha_emision."');";
						$query_new_insert = mysqli_query($con,$sql);
						if ($query_new_insert){
						$messages[] = "Su petición ha sido ingresada satisfactoriamente, en breves momentos nos comunicamos con usted.";
					} else{
						$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
					}
			
		}
			} else {
				$errors []= "Error desconocido intente mas tarde.";
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
						<strong>¡Enviado !</strong>
						<?php
							foreach ($messages as $message) {
									echo $message;
								}
							?>
				</div>
				<?php
			}

?>