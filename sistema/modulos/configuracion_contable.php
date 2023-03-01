<!DOCTYPE html>
<html lang="es">
<head>
<title>Contables</title>
<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

include("../paginas/menu_de_empresas.php");

?>
</head>
<body>
<?php
	include("../modal/detalle_compras_proveedor.php");
?>
	<div class="container-fluid">
	<!--<div class="col-md-12">-->
		<div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-pencil'></i> Configuración de cuentas contables.</h4>
		</div>		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
			<input type="hidden" name="id_cuenta" id="id_cuenta">
					<div class="form-group">
						<div class="col-md-4">
						<div class="input-group">
							<span class="input-group-addon"><b>Tipo asiento</b></span>
								<select class="form-control input-md" id="tipo_asiento" name="tipo_asiento" required>
								<option value="ventas" selected> Ventas</option>
								<option value="compras_servicios" > Compras y servicios</option>
								<option value="retenciones_ventas" > Retenciones en ventas</option>
								<option value="retenciones_compras" > Retenciones en compras</option>
								<option value="ingresos" > Ingresos</option>
								<option value="egresos" > Egresos</option>
								<option value="bancos" > Bancos</option>
								<option value="cobros" > Cobros</option>
								<option value="pagos" > Pagos</option>
								</select>
						</div>
						</div>
								<div class="col-sm-2">
									<button type="button" title="Mostrar " class="btn btn-info btn-md" onclick="mostrar_tipo()"><span class="glyphicon glyphicon-search" ></span> Mostrar</button>				
								</div>
								<span id="loader"></span>
					</div>					
			</form>
			<div id="resultados"></div><!-- Carga los datos ajax -->
			<div class='outer_div'></div><!-- Carga los datos ajax -->
	
			</div><!--fin del body de todo -->
		</div><!--fin del panel info que abarca a todo -->
	</div> <!--fin de la caja de 8 espacios -->
	<!--</div> fin del container -->

	
<?php }else{
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

function mostrar_tipo(){
	var tipo = $("#tipo_asiento").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url:'../ajax/buscar_asientos_prestablecidos.php?action=buscar_asientos_prestablecidos&tipo='+tipo,
				 beforeSend: function(objeto){
				 $('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			})	
}

//mostrar detalle
function mostrar_detalle_compras(id){
	$.ajax({
		url: "../ajax/detalle_documento.php?action=detalle_compras_proveedor&id_proveedor="+id,
		 beforeSend: function(objeto){
			$("#loaderdet").html("Mostrando detalle...");
		  },
		success: function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#loaderdet').html('');
	  }
	});
}

function guardar_cuenta(codigo_unico, id_registro, tipo, concepto_cuenta){
	$("#cuenta_contable"+codigo_unico).autocomplete({
			source:'../ajax/cuentas_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_cuenta'+codigo_unico).val(ui.item.id_cuenta);
				var id_cuenta = $("#id_cuenta"+codigo_unico).val();
				$('#cuenta_contable'+codigo_unico).val(ui.item.nombre_cuenta);
				$('#codigo_cuenta'+codigo_unico).val(ui.item.codigo_cuenta);
					$.ajax({
					url: "../ajax/guardar_asientos_programados.php?action=guarda_cuenta&id="+id_registro+"&id_cuenta="+id_cuenta+"&tipo="+tipo + "&concepto_cuenta="+concepto_cuenta,
					beforeSend: function(objeto){
						$("#loader").html("Guardando...");
					},
					success: function(data){
						$(".outer_divdet").html(data).fadeIn('fast');
						$('#loader').html('');
				}
				});
			}
		});

		//$("#cuenta_contable"+id).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
		
		$("#cuenta_contable"+codigo_unico).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_cuenta"+codigo_unico).val("");
			$("#cuenta_contable"+codigo_unico).val("");
			$("#codigo_cuenta"+codigo_unico).val("");
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#cuenta_contable"+codigo_unico).val("");
			$("#id_cuenta"+codigo_unico).val("");
			$("#codigo_cuenta"+codigo_unico).val("");
		}
		});
		
}


function eliminar_cuenta(codigo_unico, id_registro, tipo){
	if (confirm("Realmente desea eliminar la cuenta?")){	
	$.ajax({
		url: "../ajax/guardar_asientos_programados.php?action=eliminar_cuenta&id="+id_registro+"&tipo="+tipo,
		beforeSend: function(objeto){
			$("#loader").html("Guardando...");
		},
		success: function(data){
			$(".outer_divdet").html(data).fadeIn('fast');
			$('#loader').html('');
			$('#cuenta_contable'+codigo_unico).val('');
			$('#codigo_cuenta'+codigo_unico).val('');
				$('#id_cuenta'+codigo_unico).val('');
		}
		});
	}
		
}
</script>

