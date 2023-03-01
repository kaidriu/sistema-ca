<?php
//para traer la serie de la sucursal primera
$con = conenta_login();
$ruc_empresa = $_SESSION['ruc_empresa'];
//$busca_sucursales = mysqli_query($con, "SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' ");
//$row_serie = mysqli_fetch_array($busca_sucursales);
?>
<div class="modal fade" data-backdrop="static" id="detalleFacturaMecanica" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="load(1);"><span aria-hidden="true">&times;</span></button>
				  <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-list-alt'></i> Detalles de factura <span id="mensajes_ordenes_mecanica" ></span></h4>
				</div>
		<form  method="POST" >
			<div class="modal-body">
				<div class="well well-sm" style="margin-bottom: 5px; margin-top: -10px;">
				<div class="panel-body" style="margin-bottom: 5px; margin-top: -15px;">
						<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
						  <span class="input-group-addon"><b>Cliente</b></span>
							<input type="hidden" name="id_cliente_mecanica" id="id_cliente_mecanica">
							<input type="text" class="form-control input-sm" name="cliente_mecanica" id="cliente_mecanica" onkeyup='buscar_clientes();' autocomplete="off"> 			
						</div>
						</div>
						</div>
						<div class="col-sm-3">
						<div class="form-group">
						<div class="input-group">								
						<span class="input-group-addon"><b>Fecha</b></span>
							<input type="text" class="form-control input-sm text-center" name="fecha_mecanica" id="fecha_mecanica" value="<?php echo date("d-m-Y");?>">					
						</div>
						</div>
						</div>
						<div class="col-sm-2">
						<div class="form-group">
						<div class="input-group">								
						<span class="input-group-addon"><b>Serie</b></span>
							<select class="form-control" style="text-align:right; width: auto; height:30px;" title="Seleccione serie." name="serie_mecanica" id="serie_mecanica" >
								<?php
								$conexion = conenta_login();
								$sql = mysqli_query($conexion, "SELECT * FROM sucursales WHERE ruc_empresa ='".$ruc_empresa."'");
								while($o = mysqli_fetch_array($sql)){
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
					
					<div class="form-group" >
							<div class="panel panel-info" style="margin-bottom: -15px; margin-top: -20px;">
							<div class="table-responsive">							
							<table class="table table-bordered">
								<tr class="info">
								<th style ="padding: 2px;" >Producto-servicio</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_bodega">Bodega</th>
								<th style ="padding: 2px;" class="text-center">Cantidad</th>
								<th style ="padding: 2px;" class="text-center">Precio sin IVA</th>
								<th style ="padding: 2px;" class="text-center">Precio con IVA</th>
								<th style ="padding: 2px;" class="text-center" id="titulo_existencia">Existencia</th>
								<th style ="padding: 2px;" class="text-center">Agregar</th>
								</tr>
								<input type="hidden" name="codigo_unico_factura" id="codigo_unico_factura">
								<input type="hidden" name="id_producto_mecanica" id="id_producto_mecanica">
								<input type="hidden" name="precio_tmp" id="precio_tmp">
								<input type="hidden" name="tipo_producto_mecanica" id="tipo_producto_mecanica" >
								<input type="hidden" id="inventario" name="inventario" value="NO">
								<input type="hidden" id="muestra_bodega" name="muestra_bodega">
								<input type="hidden" id="medida_agregar" name="medida_agregar">
								<input type="hidden" id="porcentaje_iva" >
								<td class='col-xs-5'>
								<input type="text" class="form-control input-sm" id="nombre_producto_servicio" name="nombre_producto_servicio" placeholder="Producto o servicio" onkeyup="buscar_productos();">
								</td>
								<td class='col-xs-1' id="lista_bodega">
								  <select class="form-control" style="text-align:right; width: auto; height:30px;" title="Seleccione bodega." name="bodega_agregar" id="bodega_agregar" >
									<option value="0" >Seleccione</option>
										<?php
										$conexion = conenta_login();
										$sql = "SELECT * FROM bodega WHERE ruc_empresa ='".$ruc_empresa."'";
										$res = mysqli_query($conexion,$sql);
										while($o = mysqli_fetch_array($res)){
										?>
										<option value="<?php echo $o['id_bodega']?>" selected><?php echo strtoupper($o['nombre_bodega'])?></option>
										<?php
										}
										?>
									</select>
								</td>
								<td class='col-xs-1'>
								<div class="pull-right">
								  <input type="text" class="form-control input-sm" style="text-align:right;" title="Ingrese cantidad" name="cantidad_agregar" id="cantidad_agregar" placeholder="Cant" >
								</div>
								</td>
								<td class='col-xs-2'>
									<input type="text" style="text-align:right;" class="form-control input-sm" id="precio_agregar" name="precio_agregar" oninput="precio_sin_iva();">
								</td>
								<td class='col-xs-2'>
									<input type="text" style="text-align:right;" class="form-control input-sm" id="precio_agregar_iva" name="precio_agregar_iva" oninput="precio_con_iva();">
								</td>
								<td class="col-xs-2" id="lista_existencia">
								  <input type="text" style="text-align:right;" class="form-control input-sm" id="existencia_producto" name="existencia_producto" placeholder="0" >
								</td>
								<td class="col-sm-1" style="text-align:center;">
								<button type="button" class="btn btn-info btn-sm" title="Agregar productos" onclick="agregar_item_factura_mecanica()"><span class="glyphicon glyphicon-plus"></span></button>
								</td>
							</table>
							</div>
							</div>
					</div>
				</div>
						
						<div class="outer_divdet_mecanica" ></div><!-- Datos ajax Final -->
			</div>
				<div class="modal-footer">
						<span id="muestra_detalle_mecanica"></span><!-- Carga gif animado -->
						<button type="button" onclick ="generar_factura();" class="btn btn-success" id="guardar_datos">Generar factura</button>
						<button type="button" onclick ="generar_recibo();" class="btn btn-info" id="guardar_datos_recibo">Generar recibo</button>
						<button type="button" class="btn btn-default" data-dismiss="modal" onclick="load(1);">Cerrar</button><!--id="cerrar_detalle_factura_mecanica"-->
				</div>
		</form>
		
	</div>
</div>
</div>

