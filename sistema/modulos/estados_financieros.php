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
  <title>Estados financieros</title>
<?php 
include("../paginas/menu_de_empresas.php");
date_default_timezone_set('America/Guayaquil');
?>
  </head>
  <body>
    <div class="container">
		<div class="panel panel-info">
			<div class="panel-heading">		
				<h4><i class='glyphicon glyphicon-list-alt'></i> Estados financieros</h4>
			</div>
			<div class="panel-body">
			<form class="form-horizontal" method ="POST" action="../pdf/pdf_estados_financieros.php" name="balances" target="_blank" >
			<input type="hidden" name="id_cuenta" id="id_cuenta">
					<div class="form-group">
						<div class="col-sm-3">
						<div class="input-group">
							<span class="input-group-addon"><b>Tipo</b></span>
								<select class="form-control input-sm" id="nombre_informe" name="nombre_informe" required>
								<option value="1" selected> Estado de Situación Financiera</option>
								<option value="2" > Estado de Resultados</option>
								<option value="3" > Balance de Comprobación</option>
								<option value="sri" > Imp Renta SRI form 101</option>
								<option value="ESFS" > Supercias Estado Situación Financiera</option>
								<option value="ERIS" > Supercias Estado de Resultados</option>
								<option value="FES" > Supercias Flujo de Efectivo</option>
								<option value="FES" > Supercias Cambios en el Patrimonio</option>
								</select>
						</div>
						</div>
				
						<div class="col-sm-2">
						<div class="input-group">
							<span class="input-group-addon"><b>Desde</b></span>
							<input type="text" class="form-control input-sm text-center" name="fecha_desde" id="fecha_desde" value="<?php echo date("01-01-Y");?>">
						</div>
						</div>
						
						<div class="col-sm-2">
						<div class="input-group">
							<span class="input-group-addon"><b>Hasta</b></span>
							<input type="text" class="form-control input-sm text-center" name="fecha_hasta" id="fecha_hasta" value="<?php echo date("d-m-Y");?>">
						</div>
						</div>
						<div class="col-sm-2">
						<div class="input-group">
							<span class="input-group-addon"><b>Nivel</b></span>
								<select class="form-control input-sm" id="nivel" name="nivel" required>
								<option value="0" selected> Todos</option>
								<option value="5" > 5</option>
								<option value="4" > 4</option>
								<option value="3" > 3</option>
								<option value="2" > 2</option>
								<option value="1" > 1</option>
								</select>
						</div>
						</div>
							<div class="col-sm-3">
								<button type="button" title="Mostrar resultado" class="btn btn-info btn-sm" onclick="mostrar_informe()"><span class="glyphicon glyphicon-search" ></span></button>								
								<button type="submit" title="Imprimir pdf" class="btn btn-default btn-sm" title="Pdf">Pdf</button>
								<button type="button" onclick="document.balances.action = '../excel/estados_financieros.php'; document.balances.submit()" class="btn btn-success btn-sm" title="Descargar excel" target="_blank"><img src="../image/excel.ico" width="20" height="16"></button>
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
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
</html>
<script>
jQuery(function($){
     $("#fecha_desde").mask("99-99-9999");
	 $("#fecha_hasta").mask("99-99-9999");
});


$( function() {
	$("#fecha_desde").datepicker({
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
});

$( function() {
	$("#fecha_hasta").datepicker({
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
});

//generar informe
function mostrar_informe(){
 var nombre=$("#nombre_informe").val();
 var cuenta=$("#id_cuenta").val();
 var fecha_desde = $("#fecha_desde").val();
 var fecha_hasta = $("#fecha_hasta").val();
 var nivel = $("#nivel").val();
 
	 $.ajax({
			type: "POST",
			url: "../ajax/informes_contables.php",
			data: "action="+nombre+"&cuenta="+cuenta+"&fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&nivel="+nivel,
			 beforeSend: function(objeto){
				$('#loader').html('<img src="../image/ajax-loader.gif">');
			  },
			success: function(datos){
			$(".outer_div").html(datos);
			$("#loader").html('');
		  }
	});
}

</script>