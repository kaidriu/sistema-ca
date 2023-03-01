<html lang="es">
<meta name="viewport" content="width=device-width, initial-scale=1">
  <head>
  <title>Editar Factura Electrónica</title>
</head>	

<?php

session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

include("../paginas/menu_de_empresas.php");
include("../modal/nuevo_cliente_factura.php");

ini_set('date.timezone','America/Guayaquil');
	//para borrar los datos de la factura que este en temporal
$con = conenta_login();
if (isset($_SESSION['id_usuario'])){
$delete_factura_tmp = mysqli_query($con, "DELETE FROM factura_tmp WHERE id_usuario = '".$id_usuario."'");
$delete_adicional_tmp = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."'");
$delete_propina_tasa_tmp = mysqli_query($con, "DELETE FROM propina_tasa_tmp WHERE id_usuario = '".$id_usuario."'");
}
//hacer las consultas a la base de todo el registro de esa factura a editar
		$id_factura=base64_decode($_GET['id_factura']);
		$busca_datos_encabezados = "SELECT * FROM encabezado_factura WHERE id_encabezado_factura = '".$id_factura."' ";
		$resultado_encabezado = $con->query($busca_datos_encabezados);
		$datos_encabezados = mysqli_fetch_array($resultado_encabezado);
		$serie = $datos_encabezados['serie_factura'];
		$secuencial = $datos_encabezados['secuencial_factura'];
		$id_cliente = $datos_encabezados['id_cliente'];
		$fecha_factura = $datos_encabezados['fecha_factura'];
		$guia_remision = $datos_encabezados['guia_remision'];
		$propina = $datos_encabezados['propina'];
		$tasa_turistica = $datos_encabezados['tasa_turistica'];
		$referencia_inventario="Venta según factura: ".$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT);

//para saber los decimales que trabaja esta empresa
	$busca_info_sucursal = "SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' and serie = '".$serie."' ";
	$result_info_sucursal = $con->query($busca_info_sucursal);
	$info_sucursal = mysqli_fetch_array($result_info_sucursal);
	$decimales = intval($info_sucursal['decimal_doc']);
		
//datos del cliente
		$busca_datos_cliente = "SELECT * FROM clientes WHERE id = '".$id_cliente."' ";
		$resultado_cliente = $con->query($busca_datos_cliente);
		$datos_cliente = mysqli_fetch_array($resultado_cliente);
		$nombre_cliente = $datos_cliente['nombre'];
		$tipo_id_cliente = $datos_cliente['tipo_id'];
		$ruc_cliente = $datos_cliente['ruc'];
		$telefono_cliente = $datos_cliente['telefono'];
		$direccion_cliente = $datos_cliente['direccion'];
		$email_cliente = $datos_cliente['email'];
		$plazo_credito = $datos_cliente['plazo'];
			
//forma de pago
		$busca_forma_pago = "SELECT * FROM formas_pago_ventas fpv, formas_de_pago fdp WHERE fpv.ruc_empresa = '".$ruc_empresa."' and fpv.serie_factura = '".$serie."' and fpv.secuencial_factura = '".$secuencial."' and fpv.id_forma_pago = fdp.codigo_pago and fdp.aplica_a='VENTAS' ";
		$resultado_forma_pago = $con->query($busca_forma_pago);
		$datos_forma_pago = mysqli_fetch_array($resultado_forma_pago);
		$id_forma_pago = $datos_forma_pago['codigo_pago'];
		?>
		<input type="hidden" value="<?php echo $id_forma_pago;?>" name="id_forma_pago" id="id_forma_pago">
		<?php
		$referencia=$serie."-".str_pad($secuencial,9,"000000000",STR_PAD_LEFT);
//datos del cuerpo de la factura pasa al temporal de factura
$query_pasa_datos_factura = mysqli_query($con, "INSERT INTO factura_tmp(id, id_producto, cantidad_tmp, precio_tmp, descuento, tipo_produccion, tarifa_iva, tarifa_ice, tarifa_botellas, id_usuario, id_bodega, id_medida, lote, vencimiento) 
SELECT null,id_producto,cantidad_factura,valor_unitario_factura, descuento,tipo_produccion,tarifa_iva,tarifa_ice,tarifa_bp,'".$id_usuario."',id_bodega,id_medida_salida,lote,vencimiento  FROM cuerpo_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."'");

