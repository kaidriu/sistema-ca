<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">

<head>
	<title>Generar asientos</title>
	<?php
	session_start();
	if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])) {
		$id_usuario = $_SESSION['id_usuario'];
		$id_empresa = $_SESSION['id_empresa'];
		$ruc_empresa = $_SESSION['ruc_empresa'];

		include("../paginas/menu_de_empresas.php");
		$con = conenta_login();
	?>
</head>

<body>
	<div class="container-fluid">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h4><i class='glyphicon glyphicon-pencil'></i> Generar asientos contables</h4>
			</div>
			<div class="panel-body">
				<form class="form-horizontal" role="form">
					<input type="hidden" name="id_cuenta" id="id_cuenta">
					<div class="form-group">
						<div class="col-md-3">
							<div class="input-group">
								<span class="input-group-addon"><b>Transacción</b></span>
								<select class="form-control input-md" id="tipo_asiento" name="tipo_asiento" required>
									<option value="ventas" selected> Ventas</option>
									<option value="nc_ventas"> Notas de crédito Ventas</option>
									<option value="retenciones_ventas"> Retenciones en ventas</option>
									<option value="compras_servicios"> Compras y servicios</option>
									<option value="retenciones_compras"> Retenciones en compras</option>
									<option value="ingresos"> Ingresos</option>
									<option value="egresos"> Egresos</option>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="input-group">
								<span class="input-group-addon"><b>Cliente/Proveedor</b></span>
								<input type="text" class="form-control" name="cliente_proveedor" id="cliente_proveedor" value="Todos" placeholder="Todos" onkeyup="buscar_cli_pro();">
								<input type="hidden" name="id_cli_pro" id="id_cli_pro">
							</div>
						</div>
						<div class="col-sm-2">
							<div class="input-group">
								<span class="input-group-addon"><b>Desde</b></span>
								<input type="text" class="form-control input-sm text-center" name="fecha_desde" id="fecha_desde" value="<?php echo date("01-01-Y"); ?>">
							</div>
						</div>

						<div class="col-sm-2">
							<div class="input-group">
								<span class="input-group-addon"><b>Hasta</b></span>
								<input type="text" class="form-control input-sm text-center" name="fecha_hasta" id="fecha_hasta" value="<?php echo date("d-m-Y"); ?>">
							</div>
						</div>
						<div class="col-sm-1">
							<button type="button" title="Mostrar " class="btn btn-info btn-md" onclick="mostrar_documentos()"><span class="glyphicon glyphicon-search"></span> Generar</button>
						</div>
						<span id="loader"></span>

					</div>
				</form>
				<div id="resultados"></div><!-- Carga los datos ajax -->
				<div class='outer_div'></div><!-- Carga los datos ajax -->

			</div>
			<!--fin del body de todo -->
		</div>
		<!--fin del panel info que abarca a todo -->
	</div>
	<!--fin del container -->


<?php } else {
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
	jQuery(function($) {
		$("#fecha_desde").mask("99-99-9999");
		$("#fecha_hasta").mask("99-99-9999");
	});

	$(function() {
		$("#fecha_desde").datepicker({
			dateFormat: "dd-mm-yy",
			firstDay: 1,
			dayNamesMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
			dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
			monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
				"Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
			],
			monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun",
				"Jul", "Ago", "Sep", "Oct", "Nov", "Dic"
			]
		});
	});

	$(function() {
		$("#fecha_hasta").datepicker({
			dateFormat: "dd-mm-yy",
			firstDay: 1,
			dayNamesMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
			dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
			monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
				"Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
			],
			monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun",
				"Jul", "Ago", "Sep", "Oct", "Nov", "Dic"
			]
		});
	});

	function mostrar_documentos() {
		var transaccion = $("#tipo_asiento").val();
		var cliente_proveedor = $("#id_cli_pro").val();
		var desde = $("#fecha_desde").val();
		var hasta = $("#fecha_hasta").val();
		$("#loader").fadeIn('slow');
		$.ajax({
			url: '../ajax/buscar_documentos_por_contabilizar.php?action=' + transaccion + '&cliente_proveedor=' + cliente_proveedor + '&desde=' + desde + '&hasta=' + hasta,
			beforeSend: function(objeto) {
				$('#loader').html('<img src="../image/ajax-loader.gif">');
			},
			success: function(data) {
				$(".outer_div").html(data).fadeIn('slow');
				$('#loader').html('');
			}
		})
	}


	function buscar_cli_pro() {
		var tipo_asiento = $("#tipo_asiento").val();

		if (tipo_asiento == 'ventas' || tipo_asiento == 'nc_ventas' || tipo_asiento == 'retenciones_ventas' || tipo_asiento == 'ingresos') {
			$("#cliente_proveedor").autocomplete({
				source: '../ajax/clientes_autocompletar.php',
				minLength: 2,
				select: function(event, ui) {
					event.preventDefault();
					$('#id_cli_pro').val(ui.item.id);
					$('#cliente_proveedor').val(ui.item.nombre);
				}
			});
		}

		if (tipo_asiento == 'compras_servicios' || tipo_asiento == 'retenciones_compras' || tipo_asiento == 'egresos') {
			$("#cliente_proveedor").autocomplete({
				source: '../ajax/proveedores_autocompletar.php',
				minLength: 2,
				select: function(event, ui) {
					event.preventDefault();
					$('#id_cli_pro').val(ui.item.id_proveedor);
					$('#cliente_proveedor').val(ui.item.razon_social);
				}
			});
		}


		$("#cliente_proveedor").on("keydown", function(event) {
			if (event.keyCode == $.ui.keyCode.UP || event.keyCode == $.ui.keyCode.DOWN || event.keyCode == $.ui.keyCode.DELETE) {
				$("#id_cli_pro").val("");
				$("#cliente_proveedor").val("");
			}
			if (event.keyCode == $.ui.keyCode.DELETE) {
				$("#cliente_proveedor").val("");
				$("#id_cli_pro").val("");
			}
		});
	}

	function eliminar_registro(id_registro, transaccion) {
		if (confirm("Realmente desea eliminar el item?")) {
			$.ajax({
				type: "GET",
				url: "../ajax/buscar_documentos_por_contabilizar.php?action=eliminar_registro",
				data: "id_registro=" + id_registro+"&transaccion="+transaccion,
				beforeSend: function(objeto) {
					$("#resultados").html("Mensaje: Cargando...");
				},
				success: function(datos) {
					$(".outer_div").html(datos).fadeIn('slow');
					$("#resultados").html('');
				}
			});
			//event.preventDefault();
		}
	}

	//guardar asientos automaticos
	function guardar_asientos_automaticos() {
		var tipo_asiento = $("#tipo_asiento").val();
		var cliente_proveedor = $("#cliente_proveedor").val();
		var fecha_desde = $("#fecha_desde").val();
		var fecha_hasta = $("#fecha_hasta").val();

		$('#guardar_asiento_automatico').attr("disabled", true);
		$.ajax({
			type: "POST",
			url: '../ajax/guardar_asientos_automaticos.php',
			data: "tipo_asiento=" + tipo_asiento + "&cliente_proveedor=" + cliente_proveedor + "&fecha_desde=" + fecha_desde + "&fecha_hasta=" + fecha_hasta,
			beforeSend: function(objeto) {
				$('#loader').html('Guardando...');
			},
			success: function(datos) {
				$("#resultados").html(datos);
				$('#loader').html('');
				$('#guardar_asiento_automatico').attr("disabled", false);
			}
		});
		event.preventDefault();
	}
</script>