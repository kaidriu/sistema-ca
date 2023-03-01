<?PHP
	include("../conexiones/conectalogin.php");
	session_start();
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

if ($action == 'conciliacion_bancaria'){
	ini_set('date.timezone','America/Guayaquil');
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$cuenta = $_POST['cuenta'];
	$fecha_desde = $_POST['fecha_desde'];
	$fecha_hasta = $_POST['fecha_hasta'];
	//$fecha_desde_menos_uno = date("d-m-Y",strtotime($fecha_desde."- 1 days"));//para que me saque el saldo hasta la fecha inicial menos un dia antes
	$con = conenta_login();
	$suma_creditos_saldo_inicial = saldo_inicial_creditos($con, $cuenta, $ruc_empresa, $fecha_desde);
	$suma_debitos_saldo_inicial = saldo_inicial_debitos($con, $cuenta, $ruc_empresa, $fecha_desde);
	$cheques_saldo_inicial = cheques_saldo_inicial($con, $cuenta, $ruc_empresa, $fecha_desde);
	$saldo_inicial=$suma_creditos_saldo_inicial-$suma_debitos_saldo_inicial-$cheques_saldo_inicial;

	$total_creditos = creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'INGRESO');
	$total_debitos = creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'EGRESO');
	$cheques_pagados = cheques_pagados($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta);

	$saldo_final=$saldo_inicial+$total_creditos-$total_debitos-$cheques_pagados;
	?>
		<div class="panel-group" id="accordiones">
		
		<div class="panel panel-success">
			<a class="list-group-item list-group-item-success" data-toggle="collapse" data-parent="#accordiones" href="#resumen"><span class="caret"></span> <b> Resumen de la conciliación bancaria</b> </a>
			  <div id="resumen" class="panel-collapse collapse in">
			  <div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="success">
					<th style ="padding: 2px;" class="text-center">Saldo Inicial</th>
					<th style ="padding: 2px;" class="text-center">Créditos</th>
					<th style ="padding: 2px;" class="text-center">Débitos</th>
					<th style ="padding: 2px;" class="text-center">Saldo Final</th>
				</tr>
			<tr>
				<td style ="padding: 2px;" class="text-center"><?php echo number_format($saldo_inicial,2,'.','');?></td>
				<td style ="padding: 2px;" class="text-center"><?php echo number_format($total_creditos,2,'.','');?></td>
				<td style ="padding: 2px;" class="text-center"><?php echo number_format($total_debitos + $cheques_pagados,2,'.','');?></td>
				<td style ="padding: 2px;" class="text-center"><?php echo number_format($saldo_final,2,'.','');?></td>
			</tr>
			</table>
			</div>
			</div>
			</div>
		
			<div class="panel panel-info">
				<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordiones" href="#ingresos"><span class="caret"></span> <b> Detalle de créditos</b> </a>
			  <div id="ingresos" class="panel-collapse collapse">
			  <div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 2px;">Fecha</th>
					<th style ="padding: 2px;">Ingreso</th>
					<th style ="padding: 2px;">Recibido de</th>
					<th style ="padding: 2px;">Detalle</th>
					<th style ="padding: 2px;" class="text-center">Tipo</th>
					<th style ="padding: 2px;">Valor</th>
				</tr>
			<?php
		$sql_ingresos = detalle_creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'INGRESO');
		$total_ingresos=0;
		while ($row_ingresos=mysqli_fetch_array($sql_ingresos)){
			$fecha_emision=$row_ingresos['fecha_emision'];
			$codigo_documento=$row_ingresos['codigo_documento'];
			$nombre_ingreso = $row_ingresos['nombre_ing_egr'];
			$numero_ing_egr=$row_ingresos['numero_ing_egr'];
			$valor=$row_ingresos['valor_forma_pago'];
			$total_ingresos += $valor;
			$detalle_pago=$row_ingresos['detalle_pago'];

			switch ($row_ingresos['detalle_pago']) {
				case "D":
					$tipo = 'Dep';
					break;
				case "T":
					$tipo = 'Transf';
					break;
			}
			$sql_detalle_ingresos = detalle_ingresos_egresos($con, $codigo_documento);
			?>
			<tr>	
				<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_emision));?></td>
				<td style ="padding: 2px;"><?php echo $numero_ing_egr;?></td>
				<td style ="padding: 2px;"><?php echo $nombre_ingreso;?></td>
				<td style ="padding: 2px;"><?php foreach ($sql_detalle_ingresos as $detalle){echo $detalle['detalle_ing_egr']."<br>";}?></td>				
				<td style ="padding: 2px;" class="text-center"><?php echo $tipo;?></td>
				<td style ="padding: 2px;"><?php echo number_format($valor,2,'.','');?></td>
			</tr>
			<?php
			}
			?>
			<tr  class="info">
				<th style ="padding: 2px;" colspan="5" class="text-right">Total ingresos: </th>
				<th style ="padding: 2px;" ><?php echo number_format($total_ingresos,2,'.','');?></th>
			</tr>
			</table>
			</div>
			</div>
			</div>
			
		<div class="panel panel-info">
				<a class="list-group-item list-group-item-info" data-toggle="collapse" data-parent="#accordiones" href="#egresos"><span class="caret"></span> <b> Detalle de débitos</b> </a>
			  <div id="egresos" class="panel-collapse collapse">
			  <div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 2px;">Fecha</th>
					<th style ="padding: 2px;">Egreso</th>
					<th style ="padding: 2px;">Pagado a</th>
					<th style ="padding: 2px;">Detalle</th>
					<th style ="padding: 2px;">Tipo</th>
					<th style ="padding: 2px;">Valor</th>
				</tr>
			<?php
		$sql_egresos = detalle_creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, 'EGRESO');
		$total_egresos=0;
		while ($row_egresos=mysqli_fetch_array($sql_egresos)){
			$fecha_emision=$row_egresos['cheque']>0?$row_egresos['fecha_entrega']:$row_egresos['fecha_emision'];
			$codigo_documento=$row_egresos['codigo_documento'];
			$nombre_egreso = $row_egresos['nombre_ing_egr'];
			$numero_ing_egr=$row_egresos['numero_ing_egr'];
			$valor=$row_egresos['valor_forma_pago'];
			$total_egresos += $valor;
			switch ($row_egresos['detalle_pago']) {
				case "D":
					$tipo = 'Déb';
					break;
				case "T":
					$tipo = 'Transf';
					break;
				case "C":
					$tipo = 'Ch';
					break;
			}

			$cheque=$row_egresos['cheque']==0?"":" ".$row_egresos['cheque'];
			$detalle_pago=$tipo. $cheque;
			$sql_detalle_egresos = detalle_ingresos_egresos($con, $codigo_documento);
			?>
			<tr>	
				<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_emision));?></td>
				<td style ="padding: 2px;"><?php echo $numero_ing_egr;?></td>
				<td style ="padding: 2px;"><?php echo $nombre_egreso;?></td>
				<td style ="padding: 2px;"><?php foreach ($sql_detalle_egresos as $detalle){echo $detalle['detalle_ing_egr']."<br>";}?></td>				
				<td style ="padding: 2px;"><?php echo $detalle_pago;?></td>
				<td style ="padding: 2px;"><?php echo number_format($valor,2,'.','');?></td>
			</tr>
			<?php
			}
			?>
			<tr  class="info">
				<th style ="padding: 2px;" colspan="5" class="text-right">Total débitos: </th>
				<th style ="padding: 2px;" ><?php echo number_format($total_egresos,2,'.','');?></th>
			</tr>
			</table>
			</div>
			</div>
			</div>
			
			<div class="panel panel-warning">
				<a class="list-group-item list-group-item-warning" data-toggle="collapse" data-parent="#accordiones" href="#chequesemitidos"><span class="caret"></span> <b> Detalle de cheques emitidos en el período</b> </a>
			  <div id="chequesemitidos" class="panel-collapse collapse">
			  <div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="warning">
					<th style ="padding: 2px;">Fecha emisión</th>
					<th style ="padding: 2px;">Fecha en Cheque</th>
					<th style ="padding: 2px;">Fecha cobro</th>
					<th style ="padding: 2px;">Egreso</th>
					<th style ="padding: 2px;">No. cheque</th>
					<th style ="padding: 2px;">Beneficiario</th>
					<th style ="padding: 2px;">Detalle</th>
					<th style ="padding: 2px;">Valor</th>
				</tr>
			<?php
		$sql_cheques = cheques_emitidos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta);
		$total_cheques=0;
		while ($row_cheques=mysqli_fetch_array($sql_cheques)){
			$fecha_emision=$row_cheques['fecha_emision'];
			$fecha_entrega=$row_cheques['fecha_entrega'];
			$fecha_pago=$row_cheques['fecha_pago'];
			$estado_pago=$row_cheques['estado_pago'];
			$codigo_documento=$row_cheques['codigo_documento'];
			$nombre_egreso =  $row_cheques['nombre_ing_egr'];
			$numero_ing_egr=$row_cheques['numero_ing_egr'];
			$numero_cheque=$row_cheques['cheque'];
			$valor=$row_cheques['valor_forma_pago'];
			$total_cheques +=$valor;
			$sql_detalle_cheques = detalle_ingresos_egresos($con, $codigo_documento);
			?>
			<tr>	
				<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_emision));?></td>
				<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_pago));?></td>
				<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_entrega));?></td>
				<td style ="padding: 2px;"><?php echo $numero_ing_egr;?></td>
				<td style ="padding: 2px;"><?php echo $numero_cheque;?></td>
				<td style ="padding: 2px;"><?php echo $nombre_egreso;?></td>
				<td style ="padding: 2px;"><?php foreach ($sql_detalle_cheques as $detalle){echo $detalle['detalle_ing_egr']."<br>";}?></td>				
				<td style ="padding: 2px;"><?php echo number_format($valor,2,'.','');?></td>
			</tr>
			<?php
			}
			?>
			<tr  class="warning">
				<th style ="padding: 2px;" colspan="7" class="text-right">Total: </th>
				<th style ="padding: 2px;" ><?php echo number_format($total_cheques,2,'.','');?></th>
			</tr>
			</table>
			</div>
			</div>
			</div>
			
			<div class="panel panel-warning">
				<a class="list-group-item list-group-item-warning" data-toggle="collapse" data-parent="#accordiones" href="#chequespagados"><span class="caret"></span> <b> Detalle de cheques pagados en el período</b> </a>
			  <div id="chequespagados" class="panel-collapse collapse">
			  <div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="warning">
					<th style ="padding: 2px;">Fecha emisión</th>
					<th style ="padding: 2px;">Fecha en cheque</th>
					<th style ="padding: 2px;">Fecha Cobro</th>
					<th style ="padding: 2px;">Egreso</th>
					<th style ="padding: 2px;">No. cheque</th>
					<th style ="padding: 2px;">Beneficiario</th>
					<th style ="padding: 2px;">Detalle</th>
					<th style ="padding: 2px;">Valor</th>
				</tr>
			<?php
		$sql_cheques_pagados = detalle_cheques_pagados($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta);
		$total_cheques=0;
		while ($row_cheques=mysqli_fetch_array($sql_cheques_pagados)){
			$fecha_emision=$row_cheques['fecha_emision'];
			$fecha_entrega=$row_cheques['fecha_entrega'];
			$fecha_pago=$row_cheques['fecha_pago'];
			$codigo_documento=$row_cheques['codigo_documento'];
			$nombre_egreso = $row_cheques['nombre_ing_egr'];
			$numero_ing_egr=$row_cheques['numero_ing_egr'];
			$numero_cheque=$row_cheques['cheque'];
			$valor=$row_cheques['valor_forma_pago'];
			$total_cheques +=$valor;
			$sql_detalle_cheques = detalle_ingresos_egresos($con, $codigo_documento);
				?>
				<tr>	
					<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_emision));?></td>
					<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_pago));?></td>
					<td style ="padding: 2px;"><?php echo date("d-m-Y", strtotime($fecha_entrega));?></td>
					<td style ="padding: 2px;"><?php echo $numero_ing_egr;?></td>
					<td style ="padding: 2px;"><?php echo $numero_cheque;?></td>
					<td style ="padding: 2px;"><?php echo $nombre_egreso;?></td>
					<td style ="padding: 2px;"><?php foreach ($sql_detalle_cheques as $detalle){echo $detalle['detalle_ing_egr']."<br>";}?></td>				
					<td style ="padding: 2px;"><?php echo number_format($valor,2,'.','');?></td>
				</tr>
				<?php
			}
			?>
			<tr  class="warning">
				<th style ="padding: 2px;" colspan="7" class="text-right">Total: </th>
				<th style ="padding: 2px;" ><?php echo number_format($total_cheques,2,'.','');?></th>
			</tr>
			</table>
			</div>
			</div>
			</div>
		</div>
	<?php
}


function creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, $tipo){
	/*
	if ($tipo=='EGRESO'){
	$tipo='EGRESO';
	}
	
	if ($tipo=='INGRESO'){
	$tipo='INGRESO';
	}
	*/
	

	$sql_creditos_debitos = mysqli_query($con, "SELECT round(sum(valor_forma_pago),2) as total FROM formas_pagos_ing_egr WHERE id_cuenta='".$cuenta."' and ruc_empresa ='".$ruc_empresa."' and tipo_documento='".$tipo."' and DATE_FORMAT(fecha_emision, '%Y/%m/%d') between '".date("Y/m/d", strtotime($fecha_desde))."' and '".date("Y/m/d", strtotime($fecha_hasta))."' and estado='OK' and detalle_pago !='C' group by id_cuenta");
	$resultado = mysqli_fetch_array($sql_creditos_debitos)['total'];
	return $resultado;
}

function detalle_creditos_debitos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta, $tipo){
	/*
	if ($tipo=='EGRESO'){
	$tipo='EGRESO';
	}
	
	if ($tipo=='INGRESO'){
	$tipo='INGRESO';
	}
	*/
	

	$resultado = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr as pagos 
	INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=pagos.codigo_documento 
	WHERE pagos.id_cuenta='".$cuenta."' and pagos.ruc_empresa ='".$ruc_empresa."' 
	and pagos.tipo_documento='".$tipo."' and 
	if(pagos.cheque > 0, DATE_FORMAT(pagos.fecha_entrega, '%Y/%m/%d'), DATE_FORMAT(pagos.fecha_emision, '%Y/%m/%d')) 
	between '".date("Y/m/d", strtotime($fecha_desde))."' and '".date("Y/m/d", strtotime($fecha_hasta))."' 
	and pagos.estado='OK' and pagos.estado_pago='PAGADO' order by pagos.fecha_emision asc");
	return $resultado;
}

