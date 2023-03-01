<div id="NuevoDiarioContable" class="modal fade" data-backdrop="static" role="dialog" >
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" reset>×</button>
        <h4 class="modal-title"><i class='glyphicon glyphicon-edit'></i> Asiento contable</h4>
      </div>
	  
      <div class="modal-body">
	  <form class="form-horizontal" id="form_nuevo_diario">
	  <div id="resultados_ajax_cuentas"></div>
	  <div class="form-group">
			<div class="col-sm-3">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Fecha</b></span>
					<input type="text" class="form-control" id="fecha_diario" name="fecha_diario" tabindex="2" value="<?php echo date("d-m-Y");?>">
			  </div>
			</div>
			<div class="col-sm-9">	
			 <div class="input-group">
				  <span class="input-group-addon"><b>Concepto</b></span>
					<input type="text" class="form-control focusNext" id="concepto_diario" name="concepto_diario" tabindex="3" onkeyup="pasa_concepto();" autofocus>
			  </div>
			</div>
		</div>
		<div class="modal-body" >
				<div class="form-group">
				<input type="hidden" name="codigo_unico" id="codigo_unico">
				<input type="hidden" name="id_cuenta" id="id_cuenta">
				<input type="hidden" name="cod_cuenta" id="cod_cuenta">
					<div class="panel panel-info" style="margin-bottom: 5px; margin-top: -15px;">					
							<table class="table table-bordered">
								<tr class="info">
										<th style ="padding: 2px;">Cuenta</th>
										<th class="text-center" style ="padding: 2px;">Debe</th>
										<th class="text-center" style ="padding: 2px;">Haber</th>
										<th style ="padding: 2px;">Detalle</th>
										<th class="text-center" style ="padding: 2px;">Agregar</th>
								</tr>
								<td class='col-xs-4'>
								<input type="text" class="form-control input-sm focusNext" name="cuenta_diario" id="cuenta_diario" onkeyup='buscar_cuentas();' autocomplete="off" tabindex="4">
								</td>
								<td class='col-xs-2'><input type="text" class="form-control input-sm focusNext" name="debe_diario" id="debe_diario" tabindex="5"></td>
								<td class='col-xs-2'><input type="text" class="form-control input-sm focusNext" name="haber_cuenta" id="haber_cuenta" tabindex="6"></td>
								<td class='col-xs-4'><input type="text" class="form-control input-sm focusNext" name="det_cuenta" id="det_cuenta" tabindex="7"></td>
								<td class='col-xs-1 text-center'><button type="button" class="btn btn-info btn-sm focusNext" title="Agregar detalle de diario" tabindex="8" onclick="agregar_item_diario()"><span class="glyphicon glyphicon-plus"></span></button> </td>
							</table>
					</div>
						<div id="muestra_detalle_diario"></div><!-- Carga gif animado -->
						<div class="outer_divdet" ></div><!-- Datos ajax Final -->
				</div>
		</div>
      </div>
      <div class="modal-footer">
	  <span id="mensaje_nuevo_asiento"></span>
        <button type="button" class="btn btn-default" data-dismiss="modal" reset>Cerrar</button>
		<input type="button" class="btn btn-primary" id="guardar_datos" onclick="guardar_diario();" value="Guardar">
      </div>
	   </form>
    </div>

  </div>
</div> 