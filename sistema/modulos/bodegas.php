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
  <meta charset="utf-8">
  <title>Bodegas</title>
	<?php include("../paginas/menu_de_empresas.php");
	include("../modal/bodegas.php");
	?>
  </head>
  <body>

<div class="container">  
	<div class="col-md-8 col-md-offset-2">
    <div class="panel panel-info">
		<div class="panel-heading">
		<div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#bodegas" onclick="titulo_bodega('Nueva bodega');"><span class="glyphicon glyphicon-plus" ></span> Nueva bodega</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Bodegas</h4>		
		</div>
		<div class="panel-body">
			<form class="form-horizontal" role="form" >
				<div class="form-group row">
					<label for="q" class="col-md-1 control-label">Buscar:</label>
					<div class="col-md-8">
					<div class="input-group">
						<input type="text" class="form-control" id="q" placeholder="Nombre" onkeyup='load(1);'>
						 <span class="input-group-btn">
							<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
						 </span>
					</div>
					</div>
					<span id="loader"></span>
				</div>
			</form>
			<div id="resultados"></div><!-- Carga los datos ajax -->
			<div class='outer_div'></div><!-- Carga los datos ajax -->
		</div>
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

//para poner el nombre en la ventana modal
function titulo_bodega(titulo){
	$("#TituloModal").html(titulo);
	document.getElementById("nombre_bodega").value="";
	};
	
//para cargar al entrar a la pagina
		$(document).ready(function(){
			load(1);
		});
//para buscar las bodegas al cargar
		function load(page){
			var q= $("#q").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/bodegas.php?action=buscar_bodegas&page='+page+'&q='+q,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
					
				}
			})
		}
		
//para guardar y editar una bodega		
$( "#guarda_bodega" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url:'../ajax/bodegas.php?action=guardarYeditar_bodegas',
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_bodegas").html("Mensaje: Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_bodegas").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
});		

//para eliminar una bodega

function eliminar_bodega(id){
			var q= $("#q").val();
		if (confirm("Realmente deseas eliminar la bodega?")){	
		$.ajax({
        type: "GET",
       url:'../ajax/bodegas.php?action=eliminar_bodega',
        data: "id_bodega="+id,"q":q,
		 beforeSend: function(objeto){
			$("#resultados").html("Mensaje: Eliminando...");
		  },
        success: function(datos){
		$("#resultados").html(datos);
		load(1);
		}
			});
		}
}


function obtener_datos(id){
			var id_bodega = $("#id_bodega"+id).val();
			var nombre_bodega = $("#nombre_bodega"+id).val();
			$("#id_bodega").val(id_bodega);
			$("#nombre_bodega").val(nombre_bodega);
			$("#TituloModal").html("Editar bodega");
	}

</script>