function cheques_emitidos($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta){
	$resultado = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr as pagos INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=pagos.codigo_documento WHERE pagos.id_cuenta='".$cuenta."' and pagos.ruc_empresa ='".$ruc_empresa."' and pagos.tipo_documento='EGRESO' and pagos.cheque > 0 and DATE_FORMAT(pagos.fecha_emision, '%Y/%m/%d') between '".date("Y/m/d", strtotime($fecha_desde))."' and '".date("Y/m/d", strtotime($fecha_hasta))."' and pagos.estado='OK' order by pagos.fecha_emision asc");
	return $resultado;
}

function detalle_ingresos_egresos($con, $codigo_documento){
	$sql_detalle = mysqli_query($con, "SELECT * FROM detalle_ingresos_egresos WHERE codigo_documento='".$codigo_documento."' ");
	return $sql_detalle;
}

function saldo_inicial_creditos($con, $cuenta, $ruc_empresa, $fecha_desde){
	$suma_creditos_debitos = mysqli_query($con, "SELECT round(sum(valor_forma_pago),2) as total FROM formas_pagos_ing_egr WHERE id_cuenta='".$cuenta."' and ruc_empresa ='".$ruc_empresa."' and tipo_documento='INGRESO' and DATE_FORMAT(fecha_emision, '%Y/%m/%d') < '".date("Y/m/d", strtotime($fecha_desde))."' and estado_pago='PAGADO' and estado='OK' ");
	$suma=mysqli_fetch_array($suma_creditos_debitos)['total'];
	return $suma;
}

