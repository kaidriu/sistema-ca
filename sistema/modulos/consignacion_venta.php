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
  <title>Consignaciones ventas</title>
  
	<?php 
	include("../paginas/menu_de_empresas.php");
	//include("../modal/detalle_consignaciones.php");
	?>
	<style type="text/css">
		 ul.ui-autocomplete {
			z-index: 1100;
		}
		
		</style>
  </head>
  <body>
 	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
		    <div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#nueva_consignacion_venta" onclick="carga_modal();"><span class="glyphicon glyphicon-plus" ></span> Nueva consignación</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Consignaciones en ventas</h4>
		</div>			
			<div class="panel-body">
			<?php
				include("../modal/consignacion_venta.php");	
			?>
			<form class="form-horizontal" method ="POST">
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_consignacion">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Cliente, Número, Observaciones" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
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
<link rel="stylesheet" href="../css/jquery-ui.css"> <!--para que se vea con fondo blanco el autocomplete -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	<script src="../js/ordenado.js" type="text/javascript"></script>
	<script src="../js/notify.js"></script>
</body>
</html>
<script>
$(document).ready(function(){
	load(1);
});

jQuery(function($){
     $("#fecha_consignacion_salida").mask("99-99-9999");
	 $("#fecha_pedido").mask("99-99-9999");
	 $("#hora_entrega").mask("99:99"); 
});

function load(page){
	var q= $("#q").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/consignacion_venta.php?action=buscar_consignacion_venta&page='+page+'&q='+q+"&ordenado="+ordenado+"&por="+por,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
		}
	})
}	

function carga_modal_pedidos() {
			document.querySelector("#detalle_pedido").reset();
			$(".outer_divdetpedido").html('').fadeIn('fast');
			//$('#muestra_detalle_pedido').html('');
			document.getElementById('numero_pedido').focus();
		}

//para buscar los clientes
function buscar_clientes(){
	$("#cliente_consignacion_venta").autocomplete({
			source:'../ajax/clientes_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_cliente_consignacion_venta').val(ui.item.id);
				$('#cliente_consignacion_venta').val(ui.item.nombre);		
				document.getElementById('observacion_consignacion_venta').focus();
			}
		});

		$("#cliente_consignacion_venta" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_cliente_consignacion_venta" ).val("");
			$("#cliente_consignacion_venta" ).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#cliente_consignacion_venta" ).val("");
			$("#id_cliente_consignacion_venta" ).val("");
		}
		});
}

//para cargar el modal
function carga_modal(){
	resetea_datos();
	document.getElementById("titulo_lote").style.display="none";
	document.getElementById("titulo_caducidad").style.display="none";
	document.getElementById("titulo_medida").style.display="none";
	document.getElementById("titulo_existencia").style.display="none";
	document.getElementById("lista_lote").style.display="none";
	document.getElementById("lista_caducidad").style.display="none";
	document.getElementById("lista_medida").style.display="none";
	document.getElementById("lista_existencia").style.display="none";
		//para traer el tipo de configuracion de inventarios, si o no
		var serie_consignacion = $("#serie_consignacion").val();
	$("#codigo_unico").val('');//para borrar la info del input
		
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'inventario', serie_consultada:serie_consignacion}).done( function(respuesta_inventario)
		{		
			var resultado_inventario = $.trim(respuesta_inventario);
			$('#inventario').val(resultado_inventario);
		});

		//para traer y ver si trabaja con medida
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'medida',serie_consultada:serie_consignacion}).done( function(respuesta_medida)
		{		
			var resultado_medida = $.trim(respuesta_medida);
			$('#muestra_medida').val(resultado_medida);
		});
		
		//para traer y ver si trabaja con lote
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'lote',serie_consultada:serie_consignacion}).done( function(respuesta_lote)
		{		
			var resultado_lote = $.trim(respuesta_lote);
			$('#muestra_lote').val(resultado_lote);
		});
		
		//para traer y ver si trabaja con bodega
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'bodega',serie_consultada:serie_consignacion}).done( function(respuesta_bodega)
		{		
			var resultado_bodega = $.trim(respuesta_bodega);
			$('#muestra_bodega').val(resultado_bodega);
		});
		
		//para traer y ver si trabaja con vencimiento
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'vencimiento',serie_consultada:serie_consignacion}).done( function(respuesta_vencimiento)
		{		
			var resultado_vencimiento = $.trim(respuesta_vencimiento);
			$('#muestra_vencimiento').val(resultado_vencimiento);
		});
		
		//document.getElementById('cliente_consignacion_venta').focus();	
}


