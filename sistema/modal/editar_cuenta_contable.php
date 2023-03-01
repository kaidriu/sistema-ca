<!-- Modal <div class="col-md-10 col-md-offset-2"> -->

<div id="EditarCuentaContable" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title"><i class='glyphicon glyphicon-edit'></i> Editar cuenta contable</h4>
      </div>
	  
      <div class="modal-body">
	  
	  <form class="form-horizontal"  method="post" name="editar_cuenta" id="editar_cuenta">
	  <div id="resultados_ajax_editar_cuentas"></div>
	<input type="hidden" name="mod_id_cuenta" id="mod_id_cuenta" >
		<div class="form-group">
		<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Nivel</b></span>
					<input type="text" class="form-control text-center" name="mod_nivel_cuenta" id="mod_nivel_cuenta" readonly>
				</div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código cuenta</b></span>
					<input type="text" class="form-control text-left" name="mod_codigo_cuenta" id="mod_codigo_cuenta" readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
		<div class="col-sm-6">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código SRI</b></span>
					<input type="text" class="form-control text-left" name="mod_codigo_sri" id="mod_codigo_sri">
				</div>
			</div>
			<div class="col-sm-6">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código Supercias</b></span>
					<input type="text" class="form-control text-left" name="mod_codigo_supercias" id="mod_codigo_supercias" >
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Nombre cuenta</b></span>
					<input type="text" class="form-control text-left" name="mod_nombre_cuenta" id="mod_nombre_cuenta" >
			  </div>
			</div>
		</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
		<button type="submit" class="btn btn-primary" id="actualizar_datos">Actualizar</button>
      </div>
	   </form>
    </div>

  </div>
</div> 