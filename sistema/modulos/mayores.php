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
  <title>Mayorización</title>
<?php 
include("../paginas/menu_de_empresas.php");
date_default_timezone_set('America/Guayaquil');
include("../modal/nuevo_diario.php");
?>
<style type="text/css">
		 ul.ui-autocomplete {
			z-index: 1100;
		}
	</style>
  </head>
  <body>
 	
    <div class="container">
		<div class="panel panel-info">
			<div class="panel-heading">		
				<h4><i class='glyphicon glyphicon-list-alt'></i> Libro mayor</h4>
			</div>
			<div class="panel-body">
			<form class="form-horizontal" method ="POST" target="_blank" action="../excel/mayores.php">		
			<input type="hidden" name="id_cuenta_contable" id="id_cuenta_contable">
			<input type="hidden" name="id_pro_cli" id="id_pro_cli">
					<div class="form-group">
						<div class="col-sm-3">
						<div class="input-group">
							<span class="input-group-addon"><b>Reporte</b></span>
								<select class="form-control input-sm" id="nombre_informe" name="nombre_informe" required>
								<option value="4"> Mayor General</option>
								<option value="5"> Clientes</option>
								<option value="6"> Proveedores</option>
								<option value="7"> Por detalles</option>
								</select>
						</div>
						</div>
						<div class="col-sm-5" id="label_pro_cli">
						<div class="input-group">
							<span class="input-group-addon" id="nombre_pro_cli"></span>
							<input type="text" class="form-control input-sm" name="pro_cli" id="pro_cli" onkeyup='agregar_pro_cli();' placeholder="Todos" autocomplete="off">
						</div>
						</div>
						<div class="col-sm-4" id="label_cuenta">
						<div class="input-group">
							<span class="input-group-addon"><b>Cuenta</b></span>
							<input type="text" class="form-control input-sm" name="cuenta" id="cuenta" onkeyup='agregar_cuenta();' placeholder="Todas" autocomplete="off">
						</div>
						</div>
					</div>
						<div class="form-group">
						<div class="col-sm-3">
						<div class="input-group">
							<span class="input-group-addon"><b>Desde</b></span>
							<input type="text" class="form-control input-sm text-center" name="fecha_desde" id="fecha_desde" value="<?php echo date("01-01-Y");?>">
						</div>
						</div>
						
						<div class="col-sm-3">
						<div class="input-group">
							<span class="input-group-addon"><b>Hasta</b></span>
							<input type="text" class="form-control input-sm text-center" name="fecha_hasta" id="fecha_hasta" value="<?php echo date("d-m-Y");?>">
						</div>
						</div>
						<div class="col-sm-3">
							<button type="button" title="Mostrar resultado" class="btn btn-info btn-sm" onclick="mostrar_informe()"><span class="glyphicon glyphicon-search" ></span></button>						
							<button type="submit" title="Descargar excel" class="btn btn-success btn-sm" ><img src="../image/excel.ico" width="20" height="18"></button>
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
</body>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/siguiente_input.js" type="text/javascript"></script>
<style>
.fixedHeight {
        padding: 1px;
		max-height: 200px;
		overflow: auto;
    }
</style>
</html>
<script>
jQuery(function($){
     $("#fecha_desde").mask("99-99-9999");
	 $("#fecha_hasta").mask("99-99-9999");
	 $("#fecha_diario").mask("99-99-9999");
});

$(document).ready(function() {
	document.getElementById("label_pro_cli").style.display = "none";
	document.getElementById("label_cuenta").style.display = "";	
		});

function agregar_cuenta(){
	$("#cuenta").autocomplete({
			source:'../ajax/cuentas_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_cuenta_contable').val(ui.item.id_cuenta);
				$('#cuenta').val(ui.item.nombre_cuenta);
			}
		});
	
	$("#cuenta" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#cuenta" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_cuenta_contable" ).val("");
			$("#cuenta" ).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#id_cuenta_contable" ).val("");
			$("#cuenta" ).val("");
		}
		});
}

