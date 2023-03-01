<div class="modal fade" id="nuevo_cambio_producto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="overflow-y: scroll;">
<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				  <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-refresh'></i> Cambio de productos facturados</h4>
				</div>
		<form  method="POST" id="guardar_cambio_productos" name="guardar_cambio_productos">
			<div class="modal-body">
				<div id="mensajes_cambio_productos"></div>
				<div class="well well-sm" style="margin-bottom: 5px; margin-top: -10px;">
				<div class="panel-body" style="margin-bottom: -15px; margin-top: -15px;">
						<div class="col-sm-3">
							<div class="form-group">
							<div class="input-group">								
							<span class="input-group-addon"><b>Fecha</b></span>
								<input type="text" class="form-control input-sm text-center" name="fecha_cambio_producto" id="fecha_cambio_producto" value="<?php echo date("d-m-Y");?>">					
							</div>
							</div>
						</div>
						
						<div class="col-sm-3">
							<div class="form-group">
							<div class="input-group">								
							<span class="input-group-addon"><b>Desde</b></span>
							<select class="form-control" style="height:30px;" title="Opción de cambio" name="cambio" id="cambio" >
								<option value="F" selected>Factura</option>
								<option value="R">Re-cambio</option>
							</select>		
							</div>
							</div>
						</div>
												
						<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Cliente</b></span>
						  <input type="hidden" name="id_cliente_cambio" id="id_cliente_cambio">
							<input type="text" class="form-control input-sm" name="cliente_cambio" id="cliente_cambio" autocomplete="off" onkeyup='buscar_clientes();'> 			
						</div>
						</div>
						</div>
						<div class="col-sm-8">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Producto</b></span>
						  <input type="hidden" name="id_registro" id="id_registro">
							<input type="text" class="form-control input-sm" name="producto_facturado" id="producto_facturado" autocomplete="off" onkeyup='buscar_producto_facturado();'> 			
						</div>
						</div>
						</div>
						<div class="col-sm-3">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Cantidad</b></span>
						  <input type="hidden" name="cantidad_registrada" id="cantidad_registrada">
							<input type="text" class="form-control input-sm" name="cantidad_facturado" id="cantidad_facturado" > 			
						</div>
						</div>
						</div>
						<div class="col-sm-1">
							<div class="form-group">
							<button type="button" class="btn btn-info btn-sm" title="Agregar producto" onclick="agregar_detalle_productos()"><span class="glyphicon glyphicon-plus"></span></button>
							</div>
						</div>
						<div class="col-sm-9">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Observaciones</b></span>
							<input type="text" class="form-control input-sm" name="observaciones" id="observaciones" autocomplete="off"> 			
						</div>
						</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
							<div class="input-group">								
							<span class="input-group-addon"><b>Serie</b></span>
							<select class="form-control" style="height:30px;" title="Seleccione serie." name="serie_factura" id="serie_factura" >
								<?php
								$conexion = conenta_login();
								$sql = "SELECT * FROM sucursales WHERE ruc_empresa ='".$ruc_empresa."'";
								$res = mysqli_query($conexion,$sql);
								while($o = mysqli_fetch_array($res)){
								?>
								<option value="<?php echo $o['serie']?>" selected><?php echo strtoupper($o['serie'])?></option>
								<?php
								}
								?>
							</select>		
							</div>
							</div>
						</div>
						
						
				</div>
					
				</div>
						<div id="muestra_detalle_cambio_productos"></div><!-- Carga gif animado -->
						<div class="outer_div_cambio_producto" ></div><!-- Datos ajax Final -->
			</div>
				<div class="modal-footer">
				<span id="loader_cambio_producto"></span>
						<button type="button" class="btn btn-default" data-dismiss="modal" >Cerrar</button>
						<button type="submit" class="btn btn-info" id="guardar_datos">Guardar</button>
				</div>
		</form>
	</div>
</div>
</div>

