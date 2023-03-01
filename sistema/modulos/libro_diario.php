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
  <title>Diario General</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/nuevo_diario.php");
		  include("../modal/detalle_documento_contable.php");
		$con=conenta_login();
		$delete=mysqli_query($con, "DELETE FROM detalle_diario_tmp WHERE id_usuario='".$id_usuario."' and ruc_empresa = '".$ruc_empresa."'");
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
			<div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" onclick="iniciar_formulario();" data-toggle="modal" data-target="#NuevoDiarioContable" ><span class="glyphicon glyphicon-plus"></span> Nuevo asiento</button>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Diario general</h4>		
		</div>
		
		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#libro_diario">Libro diario</a></li>
			<li><a data-toggle="tab" href="#detalle_asientos">Detalle asientos</a></li>
			<li><a data-toggle="tab" href="#opciones_asientos">Opciones asientos en bloque</a></li>
		</ul>
		
		<div class="tab-content">
			<div id="libro_diario" class="tab-pane fade in active">
				<div class="panel-body">
					<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<div class="col-md-6">
								<input type="hidden" id="ordenado" value="numero_asiento">
								<input type="hidden" id="por" value="desc">
								<div class="input-group">
									<span class="input-group-addon"><b>Buscar:</b></span>	
									<input type="text" class="form-control" id="q" placeholder="Documento, fecha, detalle" onkeyup='load(1);'>
										<span class="input-group-btn">
											<button type="button" onclick='load(1);' class="btn btn-default" ><span class="glyphicon glyphicon-search" ></span> Buscar</button>
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
			
			
			<div id="detalle_asientos" class="tab-pane fade">
				<div class="panel-body">
					<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<div class="col-md-6">
								<div class="input-group">
									<span class="input-group-addon"><b>Buscar:</b></span>	
									<input type="text" class="form-control" id="d" placeholder="Documento, fecha, detalle" onkeyup='load(1);'>
										<span class="input-group-btn">
											<button type="button" onclick='load(1);' class="btn btn-default" ><span class="glyphicon glyphicon-search" ></span> Buscar</button>
										</span>
								</div>
							</div>
							<span id="loader_detalle_asientos"></span>
						</div>
					</form>
					<div id="resultados_detalle_sientos"></div><!-- Carga los datos ajax -->
					<div class='outer_div_detalle_asientos'></div><!-- Carga los datos ajax -->
				</div>
			</div>
			

			<div id="opciones_asientos" class="tab-pane fade">
				<div class="panel-body">
					<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="oa" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
									<div class="input-group">
									<input type="text" class="form-control" id="oa" placeholder="Tipo, fecha" onkeyup='load(1);'>
									<span class="input-group-btn">
										<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
									</span>
								</div>
							</div>
							<span id="loader_opciones_asientos"></span>
						</div>
					</form>
					<div id="resultados_opciones_asientos"></div><!-- Carga los datos ajax -->
					<div class='outer_div_opciones_asientos'></div><!-- Carga los datos ajax -->
				</div>
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
	<script src="../js/ordenado.js" type="text/javascript"></script>
	<script src="../js/siguiente_input.js" type="text/javascript"></script>
 </body>
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
     $("#fecha_diario").mask("99-99-9999");
});


$(document).ready(function(){
	load(1);
});

function load(page){
	var q= $("#q").val();
	var d= $("#d").val();
	var oa= $("#oa").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();

		$("#loader").fadeIn('slow');
		$.ajax({
			url:"../ajax/buscar_libro_diario.php?action=libro_diario&page="+page+"&q="+q+"&ordenado="+ordenado+"&por="+por,
			 beforeSend: function(objeto){
			 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
		  },
			success:function(data){
				$(".outer_div").html(data).fadeIn('slow');
				$('#loader').html('');
			}
		});
		
		$("#loader_detalle_asientos").fadeIn('slow');
		$.ajax({
			url:'../ajax/buscar_libro_diario.php?action=detalle_asientos&page='+page+'&d='+d,
			beforeSend: function(objeto){
			$('#loader_detalle_asientos').html('<img src="../image/ajax-loader.gif"> Cargando...');
		},
			success:function(data){
				$(".outer_div_detalle_asientos").html(data).fadeIn('slow');
				$('#loader_detalle_asientos').html('');
				
			}
		})


	$("#loader_opciones_asientos").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_opciones_libro_diario.php?action=buscar_asientos_bloque&page='+page+'&oa='+oa,
		 beforeSend: function(objeto){
		 $('#loader_opciones_asientos').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div_opciones_asientos").html(data).fadeIn('slow');
			$('#loader_opciones_asientos').html('');
			
		}
	})
	
	
}	


//para borrar datos del formulario para nuevo
function iniciar_formulario(){ 
	 $('#form_nuevo_diario').trigger("reset");
	 $("#codigo_unico").val('');//para borrar la info del input
	 $.ajax({
		url:'../ajax/agregar_item_diario_tmp.php?borrar_todo=borrar_todo',
		 beforeSend: function(objeto){
		 $('#muestra_detalle_diario').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#muestra_detalle_diario').html('');
		}
	})
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
			//setTimeout(function (){location.reload()}, 1000);
			load(1);
		  }
	});
 event.preventDefault();
}

//detalle de diario
function detalle_asiento(codigo){
		$("#loaderdet_contable").fadeIn('slow');
		$.ajax({
			url:'../ajax/detalle_documento_contable.php?action=detalle_asiento&codigo_unico='+codigo,
			 beforeSend: function(objeto){
			 $('#loaderdet_contable').html('<img src="../image/ajax-loader.gif"> Cargando detalle de diario...');
		  },
			success:function(data){
				$(".outer_divdet_contable").html(data).fadeIn('slow');
				$('#loaderdet_contable').html('');
			}
		})
	}
//eliminar asiento
function eliminar_asiento(id){
			var q= $("#q").val();
		if (confirm("Realmente desea anular el asiento contable?")){	
			$.ajax({
			type: "GET",
			url: "../ajax/buscar_libro_diario.php",
			data: "action=eliminar_asiento&codigo_unico="+id,"q":q,
			 beforeSend: function(objeto){
				$('#loader').html('<img src="../image/ajax-loader.gif">Eliminando...');
			  },
			success: function(datos){
			$("#loader").html(datos);
			load(1);
			}
			});
		}
}

//duplicar asiento
function duplicar_asiento(codigo){
			var q= $("#q").val();
		if (confirm("Realmente desea duplicar el asiento contable?")){	
			$.ajax({
			type: "GET",
			url: "../ajax/buscar_libro_diario.php",
			data: "action=duplicar_asiento&codigo_unico="+codigo,"q":q,
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

//eliminar asientos bloque
function eliminar_asientos_bloque(codigo){
			var q= $("#oa").val();
		if (confirm("Realmente desea anular el bloque de asientos?")){	
			$.ajax({
			type: "GET",
			url: "../ajax/buscar_opciones_libro_diario.php",
			data: "action=eliminar_asientos_bloque&codigo_bloque="+codigo,"oa":q,
			 beforeSend: function(objeto){
				$("#loader_opciones_asientos").html("Actualizando registros...");
			  },
			success: function(datos){
			$(".outer_div_opciones_asientos").html(datos);
			$("#loader_opciones_asientos").html('');
			load(1);
			}
			});
		}
}
</script>
