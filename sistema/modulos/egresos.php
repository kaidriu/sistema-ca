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
  <title>Egresos</title>
	<?php 
	include("../paginas/menu_de_empresas.php");
	include("../modal/detalle_ingreso_egreso.php");
	include("../modal/enviar_documentos_mail.php");
	$con = conenta_login();
	?>
  </head>
  <body>
    <div class="container">
		<div class="panel panel-info">
		<div class="panel-heading">
		<div class="btn-group pull-right">
			<form method="post" action="../modulos/nuevo_egreso.php" >
				<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nuevo Egreso</button>
			</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Egresos</h4>
		</div>

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#egresos">Egresos</a></li>
			<li><a data-toggle="tab" href="#detalle_egresos">Detalle de egresos</a></li>
			<li><a data-toggle="tab" href="#detalle_pagos_egresos">Detalle de pagos</a></li>
			<li><a data-toggle="tab" href="#detalle_cheques">Detalle de cheques</a></li>
		</ul>

		<div class="tab-content">
			<div id="egresos" class="tab-pane fade in active">
				<div class="panel-body">
				<form class="form-horizontal" role="form" >
					<div class="form-group row">
							<label for="egreso" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_ing_egr">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="egreso" placeholder="Proveedor, Número de egreso, fecha, detalle" onkeyup='load(1);'>
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
			<div id="detalle_egresos" class="tab-pane fade">		
				<div class="panel-body">
				<form class="form-horizontal" role="form" >
				<div class="form-group row">
							<label for="detegr" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_ing_egr">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="detegr" placeholder="Proveedor, Número de egreso, fecha, detalle" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<span id="loader_detalles"></span>
				</div>
				</form>
				<div id="resultados_detalles_egresos"></div><!-- Carga los datos ajax -->
				<div class='outer_div_detalles'></div><!-- Carga los datos ajax -->
				</div>
			</div>
	
			<div id="detalle_pagos_egresos" class="tab-pane fade">		
				<div class="panel-body">
				<form class="form-horizontal" role="form" >
				<div class="form-group row">
							<label class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="numero_ing_egr">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
								<input type="text" class="form-control" id="detpago" placeholder="Proveedor, Número de egreso, fecha, detalle" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<span id="loader_detalles_pagos"></span>
				</div>

				</form>
				<div id="resultados_detalles_pagos_egresos"></div><!-- Carga los datos ajax -->
				<div class='outer_div_detalles_pagos'></div><!-- Carga los datos ajax -->
				</div>
			</div>
			
			<div id="detalle_cheques" class="tab-pane fade">		
				<div class="panel-body">
				<form class="form-horizontal" role="form" >
				<div class="form-group row">

						<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon"><b>Cuenta</b></span>
							<select class="form-control input-sm" id="cuenta" name="cuenta" onchange='load(1);'>
									<?php
									$cuentas = mysqli_query($con,"SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.ruc_empresa ='".$ruc_empresa."'");
									while($row = mysqli_fetch_array($cuentas)){
									?>
									<option value="<?php echo $row['id_cuenta']?>" selected><?php echo strtoupper($row['cuenta_bancaria'])?></option>
									<?php
									}
									?>
							</select>
						</div>
						</div>
							
							<div class="col-md-6">
							<input type="hidden" id="ordenado_ch" value="for_pag.id_fp">
							<input type="hidden" id="por_ch" value="desc">
							<div class="input-group">
							<span class="input-group-addon"><b>Buscar</b></span>
								<input type="text" class="form-control input-sm" id="detcheque" placeholder="Fecha, cheque, detalle" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default btn-sm" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<span id="loader_detalles_cheques"></span>
				</div>
				</form>
				<div id="resultados_detalles_cheques"></div><!-- Carga los datos ajax -->
				<div class='outer_div_detalles_cheques'></div><!-- Carga los datos ajax -->
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="../js/notify.js"></script>
<script src="../js/validar_fecha.js" type="text/javascript"></script>
<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
 </body>
</html>
<script>
$(document).ready(function(){
	load(1);
});

