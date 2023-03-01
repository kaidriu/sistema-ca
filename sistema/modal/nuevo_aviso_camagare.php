	<!-- Modal -->
	<div class="modal fade" id="nuevoAviso" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-edit'></i> Agregar nuevo aviso</h4>
		  </div>
		  <div class="modal-body">
			<form class="form-horizontal" method="post" id="guardar_aviso" name="guardar_aviso">
						<div id="resultados_ajax"></div>

							 <div class="form-group">
							<label class="col-sm-3 control-label">Empresa</label>
							<div class="col-sm-8">		
							 <?php	$conexion = conenta_login(); ?>
							 <select class="form-control" id="empresa" name="empresa" required>
							 <option value="9999999999999" >Todos</option> 
									<?php
									$sql = "SELECT * FROM empresas where estado ='1' order by nombre_comercial asc;";
									$respuesta = mysqli_query($conexion,$sql);
									while($datos_estado = mysqli_fetch_assoc($respuesta)){
									?>	
									<option value="<?php echo $datos_estado['ruc'] ?>" ><?php echo $datos_estado['nombre_comercial'] ?></option> 
									<?php 
									}
									?>
							  </select>
							</div>
						  </div>

						  <div class="form-group">
							<label class="col-sm-3 control-label">Detalle</label>
							<div class="col-sm-8">
							 <textarea class="form-control" rows="5" id="detalle_aviso" name="detalle_aviso"></textarea>
							</div>
						  </div>
					  </div>
					  <div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn btn-primary" id="guardar_datos">Guardar</button>
					  </div>
		  </form>
		</div>
	  </div>
	</div>
	