//para agregar un adicional al temporal de facturas adicional
$query_pasa_detalle_adicional_factura = mysqli_query($con, "INSERT INTO adicional_tmp (id_ad_tmp, id_usuario, serie_factura, secuencial_factura, concepto, detalle) 
SELECT null, '".$id_usuario."','".$serie."','".$secuencial."',adicional_concepto,adicional_descripcion  FROM detalle_adicional_factura WHERE ruc_empresa = '".$ruc_empresa."' and serie_factura = '".$serie."' and secuencial_factura = '".$secuencial."'");
		
//para agregar a tasa temporal y propina los valores
		$agrega_propina_tasa_tmp = mysqli_query($con, "INSERT INTO propina_tasa_tmp VALUES (null, '".$id_usuario."', '".$propina."', '".$tasa_turistica."')");

?>

<body>
	<?php 
	include("../modal/aplicar_descuento.php");
	include("../modal/registro_productos.php");		
	?>
	<div class="container" id="content">
		<div class="panel panel-warning">
		
			<div class="panel-heading">
			<div class="btn-group pull-right">
			<form class="form-group" id="editar_factura_e" name="editar_factura_e" method="POST">
			<span id="mensaje_editar_factura_electronica"></span>
			<button id="editar_datos_factura_e" type="submit" class="btn btn-warning btn-md"><span class='glyphicon glyphicon-floppy-disk'></span> Actualizar</button>
			</div>
			<h4><i class='glyphicon glyphicon-edit'></i> Editar Factura Electrónica</h4>
			</div>
			
			<div class="panel-body">		
				<div id="resultados_ajax"></div>
				<div id="resultados_editar_factura_electronica"></div>
			<div class="well well-sm">
					<div class="form-group row">
								<div class="col-sm-3">
								<div class="input-group" >
								<span class="input-group-addon"><b>Fecha emisión</b></span>								
								<input type="text" class="form-control input-sm" name="fecha_factura_e" id="fecha_factura_e" value="<?php echo date("d-m-Y", strtotime($fecha_factura));?>">
								</div>
								</div>
								<div class="col-md-2">
								<div class="input-group" >
								<span class="input-group-addon"><b>Serie</b></span>
								<input type="text" class="form-control input-sm" id="editar_serie_factura" name="editar_serie_factura"  value="<?php echo $serie; ?>"  readonly>
								</div>
								</div>
								<div class="col-md-3">
								<div class="input-group" >
								<span class="input-group-addon"><b>Secuencial</b></span>								
								<input type="text" class="form-control input-sm" id="secuencial_factura_e" name="secuencial_factura_e"  placeholder="000000001" value="<?php echo $secuencial; ?>"  readonly>
								</div>
								</div>
								  <div class="col-xs-4">
									<div class="input-group" >
									<span class="input-group-addon"><b>Guía Remisión</b></span>
									  <input type="text" class="form-control input-sm" id="guia_factura_e" name="guia_factura_e" placeholder="Guía de remisión" value="<?php echo $guia_remision; ?>"><span class="input-group-btn btn-md"><button class="btn btn-info btn-md" onclick="secuencial_guia_remision()" type="button" title="Agregar guía de remisión"><span class="glyphicon glyphicon-plus"></span></button></span>
									</div>
								  </div>
								<input type="hidden" id="id_cliente_e" name="id_cliente_e" value="<?php echo $id_cliente?>">
								<input type="hidden" id="tipo_id_cliente" name="tipo_id_cliente" value="<?php echo $tipo_id_cliente?>">
								<input type="hidden" id="ruc_cliente" name="ruc_cliente" value="<?php echo $ruc_cliente?>">
								<input type="hidden" id="telefono_cliente" name="telefono_cliente" value="<?php echo $telefono_cliente?>">
								<input type="hidden" id="direccion_cliente" name="direccion_cliente" value="<?php echo $direccion_cliente?>">
								<input type="hidden" id="plazo_credito" name="plazo_credito" value="<?php echo $plazo_credito?>">
								<input type="hidden" id="email_cliente" name="email_cliente" value="<?php echo $email_cliente?>">
								<input type="hidden" id="total_factura_e" name="total_factura_e">
								<input type="hidden" id="muestra_medida" name="muestra_medida">
								<input type="hidden" id="muestra_lote" name="muestra_lote">
								<input type="hidden" id="muestra_bodega" name="muestra_bodega">
								<input type="hidden" id="muestra_vencimiento" name="muestra_vencimiento">
								<input type="hidden" id="propina_final" name="propina_final">
								<input type="hidden" id="tasa_turistica_final" name="tasa_turistica_final">
								<input type="hidden" id="referencia_inventario" value="<?php echo $referencia_inventario?>">
								<input type="hidden" id="inventario" name="inventario">
								
					 </div>
					
					<div class="form-group row">
									  <div class="col-md-6">
									  <div class="input-group" >
									<span class="input-group-addon"><b>Cliente</b></span>
									  <input style="z-index:inherit;" type="text" value="<?php echo $nombre_cliente?>" class="form-control input-sm" id="nombre_cliente_e" name="nombre_cliente_e" placeholder="Agregue un cliente por ruc o nombre" onkeyup='busca_cliente();' autocomplete="off"><span class="input-group-btn btn-md"><button class="btn btn-info btn-md" type="button" title="Agregar cliente" onclick="pasar_datos_cliente()" data-toggle="modal" data-target="#nuevoClienteFactura"><span class="glyphicon glyphicon-pencil"></span></button></span>
									  </div>
									  </div>
								<div class="col-md-6">
								<div class="input-group" >
									<span class="input-group-addon"><b>Forma pago</b></span>
									<select class="form-control input-sm" name="forma_pago_e" id="forma_pago_e">
									<option value="0" >Seleccione forma de pago</option>
									
										<?php
										$conexion = conenta_login();
										$sql = "SELECT * FROM formas_de_pago WHERE aplica_a ='VENTAS' order by nombre_pago asc";
										$res = mysqli_query($conexion,$sql);
										while($o = mysqli_fetch_assoc($res)){
										?>
										<option value="<?php echo $o['codigo_pago']?>"><?php echo $o['nombre_pago']?></option>
										<?php
										}
										?>
									</select>
								</div>
							</div>								
					</div>
			</div>
			<!-- para agregar los productos a la factura -->
			
				<div class="table-responsive">
					<table class="table table-bordered">
						<tr  class="success">
								<th style ="padding: 2px;">Producto/Servicio</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_bodega">Bodega</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_lote">Lote</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_caducidad">Caducidad</th>
								<th style ="padding: 2px;" class="text-center">Cantidad</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_medida">Medida</th>
								<th style ="padding: 2px;" class="text-center">Precio/U.</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_existencia">Existencia</th>
								<th style ="padding: 2px;" class="text-center" >Agregar</th>
						</tr>
								<td class='col-xs-5'>
								<input type="hidden" name="id_producto_agregar" id="id_producto_agregar" >
								<input type="hidden" name="tipo_producto_agregar" id="tipo_producto_agregar" >
								<input type="hidden" name="precio_tmp" id="precio_tmp" >
								<input type="hidden" name="stock_tmp" id="stock_tmp" >
								<div class="input-group">
								<input style="z-index:inherit;" type="text" class="form-control input-sm" title="Buscar producto o servicio." name="producto_agregar" id="producto_agregar" placeholder="Ingrese un producto" onkeyup='buscar_producto();' autocomplete="off">
								<span class="input-group-btn btn-md">
								<button class="btn btn-info btn-md" type="button" title="Nuevo producto o servicio" data-toggle="modal" data-target="#nuevoProducto"><span class="glyphicon glyphicon-pencil"></span></button>
								</span>
								</div>
								</td>
								<td class='col-xs-1' id="lista_bodega">
								  <select class="form-control" style="text-align:right; width: auto;" name="bodega_agregar" id="bodega_agregar" >
									<option value="0" >Seleccione</option>
										<?php
										$conexion = conenta_login();
										$sql = "SELECT * FROM bodega WHERE ruc_empresa ='$ruc_empresa'";
										$res = mysqli_query($conexion,$sql);
										while($o = mysqli_fetch_assoc($res)){
										?>
										<option value="<?php echo $o['id_bodega']?>" selected><?php echo strtoupper($o['nombre_bodega'])?></option>
										<?php
										}
										?>
								</select>
								</td>
								<td class='col-xs-1' id="lista_lote">
								  <select class="form-control" style="text-align:right; width: auto;" title="Seleccione lote." name="lote_agregar" id="lote_agregar" >
								</select>
								</td>
								<td class='col-xs-1' id="lista_caducidad">
								  <select class="form-control" style="text-align:right; width: auto;" title="Seleccione caducidad." name="caducidad_agregar" id="caducidad_agregar" >
								</select>
								</td>
								<td class='col-xs-1'>
								<div class="pull-right">
								  <input type="text" class="form-control input-sm" style="text-align:right" name="cantidad_agregar" id="cantidad_agregar" placeholder="Cantidad">
								</div>
								</td>
								<td class='col-xs-1' id="lista_medida">
									 <select class="form-control" style="text-align:right; width: auto;" title="Seleccione medida." name="medida_agregar" id="medida_agregar" >
									</select>
								</td>
								<td class='col-xs-2'>
									<input type="text" style="text-align:right;" class="form-control input-sm" id="precio_agregar" name="precio_agregar" >
								</td>
								<td class="col-md-1" id="lista_existencia">
								<input type="text" class="form-control input-sm" id="existencia_producto" name="existencia_producto" placeholder="0" readonly>
								</td>
								<td class="col-md-1">
								<div class="text-center">
								<button type="button" class="btn btn-info btn-md" title="Agregar productos a la factura" onclick="agregar_un_item()"><span class="glyphicon glyphicon-plus"></span></button>
								</div>
								</td>	
					</table>
				</div>							
			</form>	
			<div id="resultados" ></div><!-- Carga los datos ajax del detalle de la factura -->		
		</div>
		</div>
	</div>
	
<?php

}else{
header('Location: ../includes/logout.php');
}
?>

