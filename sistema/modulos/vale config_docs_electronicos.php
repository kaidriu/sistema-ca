<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
<title>Datos emisor</title>
</head>

<body>
<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

include("../paginas/menu_de_empresas.php"); 	
$con = conenta_login();
//para buscar la empresa
$busca_empresa = "SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."'";
$resultado_de_la_busqueda = $con->query($busca_empresa);
$row=mysqli_fetch_array($resultado_de_la_busqueda);
$id_empresa = $row['id'];
$razon_social = $row['nombre'];

//para buscar los datos de facturacion electronica
$busca_info_fe = "SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ";
$resultado_de_la_busqueda = $con->query($busca_info_fe);
$info_fe=mysqli_fetch_array($resultado_de_la_busqueda);
$puerto_correo = $info_fe['correo_port'];
$correo_asunto = $info_fe['correo_asunto'];
$correo_host = $info_fe['correo_host'];
$correo_remitente = $info_fe['correo_remitente'];
$ssl_hab = $info_fe['ssl_hab'];
$tipo_ambiente = $info_fe['tipo_ambiente'];
$tipo_emision = $info_fe['tipo_emision'];
$resol_ce = $info_fe['resol_cont'];
$fecha_vence = date("d-m-Y", strtotime($info_fe['fecha_fin_firma']));
$archivo_firma = $info_fe['archivo_firma'];
$agente_ret = $info_fe['agente_ret'];
$regimen_micro = $info_fe['regimen_micro'];

