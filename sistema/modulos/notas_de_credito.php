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
  <title>Notas de crédito</title>
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
			<form method="post" action="../modulos/nueva_nota_credito.php" >
							<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva nota de crédito</button>
						</form>			
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Notas de crédito</h4>
		</div>			
			<div class="panel-body">
			<form class="form-horizontal" >
						<div class="form-group row">
							<input type="hidden" id="ordenado" value="id_encabezado_nc">
							<input type="hidden" id="por" value="desc">
							<label for="nc" class="col-md-2 control-label">Buscar por:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="nc" placeholder="Fecha, Nombre, número" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'>
									<span class="glyphicon glyphicon-search" ></span> Buscar</button>
							</div>
							</div>				
						</div>
			</form>
			<div id="resultados_nc"></div><!-- Carga los datos ajax -->
			<div class='outer_div_nc'></div><!-- Carga los datos ajax -->
			</div>
		</div>

	</div>

<?php

}else{
	?>
		<div class="alert alert-danger alert-dismissable">
		<a href="../includes/logout.php" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></a>
		<strong>Hey!</strong"> Usted no tiene permisos para acceder a este sitio! </div>
		 <?php
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
			var q= $("#nc").val();
			$("#loader_nc").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_nc.php?action=ajax&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
				 beforeSend: function(objeto){
				 $('#loader_nc').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_nc").html(data).fadeIn('slow');
					$('#loader_nc').html('');
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

function eliminar_nc(id_nc){
			var q= $("#q").val();
			var serie = $("#serie_nc"+id_nc).val();
			var secuencial = $("#secuencial_nc"+id_nc).val();
		if (confirm("Realmente desea eliminar la nota de crédito "+serie+"-"+secuencial+" ?")){	
		$.ajax({
        type: "POST",
        url: "../ajax/buscar_nc.php",
        data: "id_nc="+id_nc,"q":q,
		 beforeSend: function(objeto){
			$("#resultados_nc").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#resultados_nc").html(datos);
		load(1);
		}
			});
		}
};

//pasa el codigo del id del documento a anularse al modal de anular documentos sri
function pasa_codigo_anular_nc_e(id){
			var ruc_cliente = $("#ruc_cliente"+id).val();
			var aut_sri = $("#aut_sri"+id).val();
			var mail_cliente = $("#mail_cliente"+id).val();
			$("#ruc_receptor").val('');
			$("#numero_autorizacion").val('');
			$("#clave_accesso").val('');
			$("#correo_receptor").val('');
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
				$("#tipo_comprobante").val('NOTA DE CRÉDITO');
				$("#id_documento_modificar").val(id);					
					$('#resultados_anular').html('');
					$('#anular_sri').attr("disabled", false);				
				}
			})

	}