function saldo_inicial_debitos($con, $cuenta, $ruc_empresa, $fecha_desde){
	$suma_creditos_debitos = mysqli_query($con, "SELECT round(sum(valor_forma_pago),2) as total FROM formas_pagos_ing_egr WHERE id_cuenta='".$cuenta."' and ruc_empresa ='".$ruc_empresa."' and tipo_documento='EGRESO' and DATE_FORMAT(fecha_emision, '%Y/%m/%d') < '".date("Y/m/d", strtotime($fecha_desde))."' and estado_pago='PAGADO' and estado='OK' and detalle_pago != 'C'");
	$suma=mysqli_fetch_array($suma_creditos_debitos)['total'];
	return $suma;
}

function cheques_saldo_inicial($con, $cuenta, $ruc_empresa, $fecha_desde){
	$suma_creditos_debitos = mysqli_query($con, "SELECT round(sum(valor_forma_pago),2) as total FROM formas_pagos_ing_egr WHERE id_cuenta='".$cuenta."' and ruc_empresa ='".$ruc_empresa."' and tipo_documento='EGRESO' and DATE_FORMAT(fecha_entrega, '%Y/%m/%d') < '".date("Y/m/d", strtotime($fecha_desde))."' and estado_pago='PAGADO' and estado='OK' and detalle_pago ='C'");
	$suma=mysqli_fetch_array($suma_creditos_debitos)['total'];
	return $suma;
}