//para buscar los logos de cada sucursal
$busca_info_sucursales=mysqli_query($con, "SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."'" );
?>

	<div class="container-fluid">
	<div class="col-md-8 col-md-offset-2">
		<div class="panel panel-info">
		<div class="panel-heading">
			<h4><i class='glyphicon glyphicon-pencil'></i> Configuración del emisor en facturación electrónica</h4>
		</div>			
	<div class="panel-body">
	<div class="panel-group" id="accordion">
    <div class="panel panel-success">
		<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse1" ><span class="caret"></span> Configuración del contribuyente emisor de documentos electrónicos</a>
      <div id="collapse1" class="panel-collapse collapse">	
			<form class="form-horizontal" method="POST" id="configura_emisor" name="configura_emisor" enctype="multipart/form-data">
					  <div class="panel-body">
							<div class="form-group">
								<div class="col-sm-12">	
								 <div class="input-group">
									  <span class="input-group-addon"><b>Razón Social</b></span>
										<input type="text" class="form-control" name="razon_social" value= "<?php echo $razon_social; ?>" readonly >  
								  </div>
								  <input type="hidden" name="ruc_empresa" value= "<?php echo $ruc_empresa; ?>" readonly>
								</div>
							 </div>
							 <div class="form-group">
								<div class="col-sm-6">
								 <div class="input-group">
									  <span class="input-group-addon"><b>Contribuyente Especial</b></span>
										<input type="text" class="form-control" name="resol_ce" value= "<?php echo $resol_ce; ?>"  placeholder="Resolución" title="Número de resolución de contribuyente especial">
								  </div>
								</div>
								<div class="col-sm-6">
								<div class="input-group">
									  <span class="input-group-addon"><b>SSL Habilitado</b></span>
										<input type="text" class="form-control" name="ssl" value= "false"  readonly>
								  </div>
								</div>
							 </div>
							 <div class="form-group">
								<div class="col-sm-6">
								 <div class="input-group">
									  <span class="input-group-addon"><b>Agente de retención</b></span>
										<input type="text" class="form-control" name="agente_retencion" value= "<?php echo $agente_ret; ?>"  placeholder="Resolución" title="Número de resolución de agente de retención">
								  </div>
								</div>
								<div class="col-sm-6">
								<div class="input-group">
									  <span class="input-group-addon"><b>Contribuyente Régimen Microempresas</b></span>
										<select class="form-control" name="regimen_micro" required>
										<?php
										if ($regimen_micro=="SI"){
											?>
											<option value="SI"Selected>SI</option>
												<option value="NO">NO</option>
											<?php
										}else{
											?>
												<option value="SI">SI</option>
												<option value="NO"Selected>NO</option>
											<?php
										}
										?>		
												
										</select>
								  </div>
								</div>
							 </div>
							<div class="form-group">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b>Tipo ambiente</b></span>
										<select class="form-control" name="tipo_ambiente" required>
										<?php
										if ($tipo_ambiente==1){
											?>
											<option value="1" selected>Pruebas</option>
											<option value="2">Producción</option>
											<?php
										}else{
											?>
											<option value="1">Pruebas</option>
											<option value="2"selected>Producción</option>
											<?php
										}
										?>				
										</select>
								</div>
							</div>
								<div class="col-sm-6">
									<div class="input-group">
										<span class="input-group-addon"><b>Tipo emisión</b></span>
										<select class="form-control" name="tipo_emision" required>
										<?php
										if ($tipo_emision==1){
											?>
											<option value="1"Selected>Normal</option>
												<option value="2">Por Indisponibilidad del sistema</option>
											<?php
										}else{
											?>
											<option value="1">Normal</option>
												<option value="2"Selected>Por Indisponibilidad del sistema</option>
											<?php
										}
										?>		
												
										</select>
									</div> 
								</div>
							 </div>

							 <div class="form-group">
							 <div class="col-sm-12">
								<div class="input-group">
									<span class="input-group-addon"><b> Correo asunto</b></span>
								   <input type="text" class="form-control" name="correo_asunto" id="correo_asunto" placeholder="Asunto en el correo" value= "<?php echo $correo_asunto; ?>" >
								</div>
							 </div>
							 </div>
							 <div class="form-group">
							 <div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Host Correo</b></span>
								   <input type="text" class="form-control" name="correo_host" id="correo_host" placeholder="Correo host" value= "<?php echo $correo_host; ?>" >
								</div>
							 </div>
							 <div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Puerto Correo</b></span>
								   <input type="text" class="form-control" name="correo_port" id="correo_port" placeholder="Correo port" value= "<?php echo $puerto_correo; ?>" >
								</div>
							 </div>
							 </div>
							 <div class="form-group">
							 <div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Correo remitente</b></span>
								   <input type="email" class="form-control" name="correo_remitente" id="correo_remitente" placeholder="Correo remitente" value= "<?php echo $correo_remitente; ?>" >
								</div>
							 </div>
							 <div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Contraseña correo</b></span>
								   <input type="password" class="form-control" name="correo_pass" id="correo_pass" placeholder="Contraseña correo" value= "" >
								</div>
							 </div>
							 </div>
							 <div class="form-group">
								<div id="resultados_ajax_emisor"></div>
							</div>
							</div>
							 <div class="modal-footer">
									<button type="submit" class="btn btn-primary" name="guardar_emisor" >Guardar</button>
							</div>
			</form>
      </div>
    </div>
    <div class="panel panel-success">
      <!--<div class="panel-heading">
        <h4 class="panel-title">-->
          <a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse2"><span class="caret"></span> Configuración de la firma electrónica</a>
        <!--</h4>
      </div>-->
      <div id="collapse2" class="panel-collapse collapse">
        			<form class="form-horizontal" method="POST" id="configura_firma" name="configura_firma" enctype="multipart/form-data">
					  <div class="panel-body">
					  <div class="form-group">
								 <div class="col-sm-6">
									<div class="input-group">
										<span class="input-group-addon"><b>Archivo firma</b></span>
										<input class='filestyle' data-buttonText=" Firma" type="file" name="archivo" id="archivo">
									</div> 
								 </div>
								<div class="col-sm-6">
									<div class="input-group">
									<span class="input-group-addon"><b> Contraseña firma</b></span>
									   <input type="password" class="form-control" name="clave_firma" id="clave_firma" >
									</div>
								</div>
						</div>
						
						<div class="form-group">
						<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Vencimiento firma (<?php echo $fecha_vence ?>)</b></span>
								   <input type="text" class="form-control" name="vence_firma" id="vence_firma" value= "<?php echo $fecha_vence; ?>" >
								</div>
						 </div>
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group">Archivo firma</span>
								<a href="../facturacion_electronica/firma_digital/<?php echo $archivo_firma; ?>" title='Descargar' download >Descargar archivo firma digital</i> </a>
								</div>
							</div>
						</div>
						<div class="form-group">
						<div id="resultados_ajax_firma"></div>
						</div>
			
					  </div>
					
					 <div class="modal-footer">
							<button type="submit" class="btn btn-primary" name="guardar_firma_electronica" >Guardar</button>
					</div>
			</form>
      </div>
    </div>
    <div class="panel panel-success">
      <!--<div class="panel-heading">
        <h4 class="panel-title">-->
          <a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse3"><span class="caret"></span> Información de sucursales y consecutivos iniciales de documentos</a>
        <!--</h4>
      </div>-->
      <div id="collapse3" class="panel-collapse collapse">
			<form class="form-horizontal" id= "secuencia_sucursales" method="POST" enctype="multipart/form-data" >
			  <div class="panel-body">
							<div class="form-group">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Sucursal</b></span>
								<input type="hidden" name="ruc_empresa" value= "<?php echo $ruc_empresa; ?>" >
										<select class="form-control" name="serie_sucursal" id="serie_sucursal">
											<option value="0" >Seleccione serie</option>
											<?php
											$conexion = conenta_login();
											$sql = "SELECT * FROM sucursales where ruc_empresa ='".$ruc_empresa."' order by id_sucursal asc;";
											$res = mysqli_query($conexion,$sql);
											while($o = mysqli_fetch_assoc($res)){
											?>
											<option value="<?php echo $o['serie'] ?>"><?php echo $o['serie'] ?> </option>
											<?php
											}
											?>
										</select>
								</div>
							</div> 
							
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Moneda</b></span>
										<select class="form-control" name="moneda_sucursal" id="mon_sucursal">
											<option value="0" >Seleccione moneda</option>
											<option value="DOLAR" >Dólar</option>
											<option value="EURO" >Euro</option>
										</select>
								</div> 
							 </div>
							 </div>
							 <div class="form-group">
							 <div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Decimales cantidad</b></span>
										<select class="form-control" name="decimales_cantidad" id="deci_cant">
											<option value="" selected>Seleccione</option>
											<option value="1">Cero</option>
											<option value="2">Dos</option>
											<option value="4">Cuatro</option>
											<option value="6">Seis</option>
										</select>
								</div> 
							 </div>
								<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Decimales precio</b></span>
										<select class="form-control" name="decimales_documento" id="deci_docu">
										    <option value="0" selected>Seleccione</option>
											<option value="2">Dos</option>
											<option value="4">Cuatro</option>
											<option value="6">Seis</option>
										</select>
								</div> 
							 </div>
							 </div>
							<div class="form-group">
							<div class="col-sm-12">
								<div class="input-group">
									<span class="input-group-addon"><b> Dirección sucursal</b></span>
									<input type="text" class="form-control" name="dir_sucursal" id="dir_sucursal"  required>
								</div>
							</div>
							</div>
							<div class="form-group">
							<div class="col-sm-12">
								<div class="input-group">
									<span class="input-group-addon"><b> Nombre sucursal</b></span>
									<input type="text" class="form-control" name="nombre_sucursal" id="nombre_sucursal"  required>
								</div>
							</div>
							</div>
							<div class="form-group">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Factura</b></span>
									<input type="text" class="form-control" name="inicial_factura" id="ini_factura"  required>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Nota de crédito</b></span>
									<input type="text" class="form-control" name="inicial_nc" id="ini_nc" required>
								</div>
							</div>
							</div>							
							<div class="form-group">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Nota de débito</b></span>
									<input type="text" class="form-control" name="inicial_nd" id="ini_nd"  required>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Guía de remisión</b></span>
									<input type="text" class="form-control" name="inicial_gr" id="ini_gr" required>
								</div>
							</div>
							</div>
							<div class="form-group">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Retención</b></span>
									<input type="text" class="form-control" name="inicial_cr" id="ini_cr"  required>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Liquidación</b></span>
									<input type="text" class="form-control" name="inicial_liq" id="ini_liq" required>
								</div>
							</div>
							</div>
							<div class="form-group">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><b> Logo sucursal</b></span>
								<input class='filestyle' data-buttonText=" Logo" type="file" name="logo_sucursal">
								</div> 
							 </div>
							 <label class="col-sm-6">* Cuando se actualizan los datos no es necesario agregar logo.</label>
							</div>
							<div class="form-group">
						<div class="col-sm-6">
						</div>
								<div class="col-sm-6">
									<div class="input-group">
										<span class="input-group">Logos sucursales</span>
									<?php
									while ($row=mysqli_fetch_array($busca_info_sucursales)){
									$logo=$row["logo_sucursal"];
									$sucursal=$row['serie'];
									?>
									<a href="../logos_empresas/<?php echo $logo; ?>" title='Descargar' download >Descargar logo sucursal <?php echo $sucursal; ?><br></i> </a>
									<?php
									}
									?>
									</div>
								</div>
						</div>
							<div class="form-group">
								<div id="resultados_ajax_sucursales"></div>
							</div>
					</div>
				  <div class="modal-footer">
						<button type="submit" class="btn btn-primary" name="guardar_secuencia" >Guardar</button>
				</div>
			</form>
      </div>
    </div>
	
	<!--
	<div class="panel panel-info">
          <a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordion" href="#collapse4"><span class="caret"></span> enviar otro servidor</a>
		<div id="collapse4" class="panel-collapse collapse">
			<form class="form-horizontal" id= "enviar" method="POST" enctype="multipart/form-data" >
			  <div class="panel-body">
							<div class="form-group">
								<div id="resultados_ajax_envio"></div>
							</div>
					</div>
				  <div class="modal-footer">
						<button type="button" class="btn btn-primary" name="enviar_arhivo" onclick="enviar_archivo();">Enviar</button>
				</div>
			</form>
			
			
      </div>
    </div>
	-->
	
	
	
  </div> 
					
</div><!--fin del body de todo -->
</div><!--fin del panel info que abarca a todo -->
</div> <!--fin de la caja de 8 espacios -->
</div> <!--fin del container -->

	
<?php }else{
header('Location: ../includes/logout.php');
exit;
}
?>
<script type="text/javascript" src="../js/style_bootstrap.js"> </script>
<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
<script src="../js/notify.js"></script>
</body>

