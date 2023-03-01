	<!-- Modal -->
	<div class="modal fade" id="nuevoProveedorRetencion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
							<div class="col-sm-8">
										
							 <?php	$conexion = conenta_login(); ?>
							 <select class="form-control" id="tipo_id" name="tipo_id" required>
									<?php
									$sql = "SELECT * FROM iden_comprador;";
									$respuesta = mysqli_query($conexion,$sql);
									while($datos_comprador = mysqli_fetch_assoc($respuesta)){
									?>	
									<option value="<?php echo $datos_comprador['codigo'] ?>" ><?php echo $datos_comprador['nombre'] ?></option> 
									<?php 
									}
									?>
							  </select>
							</div>
						  </div>
						<div class="form-group">
							<label for="nombre" class="col-sm-3 control-label">Ruc/cedula</label>
							<div class="col-sm-4">
							<input type="text" class="form-control" onkeyup="info_contribuyente();" id="ruc_proveedor" name="ruc_proveedor" required>
							</div>
							<div class="col-sm-4">
							<div id="resultados_info_sri"></div>
							</div>
						  </div>
						  <div class="form-group">
							<label for="nombre" class="col-sm-3 control-label">Razón social</label>
							<div class="col-sm-8">
							  <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor" required>
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
											<option value="<?php echo $o['codigo'] ?>"><?php echo $o['nombre'] ?> </option>
											<?php
										}
									?>
								</select>
								</div>
							</div>						  				  
						  <div class="form-group">
							<label for="email_proveedor" class="col-sm-3 control-label">Email</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="email_proveedor" name="email_proveedor" value="info@camagare.com" >
							</div>
							<a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a>
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
					$("#resultados_info_sri").html('Cargando...');
				  },
				success: function(datos){
				$.each(datos, function(i, item) {
					$("#nombre_proveedor").val(item.nombre);
					$("#tipo_empresa").val(item.tipo);
				});
				$("#resultados_info_sri").html('');
				}
			});
		}
	}
	</script>
	