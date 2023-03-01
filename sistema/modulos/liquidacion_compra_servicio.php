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
  <title>Liquidaciones</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/enviar_documentos_mail.php");
		  include("../modal/enviar_documentos_sri.php");
		  include("../modal/detalle_documento.php");
		  include("../modal/anular_documentos_sri.php");
		  //include("../modal/formas_de_pago.php");
	?>
  </head>
  <body>

<div class="container-fluid">  
    <div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
						<form method="POST" action="../modulos/nueva_liquidacion_cs.php" >
							<input type="hidden" name="action" value="regresa">
							<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva liquidación</button>
						</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Liquidaciones de compras de bienes o prestación de servicios </h4>		
		</div>

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#facturas">Liquidaciones</a></li>
			<li><a data-toggle="tab" href="#detalle_facturas">Detalle de liquidaciones</a></li>
			<li><a data-toggle="tab" href="#detalle_adicionales">Detalles adicionales</a></li>
		</ul>
	 
	<div class="tab-content">
    <div id="facturas" class="tab-pane fade in active">
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="id_encabezado_liq">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Cliente, serie, factura, fecha, ruc, estado" onkeyup='load(1);'>
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
    
 <div id="detalle_facturas" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="d" placeholder="Productos, servicios, código" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
							<span id="loader_detalles"></span>
						</div>
			</form>
			<div id="resultados_detalles_facturas"></div><!-- Carga los datos ajax -->
			<div class='outer_div_detalles'></div><!-- Carga los datos ajax -->
			</div>
	</div>
	<div id="detalle_adicionales" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="a" placeholder="Detalle adicionales" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
								<span id="loader_adicionales"></span>
						</div>
			</form>
			<div id="resultados_detalles_adicionales"></div><!-- Carga los datos ajax -->
			<div class='outer_div_adicionales'></div><!-- Carga los datos ajax -->
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
<link rel="stylesheet" href="../css/js/jquery-ui.css">
<script src="../js/jquery-ui.js"></script>
<script src="../js/notify.js"></script>
 </body>
</html>
<script>
$(document).ready(function(){
			load(1);
});

function load(page){
			var por= $("#por").val();
			var ordenado= $("#ordenado").val();
			var q= $("#q").val();
			var d= $("#d").val();
			var a= $("#a").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_liquidacion_compras.php?action=ajax&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			});
						
};

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

function eliminar_liquidacion(id_lc){
			var q= $("#q").val();
			var serie = $("#serie_liquidacion"+id_lc).val();
			var secuencial = $("#secuencial_liquidacion"+id_lc).val();

	if (confirm("Realmente desea eliminar la liquidación "+serie+"-"+secuencial+" ?")){	
	$.ajax({
			type: "POST",
			url: "../ajax/buscar_liquidacion_compras.php",
			data: "id_lc="+id_lc,"q":q,
			 beforeSend: function(objeto){
				$("#resultados").html("Mensaje: Cargando...");
			  },
			success: function(datos){
			$("#resultados").html(datos);
			load(1);
			}
			});
	};
};
//pasa el codigo del id del documento a anularse al modal de anular documentos sri
function pasa_codigo_anular_lc(id){
			var ruc_proveedor = $("#ruc_cliente"+id).val();
			var aut_sri = $("#aut_sri"+id).val();
			var mail_proveedor = $("#mail_cliente"+id).val();
			$("#ruc_receptor").val('');
			$("#numero_autorizacion").val('');
			$("#clave_accesso").val('');
			$("#correo_receptor").val('');
			$("#tipo_comprobante").val('');
			$('#anular_sri').attr("disabled", true);
			$.ajax({
			url:'../ajax/detalle_documento.php?action=info_fecha_autorizacion&clave_acceso='+aut_sri,
			beforeSend: function(objeto){
			 $('#resultados_anular').html('');
			},
			success:function(datos){
				$("#fecha_autorizacion").val(datos);		
			}
			});
			
			$.ajax({
				url:'../ajax/detalle_documento.php?action=info_estado_documento&clave_acceso='+aut_sri,
				beforeSend: function(objeto){
				 $('#resultados_anular').html('<img src="../image/ajax-loader.gif"> Consultando SRI, espere por favor...');
			  },
				success:function(datos){
				$("#estado_sri_consultado").val(datos);
				$("#ruc_receptor").val(ruc_proveedor);
				$("#numero_autorizacion").val(aut_sri);
				$("#clave_accesso").val(aut_sri);
				$("#correo_receptor").val(mail_proveedor);
				$("#tipo_comprobante").val('LIQUIDACIÓN DE COMPRAS Y SERVICIOS');
				$("#id_documento_modificar").val(id);					
					$('#resultados_anular').html('');
					$('#anular_sri').attr("disabled", false);				
				}
			})	

	}

