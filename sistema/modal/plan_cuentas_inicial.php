<!-- Modal <div class="col-md-10 col-md-offset-2"> -->

<div id="NuevaCuentaContable" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title"><i class='glyphicon glyphicon-edit'></i> Plan cuentas inicial</h4>
      </div>
	  
      <div class="modal-body">
	  <form class="form-horizontal"  method="post" name="guardar_plan_inicial" id="guardar_plan_inicial">
	  <div id="resultados_ajax_cuentas"></div>

		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="1" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="ACTIVOS"readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="2" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="PASIVOS"readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="3" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="PATRIMONIO"readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="4" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="INGRESOS"readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="5" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="COSTOS"readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="6" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="GASTOS"readonly>
			  </div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Código</b></span>
					<input type="text" class="form-control text-left" value="7" readonly>
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Cuenta</b></span>
					<input type="text" class="form-control text-left" value="RESUMEN RESULTADOS"readonly>
			  </div>
			</div>
		</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
		<button type="submit" class="btn btn-primary" id="guardar_datos">Crear</button>
      </div>
	   </form>
    </div>

  </div>
</div> 