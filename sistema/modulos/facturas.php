<?php
session_start();
if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])) {
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa = $_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
?>
	<!DOCTYPE html>
	<html lang="es">

	<head>
		<meta charset="utf-8">
		<title>Ventas</title>
		<?php include("../paginas/menu_de_empresas.php");
		include("../modal/enviar_documentos_sri.php");
		include("../modal/detalle_documento.php");
		include("../modal/anular_documentos_sri.php");
		include("../modal/cobro_pago_directo.php");
		include("../modal/enviar_documentos_mail.php");
		include("../modal/factura.php");
		unset($_SESSION['arrayFormaPagoIngresoFactura']);
		?>

	</head>

	<body>

		<div class="container-fluid">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="btn-group pull-right">

						<button type="submit" class="btn btn-info" data-toggle="modal" data-target="#factura" onclick="crear_factura();" title="Nueva factura electrónica"><span class="glyphicon glyphicon-plus"></span> Nueva factura</button>
					</div>
					<h4><i class="glyphicon glyphicon-search"></i> Facturas</h4>
				</div>

				<ul class="nav nav-tabs nav-justified">
					<li class="active"><a data-toggle="tab" href="#facturas">Facturas</a></li>
					<li><a data-toggle="tab" href="#detalle_facturas">Detalle de facturas</a></li>
					<li><a data-toggle="tab" href="#detalle_adicionales">Detalles adicionales</a></li>
				</ul>

				<div class="tab-content">
					<div id="facturas" class="tab-pane fade in active">
						<div class="panel-body">
							<form class="form-horizontal" role="form">
								<div class="form-group row">
									<label for="q" class="col-md-1 control-label">Buscar:</label>
									<div class="col-md-5">
										<input type="hidden" id="ordenado" value="id_encabezado_factura">
										<input type="hidden" id="por" value="desc">
										<div class="input-group">
											<input type="text" class="form-control" id="q" placeholder="Cliente, serie, factura, fecha, ruc, estado" onkeyup='load(1);'>
											<span class="input-group-btn">
												<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search"></span> Buscar</button>
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

					<div id="detalle_facturas" class="tab-pane fade">
						<div class="panel-body">
							<form class="form-horizontal" role="form">
								<div class="form-group row">
									<label for="d" class="col-md-1 control-label">Buscar:</label>
									<div class="col-md-5">
										<div class="input-group">
											<input type="text" class="form-control" id="d" placeholder="Productos, servicios, código" onkeyup='load(1);'>
											<span class="input-group-btn">
												<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search"></span> Buscar</button>
											</span>
										</div>
									</div>
									<span id="loader_detalles"></span>
								</div>
							</form>
							<div id="resultados_detalles_facturas"></div><!-- Carga los datos ajax -->
							<div class='outer_div_detalles'></div><!-- Carga los datos ajax -->
						</div>
					</div>
					<div id="detalle_adicionales" class="tab-pane fade">
						<div class="panel-body">
							<form class="form-horizontal" role="form">
								<div class="form-group row">
									<label for="d" class="col-md-1 control-label">Buscar:</label>
									<div class="col-md-5">
										<div class="input-group">
											<input type="text" class="form-control" id="a" placeholder="Detalle adicionales" onkeyup='load(1);'>
											<span class="input-group-btn">
												<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search"></span> Buscar</button>
											</span>
										</div>
									</div>
									<span id="loader_adicionales"></span>
								</div>
							</form>
							<div id="resultados_detalles_adicionales"></div><!-- Carga los datos ajax -->
							<div class="outer_div_adicionales"></div><!-- Carga los datos ajax -->
						</div>
					</div>
				</div>

			</div>
		</div>

	<?php
} else {
	header('Location: ../includes/logout.php');
	exit;
}
	?>
	<link rel="stylesheet" href="../css/jquery-ui.css">
	<script src="../js/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	</body>

	</html>
	<script>
		$(document).ready(function() {
			load(1);
		});

		function crear_factura() {
			document.querySelector("#titleModalFactura").innerHTML = "<i class='glyphicon glyphicon-ok'></i> Nueva factura";
			document.querySelector("#guardar_factura").reset();
			document.querySelector("#id_factura").value = "";
			document.querySelector("#btnActionFormFactura").classList.replace("btn-info", "btn-primary");
			document.querySelector("#btnTextFactura").innerHTML = "<i class='glyphicon glyphicon-floppy-disk'></i> Guardar";
			document.querySelector('#btnActionFormFactura').title = "Guardar Factura";
			document.getElementById("label_bodega_producto_factura").style.display = "none";
			document.getElementById("label_existencia_producto_factura").style.display = "none";
			document.getElementById("label_lote_producto_factura").style.display = "none";
			document.getElementById("label_medida_producto_factura").style.display = "none";
			document.getElementById("label_caducidad_producto_factura").style.display = "none";
			document.getElementById("lista_lote_producto_factura").style.display = "none";
			document.getElementById("lista_caducidad_producto_factura").style.display = "none";
			document.getElementById("lista_medida_producto_factura").style.display = "none";

			//para buscar el numero de factura que continua cada vez que se hace clic en nueva factura
			var id_serie = $("#serie_factura").val();

			$.post('../ajax/buscar_ultima_factura.php', {
				serie_fe: id_serie
			}).done(function(respuesta) {
				var factura_final = respuesta;
				$("#secuencial_factura").val(factura_final);
			});

			//para traer el tipo de configuracion de inventarios, si o no
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'inventario',
				serie_consultada: id_serie
			}).done(function(respuesta_inventario) {
				var resultado_inventario = $.trim(respuesta_inventario);
				$('#inventario_producto_factura').val(resultado_inventario);

				if (resultado_inventario == "SI") {
					document.getElementById("label_bodega_producto_factura").style.display = "";
				}
			});

			//para traer y ver si trabaja con medida
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'medida',
				serie_consultada: id_serie
			}).done(function(respuesta_medida) {
				var resultado_medida = $.trim(respuesta_medida);
				$('#muestra_medida_producto_factura').val(resultado_medida);
			});

			//para traer y ver si trabaja con lote
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'lote',
				serie_consultada: id_serie
			}).done(function(respuesta_lote) {
				var resultado_lote = $.trim(respuesta_lote);
				$('#muestra_lote_producto_factura').val(resultado_lote);
			});

			//para traer y ver si trabaja con bodega
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'bodega',
				serie_consultada: id_serie
			}).done(function(respuesta_bodega) {
				var resultado_bodega = $.trim(respuesta_bodega);
				$('#muestra_bodega_producto_factura').val(resultado_bodega);
			});

			//para traer y ver si trabaja con vencimiento
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'vencimiento',
				serie_consultada: id_serie
			}).done(function(respuesta_vencimiento) {
				var resultado_vencimiento = $.trim(respuesta_vencimiento);
				$('#muestra_vencimiento_producto_factura').val(resultado_vencimiento);
			});

			//para cuando es nueva factura
			$.ajax({
				url: "../ajax/facturas.php?action=nueva_factura",
				beforeSend: function(objeto) {
					$("#detalle_factura").html("Cargando...");
				},
				success: function(data) {
					$('#detalle_factura').html('');
					$('#detalle_informacion_adicional').html('');
					$('#detalle_subtotales_factura').html('');
					$('#detalle_formas_pago').html('');
				}
			});
			//document.getElementById("nombre_cliente_factura").focus();
		}


		function load(page) {
			var por = $("#por").val();
			var ordenado = $("#ordenado").val();
			var q = $("#q").val();
			var d = $("#d").val();
			var a = $("#a").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url: '../ajax/facturas.php?action=buscar_facturas&page=' + page + '&q=' + q + "&por=" + por + "&ordenado=" + ordenado,
				beforeSend: function(objeto) {
					$('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
				},
				success: function(data) {
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			});

			$("#loader_detalles").fadeIn('slow');
			$.ajax({
				url: '../ajax/facturas.php?action=buscar_detalle_facturas&page=' + page + '&d=' + d,
				beforeSend: function(objeto) {
					$('#loader_detalles').html('<img src="../image/ajax-loader.gif"> Cargando...');
				},
				success: function(data) {
					$(".outer_div_detalles").html(data).fadeIn('slow');
					$('#loader_detalles').html('');
				}
			});

			$("#loader_adicionales").fadeIn('slow');
			$.ajax({
				url: '../ajax/facturas.php?action=buscar_detalle_adicionales_facturas&page=' + page + '&a=' + a,
				beforeSend: function(objeto) {
					$('#loader_adicionales').html('<img src="../image/ajax-loader.gif"> Cargando...');
				},
				success: function(data) {
					$(".outer_div_adicionales").html(data).fadeIn('slow');
					$('#loader_adicionales').html('');
				}
			})

		}

		function ordenar(ordenado) {
			$("#ordenado").val(ordenado);
			var por = $("#por").val();
			var q = $("#q").val();
			var ordenado = $("#ordenado").val();
			$("#loader").fadeIn('slow');
			var value_por = document.getElementById('por').value;
			if (value_por == "asc") {
				$("#por").val("desc");
			}
			if (value_por == "desc") {
				$("#por").val("asc");
			}
			load(1);
		}

		function eliminar_factura(id) {
			var q = $("#q").val();
			var serie = $("#serie_factura" + id).val();
			var secuencial = $("#secuencial_factura" + id).val();

			if (confirm("Realmente desea eliminar la factura " + serie + "-" + secuencial + " ?")) {
				$.ajax({
					type: "POST",
					url: "../ajax/facturas.php?action=eliminar_factura",
					data: "id_factura=" + id,
					"q": q,
					beforeSend: function(objeto) {
						$("#loader").html("Actualizando...");
					},
					success: function(datos) {
						$("#resultados").html(datos);
						$("#loader").html("");
						load(1);
					}
				})
			}
		}
		//pasa el codigo del id del documento a anularse al modal de anular documentos sri
		function pasa_codigo_anular_factura_e(id) {
			var ruc_cliente = $("#ruc_cliente" + id).val();
			var aut_sri = $("#aut_sri" + id).val();
			var mail_cliente = $("#mail_cliente" + id).val();
			$("#ruc_receptor").val('');
			$("#numero_autorizacion").val('');
			$("#clave_accesso").val('');
			$("#correo_receptor").val('');
			$("#tipo_comprobante").val('');
			$("#fecha_autorizacion").val('');
			$("#estado_sri_consultado").val('');
			$('#anular_sri').attr("disabled", true);

			$.ajax({
				url: '../ajax/detalle_documento.php?action=info_fecha_autorizacion&clave_acceso=' + aut_sri,
				beforeSend: function(objeto) {
					$('#resultados_anular').html('');
				},
				success: function(datos) {
					$("#fecha_autorizacion").val(datos);
				}
			});

			$.ajax({
				url: '../ajax/detalle_documento.php?action=info_estado_documento&clave_acceso=' + aut_sri,
				beforeSend: function(objeto) {
					$('#resultados_anular').html('<img src="../image/ajax-loader.gif"> Consultando SRI, espere por favor...');
				},
				success: function(datos) {
					$("#estado_sri_consultado").val(datos);
					$("#ruc_receptor").val(ruc_cliente);
					$("#numero_autorizacion").val(aut_sri);
					$("#clave_accesso").val(aut_sri);
					$("#correo_receptor").val(mail_cliente);
					$("#tipo_comprobante").val('FACTURA');
					$("#id_documento_modificar").val(id);
					$('#resultados_anular').html('');
					$('#anular_sri').attr("disabled", false);
				}
			});
		}

		//para anular factura autorizada por el sri
		$("#anular_documento_sri").submit(function(event) {
			$('#anular_sri').attr("disabled", true);
			var parametros = $(this).serialize();
			if (confirm("Realmente desea anular la factura?")) {
				$.ajax({
					type: "POST",
					url: "../ajax/anular_documentos_sri.php",
					data: parametros + "&action=anular_factura",
					beforeSend: function(objeto) {
						$("#resultados_ajax_anular").html('<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando solicitud, espere por favor...</div></div>');
					},
					success: function(datos) {
						$("#resultados_ajax_anular").html(datos);
						$('#anular_sri').attr("disabled", false);
						load(1);
					}
				});
				event.preventDefault();
			};

		});

		function enviar_factura_mail(id) {
			var mail_receptor = $("#mail_cliente" + id).val();
			$("#id_documento").val(id);
			$("#mail_receptor").val(mail_receptor);
			$("#tipo_documento").val("factura");
		};

		function enviar_factura_sri(id) {
			var serie_factura = $("#serie_factura" + id).val();
			var secuencial_factura = $("#secuencial_factura" + id).val();
			var numero_factura = String("000000000" + secuencial_factura).slice(-9);
			var id_encabezado_factura = $("#id_encabezado_factura" + id).val();
			$("#id_documento_sri").val(id_encabezado_factura);
			$("#numero_documento_sri").val(serie_factura + '-' + numero_factura);
			$("#tipo_documento_sri").val("factura");
		};

		//para enviar por mail la factura
		$("#documento_mail").submit(function(event) {
			$('#enviar_mail').attr("disabled", true);
			$('#mensaje_mail').attr("hidden", true); // para mostrar el mensaje de dar clik para enviar y mas abajo lo desaparece
			var parametros = $(this).serialize();
			var pagina = $("#pagina").val();
			$.ajax({
				type: "GET",
				url: "../documentos_mail/envia_mail.php?",
				data: parametros,
				beforeSend: function(objeto) {
					$("#resultados_ajax_mail").html(
						'<div class="progress"><div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:100%;">Enviando Factura por mail espere por favor...</div></div>');
				},
				success: function(datos) {
					$("#resultados_ajax_mail").html(datos);
					$('#enviar_mail').attr("disabled", false);
					$('#mensaje_mail').attr("hidden", false); // lo vuelve a mostrar el mensaje cuando ya hace todo el proceso
					load(pagina);
				}
			});
			event.preventDefault();
		});
		//para que cuando se cierre el modal de enviar mail se reseteen los datos y se limpie
		$("#cerrar_mail").click(function() {
			$("#resultados_ajax_mail").empty();
		});

		//para que cuando se cierre el modal de enviar sri se reseteen los datos y se limpie
		$("#btnCerrar").click(function() {
			$("#resultados_ajax_sri").empty();
		});

		//para enviar la factura al sri
		$("#documento_sri").submit(function(event) {
			$('#enviar_sri').attr("disabled", true);
			var numero_factura = $("#numero_documento_sri").val();
			var parametros = $(this).serialize();
			var pagina = $("#pagina").val();
			$.ajax({
				type: "POST",
				url: '../facturacion_electronica/enviarComprobantesSri.php',
				data: parametros,
				beforeSend: function(objeto) {
					$('#resultados_ajax_sri').html('<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:100%;">Enviando Factura ' + numero_factura + ' espere por favor...</div></div>');
				},
				success: function(datos) {
					$("#resultados_ajax_sri").html(datos);
					$('#enviar_sri').attr("disabled", false);
					load(pagina);
				}

			});
			event.preventDefault();
		});


		function detalle_factura(id) {
			//var serie_factura = $("#serie_factura" + id).val();
			//var secuencial_factura = $("#secuencial_factura" + id).val();
			$("#loaderdet").fadeIn('slow');
			$.ajax({
				url: '../ajax/detalle_documento.php?action=facturas_ventas&id='+id,
				beforeSend: function(objeto) {
					$('#loaderdet').html('<img src="../image/ajax-loader.gif"> Cargando detalle de factura...');
				},
				success: function(data) {
					$(".outer_divdet").html(data).fadeIn('slow');
					$('#loaderdet').html('');
				}
			})
		}


		function editar_factura(id) {
			document.querySelector('#titleModalFactura').innerHTML = "<i class='glyphicon glyphicon-edit'></i> Actualizar Factura";
			document.querySelector("#guardar_factura").reset();
			document.querySelector("#id_factura").value = id;
			document.querySelector('#btnActionFormFactura').classList.replace("btn-primary", "btn-info");
			document.querySelector("#btnTextFactura").innerHTML = "<i class='glyphicon glyphicon-floppy-disk'></i> Actualizar";
			document.getElementById("label_bodega_producto_factura").style.display = "none";
			document.getElementById("label_existencia_producto_factura").style.display = "none";
			document.getElementById("label_lote_producto_factura").style.display = "none";
			document.getElementById("label_medida_producto_factura").style.display = "none";
			document.getElementById("label_caducidad_producto_factura").style.display = "none";
			document.getElementById("lista_lote_producto_factura").style.display = "none";
			document.getElementById("lista_caducidad_producto_factura").style.display = "none";
			document.getElementById("lista_medida_producto_factura").style.display = "none";
			//para traer el tipo de configuracion de inventarios, si o no
			var id_cliente = $("#id_cliente" + id).val();
			var nombre_cliente = $("#nombre_cliente" + id).val();
			var fecha_factura = $("#fecha_factura" + id).val();
			var id_serie = $("#serie_factura" + id).val();
			var secuencial_factura = $("#secuencial_factura" + id).val();
			var total_factura = $("#total_factura" + id).val();

			$("#id_factura").val(id);
			$("#id_cliente_factura").val(id_cliente);
			$("#nombre_cliente_factura").val(nombre_cliente);
			$("#fecha_factura").val(fecha_factura);
			$("#serie_factura").val(id_serie);
			$("#secuencial_factura").val(secuencial_factura);
			$("#suma_factura").val(total_factura);

			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'inventario',
				serie_consultada: id_serie
			}).done(function(respuesta_inventario) {
				var resultado_inventario = $.trim(respuesta_inventario);
				$('#inventario_producto_factura').val(resultado_inventario);

				if (resultado_inventario == "SI") {
					document.getElementById("label_bodega_producto_factura").style.display = "";
				}
			});

			//para traer y ver si trabaja con medida
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'medida',
				serie_consultada: id_serie
			}).done(function(respuesta_medida) {
				var resultado_medida = $.trim(respuesta_medida);
				$('#muestra_medida_producto_factura').val(resultado_medida);
			});

			//para traer y ver si trabaja con lote
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'lote',
				serie_consultada: id_serie
			}).done(function(respuesta_lote) {
				var resultado_lote = $.trim(respuesta_lote);
				$('#muestra_lote_producto_factura').val(resultado_lote);
			});

			//para traer y ver si trabaja con bodega
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'bodega',
				serie_consultada: id_serie
			}).done(function(respuesta_bodega) {
				var resultado_bodega = $.trim(respuesta_bodega);
				$('#muestra_bodega_producto_factura').val(resultado_bodega);
			});

			//para traer y ver si trabaja con vencimiento
			$.post('../ajax/consulta_configuracion_facturacion.php', {
				opcion_mostrar: 'vencimiento',
				serie_consultada: id_serie
			}).done(function(respuesta_vencimiento) {
				var resultado_vencimiento = $.trim(respuesta_vencimiento);
				$('#muestra_vencimiento_producto_factura').val(resultado_vencimiento);
			});

			//para cuando editar factura
			$.ajax({
				type: "POST",
				url: "../ajax/facturas.php?action=editar_factura",
				data: "id_factura=" + id + "&serie_factura=" + id_serie + "&secuencial_factura=" + secuencial_factura,
				beforeSend: function(objeto) {
					$("#detalle_factura").html("Cargando...");
				},
				success: function(data) {
					$('#detalle_factura').html('Cargando...');
					$('#detalle_informacion_adicional').html('Cargando...');
					$('#detalle_subtotales_factura').html('Cargando...');
					$('#detalle_formas_pago').html('Cargando...');
				}
			});

			//esperar 2 segundos para cargar
			setTimeout(function() {
				//para el encabezado de la factura y la info adicional
				$.ajax({
					url: "../ajax/facturas.php?action=muestra_cliente_adicionales_editar_factura",
					beforeSend: function(objeto) {
						$("#detalle_informacion_adicional").html("Cargando...");
					},
					success: function(dataAdicional) {
						$('#detalle_informacion_adicional').html(dataAdicional);
					}
				});

				//para mostrar el cuerpo de la factura
				$.ajax({
					type: "POST",
					url: "../ajax/facturas.php?action=muestra_cuerpo_editar_factura",
					data: "serie_factura=" + id_serie,
					beforeSend: function(objeto) {
						$("#detalle_factura").html("Cargando...");
					},
					success: function(dataCuerpo) {
						$('#detalle_factura').html(dataCuerpo);
					}
				});

				//para mostrar las formas de pago 
				$.ajax({
					type: "POST",
					url: "../ajax/facturas.php?action=muestra_formas_pago_editar_factura",
					data: "total_factura=" + total_factura,
					beforeSend: function(objeto) {
						$("#detalle_formas_pago").html("Cargando...");
					},
					success: function(dataFormasPago) {
						$('#detalle_formas_pago').html(dataFormasPago);
					}
				});

				//para mostrar subtotales de la factura
				$.ajax({
					type: "POST",
					url: "../ajax/facturas.php?action=muestra_subtotales_editar_factura",
					data: "serie_factura=" + id_serie,
					beforeSend: function(objeto) {
						$("#detalle_subtotales_factura").html("Cargando...");
					},
					success: function(datosSubtotal) {
						$("#detalle_subtotales_factura").html(datosSubtotal);
					}
				})

			}, 2000);

		}