function eliminar_detalle_consignacion(id){
	$.ajax({
			url: "../ajax/detalle_consignaciones.php?action=eliminar_item&id_registro="+id,
			 beforeSend: function(objeto){
				$("#muestra_detalle_consignacion").html("Eliminando...");
			  },
			success: function(data){
				$(".outer_divdet_consignacion").html(data).fadeIn('fast');
				$('#muestra_detalle_consignacion').html('');
		  }
	});
}

function resetea_datos(){
	 $("#guardar_consignacion_venta")[0].reset();//para reseatear formulario y limpiar todos los campos
	$.ajax({
			url: "../ajax/detalle_consignaciones.php?action=limpiar_info_entrada",
			 beforeSend: function(objeto){
				$("#muestra_detalle_consignacion").html("Iniciando...");
			  },
			success: function(data){
				$(".outer_divdet_consignacion").html(data).fadeIn('fast');
				$('#muestra_detalle_consignacion').html('');
		  }
	});
}


//para buscar productos
function buscar_productos(){
						$("#nombre_producto").autocomplete({
							source: '../ajax/productos_autocompletar_inventario.php',
							minLength: 2,
							select: function(event, ui) {
								event.preventDefault();
								$('#id_producto').val(ui.item.id);
								$('#nombre_producto').val(ui.item.nombre);

								var configuracion_inventario=document.getElementById('inventario').value;
								var configuracion_medida=document.getElementById('muestra_medida').value;
								var configuracion_lote=document.getElementById('muestra_lote').value;
								var configuracion_bodega=document.getElementById('muestra_bodega').value;
								var configuracion_vencimiento=document.getElementById('muestra_vencimiento').value;
								var producto = $("#id_producto").val();
																
								if ((configuracion_inventario =='NO' || configuracion_inventario =='')){
								document.getElementById("titulo_lote").style.display="none";
								document.getElementById("titulo_caducidad").style.display="none";
								document.getElementById("titulo_medida").style.display="";
								document.getElementById("lista_lote").style.display="none";
								document.getElementById("lista_caducidad").style.display="none";
								document.getElementById("lista_medida").style.display="";							
								var producto = $("#id_producto").val();
							
								//cuando trae se busca el producto me trae que tipo de medida tiene
									$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_tipos_medidas ){
										$("#medida_agregar").html(res_tipos_medidas);
									});
								}
								
								//aqui controla cuando se selecciona producto y trabaja con inventario
									if (configuracion_inventario =='SI'){

										if(configuracion_medida == "SI"){
											document.getElementById("titulo_medida").style.display="";
											document.getElementById("lista_medida").style.display="";
										}
										if(configuracion_lote=='SI'){
											document.getElementById("titulo_lote").style.display="";
											document.getElementById("lista_lote").style.display="";
										}
										
										if(configuracion_vencimiento=='SI'){
											document.getElementById("titulo_caducidad").style.display="";
											document.getElementById("lista_caducidad").style.display="";
										}
														
										document.getElementById("existencia_producto").disabled = true;
																			
										$("#existencia_producto" ).val("0");
										var bodega = $("#bodega_agregar").val();
										var producto = $("#id_producto").val();
									
										//cuando trae se busca el producto me trae que tipo de medida tiene
											$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
												$("#medida_agregar").html(res_id_medidas);
											});	
										//

									//para que se cargue el stock del producto al momento de buscar el producto dependiendo de la bodega que esta seleeccionada por default
										$.post( '../ajax/saldo_producto_inventario.php', {id_bodega: bodega, id_producto: producto}).done( function( respuesta ){
										var saldo_producto = respuesta;
										$("#existencia_producto").val(saldo_producto);
										$('#stock_tmp').val(saldo_producto);
										});
										
									//para traer todos los lotes en base a una bodega al momento de buscar un producto
									$.post( '../ajax/select_opciones_inventario.php', {opcion:'lote', id_producto: producto, bodega: bodega}).done( function( res_opciones_lote ){
										$("#lote_agregar").html(res_opciones_lote);
									});

									//para traer todos las caducidades en base a una bodega al momento de buscar un producto
									$.post( '../ajax/select_opciones_inventario.php', {opcion:'caducidad', id_producto: producto, bodega: bodega}).done( function( res_opciones_caducidad ){
												$("#caducidad_agregar").html(res_opciones_caducidad);
											});											
																					
										document.getElementById("titulo_existencia").style.display="";
										document.getElementById("lista_existencia").style.display="";
																				
									}
								//hasta aqui me controla si trabaja con inventario
								document.getElementById('cantidad_agregar').focus();
							}
						});
						
				$( "#nombre_producto" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
						
				$("#nombre_producto" ).on( "keydown", function( event ) {
					if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
					{
						$("#id_producto" ).val("");
						$("#nombre_producto" ).val("");
						$("#existencia_producto" ).val("");
						$("#medida_agregar" ).val("");
						$("#stock_tmp" ).val("");						
					}
			});
			
}	

