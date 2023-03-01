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
  <title>Mejor Cliente</title>
  
	<?php include("../paginas/menu_de_empresas.php");
	ini_set('date.timezone','America/Guayaquil'); 
	?>
  </head>
  <body>

<div class="container-fluid">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-search'></i> Detalle de mejor cliente según ventas</h4>	
		</div>
		<div class="panel-body">
			
		<form class="form-horizontal" method ="POST" action="../excel/mejor_cliente.php" target="_blank">
			<div class="form-group">
					<div class="col-sm-2">
					  <div class="input-group">
						<span class="input-group-addon"><b>Del</b></span>
						<input type="text" name="desde" id="desde" class="form-control input-sm text-center datepicker" value="<?php echo date("01"."-m-Y") ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-sm-2">
					<div class="input-group">
						<span class="input-group-addon"><b>Al</b></span>
						<input type="text" name="hasta" id="hasta" class="form-control input-sm text-center datepicker" value="<?php echo date("d-m-Y") ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-sm-2">
					  <div class="input-group">
						<span class="input-group-addon"><b>Los mejores</b></span>
						<input type="text" name="cantidad" id="cantidad" class="form-control input-sm" value="5" >
						</div>
					</div>
					<div class="col-sm-2">
						<button type="button" class="btn btn-info btn-sm" title="Mostrar resultado" onclick='buscar_mejor_cliente();'><span class="glyphicon glyphicon-search" ></span></button>				
						<button type="submit" class="btn btn-success btn-sm"><img src="../image/excel.ico" width="20" height="18">
					</div>

					</div>					
		</form>	
			
			<div id='resultados'></div><!-- Carga los datos ajax -->
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
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	<script src="../js/notify.js"></script>
 </body>
</html>	

<script>
jQuery(function($){
     $("#desde").mask("99-99-9999");
	 $("#hasta").mask("99-99-9999");
});
  $( function() {
    $( "#desde" ).datepicker({ dateFormat: 'dd-mm-yy' });
    $( "#hasta" ).datepicker({ dateFormat: 'dd-mm-yy' });
  } );
 

$("#producto" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#producto" ).val("");
			$("#id_producto" ).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#producto" ).val("");
			$("#id_producto" ).val("");
		}
});

function buscar_mejor_cliente(){
			var desde= $("#desde").val();
			var hasta= $("#hasta").val();
			var cantidad= $("#cantidad").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_mejor_cliente.php?action=mejor_cliente&desde='+desde+'&hasta='+hasta+'&cantidad='+cantidad,
				 beforeSend: function(objeto){
				 $('#loader').html('');
				 $.notify('Listo','success');
				 //<img src="../image/ajax-loader.gif">
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
					
				}
			});
			
};


</script>