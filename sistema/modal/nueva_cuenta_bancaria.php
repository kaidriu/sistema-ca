
<div class="modal fade" id="nuevoCuentaBancaria" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog modal-md" role="document">
<div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-edit'></i> Nueva cuenta bancaria</h4>
        </div>
        <div class="modal-body">
	<form class="form-horizontal" method="post" id="guardar_cuentas_banco" name="guardar_cuentas_banco">
		<div id="resultados_ajax"></div>
				<div class="form-group">
					<label for="" class="col-sm-4 control-label"> Banco</label>
					<div class="col-sm-5">
					   <select class="form-control" name="banco" id="banco">
									<?php
									$conexion = conenta_login();
										$sql = "SELECT * FROM bancos_ecuador ;";
										$res = mysqli_query($conexion,$sql);
									?> <option value="">Seleccione banco</option>
									 <?php
										while($o = mysqli_fetch_assoc($res)){
									?>
											<option value="<?php echo $o['id_bancos'] ?>"><?php echo $o['nombre_banco'] ?> </option>
											<?php
										}
									?>
								</select>
					</div>
					</div>
					<div class="form-group">
					<label for="" class="col-sm-4 control-label"> Tipo</label>
					<div class="col-sm-5">
					   <select class="form-control" name="tipo_cuenta" id="tipo_cuenta">
					       <option value="" selected> Seleccione cuenta</option>
							<option value="1" >Ahorros</option>
							<option value="2" >Corriente</option>
							<option value="3" >Virtual</option>
							<option value="4" >Tarjeta</option>
						</select>
					</div>
				 </div>
					<div class="form-group">
					<label for="" class="col-sm-4 control-label"> Número</label>
					<div class="col-sm-5">
					<input type="text" class="form-control input-sm" name="numero_cuenta" id="numero_cuenta" >
					</div>
					</div>				
					 
				</div>
				<div class="modal-footer">
				   <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				   <button type="submit" class="btn btn-primary" id="guardar_cuenta_bancaria" >Guardar</button>
				</div>
            </form>
	</div>
	</div>
 </div>