</html>
<script>
jQuery(function($){
     $("#vence_firma").mask("99-99-9999");
});
//para guardar los datos del emisor inicial
$(function(){
        $("#configura_emisor").on("submit", function(e){
            e.preventDefault();
            var f = $(this);
            var formData = new FormData(document.getElementById("configura_emisor"));
			formData.append("dato", "valor");
            $.ajax({
                url: "../ajax/guardar_emisor_fe.php?action=guarda_actualiza_emisor",
                type: "post",
                dataType: "html",
                data: formData,
				beforeSend: function(objeto){
						$("#resultados_ajax_emisor").html("Mensaje: Cargando...");
					  },
                cache: false,
                contentType: false,
				processData: false
            })
                .done(function(res){
                    $("#resultados_ajax_emisor").html(res);
                });
        });
    });

	//para guardar la firma
	$(function(){
        $("#configura_firma").on("submit", function(e){
            e.preventDefault();
            var f = $(this);
            var formData = new FormData(document.getElementById("configura_firma"));
			formData.append("dato", "valor");
            $.ajax({
                url: "../ajax/guardar_emisor_fe.php?action=guarda_actualiza_firma",
                type: "post",
                dataType: "html",
                data: formData,
				beforeSend: function(objeto){
						$("#resultados_ajax_firma").html("Mensaje: Cargando...");
					  },
                cache: false,
                contentType: false,
				processData: false
            })
                .done(function(res){
                    $("#resultados_ajax_firma").html(res);
                });
        });
    });

	//para guardar datos de sucursales
