<!DOCTYPE html>
<html lang="es">
  <head>
  <title>Configurar usuarios</title>
  <?php include("../head.php");?>
  <style type="text/css">
		 ul.ui-autocomplete {
			z-index: 1100;
		}
</style>
  </head>
  <body >
 	<?php
include("../conexiones/conectalogin.php");
session_start();
if($_SESSION['nivel'] >= 2){
$titulo_info ="Configurar permisos y accesos de usuarios. ";
include("../navbar_confi.php");
?>
    <div class="container">
	<div class="col-md-10 col-md-offset-1">
		<div class="panel panel-info">
		<div class="panel-heading">
			<?php
			$con = conenta_login();
			$id_usuario = $_SESSION['id_usuario'];
			$sql_usuario = mysqli_query($con,"SELECT * FROM usuarios where id= '".$id_usuario."'");
			$nombre_usuario = mysqli_fetch_array($sql_usuario);
			?>
			<h4><i class='glyphicon glyphicon-search'></i> Usuarios administrados por <?php echo $nombre_usuario['nombre'] ?> <span id="loader"></span></h4>
		</div>			
			<div class="panel-body">
			<?php
				include("../modal/asignar_empresas.php");
				include("../modal/asignar_modulos.php");
			?>
			<form class="form-horizontal" >
						<div class="form-group row">
							<div class="col-md-6">
							<input type="hidden" id="ordenado" value="usu_asi.id">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
							<span class="input-group-addon"><b>Buscar usuarios</b></span>
								<input type="text" class="form-control" id="q" placeholder="Registrados" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-info" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<div class="col-md-6">
							<input type="hidden" id="id_usuario_agregar" >
							<div class="input-group">
								<span class="input-group-addon"><b>Agregar nuevo</b></span>
								<input type="text" class="form-control" name="usuario_agregar" id="usuario_agregar" placeholder="Usuario" onkeyup='buscar_usuarios();'>
								 <span class="input-group-btn">
									<button type="button" title="Agregar nuevo usuario" class="btn btn-info" onclick='agregar_usuario();'><span class="glyphicon glyphicon-plus" ></span></button>
								  </span>
							</div>
							</div>
	
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
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> 
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
</body>
<script>
$(document).ready(function(){
	load(1);
});

function load(page){
	var q= $("#q").val();
	var busca_empresa= $("#busca_empresa").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	var id_usuario_asignado = $("#id_usuario_asignado").val();
	var id_empresa_asignada = $("#id_empresa_asignada").val();
	var nombre_usuario = $("#usuario_asignado").val();
	var nombre_empresa = $("#empresa_asignada").val();
	$("#loader").fadeIn('slow');
	//para buscar usuarios asignados
	$.ajax({
		url:'../ajax/buscar_usuarios_asignados.php?action=usuarios_asignados&page='+page+'&q='+q+'&ordenado='+ordenado+'&por='+por,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif">');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');	
		}
	});
}	

function buscar_empresas_asignadas(page){
	var id_usuario_asignado = $("#id_usuario_asignado").val();
	var id_empresa_asignada = $("#id_empresa_asignada").val();
	var nombre_usuario = $("#usuario_asignado").val();
	var nombre_empresa = $("#empresa_asignada").val();
	buscar_empresa_asignada(id_usuario_asignado, nombre_usuario, page);
}

function buscar_modulos_asignados(page){
	var id_usuario_asignado = $("#id_usuario_asignado").val();
	var id_empresa_asignada = $("#id_empresa_asignada").val();
	var nombre_usuario = $("#usuario_asignado").val();
	var nombre_empresa = $("#empresa_asignada").val();
	buscar_modulo_asignado(id_empresa_asignada, nombre_empresa ,id_usuario_asignado, page);
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
	
function eliminar_usuario_asignado(id_registro){
		var q= $("#q").val();
		if (confirm("Realmente desea quitar el usuario?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_usuarios_asignados.php",
        data: "action=eliminar_usuario_asignado&id_registro="+id_registro,
		 beforeSend: function(objeto){
			$("#loader").html("Eliminando...");
		  },
        success: function(datos){
		$("#loader").html(datos);
		load(1);
		}
			});
		}
}

function quitar_empresa_asignada(id_registro){
		if (confirm("Realmente desea quitar la empresa y los módulos asignados a este usuario?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_usuarios_asignados.php",
        data: "action=eliminar_empresa_asignada&id_registro="+id_registro,
		 beforeSend: function(objeto){
			$("#loader_empresa").html("Eliminando...");
		  },
        success: function(datos){
		$("#loader_empresa").html(datos);
		load(1);
		}
			});
		}
}

//buscar usuarios
function buscar_usuarios(){
	$("#usuario_agregar").autocomplete({
		source:'../ajax/usuarios_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_usuario_agregar').val(ui.item.id);
				$('#usuario_agregar').val(ui.item.nombre);
			}
		});
		$("#usuario_agregar" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar	
		$("#usuario_agregar" ).on( "keydown", function( event ) {
			if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
			{
				$("#id_usuario_agregar" ).val("");
				$("#usuario_agregar" ).val("");

			}
			if (event.keyCode==$.ui.keyCode.DELETE){
				$("#id_usuario_agregar" ).val("");
				$("#usuario_agregar" ).val("");
			}
		});
}

