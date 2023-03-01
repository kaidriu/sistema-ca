	<!-- Modal -->
	<div class="modal fade" data-backdrop="static" id="nuevoCliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="titleModalCliente"></h4>
			</div>
		<div class="modal-body">
			<form class="form-horizontal" method="post" id="guardar_cliente" name="guardar_cliente">
			<input type="hidden" id="id_cliente" name="id_cliente" >
			<div class="form-group">
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Tipo id</b></span>
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
				</div>
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Ruc/cedula</b></span>
				<input type="text" class="form-control" onkeyup="info_contribuyente();" id="ruc" name="ruc" required>
				</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<div class="input-group" >
					<span class="input-group-addon"><b>Nombre</b></span>
					  <input type="text" class="form-control" id="nombre" name="nombre" required>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-12">
					<div class="input-group" >
						<span class="input-group-addon"><b>Email</b></span>
						<input type="text" class="form-control" id="email" name="email" >
					<span class="input-group-addon"><a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a></span>
					</div>
				</div>
			</div>		  
			<div class="form-group">
				<div class="col-sm-12">
				<div class="input-group" >
					<span class="input-group-addon"><b>Dirección</b></span>
					<input type="text" class="form-control" id="direccion" name="direccion"   maxlength="255" required>
				</div>
			  </div>
			</div>		  
			<div class="form-group">
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Teléfono</b></span>
				  <input type="text" class="form-control" id="telefono" name="telefono" >
				</div>
			    </div>			
				 <div class="col-sm-6">
					<div class="input-group" >
						<span class="input-group-addon"><b>Plazo de crédito en días</b></span>
						<input type="number" class="form-control" id="plazo" name="plazo" value="1">
					</div>
				 </div>
			</div>
			<div class="form-group">
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Provincia</b></span>
				 <select class="form-control" id="provincia" name="provincia">
				 <?php				
					$sql = "SELECT * FROM provincia order by nombre asc;";
					$res = mysqli_query($conexion,$sql);
					?> 
					<option value="">Seleccione</option>
					<?php
					while($p = mysqli_fetch_assoc($res)){
							?>
						<option value="<?php echo $p['codigo']; ?>"><?php echo $p['nombre']; ?> </option>
						<?php
						}
				?>
				  </select>
				</div>
				</div>
				<div class="col-sm-6">
				<div class="input-group" >
				<span class="input-group-addon"><b>Ciudad</b></span>
				 <select class="form-control" id="ciudad" name="ciudad">
					 <?php
						$res = mysqli_query($conexion,"SELECT * FROM ciudad order by nombre asc");
						?> 
						<option value="">Seleccione</option>
						<?php
						while($c = mysqli_fetch_assoc($res)){
						?>
						<option value="<?php echo $c['codigo'];?>"><?php echo $c['nombre'];?> </option>
						<?php
						}
					?>
				  </select>
				</div>
				</div>
				
			</div>					  
		</div>
				  <div class="modal-footer">
				  <span id="resultados_info_sri"></span>
				  <span id="resultados_ajax_guarda_cliente"></span>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-primary" onclick="guarda_cliente();" id="btnActionFormCliente"><span id="btnTextCliente"></span></button>
				  </div>
			</form>
		</div>
	  </div>
	</div>
	<script>
	function info_contribuyente(){
		var ruc = document.getElementById('ruc').value;
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
					$("#nombre").val(item.nombre);
					$("#direccion").val(item.direccion);
					$("#provincia").val(item.codigo_provincia);
					$("#ciudad").val(item.codigo_ciudad);
				});
				$("#resultados_info_sri").html('');
				}
			});
		}
	}

	function guarda_cliente() {
        $('#btnTextCliente').attr("disabled", true);
        var id_cliente = $("#id_cliente").val();
        var tipo_id = $("#tipo_id").val();
        var email = $("#email").val();
        var direccion = $("#direccion").val();
        var telefono = $("#telefono").val();
        var plazo = $("#plazo").val();
        var provincia = $("#provincia").val();
        var ciudad = $("#ciudad").val();
		var ruc = $("#ruc").val();
		var nombre = $("#nombre").val();

        $.ajax({
            type: "POST",
            url: "../ajax/clientes.php?action=guardar_cliente",
            data: "id_cliente=" + id_cliente + "&tipo_id=" + tipo_id +
                "&email=" + email + "&direccion=" + encodeURIComponent(direccion) +
                "&telefono=" + telefono + "&plazo=" +
                plazo + "&provincia=" + provincia + "&ciudad=" +
                ciudad + "&ruc=" + ruc + "&nombre="+encodeURIComponent(nombre),
            beforeSend: function(objeto) {
                $("#resultados_ajax_guarda_cliente").html("Guardando...");
            },
            success: function(datos) {
                $("#resultados_ajax_guarda_cliente").html(datos);
                $('#btnTextCliente').attr("disabled", false);
            }
        });
        event.preventDefault();
    }

	/*
$( "#guardar_cliente" ).submit(function( event ) {
  $('#guardar_datos').attr("disabled", true);
 var parametros = $(this).serialize();
	 $.ajax({
			type: "POST",
			url: "../ajax/clientes.php?action=guardar_cliente",
			data: parametros,
			 beforeSend: function(objeto){
				$("#resultados_ajax").html("Guardando...");
			  },
			success: function(datos){
			$("#resultados_ajax").html(datos);
			$('#guardar_datos').attr("disabled", false);
			load(1);
		  }
	});
  event.preventDefault();
})
*/
	</script>
	