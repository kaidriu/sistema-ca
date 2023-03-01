

<?php
include("../sistema/conexiones/conectalogin.php");
function form_entrada(){
?>
<br>
<br>
<br>
<div class="row">
<div class="container">
<div class="col-sm-6 col-sm-offset-3">
<div class="panel panel-info">
<div class="panel-heading">
<div class="btn-group pull-center">
<div class="panel-title"><h4><span class="glyphicon glyphicon-list-alt"></span></span> Consulta de documentos electrónicos.</h4></div>
</div>
	</div>

			<div style="padding-top:30px" class="panel-body">
				<form class="form-horizontal" method="post" id="log" autocomplete="off">
					<div class="form-group">
						<div class="col-xs-2"></div>
						<div class="col-xs-6">
						<div class="input-group">
					  <span class="input-group-addon" ><span class="glyphicon glyphicon-user"></span></span>
							<input class="form-control" placeholder="Cedula/ruc/pasaporte"  id="cedula" title="Cedula" name="cedula" type="text" autofocus="" required>
						</div>
						</div>
					</div>
						<div class="form-group">
						<div class="col-xs-2"></div>
							<div class="col-sm-6">				
							 <div class="g-recaptcha" data-sitekey="6LdJLE0UAAAAAC6nmvCHnevGLAoRLwlq28XJdkXp"></div>
							 </div>
						 </div>
						 <div class="form-group">
						 <div class="col-xs-2"></div>
						<div class="col-xs-2">
							<button type="submit" class="btn btn-info " name="entrada" id="submit">Ingresar</button>	
						</div>
						 </div>
					
					
					<div class="form-group">
                                    <div class="col-md-12 control">
                                        <div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
										<div class="panel-title"><h4><a href="../web/index.php">CaMaGaRe.com</a></h4></div>
                                        </div>
                                    </div>
                                </div>
				</form>
			</div>
			
</div>
</div>
</div>

</div>
<div class="col-md-6 col-md-offset-3">
<div class="panel-body">
<div class="navbar navbar-primary navbar-fixed-bottom">
<p class="navbar-text pull-center" > &copy <?php echo date('Y');?> - Cmg Servicios. <a href="../web/index.php" target="_blank" style="color: #E67E22"><strong>Para más información sobre camagare.com, aquí.</strong></a></p>
</div>
</div>
</div>
<?php
}
 ?>

<?php
//para validar el inicio de sesion
function valida_entrada($cedula){
	$conexion = conenta_login();
$nombre_cliente_proveedor=array();
	if (strlen($cedula)==13 or strlen($cedula)==10){
	$cedula=substr($cedula,0,10);
	}else{
	$cedula=$cedula;
	}

	$sql_cliente = 'SELECT * FROM clientes WHERE substr(ruc,1,10) ="' . $cedula . '" ;';
	$datos_clientes = $conexion->query($sql_cliente);
	if ($datos_clientes){
		$cliente=mysqli_fetch_array($datos_clientes);
		$datos_cliente_proveedor[0]=$cliente['nombre'];
		$datos_cliente_proveedor[1]=$cliente['ruc'];
		
	}else{
		
		$sql_proveedor = 'SELECT * FROM proveedores WHERE substr(ruc_proveedor,1,10) ="' . $cedula . '" ;';
		$datos_proveedores = $conexion->query($sql_proveedor);
		
		if ($datos_proveedores){
			$proveedores=mysqli_fetch_array($datos_proveedores);
			$datos_cliente_proveedor[0]=$proveedores['razon_social'];
			$datos_cliente_proveedor[1]=$proveedores['ruc_proveedor'];
		}else{
		$datos_cliente_proveedor[0]="";
		}
	}

	
	if(!empty($datos_cliente_proveedor[0])){
		return $datos_cliente_proveedor;
	}else{
		return false;
	}
	mysqli_close($conexion);
}

//para desplegar el menu
function muestra_menu($datos_cliente_proveedor, $cedula){
	?>
	<div class="container">  
    <div class="panel panel-info">
	<input type="hidden" value="<?php echo $datos_cliente_proveedor[1];?>" id="ruc_cliente_proveedor">
		<div class="panel-heading">
		<div class="btn-group pull-right">
				<a href="index.php" title="Salir"><span class="glyphicon glyphicon-off"></span> Salir</a>
			</div>
			<h4><i class='glyphicon glyphicon-list-alt'></i> Documentos electrónicos emitidos a <?php echo $datos_cliente_proveedor[0]; ?></h4>		
		</div>

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#facturas">Facturas</a></li>
			<li><a data-toggle="tab" href="#retenciones">Retenciones</a></li>
			<li><a data-toggle="tab" href="#notas_de_credito">Notas de crédito</a></li>
			<li><a data-toggle="tab" href="#notas_de_debito">Notas de débito</a></li>
			<li><a data-toggle="tab" href="#guias_de_remision">Guías de remisión</a></li>
		</ul>
	 
	<div class="tab-content">
    <div id="facturas" class="tab-pane fade in active">
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="q" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="q" placeholder="Emisor, serie, factura, fecha" onkeyup='load(1);'>
								 <span class="input-group-btn">
									<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								  </span>
							</div>
							</div>
							<span id="loader_facturas"></span>
						</div>
			</form>
			<div id="resultados_facturas"></div><!-- Carga los datos ajax -->
			<div class='outer_div_facturas'></div><!-- Carga los datos ajax -->
			</div>
		</div>
    
 <div id="retenciones" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="r" placeholder="Emisor, factura, retención, fecha" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
							<span id="loader_retenciones"></span>
						</div>
			</form>
			<div id="resultados_retenciones"></div><!-- Carga los datos ajax -->
			<div class='outer_div_retenciones'></div><!-- Carga los datos ajax -->
			</div>
	</div>
	<div id="notas_de_credito" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="n" placeholder="Emisor, factura, fecha" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
								<span id="loader_notas_de_credito"></span>
						</div>
			</form>
			<div id="resultados_notas_de_credito"></div><!-- Carga los datos ajax -->
			<div class='outer_div_notas_de_credito'></div><!-- Carga los datos ajax -->
			</div>
	</div>
	<div id="notas_de_debito" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="a" placeholder="Emisor, factura, fecha" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
								<span id="loader_notas_de_debito"></span>
						</div>
			</form>
			<div id="resultados_notas_de_debito"></div><!-- Carga los datos ajax -->
			<div class='outer_div_notas_de_debito'></div><!-- Carga los datos ajax -->
			</div>
	</div>
	<div id="guias_de_remision" class="tab-pane fade">		
			<div class="panel-body">
			<form class="form-horizontal" role="form" >
						<div class="form-group row">
							<label for="d" class="col-md-1 control-label">Buscar:</label>
							<div class="col-md-5">
							<div class="input-group">
								<input type="text" class="form-control" id="g" placeholder="Emisor, factura, fecha" onkeyup='load(1);'>
								<span class="input-group-btn">
								<button type="button" class="btn btn-default" onclick='load(1);'><span class="glyphicon glyphicon-search" ></span> Buscar</button>
								</span>
							</div>
							</div>
								<span id="loader_guias_de_remision"></span>
						</div>
			</form>
			<div id="resultados_guias_de_remision"></div><!-- Carga los datos ajax -->
			<div class='outer_div_guias_de_remision'></div><!-- Carga los datos ajax -->
			</div>
	</div>
	</div>
	
	</div>
  </div>
  <?php
}
?>
<script>

</script>