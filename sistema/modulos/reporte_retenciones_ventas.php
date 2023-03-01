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
  <title>Reporte retenciones</title>
	<?php include("../paginas/menu_de_empresas.php");?>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
  </head>
  <body>
 	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">		
			<h4><i class='glyphicon glyphicon-list-alt'></i> Reportes de retenciones por ventas</h4>
		</div>			
			<div class="panel-body">
			<form class="form-horizontal" id="reporte_retenciones_ventas" method ="POST" action="../excel/reporte_retenciones_ventas.php" >
				<div class="form-group row">
					<label for="q" class="col-md-2 control-label">Ejercicio fiscal:</label>
					<div class="col-md-2">
						<input type="text" class="form-control" name="parametro" id="parametro" placeholder="(mes/año)">
					</div>
		
					<div class="col-md-1">
						<button type="button" class="btn btn-default" onclick ='reporte_retenciones_ventas();'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
					</div>
					<div class="col-md-1">							
						<button type="submit" class="btn btn-success" ><img src="../image/excel.ico" width="25" height="20"></button>
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
	
</body>
</html>
<script>

function reporte_retenciones_ventas(){
			var parametro= $("#parametro").val();
			$("#resultados").fadeIn('slow');
			$.ajax({
         type: "POST",
         url:'../ajax/reporte_retenciones_ventas.php',
         data: 'action=reporte_retenciones_ventas&parametro='+parametro,
		 beforeSend: function(objeto){
			$("#resultados").html("Mensaje: Cargando...");
		  },
			success: function(datos){
			$("#resultados").html(datos);
			}
			});
}

jQuery(function($){
     $("#parametro").mask("99/9999");
});
 </script>