</body>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
<style>
.fixedHeight {
        padding: 1px;
		max-height: 200px;
		overflow: auto;
    }
</style>
</html>
<script>
$(document).ready(function(){
	var codigo_pago = document.getElementById('id_forma_pago').value;
	$("#forma_pago_e").val(codigo_pago);
});
//para guardar el nuevo cliente desde la factura
$(function() {
$( "#guardar_cliente_directo" ).submit(function( event ) {
		  $('#guardar_datos').attr("disabled", true);
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: '../ajax/nuevo_cliente_directo.php',
					data: parametros,
					 beforeSend: function(objeto){
						$("#resultados_nuevo_cliente_directo").html("Mensaje: Guardando...");
					  },
					success: function(datos){
					$("#resultados_nuevo_cliente_directo").html(datos);
						var id_cliente_factura = $("#id_cliente_factura").val();
						var nombre_cliente_directo = $("#nombre_cliente_directo").val();
						if (id_cliente_factura>0){
						$("#id_cliente_factura").val(id_cliente_factura);
						$("#nombre_cliente_e").val(nombre_cliente_directo);
						}
					$('#guardar_datos').attr("disabled", false);
				  }  
			});
		  event.preventDefault();
});
});
//para buscar los clientes
function busca_cliente(){
	$("#nombre_cliente_e").autocomplete({
			source:'../ajax/clientes_autocompletar.php',
			minLength: 2,
			select: function(event, ui){
				event.preventDefault();
				$('#id_cliente_e').val(ui.item.id);
				$('#nombre_cliente_e').val(ui.item.nombre);
				$('#tipo_id_cliente').val(ui.item.tipo_id);
				$('#ruc_cliente').val(ui.item.ruc);
				$('#telefono_cliente').val(ui.item.telefono);
				$('#direccion_cliente').val(ui.item.direccion);
				$('#plazo_credito').val(ui.item.plazo);
				$('#email_cliente').val(ui.item.email);
				document.getElementById('forma_pago_e').focus();
				cambia_cliente();
			}
		});

		$("#nombre_cliente_e" ).on( "keydown", function( event ) {
		if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
		{
			$("#id_cliente_e" ).val("");
			$("#nombre_cliente_e" ).val("");
			$("#tipo_id_cliente" ).val("");
			$("#ruc_cliente" ).val("");
			$("#telefono_cliente" ).val("");
			$("#direccion_cliente" ).val("");
			$("#plazo_credito" ).val("");
			$("#email_cliente" ).val("");			
		}
		if (event.keyCode==$.ui.keyCode.DELETE){
			$("#nombre_cliente_e" ).val("");
			$("#id_cliente_e" ).val("");
			$("#tipo_id_cliente" ).val("");
			$("#ruc_cliente" ).val("");
			$("#telefono_cliente" ).val("");
			$("#direccion_cliente" ).val("");
			$("#plazo_credito" ).val("");
			$("#email_cliente" ).val("");			

		}
		});
};

jQuery(function($){
     $("#fecha_factura_e").mask("99-99-9999");
	 $("#guia_factura_e").mask("999-999-999999999");
});


