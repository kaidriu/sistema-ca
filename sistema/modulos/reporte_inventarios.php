<?php
session_start();
if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])) {
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa = $_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];

?>
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="utf-8">
		<title>Reporte inventarios</title>
		<?php
		include("../paginas/menu_de_empresas.php");
		//date_default_timezone_set('America/Guayaquil');
		?>
	</head>

	<body>

		<div class="container-fluid">
			<div class="panel panel-info">
				<div class="panel-heading">
					<h4><i class='glyphicon glyphicon-list-alt'></i> Reporte de inventarios</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="../excel/reporte_inventarios_excel.php">
						<div class="form-group row">
							<div class="col-sm-2">
								<div class="input-group">
									<span class="input-group-addon"><b>Tipo</b></span>
									<input type="hidden" id="ordenado" name="ordenado" value="codigo_producto">
									<input type="hidden" id="id_producto" name="id_producto">
									<input type="hidden" id="por" name="por" value="asc">
									<select class="form-control input-sm" id="registro_inventario" name="registro_inventario" required>
										<option value="1" selected> Entradas</option>
										<option value="2"> Salidas</option>
										<option value="3"> Existencia en general</option>
										<option value="4"> Existencia por caducidad</option>
										<option value="5"> Existencia por lote</option>
									</select>
								</div>
							</div>
													
							<div class="col-sm-2">
								<div class="input-group">
									<span class="input-group-addon"><b>Marca</b></span>
									<select class="form-control input-sm" title="Marca" name="id_marca" id="id_marca">
										<?php
										$sql_marca = mysqli_query($conexion, "SELECT * FROM marca where ruc_empresa='" . $ruc_empresa . "'");
										?> <option value="">Todas</option>
										<?php
										while ($tipo = mysqli_fetch_assoc($sql_marca)) {
										?>
											<option value="<?php echo $tipo['id_marca'] ?>"><?php echo strtoupper($tipo['nombre_marca']) ?> </option>
										<?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="input-group">
									<span class="input-group-addon"><b>Producto</b></span>
									<input type="text" class="form-control input-sm" name="producto" id="producto" onkeyup='agregar_productos();' placeholder="Todos" autocomplete="off">
								</div>
							</div>	

							<div class="col-sm-2" id="label_desde">
								<div class="input-group">
									<span class="input-group-addon" ><b>Desde</b></span>
									<input type="text" class="form-control input-sm text-center" name="fecha_desde" id="fecha_desde" value="<?php echo date("01-m-Y"); ?>">
								</div>
							</div>
							<div class="col-sm-2">
								<div class="input-group">
									<span class="input-group-addon"><b>Hasta</b></span>
									<input type="text" class="form-control input-sm text-center" name="fecha_hasta" id="fecha_hasta" value="<?php echo date("d-m-Y"); ?>">
								</div>
							</div>
							
							
						</div>

					<div class="form-group row">
					
						<div class="col-sm-2" id="label_lote">
								<div class="input-group">
									<span class="input-group-addon"><b>Lote</b></span>
									<input type="text" class="form-control input-sm" name="lote" id="lote" placeholder="Lote" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-2" id="label_caducidad">
								<div class="input-group">
									<span class="input-group-addon"><b>Caducidad</b></span>
									<input type="text" class="form-control input-sm" name="caducidad" id="caducidad" placeholder="Caducidad" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-4" id="label_referencia">
								<div class="input-group">
									<span class="input-group-addon" ><b>Referencia</b></span>
									<input type="text" class="form-control input-sm" name="referencia" id="referencia" placeholder="Referencia" autocomplete="off">
								</div>
							</div>

							<div class="col-sm-2">
								<button type="button" class="btn btn-info btn-sm" onclick='load(1);'><span class="glyphicon glyphicon-search"></span> Ver</button>
								<button type="submit" class="btn btn-success btn-sm"><img src="../image/excel.ico" width="20" height="18">
								</button><span id="loader"></span>
							</div>

						</div>
					</form>

					<div id="resultados"></div><!-- Carga los datos ajax -->
					<div class='outer_div'></div><!-- Carga los datos ajax -->
				</div>
			</div>
		</div>

	<?php

} else {
	header('Location: ../includes/logout.php');
	exit;
}
	?>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/notify.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	</body>

	</html>
	<script>
		jQuery(function($) {
			$("#fecha_hasta").mask("99-99-9999");
			$("#fecha_desde").mask("99-99-9999");
		});

		function agregar_productos() {
			$("#producto").autocomplete({
				source: '../ajax/productos_autocompletar_inventario.php',
				minLength: 2,
				select: function(event, ui) {
					event.preventDefault();
					$('#producto').val(ui.item.nombre);
					$('#id_producto').val(ui.item.id);
				}
			});
		}

		$("#producto").on("keydown", function(event) {
			if (event.keyCode == $.ui.keyCode.UP || event.keyCode == $.ui.keyCode.DOWN || event.keyCode == $.ui.keyCode.DELETE) {
				$("#producto").val("");
				$("#id_producto").val("");
			}
			if (event.keyCode == $.ui.keyCode.DELETE) {
				$("#producto").val("");
				$("#id_producto").val("");
			}
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


		//tiene que llamarse load para que funcione con pagination
		function load(page) {
			var desde = $("#fecha_desde").val();
			var hasta = $("#fecha_hasta").val();
			var tipo = $("#registro_inventario").val();
			var producto = $("#id_producto").val();
			var por = $("#por").val();
			var ordenado = $("#ordenado").val();
			var id_marca = $("#id_marca").val();
			var lote = $("#lote").val();
			var caducidad = $("#caducidad").val();
			var referencia = $("#referencia").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url: '../ajax/reporte_inventarios.php?action=mostrar_consulta&page=' + page + 
				'&fecha_desde=' + desde + '&fecha_hasta=' + hasta + '&tipo=' + tipo + 
				'&por=' + por + '&ordenado=' + ordenado + '&producto=' + producto + 
				'&marca='+id_marca+'&lote='+lote+'&caducidad='+caducidad+'&referencia='+referencia,
				beforeSend: function(objeto) {
					$('#loader').html('<img src="../image/ajax-loader.gif">');
				},
				success: function(datos) {
					$(".outer_div").html(datos).fadeIn('slow');
					$('#loader').html('');
				}
			});
		}

		function ordenar(ordenado) {
			$("#ordenado").val(ordenado);
			var por = $("#por").val();
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

		$('#registro_inventario').change(function() {
			$("#id_marca").val("");
			$("#id_producto").val("");
			$("#producto").val("");
			$("#lote").val("");
			$("#caducidad").val("");
			$("#referencia").val("");
			var tipo = $("#registro_inventario").val();
			if (tipo == "1" || tipo == "2") {
				document.getElementById("label_desde").style.display = "";
				document.getElementById("label_referencia").style.display = "";
				document.getElementById("fecha_desde").style.display = "";
				document.getElementById("referencia").style.display = "";
			} else {
				document.getElementById("label_desde").style.display = "none";
				document.getElementById("fecha_desde").style.display = "none";
				document.getElementById("referencia").style.display = "none";
				document.getElementById("label_referencia").style.display = "none";
			}

			$.ajax({
				url: '../ajax/reporte_inventarios.php?action=limpiar_tabla_tmp',
				beforeSend: function(objeto) {
					$('#loader').html('<img src="../image/ajax-loader.gif">');
				},
				success: function(datos) {
					$(".outer_div").html(datos).fadeIn('slow');
					$('#loader').html('');
				}
			});

		});
	</script>