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
  <title>Cargar electrónicos</title>
	<?php 
	include("../paginas/menu_de_empresas.php");
	?>
  </head>
  <body>

<div class="container-fluid">  
    <div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-search'></i> Cargar Documentos Electrónicos</h4>		
		</div>
	
			<div class="panel-body">
			<div class="row">
			<div class="col-md-5">
				<div class="panel panel-info">
				<div class="table-responsive">
					<div class="table table-bordered">
					  <table class="table">
						<tr  class="info">
							<th colspan="2">Cargar archivo txt del SRI</th>
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
				
			</div>
				
				<div class="col-md-7">
					<div class="panel panel-info">
					<div class="table-responsive">
					<div class="table table-bordered">
					  <table class="table">
						<tr  class="info">
							<th colspan="3">Cargar una clave de acceso o número de autorización</th>
						</tr>
							<tr>
							<div class="form-group">
								<td class="col-sm-8">
								<div class="input-group">
								<span class="input-group-addon"><b>Clave Acceso / autorización</b></span>
									<input type="text" class="form-control" id="clave_acceso" name="clave_acceso">
								</td>
								<td class="col-xs-1">
									<button type="button" class="btn btn-info" onclick='cargar_una_clave_acceso();'><span class="glyphicon glyphicon-upload" ></span> Cargar</button>
								</td>
							</div>
							</div>
							<span id="loader_un_documento"></span>	
							</tr>
					  </table>
					</div>
					</div>
					</div>
				</div>
			</div>
					<div id="resultados_detalles_subir"></div><!-- Carga los datos ajax -->
					<div class='outer_div_subir'></div><!-- Carga los datos ajax -->
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

</script>