$( function(){
	//para cuando se cambia el select de bodega que me cargue el saldo de ese producto
	$('#bodega_agregar').change(function(){
		var bodega = $("#bodega_agregar").val();
		var producto = $("#id_producto").val();
		var id_medida = $("#medida_agregar").val();
		
			//reinicia la medida
			$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
				$("#medida_agregar").html(res_id_medidas);
			});
		
			//trae la existencia en base a la bodega
			$.post( '../ajax/saldo_producto_inventario.php', {id_bodega: bodega, id_producto: producto}).done( function( respuesta ){
			var saldo_producto = respuesta;
			$("#existencia_producto").val(saldo_producto);
			$('#stock_tmp').val(saldo_producto);			
			});
			
			//reinicio el lote
			$.post( '../ajax/select_opciones_inventario.php', {opcion:'lote', id_producto: producto, bodega: bodega}).done( function( res_opciones_lote ){
				$("#lote_agregar").html(res_opciones_lote);
			});

			//para reinicie vencimiento
			$.post( '../ajax/select_opciones_inventario.php', {opcion:'caducidad', id_producto: producto, bodega: bodega}).done( function( res_opciones_caducidad ){
				$("#caducidad_agregar").html(res_opciones_caducidad);
			});			
	});

	//para traer el valor de conversion de medidas en el producto cuando se cambia el select de medida
	$('#medida_agregar').change(function(){	
		var id_medida = $("#medida_agregar").val();
		var id_producto = $("#id_producto").val();
		var stock_tmp = $("#stock_tmp").val();
		
		$.post( '../ajax/saldo_producto_inventario.php', {id_medida_seleccionada: id_medida, id_producto: id_producto, precio_venta: precio_venta, stock_tmp: stock_tmp, dato_obtener:'saldo' }).done( function( respuesta_saldo ){
			$("#existencia_producto").val(respuesta_saldo);
		});
	});
	
	//para traer el valor de conversion de medidas en el producto cuando se cambia el select de lote
	$('#lote_agregar').change(function(){	
		var lote = $("#lote_agregar").val();
		var producto = $("#id_producto").val();
		var bodega = $("#bodega_agregar").val();
		$.post( '../ajax/saldo_producto_inventario.php', {opcion_lote: lote, id_producto: producto, bodega: bodega}).done( function( respuesta_lote ){
			$("#existencia_producto").val(respuesta_lote);
		});
		
		//reinicia la medida
			$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
				$("#medida_agregar").html(res_id_medidas);
			});
			
			//para reinicie vencimiento
			$.post( '../ajax/select_opciones_inventario.php', {opcion:'caducidad', id_producto: producto, bodega: bodega}).done( function( res_opciones_caducidad ){
				$("#caducidad_agregar").html(res_opciones_caducidad);
			});	
		
	});
	
	//para traer el valor de conversion de medidas en el producto cuando se cambia el select de caducidad
	$('#caducidad_agregar').change(function(){	
		var caducidad = $("#caducidad_agregar").val();
		var producto = $("#id_producto").val();
	
		$.post( '../ajax/saldo_producto_inventario.php', {opcion_caducidad: caducidad, id_producto: producto }).done( function( respuesta_caducidad ){
			$("#existencia_producto").val(respuesta_caducidad);
		});
		
		//reinicia la medida
			$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
				$("#medida_agregar").html(res_id_medidas);
			});
	});

});
	
