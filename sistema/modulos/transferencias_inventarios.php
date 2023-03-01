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
  <title>Transferencias inventario</title>
	<?php include("../paginas/menu_de_empresas.php");
	?>
  </head>
  <body>
<div class="container-fluid">  
    <div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-search'></i> Transferencias de inventarios</h4>		
		</div>
		<div class="panel-body">
			<form class="form-horizontal" role="form" >
				<div class="form-group row">
					
					<label for="q" class="col-md-1 control-label">Buscar:</label>
					<div class="col-md-4">
					<input type="hidden" id="ordenado" value="nombre_producto">
					<input type="hidden" id="por" value="asc">
					<div class="input-group">
						<input type="text" class="form-control" id="q" placeholder="Producto" onkeyup='load(1);'>				
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
	carga_existencias();
});


function carga_existencias(){
	$.ajax({
		url:'../ajax/existencias_inventarios_tmp.php?action=general',
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Actualizando existencias, espere por favor...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
			$("#q" ).val("");
			load(1);
		}
		
	})
}


function load(page){
	var q= $("#q").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_existencias_inventarios.php?action=transferencias&page='+page+'&q='+q+"&ordenado="+ordenado+"&por="+por,
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


function transferir(id){
	var q= $("#q").val();
	var ordenado= $("#ordenado").val();
	var por= $("#por").val();
	var id_producto= $("#id_producto"+id).val();
	var cantidad= $("#cantidad_transferir"+id).val();
	var id_medida_transferir= $("#medida_transferir"+id).val();
	var id_bodega_transferir= $("#id_bodega_transferir"+id).val();
	var id_bodega_existente= $("#id_bodega_existente"+id).val();
	var existencia= $("#existencia"+id).val();
	var id_medida_producto= $("#id_medida_entrada"+id).val();
	
	$("#loader").fadeIn('slow');
	$.ajax({
		url:'../ajax/buscar_existencias_inventarios.php?action=transferir&q='+q+'&ordenado='+ordenado+'&por='+por+'&id_producto='+id_producto+'&cantidad='+cantidad+'&id_medida_transferir='+id_medida_transferir+'&id_bodega_transferir='+id_bodega_transferir+'&id_bodega_existente='+id_bodega_existente+'&id='+id+'&existencia='+existencia+'&id_medida_producto='+id_medida_producto,
		 beforeSend: function(objeto){
		 $('#loader').html('<img src="../image/ajax-loader.gif"> Transfiriendo...');
	  },
		success:function(data){
			$(".outer_div").html(data).fadeIn('slow');
			$('#loader').html('');
			carga_existencias();
		}
	});
	event.preventDefault();
}


</script>