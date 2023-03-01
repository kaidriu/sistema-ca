<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

	?>
<!DOCTYPE html>
<html lang="es">
  <head>
  <title>Asesores ventas</title>
	<?php include("../paginas/menu_de_empresas.php");?>
  </head>
  <body>
 	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
		    <div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#nuevoVendedor"><span class="glyphicon glyphicon-plus" ></span> Nuevo Asesor</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Asesores de ventas</h4>
		</div>			
			<div class="panel-body">
			<?php
				include("../modal/registro_vendedores.php");
				include("../modal/editar_vendedor.php");
			?>
			<form class="form-horizontal" method ="POST">
				<div class="form-group row">
					<label for="q" class="col-md-1 control-label">Buscar:</label>
					<div class="col-md-5">
					<input type="hidden" id="ordenado" value="nombre">
					<input type="hidden" id="por" value="asc">
					<div class="input-group">
						<input type="text" class="form-control" id="q" placeholder="Nombre, dirección, correo, cedula, teléfono" onkeyup='load(1);'>
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
<?php

}else{
header('Location: ../includes/logout.php');
exit;
}
?>
<script src="../js/notify.js"></script>
</body>
</html>
<script>
$(document).ready(function(){
	load(1);
});

function load(page){
	var q= $("#q").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_vendedores.php?action=buscar_vendedores&page='+page+'&q='+q+"&ordenado="+ordenado+"&por="+por,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
			
		}
	})
}	
function ordenar(ordenado){
	$("#ordenado").val(ordenado);
	var por= $("#por").val();
	var q= $("#q").val();
	var ordenado= $("#ordenado").val();
	$("#loader").fadeIn('slow');
	var value_por=document.getElementById('por').value;
			if (value_por=="asc"){
			$("#por").val("desc");
			}
			if (value_por=="desc"){
			$("#por").val("asc");
			}
	load(1);
}		
	
	
$( "#guardar_vendedor" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/nuevo_vendedor.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax").html("Mensaje: Cargando...");
			  },
			success: function(datos){
			$("#resultados_ajax").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

$( "#editar_vendedores" ).submit(function( event ) {
  $('#actualizar_datos').attr("disabled", true);
  var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/editar_vendedor.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax2").html("Mensaje: Cargando...");
			  },
			success: function(datos){
			$("#resultados_ajax2").html(datos);
			$('#actualizar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

function obtener_datos(id){
			var nombre_vendedor = $("#nombre_vendedor"+id).val();
			var tipo_id = $("#tipo_id"+id).val();
			var numero_id = $("#numero_id"+id).val();
			var telefono = $("#telefono"+id).val();
			var correo_vendedor = $("#correo"+id).val();
			var direccion = $("#direccion"+id).val();
			$("#mod_nombre").val(nombre_vendedor);
			$("#mod_tipo_id").val(tipo_id);
			$("#mod_numero_id").val(numero_id);
			$("#mod_telefono").val(telefono);
			$("#mod_correo").val(correo_vendedor);
			$("#mod_direccion").val(direccion);
			$("#mod_id_vendedor").val(id);
		}

function eliminar_vendedor(id){
			var q= $("#q").val();
		if (confirm("Realmente desea eliminar el vendedor?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_vendedores.php",
        data: "action=eliminar_vendedor&id_vendedor="+id,"q":q,
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