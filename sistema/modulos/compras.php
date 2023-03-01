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
  <title>Adquisiciones</title>
	<?php 
	include("../paginas/menu_de_empresas.php");
	include("../modal/detalle_documento.php");
	include("../modal/cobro_pago_directo.php");	
	?>
  </head>
  <body>

<div class="container-fluid">  
    <div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
					<form method="post" action="../modulos/nuevo_registro_compras.php" >
						<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva compra</button>
					</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Compras</h4>		
		</div>

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" onclick='load(1);' href="#compras" >Compras</a></li>
			<li><a data-toggle="tab" href="#detalle_compras">Detalle de compras</a></li>
			<li><a data-toggle="tab" href="#documentos_electronicos">Cargar documentos electrónicos</a></li>
		</ul>
	 
	<div class="tab-content">
    <div id="compras" class="tab-pane fade in active" >
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
				<div class="form-group row">
					<label for="q" class="col-md-1 control-label">Buscar:</label>
					<div class="col-md-5">
					<input type="hidden" id="ordenado" value="fecha_compra">
					<input type="hidden" id="por" value="desc">
					<div class="input-group">
						<input type="text" class="form-control" id="q" placeholder="Proveedor, serie, factura, fecha, ruc, estado" onkeyup='load(1);'>
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
    
 <div id="detalle_compras" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="codigo_producto">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="d" placeholder="Productos, servicios, código">
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='detalle_compra(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
							<span id="loader_detalles"></span>
						</div>
			</form>
			<div id="resultados_detalles_compras"></div>
			<div class="outer_div_detalles"></div>
			</div>
	</div>

	
	<div id="documentos_electronicos" class="tab-pane fade">		
			<div class="panel-body">
			<div class="row">
			<div class="col-md-5">
				<div class="panel panel-info">
					<div class="table table-bordered">
					  <table class="table">
						<tr  class="info">
							<th colspan=2>Cargar archivo con varias claves de acceso</th>
						</tr>
						<tr>
							<form method="post" action="" id="cargar_archivos" name="cargar_archivos" enctype="multipart/form-data">
								<div class="form-group row">
								<td class="col-xs-10">
									<input  class="filestyle" data-buttonText=" Archivo" type="file" id="archivo" name="archivo[]" data-buttonText="Archivo txt" multiple />
								</td>
								<td class="col-xs-2">
									<button type="submit" class="btn btn-info" name="subir" ><span class="glyphicon glyphicon-upload" ></span> Cargar</button>
								</td>
								</div>
							</form>
						<span id="loader_varios_documentos"></span>	
						</tr>				
					  </table>
					</div>
				</div>
				
			</div>
				
				<div class="col-md-7">
					<div class="panel panel-info">
					<div class="table table-bordered">
					  <table class="table">
						<tr  class="info">
							<th colspan=3>Cargar una clave de acceso</th>
						</tr>
							<tr>
							<div class="form-group">
								<td class="col-sm-2">Clave Acceso</td>
								<td class="col-sm-8">
									<input type="text" class="form-control" id="clave_acceso" name="clave_acceso">
								</td>
								<td class="col-xs-2">
									<button type="button" class="btn btn-info" onclick='cargar_una_clave_acceso();'><span class="glyphicon glyphicon-upload" ></span> Cargar</button>
								</td>
							</div>
							<span id="loader_un_documento"></span>	
							</tr>
					  </table>
					</div>
					</div>
				</div>
			</div>
					<div id="resultados_detalles_subir"></div><!-- Carga los datos ajax -->
					<div class="outer_div_subir"></div><!-- Carga los datos ajax -->
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
	<script type="text/javascript" src="../js/style_bootstrap.js"> </script>
	<link rel="stylesheet" href="../css/jquery-ui.css">
	<script src="../js/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>

 </body>
 <style type="text/css">
  ul.ui-autocomplete {
    z-index: 1100;
}
</style>
</html>

<script>
$(function(){
	$("#cargar_archivos").on("submit", function(e){
		e.preventDefault();
		var formData = new FormData(document.getElementById("cargar_archivos"));
		formData.append("dato", "valor");
				
			$.ajax({			
				url: "../ajax/subir_documentos_electronicos.php?action=archivo_documentos_electronicos",
				type: "post",
				dataType: "html",
				data: formData,
				beforeSend: function(objeto){					
				$('#loader_varios_documentos').html('<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Procesando documentos, espere por favor...</div></div>');					    
					},
				cache: false,
				contentType: false,
				processData: false
			})
		
			.done(function(res){
				$("#resultados_detalles_subir").html(res);
				$("#loader_varios_documentos").html('');
			});
	});	
});
	
	
function cargar_una_clave_acceso(){
		var clave_acceso= $("#clave_acceso").val();
		$("#loader_un_documento").fadeIn('slow');
		$.ajax({
			url: "../ajax/subir_documentos_electronicos.php?action=clave_compra_individual&clave_acceso="+clave_acceso,
			 beforeSend: function(objeto){
			 $('#loader_un_documento').html('<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Procesando documento, espere por favor...</div></div>');					    
		  },
			success:function(data){
				$(".outer_div_subir").html(data).fadeIn('slow');
				$("#loader_un_documento").html('');
			}
		});		
};


