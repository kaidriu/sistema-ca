<!-- Modal -->
<div id="EditarSalida" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title"><i class='glyphicon glyphicon-edit'></i> Editar Salidas inventario</h4>
      </div>
      <div class="modal-body">
	  <form class="form-horizontal"  method="post" name="editar_salida" id="editar_salida">
	  <div id="resultados_ajax_editar_salidas"></div>
	  <div class="form-group">
            <label class="col-sm-3 control-label">Producto</label>
            <div class="col-sm-8">
			<input type="hidden" name="mod_id_inventario" id="mod_id_inventario">
			<input type="hidden" name="mod_codigo_producto" id="mod_codigo_producto">
			<input type="hidden" name="mod_id_producto" id="mod_id_producto">
              <input type="text" name="mod_nombre_producto" class="form-control" id="mod_nombre_producto" value="" placeholder="Escribir para buscar un producto" readonly>
            </div>
          </div>
		  <div class="form-group">
            <label class="col-sm-3 control-label">Caducidad</label>
            <div class="col-sm-4">
              <input type="text" name="mod_fecha_caducidad" class="form-control" id="mod_fecha_caducidad" value="<?php echo date("d-m-Y");?>" placeholder="Fecha caducidad" readonly>
            </div>
            <label class="col-sm-1 control-label">Lote</label>
            <div class="col-sm-3">
              <input type="text" name="mod_lote" class="form-control" id="mod_lote" readonly>
            </div>
          </div>
		  <div class="form-group">
            <label for="quantity" class="col-sm-3 control-label">Bodega</label>
            <div class="col-sm-4">
             <?php
				$conexion = conenta_login();
					?>
						<select class="form-control" name="mod_bodega" id="mod_bodega" readonly>
					<?php
						$sql = "SELECT * FROM bodega where ruc_empresa='$ruc_empresa';";
						$res = mysqli_query($conexion,$sql);
					?> <option value="">Seleccione</option>
					 <?php
						while($o = mysqli_fetch_assoc($res)){
					?>
						<option value="<?php echo $o['id_bodega'] ?>"><?php echo strtoupper ($o['nombre_bodega']) ?> </option>
						<?php
						}
					?>
					</select>
            </div>

            <label for="quantity" class="col-sm-1 control-label">Medida</label>
            <div class="col-sm-3">
			<input type="text" name="mod_unidad_medida" class="form-control" id="mod_unidad_medida" readonly>
            </div>
			</div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Fecha registro</label>
            <div class="col-sm-4">
              <input type="text" name="mod_fecha_salida" class="form-control" id="mod_fecha_salida" value="<?php echo date("d-m-Y");?>" placeholder="Fecha salida" required>
            </div>
          </div>
		
          <div class="form-group">
            <label for="quantity" class="col-sm-3 control-label">Cantidad</label>
            <div class="col-sm-4">
              <input type="text" name="mod_cantidad" class="form-control" id="mod_cantidad" value="" placeholder="Cantidad" required>
            </div>
          </div>
		  <div class="form-group">
            <label for="quantity" class="col-sm-3 control-label">Precio venta</label>
            <div class="col-sm-4">
              <input type="text" name="mod_precio_producto" class="form-control" id="mod_precio_producto" value="" placeholder="Precio" required>
            </div>
          </div>
		 
          
          <div class="form-group">
            <label for="reference" class="col-sm-3 control-label">Referencia</label>
            <div class="col-sm-8">
              <input type="text" name="mod_referencia" class="form-control" id="mod_referencia" value="" placeholder="Referencia">
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