$( function() {
	$("#fecha_factura_e").datepicker({
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

//para buscar productos
function buscar_producto(){
		$("#producto_agregar").autocomplete({
			source: '../ajax/productos_autocompletar.php',
			minLength: 2,
			select: function(event, ui) {
				event.preventDefault();
				$('#id_producto_agregar').val(ui.item.id);
				$('#producto_agregar').val(ui.item.nombre);
				$('#precio_agregar').val(ui.item.precio);
				$('#precio_tmp').val(ui.item.precio);
				$('#tipo_producto_agregar').val(ui.item.tipo);
										
				var tipo_producto = $("#tipo_producto_agregar").val();
				var configuracion_inventario=document.getElementById('inventario').value;
				var configuracion_medida=document.getElementById('muestra_medida').value;
				var configuracion_lote=document.getElementById('muestra_lote').value;
				var configuracion_bodega=document.getElementById('muestra_bodega').value;
				var configuracion_vencimiento=document.getElementById('muestra_vencimiento').value;
				var producto = $("#id_producto_agregar").val();			
				//para traer todos los precios que esten dentro de la fecha permitida
				//$.post( '../ajax/select_opciones_inventario.php', {opcion:'precios', id_producto: producto}).done( function( res_precios ){
				//	$("#select_precio").html(res_precios);
				//});
								
				if (tipo_producto=="02"){
				document.getElementById("titulo_bodega").style.display="none";
				document.getElementById("titulo_lote").style.display="none";
				document.getElementById("titulo_caducidad").style.display="none";
				document.getElementById("titulo_medida").style.display="none";
				document.getElementById("titulo_existencia").style.display="none";
				document.getElementById("lista_bodega").style.display="none";
				document.getElementById("lista_lote").style.display="none";
				document.getElementById("lista_caducidad").style.display="none";
				document.getElementById("lista_medida").style.display="none";
				document.getElementById("lista_existencia").style.display="none";
				}
								
				if (tipo_producto=="01" && (configuracion_inventario =='NO' || configuracion_inventario =='')){
					document.getElementById("titulo_bodega").style.display="none";
					document.getElementById("titulo_lote").style.display="none";
					document.getElementById("titulo_caducidad").style.display="none";
					document.getElementById("titulo_medida").style.display="";
					document.getElementById("lista_bodega").style.display="none";
					document.getElementById("lista_lote").style.display="none";
					document.getElementById("lista_caducidad").style.display="none";
					document.getElementById("lista_medida").style.display="";
					
					var producto = $("#id_producto_agregar").val();
				
				//cuando trae se busca el producto me trae que tipo de medida tiene
						$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_tipos_medidas ){
							$("#medida_agregar").html(res_tipos_medidas);
						});
					}
				
				
				if (tipo_producto=="01" && configuracion_inventario =='SI'){
				if(configuracion_medida == "SI"){
					document.getElementById("titulo_medida").style.display="";
					document.getElementById("lista_medida").style.display="";
				}
				if(configuracion_lote=='SI'){
					document.getElementById("titulo_lote").style.display="";
					document.getElementById("lista_lote").style.display="";
				}
				if(configuracion_bodega=='SI'){
					document.getElementById("titulo_bodega").style.display="";
					document.getElementById("lista_bodega").style.display="";
				}
				if(configuracion_vencimiento=='SI'){
					document.getElementById("titulo_caducidad").style.display="";
					document.getElementById("lista_caducidad").style.display="";
				}
								
				document.getElementById("precio_agregar").disabled = false;
				document.getElementById("existencia_producto").disabled = true;
				
					$("#existencia_producto" ).val("0");
					var bodega = $("#bodega_agregar").val();
					var producto = $("#id_producto_agregar").val();
					var editar_factura = $("#referencia_inventario").val();
				
				//cuando trae se busca el producto me trae que tipo de medida tiene
						$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_tipos_medidas ){
							$("#medida_agregar").html(res_tipos_medidas);
						});	

				//para que se cargue el stock del producto al momento de buscar el producto
					$.post( '../ajax/saldo_producto_inventario.php', {id_bodega: bodega, id_producto: producto}).done( function( respuesta ){
						var saldo_producto = respuesta;
						$("#existencia_producto").val(saldo_producto);
						$('#stock_tmp').val(saldo_producto);
					
						$.post( '../ajax/saldo_producto_inventario.php', {id_bode: bodega, id_prod: producto, editar_factura: editar_factura}).done( function( respuesta_salidas ){
							var saldo_salidas = parseFloat(respuesta_salidas) + parseFloat(saldo_producto);
							$("#existencia_producto").val(saldo_salidas);
							$('#stock_tmp').val(saldo_salidas);
						});

					});
				//para traer todos los lotes en base a una bodega al momento de buscar un producto
				$.post( '../ajax/select_opciones_inventario.php', {opcion:'lote', id_producto: producto, bodega: bodega}).done( function( res_opciones_lote ){
					$("#lote_agregar").html(res_opciones_lote);
				});

				//para traer todos las caducidades en base a una bodega al momento de buscar un producto
				$.post( '../ajax/select_opciones_inventario.php', {opcion:'caducidad', id_producto: producto, bodega: bodega}).done( function( res_opciones_caducidad ){
							$("#caducidad_agregar").html(res_opciones_caducidad);
						});											
											
					document.getElementById("titulo_existencia").style.display="";
					document.getElementById("lista_existencia").style.display="";	

				}
			document.getElementById('cantidad_agregar').focus();				
			}
		});
						
			$( "#producto_agregar" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
						
				$("#producto_agregar" ).on( "keydown", function( event ) {
					if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
					{
						$("#id_producto_agregar" ).val("");
						$("#producto_agregar" ).val("");
						$("#precio_agregar" ).val("");
						$("#tipo_producto_agregar" ).val("");
						$("#existencia_producto" ).val("");
						$("#stock_tmp" ).val("");						
					}			
			});
			
}

//para mostrar al momento de iniciar la edicion de la factura
$(document).ready(function(){
		document.getElementById("titulo_bodega").style.display="none";
		document.getElementById("titulo_lote").style.display="none";
		document.getElementById("titulo_caducidad").style.display="none";
		document.getElementById("titulo_medida").style.display="none";
		document.getElementById("titulo_existencia").style.display="none";
		document.getElementById("lista_bodega").style.display="none";
		document.getElementById("lista_lote").style.display="none";
		document.getElementById("lista_caducidad").style.display="none";
		document.getElementById("lista_medida").style.display="none";
		document.getElementById("lista_existencia").style.display="none";

	var editar_serie_factura = $("#editar_serie_factura").val();
	var id_cliente_e = $("#id_cliente_e").val();
	var secuencial_factura_e = $("#secuencial_factura_e").val();
		$.ajax({
         type: "POST",
         url: "../ajax/agregar_facturacion.php",
       data: "serie_factura_e="+editar_serie_factura+"&secuencial_factura="+secuencial_factura_e+"&id_cliente="+id_cliente_e,
		 beforeSend: function(objeto){
			$("#resultados").html("Cargando...");
		  },
			success: function(datos){
			$("#resultados").html(datos);
			document.getElementById('producto_agregar').focus();
			}
			});
	//para traer el tipo de configuracion de inventarios, si o no
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'inventario',serie_consultada:editar_serie_factura}).done( function(respuesta_inventario)
		{		
			var resultado_inventario = $.trim(respuesta_inventario);
			$('#inventario').val(resultado_inventario);
		});
		
		//para traer y ver si trabaja con medida
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'medida',serie_consultada:editar_serie_factura}).done( function(respuesta_medida)
		{		
			var resultado_medida = $.trim(respuesta_medida);
			$('#muestra_medida').val(resultado_medida);
		});
		
		//para traer y ver si trabaja con lote
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'lote',serie_consultada:editar_serie_factura}).done( function(respuesta_lote)
		{		
			var resultado_lote = $.trim(respuesta_lote);
			$('#muestra_lote').val(resultado_lote);
		});
		
		//para traer y ver si trabaja con bodega
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'bodega',serie_consultada:editar_serie_factura}).done( function(respuesta_bodega)
		{		
			var resultado_bodega = $.trim(respuesta_bodega);
			$('#muestra_bodega').val(resultado_bodega);
		});
		
		//para traer y ver si trabaja con vencimiento
		$.post( '../ajax/consulta_configuracion_facturacion.php', {opcion_mostrar:'vencimiento',serie_consultada:editar_serie_factura}).done( function(respuesta_vencimiento)
		{		
			var resultado_vencimiento = $.trim(respuesta_vencimiento);
			$('#muestra_vencimiento').val(resultado_vencimiento);
		});
		
		document.getElementById('nombre_cliente_e').focus();
});

