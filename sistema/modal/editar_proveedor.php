	<!-- Modal -->
	<div class="modal fade" id="editarProveedor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-edit'></i> Editar proveedor</h4>
		  </div>
		  <div class="modal-body">
			<form class="form-horizontal" method="post" id="editar_proveedor" name="editar_proveedor">
						<div id="resultados_ajax_editar"></div>
						<div class="form-group">
							<label class="col-sm-2 control-label">Tipo id</label>
							<div class="col-sm-4">	
							 <?php	$conexion = conenta_login(); ?>
							 <select class="form-control" id="tipo_id_mod" name="tipo_id_mod" readonly>
									<?php
									$sql = "SELECT * FROM iden_comprador ;";
									$respuesta = mysqli_query($conexion,$sql);
									while($datos_comprador = mysqli_fetch_assoc($respuesta)){
									?>	
									<option value="<?php echo $datos_comprador['codigo'] ?>" ><?php echo $datos_comprador['nombre'] ?></option> 
									<?php 
									}
									?>
							  </select>
							</div>
							<label class="col-sm-2 control-label">Ruc/cedula</label>
							<div class="col-sm-4">
							<input type="text" class="form-control" id="ruc_proveedor_mod" name="ruc_proveedor_mod" placeholder="Ruc - cédula" readonly>
							</div>
						  </div>
						  <div class="form-group">
							<label class="col-sm-3 control-label">Razón social</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="razon_social_mod" name="razon_social_mod" placeholder="Razón social" required>
							  <input type="hidden" id="mod_id_proveedor" name="mod_id_proveedor" >
							</div>
						  </div>
						  <div class="form-group">
							<label class="col-sm-3 control-label">Nombre com.</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="nombre_comercial_mod" name="nombre_comercial_mod" placeholder="Nombre comercial">
							</div>
						  </div>
						  <div class="form-group">
								<label class="col-sm-3 control-label control-label">Tipo</label>
								<div class="col-sm-8">
								<select class="form-control" name="tipo_empresa_mod" id="tipo_empresa_mod">
									<?php
									$conexion = conenta_login();
										$sql = "SELECT * FROM tipo_empresa ;";
										$res = mysqli_query($conexion,$sql);
									?> <option value="">Seleccione tipo empresa</option>
									 <?php
										while($o = mysqli_fetch_assoc($res)){
									?>
											<option value="<?php echo $o['codigo'] ?>"><?php echo $o['nombre'] ?> </option>
											<?php
										}
									?>
								</select>
								</div>
							</div>
						  
						  <div class="form-group">
							<label class="col-sm-3 control-label">Teléfonos</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="telf_proveedor_mod" name="telf_proveedor_mod" placeholder="Teléfono">
							</div>
						  </div>
						  
						  <div class="form-group">
							<label  class="col-sm-3 control-label">Email</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="mail_proveedor_mod" name="mail_proveedor_mod" placeholder="email">
							</div>
							<a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a>
						  </div>
						  
						  <div class="form-group">
							<label class="col-sm-3 control-label">Dirección</label>
							<div class="col-sm-8">
								<input class="form-control" id="dir_proveedor_mod" name="dir_proveedor_mod" placeholder="Dirección"  maxlength="255" required>
							</div>
						  </div>
						  <div class="form-group">
						  <label class="col-sm-3 control-label">Plazo pago</label>
							<div class="col-sm-2">
								<input class="form-control" id="plazo_mod" name="plazo_mod">
							</div>
							<label for="tiempo" class="col-sm-3 control-label">Unidad de tiempo</label>
							<div class="col-sm-3">
							 <select class="form-control" id="unidad_tiempo_mod" name="unidad_tiempo_mod" >
								<option value="Días">Días</option>
								<option value="Meses">Meses</option>
								<option value="Años">Años</option>
							  </select>
							</div>
						  </div>
						  
					  </div>
					  <div class="modal-footer">
						<button type="button" class="btn btn-default" id="cerrar_editar" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn btn-primary" id="guardar_datos">Guardar</button>
					  </div>
		  </form>
		</div>
	  </div>
	</div>
	