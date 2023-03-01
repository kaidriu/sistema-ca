	<!-- Modal -->
	<div class="modal fade" id="nuevoVendedor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-edit'></i> Nuevo vendedor</h4>
			</div>
		<div class="modal-body">
			<form class="form-horizontal" method="POST" id="guardar_vendedor" name="guardar_vendedor">
			<div id="resultados_ajax"></div>
			<div class="form-group">
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Tipo id</b></span>
				 <select class="form-control" id="tipo_id" name="tipo_id" required>
						<option value="05" selected>Cédula</option>
						<option value="06">Pasasporte</option>
				  </select>
				</div>
				</div>
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Cedula/pas</b></span>
				<input type="text" class="form-control" onkeyup="info_contribuyente();" id="cedula" name="cedula" required>
				</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<div class="input-group" >
					<span class="input-group-addon"><b>Nombre</b></span>
					  <input type="text" class="form-control" id="nombre" name="nombre" required>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<div class="input-group" >
						<span class="input-group-addon"><b>Email</b></span>
						<input type="text" class="form-control" id="correo" name="correo" >
					<span class="input-group-addon"><a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a></span>
					</div>
				</div>
			</div>		  
			<div class="form-group">
				<div class="col-sm-7">
				<div class="input-group" >
					<span class="input-group-addon"><b>Dirección</b></span>
					<input class="form-control" id="direccion" name="direccion"   maxlength="255" required>
				</div>
			  </div>
			  <div class="col-sm-5">
				<div class="input-group" >
				<span class="input-group-addon"><b>Teléfono</b></span>
				  <input type="text" class="form-control" id="telefono" name="telefono" >
				</div>
			    </div>

			</div>		  					  
		</div>
				  <div class="modal-footer">
				  <span id="resultados_info_sri"></span>
					<button type="submit" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary" id="guardar_datos">Guardar</button>
				  </div>
			</form>
		</div>
	  </div>
	</div>
	<script>
	function info_contribuyente(){
		var cedula = document.getElementById('cedula').value;
		var info_ruc = "info_ruc";
		if (cedula.length == 10){
			$.ajax({
				type: "POST",
				url: "../clases/info_ruc_sri.php?action=info_ruc",
				data: "numero="+cedula,
				 beforeSend: function(objeto){
					$("#resultados_info_sri").html('Cargando...');
				  },
				success: function(datos){
				$.each(datos, function(i, item) {
					$("#nombre").val(item.nombre);
					$("#direccion").val(item.direccion);
				});
				$("#resultados_info_sri").html('');
				}
			});
		}
	}
	</script>
	