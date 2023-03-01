<?php
/* Connect To Database*/
include("../conexiones/conectalogin.php");
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$fecha_registro = date("Y-m-d H:i:s");
ini_set('date.timezone', 'America/Guayaquil');
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
$tipo = $_GET['tipo'];
/*
<div style="padding: 2px; margin-bottom: 5px; margin-top: -10px;" class="alert alert-info" role="alert">
		<b>Opción 2:</b> Estas cuentas se aplican en general a cada registro. Siempre que no este asignado una cuenta en opción 3</div>
		*/
//para buscar detalles asientos prestablecidos 
if ($action == 'buscar_asientos_prestablecidos') {
	
if ($tipo == 'ventas' || $tipo == 'compras_servicios') {
		$query_asientos_tipo = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE tipo_asiento = '" . $tipo . "' ");
?>		
<form class="form-horizontal" method="POST">
			<div class="panel panel-info">
				<div class="table-responsive">
					<table class="table">
						<tr class="info">
							<td>Descripción</td>
							<td>Detalle</td>
							<td>Tipo</td>
							<td>Código</td>
							<td>Cuenta contable</td>
							<td class="col-xs-1 text-center">Opciones</td>
						</tr>
						<?php

						//cuando no esta asignado una cuenta a cada concepto
						while ($row_detalle_tipo = mysqli_fetch_array($query_asientos_tipo)) {
							$codigo_unico=rand(100, 50000);
							$id_asiento_tipo = $row_detalle_tipo['id_asiento_tipo'];
							$concepto_cuenta = $row_detalle_tipo['concepto_cuenta'];
							$tipo_asiento = $row_detalle_tipo['tipo_asiento'];
							$tipo_saldo = $row_detalle_tipo['tipo_saldo'];
							$detalle = $row_detalle_tipo['detalle'];

							//PARA TRAER LAS CUENTAS ya guardadas
							$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = '" . $tipo . "' and ap.id_pro_cli='" . $id_asiento_tipo . "' ");
							$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
							$id_cuenta = $row_cuenta['id_cuenta'];
							$codigo_cuenta = $row_cuenta['codigo_cuenta'];
							$nombre_cuenta = $row_cuenta['nombre_cuenta'];
						?>
							<tr class="active">
								<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
								<td class="col-xs-3"><?php echo mb_strtoupper($concepto_cuenta, 'UTF-8') ?></td>
								<td class="col-xs-2"><?php echo ucfirst(($detalle)) ?></td>
								<td class="col-xs-1"><?php echo ucfirst(($tipo_saldo)) ?></td>
								<td class="col-xs-2"><input type="text" id="codigo_cuenta<?php echo $codigo_unico; ?>" class="form-control input-sm" value="<?php echo $codigo_cuenta; ?>" readonly></td>
								<td class="col-xs-3">
								<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_asiento_tipo; ?>','<?php echo $tipo; ?>', '<?php echo $concepto_cuenta; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">	
								</td>
								<td class="col-xs-1 text-center">
								<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_asiento_tipo; ?>','<?php echo $tipo; ?>');"><i class="glyphicon glyphicon-trash"></i></a> 
								</td>
							</tr>
						<?php
						}
						?>
					</table>
				</div>
				</div>
		</form>
	<?php
	}
		//para los iva EN VENTAS
		if ($tipo == 'ventas') {
			?>
				<form class="form-horizontal" method="POST">
					<div class="panel panel-info">
						<div class="table-responsive">
							<table class="table">
								<?php
								$sql_ratifa_iva = mysqli_query($con, "SELECT * FROM tarifa_iva WHERE porcentaje_iva>0 ORDER BY tarifa asc");
								while ($row = mysqli_fetch_array($sql_ratifa_iva)) {
									$codigo_unico=rand(100, 50000);
									$codigo_tarifa = $row['codigo'];
									$nombre_tarifa = "IVA ".$row['porcentaje_iva']."%";
		
									//PARA TRAER LAS CUENTAS ya guardadas
									$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'iva_ventas' and ap.id_pro_cli='" . $codigo_tarifa . "' ");
									$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
									$id_cuenta = $row_cuenta['id_cuenta'];
									$codigo_cuenta = $row_cuenta['codigo_cuenta'];
									$nombre_cuenta = $row_cuenta['nombre_cuenta'];
								?>
									<tr class="active">
										<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
										<td class="col-xs-3"><?php echo mb_strtoupper($nombre_tarifa, 'UTF-8') ?></td>
										<td class="col-xs-2">Aplica a todas las facturas de venta que tengan este porcentaje de IVA</td>
										<td class="col-xs-1">Pasivo</td>
										<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
										<td class="col-xs-3">
											<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $codigo_tarifa; ?>','iva_ventas', '<?php echo $nombre_tarifa; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
										</td>
										<td class="col-xs-1 text-center">
										<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $codigo_tarifa; ?>','iva_ventas');"><i class="glyphicon glyphicon-trash"></i></a> 
										</td>
									</tr>
								<?php
								}
								?>
							</table>
						</div>
						</div>
				</form>
			<?php
			}

	//para los subtotales de las tarifa de iva de ventas
	if ($tipo == 'ventas') {
	?>
	<div style="padding: 2px; margin-bottom: 5px; margin-top: -10px;" class="alert alert-info" role="alert">
			<b>Opción 2:</b> El sistema toma las cuentas asignadas a las tarifa de IVA. Si tiene asignada una cuenta el cliente, tomará esa cuenta para contabilizar.</div>

		<form class="form-horizontal" method="POST">
			<div class="panel panel-info">
				<div class="table-responsive">
					<table class="table">
						<tr class="info">
							<td>Subtotal Tarifa IVA en ventas</td>
							<td>Tipo</td>
							<td>Código</td>
							<td>Cuenta contable</td>
							<td class="col-xs-1 text-center">Opciones</td>
						</tr>
						<?php
						$sql_ratifa_iva = mysqli_query($con, "SELECT * FROM tarifa_iva ORDER BY tarifa asc");
						while ($row = mysqli_fetch_array($sql_ratifa_iva)) {
							$codigo_unico=rand(100, 50000);
							$codigo_tarifa = $row['codigo'];
							$nombre_tarifa = $row['tarifa'];

							//PARA TRAER LAS CUENTAS ya guardadas
							$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'tarifa_iva_ventas' and ap.id_pro_cli='" . $codigo_tarifa . "' ");
							$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
							$id_cuenta = $row_cuenta['id_cuenta'];
							$codigo_cuenta = $row_cuenta['codigo_cuenta'];
							$nombre_cuenta = $row_cuenta['nombre_cuenta'];
						?>
							<tr class="active">
								<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
								<td class="col-xs-3"><?php echo mb_strtoupper($nombre_tarifa, 'UTF-8') ?></td>
								<td class="col-xs-2">Ingreso</td>
								<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
								<td class="col-xs-4">
									<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $codigo_tarifa; ?>','tarifa_iva_ventas', '<?php echo $nombre_tarifa; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
								</td>
								<td class="col-xs-1 text-center">
								<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $codigo_tarifa; ?>','tarifa_iva_ventas');"><i class="glyphicon glyphicon-trash"></i></a> 
								</td>
							</tr>
						<?php
						}
						?>
					</table>
				</div>
				</div>
		</form>
	<?php
	}

		//para los clientes
		if ($tipo == 'ventas') {
			?>
			<div style="padding: 2px; margin-bottom: 5px; margin-top: -10px;" class="alert alert-info" role="alert">
			<b>Opción 1:</b> El sistema toma la cuenta asignada en cada cliente como primera opción para generar el asiento contable</div>
				<form class="form-horizontal" method="POST">
					<div class="panel panel-info">
						<div class="table-responsive">
							<table class="table">
								<tr class="info">
									<td>Cliente</td>
									<td>Tipo</td>
									<td>Código</td>
									<td>Cuenta contable</td>
									<td class="col-xs-1 text-center">Opciones</td>
								</tr>
								<?php
								$query_clientes = mysqli_query($con, "SELECT DISTINCT cli.ruc as ruc_cliente, enc_fac.id_cliente as id_cliente, cli.nombre as cliente FROM encabezado_factura as enc_fac INNER JOIN clientes as cli ON cli.id=enc_fac.id_cliente WHERE enc_fac.ruc_empresa = '" . $ruc_empresa . "' group by enc_fac.id_cliente order by cli.nombre asc");	
								while ($row_clientes = mysqli_fetch_array($query_clientes)) {
									$codigo_unico=rand(100, 50000);
									$id_cliente = $row_clientes['id_cliente'];
									$cliente = $row_clientes['cliente'];
									$ruc_cliente = $row_clientes['ruc_cliente'];
		
									//para mostrar las cuentas que ya estan guardadas
									$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'cliente' and ap.id_pro_cli='" . $id_cliente . "' ");
									$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
									$id_cuenta = $row_cuenta['id_cuenta'];
									$codigo_cuenta = $row_cuenta['codigo_cuenta'];
									$nombre_cuenta = $row_cuenta['nombre_cuenta'];
								?>
									<tr class="active">
										<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
										<td class="col-xs-4"><?php echo mb_strtoupper($cliente, 'UTF-8'); ?></td>
										<td class="col-xs-1">Ingreso</td>
										<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
										<td class="col-xs-4">
											<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_cliente; ?>','cliente', '<?php echo $ruc_cliente; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
										</td>
										<td class="col-xs-1 text-center">
										<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_cliente; ?>','cliente');"><i class="glyphicon glyphicon-trash"></i></a> 
										</td>
									</tr>
								<?php
								}
								?>
							</table>
						</div>
						</div>
				</form>
				
			<?php
			}

		//para los iva EN compras
		if ($tipo == 'compras_servicios') {
			?>
				<form class="form-horizontal" method="POST">
					<div class="panel panel-info">
						<div class="table-responsive">
							<table class="table">
								<?php
								$sql_ratifa_iva = mysqli_query($con, "SELECT * FROM tarifa_iva WHERE porcentaje_iva>0 ORDER BY tarifa asc");
								while ($row = mysqli_fetch_array($sql_ratifa_iva)) {
									$codigo_unico=rand(100, 50000);
									$codigo_tarifa = $row['codigo'];
									$nombre_tarifa = "IVA ".$row['porcentaje_iva']."% en compras";
		
									//PARA TRAER LAS CUENTAS ya guardadas
									$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'iva_compras' and ap.id_pro_cli='" . $codigo_tarifa . "' ");
									$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
									$id_cuenta = $row_cuenta['id_cuenta'];
									$codigo_cuenta = $row_cuenta['codigo_cuenta'];
									$nombre_cuenta = $row_cuenta['nombre_cuenta'];
								?>
									<tr class="active">
										<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
										<td class="col-xs-3"><?php echo mb_strtoupper($nombre_tarifa, 'UTF-8') ?></td>
										<td class="col-xs-2">Cuenta de Iva en general para todas las compras con este porcentaje.</td>
										<td class="col-xs-1">Activo</td>
										<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
										<td class="col-xs-3">
											<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $codigo_tarifa; ?>','iva_compras', '<?php echo $nombre_tarifa; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
										</td>
										<td class="col-xs-1 text-center">
										<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $codigo_tarifa; ?>','iva_compras');"><i class="glyphicon glyphicon-trash"></i></a> 
										</td>
									</tr>
								<?php
								}
								?>
							</table>
						</div>
						</div>
				</form>
			<?php
			}

		//para los subtotales de las tarifa de iva de compras
		if ($tipo == 'compras_servicios') {
			?>
			<div style="padding: 2px; margin-bottom: 5px; margin-top: -10px;" class="alert alert-info" role="alert">
			<b>Opción 2:</b> El sistema toma las cuentas asignadas a las tarifa de IVA. Si tiene asignada una cuenta el proveedor, tomará esa cuenta para contabilizar.</div>

			<form class="form-horizontal" method="POST">
					<div class="panel panel-info">
						<div class="table-responsive">
							<table class="table">
								<tr class="info">
									<td>Subtotal Tarifa IVA en compras</td>
									<td>Detalle</td>
									<td>Tipo</td>
									<td>Código</td>
									<td>Cuenta contable</td>
									<td class="col-xs-1 text-center">Opciones</td>
								</tr>
								<?php
								$sql_ratifa_iva = mysqli_query($con, "SELECT * FROM tarifa_iva ORDER BY tarifa asc");
								while ($row = mysqli_fetch_array($sql_ratifa_iva)) {
									$codigo_unico=rand(100, 50000);
									$codigo_tarifa = $row['codigo'];
									$nombre_tarifa = $row['tarifa'];
		
									//PARA TRAER LAS CUENTAS ya guardadas
									$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'tarifa_iva_compras' and ap.id_pro_cli='" . $codigo_tarifa . "' ");
									$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
									$id_cuenta = $row_cuenta['id_cuenta'];
									$codigo_cuenta = $row_cuenta['codigo_cuenta'];
									$nombre_cuenta = $row_cuenta['nombre_cuenta'];
								?>
									<tr class="active">
										<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
										<td class="col-xs-3"><?php echo mb_strtoupper($nombre_tarifa, 'UTF-8') ?></td>
										<td class="col-xs-2">Subtotal de tarifa IVA que aplica a cada factura de compra.</td>
										<td class="col-xs-1">Ingreso</td>
										<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
										<td class="col-xs-3">
											<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $codigo_tarifa; ?>','tarifa_iva_compras', '<?php echo $nombre_tarifa; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
										</td>
										<td class="col-xs-1 text-center">
										<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $codigo_tarifa; ?>','tarifa_iva_compras');"><i class="glyphicon glyphicon-trash"></i></a> 
										</td>
									</tr>
								<?php
								}
								?>
							</table>
						</div>
						</div>
				</form>
			<?php
			}

				//para los proveedores
	if ($tipo == 'compras_servicios') {
		?>
	<div style="padding: 2px; margin-bottom: 5px; margin-top: -10px;" class="alert alert-info" role="alert">
			<b>Opción 1:</b> El sistema toma la cuenta asignada en cada proveedor como primera opción para generar el asiento contable</div>
			<form class="form-horizontal" method="POST">
				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<td>Proveedor</td>
								<td>Tipo</td>
								<td>Código</td>
								<td>Cuenta contable</td>
								<td class="col-xs-1 text-center">Opciones</td>
							</tr>
							<?php
							$query_proveedores = mysqli_query($con, "SELECT DISTINCT prov.ruc_proveedor as ruc_proveedor, enc_com.id_proveedor as id_proveedor, prov.razon_social as razon_social FROM encabezado_compra as enc_com INNER JOIN proveedores as prov ON enc_com.id_proveedor=prov.id_proveedor WHERE enc_com.ruc_empresa = '" . $ruc_empresa . "' group by enc_com.id_proveedor order by prov.razon_social asc");	
							while ($row_compras = mysqli_fetch_array($query_proveedores)) {
								$codigo_unico=rand(100, 50000);
								$id_proveedor = $row_compras['id_proveedor'];
								$proveedor = $row_compras['razon_social'];
								$ruc_proveedor = $row_compras['ruc_proveedor'];

								//para mostrar las cuentas que ya estan guardadas
								$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'proveedor' and ap.id_pro_cli='" . $id_proveedor . "' ");
								$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
								$id_cuenta = $row_cuenta['id_cuenta'];
								$codigo_cuenta = $row_cuenta['codigo_cuenta'];
								$nombre_cuenta = $row_cuenta['nombre_cuenta'];
							?>
								<tr class="active">
									<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
									<td class="col-xs-4"><a href="#" title='Mostrar detalle de compras' onclick="mostrar_detalle_compras('<?php echo $id_proveedor; ?>')" data-toggle="modal" data-target="#detalleComprasProveedor"><?php echo mb_strtoupper($proveedor, 'UTF-8'); ?> </a></td>
									<td class="col-xs-1">Activo/Costo/Gasto</td>
									<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
									<td class="col-xs-4">
										<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_proveedor; ?>','proveedor', '<?php echo $ruc_proveedor; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
									</td>
									<td class="col-xs-1 text-center">
									<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_proveedor; ?>','proveedor');"><i class="glyphicon glyphicon-trash"></i></a> 
									</td>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
					</div>
			</form>
			
		<?php
		}

	//para las retenciones en compras
	if ($tipo == 'retenciones_compras') {
	?>
		<form class="form-horizontal" method="POST">
			<div class="panel panel-info">
				<div class="table-responsive">
					<table class="table">
						<tr class="info">
							<td>Concepto</td>
							<td>Impuesto</td>
							<td>Cod</td>
							<td>%</td>
							<td>Tipo</td>
							<td>Código</td>
							<td>Cuenta contable</td>
							<td class="col-xs-1 text-center">Opciones</td>
						</tr>
						<?php
						$query_concepto_retencion = mysqli_query($con, "SELECT DISTINCT(cue_ret.codigo_impuesto) as codigo_impuesto, cue_ret.id_cr as id_retencion, cue_ret.impuesto as impuesto, cue_ret.porcentaje_retencion as porcentaje_retencion FROM cuerpo_retencion as cue_ret WHERE cue_ret.ruc_empresa = '" . $ruc_empresa . "' group by cue_ret.codigo_impuesto");
						while ($row_concepto_retencion = mysqli_fetch_array($query_concepto_retencion)) {
							$codigo_unico=rand(100, 50000);
							$id_retencion = $row_concepto_retencion['codigo_impuesto']; //$row_concepto_retencion['id_retencion'];
							$codigo_impuesto = $row_concepto_retencion['codigo_impuesto'];
							$impuesto = $row_concepto_retencion['impuesto'];
							$porcentaje_retencion = $row_concepto_retencion['porcentaje_retencion'];

							switch ($impuesto) {
								case "1":
									$impuesto = 'RENTA';
									break;
								case "2":
									$impuesto = 'IVA';
									break;
								case "3":
									$impuesto = 'ISD';
									break;
							}

							//PARA TRAER LOS NOMBRES DE CONCEPTOS DE RETENCIONES
							$query_retenciones = mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret='" . $codigo_impuesto . "'");
							$row_retenciones = mysqli_fetch_array($query_retenciones);
							$concepto_ret = $row_retenciones['concepto_ret'];

							//para mostrar las cuentas que ya estan guardadas
							$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'retenciones_compras' and ap.id_pro_cli='" . $id_retencion . "' ");
							$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
							$id_cuenta = $row_cuenta['id_cuenta'];
							$codigo_cuenta = $row_cuenta['codigo_cuenta'];
							$nombre_cuenta = $row_cuenta['nombre_cuenta'];
						?>
							<tr class="active">
								<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
								<td class="col-xs-2"><?php echo mb_strtoupper($concepto_ret, 'UTF-8') ?></td>
								<td class="col-xs-1"><?php echo $impuesto ?></td>
								<td class="col-xs-1"><?php echo $codigo_impuesto ?></td>
								<td class="col-xs-1"><?php echo $porcentaje_retencion . "%" ?></td>
								<td class="col-xs-1">Pasivo</td>
								<td class="col-xs-1"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
								<td class="col-xs-4">
									<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_retencion; ?>','retenciones_compras', '<?php echo $impuesto.$codigo_impuesto; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
								</td>
								<td class="col-xs-1 text-center">
								<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_retencion; ?>','retenciones_compras');"><i class="glyphicon glyphicon-trash"></i></a> 
								</td>
							</tr>
						<?php
						}
						?>
					</table>
				</div>
				</div>
		</form>
	<?php
	}

	//para las retenciones en ventas
	if ($tipo == 'retenciones_ventas') {
	?>
		<form class="form-horizontal" method="POST">
			<div class="panel panel-info">
				<div class="table-responsive">
					<table class="table">
						<tr class="info">
							<td>Concepto</td>
							<td>Impuesto</td>
							<td>Cod</td>
							<td>%</td>
							<td>Tipo</td>
							<td>Código</td>
							<td>Cuenta contable</td>
							<td class="col-xs-1 text-center">Opciones</td>
						</tr>
						<?php
						$query_concepto_retencion = mysqli_query($con, "SELECT DISTINCT(cue_ret.codigo_impuesto) as codigo_impuesto, cue_ret.id_cr as id_retencion, cue_ret.impuesto as impuesto, cue_ret.porcentaje_retencion as porcentaje_retencion FROM cuerpo_retencion_venta as cue_ret WHERE cue_ret.ruc_empresa = '" . $ruc_empresa . "' group by cue_ret.codigo_impuesto");
						while ($row_concepto_retencion = mysqli_fetch_array($query_concepto_retencion)) {
							$codigo_unico=rand(100, 50000);
							$id_retencion = $row_concepto_retencion['codigo_impuesto']; //$row_concepto_retencion['id_retencion'];
							$codigo_impuesto = $row_concepto_retencion['codigo_impuesto'];
							$impuesto = $row_concepto_retencion['impuesto'];
							$porcentaje_retencion = $row_concepto_retencion['porcentaje_retencion'];

							switch ($impuesto) {
								case "1":
									$impuesto = 'RENTA';
									break;
								case "2":
									$impuesto = 'IVA';
									break;
								case "3":
									$impuesto = 'ISD';
									break;
							}

							//PARA TRAER LOS NOMBRES DE CONCEPTOS DE RETENCIONES
							$query_retenciones = mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret='" . $codigo_impuesto . "'");
							$row_retenciones = mysqli_fetch_array($query_retenciones);
							$concepto_ret = $row_retenciones['concepto_ret'];

							//para mostrar las cuentas que ya estan guardadas
							$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'retenciones_ventas' and ap.id_pro_cli='" . $codigo_impuesto . "' ");
							$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
							$id_cuenta = $row_cuenta['id_cuenta'];
							$codigo_cuenta = $row_cuenta['codigo_cuenta'];
							$nombre_cuenta = $row_cuenta['nombre_cuenta'];

							if ($concepto_ret == "") {
								$concepto_ret = "Retenciones de " . $impuesto . " código " . $codigo_impuesto;
							} else {
								$concepto_ret = $concepto_ret;
							}

						?>
							<tr class="active">
								<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
								<td class="col-xs-2"><?php echo mb_strtoupper($concepto_ret, 'UTF-8') ?></td>
								<td class="col-xs-1"><?php echo $impuesto ?></td>
								<td class="col-xs-1"><?php echo $codigo_impuesto ?></td>
								<td class="col-xs-1"><?php echo $porcentaje_retencion . "%" ?></td>
								<td class="col-xs-1">Activo</td>
								<td class="col-xs-1"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
								<td class="col-xs-4">
									<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_retencion; ?>','retenciones_ventas', '<?php echo $impuesto.$codigo_impuesto; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
								</td>
								<td class="col-xs-1 text-center">
								<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_retencion; ?>','retenciones_ventas');"><i class="glyphicon glyphicon-trash"></i></a> 
								</td>
							</tr>
						<?php
						}
						?>
					</table>
				</div>
				</div>
		</form>
	<?php
	}

	//para las formas de pago en bancos
	if ($tipo == 'bancos') {
	?>
		<form class="form-horizontal" method="POST">
			<div class="panel panel-info">
				<div class="table-responsive">
					<table class="table">
						<tr class="info">
							<td>Cuenta bancaria</td>
							<td>Tipo</td>
							<td>Código</td>
							<td>Cuenta contable</td>
							<td class="col-xs-1 text-center">Opciones</td>
						</tr>
						<?php
						$cuentas = mysqli_query($con, "SELECT cue_ban.numero_cuenta as numero_cuenta, cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.ruc_empresa = '" . $ruc_empresa . "' ");
						while ($row = mysqli_fetch_array($cuentas)) {
							$codigo_unico=rand(100, 50000);
							$id_cuenta_bancaria = $row['id_cuenta'];
							$cuenta_bancaria = $row['cuenta_bancaria'];
							$numero_cuenta = $row['numero_cuenta'];

							//para mostrar las cuentas que ya estan guardadas
							$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'bancos' and ap.id_pro_cli='" . $id_cuenta_bancaria . "' ");
							$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
							$id_cuenta = $row_cuenta['id_cuenta'];
							$codigo_cuenta = $row_cuenta['codigo_cuenta'];
							$nombre_cuenta = $row_cuenta['nombre_cuenta'];

						?>
							<tr class="active">
								<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
								<td class="col-xs-3"><?php echo mb_strtoupper($cuenta_bancaria, 'UTF-8') ?></td>
								<td class="col-xs-2">Activo</td>
								<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
								<td class="col-xs-4">
									<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_cuenta_bancaria; ?>','bancos', '<?php echo $numero_cuenta; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
								</td>
								<td class="col-xs-1 text-center">
								<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_cuenta_bancaria; ?>','bancos');"><i class="glyphicon glyphicon-trash"></i></a> 
								</td>
							</tr>
						<?php
						}
						?>
					</table>
				</div>
			</div>
		</form>
<?php
	}

	//para los ingresos
	if ($tipo == 'ingresos') {
		?>
			<form class="form-horizontal" method="POST">
				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<td>Descripción</td>
								<td>Tipo</td>
								<td>Código</td>
								<td>Cuenta contable</td>
								<td class="col-xs-1 text-center">Opciones</td>
							</tr>
							<?php
							$query_registros = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE ruc_empresa = '" . $ruc_empresa . "' and tipo_opcion='1' and status='1' order by descripcion asc");	
							while ($row_registro = mysqli_fetch_array($query_registros)) {
								$codigo_unico=rand(100, 50000);
								$id_ingreso = $row_registro['id'];
								$descripcion = $row_registro['descripcion'];

								//para mostrar las cuentas que ya estan guardadas
								$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'opcion_ingreso' and ap.id_pro_cli='" . $id_ingreso . "' ");
								$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
								$id_cuenta = $row_cuenta['id_cuenta'];
								$codigo_cuenta = $row_cuenta['codigo_cuenta'];
								$nombre_cuenta = $row_cuenta['nombre_cuenta'];
							?>
								<tr class="active">
									<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
									<td class="col-xs-4"><?php echo mb_strtoupper($descripcion, 'UTF-8'); ?></td>
									<td class="col-xs-1">Activo/Pasivo/Costo/Gasto</td>
									<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
									<td class="col-xs-4">
										<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_ingreso; ?>','opcion_ingreso', '<?php echo $descripcion; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
									</td>
									<td class="col-xs-1 text-center">
									<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_ingreso; ?>','opcion_ingreso');"><i class="glyphicon glyphicon-trash"></i></a> 
									</td>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
					</div>
			</form>
			
		<?php
		}

			//para los egresos
	if ($tipo == 'egresos') {
		?>
			<form class="form-horizontal" method="POST">
				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<td>Descripción</td>
								<td>Tipo</td>
								<td>Código</td>
								<td>Cuenta contable</td>
								<td class="col-xs-1 text-center">Opciones</td>
							</tr>
							<?php
							$query_registros = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE ruc_empresa = '" . $ruc_empresa . "' and tipo_opcion='2' and status='1' order by descripcion asc");	
							while ($row_registro = mysqli_fetch_array($query_registros)) {
								$codigo_unico=rand(100, 50000);
								$id_ingreso = $row_registro['id'];
								$descripcion = $row_registro['descripcion'];

								//para mostrar las cuentas que ya estan guardadas
								$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'opcion_egreso' and ap.id_pro_cli='" . $id_ingreso . "' ");
								$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
								$id_cuenta = $row_cuenta['id_cuenta'];
								$codigo_cuenta = $row_cuenta['codigo_cuenta'];
								$nombre_cuenta = $row_cuenta['nombre_cuenta'];
							?>
								<tr class="active">
									<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
									<td class="col-xs-4"><?php echo mb_strtoupper($descripcion, 'UTF-8'); ?></td>
									<td class="col-xs-1">Activo/Pasivo/Costo/Gasto</td>
									<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
									<td class="col-xs-4">
										<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_ingreso; ?>','opcion_egreso', '<?php echo $descripcion; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
									</td>
									<td class="col-xs-1 text-center">
									<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_ingreso; ?>','opcion_egreso');"><i class="glyphicon glyphicon-trash"></i></a> 
									</td>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
					</div>
			</form>
			
		<?php
		}

					//para los cobros
	if ($tipo == 'cobros') {
		?>
			<form class="form-horizontal" method="POST">
				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<td>Descripción</td>
								<td>Tipo</td>
								<td>Código</td>
								<td>Cuenta contable</td>
								<td class="col-xs-1 text-center">Opciones</td>
							</tr>
							<?php
							$query_registros = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE ruc_empresa = '" . $ruc_empresa . "' and tipo_opcion='1' and status='1' order by descripcion asc");	
							while ($row_registro = mysqli_fetch_array($query_registros)) {
								$codigo_unico=rand(100, 50000);
								$id_ingreso = $row_registro['id'];
								$descripcion = $row_registro['descripcion'];

								//para mostrar las cuentas que ya estan guardadas
								$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'opcion_cobro' and ap.id_pro_cli='" . $id_ingreso . "' ");
								$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
								$id_cuenta = $row_cuenta['id_cuenta'];
								$codigo_cuenta = $row_cuenta['codigo_cuenta'];
								$nombre_cuenta = $row_cuenta['nombre_cuenta'];
							?>
								<tr class="active">
									<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
									<td class="col-xs-4"><?php echo mb_strtoupper($descripcion, 'UTF-8'); ?></td>
									<td class="col-xs-1">Activo</td>
									<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
									<td class="col-xs-4">
										<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_ingreso; ?>','opcion_cobro', '<?php echo $descripcion; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
									</td>
									<td class="col-xs-1 text-center">
									<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_ingreso; ?>','opcion_cobro');"><i class="glyphicon glyphicon-trash"></i></a> 
									</td>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
					</div>
			</form>
			
		<?php
		}

	//para los pagos
	if ($tipo == 'pagos') {
		?>
			<form class="form-horizontal" method="POST">
				<div class="panel panel-info">
					<div class="table-responsive">
						<table class="table">
							<tr class="info">
								<td>Descripción</td>
								<td>Tipo</td>
								<td>Código</td>
								<td>Cuenta contable</td>
								<td class="col-xs-1 text-center">Opciones</td>
							</tr>
							<?php
							$query_registros = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE ruc_empresa = '" . $ruc_empresa . "' and tipo_opcion='2' and status='1' order by descripcion asc");	
							while ($row_registro = mysqli_fetch_array($query_registros)) {
								$codigo_unico=rand(100, 50000);
								$id_ingreso = $row_registro['id'];
								$descripcion = $row_registro['descripcion'];

								//para mostrar las cuentas que ya estan guardadas
								$sql_cuentas_programadas = mysqli_query($con, "SELECT * FROM asientos_programados as ap INNER JOIN plan_cuentas as pc ON pc.id_cuenta=ap.id_cuenta WHERE ap.ruc_empresa = '" . $ruc_empresa . "' and ap.tipo_asiento = 'opcion_pago' and ap.id_pro_cli='" . $id_ingreso . "' ");
								$row_cuenta = mysqli_fetch_array($sql_cuentas_programadas);
								$id_cuenta = $row_cuenta['id_cuenta'];
								$codigo_cuenta = $row_cuenta['codigo_cuenta'];
								$nombre_cuenta = $row_cuenta['nombre_cuenta'];
							?>
								<tr class="active">
									<input type="hidden" id="id_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $id_cuenta; ?>">
									<td class="col-xs-4"><?php echo mb_strtoupper($descripcion, 'UTF-8'); ?></td>
									<td class="col-xs-1">Activo</td>
									<td class="col-xs-2"><input type="text" class="form-control input-sm" id="codigo_cuenta<?php echo $codigo_unico; ?>" value="<?php echo $codigo_cuenta; ?>" readonly></td>
									<td class="col-xs-4">
										<input type="text" <?php echo empty($codigo_cuenta)?"style='border: 1px solid #f00;'":""; ?> class="form-control input-sm" id="cuenta_contable<?php echo $codigo_unico; ?>" onkeyup="guardar_cuenta('<?php echo $codigo_unico; ?>', '<?php echo $id_ingreso; ?>','opcion_pago', '<?php echo $descripcion; ?>');" autocomplete="off" placeholder="Ingrese cuenta" value="<?php echo $nombre_cuenta; ?>">
									</td>
									<td class="col-xs-1 text-center">
									<a href="#" class="btn btn-danger btn-xs" title="Eliminar cuenta" onclick="eliminar_cuenta('<?php echo $codigo_unico; ?>','<?php echo $id_ingreso; ?>','opcion_pago');"><i class="glyphicon glyphicon-trash"></i></a> 
									</td>
								</tr>
							<?php
							}
							?>
						</table>
					</div>
					</div>
			</form>
			
		<?php
		}


}
?>