//para anular nc autorizada por el sri
$( "#anular_documento_sri" ).submit(function( event ) {
 $('#anular_sri').attr("disabled", true);
	var parametros = $(this).serialize();
	if (confirm("Realmente desea anular la NC?")){
	 $.ajax({
			type: "POST",
			url: "../ajax/anular_documentos_sri.php",
			data: parametros+"&action=anular_nc",
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

function enviar_nc_mail(id){
			var mail_nc = $("#mail_cliente"+id).val();
			$("#id_documento").val(id); //uso el id del mismo formulario para no crear un nuevo modal
			$("#mail_receptor").val(mail_nc);
			$("#tipo_documento").val("nc");		
	};
	
function enviar_nc_sri(id){
			var serie_nc = $("#serie_nc"+id).val();
			var secuencial_nc = $("#secuencial_nc"+id).val();
			var numero_nc = String("000000000" + secuencial_nc).slice(-9);
			var id_encabezado_nc =  $("#id_encabezado_nc"+id).val();
			$("#id_documento_sri").val(id_encabezado_nc);
			$("#numero_documento_sri").val(serie_nc +'-'+ numero_nc);
			$("#tipo_documento_sri").val("nc");
			
};

//para enviar por mail la nc
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
				'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando nota de crédito por mail espere por favor...</div></div>');
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
   
//para enviar la nc al sri
$( "#documento_sri" ).submit(function( event ) {
 $('#enviar_sri').attr("disabled", true);
	var numero_nc= $("#numero_documento_sri").val();
	var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: '../facturacion_electronica/enviarComprobantesSri.php',
			data: parametros,
			 beforeSend: function(objeto){
				$('#resultados_ajax_sri').html(
				'<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Enviando Nota de crédito '+numero_nc+' espere por favor...</div></div>');
			  },
			success: function(datos){
			$("#resultados_ajax_sri").html(datos);
			$('#enviar_sri').attr("disabled", false);
			load(1);
		  }
		  
	});
  event.preventDefault();

});
/*
function detalle_nc_e(id_fe){
			var serie_nc= $("#serie_nc"+id_fe).val();
			var secuencial_nc= $("#secuencial_nc"+id_fe).val();
			$("#loaderdet").fadeIn('slow');
			$.ajax({
				url:'../ajax/detalle_nc_e.php?action=ajax&serie_nc='+serie_nc+'&secuencial_nc='+secuencial_nc,
				 beforeSend: function(objeto){
				 $('#loaderdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle de nota de crédito...');
			  },
				success:function(data){
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#loaderdet').html('');
				}
			})
	}
	*/
function editar_nc_e(id_fe){
			var serie_factura= $("#serie_factura"+id_fe).val();
			var secuencial_factura= $("#secuencial_factura"+id_fe).val();
			var fecha_factura= $("#fecha_factura"+id_fe).val();
			var id_factura= $("#id_encabezado_factura"+id_fe).val();
			$("#edita_fecha_f").val(fecha_factura);
			$("#edita_serie_f").val(serie_factura);
			$("#edita_secuencial_f").val(secuencial_factura);
			$("#id_factura_electronica").val(id_factura);
			
			$("#loaderedit").fadeIn('fast');
			$.ajax({
				url:'../ajax/editar_factura_e.php?action=ajax&serie_factura='+serie_factura+'&secuencial_factura='+secuencial_factura,
				 beforeSend: function(objeto){
				 $('#loaderedit').html('<img src="../image/ajax-loader.gif"> Cargando... por favor espere a que se cargue la información.');
			  },
				success:function(data){
					$(".outer_divedit").html(data).fadeIn('fast');
					$('#loaderedit').html('');
				}
			})
	}
	
$( function() {
	$("#edita_fecha_f").datepicker({
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
	$( "#edita_fecha_f" ).datepicker( "option", "minDate", "-1m:+26d" );
    $( "#edita_fecha_f" ).datepicker( "option", "maxDate", "+0m +0d" );
	} );


function agregar_forma_pago_fe(){
					var codigo_forma_pago= $("#forma_pago_e").val();
					var serie_factura= $("#edita_serie_f").val();
					var secuencial_factura= $("#edita_secuencial_f").val();

			if (codigo_forma_pago=='0'){
				alert("Seleccione una forma de pago.");
			return false;
			}
		if (codigo_forma_pago!='0'){
					$("#loaderedit").fadeIn('slow');
					$.ajax({
						url:'../ajax/editar_factura_e.php?serie_factura='+serie_factura+'&secuencial_factura='+secuencial_factura+'&codigo_forma_pago='+codigo_forma_pago,
						 beforeSend: function(objeto){
						 $('#loaderedit').html('<img src="../image/ajax-loader.gif"> Agregando nueva forma de pago...');
					  },
						success:function(data){
							$(".outer_divedit").html(data).fadeIn('slow');
							$('#loaderedit').html('');
						}
					});
			};
	}
function agregar_info_adicional(){
			var concepto_adicional= $("#concepto_adicional").val();
			var detalle_adicional= $("#detalle_adicional").val();
			var serie_factura= $("#edita_serie_f").val();
			var secuencial_factura= $("#edita_secuencial_f").val();

				if ((concepto_adicional=='')){
					alert("Ingrese un concepto.");
				return false;
				}
				if ((detalle_adicional=='')){
					alert("Ingrese un detalle referente al concepto");
				return false;
				}
			if ((concepto_adicional !='') && (detalle_adicional !='')) {
						$("#loaderedit").fadeIn('slow');
						$.ajax({
							url:'../ajax/editar_factura_e.php?serie_factura='+serie_factura+'&secuencial_factura='+secuencial_factura+'&concepto='+concepto_adicional+'&detalle='+detalle_adicional,
							 beforeSend: function(objeto){
							 $('#loaderedit').html('<img src="../image/ajax-loader.gif"> Agregando nuevo detalle adicional...');
						  },
							success:function(data){
								$(".outer_divedit").html(data).fadeIn('slow');
								$('#loaderedit').html('');
							}
						});
				};
	}
function eliminar_forma_pago(id_forma_pa){
			var id_forma_pago = $("#forma_pago"+id_forma_pa).val();
			var serie_factura= $("#edita_serie_f").val();
			var secuencial_factura= $("#edita_secuencial_f").val();
			$("#loaderedit").fadeIn('slow');
			$.ajax({
				url:'../ajax/editar_factura_e.php?serie_factura='+serie_factura+'&secuencial_factura='+secuencial_factura+'&id_forma_pago='+id_forma_pago,
				 beforeSend: function(objeto){
				 $('#loaderedit').html('<img src="../image/ajax-loader.gif"> Eliminando forma de pago...');
			  },
				success:function(data){
					$(".outer_divedit").html(data).fadeIn('slow');
					$('#loaderedit').html('');
				}
			});
	}

function eliminar_detalle_adicional(id_info_adicional){
			var id_adicional = $("#id_adicional"+id_info_adicional).val();
			var serie_factura= $("#edita_serie_f").val();
			var secuencial_factura= $("#edita_secuencial_f").val();
			

			$("#loaderedit").fadeIn('slow');
			$.ajax({
				url:'../ajax/editar_factura_e.php?serie_factura='+serie_factura+'&secuencial_factura='+secuencial_factura+'&id_info_adi='+id_adicional,
				 beforeSend: function(objeto){
				 $('#loaderedit').html('<img src="../image/ajax-loader.gif"> Eliminando detalle adicional...');
			  },
				success:function(data){
					$(".outer_divedit").html(data).fadeIn('slow');
					$('#loaderedit').html('');
				}
			});
	};



function detalle_nc(id_nc){
			var serie_nc= $("#serie_nc"+id_nc).val();
			var secuencial_nc= $("#secuencial_nc"+id_nc).val();
			$("#loaderdet").fadeIn('slow');
			$.ajax({
				url:'../ajax/detalle_documento.php?action=notas_credito&serie_nc='+serie_nc+'&secuencial_nc='+secuencial_nc,
				 beforeSend: function(objeto){
				 $('#loaderdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle de nc...');
			  },
				success:function(data){
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#loaderdet').html('');
				}
			})
	}
</script>