//buscar empresas
function buscar_empresas(){
	$("#empresa_agregar").autocomplete({
			source:'../ajax/empresas_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_empresa_agregar').val(ui.item.id_empresa);
				$('#empresa_agregar').val(ui.item.nombre_comercial);
			}
		});

		$("#empresa_agregar" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#empresa_agregar" ).on( "keydown", function( event ) {
			if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
			{
				$("#id_empresa_agregar" ).val("");
				$("#empresa_agregar" ).val("");

			}
			if (event.keyCode==$.ui.keyCode.DELETE){
				$("#id_empresa_agregar" ).val("");
				$("#empresa_agregar" ).val("");
			}
		});
}


//agregar usuarios
function agregar_usuario(){
		var id_usuario_agregar = $("#id_usuario_agregar").val();
		if (id_usuario_agregar==""){
			alert('Seleccione un usuario');
			document.getElementById('usuario_agregar').focus();
			return false;
			}
		
		$.ajax({
			type: "POST",
			url: "../ajax/buscar_usuarios_asignados.php",
			data: "action=asignar_usuario&id_usuario_agregar="+id_usuario_agregar,
			 beforeSend: function(objeto){
				$("#loader").html("Agregando...");
			  },
			success: function(datos){
			$("#resultados").html(datos);
			$("#loader").html('');
			$("#id_usuario_agregar").val("");
			$("#usuario_agregar").val("");
			load(1);
			}
		});
}

//agregar empresas
function agregar_empresas(){
		var id_empresa_agregar = $("#id_empresa_agregar").val();
		var id_usuario_agregar = $("#id_usuario_asignado").val();
		
		if (id_empresa_agregar==""){
			alert('Seleccione una empresa');
			document.getElementById('empresa_agregar').focus();
			return false;
			}
		
		$.ajax({
			type: "POST",
			url: "../ajax/buscar_usuarios_asignados.php",
			data: "action=asignar_empresa&id_empresa_agregar="+id_empresa_agregar+"&id_usuario_agregar="+id_usuario_agregar,
			 beforeSend: function(objeto){
				$("#loader_empresa").html("Agregando...");
			  },
			success: function(datos){
			$("#outer_divdet_empresa").html(datos);
			$("#loader_empresa").html('');
			$("#id_empresa_agregar").val("");
			$("#empresa_agregar").val("");
			load(1);
			}
		});
}

//buscar empresas asignadas
function buscar_empresa_asignada(id_usuario_asignado, nombre_usuario, page){
	var busca_empresa = $("#busca_empresa").val();
	$("#id_usuario_asignado").val(id_usuario_asignado);
	$("#usuario_asignado").val(nombre_usuario);
	$.ajax({
		url:'../ajax/buscar_usuarios_asignados.php?action=buscar_empresas_asignadas&id_usuario_asignado='+id_usuario_asignado+'&busca_empresa='+busca_empresa+'&page='+page,
		 beforeSend: function(objeto){
		 $('#outer_divdet_empresa').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_divdet_empresa").html(data).fadeIn('slow');
			$('#outer_divdet_empresa').html('');			
		}
	});
}

//buscar modulos asignados
function buscar_modulo_asignado(id_empresa_asignada, nombre_empresa ,id_usuario_seleccionado, page){
	var busca_modulo= $("#busca_modulo").val();
	$("#id_empresa_asignada").val(id_empresa_asignada);
	var usuario_asignado= $("#usuario_asignado").val();
	$("#usuario_asignado_modulos").val(usuario_asignado);
	$("#empresa_asignada").val(nombre_empresa);
	$("#outer_divdet_modulos").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_usuarios_asignados.php?action=buscar_modulos_asignados&id_empresa_asignada='+id_empresa_asignada+'&id_usuario_seleccionado='+id_usuario_seleccionado+'&busca_modulos='+busca_modulo+'&page='+page,
		 beforeSend: function(objeto){
		 $('#outer_divdet_modulos').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_divdet_modulos").html(data).fadeIn('slow');
			$('#outer_divdet_modulos').html('');			
		}
	});
}

//agregar modulos a usuarios
function agregar_modulo(id_usuario, id_empresa, id_modulo, id_submodulo){
	if (id_usuario==0) {
	alert('Seleccione usuario para asignar módulos');
	return false;
	}
	if (id_empresa==0) {
	alert('Seleccione empresa para asignar módulos');
	return false;
	}
		$.ajax({
        type: "POST",
        url: '../ajax/agregar_modulos.php',
        data: 'id_sub_modulo='+id_submodulo+'&id_sub_usuario='+id_usuario+'&id_sub_empresa='+id_empresa+'&id_modulo='+id_modulo,
		 beforeSend: function(objeto){
			$("#loader_modulo").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#loader_modulo").html(datos);
		}
			});
	//event.preventDefault();
};
</script>
</html>