function agregar_pro_cli(){
	var nombre_informe = $("#nombre_informe").val();
	if (nombre_informe == '5' ){
		$("#pro_cli").autocomplete({
			source:'../ajax/clientes_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_pro_cli').val(ui.item.id);
				$('#pro_cli').val(ui.item.nombre);
			}
		});
	}
	
	if (nombre_informe == '6' ){
		$("#pro_cli").autocomplete({
			source:'../ajax/proveedores_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_pro_cli').val(ui.item.id_proveedor);
				$('#pro_cli').val(ui.item.razon_social);
			}
		});
	}

	$("#pro_cli" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#pro_cli" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_pro_cli" ).val("");
			$("#pro_cli" ).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#id_pro_cli" ).val("");
			$("#pro_cli" ).val("");
		}
		});

 }
 

$( function() {
	$("#fecha_desde").datepicker({
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
});

$( function() {
	$("#fecha_hasta").datepicker({
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
});

//generar informe
function mostrar_informe(){
 var nombre=$("#nombre_informe").val();
 var cuenta=$("#id_cuenta_contable").val();
 var pro_cli=$("#id_pro_cli").val();
 var det_pro_cli=$("#pro_cli").val();
 var desde = $("#fecha_desde").val();
 var hasta = $("#fecha_hasta").val();
 
	 $.ajax({
			type: "POST",
			url: "../ajax/informes_contables.php",
			data: "action="+nombre+"&cuenta="+cuenta+"&fecha_desde="+desde+"&fecha_hasta="+hasta+"&pro_cli="+pro_cli+"&det_pro_cli="+det_pro_cli,
			 beforeSend: function(objeto){
				$('#loader').html('<img src="../image/ajax-loader.gif">');
			  },
			success: function(datos){
			$(".outer_div").html(datos);
			$("#loader").html('');
		  }
	});
}



//de aqui para abajo es para modificar el asiento

function obtener_datos(id){
		var codigo_unico = $("#mod_codigo_unico"+id).val();
		var concepto_general = $("#mod_concepto_general"+id).val();
		var fecha_asiento = $("#mod_fecha_asiento"+id).val();

		$("#codigo_unico").val(codigo_unico);
		$("#concepto_diario").val(concepto_general);
		$("#fecha_diario").val(fecha_asiento);
		
	$("#muestra_detalle_diario").fadeIn('fast');
	$.ajax({
		url:'../ajax/agregar_item_diario_tmp.php?action=cargar_detalle_diario&codigo_unico='+codigo_unico,
		 beforeSend: function(objeto){
		 $('#muestra_detalle_diario').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#muestra_detalle_diario').html('');
			document.getElementById('cuenta_diario').focus();
		}
	});
}



//para buscar las cuentas al hacer un nuevo asiento
function buscar_cuentas(){
	$("#cuenta_diario").autocomplete({
			source:'../ajax/cuentas_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_cuenta').val(ui.item.id_cuenta);
				$('#cuenta_diario').val(ui.item.nombre_cuenta);
				$('#cod_cuenta').val(ui.item.codigo_cuenta);
				document.getElementById('debe_diario').focus();
			}
		});

		$("#cuenta_diario" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#cuenta_diario" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_cuenta" ).val("");
			$("#cuenta_diario" ).val("");
			$("#cod_cuenta" ).val("");
		}
		if (event.keyCode== $.ui.keyCode.DELETE )
			{
			$("#id_cuenta" ).val("");
			$("#cuenta_diario" ).val("");
			$("#cod_cuenta" ).val("");
			}
		});
}


