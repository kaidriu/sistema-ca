<?php
include("../conexiones/conectalogin.php");
include("../validadores/ruc.php");
include("../validadores/cedula.php");
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$fecha_agregado=date("Y-m-d H:i:s");


	if (empty($_POST['id_alumno'])){
           $errors[] = "Cierre y vuelva a seleccionar desde un alumno";
		} else if (!empty($_POST['id_alumno'])){   
		$id_alumno=mysqli_real_escape_string($con,(strip_tags($_POST["id_alumno"],ENT_QUOTES)));
		
		 //Editar el registro de cliente y asignar el id_cliente al alumno
		 if (!empty($_POST['id_cliente_alumno'])){
			if (empty($_POST['tipo_id_cliente'])){		
			 $errors[] = "Seleccione un tipo de id: cedula, ruc...";
			 }else if (empty($_POST['ruc_cliente_alumno'])) {
			 $errors[] = "Ingrese número de cedula ruc o pasaporte";
			 }else if (empty($_POST['nombre_cliente_alumno'])) {
			 $errors[] = "Ingrese nombre del cliente a quien se va a emitir la factura";
			  }else if (empty($_POST['direccion_cliente_alumno'])) {
			 $errors[] = "Ingrese dirección del cliente";
			 }else if (empty($_POST['email_cliente_alumno'])) {
			 $errors[] = "Ingrese dirección de correo electrónico";
			 } elseif (!filter_var($_POST['email_cliente_alumno'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "La dirección de correo electrónico no está en un formato de correo electrónico válida"; 
			} else if (!empty($_POST['id_alumno']) && filter_var($_POST['email_cliente_alumno'], FILTER_VALIDATE_EMAIL) 
			&& !empty($_POST['tipo_id_cliente'])
			&& !empty($_POST['ruc_cliente_alumno'])
			&& !empty($_POST['nombre_cliente_alumno'])
			&& !empty($_POST['direccion_cliente_alumno'])){
			$id_cliente=mysqli_real_escape_string($con,(strip_tags($_POST["id_cliente_alumno"],ENT_QUOTES)));
			$tipo_id=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_id_cliente"],ENT_QUOTES)));
			$ruc=mysqli_real_escape_string($con,(strip_tags($_POST["ruc_cliente_alumno"],ENT_QUOTES)));
			$nombre=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_cliente_alumno"],ENT_QUOTES)));
			$direccion=mysqli_real_escape_string($con,(strip_tags($_POST["direccion_cliente_alumno"],ENT_QUOTES)));
			$telefono=mysqli_real_escape_string($con,(strip_tags($_POST["telefono_cliente_alumno"],ENT_QUOTES)));
			$email=mysqli_real_escape_string($con,(strip_tags($_POST["email_cliente_alumno"],ENT_QUOTES)));
			$plazo=mysqli_real_escape_string($con,(strip_tags($_POST["plazo_cliente_alumno"],ENT_QUOTES)));
		
			echo editar_alumno($id_usuario, $fecha_agregado, $id_cliente, $id_alumno, $con); 
			 //editar el cliente
		
					
			switch ($tipo_id) {
			case 05:
				$validacedula = validacedula($ruc);
				if ($validacedula == "cedula correcta") { 
				echo editar_cliente($nombre,$telefono,$email,$direccion,$plazo,$id_usuario,$con,$id_cliente); 
				}else{
					$errors []= $validacedula . mysqli_error($con);
				}
				break;
			case 04:
				$validaruc = validaRuc($ruc);
				if ($validaruc == "correcto") { 
				echo editar_cliente($nombre,$telefono,$email,$direccion,$plazo,$id_usuario,$con,$id_cliente);
				}else{
					$errors []= $validaruc . mysqli_error($con);
				}
				break;
				default:
				echo editar_cliente($nombre,$telefono,$email,$direccion,$plazo,$id_usuario,$con,$id_cliente);
				}
		 
			}
		}else{
			
			if (empty($_POST['tipo_id_cliente'])){		
			 $errors[] = "Seleccione un tipo de id: cedula, ruc...";
			 }else if (empty($_POST['ruc_cliente_alumno'])) {
			 $errors[] = "Ingrese número de cedula ruc o pasaporte";
			 }else if (empty($_POST['nombre_cliente_alumno'])) {
			 $errors[] = "Ingrese nombre del cliente a quien se va a emitir la factura";
			  }else if (empty($_POST['direccion_cliente_alumno'])) {
			 $errors[] = "Ingrese dirección del cliente";
			 }else if (empty($_POST['email_cliente_alumno'])) {
			 $errors[] = "Ingrese dirección de correo electrónico";
			 } elseif (!filter_var($_POST['email_cliente_alumno'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "La dirección de correo electrónico no está en un formato de correo electrónico válida"; 
			} else if (!empty($_POST['id_alumno']) && filter_var($_POST['email_cliente_alumno'], FILTER_VALIDATE_EMAIL) 
			&& !empty($_POST['tipo_id_cliente'])
			&& !empty($_POST['ruc_cliente_alumno'])
			&& !empty($_POST['nombre_cliente_alumno'])
			&& !empty($_POST['direccion_cliente_alumno'])){
			$id_cliente=mysqli_real_escape_string($con,(strip_tags($_POST["id_cliente_alumno"],ENT_QUOTES)));
			$tipo_id=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_id_cliente"],ENT_QUOTES)));
			$ruc=mysqli_real_escape_string($con,(strip_tags($_POST["ruc_cliente_alumno"],ENT_QUOTES)));
			$nombre=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_cliente_alumno"],ENT_QUOTES)));
			$direccion=mysqli_real_escape_string($con,(strip_tags($_POST["direccion_cliente_alumno"],ENT_QUOTES)));
			$telefono=mysqli_real_escape_string($con,(strip_tags($_POST["telefono_cliente_alumno"],ENT_QUOTES)));
			$email=mysqli_real_escape_string($con,(strip_tags($_POST["email_cliente_alumno"],ENT_QUOTES)));
			$plazo=mysqli_real_escape_string($con,(strip_tags($_POST["plazo_cliente_alumno"],ENT_QUOTES)));

			//si no hay id cliente hay que guardar el nuevo cliente y luego asignarle al alumno
			$busca_cliente = "SELECT * FROM clientes WHERE ruc = '".$ruc."' and ruc_empresa = '".$ruc_empresa."'";
			 $result = $con->query($busca_cliente);
			 $count = mysqli_num_rows($result);
			 if ($count == 1) {
				 $errors []= "El cliente ya está registrado.".mysqli_error($con);
			 }else{
					switch ($tipo_id) {
					case 05:
					$validacedula = validacedula($ruc);
					if ($validacedula == "cedula correcta") { 
						echo guardar_cliente($ruc_empresa,$nombre,$tipo_id,$ruc,$telefono,$email,$direccion,$fecha_agregado,$plazo,$id_usuario,$con);
						echo asignar_cliente($ruc, $ruc_empresa, $nombre, $email, $direccion, $con, $id_alumno);
					}else{
						$errors []= $validacedula . mysqli_error($con);
					}
					break;
					case 04:
					$validaruc = validaRuc($ruc);
					if ($validaruc == "correcto") { 
						echo guardar_cliente($ruc_empresa,$nombre,$tipo_id,$ruc,$telefono,$email,$direccion,$fecha_agregado,$plazo,$id_usuario,$con);
						echo asignar_cliente($ruc, $ruc_empresa, $nombre, $email, $direccion, $con, $id_alumno);
					}else{
						$errors []= $validaruc . mysqli_error($con);
					}
					break;
					default:
						echo guardar_cliente($ruc_empresa,$nombre,$tipo_id,$ruc,$telefono,$email,$direccion,$fecha_agregado,$plazo,$id_usuario,$con);
						echo asignar_cliente($ruc, $ruc_empresa, $nombre, $email, $direccion, $con, $id_alumno);
					}
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

function editar_alumno($id_usuario, $fecha_agregado, $id_cliente, $id_alumno, $con){
$sql="UPDATE alumnos SET id_usuario = '".$id_usuario."',fecha_agregado='".$fecha_agregado."',id_cliente='".$id_cliente."' WHERE id_alumno='".$id_alumno."'";
$query_update = mysqli_query($con,$sql);
	if ($query_update){
		echo "<script>
		$.notify('Los datos para la facturación han sido actualizado satisfactoriamente.','success');
		</script>";	
	} else{
		echo "<script>
		$.notify('Lo siento algo ha salido mal intenta nuevamente.','error');
		</script>";
	}
}

function editar_cliente($nombre,$telefono,$email,$direccion,$plazo,$id_usuario,$con,$id_cliente){
$sql="UPDATE clientes SET nombre='".$nombre."', telefono='".$telefono."', email='".$email."', direccion='".$direccion."', plazo= '".$plazo."', id_usuario = '".$id_usuario."'  WHERE id='".$id_cliente."'";
$query_update = mysqli_query($con,$sql);
	if ($query_update){
		echo "<script>
		$.notify('Cliente ha sido actualizado satisfactoriamente.','success');
		</script>";	
		echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";
	} else{
		echo "<script>
		$.notify('Lo siento algo ha salido mal intenta nuevamente.','error');
		</script>";
	}
}

function guardar_cliente($ruc_empresa,$nombre,$tipo_id,$ruc,$telefono,$email,$direccion,$fecha_agregado,$plazo,$id_usuario,$con){
$sql="INSERT INTO clientes VALUES (null,'".$ruc_empresa."' ,'".$nombre."','".$tipo_id."','".$ruc."','".$telefono."','".$email."','".$direccion."','".$fecha_agregado."','".$plazo."','".$id_usuario."','','')";
$query_new_insert = mysqli_query($con,$sql);
	if ($query_new_insert){
		echo "<script>
		$.notify('Cliente ha sido ingresado satisfactoriamente..','success');
		</script>";	
	} else{
		echo "<script>
		$.notify('Lo siento algo ha salido mal intenta nuevamente.','error');
		</script>";
	}
} 

function asignar_cliente($ruc, $ruc_empresa, $nombre, $email, $direccion, $con, $id_alumno){
	$busca_cliente = "SELECT * FROM clientes WHERE ruc = '".$ruc."' and ruc_empresa = '".$ruc_empresa."' and nombre= '".$nombre."'  and email= '".$email."' and direccion = '".$direccion."' ";
	$result = $con->query($busca_cliente);
	$row_cliente = mysqli_fetch_array($result);
	$id_cliente=$row_cliente['id'];
	//actualizar el id_cliente en el alumno
	
$sql="UPDATE alumnos SET id_cliente='".$id_cliente."' WHERE id_alumno='".$id_alumno."'";
$query_update = mysqli_query($con,$sql);
	if ($query_update){
		echo "<script>
		$.notify('Los datos para la facturación han sido actualizado satisfactoriamente.','success');
		</script>";
		echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";
	} else{
		echo "<script>
		$.notify('Lo siento algo ha salido mal intenta nuevamente.','error');
		</script>";
	}
}
?>