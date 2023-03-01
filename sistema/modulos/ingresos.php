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
  <title>Ingresos</title>
	<?php 
	include("../paginas/menu_de_empresas.php");
	include("../modal/detalle_ingreso_egreso.php");
	$con = conenta_login();
	$limpiar_saldos = mysqli_query($con, "DELETE FROM saldo_porcobrar_porpagar WHERE id_usuario='".$id_usuario."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and tipo='POR_COBRAR'");
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
			
			<form method="post" action="" >
			<!-- ../modulos/nuevo_ingreso.php -->
			
			<button type="submit" class="btn btn-info" onclick='generar_cuentas_por_cobrar();' ><span class="glyphicon glyphicon-plus" ></span> Nuevo ingreso</button>
			</form>
			</div>
			<h4><i class="glyphicon glyphicon-search"></i> Ingresos</h4>
		</div>
		<span id="mensaje_nuevo_ingreso"></span>
		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#ingresos">Ingresos</a></li>
			<li><a data-toggle="tab" href="#detalle_ingresos">Detalle de ingresos</a></li>
			<li><a data-toggle="tab" href="#detalle_cobros_ingresos">Detalle de cobros</a></li>
		</ul>
		<div class="tab-content">
			<div id="ingresos" class="tab-pane fade in active">
			<div class="panel-body">
			<form class="form-horizontal" method ="POST">
						<div class="form-group row">
							<label for="ingreso" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_ing_egr">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="ingreso" placeholder="Cliente, Número, Observaciones" onkeyup='load(1);'>
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
			<div id="detalle_ingresos" class="tab-pane fade">
			<div class="panel-body">
				<form class="form-horizontal" role="form" >
				<div class="form-group row">
							<label for="deting" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_ing_egr">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="deting" placeholder="Cliente, Número de ingreso, fecha, detalle" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<span id="loader_detalles"></span>
				</div>
				</form>
				<div id="resultados_detalles_ingresos"></div><!-- Carga los datos ajax -->
				<div class='outer_div_detalles'></div><!-- Carga los datos ajax -->
			</div>
			</div>
			<div id="detalle_cobros_ingresos" class="tab-pane fade">		
				<div class="panel-body">
				<form class="form-horizontal" role="form" >
				<div class="form-group row">
							<label class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_ing_egr">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="detpago" placeholder="Cliente, Número de ingreso, fecha, detalle" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<span id="loader_detalles_pagos"></span>
				</div>

				</form>
				<div id="resultados_detalles_pagos_ingresos"></div><!-- Carga los datos ajax -->
				<div class='outer_div_detalles_pagos'></div><!-- Carga los datos ajax -->
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
	<link rel="stylesheet" href="../css/jquery-ui.css"> <!--para que se vea con fondo blanco el autocomplete--> 
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	<script src="../js/ordenado.js" type="text/javascript"></script>
	<script src="../js/validar_fecha.js" type="text/javascript"></script>
</body>
</html>
<script>

$(document).ready(function(){
	load(1);
});


function generar_cuentas_por_cobrar(){
	//$('#guarda_datos_btn').attr("disabled", true);
	//$('#boton_buscar_ventas').attr("disabled", true);

	$.ajax({
		url:'../ajax/detalle_ingresos.php?action=saldos_cuentas_por_cobrar',
		 beforeSend: function(objeto){
		 //$('#mensaje_nuevo_ingreso').html('Actualizando saldos, espere por favor...');
		 $('#mensaje_nuevo_ingreso').html('<div class="progress"><div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width:100%;"> Actualizando saldos de cuentas por cobrar, espere por favor...</div></div>');

	  },
		success:function(data){
			$(".resultados_guardar_ingreso").html(data).fadeIn('slow');
			$('#mensaje_nuevo_ingreso').html('');
			setTimeout(function (){location.href ='../modulos/nuevo_ingreso.php'}, 1000);
			//$('#boton_buscar_ventas').attr("disabled", false);
			//$('#guarda_datos_btn').attr("disabled", false);
		}
	});
	event.preventDefault();
}

