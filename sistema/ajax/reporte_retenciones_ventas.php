<?php
/* Connect To Database*/
include("../conexiones/conectalogin.php");
$con = conenta_login();
session_start();
$id_usuario = $_SESSION['id_usuario'];
$ruc_empresa = $_SESSION['ruc_empresa'];

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
if($action == 'reporte_retenciones_ventas'){
		if (empty($_POST['parametro'])) {
           $errors[] = "Ingrese ejercicio fiscal, mes/año.";
        } else if (!empty($_POST['parametro'])){
$parametro=mysqli_real_escape_string($con,(strip_tags($_POST["parametro"],ENT_QUOTES)));
?>
<div class="panel panel-info">
   <div class="table-responsive">
   <table class="table">
  <tr class="info">
	<th>Registros</th>
	<th>Código</th>
	<th>Concepto</th>
	<th>Impuesto</th>
	<th>Base imponible</th>
	<th>Porcentaje</th>
	<th>Valor retenido</th>
</tr>
<?php

// PARA MOSTRAR LOS CONCEPTOS DE RETENCIONES
	$sql_conceptos_retencion=mysqli_query($con, "SELECT cr.impuesto as impuesto, count(cr.secuencial_retencion) as registros, cr.porcentaje_retencion as porcentaje_retencion, cr.codigo_impuesto as codigo_impuesto, sum(cr.base_imponible) as base_imponible, sum(cr.valor_retenido) as valor_retenido FROM cuerpo_retencion_venta cr WHERE mid(cr.ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and cr.ejercicio_fiscal = '".$parametro."' group by cr.codigo_impuesto");
	while ($row=mysqli_fetch_array($sql_conceptos_retencion)){
	$registros=$row["registros"];
	$porcentaje_retencion=$row["porcentaje_retencion"];
	$codigo_impuesto=$row["codigo_impuesto"];
	$tipo_impuesto=$row["impuesto"];
	
	switch ($tipo_impuesto) {
	case "1":
		$tipo_impuesto='RENTA';
		break;
	case "2":
		$tipo_impuesto='IVA';
		break;
	case "6":
		$tipo_impuesto='ISD';
		break;
		}
		
		$sql_nombres_retenciones=mysqli_query($con, "SELECT * FROM retenciones_sri WHERE codigo_ret='".$codigo_impuesto."' and porcentaje_ret='".$porcentaje_retencion."'");
		$row_nombre_ret=mysqli_fetch_array($sql_nombres_retenciones);
		$concepto_retencion=$row_nombre_ret["concepto_ret"];

	$base_imponible=$row["base_imponible"];
	
	$valor_retenido=$row["valor_retenido"];
		?>
		<tr>
			<td><?php echo $registros;?></td>
			<td><?php echo $codigo_impuesto;?></td>
			<td><?php echo $concepto_retencion;?></td>
			<td><?php echo $tipo_impuesto;?></td>
			<td><?php echo $base_imponible;?></td>
			<td><?php echo $porcentaje_retencion."%";?></td>
			<td class='text-center'><?php echo $valor_retenido;?></td>
		</tr>		

		<?php
		}
		//para sacar las sumas de las retenciones de renta
		$suma_retenciones_renta=mysqli_query($con, "SELECT sum(valor_retenido) as retenciones_renta FROM cuerpo_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and ejercicio_fiscal = '".$parametro."' and impuesto='1'");
		$renta=mysqli_fetch_array($suma_retenciones_renta);
		$suma_renta = $renta['retenciones_renta'];
		
		//para sacar las sumas de las retenciones de iva
		$suma_retenciones_iva=mysqli_query($con, "SELECT sum(valor_retenido) as retenciones_iva FROM cuerpo_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and ejercicio_fiscal = '".$parametro."' and impuesto='2'");
		$iva=mysqli_fetch_array($suma_retenciones_iva);
		$suma_iva = $iva['retenciones_iva'];
		
		//para sacar las sumas de las retenciones de isd
		$suma_retenciones_isd=mysqli_query($con, "SELECT sum(valor_retenido) as retenciones_isd FROM cuerpo_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and ejercicio_fiscal = '".$parametro."' and impuesto='6'");
		$isd=mysqli_fetch_array($suma_retenciones_isd);
		$suma_isd = $isd['retenciones_isd'];
		
		?>
		
	<tr class="info">
		<td class='text-right' colspan=6 >Total retenciones de IVA: </td>
		<td class='text-center'><?php echo number_format($suma_iva,2,'.','');?></td>
	</tr>
	<tr class="info">
		<td class='text-right' colspan=6 >Total retenciones de RENTA: </td>
		<td class='text-center'><?php echo number_format($suma_renta,2,'.','');?></td>
	</tr>
	<tr class="info">
		<td class='text-right' colspan=6 >Total retenciones de ISD: </td>
		<td class='text-center'><?php echo number_format($suma_isd,2,'.','');?></td>
	</tr>
		
</table>	
</div>
</div>

<?php
}else {
			$errors []= "Error desconocido.";
		}
}

//fin de retenciones ventas
		if (isset($errors)){
			
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Error!</strong> 
					<?php
						foreach ($errors as $error) {
								echo $error;
							}
						?>
			</div>
			<?php
			}
			if (isset($messages)){
				
				?>
				<div class="alert alert-success" role="alert">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>¡Bien hecho!</strong>
						<?php
							foreach ($messages as $message) {
									echo $message;
								}
							?>
				</div>
				<?php
			}
?>