//para agregar un iten de diario
function agregar_item_diario(){
			var id_cuenta=$("#id_cuenta").val();
			var cod_cuenta=$("#cod_cuenta").val();
			var cuenta_diario=$("#cuenta_diario").val();
			var debe_diario=$("#debe_diario").val();
			var haber_cuenta=$("#haber_cuenta").val();
			var det_cuenta=$("#det_cuenta").val();
			//Inicia validacion

			if (id_cuenta==""){
			alert('Agregue una cuenta contable.');
			document.getElementById('cuenta_diario').focus();
			return false;
			}
			if (isNaN(debe_diario)){
			alert('El dato ingresado en el debe, no es un número');
			document.getElementById('debe_diario').focus();
			return false;
			}
			
			if (isNaN(haber_cuenta)){
			alert('El dato ingresado en el haber, no es un número');
			document.getElementById('haber_cuenta').focus();
			return false;
			}
			
			if (debe_diario =="0" && haber_cuenta=="0"){
			alert('Ingrese valores en el debe o haber');
			document.getElementById('debe_diario').focus();
			return false;
			}
			
			if (debe_diario =="" && haber_cuenta==""){
			alert('Ingrese valores en el debe o haber');
			document.getElementById('debe_diario').focus();
			return false;
			}
			
			if (debe_diario =="0" && haber_cuenta==""){
			alert('Ingrese valores en el debe o haber');
			document.getElementById('debe_diario').focus();
			return false;
			}
			
			if (debe_diario =="" && haber_cuenta=="0"){
			alert('Ingrese valores en el debe o haber');
			document.getElementById('debe_diario').focus();
			return false;
			}
			
			
			if ((debe_diario)>0 && (haber_cuenta)>0){
			alert('Corregir valores, no pueden tener valores el debe y el haber.');
			document.getElementById('haber_cuenta').focus();
			return false;
			}
			
			if (det_cuenta==""){
			alert('Agregue un detalle.');
			document.getElementById('det_cuenta').focus();
			return false;
			}			
			
			//Fin validacion
			$.ajax({
         type: "POST",
         url: "../ajax/agregar_item_diario_tmp.php",
         data: "id_cuenta="+id_cuenta+"&cod_cuenta="+cod_cuenta+"&cuenta_diario="+cuenta_diario+"&debe_diario="+debe_diario+"&haber_cuenta="+haber_cuenta+"&det_cuenta="+det_cuenta+"&detalle_diario=detalle_diario",
		 beforeSend: function(objeto){
			$("#mensaje_nuevo_asiento").html("Agregando...");
		  },
			success: function(datos){
			$(".outer_divdet").html(datos).fadeIn('fast');
			$('#muestra_detalle_diario').html('');
			$('#mensaje_nuevo_asiento').html('');
			$("#id_cuenta" ).val("");
			$("#cod_cuenta" ).val("");
			$("#cuenta_diario" ).val("");
			$("#debe_diario" ).val("");
			$("#haber_cuenta" ).val("");
			$("#det_cuenta" ).val("");
			pasa_concepto();
			document.getElementById('cuenta_diario').focus();
			}
			});
		
	}
	
function eliminar_item_diario(id){
		$.ajax({
			type: "GET",
			url: "../ajax/agregar_item_diario_tmp.php",
			data: "id_diario="+id,
			 beforeSend: function(objeto){
				$("#mensaje_nuevo_asiento").html("Eliminando...");
			  },
			success: function(datos){
				$(".outer_divdet").html(datos).fadeIn('fast');
				$('#muestra_detalle_diario').html('');
				$('#mensaje_nuevo_asiento').html('');
			document.getElementById('cuenta_diario').focus();
			}
		});
}

function pasa_concepto(){
	var concepto_diario=$("#concepto_diario").val();
	$("#det_cuenta").val(concepto_diario);
}