function load(page){
	var ingreso= $("#ingreso").val();
	var deting= $("#deting").val();
	var detpago= $("#detpago").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_ingresos.php?action=ingresos&page='+page+'&ingreso='+ingreso+"&ordenado="+ordenado+"&por="+por,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
		}
	});
	
	$("#loader_detalles").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_ingresos.php?action=detalle&page='+page+'&deting='+deting,
				 beforeSend: function(objeto){
				 $('#loader_detalles').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles").html(data).fadeIn('slow');
					$('#loader_detalles').html('');
				}
			})
			
			$("#loader_detalles_pagos").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_ingresos.php?action=pagos_ingresos&page='+page+'&detpago='+detpago,
				 beforeSend: function(objeto){
				 $('#loader_detalles_pagos').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles_pagos").html(data).fadeIn('slow');
					$('#loader_detalles_pagos').html('');
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

//anular ingreso
function anular_ingreso(codigo){
		var q= $("#q").val();
	if (confirm("Realmente desea anular el ingreso?")){	
		$.ajax({
		type: "POST",
		url: "../ajax/detalle_ingresos.php",
		data: "action=anular_ingreso&codigo_unico="+codigo,"q":q,
		 beforeSend: function(objeto){
			$("#resultados").html("Cargando...");
		  },
		success: function(datos){
		$("#resultados").html(datos);
		load(1);
		}
		});
	}
}

//DETALLE de ingreso
function mostrar_detalle_ingreso(codigo){
	$(".outer_divdet").html('');
	$.ajax({
		url: "../ajax/detalle_ingresos.php?action=detalle_ingreso&codigo_unico="+codigo,
		 beforeSend: function(objeto){
			$("#loaderdet").html("Cargando...");
		  },
		success: function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#loaderdet').html('');
	  }
	});
}


//para modifcar la fecha de ingreso
function modificar_fecha_ingreso(id){
		var fecha_anterior_ingreso= $("#fecha_anterior_ingreso"+id).val();
		var fecha_nueva_ingreso= $("#fecha_nueva_ingreso"+id).val();
		var pagina = $("#pagina").val();
		if (validarFecha(fecha_nueva_ingreso)!=true){
		alert("Error en fecha, formato: dd-mm-aaaa");
		$("#fecha_nueva_ingreso"+id).val(fecha_anterior_ingreso);
		return false;
		}
				
		if (fecha_nueva_ingreso==""){
		$("#fecha_nueva_ingreso"+id).val(fecha_anterior_ingreso);
		return false;
		}

	if (confirm("Realmente desea cambiar la fecha del ingreso?")){	
		$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_ingresos.php",
		 data: "action=actualizar_fecha_ingreso&id_registro="+id+"&nueva_fecha="+fecha_nueva_ingreso,
		 beforeSend: function(objeto){
			$("#loader").html("Actualizando...");
		  },
			success: function(datos){
				$("#loader").html(datos);

				load(1);
				//event.preventDefault();	
			}
		})
	}else{
		$("#fecha_nueva_ingreso"+id).val(fecha_anterior_ingreso);
	}
	
}

//para modifcar detalle
function modificar_detalle_egreso(id){
		var detalle_adicional_anterior= $("#detalle_adicional_anterior"+id).val();
		var detalle_adicional_nuevo= $("#detalle_adicional_nuevo"+id).val();
				
		/*
		if (detalle_adicional_nuevo==""){
		$("#detalle_adicional_nuevo"+id).val(detalle_adicional_anterior);
		return false;
		}
		*/

	if (confirm("Realmente desea cambiar el detalle del egreso?")){	
		$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_egresos.php",
		 data: "action=actualizar_detalle_egreso&id_registro="+id+"&nuevo_detalle="+detalle_adicional_nuevo,
		 beforeSend: function(objeto){
			$("#loader").html("Actualizando...");
		  },
			success: function(datos){
				$("#loader").html(datos);
				load(1);
				//event.preventDefault();	
			}
		})
	}else{
		$("#detalle_adicional_nuevo"+id).val(detalle_adicional_anterior);
	}
}
</script>