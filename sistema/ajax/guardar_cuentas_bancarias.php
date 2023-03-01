<?php
/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();

		
	if (empty($_POST['banco'])) {
           $errors[] = "Seleccione banco";
		}else if (empty($_POST['tipo_cuenta'])) {
           $errors[] = "Seleccione tipo de cuenta";
		}else if (empty($_POST['numero_cuenta'])) {
           $errors[] = "Ingrese número de cuenta o tarjeta";
        }else if (!empty($_POST['banco'])&& !empty($_POST['tipo_cuenta'])&& !empty($_POST['numero_cuenta'])
		){
					
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		$banco=mysqli_real_escape_string($con,(strip_tags($_POST["banco"],ENT_QUOTES)));
		$tipo_cuenta=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_cuenta"],ENT_QUOTES)));
		$numero_cuenta=mysqli_real_escape_string($con,(strip_tags($_POST["numero_cuenta"],ENT_QUOTES)));

		 $busca_cuentas = "SELECT * FROM cuentas_bancarias WHERE ruc_empresa = '$ruc_empresa' and id_banco = $banco and id_tipo_cuenta = $tipo_cuenta and numero_cuenta ='$numero_cuenta'";
		 $result = $con->query($busca_cuentas);
		 $count = mysqli_num_rows($result);
				 if ($count == 1) {
					 $errors []= "El número de cuenta, banco y tipo de cuenta, ya están registrados.".mysqli_error($con);
				 }else{
						$sql="INSERT INTO cuentas_bancarias VALUES (null,'$ruc_empresa',$banco,$tipo_cuenta,'$numero_cuenta',$id_usuario,'')";
						$query_new_insert = mysqli_query($con,$sql);
						if ($query_new_insert){
							$messages[] = "La cuenta bancaria ha sido registrada satisfactoriamente.";
						} else{
							$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
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