//para cuando se selecciona una bodega que me cargue el saldo de ese producto
$( function(){

	$('#bodega_agregar').change(function(){
		var bodega = $("#bodega_agregar").val();
		var producto = $("#id_producto_agregar").val();
		var id_medida = $("#medida_agregar").val();
		var editar_factura = $("#referencia_inventario").val();
		
			$.post( '../ajax/saldo_producto_inventario.php', {id_bodega: bodega, id_producto: producto}).done( function( respuesta ){
			var saldo_producto = respuesta;
			$("#existencia_producto").val(saldo_producto);
			$('#stock_tmp').val(saldo_producto);			
		
			$.post( '../ajax/saldo_producto_inventario.php', {id_bode: bodega, id_prod: producto, editar_factura: editar_factura}).done( function( respuesta_salidas ){
				var saldo_salidas = parseFloat(respuesta_salidas) + parseFloat(saldo_producto);
				$("#existencia_producto").val(saldo_salidas);
				$('#stock_tmp').val(saldo_salidas);
			});
			});
			
			//reinicia la medida
			$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
				$("#medida_agregar").html(res_id_medidas);
			});
			//reinicie el precio
			var precio_venta = $("#precio_tmp").val();
			$("#precio_agregar").val(precio_venta);
			
			//trae la existencia en base a la bodega
			$.post( '../ajax/saldo_producto_inventario.php', {id_bodega: bodega, id_producto: producto}).done( function( respuesta ){
			var saldo_producto = respuesta;
			$("#existencia_producto").val(saldo_producto);
			$('#stock_tmp').val(saldo_producto);			
			});
			
			//reinicio el lote
			$.post( '../ajax/select_opciones_inventario.php', {opcion:'lote', id_producto: producto, bodega: bodega}).done( function( res_opciones_lote ){
				$("#lote_agregar").html(res_opciones_lote);
			});

			//para reinicie vencimiento
			$.post( '../ajax/select_opciones_inventario.php', {opcion:'caducidad', id_producto: producto, bodega: bodega}).done( function( res_opciones_caducidad ){
				$("#caducidad_agregar").html(res_opciones_caducidad);
			});	
			
			
	});
	
	//para cuando se cambie la medida cambie el saldo
	$('#medida_agregar').change(function(){
		var serie = $("#editar_serie_factura").val();
		var id_medida = $("#medida_agregar").val();
		var id_producto = $("#id_producto_agregar").val();
		var precio_venta = $("#precio_tmp").val();//este precio es para que no se me cambie del precio que calculo cada vez que cambio el selec de medida
		var stock_tmp = $("#stock_tmp").val();
		var editar_factura = $("#referencia_inventario").val();		
		
		$.post( '../ajax/saldo_producto_inventario.php', {id_medida_seleccionada: id_medida, id_producto: id_producto, precio_venta: precio_venta, stock_tmp: stock_tmp, dato_obtener:'saldo' }).done( function( respuesta_saldo ){
					$("#existencia_producto").val(respuesta_saldo);
					
					$.post( '../ajax/saldo_producto_inventario.php', {id_bode: bodega, id_prod: producto, editar_factura: editar_factura}).done( function( respuesta_salidas ){
					var saldo_salidas = parseFloat(respuesta_salidas) + parseFloat(respuesta_saldo);
					$("#existencia_producto").val(saldo_salidas);
				});	
		});
		
		$.post( '../ajax/saldo_producto_inventario.php', {id_medida_seleccionada: id_medida, id_producto: id_producto, precio_venta: precio_venta, stock_tmp: stock_tmp, dato_obtener:'precio' }).done( function( respuesta_precio ){
					$("#precio_agregar").val(respuesta_precio);
		});	
	});
	
	//para traer el valor de conversion de medidas en el producto cuando se cambia el select de lote
	$('#lote_agregar').change(function(){	
		var lote = $("#lote_agregar").val();
		var producto = $("#id_producto_agregar").val();
		var bodega = $("#bodega_agregar").val();
	
		$.post( '../ajax/saldo_producto_inventario.php', {opcion_lote: lote, id_producto: producto, bodega: bodega }).done( function( respuesta_lote ){
					$("#existencia_producto").val(respuesta_lote);
		});
		
		//reinicia la medida
			$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
				$("#medida_agregar").html(res_id_medidas);
			});
			//reinicie el precio
			var precio_venta = $("#precio_tmp").val();
			$("#precio_agregar").val(precio_venta);
			
			//para reinicie vencimiento
			$.post( '../ajax/select_opciones_inventario.php', {opcion:'caducidad', id_producto: producto, bodega: bodega}).done( function( res_opciones_caducidad ){
				$("#caducidad_agregar").html(res_opciones_caducidad);
			});	
		
	});
	
	//para traer el valor de conversion de medidas en el producto cuando se cambia el select de caducidad
	$('#caducidad_agregar').change(function(){	
		var caducidad = $("#caducidad_agregar").val();
		var producto = $("#id_producto_agregar").val();
	
		$.post( '../ajax/saldo_producto_inventario.php', {opcion_caducidad: caducidad, id_producto: producto }).done( function( respuesta_caducidad ){
					$("#existencia_producto").val(respuesta_caducidad);
		});
		
		//reinicia la medida
			$.post( '../ajax/select_tipo_medida.php', {id_producto: producto}).done( function( res_id_medidas ){
				$("#medida_agregar").html(res_id_medidas);
			});
			//reinicie el precio
			var precio_venta = $("#precio_tmp").val();
			$("#precio_agregar").val(precio_venta);
		
	});

});

