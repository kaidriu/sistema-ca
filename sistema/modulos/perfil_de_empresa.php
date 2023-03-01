<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
<title>Perfil empresa</title>
<?php include("../head.php");?>
<script src="../js/jquery.min.js"></script> <!--para que me cargue el select con las ciudades -->
<script type="text/javascript" src="../js/select_ciudad.js"></script>
</head>

<body>
<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

include("../paginas/menu_de_empresas.php"); 	
$con = conenta_login();
//para buscar la empresa
$busca_empresa = "SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."'";
$resultado_de_la_busqueda = $con->query($busca_empresa);
$row=mysqli_fetch_array($resultado_de_la_busqueda);
$id_empresa = $row['id'];
$razon_social = $row['nombre'];
$nombre_comercial = $row['nombre_comercial'];
$direccion = $row['direccion'];
$telefono = $row['telefono'];
$tipo = $row['tipo'];
$rep_legal = $row['nom_rep_legal'];
$id_rep_legal = $row['ced_rep_legal'];
$mail = $row['mail'];
$provincia = $row['cod_prov'];
$ciudad = $row['cod_ciudad'];
$nombre_contador = $row['nombre_contador'];
$ruc_contador = $row['ruc_contador'];
$ruc = substr($row['ruc'],0,12)."1";
?>

	<div class="container-fluid">
	<div class="col-md-6 col-md-offset-3">
		<div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-pencil'></i> Información de la matriz</h4>
		</div>			
			<div class="panel-body">
			
			<form class="form-horizontal" method="post" id="editar_empresa" name="editar_empresa" enctype="multipart/form-data">
					<div class="form-group">
						<div class="col-sm-12">
						<div class="input-group">
						<span class="input-group-addon"><b>Ruc empresa</b></span>
						   <input type="text" class="form-control" value= "<?php echo $ruc; ?>" readonly >
						</div>
						</div>
					 </div>
					<div class="form-group">
						<div class="col-sm-12">
						<div class="input-group">
						<span class="input-group-addon"><b>Razón Social</b></span>
						   <input type="hidden" name="id_empresa" value= "<?php echo $id_empresa; ?>" >
						   <input type="text" class="form-control" name="razon_social" value= "<?php echo $razon_social; ?>" required >
						</div>
						</div>
					 </div>
					<div class="form-group">
						  <div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Nombre comercial</b></span>
							 <input type="text" class="form-control" name="nombre_comercial" value= "<?php echo $nombre_comercial; ?>">
						  </div>
						  </div>
					</div>
					<div class="form-group">
						 <div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Dirección</b></span>
							<input type="text" class="form-control" name="direccion" value= "<?php echo $direccion; ?>" required>
						</div>
						</div>
					</div>
					<div class="form-group">
						 <div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Teléfono</b></span>
							<input type="text" class="form-control" name="telefono" value= "<?php echo $telefono; ?>" >
						</div>
						</div>
					</div>
					<div class="form-group">
						 <div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Tipo contribuyente</b></span>
							<select class="form-control" name="tipo" required>
									<?php
									$conexion = conenta_login();
									$sql = "SELECT * FROM tipo_empresa ;";
									$res = mysqli_query($conexion,$sql);
									?> 
									<option value="">Seleccione tipo de empresa</option>
									<?php
									while($o = mysqli_fetch_assoc($res)){
									
									if ($o['codigo'] == $tipo){
									?>
									<option value=<?php echo $tipo; ?> selected><?php echo $o['nombre']; ?> </option>
									<?php
									}else{			
									?>
									<option value="<?php echo $o['codigo']; ?>"><?php echo $o['nombre']; ?> </option>
									<?php
									}
									}
								?>
							</select>
						</div>
						</div>						
					 </div>
					<div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Representante legal</b></span>
							<input type="text" class="form-control" name="representante_legal" value= "<?php echo $rep_legal; ?>" >
						</div>
					</div>						
					 </div>
					<div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>ID Representante legal</b></span>
							<input type="text" class="form-control" name="id_representante_legal" value="<?php echo $id_rep_legal; ?>" >
						</div>
						</div>						
					 </div>
					 <div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Nombre contador</b></span>
							<input type="text" class="form-control" name="nombre_contador" value= "<?php echo $nombre_contador; ?>" >
						</div>
					</div>						
					 </div>
					<div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Ruc contador</b></span>
							<input type="text" class="form-control" name="ruc_contador" value="<?php echo $ruc_contador; ?>" >
						</div>
						</div>						
					 </div>
					 <div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Email</b></span>
							<input type="text" class="form-control" name="mail" value= "<?php echo $mail; ?>">
						</div>
					</div>						
					 </div>
					 <div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Provincia</b></span>
							<select class="form-control" name="provincia" id="provincia" required>
									<?php
									$conexion = conenta_login();
									$sql = "SELECT * FROM provincia ;";
									$res = mysqli_query($conexion,$sql);
									?> 
									<option value="">Seleccione una provincia</option>
									<?php
									while($p = mysqli_fetch_assoc($res)){
										if ($p['codigo'] == $provincia){
										?>
										<option value=<?php echo $provincia; ?> selected><?php echo $p['nombre']; ?> </option>
										<?php
										}else{			
										?>
										<option value="<?php echo $p['codigo']; ?>"><?php echo $p['nombre']; ?> </option>
										<?php
										}
									}
								?>
							</select>
						</div>
					</div>						
					 </div>
					  <div class="form-group">
						<div class="col-sm-12">
						  <div class="input-group">
						<span class="input-group-addon"><b>Ciudad</b></span>
						<select class="form-control" name="ciudad" id="ciudad" required>
									<?php
									$conexion = conenta_login();
									$res = mysqli_query($conexion,"SELECT ciu.codigo as codigo, ciu.nombre as nombre FROM ciudad AS ciu INNER JOIN empresas as emp ON emp.cod_ciudad = ciu.codigo WHERE emp.cod_prov = '".$provincia."' and emp.ruc='".$ruc_empresa."' ");
									?> 
									<option value="">Seleccione una ciudad</option>
									<?php
									while($c = mysqli_fetch_assoc($res)){
									if ($c['codigo'] == $ciudad){
									?>
									<option value=<?php echo $ciudad; ?> selected><?php echo $c['nombre']; ?> </option>
									<?php
									}else{			
									?>
									<option value="<?php echo $c['codigo']; ?>"><?php echo $c['nombre']; ?> </option>
									<?php
									}
									}
								?>
							</select>
						</div>
						</div>
					 </div>
					 <div id="resultados_ajax"></div>
					</div>
					<div class="modal-footer">
					   <button type="submit" class="btn btn-primary" id="guardar_perfil_empresa" >Guardar</button>
					</div>
            </form>
			
		</div>
	</div>
	</div>

<?php }else{
header('Location: ../includes/logout.php');
exit;
}
?>

<script type="text/javascript" src="../js/style_bootstrap.js"> </script>
<script src="../js/notify.js"></script>
</body>

</html>
<script>
//para pasar archivos file debe ser este codigo ajax 
$(function(){
        $("#editar_empresa").on("submit", function(e){
            e.preventDefault();
            var f = $(this);
            var formData = new FormData(document.getElementById("editar_empresa"));
            formData.append("dato", "valor");
            $.ajax({
                url: "../ajax/editar_perfil_empresa.php",
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
	     processData: false
            })
                .done(function(res){
                    $("#resultados_ajax").html(res);
                });
        });
    });

</script>

