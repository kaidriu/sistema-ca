<div class="modal fade" id="DetalleFacturaProgramada" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				  <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-list-alt'></i> Detalle de factura programada</h4>
				</div>
			<div class="modal-body">
			<input type="hidden" name="id_cliente_pf" id="id_cliente_pf">
			<input type="hidden" name="id_producto" id="id_producto">
					<div class="form-group">
						<div class="table-responsive">
								<div class="panel panel-warning">						
								<table class="table table-bordered">
									<tr  class="warning">
											<th>Producto</th>
											<th>Cantidad</th>
											<th>Precio</th>
											<th>Período</th>
											<th class="text-center">Agregar</th>
									</tr>
									<td class='col-xs-6'>
									<input type="text" class="form-control input-sm" id="nombre_producto_servicio" name="nombre_producto_servicio" placeholder="Producto">
									</td>
									<td class='col-xs-1'><input type="text" class="form-control input-sm" name="cantidad" id="cantidad" value="1"></td>
									<td class='col-xs-2'><input type="text" class="form-control input-sm" name="precio" id="precio" ></td>
									<td class='col-xs-4'>												
									<select class="form-control" id="periodo" name="periodo" required>
											<option value="" Selected>Seleccione período</option>
											<?php
											$con = conenta_login();
											$sql = "SELECT * FROM periodo_a_facturar";
											$respuesta = mysqli_query($con,$sql);
											while($datos_periodo = mysqli_fetch_assoc($respuesta)){
											?>	
											<option value="<?php echo $datos_periodo['codigo_periodo']?>"><?php echo $datos_periodo['detalle_periodo'] ?></option> 
											<?php 
											}
											?>
										</select>
									</td>
									<td class="text-center"><a class='btn btn-info' onclick="agregar_detalle_factura_programada()"><i class="glyphicon glyphicon-plus"></i></a></td>
								</table>
								</div>
						</div>
					</div>
						<div id="muestra_detalle_factura_programada"></div><!-- Carga gif animado -->
						<div class="outer_divdet" ></div><!-- Datos ajax Final -->
			</div>

				<div class="modal-footer">
				   <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
		</div>
</div>
 </div>