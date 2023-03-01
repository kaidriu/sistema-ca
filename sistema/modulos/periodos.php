<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
<title>Períodos contables</title>
<?php include("../head.php");?>
</head>

<body>
<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

include("../paginas/menu_de_empresas.php"); 	
?>
<?php include("../modal/nuevo_periodo.php");?>

 <div class="container">
 <div class="col-md-6 col-md-offset-3">
		<div class="panel panel-info">
		<div class="panel-heading">
		    <div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#nuevoPeriodo"><span class="glyphicon glyphicon-plus" ></span> Nuevo período</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Buscar períodos contables</h4>
		</div>			
			<div class="panel-body">
			<form class="form-horizontal" >
						<div class="form-group row">
							<label for="q" class="col-md-2 control-label">Período:</label>
							<div class="col-md-6">
								<input type="text" class="form-control" id="q" placeholder="Mes, año" onkeyup='load(1);'>
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
	</div>

<?php }else{ ?>
		  <div class="alert alert-danger alert-dismissible" role="alert">
		  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  <strong>Hey!</strong> Usted no tiene permisos para acceder a este sitio! </div>
		 
		  
<?php
}
?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

<script>
		$(document).ready(function(){
			load(1);
		});

		function load(page){
			var q= $("#q").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_periodos.php?action=ajax&page='+page+'&q='+q,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
					
				}
			})
		}	
		
$( "#guardar_periodo" ).submit(function( event ) {
		  $('#guardar_datos_periodo').attr("disabled", true);
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: "../ajax/guardar_periodo.php",
					data: parametros,
					 beforeSend: function(objeto){
						$("#resultados_ajax").html("Mensaje: Cargando...");
					  },
					success: function(datos){
					$("#resultados_ajax").html(datos);
					$('#guardar_datos_periodo').attr("disabled", false);
					load(1);
				  }
			});
		  event.preventDefault();
		})
	
function eliminar_periodo(id_periodo){
			var q= $("#q").val();
		if (confirm("Realmente desea eliminar el período?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_periodos.php",
        data: "id_periodo="+id_periodo,"q":q,
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
</body>

</html>
