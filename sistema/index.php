<!DOCTYPE html>
<html lang="es">
<script src='https://www.google.com/recaptcha/api.js'></script>
<head>
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>CaMaGaRe</title>
	<link rel="shortcut icon" type="image/png" href="../sistema/image/logofinal.png"/>
	<?php include("head.php");?>
		
</head>
<body style="background-color: hsla(146, 30%, 61%, 0.67);padding: 2px;">
<?php
session_start();
include("includes/login.php");
if(isset($_POST['login']) && isset($_POST['password']) && isset($_POST['cedula'])){
	$password = $_POST['password'];
	$cedula= $_POST['cedula'];
	$user = valida_login($cedula,$password);
	$identificacion=$user['cedula'];
	$clave=$user['password'];
	if($identificacion===$cedula && $clave===$password){
		session_destroy();
		session_start();
		ini_set('date.timezone','America/Guayaquil');
		$_SESSION['nivel'] = $user['nivel'];
		$_SESSION['nombre'] = $user['nombre'];
		$_SESSION['id_usuario'] = $user['id'];
		$_SESSION["ultimoAcceso"] = date("Y-n-j H:i:s");
		//guarde el registro de entrada de usuario
		include("../sistema/validadores/control_usuarios.php");
		//include("../sistema/includes/cierre_sesion.php");
		control_usuario_entrada($_SESSION['id_usuario'],'entrada');
		echo display_menu($_SESSION['nivel']);
	}else{
		?>
		<div class="progress"><div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width:100%;"><b>Datos de usuario incorrectos, vuelva a intentarlo.</b></div></div>					    	
		<?php
		echo get_form_login();
	}
}elseif (isset($_GET['menu']) && $_SESSION['nivel'] != NULL){
	echo display_menu($_SESSION['nivel']);
}else{
	echo get_form_login();
}
?>
<?php include("pie.php");?>
<script src="js/md5.js"></script>
</body>
<script>
    function cifrar(){
	 var input_pass= MD5($("#password").val());
	  //input_pass.value = sha1(input_pass.value);
	  //alert(MD5(input_pass));
	  $("#password").val(input_pass);
    }
  </script>
</html>

<script>
//para guardar nuevo usuario
$( "#guardar_usuario" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "./ajax/nuevo_usuario.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_usuarios").html("Mensaje: Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_usuarios").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

//para recuperar contraseña
$( "#recuperar_clave" ).submit(function( event ) {
  $('#enviar_datos').attr("disabled", true);

 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "./ajax/recupera_clave.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_clave").html("Mensaje: enviando...");
			  },
			success: function(datos){
			$("#resultados_ajax_clave").html(datos);
			$('#enviar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

//buscador de empresas
		$(document).ready(function(){
			load(1);
		});

function load(page){
			var q= $("#q").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'./ajax/buscar_empresa_asignada.php?action=buscar_empresa_asignada&page='+page+'&q='+q,
				 beforeSend: function(objeto){
				 $('#loader').html('Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			})
	}	
</script>
<?php
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