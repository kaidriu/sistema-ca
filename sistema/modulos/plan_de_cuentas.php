<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	
?>
<?php
   // header('Content-Type: text/html; charset=ISO-8859-1');
?>
<!DOCTYPE html>
<html lang="es">
  <head>
  
  <meta charset="utf-8">
  <title>Plan de cuentas</title>
	<?php include("../paginas/menu_de_empresas.php");
		  include("../modal/plan_cuentas_inicial.php");
		  include("../modal/editar_cuenta_contable.php");
		  include("../modal/nueva_cuenta_contable.php");
	?>
  </head>
  <body>

<div class="container">  
    <div class="panel panel-info">
		<div class="panel-heading">
		<div class="btn-group pull-right">
			<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#NuevaCuentaContable"><span class="glyphicon glyphicon-plus" ></span> Crear plan de cuentas inicial</button>
		</div>
			<h4><i class='glyphicon glyphicon-search'></i> Plan de cuentas</h4>		
		</div>

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#cuentas">Cuentas</a></li>
			<li><a data-toggle="tab" href="#cargar_cuentas">Cargar cuentas</a></li>
		</ul>
	 
	<div class="tab-content">
    <div id="cuentas" class="tab-pane fade in active">
			<div class="panel-body">
			<form class="form-horizontal" role="form" method ="POST" action="../excel/reporte_plan_cuentas.php" >
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="codigo_cuenta">
							<input type="hidden" id="por" value="asc">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Cuenta, código" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<div class="col-md-1">
								<button type="submit" title="Descargar plan de cuentas a excel" class="btn btn-success" ><img alt="Brand" src="../image/excel.ico" width="25" height="20"></button>
							</div>
							<span id="loader"></span>
						</div>

			</form>
			<div id="resultados"></div><!-- Carga los datos ajax -->
			<div class='outer_div'></div><!-- Carga los datos ajax -->
			</div>
		</div>
    
 <div id="cargar_cuentas" class="tab-pane fade">		
			<div class="panel-body">
			<div class="row">
			<div class="col-md-4">
				<div class="panel panel-info">
					<div class="table table-bordered">
					  <table class="table">
						<tr  class="info">
							<th colspan=2>Cargar cuentas desde excel</th>
						</tr>
						<tr>
							<form method="post" action="" id="cargar_archivo_cuentas" name="cargar_archivo_cuentas" enctype="multipart/form-data">
								<div class="form-group row">
								<td class="col-xs-10">
									<input  class="filestyle" data-buttonText=" Archivo" type="file" id="archivo" name="archivo" data-buttonText="Archivo excel" multiple />
								</td>
								<td class="col-xs-2">
									<button type="submit" class="btn btn-info" name="subir" ><span class="glyphicon glyphicon-upload" ></span> Cargar</button>
								</td>
								</div>
							</form>
						<span id="loader_cargar_cuentas"></span>	
						</tr>				
					  </table>
					</div>
				</div>
				
			</div>
			<div class="col-md-8">
				<div class="panel panel-info">
					<div class="table table-bordered">
					  <table class="table">
						<tr  class="info">
							<th colspan=2>Opciones de plan de cuentas</th>
						</tr>
						<tr>
							
						<div class="form-group row">
						<td class="col-xs-1">
						<a href="../descargas/plancuentas.xlsx" class="list-group-item list-group-item-warning" target="_blank"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Descargar plan de cuentas modelo </a>	
						</td>
						<td class="col-xs-1">
							<div id="loader_carga"></div><!-- Carga los datos ajax -->
							<div class='outer_div_carga'></div><!-- Carga los datos ajax -->
						</td>
						
						</div>
						</tr>					
					  </table>
					</div>
				</div>
				
			</div>
			
			</div>
					<div id="resultados_cargar_cuentas"></div><!-- Carga los datos ajax -->
					<div class='outer_div_cargar_cuentas'></div><!-- Carga los datos ajax -->
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
<script type="text/javascript" src="../js/ordenado.js"></script>
<script type="text/javascript" src="../js/style_bootstrap.js"> </script>
<script src="../js/notify.js"></script>
<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
 </body>
</html>
<script>
$(document).ready(function(){
	buscar_cargas();
	load(1);
});