//eliminar iten de la factura
function eliminar_fila (id){
			var id_cliente = $("#id_cliente_e").val();
			var serie_factura = $("#editar_serie_factura").val();
			var secuencial_factura = $("#secuencial_factura_e").val();
			$.ajax({
        type: "POST",
        url: "../ajax/agregar_facturacion.php",
        data: "id="+id+"&id_cliente="+id_cliente+"&serie_factura_e="+serie_factura+"&secuencial_factura="+secuencial_factura,
		 beforeSend: function(objeto){
			$("#resultados").html("Mensaje: Cargando...");
		  },
        success: function(datos){
		$("#resultados").html(datos);
		}
			});

	};

//para editar la factura electronica
$(function() {
$( "#editar_factura_e" ).submit(function( event ) {
	$('#editar_datos_factura_e').attr("disabled", true);
		//para pasar el total de la factura de un text a otro text
		var total_factura = $("#suma_factura").val();
		var propina = $("#propina").val();
		var tasa_turistica = $("#tasa_turistica").val();
		$("#total_factura_e").val(total_factura);
		$("#propina_final").val(propina);
		$("#tasa_turistica_final").val(tasa_turistica);
		
		//de aqui para abajo para guardar la factura
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: '../ajax/editar_factura_electronica.php',
					data: parametros,
					 beforeSend: function(objeto){
						$("#mensaje_editar_factura_electronica").html("Actualizando...");
					  },
					success: function(datos){
					$("#resultados_editar_factura_electronica").html(datos);
					$("#mensaje_editar_factura_electronica").html("");
					$('#editar_datos_factura_e').attr("disabled", false);
				  }  
			});
		  event.preventDefault();
});
});

function aplicar_descuento_directo(id){
	var id_cliente = $("#id_cliente_e").val();
	var serie_factura = $("#editar_serie_factura").val();
	var secuencial_factura = $("#secuencial_factura_e").val();
	var subtotal_item = $("#subtotal_item"+id).val();
	var valor_descuento = $("#descuento"+id).val();
	var descuento_item = $("#descuento_item"+id).val();
	
	
	if (isNaN(valor_descuento)){
	alert('El dato ingresado en descuento, no es un número');
	document.getElementById('descuento'+id).focus();
	$("#descuento"+id).val(descuento_item);
	return false;
	}
	
	if ((valor_descuento<0)){
	alert('El descuento, debe ser mayor a cero');
	$("#descuento"+id).val(descuento_item);
	document.getElementById('descuento'+id).focus();
	return false;
	}
	
	if ((parseFloat(valor_descuento) > parseFloat(subtotal_item))){
	alert('El descuento es mayor al subtotal del item');
	$("#descuento"+id).val(descuento_item);
	document.getElementById('descuento'+id).focus();
	return false;
	}
	
	$.ajax({
		 type: "POST",
		 url: "../ajax/agregar_facturacion.php",
		 data: "id_tmp_descuento="+id+"&valor_descuento="+valor_descuento+"&id_cliente="+id_cliente+"&serie_factura_e="+serie_factura+"&secuencial_factura="+secuencial_factura,
		 beforeSend: function(objeto){
			$("#resultados").html("Cargando...");
		  },
			success: function(datos){
			$("#resultados").html(datos);
			}
		});
}

function aplicar_descuento_todos(){
	var porcentaje_descuento = $("#porcentaje_descuento").val();
	var id_cliente = $("#id_cliente_e").val();
	var serie_factura = $("#editar_serie_factura").val();
	var secuencial_factura = $("#secuencial_factura_e").val();

	if (isNaN(porcentaje_descuento)){
	alert('El dato ingresado en porcentaje, no es un número');
	document.getElementById('porcentaje_descuento').focus();
	return false;
	}
	
	if ((porcentaje_descuento<0)){
	alert('El porcentaje, debe ser mayor a cero');
	document.getElementById('porcentaje_descuento').focus();
	return false;
	}
		
	$.ajax({
		 type: "POST",
		 url: "../ajax/agregar_facturacion.php",
		 data: "aplicar_descuento_todos=aplicar_descuento_todos&id_cliente="+id_cliente+"&serie_factura_e="+serie_factura+"&secuencial_factura="+secuencial_factura+"&porcentaje_descuento="+porcentaje_descuento,
		 beforeSend: function(objeto){
			$("#resultados").html("Cargando...");
		  },
			success: function(datos){
			$("#resultados").html(datos);
			}
		});
}


//para pasar el id de descuento al modal de aplicar descuento
function pasa_descuento(id_tmp, subtotal, serie_factura, secuencial_factura, id_cliente, descuento){ 
			$("#id_tmp_descuento").val(id_tmp);
			$("#valor_subtotal").val(subtotal);
			$("#serie_factura_descuento").val(serie_factura);
			$("#secuencial_factura_descuento").val(secuencial_factura);
			$("#id_cliente_descuento").val(id_cliente);
			$("#valor_descuento").val(descuento);
};

//REGISTRA EL DESCUENTO
$(function() {
$( "#guardar_descuento" ).submit(function( event ) {//viene del boton aplicar descuento
	 $('#aplicar_descuento').attr("disabled", true);
	 var parametros = $(this).serialize();
	 
			var valor_descuento = $("#valor_descuento").val();
			var valor_subtotal = $("#valor_subtotal").val();
			//Inicia validacion
			if (isNaN(valor_descuento)){
			alert('El dato ingresado, no es un número');
			document.getElementById('valor_descuento').focus();
			$('#aplicar_descuento').attr("disabled", false);
			return false;
			}
			if ((Number(valor_descuento)) > (Number(valor_subtotal))){
			alert('El descuento ingresado, es mayor que el subtotal');
			document.getElementById('valor_descuento').focus();
			$('#aplicar_descuento').attr("disabled", false);
			return false;
			}	
			if ((Number(valor_descuento)) < 0) {
			alert('El descuento ingresado, debe ser mayor o igual a cero');
			document.getElementById('valor_descuento').focus();
			$('#aplicar_descuento').attr("disabled", false);
			return false;
			}				
			//Fin validacion
			$.ajax({
         type: "POST",
         url: "../ajax/agregar_facturacion.php",
         data: parametros,
		 beforeSend: function(objeto){
			$("#resultados").html("Cargando...");
		  },
			success: function(datos){
			$("#resultados").html(datos);
			$('#aplicar_descuento').attr("disabled", false);
				load(1);
			}
			});
		 event.preventDefault();
		 $('#aplicarDescuento').modal('hide');
		});
});

