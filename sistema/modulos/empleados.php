<?php

session_start();
if($_SESSION['nivel'] >= 1 && isset($_POST['id_usuario']) && isset($_POST['id_empresa'])){

?>
<!DOCTYPE html>
<html lang="es">
  <head>
  <title>Empleados</title>
	<?php include("../paginas/menu_y_empresas.php");?>
	<?php include("../modal/nuevo_empleado.php");?>
	<?php include("../modal/sueldo_empleado.php");?>
  </head>
  <body>
 	
    <div class="container">
		<div class="panel panel-info">
		<div class="panel-heading">
		    <div class="btn-group pull-right">
				<button type='button' class="btn btn-info" data-toggle="modal" data-target="#nuevoEmpleado"><span class="glyphicon glyphicon-plus" ></span> Nuevo Empleado</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Buscar Empleados</h4>
		</div>			
			<div class="panel-body">
			
			<form class="form-horizontal" role="form" id="datos_cotizacion">
				
						<div class="form-group row">
							<label for="q" class="col-md-2 control-label">Nombres:</label>
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
	?>
		<div class="alert alert-danger alert-dismissable">
		<a href="../includes/logout.php" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></a>
		<strong>Hey!</strong"> Usted no tiene permisos para acceder a este sitio! </div>
		 <?php
exit;
}
?>
<script type="text/javascript" src="../js/nuevo_empleado.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  </body>
</html>

<script>
$( "#guardar_empleado" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
  
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/nuevo_empleado.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_empleados").html("Mensaje: Cargando...");
			  },
			success: function(datos){
			$("#resultados_ajax_empleados").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

$(function(){
	$('#rama').change(function(){
			var codigo_cargo = $("#rama").val();
			$.post( '../paginas/select_cargo.php', {cargo: codigo_cargo}).done( function( respuesta )
		{
			$( '#cargo_empleado' ).html( respuesta );
		});

		});	
		
		});


	$("#nacimiento").datepicker({
        dateFormat: "dd-mm-yy",
        firstDay: 1,
        dayNamesMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
        monthNames: 
            ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
            "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        monthNamesShort: 
            ["Ene", "Feb", "Mar", "Abr", "May", "Jun",
            "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"]
});
	
</script>

