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
  <title>Clientes</title>
	<?php include("../paginas/menu_de_empresas.php");?>
  </head>
  <body>
 	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
		    <div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#nuevoCliente" onclick="carga_modal();"><span class="glyphicon glyphicon-plus" ></span> Nuevo Cliente</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Clientes</h4>
		</div>			
			<div class="panel-body">
			<?php
				include("../modal/clientes.php");
				//include("../modal/editar_clientes.php");
			?>
			<form class="form-horizontal" method ="POST">
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="nombre">
							<input type="hidden" id="por" value="asc">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Nombre, dirección, correo, ruc, teléfono" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<div class="col-md-1">
							<a href="../excel/clientes.php" class="btn btn-success" title='Descargar en Excel' target="_blank"><img src="../image/excel.ico" width="25" height="20"></a>												
							</div>		
							<span id="loader"></span>							
						</div>
			</form>
			<div id="resultados"></div><!-- Carga los datos ajax -->
			<div class="outer_div"></div><!-- Carga los datos ajax -->
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
<script type="text/javascript" src="../js/select_ciudad.js"></script>
</body>
</html>
<script>
$(document).ready(function(){
	window.addEventListener("keypress", function(event){
		if (event.keyCode == 13){
			event.preventDefault();
		}
	}, false);
	load(1);
});

function carga_modal() {
			document.querySelector("#titleModalCliente").innerHTML = "<i class='glyphicon glyphicon-ok'></i> Nuevo Cliente";
			document.querySelector("#guardar_cliente").reset();
			document.querySelector("#id_cliente").value = "";
			document.querySelector("#btnActionFormCliente").classList.replace("btn-info", "btn-primary");
			document.querySelector("#btnTextCliente").innerHTML = "<i class='glyphicon glyphicon-floppy-disk'></i> Guardar";
			document.querySelector('#btnActionFormCliente').title = "Guardar cliente";
		}

function load(page){
	var q= $("#q").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/clientes.php?action=buscar_clientes&page='+page+'&q='+q+"&ordenado="+ordenado+"&por="+por,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Buscando...');
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
	
function editar_cliente(id){
	document.querySelector('#titleModalCliente').innerHTML ="<i class='glyphicon glyphicon-edit'></i> Actualizar Cliente";
	document.querySelector("#guardar_cliente").reset();
	document.querySelector("#id_cliente").value = id;
	document.querySelector('#btnActionFormCliente').classList.replace("btn-primary", "btn-info");
	document.querySelector("#btnTextCliente").innerHTML = "<i class='glyphicon glyphicon-floppy-disk'></i> Actualizar";

			var nombre_cliente = $("#nombre_cliente"+id).val();
			var tipo_id_cliente = $("#tipo_id_cliente"+id).val();
			var ruc_cliente = $("#ruc_cliente"+id).val();
			var telefono_cliente = $("#telefono_cliente"+id).val();
			var email_cliente = $("#email_cliente"+id).val();
			var direccion_cliente = $("#direccion_cliente"+id).val();
			var plazo_p = $("#plazo_pago"+id).val();
			var provincia = $("#provincia"+id).val();
			var ciudad = $("#ciudad"+id).val();
	
			$("#tipo_id").val(tipo_id_cliente);
			$("#ruc").val(ruc_cliente);
			$("#nombre").val(nombre_cliente);
			$("#email").val(email_cliente);
			$("#direccion").val(direccion_cliente);
			$("#telefono").val(telefono_cliente);
			$("#plazo").val(plazo_p);	
			$("#id_cliente").val(id);
			$("#provincia").val(provincia);
			$("#ciudad").val(ciudad);		
		}

function eliminar_cliente(id){
			var q= $("#q").val();
		if (confirm("Realmente desea eliminar el cliente?")){	
		$.ajax({
        type: "POST",
        url: "../ajax/clientes.php?action=eliminar_cliente",
        data: "id_cliente="+id,"q":q,
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
	
/*		
$( "#guardar_cliente" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/clientes.php?action=guardar_cliente",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax").html("Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})
*/

</script>