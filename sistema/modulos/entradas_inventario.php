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
  <title>Entradas inventarios</title>
	<?php include("../paginas/menu_de_empresas.php");
		  ini_set('date.timezone','America/Guayaquil');
		  include("../modal/entradas_inventario.php");
		  include("../modal/editar_entradas_inventario.php");
	?>
<style type="text/css">
 ul.ui-autocomplete {
    z-index: 1100;
}
</style>
  </head>
  <body>

<div class="container-fluid">  
    <div class="panel panel-success">
		<div class="panel-heading">
		<div class="btn-group pull-right">
			<button type='submit' class="btn btn-success" data-toggle="modal" data-target="#NuevaEntrada"><span class="glyphicon glyphicon-plus" ></span> Nueva entrada</button>
		</div>
			<h4><i class='glyphicon glyphicon-search'></i> Entradas de inventarios</h4>		
		</div>
		<div class="panel-body">
			<form class="form-horizontal" role="form" method ="POST" action="" >
						<div class="form-group row">
						<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="id_inventario">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
							
								<input type="text" class="form-control" id="q" placeholder="Nombre, referencia" onkeyup='load(1);'>
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
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
 </body>
</html>
<script>
jQuery(function($){
     $("#fecha_entrada").mask("99-99-9999");
	 $("#fecha_caducidad").mask("99-99-9999");
	 $("#mod_fecha_entrada").mask("99-99-9999");
	 $("#mod_fecha_caducidad").mask("99-99-9999");
	 $("#entrada_desde").mask("99-99-9999");
	 $("#entrada_hasta").mask("99-99-9999");
});

$( function() {
$("#entrada_desde").datepicker({
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
$("#entrada_desde").datepicker("setDate", "-1m");

$("#entrada_hasta").datepicker({
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

	
	$("#fecha_entrada").datepicker({
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

	
$("#fecha_caducidad").datepicker({
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

$("#mod_fecha_entrada").datepicker({
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

$("#mod_fecha_caducidad").datepicker({
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

	} );


function agregar_productos(){
	$("#nombre_producto").autocomplete({
			source:'../ajax/productos_autocompletar_inventario.php',
			minLength: 2,
			select: function(event, ui) {
				event.preventDefault();
				$('#id_producto').val(ui.item.id);
				$('#codigo_producto').val(ui.item.codigo);
				$('#nombre_producto').val(ui.item.nombre);
				$('#precio_producto').val(ui.item.precio);
				$('#unidad_medida').val(ui.item.unidad_medida);
				var producto= $("#id_producto").val();
				var consultar_costo= "consultar_costo";
				
				//para ver el costo dell producto del ultimo registro
				$.post( '../ajax/buscar_entradas_inventarios.php', {action: consultar_costo, id_producto: producto}).done( function( respuesta_costo ){
					var costo_producto = respuesta_costo;
					$("#costo_producto").val(costo_producto);
				});
				document.getElementById('cantidad').focus();
			}

		});
 }
 

$("#nombre_producto" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_producto" ).val("");
			$("#nombre_producto" ).val("");
			$("#precio_producto" ).val("");
			$("#codigo_producto" ).val("");
			$("#unidad_medida" ).val("");
			$("#costo_producto" ).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#nombre_producto" ).val("");
			$("#id_producto" ).val("");
			$("#precio_producto" ).val("");
			$("#codigo_producto" ).val("");
			$("#unidad_medida" ).val("");
			$("#costo_producto" ).val("");
		}
});


$(document).ready(function(){
	load(1);
});


function load(page){
	var q= $("#q").val();
	var por= $("#por").val();
	var ordenado= $("#ordenado").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_entradas_inventarios.php?action=ajax&page='+page+'&q='+q+"&ordenado="+ordenado+"&por="+por,
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

$( "#guardar_entrada" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/nueva_entrada_inventario.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_entradas").html("Mensaje: Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_entradas").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
});

//para eliminar una entrada
function eliminar_entrada(id){
		var q= $("#q").val();
		var tipo_registro = $("#tipo_registro"+id).val();
	//Inicia validacion
			
		if (confirm("Realmente desea eliminar la entrada de inventario?")){	
		$.ajax({
        type: "GET",
       url:'../ajax/buscar_entradas_inventarios.php?action=eliminar_entrada',
        data: "id_entrada="+id,"q":q,
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
			var id_inventario = $("#id_inventario"+id).val();
			var nombre_producto = $("#nombre_producto"+id).val();
			var fecha_registro = $("#fecha_registro"+id).val();
			var fecha_vencimiento = $("#fecha_vencimiento"+id).val();
			var cantidad = $("#cantidad"+id).val();
			var costo_unitario = $("#costo_unitario"+id).val();
			var tipo_medida = $("#tipo_medida"+id).val();
			var medida = $("#medida"+id).val();
			var lote = $("#lote"+id).val();
			var bodega = $("#bodega"+id).val();
			var referencia = $("#referencia"+id).val();
			$("#mod_id_inventario").val(id_inventario);
			$("#mod_nombre_producto").val(nombre_producto);
			$("#mod_fecha_entrada").val(fecha_registro);
			$("#mod_fecha_caducidad").val(fecha_vencimiento);
			$("#mod_cantidad").val(cantidad);
			$("#mod_costo_producto").val(costo_unitario);
			$("#mod_tipo_medida").val(tipo_medida);
			$("#mod_unidad_medida").val(medida);
			$("#mod_bodega").val(bodega);
			$("#mod_lote").val(lote);
			$("#mod_referencia").val(referencia);
			
			$.post( '../ajax/select_tipo_medida.php', {tipo_med: tipo_medida}).done( function( respuesta ){
				$("#mod_unidad_medida").html(respuesta);
			});
			

	}

//editar una entrada
$( "#editar_entrada" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/editar_entradas_inventario.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_editar_entradas").html("Mensaje: Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_editar_entradas").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
});



</script>