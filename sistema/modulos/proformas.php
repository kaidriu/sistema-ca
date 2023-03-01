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
  <title>Proformas</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/enviar_documentos_mail.php");
		  include("../modal/detalle_documento.php");
		  include("../modal/editar_factura_e.php");
		  include("../modal/formas_de_pago.php");
	?>
  </head>
  <body>

<div class="container-fluid">  
    <div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
						<form method="POST" action="../modulos/nueva_proforma.php" >
							<input type="hidden" name="action" value="regresa">
							<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva proforma</button>
						</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Proformas</h4>		
		</div>

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#proformas">Proformas</a></li>
			<li><a data-toggle="tab" href="#detalle_proformas">Detalle de proformas</a></li>
			<li><a data-toggle="tab" href="#detalle_adicionales">Detalles adicionales</a></li>
		</ul>
	 
	<div class="tab-content">
    <div id="proformas" class="tab-pane fade in active">
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="id_encabezado_proforma">
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
    
 <div id="detalle_proformas" class="tab-pane fade">		
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
			<div id="resultados_detalles_proformas"></div><!-- Carga los datos ajax -->
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
<link rel="stylesheet" href="../css/jquery-ui.css">
<script src="../js/jquery-ui.js"></script>
<script src="../js/notify.js"></script>
<script src="../js/ordenado.js"></script>
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
				url:'../ajax/buscar_proformas.php?action=proformas&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
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
				url:'../ajax/buscar_detalle_proformas.php?action=ajax&page='+page+'&d='+d,
				 beforeSend: function(objeto){
				 $('#loader_detalles').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles").html(data).fadeIn('slow');
					$('#loader_detalles').html('');
				}
			});
			
			$("#loader_adicionales").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_detalle_adicionales_facturas.php?action=ajax&page='+page+'&a='+a,
				 beforeSend: function(objeto){
				 $('#loader_adicionales').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_adicionales").html(data).fadeIn('slow');
					$('#loader_adicionales').html('');
				}
			})
			*/
			
};

function anular_proforma(id_proforma){
			var q= $("#q").val();
			var secuencial = $("#secuencial_proforma"+id_proforma).val();

	if (confirm("Realmente desea anular la proforma "+secuencial+" ?")){	
	$.ajax({
			type: "POST",
			url: "../ajax/buscar_proformas.php",
			data: "action=anular_proforma&id_proforma="+id_proforma,"q":q,
			 beforeSend: function(objeto){
				$("#loader").html("Actualizando...");
			  },
			success: function(datos){
			$("#resultados").html(datos);
			$("#loader").html("");
			load(1);
			}
			});
	};

}

function enviar_mail_proforma(id){
			var mail_receptor = $("#mail_cliente"+id).val();
			$("#id_documento").val(id);
			$("#mail_receptor").val(mail_receptor);
			$("#tipo_documento").val("proforma");
	}

function validarEmail(valor) {
  emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;

 // if (emailRegex.test(valor)){
	if (valor !=""){
   return "correcto";
  } else {
   return "incorrecto";
  }
}

//para enviar la proforma al sri
$( "#documento_mail" ).submit(function( event ) {
 $('#enviar_mail').attr("disabled", true);
	var id_documento = $("#id_documento").val();
	var mail_receptor = $("#mail_receptor").val();
	var tipo_documento = $("#tipo_documento").val();
	var pagina = $("#pagina").val();//esta variable me la trae de buscar proformas
	/*
	var email = validarEmail(mail_receptor);
	//para validar el correo
	if (email != 'correcto'){
	alert("La dirección de email es incorrecta.");
	document.getElementById('mail_receptor').focus();
	$('#enviar_mail').attr("disabled", false);
	return false;
	}
	*/
			
	//modificar el correo en la proforma
	$.post( '../ajax/buscar_proformas.php', {action: 'actualizar_mail_cliente', id_documento: id_documento, mail: mail_receptor }).done( function( respuesta ){
		var actualizado = respuesta;
//		alert(respuesta);
		});

	$.ajax({
			type: "POST",
			url: '../facturacion_electronica/enviarComprobantesSri.php',
			data: 'id_documento_sri='+id_documento+'&tipo_documento_sri='+tipo_documento+'&modo_envio=online',
			 beforeSend: function(objeto){
				$('#resultados_ajax_mail').html('<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Enviando proforma espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_mail").html(datos);
			$('#enviar_mail').attr("disabled", false);
			load(pagina);
		  } 
	});


  event.preventDefault();
});


//para que cuando se cierre el modal de enviar mail se reseteen los datos y se limpie
$("#cerrar_mail").click(function(){
	$("#resultados_ajax_mail").empty();
    });

function detalle_proforma(id){
			var codigo_unico= id;
			$("#loaderdet").fadeIn('slow');
			$.ajax({
				url:'../ajax/detalle_documento.php?action=proformas&codigo_unico='+codigo_unico,
				 beforeSend: function(objeto){
				 $('#loaderdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle de proforma...');
			  },
				success:function(data){
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#loaderdet').html('');
				}
			})
	}
	
	
//facturar proforma
function facturar_proforma(id_documento, codigo, serie, secuencial){
		var q= $("#q").val();
		var tipo_documento = 'proforma';
		
	if (confirm("Realmente desea facturar la proforma "+serie+"-"+secuencial+"?")){
		$.post( '../ajax/buscar_ultima_factura.php', {serie_fe: serie}).done( function( respuesta )	{
		var factura_final = respuesta;
	//generar el pdf de la proforma

		//modificar el correo en la proforma
		//$.post( '../ajax/buscar_proformas.php', {action: 'actualizar_mail_cliente', id_documento: id_documento, mail: '' }).done( function( respuesta ){
		//	var actualizado = respuesta;
		//	});

		$.ajax({
				type: "POST",
				url: '../facturacion_electronica/enviarComprobantesSri.php',
				data: 'id_documento_sri='+id_documento+'&tipo_documento_sri='+tipo_documento+'&modo_envio=offline',
				 beforeSend: function(objeto){
					$('#loader').html('Generando pdf...');
				  },
				success: function(datos){
				$("#loader").html(datos);
				//$("#loader").html('');
			  } 
		});
		

//hasta aqui generar el pdf
		
			$.ajax({
			type: "GET",
			url: "../ajax/buscar_proformas.php",
			data: "action=facturar_proforma&codigo_unico="+codigo+"&factura_final="+factura_final+"&serie="+serie+"&secuencial="+secuencial,"q":q,
			 beforeSend: function(objeto){
				$("#loader").html("Cargando...");
			  },
			success: function(datos){
			$("#resultados").html(datos);
			$("#loader").html('');
			load(1);
			}
			});
		});
		
	}
	
}
</script>