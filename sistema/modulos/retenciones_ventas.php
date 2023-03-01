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
  <title>Retenciones ventas</title>
	<?php 
	include("../paginas/menu_de_empresas.php");
	include("../modal/detalle_documento.php");
	?>
  </head>
  <body>	
	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
			<div class="btn-group pull-right">
						<form method="post" action="../modulos/nueva_retencion_ventas.php" >
							<button type='submit' class="btn btn-info" ><span class="glyphicon glyphicon-plus" ></span> Nueva retención por ventas</button>
						</form>
			</div>
			<h4><i class='glyphicon glyphicon-search'></i> Retenciones por ventas</h4>		
		</div>
		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" onclick='load(1);' href="#retenciones_ventas" >Retenciones</a></li>
			<li><a data-toggle="tab" href="#retenciones_electronicas">Cargar retenciones electrónicas</a></li>
		</ul>


		
	<div class="tab-content">
    <div id="retenciones_ventas" class="tab-pane fade in active" >
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
				<div class="form-group row">
					<label for="q" class="col-md-1 control-label">Buscar:</label>
					<div class="col-md-5">
					<input type="hidden" id="ordenado" value="fecha_compra">
					<input type="hidden" id="por" value="desc">
					<div class="input-group">
						<input type="text" class="form-control" id="q" placeholder="Cliente, serie, factura, fecha, ruc" onkeyup='load(1);'>
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
    
 <div id="retenciones_electronicas" class="tab-pane fade">		
<div class="panel-body">
			<div class="row">
			<div class="col-xs-5">
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
				
				<div class="col-xs-7">
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
					<div class='outer_div_subir'></div><!-- Carga los datos ajax -->

		
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
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
 </body>
</html>
<script>
$(document).ready(function(){
			load(1);
});

function load(page){
			var q= $("#q").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_retenciones_ventas.php?action=buscar&page='+page+'&q='+q,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			})
};

function eliminar_retencion_ventas(id_retencion){
			var q= $("#q").val();
			var serie = $("#serie_retencion"+id_retencion).val();
			var secuencial = $("#secuencial_retencion"+id_retencion).val();
		if (confirm("Realmente desea eliminar la retención "+serie+"-"+secuencial+" ?")){	
		$.ajax({
        type: "POST",
        url: "../ajax/buscar_retenciones_ventas.php",
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

function detalle_retencion_venta(id_ret){
			$("#outer_divdet").fadeIn('slow');
			$.ajax({
				url:'../ajax/detalle_documento.php?action=detalle_retencion_ventas&id_ret='+id_ret,
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