//agrega un item al cuerpo de la factura
function agregar_un_item(){
			var id_cliente_e = $("#id_cliente_e").val();
			var serie_factura_e = $("#editar_serie_factura").val();
			var secuencial_factura_e = $("#secuencial_factura_e").val();
			var id_producto_agregar = $("#id_producto_agregar").val();
			var tipo_producto_agregar = $("#tipo_producto_agregar").val();	
			var cantidad=document.getElementById('cantidad_agregar').value;
			var lote_agregar=document.getElementById('lote_agregar').value;
			var caducidad_agregar=document.getElementById('caducidad_agregar').value;
			var medida_agregar=document.getElementById('medida_agregar').value;
			var precio_venta=document.getElementById('precio_agregar').value;
			var bodega_agregar=document.getElementById('bodega_agregar').value;
			var existencia_producto=document.getElementById('existencia_producto').value;
			var configuracion_inventario=document.getElementById('inventario').value;
			
			var control_bodega=document.getElementById('muestra_bodega').value;
			var control_lote=document.getElementById('muestra_lote').value;
			var control_caducidad=document.getElementById('muestra_vencimiento').value;
			
			//Inicia validacion
			if (id_cliente_e==""){
			alert('Seleccione un cliente');
			document.getElementById('id_cliente_e').focus();
			return false;
			}			
			if (id_producto_agregar==""){
			alert('Seleccione un producto o servicio');
			document.getElementById('id_producto_agregar').focus();
			return false;
			}
			if (cantidad==""){
			alert('Ingrese cantidad');
			document.getElementById('cantidad_agregar').focus();
			return false;
			}
			if (isNaN(cantidad)){
			alert('El dato ingresado en cantidad, no es un número');
			document.getElementById('cantidad_agregar').focus();
			return false;
			}
			if (isNaN(precio_venta)){
			alert('El dato ingresado en precio, no es un número');
			document.getElementById('precio_agregar').focus();
			return false;
			}
			if (configuracion_inventario =='SI' && tipo_producto_agregar=='01'&& control_bodega=='SI' && bodega_agregar=='0'){
			alert('Seleccione una bodega');
			document.getElementById('bodega_agregar').focus();
			return false;
			}
			
			if (configuracion_inventario =='SI' && tipo_producto_agregar=='01' && control_lote=='SI' && lote_agregar=='0' ){
			alert('Seleccione un lote');
			document.getElementById('lote_agregar').focus();
			return false;
			}
			
			if (configuracion_inventario =='SI' && tipo_producto_agregar=='01' && control_caducidad=='SI' && caducidad_agregar=='0' ){
			alert('Seleccione fecha de vencimiento');
			document.getElementById('caducidad_agregar').focus();
			return false;
			}

			if (parseFloat(cantidad) > parseFloat(existencia_producto) && configuracion_inventario =='SI' && tipo_producto_agregar=='01'){
			alert('El saldo en inventarios es menor a la cantidad a facturar ');
			document.getElementById('cantidad_agregar').focus();
			return false;
			}
			
			//Fin validacion
			$.ajax({
         type: "POST",
         url: "../ajax/agregar_facturacion.php",
         data: "id_producto="+id_producto_agregar+"&precio_venta="+precio_venta+"&cantidad="+cantidad+"&serie_factura_e="+serie_factura_e+"&bodega_agregar="+bodega_agregar+"&id_cliente="+id_cliente_e+"&secuencial_factura="+secuencial_factura_e+"&medida_agregar="+medida_agregar+"&lote_agregar="+lote_agregar+"&caducidad_agregar="+caducidad_agregar,
		 beforeSend: function(objeto){
			$("#resultados").html("Cargando...");
		  },
			success: function(datos){
			$("#resultados").html(datos);
			$("#producto_agregar" ).val("");
			$("#id_producto_agregar" ).val("");
			$("#precio_agregar" ).val("");
			$("#tipo_producto_agregar" ).val("");
			$("#existencia_producto" ).val("0");
			$("#cantidad_agregar" ).val("1");
			document.getElementById('producto_agregar').focus();
			}
			});
		
};

//para agregar informacion adicional a la factura
function agregar_info_adicional(){
			var id_cliente = $("#id_cliente_e").val();
			var serie_factura_e = $("#editar_serie_factura").val();
			var secuencial_factura_e = $("#secuencial_factura_e").val();
			var adicional_concepto= $("#adicional_concepto").val();
			var adicional_descripcion= $("#adicional_descripcion").val();

			//Inicia validacion
			if (adicional_concepto ==''){
			alert('Ingrese un concepto');
			document.getElementById('adicional_concepto').focus();
			return false;
			}
			if (adicional_descripcion ==''){
			alert('Ingrese detalle');
			document.getElementById('adicional_descripcion').focus();
			return false;
			}
			
			//Fin validacion
			 $.ajax({
				 type: "POST",
					url: "../ajax/agregar_facturacion.php",
					data: "agregar_adicional=agregar_adicional&serie_factura_e="+serie_factura_e+"&secuencial_factura="+secuencial_factura_e+"&adicional_concepto="+adicional_concepto+"&adicional_descripcion="+adicional_descripcion+"&id_cliente="+id_cliente,
					 beforeSend: function(objeto){
						$("#resultados").html("Cargando...");
					  },
					success: function(datos){
						$("#resultados").html(datos);
						document.getElementById("adicional_concepto").value = "";
						document.getElementById("adicional_descripcion").value = "";
				  }
			});
};

//para eliminar cada detalle adicional de la factura a guardarse
function eliminar_detalle_info_adicional(id_info_adicional){
			var id_cliente = $("#id_cliente_adicional").val();
			var serie_factura = $("#serie_adicional").val();
			var secuencial_factura = $("#secuencial_adicional").val();

			 $.ajax({
					type: "POST",
					url: "../ajax/agregar_facturacion.php",
					data:"id_adicional="+id_info_adicional+"&serie_factura_e="+serie_factura+"&secuencial_factura="+secuencial_factura+"&id_cliente="+id_cliente,	
					beforeSend: function(objeto){
						$("#resultados").html("Cargando detalle...");
					  },
						success: function(datos){
						$("#resultados").html(datos);
					}
			});	
};

//para mostrar la guia que continua segun la serie seleccionada
function secuencial_guia_remision(){
		var id_serie = $("#editar_serie_factura").val();	
			$.post( '../ajax/buscar_ultima_gr.php', {serie_gr: id_serie}).done( function( respuesta )
		{
			var numero = String("000000000"+respuesta).slice(-9);
			var guia_final = id_serie+'-'+numero;
			$("#guia_factura_e").val(guia_final);
			$("#guia_factura_e").mask("999-999-999999999");			
		});

};

