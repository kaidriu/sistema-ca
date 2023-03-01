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
  <title>Cuentas bancarias</title>
	<?php 
	include("../paginas/menu_de_empresas.php");
	include("../modal/nueva_cuenta_bancaria.php");
	?>
  </head>
  <body>
    <div class="container">
		<div class="panel panel-info">
		<div class="panel-heading">
		<div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#nuevoCuentaBancaria"><span class="glyphicon glyphicon-plus" ></span> Nueva cuenta bancaria</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Buscar cuenta</h4>
		</div>			
			<div class="panel-body">
			
			<form class="form-horizontal" role="form" id="datos_cotizacion">
				
						<div class="form-group row">
							<label for="q" class="col-md-2 control-label">Cuentas:</label>
							<div class="col-md-5">
								<input type="text" class="form-control" id="q" placeholder="Banco, tipo, cuenta" onkeyup='load(1);'>
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
<script src="../js/notify.js"></script>
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
				url:'../ajax/buscar_cuentas_bancarias.php?action=ajax&page='+page+'&q='+q,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
					
				}
			})
		}		
$( "#guardar_cuentas_banco" ).submit(function( event ) {
		  $('#guardar_cuenta_bancaria').attr("disabled", true);
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: "../ajax/guardar_cuentas_bancarias.php",
					data: parametros,
					 beforeSend: function(objeto){
						$("#resultados_ajax").html("Mensaje: Guardando...");
					  },
					success: function(datos){
					$("#resultados_ajax").html(datos);
					$('#guardar_cuenta_bancaria').attr("disabled", false);
					load(1);
				  }
			});
		  event.preventDefault();
		})
	
function eliminar_cuenta_bancaria(id_cuenta){
			var q= $("#q").val();
		if (confirm("Realmente desea eliminar la cuenta bancaria?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_cuentas_bancarias.php",
        data: "id_cuenta="+id_cuenta,"q":q,
		 beforeSend: function(objeto){
			$("#resultados").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#resultados").html(datos);
		load(1);
		}
			});
		}
}
	</script>