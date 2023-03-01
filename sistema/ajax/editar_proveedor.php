<?php
include("../validadores/ruc.php");
include("../validadores/cedula.php");
include("../validadores/valida_varios_mails.php");
require_once("../helpers/helpers.php");
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];

	if (empty($_POST['razon_social_mod'])) {
		$errors[] = "Ingrese razón social";
		} else if (empty($_POST['mod_id_proveedor'])){
			$errors[] = "Seleccione un proveedor";
		} else if (empty($_POST['tipo_empresa_mod'])){
			$errors[] = "Seleccione tipo de empresa";
		} else if (empty($_POST['tipo_id_mod'])){
			$errors[] = "Seleccione el tipo de identificación del proveedor";
		} else if (empty($_POST['mail_proveedor_mod'])){
			$errors[] = "Ingrese mail";
		} elseif (!empty($_POST['mail_proveedor_mod']) && validar_mails($_POST['mail_proveedor_mod'])=='error') {
			$errors[] = "Error en mail, Puede ingresar varios correos separados por coma y espacio.";
		} else if (empty($_POST['dir_proveedor_mod'])){
			$errors[] = "Ingrese dirección del proveedor";
		} else if (empty($_POST['plazo_mod'])){
			$errors[] = "Ingrese plazo";
		} else if (empty($_POST['unidad_tiempo_mod'])){
			$errors[] = "Seleccione unidad de tiempo";
        } else if (!empty($_POST['razon_social_mod']) && (!empty($_POST['mod_id_proveedor'])) && (!empty($_POST['tipo_empresa_mod'])) && (!empty($_POST['tipo_id_mod'])) 
			&& (!empty($_POST['mail_proveedor_mod'])) && (!empty($_POST['dir_proveedor_mod'])) && (!empty($_POST['plazo_mod'])) && (!empty($_POST['unidad_tiempo_mod']))){
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		$con = conenta_login();
		// escaping, additionally removing everything that could be (html/javascript-) code
		$mod_id_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_proveedor"],ENT_QUOTES)));
		$razon_social=mysqli_real_escape_string($con,(strip_tags(strClean($_POST["razon_social_mod"]),ENT_QUOTES)));
		$nombre_comercial=strClean($_POST["nombre_comercial_mod"]);
		$tipo_empresa=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_empresa_mod"],ENT_QUOTES)));
		$tipo_id=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_id_mod"],ENT_QUOTES)));
		$email_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["mail_proveedor_mod"],ENT_QUOTES)));
		$telefono_proveedor=mysqli_real_escape_string($con,(strip_tags($_POST["telf_proveedor_mod"],ENT_QUOTES)));
		$direccion_proveedor=mysqli_real_escape_string($con,(strip_tags(strClean($_POST["dir_proveedor_mod"]),ENT_QUOTES)));
		$plazo=mysqli_real_escape_string($con,(strip_tags($_POST["plazo_mod"],ENT_QUOTES)));
		$unidad_tiempo=mysqli_real_escape_string($con,(strip_tags($_POST["unidad_tiempo_mod"],ENT_QUOTES)));
		$relacionado='1';//mysqli_real_escape_string($con,(strip_tags($_POST["relacionado_mod"],ENT_QUOTES)));
		$fecha_agregado=date("Y-m-d H:i:s");
		
		$sql="UPDATE proveedores SET razon_social='".$razon_social."', nombre_comercial='".$nombre_comercial."', tipo_id_proveedor='".$tipo_id."',mail_proveedor='".$email_proveedor."',dir_proveedor='".$direccion_proveedor."',telf_proveedor='".$telefono_proveedor."',tipo_empresa='".$tipo_empresa."',fecha_agregado='".$fecha_agregado."',plazo='".$plazo."',unidad_tiempo='".$unidad_tiempo."',relacionado='".$relacionado."' WHERE id_proveedor = '".$mod_id_proveedor."' ";
		$query_update = mysqli_query($con,$sql);
			if ($query_update){
				$messages[] = "Proveedor ha sido actualizado satisfactoriamente.";
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		
		} else {
			$errors []= "Complete todos los campos.";
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