function load(page){
			var egreso= $("#egreso").val();
			var detegr= $("#detegr").val();
			var detpago= $("#detpago").val();
			var detcheque= $("#detcheque").val();
			var por= $("#por").val();
			var ordenado= $("#ordenado").val();
			var por_ch= $("#por_ch").val();
			var ordenado_ch= $("#ordenado_ch").val();
			var cuenta= $("#cuenta").val();
			
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_egresos.php?action=egresos&page='+page+'&egreso='+egreso+"&por="+por+"&ordenado="+ordenado,
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
				url:'../ajax/buscar_egresos.php?action=detalle&page='+page+'&detegr='+detegr,
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
				url:'../ajax/buscar_egresos.php?action=pagos_egresos&page='+page+'&detpago='+detpago,
				 beforeSend: function(objeto){
				 $('#loader_detalles_pagos').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles_pagos").html(data).fadeIn('slow');
					$('#loader_detalles_pagos').html('');
				}
			})
			
			$("#loader_detalles_cheques").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_egresos.php?action=detalle_cheques&page='+page+'&detcheque='+detcheque+"&por_ch="+por_ch+"&ordenado_ch="+ordenado_ch+"&cuenta="+cuenta,
				 beforeSend: function(objeto){
				 $('#loader_detalles_cheques').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_detalles_cheques").html(data).fadeIn('slow');
					$('#loader_detalles_cheques').html('');
				}
			})
			
};

function ordenar(ordenado){
	$("#ordenado").val(ordenado);
	var por= $("#por").val();
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
function ordenar_ch(ordenado){
	$("#ordenado_ch").val(ordenado);
	var por= $("#por_ch").val();
	var ordenado= $("#ordenado_ch").val();
	$("#loader").fadeIn('slow');
	var value_por=document.getElementById('por_ch').value;
			if (value_por=="asc"){
			$("#por_ch").val("desc");
			}
			if (value_por=="desc"){
			$("#por_ch").val("asc");
			}
	load(1);
}
//para anular egresos
function anular_egreso(id_egreso){
			var q= $("#q").val();
			var numero_egreso = $("#numero_egreso"+id_egreso).val();
			var codigo_documento = $("#codigo_documento"+id_egreso).val();

	if (confirm("Realmente desea eliminar el egreso "+numero_egreso+" ?")){	
	$.ajax({
			type: "POST",
			url: "../ajax/buscar_egresos.php",
			data: "action=anular_egreso&codigo_documento="+codigo_documento,"q":q,
			 beforeSend: function(objeto){
				$("#loader").html("Actualizando...");
			  },
			success: function(datos){
			$("#resultados").html(datos);
			$("#loader").html("");
			load(1);
			}
			});
	}
}

//DETALLE de egreso
function mostrar_detalle_egreso(codigo){
	$(".outer_divdet").html('');
	$.ajax({
		url: "../ajax/detalle_documento.php?action=detalle_egreso&codigo_unico="+codigo,
		 beforeSend: function(objeto){
			$("#loaderdet").html("Cargando...");
		  },
		success: function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#loaderdet').html('');
	  }
	});
}

function enviar_egreso_mail(id){
			var mail_receptor = $("#mail_proveedor"+id).val();
			$("#id_documento").val(id);
			$("#mail_receptor").val(mail_receptor);
			$("#tipo_documento").val("egreso");
	}

//para enviar por mail el egreso
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
				'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando egreso por mail espere por favor...</div></div>');
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

//para modificar el estado del cheque
function modificar_estado_cheque(id){
		var estado_actual= $("#estado_actual_cheque"+id).val();
		var nuevo_estado= $("#estado_cheque"+id).val();
		var pagina = $("#pagina").val();
		var detcheque= $("#detcheque").val();
	if (confirm("Realmente desea cambiar el estado del cheque?")){	
		$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_egresos.php",
		 data: "action=actualizar_estado_cheque&id_cheque="+id+"&nuevo_estado="+nuevo_estado,
		 beforeSend: function(objeto){
			$("#loader_detalles_cheques").html("Actualizando...");
		  },
			success: function(datos){
				$("#loader_detalles_cheques").html(datos);
				$.ajax({
					url:'../ajax/buscar_egresos.php?action=detalle_cheques&page='+pagina+'&detcheque='+detcheque,
					 beforeSend: function(objeto){
					 $('#loader_detalles_cheques').html('<img src="../image/ajax-loader.gif"> Cargando...');
				  },
					success:function(data){
						$(".outer_div_detalles_cheques").html(data).fadeIn('slow');
						$('#loader_detalles_cheques').html('');
					}
					
				});
				//event.preventDefault();
			}
		})
		load(pagina);
	}else{
		$("#estado_cheque"+id).val(estado_actual);
	}
	
}

