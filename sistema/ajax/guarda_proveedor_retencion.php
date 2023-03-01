<?php
include("../validadores/ruc.php");
include("../validadores/cedula.php");
include("../validadores/valida_varios_mails.php");
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];

	if (empty($_POST['nombre_proveedor'])) {
		$errors[] = "Ingrese razón social";
		} else if (empty($_POST['tipo_empresa'])){
			$errors[] = "Seleccione tipo de empresa";
		} else if (empty($_POST['tipo_id'])){
			$errors[] = "Seleccione tipo id";
		} else if (empty($_POST['ruc_proveedor'])){
			$errors[] = "Ingrese identificación del proveedor";
		} else if (empty($_POST['email_proveedor'])){
			$errors[] = "Ingrese mail";
		} elseif (!empty($_POST['email_proveedor']) && validar_mails($_POST['email_proveedor'])=='error') {
			$errors[] = "Error en mail, Puede ingresar varios correos separados por coma y espacio.";
        } else if (!empty($_POST['nombre_proveedor']) && (!empty($_POST['tipo_id'])) && (!empty($_POST['tipo_empresa'])) && (!empty($_POST['ruc_proveedor'])) && (!empty($_POST['email_proveedor'])) ){
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		// escaping, additionally removing everything that could be (html/javascript-) code
		$nombre_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_proveedor"],ENT_QUOTES)));
		$tipo_empresa=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_empresa"],ENT_QUOTES)));
		$tipo_id=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_id"],ENT_QUOTES)));
		$ruc_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["ruc_proveedor"],ENT_QUOTES)));
		$email_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["email_proveedor"],ENT_QUOTES)));
		$fecha_agregado=date("Y-m-d H:i:s");
		
		
	$busca_empresa = "SELECT * FROM proveedores WHERE ruc_proveedor = '".$ruc_proveedor."' and ruc_empresa='".$ruc_empresa."'";
	 $result = $con->query($busca_empresa);
	 $count = mysqli_num_rows($result);
	 if ($count == 1) {
		 $errors []= "El proveedor ya está registrado.".mysqli_error($con);
	 }else{
		
		switch ($tipo_id) {
    case 05:
        $validacedula = validacedula($ruc_proveedor);
		if ($validacedula == "cedula correcta") { 
			$sql="INSERT INTO proveedores VALUES (null,'".$nombre_proveedor."','".$nombre_proveedor."','".$ruc_empresa."','".$tipo_id."','".$ruc_proveedor."','".$email_proveedor."','','','".$tipo_empresa."','".$fecha_agregado."','','','')";
		$query_new_insert = mysqli_query($con,$sql);
			if ($query_new_insert){
				$messages[] = "Proveedore ha sido ingresado satisfactoriamente.";
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		}else{
			$errors []= $validacedula . mysqli_error($con);
		}
        break;
    case 04:
        $validaruc = validaRuc($ruc_proveedor);
		if ($validaruc == "correcto") { 
			$sql="INSERT INTO proveedores VALUES (null,'".$nombre_proveedor."','".$nombre_proveedor."','".$ruc_empresa."','".$tipo_id."','".$ruc_proveedor."','".$email_proveedor."','','','".$tipo_empresa."','".$fecha_agregado."','','','')";
		$query_new_insert = mysqli_query($con,$sql);
			if ($query_new_insert){
				$messages[] = "Proveedor ha sido ingresado satisfactoriamente.";
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		}else{
			$errors []= $validaruc . mysqli_error($con);
		}
        break;
		default:
			$sql="INSERT INTO proveedores VALUES (null,'".$nombre_proveedor."','".$nombre_proveedor."','".$ruc_empresa."','".$tipo_id."','".$ruc_proveedor."','".$email_proveedor."','','','".$tipo_empresa."','".$fecha_agregado."','','','')";
		$query_new_insert = mysqli_query($con,$sql);
			if ($query_new_insert){
				$messages[] = "Proveedor ha sido ingresado satisfactoriamente.";
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		}
		}
		} else {
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