//agrega un item
function agregar_item(){
			var id_producto= $("#id_producto").val();
			var cantidad_agregar= $("#cantidad_agregar").val();
			var nup_agregar= $("#nup").val();
			var bodega_agregar= $("#bodega_agregar").val();
			var medida_agregar= $("#medida_agregar").val();
			var lote_agregar= $("#lote_agregar").val();
			var caducidad_agregar= $("#caducidad_agregar").val();
			var existencia_producto=document.getElementById('existencia_producto').value;
			var configuracion_inventario = $("#inventario").val();
			var control_bodega=document.getElementById('muestra_bodega').value;
			var control_lote=document.getElementById('muestra_lote').value;
			var control_caducidad=document.getElementById('muestra_vencimiento').value;

			//Inicia validacion
			if (id_producto ==''){
			alert('Ingrese producto');
			document.getElementById('nombre_producto').focus();
			return false;
			}
			if (cantidad_agregar ==''){
			alert('Ingrese cantidad');
			document.getElementById('cantidad_agregar').focus();
			return false;
			}
			if (nup_agregar ==''){
			alert('Ingrese número único de producto');
			document.getElementById('nup').focus();
			return false;
			}

			if (isNaN(cantidad_agregar)){
			alert('El dato ingresado en cantidad, no es un número');
			document.getElementById('cantidad_agregar').focus();
			return false;
			}

			if (configuracion_inventario =='SI' && control_bodega=='SI' && bodega_agregar=='0' ){
			alert('Seleccione una bodega');
			document.getElementById('bodega_agregar').focus();
			return false;
			}
			
			if (configuracion_inventario =='SI' && control_lote=='SI' && lote_agregar=='0' ){
			alert('Seleccione un lote');
			document.getElementById('lote_agregar').focus();
			return false;
			}
			
			if (configuracion_inventario =='SI' && control_caducidad=='SI' && caducidad_agregar=='0' ){
			alert('Seleccione fecha de vencimiento');
			document.getElementById('caducidad_agregar').focus();
			return false;
			}
			
			if (parseFloat(cantidad_agregar) > parseFloat(existencia_producto) && configuracion_inventario =='SI'){
			alert('El saldo en inventarios es menor a la cantidad a consignar ');
			document.getElementById('cantidad_agregar').focus();
			return false;
			}

			//Fin validacion
			$("#muestra_detalle_consignacion").fadeIn('fast');
			 $.ajax({
					url: "../ajax/detalle_consignaciones.php?action=agregar_detalle_consignacion_venta&id_producto="+id_producto+"&cantidad_agregar="+cantidad_agregar+"&bodega_agregar="+bodega_agregar+"&medida_agregar="+medida_agregar+"&lote_agregar="+lote_agregar+"&caducidad_agregar="+caducidad_agregar+"&inventario="+inventario+"&nup="+nup_agregar,
					 beforeSend: function(objeto){
						$("#muestra_detalle_consignacion").html("Cargando detalle...");
					  },
					success: function(data){
						$(".outer_divdet_consignacion").html(data).fadeIn('fast');
						$('#muestra_detalle_consignacion').html('');
						document.getElementById("nombre_producto").value = "";
						document.getElementById("cantidad_agregar").value = "1";
						document.getElementById("medida_agregar").value = "";
						document.getElementById("existencia_producto").value = "";
						document.getElementById("id_producto").value = "";
						document.getElementById("nup").value = "";
						document.getElementById('nombre_producto').focus();
				  }
			});
}

