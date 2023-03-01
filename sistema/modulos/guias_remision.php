<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	
?>
<!DOCTYPE html>
<html lang="en">
  <head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Guías de remisión</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/enviar_documentos_mail.php");
		  include("../modal/enviar_documentos_sri.php");
		  include("../modal/anular_documentos_sri.php");
	?>
  </head>
  <body>

<div class="container-fluid">  
    <div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
						<form method="post" action="../modulos/nueva_guia_remision.php" >
							<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva Guía de remisión</button>
						</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Guías de remisión</h4>		
		</div>


			<div class="panel-body">
			<form class="form-horizontal" role="form" id="datos_cotizacion">
						<div class="form-group row">
							<input type="hidden" id="ordenado" value="id_encabezado_gr">
							<input type="hidden" id="por" value="desc">
							<label for="q" class="col-md-2 control-label">Buscar:</label>
							<div class="col-md-5">
								<input type="text" class="form-control" id="q" placeholder="Cliente, serie, guías, fecha, transportista" onkeyup='load(1);'>
							</div>
				
							<div class="col-md-3">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
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
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_guias_remision.php?action=ajax&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
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

function eliminar_guía(id_guia){
			var q= $("#q").val();
			var serie = $("#serie_guia"+id_guia).val();
			var secuencial = $("#secuencial_guia"+id_guia).val();

if (confirm("Realmente desea eliminar la guía de remisión "+serie+"-"+secuencial+" ?")){	
$.ajax({
        type: "POST",
        url: "../ajax/buscar_guias_remision.php",
        data: "id_guia="+id_guia,"q":q,
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

function enviar_gr_mail(id){
			var mail_guia = $("#mail_cliente"+id).val();
			$("#id_documento").val(id);
			$("#mail_receptor").val(mail_guia);
			$("#tipo_documento").val("gr");
	};
	
function enviar_guia_sri(id){
			var serie_guia = $("#serie_guia"+id).val();
			var secuencial_guia = $("#secuencial_guia"+id).val();
			var numero_guia = String("000000000" + secuencial_guia).slice(-9);
			var id_encabezado_guia =  $("#id_encabezado_guia"+id).val();
			$("#id_documento_sri").val(id_encabezado_guia);//no cambiar los id para que funcione con la misma modal
			$("#numero_documento_sri").val(serie_guia +'-'+ numero_guia);//no cambiar los id para que funcione con la misma modal
			$("#tipo_documento_sri").val("gr");
};

//para enviar por mail la guia de remision
 $( "#documento_mail" ).submit(function( event ) {
 $('#enviar_mail').attr("disabled", true);
 $('#mensaje_mail').attr("hidden", true);// para mostrar el mensaje de dar clik para enviar y mas abajo lo desaparece
	var parametros = $(this).serialize();
	 $.ajax({
			type: "GET",
			url: "../documentos_mail/envia_mail.php?",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_mail").html(	
				'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando guía de remisión por mail espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_mail").html(datos);
			$('#enviar_mail').attr("disabled", false);
			$('#mensaje_mail').attr("hidden", false); // lo vuelve a mostrar el mensaje cuando ya hace todo el proceso
			load(1);
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
	$("#resultados_ajax_sri").empty();
    });
   
//para enviar la GUIA al sri
$( "#documento_sri" ).submit(function( event ) {
 $('#enviar_sri').attr("disabled", true);
	var numero_guia= $("#numero_documento_sri").val();
	var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: '../facturacion_electronica/enviarComprobantesSri.php',
			data: parametros,
			 beforeSend: function(objeto){
				$('#resultados_ajax_sri').html(
				'<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Enviando guía de remisión '+numero_guia+' espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_sri").html(datos);
			$('#enviar_sri').attr("disabled", false);
			load(1);
		  }
		  
	});
  event.preventDefault();

});
//pasa el codigo del id del documento a anularse al modal de anular documentos sri
function pasa_codigo_anular_gr_e(id){
			var ruc_cliente = $("#ruc_cliente"+id).val();
			var aut_sri = $("#aut_sri"+id).val();
			var mail_cliente = $("#mail_cliente"+id).val();
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
				$("#ruc_receptor").val(ruc_cliente);
				$("#numero_autorizacion").val(aut_sri);
				$("#clave_accesso").val(aut_sri);
				$("#correo_receptor").val(mail_cliente);
				$("#tipo_comprobante").val('GUÍA DE REMISIÓN');
				$("#id_documento_modificar").val(id);					
					$('#resultados_anular').html('');
					$('#anular_sri').attr("disabled", false);				
				}
			})	
	}

//para anular guia autorizada por el sri
$( "#anular_documento_sri" ).submit(function( event ) {
 $('#anular_sri').attr("disabled", true);
	var parametros = $(this).serialize();
	if (confirm("Realmente desea anular la GR?")){
	 $.ajax({
			type: "POST",
			url: "../ajax/anular_documentos_sri.php",
			data: parametros+"&action=anular_guia",
			 beforeSend: function(objeto){
				$("#resultados_ajax_anular").html(	
				'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando solicitud, espere por favor...</div></div>');
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
</script>