<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">


<head>
<title>Cambiar contraseña</title>
<?php include("../head.php");?>
</head>
<body>

<?php
include("../conexiones/conectalogin.php");
session_start();
if($_SESSION['nivel'] >= 1){
$titulo_info ="Cambiar contraseña";
include("../navbar_confi.php");
	?>
	<div class="col-md-6 col-md-offset-3">
		<div class="container-center">
			<div class="panel panel-info" >
				<div class="panel-heading">
					<h4><i class='glyphicon glyphicon-cd'></i> Cambiar contraseña</h4>
				</div>	

					<div class="panel-body">
						<form class="form-horizontal" name= "nueva_clave" action="" method="POST"> 
							<input type="hidden" name="id" value = <?php echo $_SESSION['id_usuario'] ?> >
								<div class="form-group">
									<label for="estado" class="col-sm-3 control-label">Contraseña actual</label>
									<div class="col-sm-6">
										<input type="password" class="form-control input-sm" name="clave_actual" placeholder="Contraseña actual" required></input>
									</div>
							    </div>
			  
							    <div class="form-group">
									<label for="estado" class="col-sm-3 control-label">Nueva contraseña</label>
									<div class="col-sm-6">
										<input type="password" class="form-control input-sm" name="nueva_contra" placeholder="Nueva contraseña" pattern=".{4,}" required title="4 caracteres mínimo"></input>
									</div>
							    </div>
								<div class="form-group">
									<label for="estado" class="col-sm-3 control-label">Repetir contraseña</label>
									<div class="col-sm-6">
										<input type="password" class="form-control input-sm" name="repetir_contra" placeholder="Repetir contraseña" pattern=".{4,}" required title="4 caracteres mínimo"></input>
									</div>
							    </div>
						</div>
								<div class="modal-footer">
									<button type="submit" class="btn btn-primary" name="nueva_clave" value="Guardar">Actualizar contraseña</button>
								</div>
					     
						</form>
	<?php
}else{
header('Location: ../includes/logout.php');
exit;
}
    
//para revisar si la clave anterior es la que ingreso
function valida_clave($id,$password){
	$conexion = conenta_login();
	$sql = 'SELECT * FROM usuarios WHERE id ="' . $id . '" AND password="'.$password.'";';
	$result = $conexion->query($sql);
	if($user=mysqli_fetch_array($result)){
		return $user;
	}else{
		return false;
	}
	mysqli_close($conexion);
}
       
if(isset($_POST['nueva_clave'])){
	$id = $_POST['id'];
    $password = md5($_POST['clave_actual']);
	$nueva_clave =md5($_POST['nueva_contra']);
	$repetir_clave =md5($_POST['repetir_contra']);
    $user = valida_clave($_POST['id'],$password);

	if($user != false && $nueva_clave==$repetir_clave){
    $conexion = conenta_login(); 
    $sql = "UPDATE usuarios SET password='".$nueva_clave."' WHERE id='".$id."';";

		if(mysqli_query($conexion,$sql)){
				?>
			  <div class="alert alert-success alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Bien hecho!</strong> La contraseña se actualizó correctamente </div>
			  <?php
		}else{
				?>
			  <div class="alert alert-danger alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Algo pasa!</strong> Error al actualizar la contraseña.</div>
			  <?php
		}
	}else{
			?>
		  <div class="alert alert-danger alert-dismissible" role="alert">
		  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  <strong>Lo siento!</strong> No coinciden los datos proporcionados, vuelva a intentarlo.</div>
		  <?php
	}
}
  ?>  
			</div>
		</div>
	</div>	
	
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<?php
	  
?>

</body>

</html>