$(document).ready(function(){
			load(1);
});

function load(page){
			var por= $("#por").val();
			var ordenado= $("#ordenado").val();
			var q= $("#q").val();
			var p= $("#p").val();
			var d= $("#d").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_compras.php?action=compras&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			});
			/*
			$("#loader_detalles").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_detalle_compras.php?action=detalle_compras&page='+page+'&d='+d+"&por="+por+"&ordenado="+ordenado,
				 beforeSend: function(objeto){
				 $('#loader_detalles').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles").html(data).fadeIn('slow');
					$('#loader_detalles').html('');
				}
			})
			*/			
}
function detalle_compra(page){
			var por= $("#por").val();
			var ordenado= $("#ordenado").val();
			var q= $("#q").val();
			var p= $("#p").val();
			var d= $("#d").val();
			$("#loader").fadeIn('slow');

			$("#loader_detalles").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_detalle_compras.php?action=detalle_compras&page='+page+'&d='+d+"&por="+por+"&ordenado="+ordenado,
				 beforeSend: function(objeto){
				 $('#loader_detalles').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles").html(data).fadeIn('slow');
					$('#loader_detalles').html('');
				}
			})
						
}

function ordenar(ordenado){
	$("#ordenado").val(ordenado);
	var por= $("#por").val();
	var ordenado= $("#ordenado").val();
	var q= $("#q").val();
	var p= $("#p").val();
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

function eliminar_registro(id){
			var q= $("#q").val();
			var codigo_documento= $("#codigo_documento"+id).val();
			var proveedor_documento = $("#proveedor_documento"+id).val();
			var numero_documento = $("#numero_documento"+id).val();

	if (confirm("Realmente desea eliminar el registro de "+proveedor_documento+" N."+numero_documento+" ?")){	
	$.ajax({
			type: "POST",
			url: "../ajax/buscar_compras.php",
			data: "action=eliminar_compra&codigo_documento="+codigo_documento,"q":q,
			 beforeSend: function(objeto){
				$("#loader").html("Cargando...");
			  },
			success: function(datos){
				$("#loader").html(datos);
			load(1);
			}
			});
	}

}

function detalle_factura_compra(codigo){
			$("#loaderdet").fadeIn('slow');
			$.ajax({
				url:'../ajax/detalle_documento.php?action=compras&codigo='+codigo,
				 beforeSend: function(objeto){
				 $('#loaderdet').html('<img src="../image/ajax-loader.gif"> Cargando detalles...');
			  },
				success:function(data){
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#loaderdet').html('');
				}
			});

	}
	
function detalle_tributario_compra(id){
			$("#outer_divdet").fadeIn('slow');
			var id_encabezado_compra = $("#id_documento"+id).val();
			var id_documento = $("#id_comprobante"+id).val();
			var codigo_sustento = $("#codigo_sustento"+id).val();
			var codigo_deducible = $("#codigo_deducible"+id).val();
			var cod_doc_mod = $("#cod_doc_mod"+id).val();
			var codigo_comprobante = $("#codigo_comprobante"+id).val();
			
			$.ajax({
				url:'../ajax/detalle_documento.php?action=detalle_tributario&codigo_sustento='+codigo_sustento+"&id_documento="+id_documento+"&codigo_deducible="+codigo_deducible+"&id_encabezado_compra="+id_encabezado_compra+"&cod_doc_mod="+cod_doc_mod+"&codigo_comprobante="+codigo_comprobante,
				 beforeSend: function(objeto){
				 $('#outer_divdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle tributario...');
			  },
				success:function(data){
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#outer_divdet').html('');			
				}
			});
			
			
	}

//detalle de retenciones de compra
function detalle_retencion_compra(id){
	$("#outer_divdet").fadeIn('slow');
	var id_encabezado_compra = $("#id_documento"+id).val();
	$.ajax({
		url:'../ajax/detalle_documento.php?action=detalle_retenciones_compras&id_encabezado_compra='+id_encabezado_compra,
		 beforeSend: function(objeto){
		 $('#outer_divdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle retenciones...');
	  },
		success:function(data){
			$(".outer_divdet").html(data).fadeIn('slow');
			$('#outer_divdet').html('');			
		}
	});
}


function carga_modal_registrar_pago(id, valor, proveedor, numero_documento){
	document.querySelector("#detalle_pago_compra").reset();
	$(".outer_divPagoCompra").html('').fadeIn('fast');
	$("#id_FacturaCompra").val(id);
	$("#valor_pago_egreso").val(valor);
	$("#porpagar_FacturaCompra").val(valor);
	document.querySelector("#datos_pago_compra").innerHTML = 'Proveedor: '+ proveedor + ' </br>Documento: ' + numero_documento + ' Saldo por pagar: ' + valor ;
	$.ajax({
				url: "../ajax/buscar_compras.php?action=nuevo_pago_compra",
				beforeSend: function(objeto) {
					$("#loaderCobroFacturaCompra").html("Cargando...");
				},
				success: function(data) {
					$('#loaderCobroFacturaCompra').html('');
				}
			});
}


//agregar pagos	
function agrega_pagos_egreso(){
	var forma_pago_egreso=$("#forma_pago_egreso").val();
	var valor_pago_egreso=$("#valor_pago_egreso").val();
	var numero_cheque_egreso=$("#numero_cheque_egreso").val();
	var fecha_cobro_egreso=$("#fecha_cobro_egreso").val();
	var tipo=$("#tipo_egreso").val();
	
	//Inicia validacion
	if ((forma_pago_egreso==0)){
	alert('Seleccione forma de pago');
	document.getElementById('forma_pago_egreso').focus();
	return false;
	}

	//origen es para ver de que tabla me esta trayendo el dato, para segubn eso mostrar deposito o transferencia
	var origen= forma_pago_egreso.substring(0,1);

	if (origen == 1 && tipo !='0'){
	document.getElementById("tipo_egreso").value = "0";
	document.getElementById('valor_pago_egreso').focus();
	return false;
	}

	if (origen == 2 && tipo =='0'){
	alert('Seleccione cheque, débito o transferencia.');
	document.getElementById('tipo_egreso').focus();
	return false;
	}

	if (isNaN(valor_pago_egreso)){
	alert('Ingrese un valor correcto');
	document.getElementById('valor_pago_egreso').focus();
	return false;
	}
	
	if (Number(valor_pago_egreso) <0 ){
	alert('El valor ingresado debe ser mayor o igual a cero');
	document.getElementById('valor_pago_egreso').focus();
	return false;
	}
	
	if ((tipo=="C" && numero_cheque_egreso=="")){
	alert('Ingrese número de cheque.');
	document.getElementById('numero_cheque_egreso').focus();
	return false;
	}
		
	if ((tipo=="C" && numero_cheque_egreso !="" && fecha_cobro_egreso=="")){
	alert('Ingrese fecha de cobro del cheque.');
	document.getElementById('fecha_cobro_egreso').focus();
	return false;
	}
	
	var forma_pago= forma_pago_egreso.substring(1,forma_pago_egreso.length);
	//Fin validacion
	$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_compras.php?action=forma_de_pago_egreso",
		 data: "forma_pago_egreso="+forma_pago+"&valor_pago_egreso="+valor_pago_egreso+"&numero_cheque_egreso="+numero_cheque_egreso+"&fecha_cobro_egreso="+fecha_cobro_egreso+"&origen="+origen+"&tipo="+tipo,
		 beforeSend: function(objeto){
			$("#loaderCobroFacturaCompra").html("Cargando...");
		  },
			success: function(datos){
			$(".outer_divPagoCompra").html(datos).fadeIn('fast');
			$("#loaderCobroFacturaCompra").html('');
			document.getElementById("forma_pago_egreso").value = "0";
			document.getElementById("tipo_egreso").value = "0";
			document.getElementById("valor_pago_egreso").value = "";
			}
	});
