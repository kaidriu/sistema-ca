<div class="modal fade" id="detalleNumeroConsignacion" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="myModalLabel" style="overflow-y: scroll;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-list-alt'></i> Detalle de consignación</h4>
			</div>
			<div class="modal-body">
				<form method="POST" id="agregar_items_facturacion_consignacion" name="agregar_items_facturacion_consignacion">
					<div class="outer_div_numero_consignacion"></div><!-- Datos ajax Final -->
			</div>
			<div class="modal-footer">
				<span id="loaderdetnumfac"></span><!-- Carga gif animado -->
				<button type="button" class="btn btn-default" data-dismiss="modal" reset>Cerrar</button>
				<button type="button" class="btn btn-info" onclick="agregar_items_factura()" id="btn_agregar_items_factura">Agregar a factura</button>
			</div>
			</form>
		</div>
	</div>
</div>
<script>
	/*
	$("#agregar_items_facturacion_consignacion").submit(function(event) {
		$('#agregar_items_factura').attr("disabled", true);
		var numero_consignacion = $("#numero_consignacion").val();
		var serie_factura = $("#serie_factura_consignacion").val();
		var parametros = $(this).serialize();
		$.ajax({
			type: "POST",
			url: "../ajax/detalle_consignaciones.php?action=agregar_detalle_facturacion_consignacion_venta",
			data: parametros,
			beforeSend: function(objeto) {
				$("#loaderdetnumfac").html("Agregando... ");
			},
			success: function(datos) {
				$("#muestra_detalle_facturacion_consignacion").html(datos);
				$("#loaderdetnumfac").html('');
				$('#agregar_items_factura').attr("disabled", false);
				$("#numero_consignacion").val("");
				$.ajax({
					url: "../ajax/detalle_consignaciones.php?action=muestra_detalle_consignacion_para_facturacion&numero_consignacion=" + numero_consignacion + "&serie_factura=" + serie_factura,
					beforeSend: function(objeto) {
						$("#loaderdetnumfac").html("Cargando...");
					},
					success: function(data) {
						$(".outer_div_numero_consignacion").html(data).fadeIn('fast');
						$('#loaderdetnumfac').html('');
					}
				});
			}
		});
		event.preventDefault();
	});
	*/
</script>