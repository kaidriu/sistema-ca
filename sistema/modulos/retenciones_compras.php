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
  <title>Retenciones compras</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/enviar_documentos_mail.php");
		  include("../modal/enviar_documentos_sri.php");
		  include("../modal/anular_documentos_sri.php");
		  include("../modal/detalle_documento.php");
	?>
  </head>
  <body>	
	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
						<form method="post" action="../modulos/nueva_retencion_electronica.php" >
							<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva retención por compras</button>
						</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Retenciones por compras</h4>		
		</div>			
			<div class="panel-body">
			<form class="form-horizontal" role="form" id="datos_cotizacion">
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="id_encabezado_retencion">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Fecha, Proveedor, serie, número, código, concepto, factura" onkeyup='load(1);'>
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
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_retenciones_compras.php?action=ajax&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			})
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

function eliminar_retencion_compras(id_retencion){
			var q= $("#q").val();
			var serie = $("#serie_retencion"+id_retencion).val();
			var secuencial = $("#secuencial_retencion"+id_retencion).val();
		if (confirm("Realmente desea eliminar la retención "+serie+"-"+secuencial+" ?")){	
		$.ajax({
        type: "POST",
        url: "../ajax/buscar_retenciones_compras.php",
        data: "id_retencion="+id_retencion,"q":q,
		 beforeSend: function(objeto){
			$("#resultados").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#resultados").html(datos);
		load(1);
		}
			});
		}
};

function enviar_retencion_mail(id){
			var mail_retencion = $("#mail_proveedor"+id).val();
			$("#id_documento").val(id); //uso el id del mismo formulario para no crear un nuevo modal
			$("#mail_receptor").val(mail_retencion);
			$("#tipo_documento").val("retencion");
	};
	

//pasa el codigo del id del documento a anularse al modal de anular documentos sri
function pasa_codigo_anular_retencion_e(id){
			var ruc_proveedor = $("#ruc_proveedor"+id).val();
			var aut_sri = $("#aut_sri"+id).val();
			var mail_proveedor = $("#mail_proveedor"+id).val();
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
				$("#tipo_comprobante").val('RETENCIÓN');
				$("#id_documento_modificar").val(id);					
					$('#resultados_anular').html('');
					$('#anular_sri').attr("disabled", false);				
				}
			})
}

//para anular retencion autorizada por el sri
$( "#anular_documento_sri" ).submit(function( event ) {
 $('#anular_sri').attr("disabled", true);
	var parametros = $(this).serialize();
	if (confirm("Realmente desea anular la retención?")){
	 $.ajax({
			type: "POST",
			url: "../ajax/anular_documentos_sri.php",
			data: parametros+"&action=anular_retencion",
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

function enviar_retencion_sri(id){
			var serie_retencion = $("#serie_retencion"+id).val();
			var secuencial_retencion = $("#secuencial_retencion"+id).val();
			var numero_retencion = String("000000000" + secuencial_retencion).slice(-9);
			var id_encabezado_retencion =  $("#id_encabezado_retencion"+id).val();
			$("#id_documento_sri").val(id_encabezado_retencion);
			$("#numero_documento_sri").val(serie_retencion +'-'+ numero_retencion);
			$("#tipo_documento_sri").val("retencion");
};

//para enviar el mail de la retenciones al proeveedor
 $( "#documento_mail" ).submit(function( event ) {
 $('#enviar_mail').attr("disabled", true);
	var parametros = $(this).serialize();
	 $.ajax({
			type: "GET",
			url: "../documentos_mail/envia_mail.php?",
			data: parametros,
			 beforeSend: function(objeto){$("#resultados_ajax_mail").html(	
				'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando Retención por mail espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_mail").html(datos);
			$('#enviar_mail').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
});
//para que cuando se cierre el modal de enviar sri se reseteen los datos y se limpie
$("#btnCerrar").click(function(){
	$("#resultados_ajax_sri").empty();
    });
   
//desde el boton del modal enviar que envia al sri
$( "#documento_sri" ).submit(function( event ) {
 $('#enviar_sri').attr("disabled", true);
	var numero_retencion= $("#numero_documento_sri").val();
	var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: '../facturacion_electronica/enviarComprobantesSri.php',
			data: parametros,
			 beforeSend: function(objeto){
				$('#resultados_ajax_sri').html(
				'<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Enviando Retención '+numero_retencion+' espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_sri").html(datos);
			$('#enviar_sri').attr("disabled", false);
			load(1);
		  }
		  
	});
  event.preventDefault();

});


$( function(){
	$("#edita_fecha_r").datepicker({
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
	$( "#edita_fecha_r" ).datepicker( "option", "minDate", "-1m:+26d" );
    $( "#edita_fecha_r" ).datepicker( "option", "maxDate", "+0m +0d" );
});

function detalle_retencion_compra(id_ret){
			$("#outer_divdet").fadeIn('slow');
			$.ajax({
				url:'../ajax/detalle_documento.php?action=detalle_retencion_compras&id_ret='+id_ret,
				 beforeSend: function(objeto){
				 $('#outer_divdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle de retención...');
			  },
				success:function(data){
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#outer_divdet').html('');
				}
			})
	}
</script>