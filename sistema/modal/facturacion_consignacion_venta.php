<?php
//para traer la serie de la sucursal primera
$con = conenta_login();
$ruc_empresa = $_SESSION['ruc_empresa'];
ini_set('date.timezone','America/Guayaquil');
?>
<div class="modal fade" id="facturacion_consignacion_venta" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="overflow-y: scroll;">
<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				  <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-list-alt'></i> Facturación de consignación en ventas</h4>
				</div>
			<div class="modal-body">
	<form  method="POST" id="guardar_facturacion_consignacion_venta" name="guardar_facturacion_consignacion_venta">
				<div id="mensajes_facturacion_consignacion_venta"></div>
				<div class="well well-sm" style="margin-bottom: 5px; margin-top: -10px;">
				<div class="panel-body" style="margin-bottom: -25px; margin-top: -15px;">
						<div class="col-sm-3">
							<div class="form-group">
							<div class="input-group">								
							<span class="input-group-addon"><b>Fecha</b></span>
								<input type="text" class="form-control input-sm text-center" name="fecha_factura_consignacion_venta" id="fecha_factura_consignacion_venta" value="<?php echo date("d-m-Y");?>">					
							</div>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
							<div class="input-group">								
							<span class="input-group-addon"><b>Serie</b></span>
							<select class="form-control" style="height:30px;" title="Seleccione serie." name="serie_factura_consignacion" id="serie_factura_consignacion" >
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
						<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Cliente</b></span>
							<input type="hidden" name="id_cliente_factura_consignacion_venta" id="id_cliente_factura_consignacion_venta">
							<input type="text" class="form-control input-sm" name="cliente_factura_consignacion_venta" id="cliente_factura_consignacion_venta" onkeyup='buscar_clientes();' autocomplete="off"> 			
						</div>
						</div>
						</div>
						
						<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Info Adicional</b></span>
							<input type="text" class="form-control input-sm" name="adi_concepto" id="adi_concepto" placeholder="Concepto"> 			
							<input type="text" class="form-control input-sm" name="adi_detalle" id="adi_detalle" placeholder="Detalle" > 			
						</div>
						</div>
						</div>

						<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Asesor</b></span>
								<select class="form-control input-sm" name="vendedor" id="vendedor">
								<option value="0" selected>Ninguno</option>
										<?php
										$vendedores = mysqli_query($con, "SELECT * FROM vendedores WHERE ruc_empresa ='".$ruc_empresa."' order by nombre asc ");
										while ($row_vendedores = mysqli_fetch_assoc($vendedores)) {
											?>
											<option value="<?php echo $row_vendedores['id_vendedor'] ?>" ><?php echo $row_vendedores['nombre'] ?></option>
										<?php
											}
										?>
								</select>
								</div>
								<div class="input-group">
								<span class="input-group-addon"><b>No. CV</b></span>
								  <input style="z-index:inherit;" type="text"  class="form-control input-sm" title="Ingrese No. CV" name="numero_consignacion" id="numero_consignacion" placeholder="No." ><!--onkeyup="limpiar_producto();"-->
								  <span class="input-group-btn btn-md">
								  <button class="btn btn-info btn-sm" type="button" title="Mostrar todo" onclick="mostrar_detalle_numero_consignacion()" data-toggle="modal" data-target="#detalleNumeroConsignacion"><span class="glyphicon glyphicon-search"></span></button>
								  </span>
								</div>					
						</div>
						</div>
					
						<div class="col-sm-12">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Observaciones</b></span>
							<input type="text" class="form-control input-sm" name="observacion_factura_consignacion_venta" id="observacion_factura_consignacion_venta" > 			
						</div>
						</div>
						</div>
						
						
				</div>
				</div>
						<div id="muestra_detalle_facturacion_consignacion"></div><!-- Carga gif animado -->
						<div class="outer_div_facturacion_consignacion" ></div><!-- Datos ajax Final -->
			</div>
				<div class="modal-footer">
				<span id="loader_facturacion"></span>
						<button type="button" class="btn btn-default" onclick="setTimeout(function (){location.href ='../modulos/facturacion_consignacion_venta.php'}, 100);" data-dismiss="modal" id="cerrar_detalle_consignacion">Cerrar</button>
						<button type="submit" class="btn btn-info" id="guardar_datos">Guardar</button>
				</div>
		</form>
	</div>
</div>
</div>


