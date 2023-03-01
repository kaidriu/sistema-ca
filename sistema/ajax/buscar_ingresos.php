<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	include("../validadores/periodo_contable.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$fecha_registro=date("Y-m-d H:i:s");


//PARA BUSCAR LOS INGRESOS
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	
	//para actualizar el detalle del egreso
if ($action == 'actualizar_detalle_egreso'){
	$codigo_documento=$_POST['id_registro'];
	$nuevo_detalle=$_POST['nuevo_detalle'];
		$actualiza_beneficiario=mysqli_query($con,"UPDATE ingresos_egresos SET detalle_adicional='".$nuevo_detalle."' WHERE id_ing_egr='".$codigo_documento."' ");

		if ($actualiza_beneficiario){
				echo "<script>
				$.notify('Detalle actualizado','success')
				</script>";
		}else {
			echo "<script>
			$.notify('Lo siento, algo salio mal, intente nuevamente','error')
			</script>";
		}
}
	//para actualizar la fecha de ingreso
if ($action == 'actualizar_fecha_ingreso'){
		$id_registro=$_POST['id_registro'];
		$nueva_fecha=date("Y/m/d", strtotime($_POST['nueva_fecha']));
			$actualiza_estado_fecha_ingreso=mysqli_query($con,"UPDATE ingresos_egresos SET fecha_ing_egr='".$nueva_fecha."', fecha_agregado='".$fecha_registro."' WHERE id_ing_egr='".$id_registro."' ");
			$actualiza_estado_fecha_pago=mysqli_query($con,"UPDATE formas_pagos_ing_egr as fpie INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=fpie.codigo_documento SET fpie.fecha_emision='".$nueva_fecha."' WHERE ing_egr.id_ing_egr='".$id_registro."' ");
			$consultar_codigo_contable=mysqli_query($con,"SELECT * FROM ingresos_egresos WHERE id_ing_egr='".$id_registro."' ");
			$row_consulta_contable=mysqli_fetch_array($consultar_codigo_contable);
			$numero_asiento=$row_consulta_contable['codigo_contable'];
			$actualiza_fecha_contable=mysqli_query($con,"UPDATE encabezado_diario SET fecha_asiento='".$nueva_fecha."', fecha_registro='".$fecha_registro."' WHERE numero_asiento='".$numero_asiento."' and tipo='INGRESOS' and ruc_empresa='".$ruc_empresa."' ");
			
			if ($actualiza_estado_fecha_ingreso && $actualiza_estado_fecha_pago){
					echo "<script>
					$.notify('Fecha actualizada','success')
					</script>";
			}else {
				echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
			}
	}
	
	if($action == 'ingresos'){
		$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
         $ing = mysqli_real_escape_string($con,(strip_tags($_REQUEST['ingreso'], ENT_QUOTES)));
		 $aColumns = array('nombre_ing_egr', 'numero_ing_egr','detalle_adicional','fecha_ing_egr', 'valor_ing_egr');//Columnas de busqueda
		 $sTable = "ingresos_egresos";
		 $sWhere = "WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='INGRESO' " ;
		if ( $_GET['ingreso'] != "" )
		{
			$sWhere = "WHERE (ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='INGRESO' AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$ing."%' and ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='INGRESO' OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='INGRESO' ", -3 );
			$sWhere .= ')';
		}
		$sWhere.="order by $ordenado $por";
		include ("../ajax/pagination.php"); //include pagination file
		//pagination variables
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 20; //how much records you want to show
		$adjacents  = 4; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		//Count the total number of row in your table*/
		$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = '../ingresos.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_ing_egr");'>Fecha</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_ing_egr");'>Recibido de</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("detalle_adicional");'>Detalle</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_ing_egr");'>Número</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("valor_ing_egr");'>Total</button></th>
					<th class='text-right'>Opciones</th>
					
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$codigo_unico=$row['codigo_documento'];
						$id_ingreso=$row['id_ing_egr'];
						$fecha_ingreso=$row['fecha_ing_egr'];
						$nombre_ingreso=$row['nombre_ing_egr'];
						$numero_ingreso=$row['numero_ing_egr'];
						$valor_ingreso=$row['valor_ing_egr'];
						$detalle=$row['detalle_adicional'];
					?>
					<input type="hidden" value="<?php echo $numero_ingreso;?>" id="numero_ingreso<?php echo $id_ingreso;?>">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_ingreso));?>" id="fecha_anterior_ingreso<?php echo $id_ingreso;?>">
					<input type="hidden" value="<?php echo $page;?>" id="pagina">
					<input type="hidden" value="<?php echo $detalle_adicional;?>" id="detalle_adicional_anterior<?php echo $id_egreso;?>">
					<tr>
						<td class='col-sm-2'>
						<input id="fecha_nueva_ingreso<?php echo $id_ingreso ?>" class="form-control text-center input-sm" value="<?php echo date("d-m-Y", strtotime($fecha_ingreso)) ?>" onchange="modificar_fecha_ingreso('<?php echo $id_ingreso ?>')">
						</td>
						<td><?php echo strtoupper ($nombre_ingreso); ?></td>
						<td class='col-sm-2'>
						<textarea id="detalle_adicional_nuevo<?php echo $id_ingreso ?>" class="form-control text-left input-sm" onchange="modificar_detalle_egreso('<?php echo $id_ingreso ?>')" ><?php echo  strtoupper ($detalle); ?></textarea>
						</td>
						<td><?php echo $numero_ingreso;?></td>
						<td><?php echo number_format($valor_ingreso,2,'.',''); ?></td>
						
				
					<td class='col-md-2'><span class="pull-right">
					<a title='Imprimir pdf' href="../pdf/pdf_ingreso.php?action=ingreso&codigo_unico=<?php echo $codigo_unico ?>" class='btn btn-default btn-xs' title='Pdf' target="_blank">Pdf</a>
					<a href="#" class='btn btn-info btn-xs' title='Detalle de ingreso' onclick="mostrar_detalle_ingreso('<?php echo $codigo_unico; ?>')" data-toggle="modal" data-target="#detalle_ingreso_egreso"><i class="glyphicon glyphicon-list"></i> </a>
					<a href="#" class='btn btn-danger btn-xs' title='Anular ingreso' onclick="anular_ingreso('<?php echo $codigo_unico; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>				
					</span></td>
					
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="9"><span class="pull-right">
					<?php
					 echo paginate($reload, $page, $total_pages, $adjacents);
					?></span></td>
				</tr>
			  </table>
			</div>
			</div>
			<?php
		}
	}
	
		//para detalles de ingresos
	if($action == 'detalle'){
		// escaping, additionally removing everything that could be (html/javascript-) code
         $deting = mysqli_real_escape_string($con,(strip_tags($_REQUEST['deting'], ENT_QUOTES)));
		 $aColumns = array('beneficiario_cliente','detalle_ing_egr','numero_ing_egr');//Columnas de busqueda
		 $sTable = "detalle_ingresos_egresos";
		 $sWhere = "WHERE ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO' " ;
		if ( $_GET['deting'] != "" )
		{
			$sWhere = "WHERE (ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO' AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$deting."%' and ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO' OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO' ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by numero_ing_egr desc";
		include ("../ajax/pagination.php"); //include pagination file
		//pagination variables
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 10; //how much records you want to show
		$adjacents  = 4; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		//Count the total number of row in your table*/
		$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = '../egresos.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>Recibido de</th>
					<th>Número</th>
					<th>Valor</th>
					<th>Tipo</th>
					<th>Detalle</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$nombre_cliente=$row['beneficiario_cliente'];
						$numero_ingreso=$row['numero_ing_egr'];
						$valor_ing_egr=$row['valor_ing_egr'];
						$detalle_ing_egr=$row['detalle_ing_egr'];
						$tipo_ing_egr=$row['tipo_ing_egr'];
						$tipo_pago = mysqli_query($con,"SELECT * FROM tipo_ingreso_egreso WHERE codigo='".$tipo_ing_egr."' and aplica ='INGRESO' ");
						$row_tipo_pago = mysqli_fetch_assoc($tipo_pago);
						$transaccion=$row_tipo_pago['nombre'];
					?>
					<tr>
						<td><?php echo $nombre_cliente; ?></td>
						<td><?php echo $numero_ingreso; ?></td>
						<td><?php echo $valor_ing_egr; ?></td>
						<td><?php echo $transaccion; ?></td>
						<td><?php echo $detalle_ing_egr; ?></td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="9"><span class="pull-right">
					<?php
					 echo paginate($reload, $page, $total_pages, $adjacents);
					?></span></td>
				</tr>
			  </table>
			</div>
			</div>
			<?php
		}
	}
	
	//para buscar los pagos en los INgresos
	if($action == 'pagos_ingresos'){
		// escaping, additionally removing everything that could be (html/javascript-) code
         $detpago = mysqli_real_escape_string($con,(strip_tags($_REQUEST['detpago'], ENT_QUOTES)));
		 $aColumns = array('fecha_emision','numero_ing_egr','detalle_pago','cheque');//Columnas de busqueda
		 $sTable = "formas_pagos_ing_egr";
		 $sWhere = "WHERE ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO' " ;
		if ( $_GET['detpago'] != "" ){
			$sWhere = "WHERE (ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO' AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$detpago."%' and ruc_empresa = '".$ruc_empresa."'' and tipo_documento='INGRESO' OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".$ruc_empresa."' and tipo_documento='INGRESO'", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by numero_ing_egr desc";
		include ("../ajax/pagination.php"); //include pagination file
		//pagination variables
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 10; //how much records you want to show
		$adjacents  = 4; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		//Count the total number of row in your table*/
		$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = '../egresos.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>Número ingreso</th>
					<th>Forma de cobro</th>
					<th>Cuenta bancaria</th>
					<th>Valor</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$numero_ingreso=$row['numero_ing_egr'];
						$codigo_forma_pago=$row['codigo_forma_pago'];
						$valor_forma_pago=$row['valor_forma_pago'];
						$id_cuenta=$row['id_cuenta'];
						$cheque=$row['cheque'];
						$estado_pago=$row['estado_pago'];
						
						if ($id_cuenta>0){
									$cuentas = mysqli_query($con,"SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.id_cuenta ='".$id_cuenta."'");
									$row = mysqli_fetch_array($cuentas);
									$forma_pago="Transferencia";
									$cuenta_bancaria=strtoupper($row['cuenta_bancaria']);
									
								}else{
								$cuenta_bancaria="";
								switch ($codigo_forma_pago) {
								case "E":
									$forma_pago='Efectivo';
									break;
								case "C":
									$forma_pago='Cheque';
									break;
								case "T":
									$forma_pago='Tarjeta';
									break;
								case "O":
									$forma_pago='Otros';
									break;
									}
								}
					
					?>
					<tr>

						<td><?php echo $numero_ingreso; ?></td>
						<td><?php echo $forma_pago; ?></td>
						<td><?php echo $cuenta_bancaria; ?></td>
						<td><?php echo number_format($valor_forma_pago,2,'.',''); ?></td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="9"><span class="pull-right">
					<?php
					 echo paginate($reload, $page, $total_pages, $adjacents);
					?></span></td>
				</tr>
			  </table>
			</div>
			</div>
			<?php
		}
	}
?>