$(function(){
        $("#secuencia_sucursales").on("submit", function(e){
            e.preventDefault();
            var f = $(this);
            var formData = new FormData(document.getElementById("secuencia_sucursales"));
            formData.append("dato", "valor");
            $.ajax({
                url: "../ajax/guardar_sucursales_fe.php",
                type: "post",
                dataType: "html",
                data: formData,
				beforeSend: function(objeto){
						$("#resultados_ajax_sucursales").html("Mensaje: Actualizando...");
					  },
                cache: false,
                contentType: false,
				processData: false
            })
                .done(function(res){
                    $("#resultados_ajax_sucursales").html(res);
                });
        });
    });
	
//para traer informacion de la sucursal cuando se seleccione la serie
$(function(){
	$('#serie_sucursal').change(function(){
		 var serie_a_pasar = $(this).val();
		$.post( '../ajax/combo_info_sucursal.php', {serie_va: serie_a_pasar}).done( function( respuesta ){
			$( '#resultados_ajax_sucursales' ).html( respuesta );
			var direccion_sucursal = $("#direccion_s").val();
			var nombre_sucursal = $("#nombre_s").val();
			var moneda_sucursal = $("#moneda_s").val();
			var factura_inicial = $("#inicial_factura").val();
			var nc_inicial = $("#inicial_nc").val();
			var nd_inicial = $("#inicial_nd").val();
			var gr_inicial = $("#inicial_gr").val();
			var cr_inicial = $("#inicial_cr").val();
			var liq_inicial = $("#inicial_liq").val();
			var decimal_docs = $("#decimal_doc").val();
			var decimal_cant = $("#decimal_cant").val();
		
			$("#dir_sucursal").val(direccion_sucursal);
			$("#nombre_sucursal").val(nombre_sucursal);
			$("#mon_sucursal").val(moneda_sucursal);
			$("#ini_factura").val(factura_inicial);
			$("#ini_nc").val(nc_inicial);
			$("#ini_nd").val(nd_inicial);
			$("#ini_gr").val(gr_inicial);
			$("#ini_cr").val(cr_inicial);
			$("#ini_liq").val(liq_inicial);
			$("#deci_docu").val(decimal_docs);
			$("#deci_cant").val(decimal_cant);
		}); 
	});
			
});

/*
//para enviar un archivo a otro servidor prueba
function enviar_archivo(){
	$.ajax({
			type: "GET",
			url: "../ajax/enviar_servidor.php",
			 beforeSend: function(objeto){
				$("#resultados_ajax_envio").html("Mensaje: Enviando...");
			  },
			success: function(datos){
			$("#resultados_ajax_envio").html(datos).fadeIn('slow');
			//$("#resultados_ajax_envio").html(datos);
			}
		});
}
*/
</script>