//para modifcar la fecha de pago y entrega del cheque
function modificar_fecha_entrega_cheque(id){
		var fecha_actual= $("#fecha_entrega_actual_cheque"+id).val();
		var nueva_fecha= $("#fecha_entrega_cheque"+id).val();
		var pagina = $("#pagina").val();
		var detcheque= $("#detcheque").val();
		if (validarFecha(nueva_fecha)!=true){
		alert("Error en fecha, formato: dd-mm-aaaa");
		$("#fecha_entrega_cheque"+id).val(fecha_actual);
		return false;
		}
				
		if (nueva_fecha==""){
		$("#fecha_entrega_cheque"+id).val(fecha_actual);
		return false;
		}

	if (confirm("Realmente desea cambiar la fecha?")){	
		$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_egresos.php",
		 data: "action=actualizar_fecha_entrega_cheque&id_registro="+id+"&nueva_fecha="+nueva_fecha,
		 beforeSend: function(objeto){
			$("#loader_detalles_cheques").html("Actualizando...");
		  },
			success: function(datos){
				$("#loader_detalles_cheques").html(datos);
				$.ajax({
					url:'../ajax/buscar_egresos.php?action=detalle_cheques&page='+pagina,
					 beforeSend: function(objeto){
					 $('#loader_detalles_cheques').html('<img src="../image/ajax-loader.gif"> Cargando...');
				  },
					success:function(data){
						$(".outer_div_detalles_cheques").html(data).fadeIn('slow');
						$('#loader_detalles_cheques').html('');
					}
				});
				//event.preventDefault();
			}
		})
		load(pagina);
	}else{
		$("#fecha_entrega_cheque"+id).val(fecha_actual);
	}
}


//para modifcar la fecha de egreso
function modificar_fecha_egreso(id){
		var fecha_anterior_egreso= $("#fecha_anterior_egreso"+id).val();
		var fecha_nueva_egreso= $("#fecha_nueva_egreso"+id).val();
		var pagina = $("#pagina").val();
		if (validarFecha(fecha_nueva_egreso)!=true){
		alert("Error en fecha, formato: dd-mm-aaaa");
		$("#fecha_nueva_egreso"+id).val(fecha_anterior_egreso);
		return false;
		}
				
		if (fecha_nueva_egreso==""){
		$("#fecha_nueva_egreso"+id).val(fecha_anterior_egreso);
		return false;
		}

	if (confirm("Realmente desea cambiar la fecha del egreso?")){	
		$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_egresos.php",
		 data: "action=actualizar_fecha_egreso&id_registro="+id+"&nueva_fecha="+fecha_nueva_egreso,
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
		$("#fecha_nueva_egreso"+id).val(fecha_anterior_egreso);
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


function buscar_beneficiarios(id){
$("#beneficiario_final_cheque"+id).autocomplete({
		source:'../ajax/proveedores_autocompletar.php',
		minLength: 2,
		select: function(event, ui){
			event.preventDefault();
			$('#id_beneficiario_final').val(ui.item.id_proveedor);
			$('#beneficiario_final_cheque'+id).val(ui.item.razon_social);
		}
	});

	$("#beneficiario_final_cheque"+id).on( "keydown", function( event ) {
	if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
	{
		$("#id_proveedor" ).val("");
		$("#beneficiario_final_cheque"+id).val("");	
	}
	if (event.keyCode==$.ui.keyCode.DELETE){
		$("#id_proveedor" ).val("");
		$("#beneficiario_final_cheque"+id).val("");
	}
	});
}

//para modifcar el beneficiario
function modificar_beneficiario(id){
		var nombre_actual_cheque= $("#nombre_actual_cheque"+id).val();
		var beneficiario_final_cheque= $("#beneficiario_final_cheque"+id).val();
		var id_beneficiario_actual= $("#id_beneficiario_actual"+id).val();
		var id_beneficiario_final= $("#id_beneficiario_final").val();
		var codigo_documento= $("#codigo_documento"+id).val();
		var pagina = $("#pagina").val();
		
		if (beneficiario_final_cheque==""){
		alert("Seleccione un beneficiario de la lista desplegable");
		$("#beneficiario_final_cheque"+id).val(nombre_actual_cheque);
		$("#id_beneficiario_final"+id).val(id_beneficiario_actual);
		return false;
		}
		
		if (id_beneficiario_final==""){
		$("#beneficiario_final_cheque"+id).val(nombre_actual_cheque);
		$("#id_beneficiario_final"+id).val(id_beneficiario_actual);
		return false;
		}

	if (confirm("Realmente desea cambiar el nombre del beneficiario del cheque?")){	
		$.ajax({
		 type: "POST",
		 url: "../ajax/buscar_egresos.php",
		 data: "action=actualizar_beneficiario&codigo_documento="+codigo_documento+"&beneficiario_final_cheque="+beneficiario_final_cheque+"&id_beneficiario_final="+id_beneficiario_final,
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
		$("#beneficiario_final_cheque"+id).val(nombre_actual_cheque);
		$("#id_beneficiario_final"+id).val(id_beneficiario_actual);
	}
}
</script>