//para guardar la consignacion_ventas
$( "#guardar_consignacion_venta" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/guardar_consignacion_ventas.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#loader_consignacion_venta").html("Guardando...");
			  },
			success: function(datos){
			$("#loader_consignacion_venta").html(datos);
			$("#loader_consignacion_venta").html('');
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
});

//eliminar consignacion_ventas
function eliminar_consignacion_ventas(codigo){
			var q= $("#q").val();
		if (confirm("Realmente desea anular la consignación?")){	
			$.ajax({
			type: "GET",
			url: "../ajax/detalle_consignaciones.php",
			data: "action=eliminar_consignacion_ventas&codigo_unico="+codigo,"q":q,
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

//DETALLE consignacion_ventas
function mostrar_detalle_consignacion(codigo){
	$.ajax({
		url: "../ajax/detalle_consignaciones.php?action=detalle_consignacion&codigo_unico="+codigo,
		 beforeSend: function(objeto){
			$("#loaderdet").html("Iniciando...");
		  },
		success: function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#loaderdet').html('');
	  }
	});
}


function obtener_datos(id){
		carga_modal();
		var codigo_unico = $("#mod_codigo_unico"+id).val();
		var fecha_consignacion = $("#mod_fecha_consignacion"+id).val();
		var mod_nombre_cliente = $("#mod_nombre_cliente"+id).val();
		var mod_id_cliente = $("#mod_id_cliente"+id).val();
		var mod_punto_partida = $("#mod_punto_partida"+id).val();
		var mod_punto_llegada = $("#mod_punto_llegada"+id).val();
		var mod_responsable = $("#mod_responsable"+id).val();
		var mod_serie = $("#mod_serie"+id).val();
		var mod_observaciones = $("#mod_observaciones"+id).val();
		var mod_fecha_entrega = $("#mod_fecha_entrega"+id).val();
		var mod_hora_entrega = $("#mod_hora_entrega"+id).val();
		var mod_traslado_por = $("#mod_traslado_por"+id).val();
	
		$("#codigo_unico").val(codigo_unico);
		$("#fecha_consignacion_salida").val(fecha_consignacion);
		$("#id_cliente_consignacion_venta").val(mod_id_cliente);
		$("#cliente_consignacion_venta").val(mod_nombre_cliente);
		$("#punto_partida").val(mod_punto_partida);
		$("#punto_llegada").val(mod_punto_llegada);
		$("#responsable_traslado").val(mod_responsable);
		$("#serie_consignacion").val(mod_serie);
		$("#observacion_consignacion_venta").val(mod_observaciones);
		$("#fecha_pedido").val(mod_fecha_entrega);
		$("#hora_entrega").val(mod_hora_entrega);
		$("#traslado").val(mod_traslado_por);
		
	$("#muestra_detalle_consignacion").fadeIn('fast');
	$.ajax({
		url: "../ajax/detalle_consignaciones.php?action=editar_detalle_consignacion_venta&codigo_unico="+codigo_unico,
		 beforeSend: function(objeto){
		 $('#muestra_detalle_consignacion').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_divdet_consignacion").html(data).fadeIn('fast');
			$('#muestra_detalle_consignacion').html('');
			document.getElementById('cuenta_diario').focus();
		}
	});
}


function mostrar_detalle_pedido(){
	var pedido= $("#numero_pedido").val();
	var bodega= $("#bodega_pedido").val();
	//var id_producto= $("#id_producto_pedido").val();
	if(pedido==""){
		alert('Ingrese un número de pedido.');
		document.getElementById('numero_pedido').focus();
		return false;
	}
	$.ajax({
		url: "../ajax/consignacion_venta.php?action=detalle_pedido&pedido="+pedido+"&bodega="+bodega,
		 beforeSend: function(objeto){
			$("#muestra_detalle_pedido").html("Cargando...");
		  },
		success: function(data){
			$(".outer_divdetpedido").html(data).fadeIn('fast');
			$('#muestra_detalle_pedido').html('');
	  }
	});
}

//cada vez que se selecciona un lote en pedidos
function saldo_producto_pedido(id){
		var lote = $("#lote_pedido"+id).val();
		var producto = $("#id_producto_pedido"+id).val();
		var bodega = $("#bodega_pedido").val();
		$.post( '../ajax/saldo_producto_inventario.php', {opcion_lote: lote, id_producto: producto, bodega: bodega}).done( function( respuesta_lote ){
			$("#existencia_pedido"+id).val(respuesta_lote);
		});	
		document.getElementById('nup_pedido'+id).focus();	
}

function agregar_item_pedido(id){
			var id_producto= $("#id_producto_pedido"+id).val();
			var cantidad_agregar= $("#cantidad_pedido"+id).val();
			var nup_agregar= $("#nup_pedido"+id).val();
			var bodega_agregar= $("#bodega_pedido").val();
			var medida_agregar= $("#id_medida_pedido"+id).val();
			var lote_agregar= $("#lote_pedido"+id).val();
			var caducidad_agregar= 0;
			var existencia_producto=document.getElementById("existencia_pedido"+id).value;
			var id_cliente= $("#id_cliente_pedido"+id).val();
			var numero_pedido= $("#numero_pedido"+id).val();
			var observaciones_pedido= $("#observaciones_pedido"+id).val();
			var nombre_cliente= $("#nombre_cliente_pedido"+id).val();
			var saldo_entrante= $("#saldo_entrante"+id).val();
			var hora_entrega= $("#hora_entrega"+id).val();
			var fecha_entrega= $("#fecha_entrega"+id).val();
			var responsable= $("#responsable"+id).val();
			
			//Inicia validacion
			if (id_producto ==''){
			alert('Cargue un pedido y luego seleccione un producto');
			return false;
			}
			if (cantidad_agregar ==''){
			alert('Ingrese cantidad');
			document.getElementById('cantidad_pedido'+id).focus();
			return false;
			}
			if (lote_agregar ==0){
			alert('Seleccione un lote');
			document.getElementById('lote_pedido'+id).focus();
			return false;
			}
			if (nup_agregar ==''){
			alert('Ingrese número único de producto');
			document.getElementById('nup_pedido'+id).focus();
			return false;
			}

			if (isNaN(cantidad_agregar)){
			alert('El dato ingresado en cantidad, no es un número');
			document.getElementById('cantidad_pedido'+id).focus();
			return false;
			}
		
			if (parseFloat(cantidad_agregar) > parseFloat(existencia_producto)){
			alert('El saldo en inventarios es menor a la cantidad a consignar.');
			document.getElementById('cantidad_pedido'+id).focus();
			return false;
			}

			if (saldo_entrante ==0){
			alert('No es posible agregar, este producto en este pedido ya fue despachado en su totalidad.');
			return false;
			}

			//Fin validacion
			$("#muestra_detalle_consignacion").fadeIn('fast');
			 $.ajax({
					url: "../ajax/detalle_consignaciones.php?action=agregar_detalle_consignacion_venta&id="+id+"&id_producto="+id_producto+"&cantidad_agregar="+cantidad_agregar+"&bodega_agregar="+bodega_agregar+"&medida_agregar="+medida_agregar+"&lote_agregar="+lote_agregar+"&caducidad_agregar="+caducidad_agregar+"&inventario="+inventario+"&nup="+nup_agregar,
					 beforeSend: function(objeto){
						$("#muestra_detalle_consignacion").html("Cargando detalle...");
					  },
					success: function(data){
						$(".outer_divdet_consignacion").html(data).fadeIn('fast');
						$('#muestra_detalle_consignacion').html('');
						document.getElementById("cantidad_pedido"+id).value = "1";
						document.getElementById("existencia_pedido"+id).value = 0;
						document.getElementById("lote_pedido"+id).value = 0;
						document.getElementById("nup_pedido"+id).value = "";
						document.getElementById('nup_pedido'+id).focus();
						$("#id_cliente_consignacion_venta").val(id_cliente);
						$("#cliente_consignacion_venta").val(nombre_cliente);
						$("#observacion_consignacion_venta").val('No. pedido: '+numero_pedido+' Obs: ' + observaciones_pedido);
						$("#fecha_pedido").val(fecha_entrega);
						$("#hora_entrega").val(hora_entrega);
						$("#traslado").val(responsable);
						mostrar_detalle_pedido();
					}
			});

			
}

</script>