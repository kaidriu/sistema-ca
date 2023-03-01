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
  <title>Existencias</title>
<?php 
include("../paginas/menu_de_empresas.php");
date_default_timezone_set('America/Guayaquil');
?>
  </head>
  <body>
 	
    <div class="container">
		<div class="panel panel-info">
			<div class="panel-heading">		
				<h4><i class='glyphicon glyphicon-list-alt'></i> Existencias en consignación por ventas</h4>
			</div>
		<div class="panel-body">
		<form class="form-horizontal" role="form" method ="POST" action="../excel/reporte_existencias_cv.php?action=existencia_consignacion_ventas" >
				<div class="form-group row">
					<input type="hidden" id="ordenado" value="nombre_producto">
					<input type="hidden" id="por" value="asc">
					
					<div class="col-sm-3">
					<div class="input-group">
						<span class="input-group-addon"><b>Buscar por:</b></span>
							<select class="form-control input-sm" id="tipo_existencia" name="tipo_existencia" required>
							<option value="1" selected> Clientes</option>
							<option value="2" > No. CV</option>
							<option value="3" > Producto</option>
							<option value="4" > NUP</option>
							</select>
					</div>
					</div>
					<div class="col-sm-6">
					<div class="input-group">
						<span class="input-group-addon"><b>Nombre/NCV:</b></span>
							<input type="hidden" name="id_nombre_buscar" id="id_nombre_buscar" >
							<input type="text" class="form-control input-sm text-left" name="nombre_buscar" id="nombre_buscar" onkeyup='buscar_cliente();'>
					</div>
					</div>
				
					<div class="col-sm-2">
						<button type="button" title="Mostrar resultado" class="btn btn-info btn-sm" onclick="buscar_existencia()"><span class="glyphicon glyphicon-search" ></span></button>				
						<button type="submit" title="Descargar excel" class="btn btn-success btn-sm"><img src="../image/excel.ico" width="16" height="16"></button>
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
</body>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/ordenado.js"></script>
</html>
<script>
function buscar_existencia(){
	var tipo_existencia= $("#tipo_existencia").val();
	var id_nombre_buscar= $("#id_nombre_buscar").val();
	var nombre_buscar= $("#nombre_buscar").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_existencias_consignacion.php?action=existencia_consignacion_ventas&tipo_existencia='+tipo_existencia+'&id_nombre_buscar='+id_nombre_buscar+"&nombre_buscar="+nombre_buscar,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif">');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
		}
	})
}

function buscar_cliente(){
	var nombre_informe = $("#tipo_existencia").val();
		//buscar por cliente
		if (nombre_informe == '1' ){
			$("#nombre_buscar").autocomplete({
				source:'../ajax/clientes_autocompletar.php',
				minLength: 2,
				select: function(event, ui){
					event.preventDefault();
					$('#id_nombre_buscar').val(ui.item.id);
					$('#nombre_buscar').val(ui.item.nombre);
				}
			});
		}
	//buscar producto
	if (nombre_informe == '3' ){
	$("#nombre_buscar").autocomplete({
		source: '../ajax/productos_autocompletar.php',
		minLength: 2,
		select: function(event, ui) {
			event.preventDefault();
			$('#id_nombre_buscar').val(ui.item.id);
			$('#nombre_buscar').val(ui.item.nombre);
			}
		});
	}

	$("#nombre_buscar" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#nombre_buscar" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_nombre_buscar" ).val("");
			$("#nombre_buscar" ).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#id_nombre_buscar" ).val("");
			$("#nombre_buscar" ).val("");
		}
		});
 }

 $('#tipo_existencia').change(function(){	
		$("#id_nombre_buscar" ).val("");
		$("#nombre_buscar" ).val("");
		document.getElementById('nombre_buscar').focus();
	});
 
</script>