	<!-- Modal -->
	<div class="modal fade" id="nuevoProveedor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-edit'></i> Agregar nuevo proveedor</h4>
		  </div>
		  <div class="modal-body">
			<form class="form-horizontal" method="post" id="guardar_proveedor" name="guardar_proveedor">
						<div id="resultados_ajax"></div>
						<div class="form-group">
							<label for="estado" class="col-sm-3 control-label">Tipo id</label>
							<div class="col-sm-3">	
							 <?php	$conexion = conenta_login(); ?>
							 <select class="form-control" id="tipo_id" name="tipo_id" required>
									<?php
									$sql = "SELECT * FROM iden_comprador ;";
									$respuesta = mysqli_query($conexion,$sql);
									while($datos_comprador = mysqli_fetch_assoc($respuesta)){
									?>	
									<option value="<?php echo $datos_comprador['codigo'] ?>" ><?php echo $datos_comprador['nombre'] ?></option> 
									<?php 
									}
									?>
							  </select>
							</div>
							<label class="col-sm-1 control-label">Ruc</label>
							<div class="col-sm-4">
							<input type="text" class="form-control" onkeyup="info_contribuyente();" id="ruc_proveedor" name="ruc_proveedor" placeholder="Ruc - cédula" required>
							</div>
						  </div>
						  <div class="form-group">
							<label class="col-sm-3 control-label">Razón social</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="razon_social" name="razon_social" placeholder="Razón social" required>
							</div>
						  </div>
						  <div class="form-group">
							<label class="col-sm-3 control-label">Nombre com.</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" placeholder="Nombre comercial">
							</div>
						  </div>
						  <div class="form-group">
								<label class="col-sm-3 control-label control-label">Tipo</label>
								<div class="col-sm-8">
								<select class="form-control" name="tipo_empresa" id="tipo_empresa">
									<?php
									$conexion = conenta_login();
										$sql = "SELECT * FROM tipo_empresa ;";
										$res = mysqli_query($conexion,$sql);
									?> <option value="">Seleccione tipo empresa</option>
									 <?php
										while($o = mysqli_fetch_assoc($res)){
										?>
											<option value="<?php echo $o['codigo']?>"><?php echo $o['nombre'] ?> </option>
											<?php
										}
									?>
								</select>
								</div>
							</div>
						  <div class="form-group">
							<label class="col-sm-3 control-label">Teléfono</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="telefono_proveedor" name="telefono_proveedor" placeholder="Teléfono">
							</div>
						  </div>
						  <div class="form-group">
							<label  class="col-sm-3 control-label">Email</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="mail_proveedor" name="mail_proveedor" placeholder="email">
							</div>
							<a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a>
						  </div>
						  
						  <div class="form-group">
							<label class="col-sm-3 control-label">Dirección</label>
							<div class="col-sm-8">
								<input class="form-control" id="direccion_proveedor" name="direccion_proveedor" placeholder="Dirección"  maxlength="255" required>
							</div>
						  </div>
						  <div class="form-group">
						  <label for="plazo" class="col-sm-3 control-label">Plazo pago</label>
							<div class="col-sm-2">
							<input class="form-control" id="plazo" name="plazo" value="1">
							</div>
							<label for="tiempo" class="col-sm-3 control-label">Unidad de tiempo</label>
							<div class="col-sm-3">
							 <select class="form-control" id="unidad_tiempo" name="unidad_tiempo" >
								<option value="Días">Días</option>
								<option value="Meses">Meses</option>
								<option value="Años">Años</option>
							  </select>
							</div>
						  </div>
						  <div id="resultados_info_sri"></div>
					  </div>
					  <div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn btn-primary" id="guardar_datos">Guardar</button>
					  </div>
		  </form>
		</div>
	  </div>
	</div>
	<script>
	function info_contribuyente(){
		var ruc = document.getElementById('ruc_proveedor').value;
		var info_ruc = "info_ruc";
		if (ruc.length == 10 || ruc.length == 13){
			$.ajax({
				type: "POST",
				url: "../clases/info_ruc_sri.php?action=info_ruc",
				data: "numero="+ruc,
				 beforeSend: function(objeto){
					$("#resultados_info_sri").html('Cargando información, espere por favor...');
				  },
				success: function(datos){
				$.each(datos, function(i, item) {
					$("#razon_social").val(item.nombre);
					$("#direccion_proveedor").val(item.direccion);
					$("#nombre_comercial").val(item.nombre_comercial);
					$("#tipo_empresa").val(item.tipo);
				});
				$("#resultados_info_sri").html('');
				}
			});
		}
	}
	</script>
	