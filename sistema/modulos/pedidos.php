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
		<title>Pedidos</title>
		<?php
		include("../paginas/menu_de_empresas.php");
		?>
		<style type="text/css">
			ul.ui-autocomplete {
				z-index: 1100;
			}
		</style>
	</head>

	<body>
		<div class="container-fluid">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="btn-group pull-right">
						<button type='submit' class="btn btn-info" data-toggle="modal" data-target="#pedidos" onclick="carga_modal();"><span class="glyphicon glyphicon-plus"></span> Nuevo pedido</button>
					</div>
					<h4><i class='glyphicon glyphicon-search'></i> Pedidos</h4>
				</div>
				<div class="panel-body">
					<?php
					include("../modal/pedido.php");
					?>
					<form class="form-horizontal" method="POST">
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
								<input type="hidden" id="ordenado" value="fecha_entrega">
								<input type="hidden" id="por" value="desc">
								<div class="input-group">
									<input type="text" class="form-control" id="q" placeholder="Cliente, Número, Observaciones" onkeyup='load(1);'>
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

		</div>
	<?php

} else {
	header('Location: ../includes/logout.php');
	exit;
}
	?>
	<link rel="stylesheet" href="../css/jquery-ui.css">
	<!--para que se vea con fondo blanco el autocomplete -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="../js/jquery.maskedinput.js" type="text/javascript"></script>
	<script src="../js/ordenado.js" type="text/javascript"></script>
	<script src="../js/notify.js"></script>
	</body>

	</html>
	<script>
		$(document).ready(function() {
			load(1);
		});

		jQuery(function($) {
			$("#fecha_pedido").mask("99-99-9999");
			$("#hora_entrega").mask("99:99");
		});

		function load(page) {
			var q = $("#q").val();
			var ordenado = $("#ordenado").val();
			var por = $("#por").val();
			$("#loader").fadeIn('slow');
			$.ajax({
				url: '../ajax/pedidos.php?action=buscar_pedidos&page=' + page + '&q=' + q + "&ordenado=" + ordenado + "&por=" + por,
				beforeSend: function(objeto) {
					$('#loader').html('<img src="../image/ajax-loader.gif"> Cargando...');
				},
				success: function(data) {
					$(".outer_div").html(data).fadeIn('slow');
					$('#loader').html('');
				}
			})
		}


		function carga_modal() {
			document.querySelector("#titleModal").innerHTML = "<i class='glyphicon glyphicon-ok'></i> Nuevo pedido";
			document.querySelector("#guardar_pedido").reset();
			document.querySelector("#idPedido").value = "";
			document.querySelector("#btnActionForm").classList.replace("btn-info", "btn-primary");
			document.querySelector("#btnText").innerHTML = "<i class='glyphicon glyphicon-floppy-disk'></i> Guardar";
			document.querySelector('#btnActionForm').title = "Guardar pedido";
			$("#muestra_detalle_pedido").fadeIn('fast');
			$.ajax({
				url: "../ajax/pedidos.php?action=nuevo_pedido",
				beforeSend: function(objeto) {
					$("#muestra_detalle_pedido").html("Cargando detalle...");
				},
				success: function(data) {
					$(".outer_divdet_pedido").html(data).fadeIn('fast');
					$('#muestra_detalle_pedido').html('');
					document.getElementById('responsable_traslado').focus();
				}
			});
		}


		//para buscar los clientes
		function buscar_clientes() {
			$("#cliente_pedido").autocomplete({
				source: '../ajax/clientes_autocompletar.php',
				minLength: 2,
				select: function(event, ui) {
					event.preventDefault();
					$('#id_cliente_pedido').val(ui.item.id);
					$('#cliente_pedido').val(ui.item.nombre);
					document.getElementById('observacion_pedido').focus();
				}
			});

			$("#cliente_pedido").on("keydown", function(event) {
				if (event.keyCode == $.ui.keyCode.UP || event.keyCode == $.ui.keyCode.DOWN || event.keyCode == $.ui.keyCode.DELETE) {
					$("#id_cliente_pedido").val("");
					$("#cliente_pedido").val("");
				}
				if (event.keyCode == $.ui.keyCode.DELETE) {
					$("#cliente_pedido").val("");
					$("#id_cliente_pedido").val("");
				}
			});
		}

		//para buscar productos
		function buscar_productos() {
			$("#nombre_producto").autocomplete({
				source: '../ajax/productos_autocompletar_inventario.php',
				minLength: 2,
				select: function(event, ui) {
					event.preventDefault();
					$('#id_producto').val(ui.item.id);
					$('#nombre_producto').val(ui.item.nombre);
					$('#codigo_producto').val(ui.item.codigo);
					document.getElementById('cantidad_agregar').focus();
				}
			});

			//$("#nombre_producto").autocomplete("widget").addClass("fixedHeight"); //para que aparezca la barra de desplazamiento en el buscar

			$("#nombre_producto").on("keydown", function(event) {
				if (event.keyCode == $.ui.keyCode.UP || event.keyCode == $.ui.keyCode.DOWN || event.keyCode == $.ui.keyCode.DELETE) {
					$("#id_producto").val("");
					$("#nombre_producto").val("");
					$("#codigo_producto").val("");
				}
			});

		}

		//agrega un item
		function agregar_item() {
			var id_producto = $("#id_producto").val();
			var cantidad_agregar = $("#cantidad_agregar").val();
			var nombre_producto = $("#nombre_producto").val();
			var codigo_producto = $("#codigo_producto").val();
			//Inicia validacion
			if (id_producto == '') {
				alert('Ingrese producto.');
				document.getElementById('nombre_producto').focus();
				return false;
			}
			if (cantidad_agregar == '') {
				alert('Ingrese cantidad.');
				document.getElementById('cantidad_agregar').focus();
				return false;
			}

			if (isNaN(cantidad_agregar)) {
				alert('El dato ingresado en cantidad, no es un número');
				document.getElementById('cantidad_agregar').focus();
				return false;
			}

			//Fin validacion
			$("#muestra_detalle_pedido").fadeIn('fast');
			$.ajax({
				url: "../ajax/pedidos.php?action=agregar_producto&id_producto=" + id_producto + "&cantidad_agregar=" + cantidad_agregar + "&nombre_producto=" + nombre_producto + "&codigo_producto=" + codigo_producto,
				beforeSend: function(objeto) {
					$("#muestra_detalle_pedido").html("Cargando detalle...");
				},
				success: function(data) {
					$(".outer_divdet_pedido").html(data).fadeIn('fast');
					$('#muestra_detalle_pedido').html('');
					document.getElementById("nombre_producto").value = "";
					document.getElementById("cantidad_agregar").value = "";
					document.getElementById("id_producto").value = "";
					document.getElementById("codigo_producto").value = "";
					document.getElementById('nombre_producto').focus();
				}
			});
		}

		//eliminar un item
		function eliminar_item(id) {
			//Inicia validacion
			if (id == '') {
				alert('Seleccione un producto para eliminar.');
				return false;
			}
			//Fin validacion
			$("#muestra_detalle_pedido").fadeIn('fast');
			$.ajax({
				url: "../ajax/pedidos.php?action=eliminar_detalle_pedido&id=" + id,
				beforeSend: function(objeto) {
					$("#muestra_detalle_pedido").html("Cargando detalle...");
				},
				success: function(data) {
					$(".outer_divdet_pedido").html(data).fadeIn('fast');
					$('#muestra_detalle_pedido').html('');
					document.getElementById("nombre_producto").value = "";
					document.getElementById("cantidad_agregar").value = "";
					document.getElementById("id_producto").value = "";
					document.getElementById("codigo_producto").value = "";
					document.getElementById('nombre_producto').focus();
				}
			});
		}

		//para guardar pedido
		$("#guardar_pedido").submit(function(event) {
			$('#btnActionForm').attr("disabled", true);
			var parametros = $(this).serialize();
			$.ajax({
				type: "POST",
				url: "../ajax/pedidos.php?action=guardar_pedido",
				data: parametros,
				beforeSend: function(objeto) {
					$("#loader_pedido").html("Guardando...");
				},
				success: function(datos) {
					$("#loader_pedido").html(datos);
					$("#loader_pedido").html('');
					$('#btnActionForm').attr("disabled", false);
					load(1);
				}
			});
			event.preventDefault();
		});

		//eliminar pedido
		function eliminar_pedido(id) {
			if (confirm("Realmente desea anular el pedido?")) {
				$.ajax({
					type: "GET",
					url: "../ajax/pedidos.php?action=eliminar_pedido&id="+id,
					beforeSend: function(objeto) {
						$("#resultados").html("Mensaje: Cargando...");
					},
					success: function(datos) {
						$("#resultados").html(datos);
						load(1);
					}
				});
			}
		}

		function editar_pedido(id) {
			document.querySelector('#titleModal').innerHTML ="<i class='glyphicon glyphicon-edit'></i> Actualizar pedido";
			document.querySelector("#guardar_pedido").reset();
			document.querySelector("#idPedido").value = id;
			document.querySelector('#btnActionForm').classList.replace("btn-primary", "btn-info");
			document.querySelector("#btnText").innerHTML = "<i class='glyphicon glyphicon-floppy-disk'></i> Guardar";

			var fecha_entrega = $("#fecha_entrega_mod" + id).val();
			var responsable = $("#responsable_mod" + id).val();
			var hora_entrega = $("#hora_entrega_mod" + id).val();
			var id_cliente = $("#id_cliente_mod" + id).val();
			var cliente = $("#cliente_mod" + id).val();
			var observaciones = $("#observaciones_mod" + id).val();
			var status = $("#status_mod" + id).val();

			$("#fecha_pedido").val(fecha_entrega);
			$("#responsable_traslado").val(responsable);
			$("#hora_entrega").val(hora_entrega);
			$("#id_cliente_pedido").val(id_cliente);
			$("#cliente_pedido").val(cliente);
			$("#observacion_pedido").val(observaciones);
			$("#listStatus").val(status);

			$("#muestra_detalle_pedido").fadeIn('fast');
			$.ajax({
				url: "../ajax/pedidos.php?action=editar_pedido&id="+id,
				beforeSend: function(objeto) {
					$("#muestra_detalle_pedido").html("Cargando detalle...");
				},
				success: function(data) {
					$(".outer_divdet_pedido").html(data).fadeIn('fast');
					$('#muestra_detalle_pedido').html('');
				}
			});

		}

		function detalle_pedido(id) {
			$("#detalle_pedido").fadeIn('fast');
			$.ajax({
				url: "../ajax/pedidos.php?action=detalle_pedido&id="+id,
				beforeSend: function(objeto) {
					$("#detalle_pedido").html("Cargando detalle...");
				},
				success: function(data) {
					$(".outer_detalle_pedido").html(data).fadeIn('fast');
					$('#detalle_pedido').html('');
				}
			});

		}
	</script>