function cheques_pagados($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta){
	$sql_cheques_pagados = mysqli_query($con, "SELECT round(sum(valor_forma_pago),2) as total FROM formas_pagos_ing_egr WHERE id_cuenta='".$cuenta."' and ruc_empresa ='".$ruc_empresa."' and tipo_documento='EGRESO' and detalle_pago = 'C' and DATE_FORMAT(fecha_entrega, '%Y/%m/%d') between '".date("Y/m/d", strtotime($fecha_desde))."' and '".date("Y/m/d", strtotime($fecha_hasta))."' and estado='OK' and estado_pago='PAGADO' order by fecha_entrega asc");
	$resultado = mysqli_fetch_array($sql_cheques_pagados)['total'];
	return $resultado;
}

function detalle_cheques_pagados($con, $cuenta, $ruc_empresa, $fecha_desde, $fecha_hasta){
	$resultado = mysqli_query($con, "SELECT * FROM formas_pagos_ing_egr as pagos INNER JOIN ingresos_egresos as detalle ON detalle.codigo_documento=pagos.codigo_documento WHERE pagos.id_cuenta='".$cuenta."' and pagos.ruc_empresa ='".$ruc_empresa."' and pagos.tipo_documento='EGRESO' and pagos.cheque > 0 and DATE_FORMAT(pagos.fecha_entrega, '%Y/%m/%d') between '".date("Y/m/d", strtotime($fecha_desde))."' and '".date("Y/m/d", strtotime($fecha_hasta))."' and pagos.estado_pago='PAGADO' and pagos.estado='OK' order by pagos.fecha_pago asc");
	return $resultado;
}


?>