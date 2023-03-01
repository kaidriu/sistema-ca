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
  <title>Costos Productos/servicios</title>
	<?php include("../paginas/menu_de_empresas.php");?>
  </head>
  <body>
 	
    <div class="container-fluid">
		<div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class="glyphicon glyphicon-search"></i> Costos de Productos y Servicios</h4>
		</div>			
			<div class="panel-body">
			<?php
			?>
			<form class="form-horizontal" method ="POST" action="../excel/productos.php">
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<input type="hidden" id="ordenado" value="id">
							<input type="hidden" id="por" value="desc">
							<div class="input-group">
							<input type="text" class="form-control" id="q" placeholder="Nombre" onkeyup='load(1);'>
							<span class="input-group-btn">
							<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
							</span>
							</div>						
							</div>
							<div class="col-md-1">							
							<!--<button type="submit" class="btn btn-success" title="Descargar a excel"><img src="../image/excel.ico" width="25" height="20"></button>-->
							</div>
							<div id="loader"></div>										
						</div>
			</form>
			<div id="resultados"></div><!-- Carga los datos ajax -->
			<div class="outer_div"></div><!-- Carga los datos ajax -->
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
<script src="../js/notify.js"></script>
</body>
</html>
<script>
$(document).ready(function(){
	load(1);
});


function load(page){
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	var q= $("#q").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/costos_productos.php?action=buscar_costos_productos&page='+page+'&q='+q+"&ordenado="+ordenado+"&por="+por,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
			
		}
	})
}

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



    //actualizar costo
    function actualiza_costo_producto(id) {
        var costo_producto = document.getElementById('costo_producto' + id).value;
		var costo_original = document.getElementById('costo_original' + id).value;
		var pagina = document.getElementById('pagina').value;
		//var id_producto_costo = document.getElementById('id_producto_costo'+ id).value;
		
        if (isNaN(costo_producto)) {
            alert('El valor ingresado, no es un número');
            $("#costo_original" + id).val(costo_original);
            document.getElementById('costo_producto' + id).focus();
            return false;
        }

        if ((costo_producto < 0)) {
            alert('El valor ingresado debe ser mayor a cero');
            $("#costo_original" + id).val(costo_original);
            document.getElementById('costo_producto' + id).focus();
            return false;
        }

        $.ajax({
            type: "POST",
            url: "../ajax/costos_productos.php?action=actualiza_costo_directo",
            data: "id_producto=" + id + "&costo_producto=" + costo_producto,
            beforeSend: function(objeto) {
                $("#loader").html("Actualizando...");
            },
            success: function(datos) {
                $("#loader").html(datos);
				load(pagina);
            }
        });
    }

</script>