//para guardar el diario
function guardar_diario(){
  $('#guardar_datos').attr("disabled", true);
 var fecha_diario=$("#fecha_diario").val();
 var concepto_diario=$("#concepto_diario").val();
 var subtotal_debe = $("#subtotal_debe").val();
 var subtotal_haber = $("#subtotal_haber").val();
 var tipo = $("#tipo").val();
 var codigo_unico = $("#codigo_unico").val();
	 $.ajax({
			type: "POST",
			url: "../ajax/guardar_libro_diario.php",
			data: "fecha_diario="+fecha_diario+"&concepto_diario="+concepto_diario+"&subtotal_debe="+subtotal_debe+"&subtotal_haber="+subtotal_haber+"&tipo="+tipo+"&codigo_unico="+codigo_unico,
			 beforeSend: function(objeto){
				$("#mensaje_nuevo_asiento").html("Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_cuentas").html(datos);
			$("#mensaje_nuevo_asiento").html("");
			$('#guardar_datos').attr("disabled", false);
		   }
	});
 //event.preventDefault();
}

//para modificar el codigo de la cuenta
function buscar_cuenta_modificar(id){
		$("#modificar_codigo_cuenta"+id).autocomplete({
			source:'../ajax/cuentas_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_cuenta_modificar'+id).val(ui.item.id_cuenta);
				$('#modificar_cuenta'+id).val(ui.item.nombre_cuenta);
				$('#modificar_codigo_cuenta'+id).val(ui.item.codigo_cuenta);
				document.getElementById('modificar_debe'+id).focus();
			}
		});

		$("#modificar_codigo_cuenta"+id).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#modificar_codigo_cuenta"+id).on( "keydown", function( event ) {
			if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
			{
				$("#id_cuenta_modificar"+id).val("");
				$("#modificar_cuenta"+id).val("");
				$("#modificar_codigo_cuenta"+id).val("");
			}
			if (event.keyCode== $.ui.keyCode.DELETE || event.keyCode== $.ui.keyCode.BACKSPACE)
			{
				$("#id_cuenta_modificar"+id).val("");
				$("#modificar_cuenta"+id).val("");
			}
		});		
}

//cambiar cuenta 
function actualizar_cuenta_modificar(id){
	var codigo_actual = $("#codigo_actual"+id).val();
	var cuenta_actual = $("#cuenta_actual"+id).val();
	var id_cuenta_actual = $("#id_cuenta_modificar"+id).val();
	
	var id_cuenta_modificar = $("#id_cuenta_modificar"+id).val();
	var modificar_codigo_cuenta = $("#modificar_codigo_cuenta"+id).val();
	var modificar_cuenta = $("#modificar_cuenta"+id).val();
	
	if (modificar_codigo_cuenta==""){
	alert('Ingrese cuenta contable');
	document.getElementById('modificar_codigo_cuenta'+id).focus();
	$("#id_cuenta_modificar"+id).val(id_cuenta_actual);
	$("#modificar_cuenta"+id).val(cuenta_actual);
	$("#modificar_codigo_cuenta"+id).val(codigo_actual);
	return false;
	}
		
	$.ajax({
		 type: "POST",
		 url: "../ajax/agregar_item_diario_tmp.php",
		 data: "action=actualizar_ceuntas_asiento&id_item="+id+"&id_cuenta="+id_cuenta_modificar+"&codigo_cuenta="+modificar_codigo_cuenta+"&nombre_cuenta="+modificar_cuenta,
		 beforeSend: function(objeto){
			$("#mensaje_nuevo_asiento").html("Actualizando...");
		  },
			success: function(datos){
			$(".outer_divdet").html(datos).fadeIn('fast');
			$('#mensaje_nuevo_asiento').html('');
			}
		});
}

