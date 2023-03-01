<div class="modal fade" data-backdrop="static" id="AnularDocumentosSri" name="AnularDocumentosSri" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	          <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-trash'></i> Anulación de comprobantes</h4>
	        </div>
	        <div class="modal-body">
				<form class="form-horizontal" id="anular_documento_sri" name="anular_documento_sri">
					<div class="col-sm-12">
						<div id="resultados_ajax_anular"></div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-12 text-center">
							<a href="https://srienlinea.sri.gob.ec/auth/realms/Internet/protocol/openid-connect/auth?response_type=code&client_id=app-tuportal-internet&redirect_uri=https%3A%2F%2Fsrienlinea.sri.gob.ec%2Ftuportal-internet%2FverificaEmail.jspa&state=40ce4e57-0f21-4443-8bc0-bf4fa970bc89&login=true&scope=openid" title="link anular documentos SRI" target="_blank" ><h4><span class="glyphicon glyphicon-hand-right"></span><b> Click aquí para acceder al SRI. <span class="glyphicon glyphicon-hand-left"></span></h4></b></a>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*Tipo de comprobante</span>
								<input class="form-control" style ="height: 30px;" id="tipo_comprobante" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*Fecha autorización</span>
								<input class="form-control" style ="height: 30px;" id="fecha_autorizacion" readonly>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*Clave acceso</span>
								<input class="form-control" style ="height: 30px;" id="clave_accesso" readonly>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*No. Autorización</span>
								<input class="form-control" style ="height: 30px;" id="numero_autorizacion" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*Identificación receptor</span>
								<input class="form-control" style ="height: 30px;" id="ruc_receptor" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*Correo electrónico receptor</span>
								<input class="form-control" style ="height: 30px;" id="correo_receptor" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<div class="input-group" >
							<span class="input-group-addon">*Estado SRI</span>
								<input class="form-control" style ="height: 30px;" id="estado_sri_consultado" name="estado_sri_consultado"readonly>
							</div>
						</div>
					</div>
					<input type="hidden" id="id_documento_modificar" name="id_documento_modificar">				
			</div>
			   
			<div class="modal-footer">
			<span id="resultados_anular"></span>
			   <button type="button" class="btn btn-default" id="btnCerrar" data-dismiss="modal" onclick="load(1);" reset>Cerrar</button>
			   <button type="submit" class="btn btn-primary" id="anular_sri">Anular</button>
			</div>
	            </form>
		</div>
	</div>
</div>