event.preventDefault();
}


 //eliminar iten del egreso de forma de pago
 function eliminar_fila_pago_egreso(id){
	$.ajax({
		url: "../ajax/buscar_compras.php?action=eliminar_pago_egreso&id_fp_tmp=" + id,
		 beforeSend: function(objeto){
			$("#loaderCobroFacturaCompra").html("Eliminando...");
		  },
		success: function(datos){
			$(".outer_divPagoCompra").html(datos).fadeIn('fast');
			$('#loaderCobroFacturaCompra').html('');
		}
	});
	event.preventDefault();
}

function guarda_pago_compra() {
        $('#btnActionFormPagoCompra').attr("disabled", true);
        var id_compra = $("#id_FacturaCompra").val();
		var fecha_egreso = $("#fecha_egreso").val();
		var saldo = $("#porpagar_FacturaCompra").val();
        $.ajax({
            type: "POST",
            url: "../ajax/buscar_compras.php?action=guardar_pago_compra",
            data: "id_compra=" + id_compra + "&fecha_egreso="+fecha_egreso+"&saldo="+saldo,
            beforeSend: function(objeto) {
                $("#loaderCobroFacturaCompra").html("Guardando...");
            },
            success: function(datos) {
                $("#loaderCobroFacturaCompra").html(datos);
                $('#btnActionFormPagoCompra').attr("disabled", false);
            }
        });
        event.preventDefault();
    }
</script>
