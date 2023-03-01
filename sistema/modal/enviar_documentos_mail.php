<div class="modal fade" data-backdrop="static" id="EnviarDocumentosMail" name="EnviarDocumentosMail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog" role="document">
<div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><i class='glyphicon glyphicon-envelope'></i> Enviar documento por mail</h4>
        </div>
        <div class="modal-body">
			<form class="form-horizontal" id="documento_mail" name="documento_mail">
				<div id="resultados_ajax_mail"></div>
				<div class="form-group">
						<label for="" class="col-sm-1 control-label"> Mail</label>
					  <div class="col-sm-10">
					    <div id="mensaje_mail"></div>
						<input type="hidden" id="id_documento" name="id_documento">
						<input type="hidden" id="tipo_documento" name="tipo_documento">
						<input type="text" class="form-control" id="mail_receptor" name="mail_receptor" placeholder="e-mail" required>
					  </div>
					  <a href="#" data-toggle="tooltip" data-placement="top" title="Puede agregar varios correos separados por coma y espacio"><span class="glyphicon glyphicon-question-sign"></span></a>
				</div>		   
		</div>
				<div class="modal-footer">
				   <button type="button" class="btn btn-default" id="cerrar_mail" data-dismiss="modal">Cerrar</button>
				   <button type="submit" class="btn btn-primary" id="enviar_mail">Enviar</button>
				</div>
            </form>
	</div>
	</div>
 </div>