function carga_modal_registrar_pago(id, valor, cliente, numero_factura){
	document.querySelector("#detalle_pago_factura").reset();
	$(".outer_divCobroVenta").html('').fadeIn('fast');
	$("#id_FacturaVenta").val(id);
	$("#valor_pago").val(valor);
	$("#porcobrar_FacturaVenta").val(valor);
	document.querySelector("#datos_cobro_factura").innerHTML = 'Cliente: '+ cliente + ' </br>Documento: ' + numero_factura + ' Saldo por cobrar: ' + valor ;
	$.ajax({
				url: "../ajax/facturas.php?action=nuevo_pago_factura",
				beforeSend: function(objeto) {
					$("#detalle_factura").html("Cargando...");
				},
				success: function(data) {
					$('#detalle_factura').html('');
				}
			});
}

	//agrega una forma de pago
	function agregar_forma_pago() {
		var forma_pago = $("#forma_pago").val();
		var valor_pago = $("#valor_pago").val();
		var tipo = $("#tipo").val();
	
		//Inicia validacion
		if (forma_pago == '0') {
			alert('Seleccione una forma de pago');
			document.getElementById('forma_pago').focus();
			return false;
		}

		//origen es para ver de que tabla me esta trayendo el dato, para segubn eso mostrar deposito o transferencia
		var origen = forma_pago.substring(0, 1);

		if (origen == 1 && tipo != '0') {
			document.getElementById("tipo").value = "0";
			document.getElementById('valor_pago').focus();
			return false;
		}

		if (origen == 2 && tipo == '0') {
			alert('Seleccione depósito o transferencia.');
			document.getElementById('tipo').focus();
			return false;
		}

		if (valor_pago == '') {
			alert('Ingrese valor');
			document.getElementById('valor_pago').focus();
			return false;
		}

		if (isNaN(valor_pago)) {
			alert('El dato ingresado en valor, no es un número');
			document.getElementById('valor_pago').focus();
			return false;
		}

		var forma_pago = forma_pago.substring(1, forma_pago.length);
		//Fin validacion
		$("#loaderCobroFacturaVenta").fadeIn('fast');
		$.ajax({
			url: "../ajax/facturas.php?action=agregar_forma_pago_ingreso_factura&forma_pago=" + forma_pago + "&valor_pago=" + valor_pago + "&tipo=" + tipo + "&origen=" + origen,
			beforeSend: function(objeto) {
				$("#loaderCobroFacturaVenta").html("Cargando...");
			},
			success: function(data) {
				$(".outer_divCobroVenta").html(data).fadeIn('fast');
				$('#loaderCobroFacturaVenta').html('');
				document.getElementById("forma_pago").value = "0";
				document.getElementById("tipo").value = "0";
				document.getElementById("valor_pago").value = "";
			}
		});
		event.preventDefault();
	}

	function eliminar_item_pago(id) {
		$.ajax({
			url: "../ajax/facturas.php?action=eliminar_item_pago&id_registro=" + id,
			beforeSend: function(objeto) {
				$("#loaderCobroFacturaVenta").html("Eliminando...");
			},
			success: function(data) {
				$(".outer_divCobroVenta").html(data).fadeIn('fast');
				$('#loaderCobroFacturaVenta').html('');
			}
		});
		event.preventDefault();
	}

	function guarda_pago_factura() {
        $('#btnActionFormPagoFactura').attr("disabled", true);
        var id_factura = $("#id_FacturaVenta").val();
		var fecha_ingreso = $("#fecha_ingreso").val();
        $.ajax({
            type: "POST",
            url: "../ajax/facturas.php?action=guardar_pago_factura",
            data: "id_factura=" + id_factura + "&fecha_ingreso="+fecha_ingreso,
            beforeSend: function(objeto) {
                $("#loaderCobroFacturaVenta").html("Guardando...");
            },
            success: function(datos) {
				$(".outer_divCobroVenta").html(datos);
                $("#loaderCobroFacturaVenta").html('');
                $('#btnActionFormPagoFactura').attr("disabled", false);
            }
        });
        event.preventDefault();
    }


	function duplicar_factura(id) {
		if (confirm("Seguro desea duplicar la factura?")) {
			$('#duplicarFactura').attr("disabled", true);
		$.ajax({
			type: "GET",
			url: "../ajax/facturas.php?action=duplicar_factura&id_factura=" + id,
			beforeSend: function(objeto) {
				$("#loaderdet").html("Duplicando factura...");
			},
			success: function(data) {
				$(".outer_divdet").html(data).fadeIn('fast');
				$('#loaderdet').html('');
				$('#duplicarFactura').attr("disabled", false);
			}
		});
		event.preventDefault();
	}
	}


	function generar_recibo_venta(id) {
		if (confirm("Seguro desea crear recibo de venta y eliminar la factura?")) {
			$('#reciboVenta').attr("disabled", true);
		$.ajax({
			type: "GET",
			url: "../ajax/facturas.php?action=recibo_venta&id_factura=" + id,
			beforeSend: function(objeto) {
				$("#loaderdet").html("Creando recibo de venta...");
			},
			success: function(data) {
				$(".outer_divdet").html(data).fadeIn('fast');
				$('#loaderdet').html('');
				$('#reciboVenta').attr("disabled", false);
			}
		});
		event.preventDefault();
	}
	}


	function imprimir_ticket(opcion, id_factura){
	window.open('../impresiones/imprimir.php?action='+opcion+'&id_factura='+id_factura, '_blank');
	}

	</script>