$(function(){
        $("#cargar_archivo_cuentas").on("submit", function(e){
            e.preventDefault();
            var formData = new FormData(document.getElementById("cargar_archivo_cuentas"));
			formData.append("dato", "valor");
			
            $.ajax({			
                url: "../ajax/subir_plan_de_cuentas.php?action=archivo_excel_plan_de_cuentas",
                type: "post",
                dataType: "html",
                data: formData,
				beforeSend: function(objeto){					
				$('#loader_cargar_cuentas').html('<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Procesando archivo excel, espere por favor...</div></div>');					    
					},
                cache: false,
                contentType: false,
				processData: false
            })
                .done(function(res){			
                    $("#resultados_cargar_cuentas").html(res);
					$("#loader_cargar_cuentas").html('');
					load(1);
                });

        });	
});

function buscar_cargas(page){
			$("#loader_carga").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_cargas_plan_cuentas.php?action=cargas_plan_de_cuentas',
				 beforeSend: function(objeto){
				 $('#loader_carga').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_carga").html(data).fadeIn('slow');
					$('#loader_carga').html('');
					
				}
			})
		}
		
		
function eliminar_carga(codigo){
		if (confirm("Realmente desea eliminar la carga?")){	
		$.ajax({
        type: "POST",
        url: "../ajax/buscar_cargas_plan_cuentas.php",
        data: "codigo="+codigo,
		 beforeSend: function(objeto){
			$("#resultados_cargar_cuentas").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#resultados_cargar_cuentas").html(datos);
		setTimeout(function (){location.href ='../modulos/plan_de_cuentas.php'}, 2000);
		load(1);
		}
			});
		}
}


function load(page){
	var q= $("#q").val();
	var por= $("#por").val();
	var ordenado= $("#ordenado").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_cuentas_contables.php?action=cuentas_contables&page='+page+'&q='+q+"&por="+por+"&ordenado="+ordenado,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
			
		}
	})
}


function eliminar_cuenta_contable(id){
		var q= $("#q").val();
		if (confirm("Realmente desea eliminar la cuenta contable?")){	
		$.ajax({
        type: "GET",
        url: "../ajax/buscar_cuentas_contables.php",
        data: "action=eliminar_cuentas_contables&id_cuenta="+id,"q":q,
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

$( "#editar_cuenta" ).submit(function( event ) {
	var page= $("#pagina").val();
  $('#actualizar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/editar_cuenta_contable.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_editar_cuentas").html("Mensaje: Actualizando...");
			  },
			success: function(datos){
			$("#resultados_ajax_editar_cuentas").html(datos);
			$('#actualizar_datos').attr("disabled", false);
			document.getElementById("editar_cuenta").reset();
			load(page);
			
		  }
	});
	event.preventDefault();
})	

function mostrar_datos(id){
		var nombre_cuenta = $("#nombre_cuenta"+id).val();
		var codigo_cuenta = $("#codigo_cuenta"+id).val();
		var nivel = $("#nivel_cuenta"+id).val();
		$("#nuevo_nivel_cuenta").val(parseInt(nivel)+parseInt(1));
		$("#mostrar_codigo_cuenta").val('La nueva cuenta se creará dentro de: '+nombre_cuenta+' '+codigo_cuenta);
		
		$.post( '../ajax/consulta_datos_plan_cuentas.php', {opcion: 'siguiente_codigo_cuenta', id_cuenta: id, nivel: nivel}).done( function(respuesta){
		$("#nuevo_codigo_cuenta").val(respuesta.replace(/^\s*|\s*$/g,""));
		});
		
		}

function obtener_datos(id){
		var nombre_cuenta = $("#nombre_cuenta"+id).val();
		var codigo_sri = $("#codigo_sri"+id).val();
		var codigo_supercias = $("#codigo_supercias"+id).val();
		var nivel_cuenta = $("#nivel_cuenta"+id).val();
		var codigo_cuenta = $("#codigo_cuenta"+id).val();

		$("#mod_nombre_cuenta").val(nombre_cuenta);
		$("#mod_codigo_sri").val(codigo_sri);
		$("#mod_codigo_supercias").val(codigo_supercias);
		$("#mod_nivel_cuenta").val(nivel_cuenta);
		$("#mod_codigo_cuenta").val(codigo_cuenta);
		$("#mod_id_cuenta").val(id);
		}
		
		
$( "#guardar_plan_inicial" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/guardar_plan_cuentas_inicial.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_cuentas").html("Mensaje: Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_cuentas").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})

$( "#guardar_nueva_cuenta" ).submit(function( event ) {
	var page= $("#pagina").val();
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/guardar_cuenta_contable.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#loader_guardar_cuenta").html("Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax_guardar_cuentas").html(datos);
			$("#loader_guardar_cuenta").html("");
			$('#guardar_datos').attr("disabled", false);
			load(page);
		  }
	});
  event.preventDefault();
})
</script>
