<?php

session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	
	

?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <title>Alumnos</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/nuevo_nivel_alumno.php");
		  include("../modal/editar_nivel_alumno.php");
	?>
  </head>
  <body>	
	
    <div class="container">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="btn-group pull-right">
					<a href="#" data-toggle="modal" data-target="#nuevoNivelAlumno" class="btn btn-info"><span class="glyphicon glyphicon-plus"></span> Nuevo Nivel</a>
				</div>
				<h4><i class='glyphicon glyphicon-search'></i> Buscar nivel o paralelo</h4>	
			</div>
			<div class="panel-body">
				<form class="form-horizontal" role="form" id="datos_cotizacion">
							<div class="form-group row">
								<label for="q" class="col-md-2 control-label">Buscar:</label>
								<div class="col-md-5">
									<input type="text" class="form-control" id="q" placeholder="Nombre" onkeyup='load(1);'>
								</div>
								<div class="col-md-3">
									<button type="button" class="btn btn-default" onclick='load(1);'>
										<span class="glyphicon glyphicon-search" ></span> Buscar</button>
									<span id="loader"></span>
								</div>
							</div>
				</form>
				<div id="resultados"></div><!-- Carga los datos ajax -->
				<div class='outer_div'></div><!-- Carga los datos ajax -->
			</div>
		</div>
	</div>


<?php
}else{
header('Location: ../includes/logout.php');
exit;
}
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
 </body>
</html>
<script>

$(document).ready(function(){
			load(1);
		});

function load(page){
			var q= $("#q").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_nivel_alumnos.php?action=ajax&page='+page+'&q='+q,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			})
};

function eliminar_nivel_alumno(id_nivel){
			var id_nivel = $("#id_nivel"+id_nivel).val();
		if (confirm("Realmente desea eliminar el nivel?")){	
		$.ajax({
        type: "POST",
        url: "../ajax/buscar_nivel_alumnos.php",
        data: "id_nivel="+id_nivel,
		 beforeSend: function(objeto){
			$("#resultados").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#resultados").html(datos);
		load(1);
		}
			});
		}
};

function pasa_datos_editar_nivel_alumno(id_nivel){
		var id_nivel = $("#id_nivel"+id_nivel).val();
		var nombre_nivel = $("#nombre_nivel"+id_nivel).val();
		$("#edit_id_nivel").val(id_nivel);
		$("#edit_nombre_nivel").val(nombre_nivel);
};


$( "#guardar_nivel_alumno" ).submit(function( event ) {
		  $('#guardar_datos_nivel').attr("disabled", true);
		  
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: "../ajax/nuevo_nivel_alumno.php",
					data: parametros,
					 beforeSend: function(objeto){
						$("#resultados_ajax_nivel_alumnos").html("Mensaje: Cargando...");
					  },
					success: function(datos){
					$("#resultados_ajax_nivel_alumnos").html(datos);
					$('#guardar_datos_nivel').attr("disabled", false);
					load(1);
				  }
			});
		  event.preventDefault();
})

$( "#editar_nivel_alumno" ).submit(function( event ) {
		  $('#guardar_datos_nivel').attr("disabled", true);
		  
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: "../ajax/editar_nivel_alumno.php",
					data: parametros,
					 beforeSend: function(objeto){
						$("#resultados_ajax_edit_nivel_alumnos").html("Mensaje: Cargando...");
					  },
					success: function(datos){
					$("#resultados_ajax_edit_nivel_alumnos").html(datos);
					$('#guardar_datos_nivel').attr("disabled", false);
					load(1);
				  }
			});
		  event.preventDefault();
})


</script>