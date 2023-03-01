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
  <title>Proveedores</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/nuevo_proveedor.php");
		  include("../modal/editar_proveedor.php");
	?>
  </head>
  <body>	
	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
				<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#nuevoProveedor"><span class="glyphicon glyphicon-plus" ></span> Nuevo Proveedor</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Buscar Proveedores</h4>		
		</div>			
			<div class="panel-body">
			<form class="form-horizontal" method ="POST">
						<div class="form-group row">
							<label for="q" class="col-md-2 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="nombre">
							<input type="hidden" id="por" value="asc">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Razon social, ruc, nombre comercial, dirección" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>			
							<div class="col-md-1">
							<a href="../excel/proveedores.php" class="btn btn-success" title='Descargar en Excel' target="_blank"><img src="../image/excel.ico" width="25" height="20"></a>																			
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
function obtener_datos(id){
			var razon_social = $("#razon_social"+id).val();
			var nombre_comercial = $("#nombre_comercial"+id).val();
			var ruc_proveedor = $("#ruc_proveedor"+id).val();
			var telf_proveedor = $("#telf_proveedor"+id).val();
			var dir_proveedor = $("#dir_proveedor"+id).val();
			var tipo_id = $("#tipo_id"+id).val();
			var tipo_empresa = $("#tipo"+id).val();
			var plazo = $("#plazo"+id).val();
			var unidad_tiempo = $("#unidad_tiempo"+id).val();
			var relacionado = $("#relacionado"+id).val();
			var mail_proveedor = $("#mail_proveedor"+id).val();
			
	
			$("#razon_social_mod").val(razon_social);
			$("#nombre_comercial_mod").val(nombre_comercial);
			$("#ruc_proveedor_mod").val(ruc_proveedor);
			$("#telf_proveedor_mod").val(telf_proveedor);
			$("#dir_proveedor_mod").val(dir_proveedor);
			$("#tipo_id_mod").val(tipo_id);
			$("#tipo_empresa_mod").val(tipo_empresa);
			$("#mod_id_proveedor").val(id);
			$("#plazo_mod").val(plazo);
			$("#unidad_tiempo_mod").val(unidad_tiempo);
			$("#relacionado_mod").val(relacionado);
			$("#mail_proveedor_mod").val(mail_proveedor);

		
		}
		
$(document).ready(function(){
	window.addEventListener("keypress", function(event){
		if (event.keyCode == 13){
			event.preventDefault();
		}
	}, false);
	load(1);
});

function load(page){
	var q= $("#q").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_proveedores.php?action=ajax&page='+page+'&q='+q,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
			
		}
	})
}	
		
function eliminar_proveedor(id){
			var q= $("#q").val();
		if (confirm("Realmente deseas eliminar el proveedor?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_proveedores.php",
        data: "action=eliminar_proveedor&id_proveedor="+id,"q":q,
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
		
		
//para guardar el proveedor
$( "#guardar_proveedor" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
  
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/guarda_proveedor.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax").html("Mensaje: Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

$( "#editar_proveedor" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/editar_proveedor.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_editar").html("Mensaje: Cargando...");
			  },
			success: function(datos){
			$("#resultados_ajax_editar").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

//para que cuando se cierre el modal limpiar todo
$("#cerrar_editar").click(function(){
	$("#resultados_ajax_editar").empty();
    });
</script>