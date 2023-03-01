<!-- Modal <div class="col-md-10 col-md-offset-2"> -->

<div id="NuevaCuenta" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title"><i class='glyphicon glyphicon-pencil'></i> Nueva cuenta contable</h4>
      </div>
	  
      <div class="modal-body">
	  
	  <form class="form-horizontal"  method="post" name="guardar_nueva_cuenta" id="guardar_nueva_cuenta">
	  <div id="resultados_ajax_guardar_cuentas"></div>
		<div class="form-group">
		<div class="col-sm-12">
		<input type="text" class="form-control text-center" id="mostrar_codigo_cuenta" readonly>
			</div>
		</div>
		<div class="form-group">
		<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Nivel</b></span>
					<input type="text" class="form-control text-center" name="nuevo_nivel_cuenta" id="nuevo_nivel_cuenta" readonly>
				</div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Nuevo Código</b></span>
					<input type="text" class="form-control text-left" name="nuevo_codigo_cuenta" id="nuevo_codigo_cuenta">
			  </div>
			</div>
		</div>
		<div class="form-group">
		<div class="col-sm-6">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código SRI</b></span>
					<input type="text" class="form-control text-left" name="nuevo_codigo_sri" id="nuevo_codigo_sri">
				</div>
			</div>
			<div class="col-sm-6">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código Supercias</b></span>
					<input type="text" class="form-control text-left" name="nuevo_codigo_supercias" id="nuevo_codigo_supercias" >
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Nueva cuenta</b></span>
					<input type="text" class="form-control text-left" name="nuevo_nombre_cuenta" id="nuevo_nombre_cuenta" >
			  </div>
			</div>
		</div>

      </div>
      <div class="modal-footer">
	  <span id="loader_guardar_cuenta"></span>
        <button type="button" class="btn btn-default" data-dismiss="modal" reset>Cerrar</button>
		<button type="submit" class="btn btn-primary" id="guardar_datos">Guardar</button>
      </div>
	   </form>
    </div>

  </div>
</div> 