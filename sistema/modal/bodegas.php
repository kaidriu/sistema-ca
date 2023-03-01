<!-- Modal -->
<div id="bodegas" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title" id="TituloModal"></h4>
      </div>
      <div class="modal-body">
	  <form class="form-horizontal"  method="post" name="guarda_bodega" id="guarda_bodega" >
	  <div id="resultados_ajax_bodegas"></div>
	  <div class="form-group">
            <label class="col-sm-4 control-label">Nombre bodega</label>
            <div class="col-sm-6">
			  <input type="hidden" name="id_bodega" id="id_bodega">
              <input type="text" name="nombre_bodega" class="form-control" id="nombre_bodega" value="" placeholder="Nombre">
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
