<?php
include("../conexiones/conectalogin.php");
$con = conenta_login();
//para guardar un nuevo registro de minimos
if (empty($_POST['mod_id_minimo'])) {
	
if (empty($_POST['mod_valor_minimo'])) {
           $errors[] = "Ingrese valor mínimo";
	} else if (!is_numeric($_POST['mod_valor_minimo'])){
			$errors[] = "No es valor";	   
} else if (!empty($_POST['mod_valor_minimo'])
		){
		$ruc_empresa=mysqli_real_escape_string($con,(strip_tags($_POST["mod_ruc_empresa"],ENT_QUOTES)));
		$mod_id_producto=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_producto"],ENT_QUOTES)));
		$mod_id_bodega=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_bodega"],ENT_QUOTES)));
		$mod_valor_minimo=mysqli_real_escape_string($con,(strip_tags($_POST["mod_valor_minimo"],ENT_QUOTES)));
			
		$sql="INSERT INTO minimos_inventarios VALUES (NULL, '$ruc_empresa', $mod_id_producto, $mod_id_bodega, $mod_valor_minimo)";
		$query_new_insert = mysqli_query($con,$sql);
			if ($query_new_insert){
				$messages[] = "Valor de mínimo registrado satisfactoriamente.";	
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";	
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		
		}else {
			$errors []= "Error desconocido.";
		}			
}
//para modificar un minimo
if (!empty($_POST['mod_id_minimo'])) {
	
if (empty($_POST['mod_valor_minimo'])) {
           $errors[] = "Ingrese valor mínimo";
	} else if (!is_numeric($_POST['mod_valor_minimo'])){
			$errors[] = "No es valor";	   
} else if (!empty($_POST['mod_valor_minimo'])
		){
		$id_minimo=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_minimo"],ENT_QUOTES)));
		$ruc_empresa=mysqli_real_escape_string($con,(strip_tags($_POST["mod_ruc_empresa"],ENT_QUOTES)));
		$mod_id_producto=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_producto"],ENT_QUOTES)));
		$mod_id_bodega=mysqli_real_escape_string($con,(strip_tags($_POST["mod_id_bodega"],ENT_QUOTES)));
		$mod_valor_minimo=mysqli_real_escape_string($con,(strip_tags($_POST["mod_valor_minimo"],ENT_QUOTES)));
			
		
		$sql="UPDATE minimos_inventarios SET valor_minimo='".$mod_valor_minimo."' WHERE id_minimo='".$id_minimo."'";
		$query_update = mysqli_query($con,$sql);
			if ($query_update){
				$messages[] = "Valor de mínimo registrado satisfactoriamente.";	
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";	
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		
		}else {
			$errors []= "Error desconocido.";
		}			
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