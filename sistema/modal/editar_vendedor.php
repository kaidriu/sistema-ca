<!-- Modal -->
<div class="modal fade" id="editar_vendedor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-edit'></i> Actualizar vendedor</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" method="POST" id="editar_vendedores" name="editar_vendedores">
				
					<div class="form-group">
					
						<div class="col-sm-6">
						<div class="input-group" >
						<span class="input-group-addon"><b>Tipo id</b></span>
						<select class="form-control" id="mod_tipo_id" name="mod_tipo_id" readonly>
							<option value="05">Cédula</option>
							<option value="06">Pasasporte</option>
				  		</select>
						</div>
						</div>
						<div class="col-sm-6">
						<div class="input-group" >
						<span class="input-group-addon"><b>Cédula/pas</b></span>
						<input type="hidden" name="mod_id_vendedor" id="mod_id_vendedor">
							<input type="text" class="form-control" id="mod_numero_id" name="mod_numero_id" readonly>
						</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
						<div class="input-group" >
						<span class="input-group-addon"><b>Nombre</b></span>
							<input type="text" class="form-control" id="mod_nombre" name="mod_nombre" required>
						</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
						<div class="input-group" >
						<span class="input-group-addon"><b>Email</b></span>
							<input type="text" class="form-control" id="mod_correo" name="mod_correo">
							<span class="input-group-addon"><a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a></span>
						</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-7">
						<div class="input-group" >
						<span class="input-group-addon"><b>Dirección</b></span>
							<input class="form-control" id="mod_direccion" name="mod_direccion">
						</div>
						</div>
						<div class="col-sm-5">
						<div class="input-group" >
						<span class="input-group-addon"><b>Teléfono</b></span>
							<input type="text" class="form-control" id="mod_telefono" name="mod_telefono">
						</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
				<span id="resultados_ajax2"></span>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary" id="actualizar_datos">Actualizar</button>
				</div>
			</form>
		</div>
	</div>
</div>