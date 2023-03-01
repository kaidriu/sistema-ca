<?php
include("conexiones/conectalogin.php");
function get_form_login(){
?>
<br>
<br>
<br>
<div class="row">
<div class="container-center">
<div class="col-sm-4 col-sm-offset-4">
<div class="panel panel-info">
	<div class="panel-heading">
	<div class="panel-title"><h4><a href="https://www.camagare.com">CaMaGaRe.com</a></h4></div>	
			<div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="https://v2.camagare.com/">Recuperar contraseña</a></div>
	</div>
			<div style="padding-top:30px" class="panel-body">
				<form class="form-horizontal" method="post" id="log" autocomplete="off">
				<div style="margin-top:10px" class="form-group">
						<div class="col-md-2">
						</div>
					<div class="col-md-8" align="center">
						<div class="input-group">
						<span class="input-group-addon" ><span class="glyphicon glyphicon-user"></span></span>
							<input class="form-control" placeholder="Cedula"  id="cedula" title="Cedula" name="cedula" type="text" maxlength="10" autofocus="" required>
						</div>
					</div>
				</div>
					<div style="margin-top:10px" class="form-group">
						<div class="col-md-2">
						</div>
						<div class="col-md-8" align="center">
						<div class="input-group">
					  <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-lock"></span></span>
							<input class="form-control" placeholder="Contraseña" title="Password" id="password" name="password" type="password" maxlength="30" autocomplete="off" required>
						</div>
						</div>
					</div>		
					
					<div style="margin-top:30px" class="form-group">
						<div class="col-md-2">
						</div>
						<div class="col-md-8" align="center">
							<button type="submit" class="btn btn-success" onclick="cifrar()" name="login" id="submit">Iniciar Sesión</button>	
						</div>
					</div>
					
<!--
					<div class="form-group">
						<div class="col-md-12 control">
							<div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
								Para registrarse como nuevo usuario! 
							<a href="#" data-toggle="modal" data-target="#nuevoUsuario"> Click aqui </a>
							</div>
						</div>
                    </div>
					-->
				</form>
			</div>
			
</div>
</div>
</div>
</div>

<div class="col-md-6 col-md-offset-3">
<div class="panel-body">
<div class="navbar navbar-primary navbar-fixed-bottom">
<p class="navbar-text pull-center" > &copy <?php echo date('Y');?> - Cmg Servicios. <a href="https://www.camagare.com" target="_blank" style="color: #E67E22"><strong>Para más información sobre camagare.com, aquí.</strong></a></p>
</div>
</div>
</div>

    <?php
    include("../sistema/modal/nuevo_usuario.php");
	include("../sistema/modal/recuperar_clave.php");
    ?>
<?php
}
 ?>

<?php
//para validar el inicio de sesion
function valida_login($cedula,$password){
	$conexion = conenta_login(); 
	$datos_usuario = mysqli_query($conexion," SELECT * FROM usuarios WHERE cedula = '".$cedula."' AND password='".$password."' and estado = '1'");
	if($user = mysqli_fetch_array($datos_usuario)){
		return $user;
	}else{
		return false;
	}
	mysqli_close($conexion);
}

//para desplegar el menu
function display_menu($nivel){
	//para traer datos del usuario sobre el nivel que tiene para el sistema
	$conexion = conenta_login();
	$sql = "SELECT * FROM menu WHERE nivel BETWEEN 0 AND '".$nivel."' and estado=1 order by etiqueta asc;";
	$datos_opciones_menu = $conexion->query($sql);
	
// de aqui para abajo es para hacer el encabezado del menu donde estan las empresas y demas opciones de cada usuario
?>
<div class="col-md-12">
	<div class="panel panel-primary">
	<div class="panel-heading">
		<div class="btn-group pull-left">
				<h5><span class="glyphicon glyphicon-user" ></span> <?php echo ucwords(strtolower($_SESSION['nombre'])) ?> </h5>
		</div>
				<h4 class="text-center"><?php echo actual_date();?></h4>
	</div>
	
<div class="panel-body">
<div class="container-fluid">	
	  <div class="row">
		 <div class="col-md-4">
			<?php include("../sistema/paginas/avisos_tareas_actividades.php"); ?>
		</div>
	
<!--para mostrar el buscador de empresas asignadas -->

<?php
	$con = conenta_login();
	$id_usuario = $_SESSION['id_usuario'];
	$datos_asignadas = mysqli_query($con, "SELECT * FROM empresa_asignada emp_asi INNER JOIN empresas emp ON emp_asi.id_empresa=emp.id WHERE emp_asi.id_usuario = '".$id_usuario."' and emp.estado='1'");
	$count = mysqli_num_rows($datos_asignadas);
		if ($count > 10) {
?>
	 <div class="col-md-4">
		 <div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-briefcase'></i> Empresas <span class="pull-right"><span id="loader"></span></span></h4>
		</div>
		<ul class="list-group">	
					<div class="col-md-12">		
					<form class="form-horizontal" role="form" >
						  <input type="text" class="form-control" id="q" placeholder="Buscar empresas"  onkeyup='load(1);'>
					</form>
					</div>
			 <div id="resultados"></div><!-- Carga los datos ajax  de ajax/buscar_empresa_asignada.php-->
			<div class='outer_div'></div><!-- Carga los datos ajax -->
		</ul>
		</div>
	</div>
	<?php
		}else{
		?>
		<div class="col-md-4">
		 <div class="panel panel-info">
			<div class="panel-heading">
				<h4><i class='glyphicon glyphicon-briefcase'></i> Empresas <span class="pull-right"><span id="loader"></span></span></h4>
			</div>	
			<ul class="list-group">		
				 <form class="form-horizontal" role="form">
					<input type="hidden" class="form-control" id="q" placeholder="Empresas" onkeyup='load(1);'>
				 </form>
			<div id="resultados"></div><!-- Carga los datos ajax  de ajax/buscar_empresa_asignada.php-->
			<div class='outer_div'></div><!-- Carga los datos ajax -->
			</ul>
		</div>
	   </div>
		<br>
		<?php
		}
		mysqli_close($con);
	?>
<!-- opciones de cada usuario en el menu principal-->
			<div class="col-md-4">
				<div class="panel panel-success">
				<div class="panel-heading">
						<h4><i class='glyphicon glyphicon-wrench'></i> Opciones</h4>
					</div>	
				 <ul class="list-group">
					<?php
					while($item = mysqli_fetch_array($datos_opciones_menu)){
					?>
						<a href="<?php echo $item['ruta'] ?> " class="list-group-item list-group-item-success"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> <?php echo $item['etiqueta'] ?> </a>
					<?php
					}
					?>
						<a href="includes/logout.php" class="list-group-item list-group-item-danger"><span class="glyphicon glyphicon-off" ></span> Cerrar sesión </a>
				</ul>
				</div>
			</div>
	</div>
	</div>
	</div>
	</div>
	</div>
<?php
}

function actual_date (){
	ini_set('date.timezone','America/Guayaquil'); 
    $week_days = array ("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");  
    $months = array ("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");  
    $year_now = date ("Y");  
    $month_now = date ("n");  
    $day_now = date ("j");  
    $week_day_now = date ("w");
	$hora = date("H:i:s",time());
    $date = $week_days[$week_day_now] . ", " . $day_now . " de " . $months[$month_now] . " del " . $year_now . " Hora: " . $hora;   
    return $date;    
}  	

?>