//para aplicar propina y tasa
function aplica_propina_tasa(){
			var propina = $("#propina").val();
			var tasa = $("#tasa_turistica").val();
			var id_cliente = $("#id_cliente_e").val();
			var serie_factura_e = $("#editar_serie_factura").val();
			var secuencial_factura_e = $("#secuencial_factura_e").val();
			var adicional_concepto= $("#adicional_concepto").val();
			var adicional_descripcion= $("#adicional_descripcion").val();
			var $elem = $('#content');
			//Inicia validacion
			
			if (propina <0){
			alert('Ingrese valor positivo en propina');
			document.getElementById('propina').focus();
			return false;
			}
			
			if (tasa_turistica <0){
			alert('Ingrese valor positivo en tasa turistica');
			document.getElementById('tasa_turistica').focus();
			return false;
			}
			
			//Fin validacion
			 $.ajax({
				 type: "POST",
					url: "../ajax/agregar_facturacion.php",
					data: "agregar_propina_tasa=agregar_propina_tasa&serie_factura_e="+serie_factura_e+"&secuencial_factura="+secuencial_factura_e+"&adicional_concepto="+adicional_concepto+"&adicional_descripcion="+adicional_descripcion+"&id_cliente="+id_cliente+"&propina="+propina+"&tasa="+tasa,
					 beforeSend: function(objeto){
						$("#resultados").html("Cargando...");
					  },
					success: function(datos){
						$("#resultados").html(datos);
				  }
			});
			$('body').animate({scrollTop: $elem.height()}, 500);
};

//para calcular automaticamente la propina
function calcular_propina(){
		var propina_calculada = $("#propina_calculada").val();
		$("#propina").val(propina_calculada);
		var propina = $("#propina").val();
		var tasa = $("#tasa_turistica").val();
		var id_cliente = $("#id_cliente_e").val();
		var serie_factura_e = $("#editar_serie_factura").val();
		var secuencial_factura_e = $("#secuencial_factura_e").val();
		var adicional_concepto= $("#adicional_concepto").val();
		var adicional_descripcion= $("#adicional_descripcion").val();
		var $elem = $('#content');
		 $.ajax({
			 type: "POST",
				url: "../ajax/agregar_facturacion.php",
				data: "agregar_propina_tasa=agregar_propina_tasa&serie_factura_e="+serie_factura_e+"&secuencial_factura="+secuencial_factura_e+"&adicional_concepto="+adicional_concepto+"&adicional_descripcion="+adicional_descripcion+"&id_cliente="+id_cliente+"&propina="+propina+"&tasa="+tasa,
				 beforeSend: function(objeto){
					$("#resultados").html("Cargando...");
				  },
				success: function(datos){
					$("#resultados").html(datos);
			  }
		});
		$('body').animate({scrollTop: $elem.height()}, 500);
};

//para pasar los datos del cliente y editar directamente
function pasar_datos_cliente(){
		var id_cliente_e = $("#id_cliente_e").val();
		var nombre_cliente_e = $("#nombre_cliente_e").val();
		var tipo_id_cliente = $("#tipo_id_cliente").val();
		var ruc_cliente = $("#ruc_cliente").val();
		var telefono_cliente = $("#telefono_cliente").val();
		var direccion_cliente = $("#direccion_cliente").val();
		var plazo_credito = $("#plazo_credito").val();
		var email_cliente = $("#email_cliente").val();
		$("#id_cliente_factura").val(id_cliente_e);
		$("#tipo_id_cliente_modi").val(tipo_id_cliente);
		$("#ruc_cliente_directo").val(ruc_cliente);
		$("#nombre_cliente_directo").val(nombre_cliente_e);
		$("#telefono_cliente_directo").val(telefono_cliente);
		$("#direccion_cliente_directo").val(direccion_cliente);
		$("#plazo_cliente_directo").val(plazo_credito);
		$("#email_cliente_directo").val(email_cliente);
		//document.getElementById("tipo_id_cliente").disabled = true;
		document.getElementById("ruc_cliente_directo").readOnly = true;

	}
	
	//borrar datos del formulario de nuevo cliente
$("#borrar_datos").click(function(){
	$("#id_cliente_factura" ).val("");
	$("#ruc_cliente_directo" ).val("");
	$("#nombre_cliente_directo" ).val("");
	$("#telefono_cliente_directo" ).val("");
	$("#direccion_cliente_directo" ).val("");
	$("#plazo_cliente_directo" ).val("5");
	$("#email_cliente_directo" ).val("");
	$("#id_cliente_e" ).val("");
	$("#nombre_cliente_e" ).val("");
	//document.getElementById("tipo_id_cliente").disabled = false;
	document.getElementById("ruc_cliente_directo").readOnly = false;
    });
	
	
	//al cambiar de cliente debe actualizarse los datos del mismo en adicionales
function cambia_cliente(){
			var $elem = $('#content');
			var id_cliente = $("#id_cliente_e").val();
			var serie_factura_e = $("#editar_serie_factura").val();
			var secuencial_factura_e = $("#secuencial_factura_e").val();
			
			//Fin validacion
			 $.ajax({
				 type: "POST",
					url: "../ajax/agregar_facturacion.php",
					data: "cambia_cliente=cambia_cliente&serie_factura_e="+serie_factura_e+"&secuencial_factura="+secuencial_factura_e+"&id_cliente="+id_cliente,
					 beforeSend: function(objeto){
						$("#resultados").html("Cargando...");
					  },
					success: function(datos){
						$("#resultados").html(datos);
						document.getElementById("adicional_concepto").value = "";
						document.getElementById("adicional_descripcion").value = "";
				  }
			});
			//$('body').animate({scrollTop: $elem.height()}, 500);
};


//para guardar el producto directamente desde la factura
$( "#guardar_producto" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/nuevo_producto.php",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax_productos").html("Mensaje: Cargando...");
			  },
			success: function(datos){
			$("#resultados_ajax_productos").html(datos);
			$('#guardar_datos').attr("disabled", false);
		  }
	});
  event.preventDefault();
})

//para que cuando se cierre el modal de nuevo producto
$("#cerrar_nuevo_producto").click(function(){
	$("#resultados_ajax_productos").empty();
	document.getElementById("guardar_producto").reset();
    });	
</script>