//para anular lc autorizada por el sri
$( "#anular_documento_sri" ).submit(function( event ) {
 $('#anular_sri').attr("disabled", true);
	var parametros = $(this).serialize();
	 if (confirm("Realmente desea anular la LCS?")){
	 $.ajax({
			type: "POST",
			url: "../ajax/anular_documentos_sri.php",
			data: parametros+"&action=anular_liquidacion",
			 beforeSend: function(objeto){
				$("#resultados_ajax_anular").html('<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando solicitud, espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_anular").html(datos);
			$('#anular_sri').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
	 };
});


function enviar_liquidacion_mail(id){
			var mail_receptor = $("#mail_cliente"+id).val();
			$("#id_documento").val(id);
			$("#mail_receptor").val(mail_receptor);
			$("#tipo_documento").val("liquidacion");
	};

function enviar_liquidacion_sri(id){
		var serie_liquidacion = $("#serie_liquidacion"+id).val();
		var secuencial_liquidacion = $("#secuencial_liquidacion"+id).val();
		var numero_liquidacion = String("000000000" + secuencial_liquidacion).slice(-9);
		var id_encabezado_liquidacion =  $("#id_encabezado_liquidacion"+id).val();
		$("#id_documento_sri").val(id_encabezado_liquidacion);
		$("#numero_documento_sri").val(serie_liquidacion +'-'+ numero_liquidacion);
		$("#tipo_documento_sri").val("liquidacion");
};

//para enviar por mail la liquidacion
 $( "#documento_mail" ).submit(function( event ) {
 $('#enviar_mail').attr("disabled", true);
 $('#mensaje_mail').attr("hidden", true);// para mostrar el mensaje de dar clik para enviar y mas abajo lo desaparece
	var parametros = $(this).serialize();
	var pagina = $("#pagina").val();
	 $.ajax({
			type: "GET",
			url: "../documentos_mail/envia_mail.php?",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_mail").html(	
				'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando liquidación de compras o servicios por mail espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_mail").html(datos);
			$('#enviar_mail').attr("disabled", false);
			$('#mensaje_mail').attr("hidden", false); // lo vuelve a mostrar el mensaje cuando ya hace todo el proceso
			load(pagina);
		  }
	});
  event.preventDefault();
});
//para que cuando se cierre el modal de enviar mail se reseteen los datos y se limpie
$("#cerrar_mail").click(function(){
	$("#resultados_ajax_mail").empty();
    });

//para que cuando se cierre el modal de enviar sri se reseteen los datos y se limpie
$("#btnCerrar").click(function(){
	load(1);
	$("#resultados_ajax_sri").empty();
	$("#resultados_ajax_sri").html("<div class='alert alert-info'><span class='glyphicon glyphicon-expand'></span> Dar click en enviar para autorizar el documento.</div>")
    });
   
//para enviar la liquidacion al sri
$( "#documento_sri" ).submit(function( event ) {
 $('#enviar_sri').attr("disabled", true);
	var numero_liquidacion= $("#numero_documento_sri").val();
	var parametros = $(this).serialize();
	var pagina = $("#pagina").val();
	 $.ajax({
			type: "POST",
			url: '../facturacion_electronica/enviarComprobantesSri.php',
			data: parametros,
			 beforeSend: function(objeto){
				$('#resultados_ajax_sri').html(	'<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Enviando liquidación de compras o servicios '+numero_liquidacion+' espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_sri").html(datos);
			$('#enviar_sri').attr("disabled", false);
			load(pagina);
		  }
	
	});
  event.preventDefault();
});


function detalle_liquidacion(id_lc){
	var serie_liquidacion= $("#serie_liquidacion"+id_lc).val();
	var secuencial_liquidacion= $("#secuencial_liquidacion"+id_lc).val();
	$("#loaderdet").fadeIn('slow');
	$.ajax({
		url:'../ajax/detalle_documento.php?action=liquidacion_compras&serie_liquidacion='+serie_liquidacion+'&secuencial_liquidacion='+secuencial_liquidacion,
		 beforeSend: function(objeto){
		 $('#loaderdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle de la liquidación...');
	  },
		success:function(data){
			$(".outer_divdet").html(data).fadeIn('slow');
			$('#loaderdet').html('');
		}
	})
}

function enviar_liquidacion_compras(clave_acceso){
		$("#loader").fadeIn('slow');
		$.ajax({
			url: "../ajax/subir_documentos_electronicos.php?action=clave_compra_individual&clave_acceso="+clave_acceso,
			 beforeSend: function(objeto){
			 $('#loader').html('<img src="../image/ajax-loader.gif"> Procesando...');					    
		  },
			success:function(data){
				$(".outer_div").html(data).fadeIn('slow');
				setTimeout(function () {location.reload()}, 50 * 30);
				$("#loader").html('');
				//load(1);
			}
		});	
//event.preventDefault();		
}

</script>