//para modificar el detalle del asiento de cada item
function modificar_detalle_directo(id){
	var detalle_original = $("#detalle_original"+id).val();
	var detalle_asiento = $("#detalle_asiento"+id).val();
	
	if (detalle_asiento==""){
	alert('Ingrese detalle del item, no puede quedar vacio');
	document.getElementById('detalle_asiento'+id).focus();
	$("#detalle_asiento"+id).val(detalle_original);
	return false;
	}
		
	$.ajax({
		 type: "POST",
		 url: "../ajax/agregar_item_diario_tmp.php",
		 data: "action=actualizar_item_asiento&id_item="+id+"&detalle_item="+detalle_asiento,
		 beforeSend: function(objeto){
			$("#mensaje_nuevo_asiento").html("Actualizando...");
		  },
			success: function(datos){
			$(".outer_divdet").html(datos).fadeIn('fast');
			$('#mensaje_nuevo_asiento').html('');
			}
		});
}

function modificar_debe(id){
		var modificar_debe= $("#modificar_debe"+id).val();
		var debe_actual = $("#debe_actual"+id).val();

		if (isNaN(modificar_debe)){
			alert('El dato ingresado, no es un número');
			$("#modificar_debe"+id).val(debe_actual);
			document.getElementById('modificar_debe'+id).focus();
			return false;
			}
		
		if (modificar_debe <0){
			alert('Ingrese valor mayor a cero');
			$("#modificar_debe"+id).val(debe_actual);
			document.getElementById('modificar_debe'+id).focus();
			return false;
			}
						
			$.ajax({
			 type: "POST",
			 url: "../ajax/agregar_item_diario_tmp.php",
			 data: "action=actualizar_debe&id_item="+id+"&debe="+modificar_debe,
			 beforeSend: function(objeto){
				$("#mensaje_nuevo_asiento").html("Actualizando...");
			  },
				success: function(datos){
				$(".outer_divdet").html(datos).fadeIn('fast');
				$('#mensaje_nuevo_asiento').html('');
				}
			});
}

function modificar_haber(id){
		var modificar_haber= $("#modificar_haber"+id).val();
		var haber_actual = $("#haber_actual"+id).val();

		if (isNaN(modificar_haber)){
			alert('El dato ingresado, no es un número');
			$("#modificar_haber"+id).val(haber_actual);
			document.getElementById('modificar_haber'+id).focus();
			return false;
			}
		
		if (modificar_haber <0){
			alert('Ingrese valor mayor a cero');
			$("#modificar_haber"+id).val(haber_actual);
			document.getElementById('modificar_haber'+id).focus();
			return false;
			}
			
			
			$.ajax({
			 type: "POST",
			 url: "../ajax/agregar_item_diario_tmp.php",
			 data: "action=actualizar_haber&id_item="+id+"&haber="+modificar_haber,
			 beforeSend: function(objeto){
				$("#mensaje_nuevo_asiento").html("Actualizando...");
			  },
				success: function(datos){
				$(".outer_divdet").html(datos).fadeIn('fast');
				$('#mensaje_nuevo_asiento').html('');
				}
			});
}


$('#nombre_informe').change(function() {
			//$("#id_marca").val("");
			$("#id_pro_cli" ).val("");
			$("#pro_cli" ).val("");
			$("#cuenta" ).val("");
			$("#id_cuenta_contable" ).val("");
			
			var tipo = $("#nombre_informe").val();
			if (tipo == "4") {
				document.getElementById("label_pro_cli").style.display = "none";
				document.getElementById("label_cuenta").style.display = "";
			} else {
				document.getElementById("label_pro_cli").style.display = "";
			}

			if (tipo == "5") {
				document.querySelector("#nombre_pro_cli").innerHTML = "<b>Cliente</b>";
				document.getElementById("label_cuenta").style.display = "";
			} 
			if (tipo == "6") {
				document.querySelector("#nombre_pro_cli").innerHTML = "<b>Proveedor</b>";
				document.getElementById("label_cuenta").style.display = "";
			} 
			if (tipo == "7") {
				document.querySelector("#nombre_pro_cli").innerHTML = "<b>Detalle</b>";
				document.getElementById("label_cuenta").style.display = "none";
			} 

		});
</script>