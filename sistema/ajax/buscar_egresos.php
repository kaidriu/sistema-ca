<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	//include("../validadores/periodo_contable.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	ini_set('date.timezone','America/Guayaquil');
	$fecha_registro=date("Y-m-d H:i:s");

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

//para actualizar el nombre del beneficiario
if ($action == 'actualizar_beneficiario'){
		$codigo_documento=$_POST['codigo_documento'];
		$id_beneficiario_final=$_POST['id_beneficiario_final'];
		$beneficiario_final_cheque=$_POST['beneficiario_final_cheque'];
			$actualiza_beneficiario=mysqli_query($con,"UPDATE ingresos_egresos SET nombre_ing_egr='".$beneficiario_final_cheque."', id_cli_pro='".$id_beneficiario_final."' WHERE codigo_documento='".$codigo_documento."' ");

			if ($actualiza_beneficiario){
					echo "<script>
					$.notify('Nombre actualizado','success')
					</script>";
			}else {
				echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
			}
	}
	
//para actualizar la fecha de egreso
if ($action == 'actualizar_fecha_egreso'){
		$id_registro=$_POST['id_registro'];
		$nueva_fecha=date("Y/m/d", strtotime($_POST['nueva_fecha']));
			$actualiza_estado_fecha_egreso=mysqli_query($con,"UPDATE ingresos_egresos SET fecha_ing_egr='".$nueva_fecha."', fecha_agregado='".$fecha_registro."' WHERE id_ing_egr='".$id_registro."' ");
			$actualiza_estado_fecha_pago=mysqli_query($con,"UPDATE formas_pagos_ing_egr as fpie INNER JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento=fpie.codigo_documento SET fpie.fecha_emision='".$nueva_fecha."' WHERE ing_egr.id_ing_egr='".$id_registro."' ");
			$consultar_codigo_contable=mysqli_query($con,"SELECT * FROM ingresos_egresos WHERE id_ing_egr='".$id_registro."' ");
			$row_consulta_contable=mysqli_fetch_array($consultar_codigo_contable);
			$numero_asiento=$row_consulta_contable['codigo_contable'];
			$actualiza_fecha_contable=mysqli_query($con,"UPDATE encabezado_diario SET fecha_asiento='".$nueva_fecha."', fecha_registro='".$fecha_registro."' WHERE numero_asiento='".$numero_asiento."' and tipo='EGRESOS' and ruc_empresa='".$ruc_empresa."' ");
			
			if ($actualiza_estado_fecha_egreso && $actualiza_estado_fecha_pago){
					echo "<script>
					$.notify('Fecha actualizada','success')
					</script>";
			}else {
				echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
			}
	}


//para actualizar la fecha de entrega del cheque
if ($action == 'actualizar_fecha_entrega_cheque'){
		$id_registro=$_POST['id_registro'];
		$nueva_fecha=date("Y/m/d", strtotime($_POST['nueva_fecha']));
			$actualiza_estado_fecha_cheque=mysqli_query($con,"UPDATE formas_pagos_ing_egr SET fecha_entrega='".$nueva_fecha."' WHERE id_fp='".$id_registro."' ");
			if ($actualiza_estado_fecha_cheque){
					echo "<script>
					$.notify('Actualizado','success')
					</script>";
			}else {
				echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
			}
	}
	

//para actualizar estado del cheque
if ($action == 'actualizar_estado_cheque'){
		$id_cheque=$_POST['id_cheque'];
		$nuevo_estado=$_POST['nuevo_estado'];
		if($nuevo_estado == "ANULADO"){
	
			//echo "<script>$.notify('Para anular el cheque debe anular el egreso','error')</script>";
		$actualiza_estado_cheque=mysqli_query($con,"UPDATE formas_pagos_ing_egr SET valor_forma_pago=0, estado_pago='".$nuevo_estado."', fecha_entrega=fecha_emision WHERE id_fp='".$id_cheque."' ");
		$registro_ingresos_egresos=mysqli_query($con,"SELECT * from formas_pagos_ing_egr WHERE id_fp='".$id_cheque."' ");
		$row_registro_ingresos_egresos=mysqli_fetch_array($registro_ingresos_egresos);
		$codigo_documento=$row_registro_ingresos_egresos['codigo_documento'];

		include("../clases/anular_registros.php");
		$anular_asiento_contable = new anular_registros(); 
		$datos_encabezado=mysqli_query($con,"SELECT * FROM ingresos_egresos WHERE codigo_documento = '".$codigo_documento."' ");
		$row_encabezado=mysqli_fetch_array($datos_encabezado);
		$id_registro_contable=$row_encabezado['codigo_contable'];
		$anio_documento=date("Y", strtotime($row_encabezado['fecha_ing_egr']));
		$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
		
		$actualiza_ingresos_egresos=mysqli_query($con,"UPDATE ingresos_egresos SET valor_ing_egr=0, detalle_adicional='CHEQUE ANULADO' WHERE codigo_documento='".$codigo_documento."' ");
		$actualiza_detalle_ingresos_egresos=mysqli_query($con,"UPDATE detalle_ingresos_egresos SET valor_ing_egr=0, codigo_documento_cv=0, detalle_ing_egr='CHEQUE ANULADO' WHERE codigo_documento='".$codigo_documento."' ");
		
		echo "<script>
		$.notify('Estado actualizado','success')
		</script>";
		
		
		}else{
			$actualiza_estado_cheque=mysqli_query($con,"UPDATE formas_pagos_ing_egr SET estado_pago='".$nuevo_estado."', fecha_entrega=fecha_emision WHERE id_fp='".$id_cheque."' ");
			if ($actualiza_estado_cheque){
					echo "<script>
					$.notify('Estado actualizado','success')
					</script>";
			}else {
				echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
			}
		}
		
	}

//para anular un egreso
if ($action == 'anular_egreso' && isset($_POST['codigo_documento'])){
		$id_usuario = $_SESSION['id_usuario'];
		$codigo_documento=$_POST['codigo_documento'];
		
		include("../clases/anular_registros.php");
		$anular_asiento_contable = new anular_registros(); 
		$datos_encabezado=mysqli_query($con,"SELECT * FROM ingresos_egresos WHERE codigo_documento = '".$codigo_documento."' ");
		$row_encabezado=mysqli_fetch_array($datos_encabezado);
		$id_registro_contable=$row_encabezado['codigo_contable'];
		$anio_documento=date("Y", strtotime($row_encabezado['fecha_ing_egr']));
		$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
		
		if ($resultado_anular_documento=="NO"){
		echo "<script>
			$.notify('Primero se debe anular el asiento contable','error');
			</script>";
		exit;
		}
			
		//anular el egreso y detalles y formas de pagos
		$anular_encabezado_egreso=mysqli_query($con,"UPDATE ingresos_egresos SET nombre_ing_egr='ANULADO', valor_ing_egr=0, estado='ANULADO' WHERE codigo_documento = '".$codigo_documento."' ");
		$anular_detalle_egreso=mysqli_query($con,"DELETE FROM detalle_ingresos_egresos WHERE codigo_documento = '".$codigo_documento."'");
		$anular_anular_formas_pagos_egreso=mysqli_query($con,"DELETE FROM formas_pagos_ing_egr WHERE codigo_documento = '".$codigo_documento."'");
		
		if ($anular_encabezado_egreso && $anular_detalle_egreso && $anular_anular_formas_pagos_egreso){
				echo "<script>
				$.notify('Egreso anulado exitosamente','success')
				</script>";
		}else {
			echo "<script>
				$.notify('Lo siento, algo salio mal, intente nuevamente','error')
				</script>";
		}
	}
	
	
//PARA BUSCAR LOS EGRESOS
	if($action == 'egresos'){
		// escaping, additionally removing everything that could be (html/javascript-) code
		$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		 $egreso = mysqli_real_escape_string($con,(strip_tags($_REQUEST['egreso'], ENT_QUOTES)));
		 $aColumns = array('nombre_ing_egr', 'numero_ing_egr','detalle_adicional','fecha_ing_egr', 'valor_ing_egr');//Columnas de busqueda
		 $sTable = "ingresos_egresos";
		 $sWhere = "WHERE ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='EGRESO' " ;
		if ( $_GET['egreso'] != "" )
		{
			$sWhere = "WHERE (ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='EGRESO' AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$egreso."%' and ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='EGRESO' OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".$ruc_empresa."' and tipo_ing_egr='EGRESO' ", -3 );
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
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_ing_egr");'>Fecha</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_ing_egr");'>Pagado a</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("detalle_adicional");'>Detalle</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_ing_egr");'>Número</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado");'>Estado</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("valor_ing_egr");'>Total</button></th>
					<th class='text-right'>Opciones</th>
					
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$id_egreso=$row['id_ing_egr'];
						$codigo_documento=$row['codigo_documento'];
						$fecha_egreso=$row['fecha_ing_egr'];
						$detalle_adicional=$row['detalle_adicional'];
						$nombre_egreso=$row['nombre_ing_egr'];
						$numero_egreso=$row['numero_ing_egr'];
						$valor_egreso=$row['valor_ing_egr'];
						$estado=$row['estado'];
						$id_proveedor=$row['id_cli_pro'];
						
						$buscar_mail_proveedor=mysqli_query($con,"SELECT * FROM proveedores WHERE id_proveedor = '".$id_proveedor."' ");
						$row_mail_proveedor=mysqli_fetch_array($buscar_mail_proveedor);
						$mail_proveedor=empty($row_mail_proveedor['mail_proveedor'])?"":$row_mail_proveedor['mail_proveedor'];

						switch ($estado) {
					case "OK":
						$label_class_estado='label-success';
						break;
					case "ANULADO":
						$label_class_estado='label-danger';
						break;
						}

					?>
					<input type="hidden" value="<?php echo $id_egreso;?>" id="id_fecha<?php echo $id_egreso;?>">
					<input type="hidden" value="<?php echo $mail_proveedor;?>" id="mail_proveedor<?php echo $id_egreso;?>">
					<input type="hidden" value="<?php echo $numero_egreso;?>" id="numero_egreso<?php echo $id_egreso;?>">
					<input type="hidden" value="<?php echo $codigo_documento;?>" id="codigo_documento<?php echo $id_egreso;?>">
					<input type="hidden" value="<?php echo $detalle_adicional;?>" id="detalle_adicional_anterior<?php echo $id_egreso;?>">
					<input type="hidden" value="<?php echo $page;?>" id="pagina">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_egreso));?>" id="fecha_anterior_egreso<?php echo $id_egreso;?>">
					<tr>
						<td class='col-sm-2'>
						<input id="fecha_nueva_egreso<?php echo $id_egreso ?>" class="form-control text-center input-sm" value="<?php echo date("d-m-Y", strtotime($fecha_egreso)) ?>" onchange="modificar_fecha_egreso('<?php echo $id_egreso ?>')">
						</td>
						<td><?php echo strtoupper ($nombre_egreso); ?></td>
						<td class='col-sm-2'>
						<textarea id="detalle_adicional_nuevo<?php echo $id_egreso ?>" class="form-control text-left input-sm" onchange="modificar_detalle_egreso('<?php echo $id_egreso ?>')" ><?php echo  strtoupper ($detalle_adicional); ?></textarea>
						</td>
						<td><?php echo $numero_egreso;?></td>
						<td><span class="label <?php echo $label_class_estado;?>"><?php echo $estado; ?></span></td>
						<td><?php echo number_format($valor_egreso,2,'.',''); ?></td>
				
					<td class='col-md-2'><span class="pull-right">
					<a href="../pdf/pdf_egreso.php?action=egreso&codigo_documento=<?php echo $codigo_documento; ?>" class='btn btn-default btn-xs' title='Pdf' target="_blank">Pdf</a>
					<a href="#" class='btn btn-info btn-xs' title='Enviar egreso por mail' onclick="enviar_egreso_mail('<?php echo $id_egreso;?>')" data-toggle="modal" data-target="#EnviarDocumentosMail"><i class="glyphicon glyphicon-envelope"></i> </a>
					<a href="#" class='btn btn-info btn-xs' title='Detalle del egreso' onclick="mostrar_detalle_egreso('<?php echo $codigo_documento; ?>')" data-toggle="modal" data-target="#detalle_ingreso_egreso"><i class="glyphicon glyphicon-list"></i> </a>
					<a href="#" class='btn btn-warning btn-xs' title='Anular egreso' onclick="anular_egreso('<?php echo $id_egreso;?>')"><i class="glyphicon glyphicon-erase"></i> </a>				
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
	
	//para detalles de egresos
	if($action == 'detalle'){
		// escaping, additionally removing everything that could be (html/javascript-) code
		//$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 //$por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
         $detegr = mysqli_real_escape_string($con,(strip_tags($_REQUEST['detegr'], ENT_QUOTES)));
		 $aColumns = array('beneficiario_cliente','detalle_ing_egr','numero_ing_egr');//Columnas de busqueda
		 $sTable = "detalle_ingresos_egresos";
		 $sWhere = "WHERE ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' " ;
		if ( $_GET['detegr'] != "" )
		{
			$sWhere = "WHERE (ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$detegr."%' and ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' ", -3 );
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
					<th>Pagado a</th>
					<th>Número</th>
					<th>Valor</th>
					<th>Tipo</th>
					<th>Descripción</th>
					<th class='text-center'>Detalle</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$nombre_cliente=$row['beneficiario_cliente'];
						$numero_ingreso=$row['numero_ing_egr'];
						$tipo_ing_egr=$row['tipo_ing_egr'];
						$codigo_documento=$row['codigo_documento'];

						if(!is_numeric($tipo_ing_egr)){
						$tipo_asiento = mysqli_query($con, "SELECT * FROM asientos_tipo WHERE codigo='" . $tipo_ing_egr . "' ");
						$row_asiento = mysqli_fetch_assoc($tipo_asiento);
						$transaccion = $row_asiento['tipo_asiento'];
						}else{
						$tipo_pago = mysqli_query($con, "SELECT * FROM opciones_ingresos_egresos WHERE id='" . $tipo_ing_egr . "' and tipo_opcion ='2' ");
						$row_tipo_pago = mysqli_fetch_assoc($tipo_pago);
						$transaccion = $row_tipo_pago['descripcion'];
						}
						$valor_ing_egr = number_format($row['valor_ing_egr'], 2, '.', '');
						$detalle = $row['detalle_ing_egr'];
					?>
					<tr>
						<td><?php echo $nombre_cliente; ?></td>
						<td><?php echo $numero_ingreso; ?></td>
						<td><?php echo $valor_ing_egr; ?></td>
						<td><?php echo $transaccion; ?></td>
						<td><?php echo $detalle; ?></td>
						<td>
						<a href="#" class='btn btn-info btn-xs' title='Detalle del egreso' onclick="mostrar_detalle_egreso('<?php echo $codigo_documento; ?>')" data-toggle="modal" data-target="#detalle_ingreso_egreso"><i class="glyphicon glyphicon-list"></i> </a>
						</td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="9" ><span class="pull-right">
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
	
	//para buscar los pagos en los egresos
	if($action == 'pagos_egresos'){
		// escaping, additionally removing everything that could be (html/javascript-) code
         $detpago = mysqli_real_escape_string($con,(strip_tags($_REQUEST['detpago'], ENT_QUOTES)));
		 $aColumns = array('fecha_emision','numero_ing_egr','detalle_pago','cheque');//Columnas de busqueda
		 $sTable = "formas_pagos_ing_egr";
		 $sWhere = "WHERE ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' " ;
		if ( $_GET['detpago'] != "" ){
			$sWhere = "WHERE (ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$detpago."%' and ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO' OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".$ruc_empresa."' and tipo_documento='EGRESO'", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by cheque desc";
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
					<th>Número egreso</th>
					<th>Forma de pago</th>
					<th>Cuenta bancaria</th>
					<th>Valor</th>
					<th>Cheque</th>
					<th>Estado pago</th>
					<th class='text-center'>Detalle</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$numero_egreso=$row['numero_ing_egr'];
						$codigo_forma_pago=$row['codigo_forma_pago'];
						$valor_forma_pago=$row['valor_forma_pago'];
						$id_cuenta=$row['id_cuenta'];
						$cheque=$row['cheque'];
						$estado_pago=$row['estado_pago'];
						$codigo_documento=$row['codigo_documento'];
						
						if ($id_cuenta > 0) {
							$cuentas = mysqli_query($con, "SELECT cue_ban.id_cuenta as id_cuenta, concat(ban_ecu.nombre_banco,' ',cue_ban.numero_cuenta,' ', if(cue_ban.id_tipo_cuenta=1,'Aho','Cte')) as cuenta_bancaria FROM cuentas_bancarias as cue_ban INNER JOIN bancos_ecuador as ban_ecu ON cue_ban.id_banco=ban_ecu.id_bancos WHERE cue_ban.id_cuenta ='" . $id_cuenta . "'");
							$row_cuenta = mysqli_fetch_array($cuentas);
							$cuenta_bancaria = strtoupper($row_cuenta['cuenta_bancaria']);
							$forma_pago = $row['detalle_pago'];
							switch ($forma_pago) {
								case "D":
									$tipo = 'Débito';
									break;
								case "C":
									$tipo = 'Cheque';
									break;
								case "T":
									$tipo = 'Transferencia';
									break;
							}
							$forma_pago = $tipo;
						} 
						
						if($codigo_forma_pago>0) {
							$opciones_pagos = mysqli_query($con, "SELECT * FROM opciones_cobros_pagos WHERE id ='" . $codigo_forma_pago . "'");
							$row_opciones_pagos = mysqli_fetch_array($opciones_pagos);
							$forma_pago = strtoupper($row_opciones_pagos['descripcion']);
							$cuenta_bancaria = "";
						}
										
						$valor_forma_pago =  number_format($row['valor_forma_pago'], 2, '.', '');
								
								//$cuenta_bancaria = $nombre_banco."-".$tipo_cuenta_pago."-".$numero_cuenta;
					?>
					<tr>

						<td><?php echo $numero_egreso; ?></td>
						<td><?php echo $forma_pago; ?></td>
						<td><?php echo $cuenta_bancaria; ?></td>
						<td><?php echo number_format($valor_forma_pago,2,'.',''); ?></td>
						<td><?php echo $cheque; ?></td>
						<td><?php echo $estado_pago; ?></td>
						<td>
						<a href="#" class='btn btn-info btn-xs' title='Detalle del egreso' onclick="mostrar_detalle_egreso('<?php echo $codigo_documento; ?>')" data-toggle="modal" data-target="#detalle_ingreso_egreso"><i class="glyphicon glyphicon-list"></i> </a>
						</td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="9" ><span class="pull-right">
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

	//para buscar los pagos en los egresos
	if($action == 'detalle_cheques'){
		 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado_ch'], ENT_QUOTES)));
		 $id_cuenta = mysqli_real_escape_string($con,(strip_tags($_GET['cuenta'], ENT_QUOTES)));
		 //$ordenado = !isset($_GET['ordenado_ch'])?"numero_ing_egr":mysqli_real_escape_string($con,(strip_tags($_GET['ordenado_ch'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por_ch'], ENT_QUOTES)));
		 //$por = !isset($_GET['por_ch'])?"asc":mysqli_real_escape_string($con,(strip_tags($_GET['por_ch'], ENT_QUOTES)));
         $detcheque = mysqli_real_escape_string($con,(strip_tags($_REQUEST['detcheque'], ENT_QUOTES)));
		 $aColumns = array('for_pag.numero_ing_egr','for_pag.cheque','nombre_ing_egr','for_pag.fecha_emision');//Columnas de busqueda
		 $sTable = "formas_pagos_ing_egr as for_pag LEFT JOIN ingresos_egresos as ing_egr ON ing_egr.codigo_documento = for_pag.codigo_documento ";
		 $sWhere = "WHERE for_pag.ruc_empresa = '".$ruc_empresa."' and for_pag.tipo_documento='EGRESO' and for_pag.codigo_forma_pago='0' and for_pag.id_cuenta='".$id_cuenta."' and for_pag.detalle_pago='C' " ;
		if ( $_GET['detcheque'] != "" ){
			$sWhere = "WHERE for_pag.ruc_empresa = '".$ruc_empresa."' and for_pag.tipo_documento='EGRESO' and for_pag.codigo_forma_pago='0' and for_pag.id_cuenta='".$id_cuenta."' and for_pag.detalle_pago='C' AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ ) 
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$detcheque."%' and for_pag.ruc_empresa = '".$ruc_empresa."' and for_pag.tipo_documento='EGRESO' and for_pag.codigo_forma_pago='0' and for_pag.id_cuenta='".$id_cuenta."' and for_pag.detalle_pago='C' OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND for_pag.ruc_empresa = '".$ruc_empresa."' and for_pag.tipo_documento='EGRESO' and for_pag.codigo_forma_pago='0' and for_pag.id_cuenta='".$id_cuenta."' and for_pag.detalle_pago='C'", -3 );
			//$sWhere .= '';
		}
		$sWhere.=" order by ". $ordenado." ".$por;//for_pag.cheque desc	
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
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.cheque");'>Número Cheque</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.fecha_emision");'>Fecha emisión</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("ing_egr.nombre_ing_egr");'>Beneficiario</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.fecha_entrega");'>Fecha cobro</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.fecha_pago");'>Fecha cheque</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.id_cuenta");'>Cuenta bancaria</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.valor_forma_pago");'>Valor</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar_ch("for_pag.estado_pago");'>Estado cheque</button></th>
				<th class="text-right">Imprimir</th>
				<th class="text-center">Detalle</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$codigo_documento=$row['codigo_documento'];
						$id_forma_pago=$row['id_fp'];
						$id_beneficiario=$row['id_cli_pro'];
						$numero_cheque=$row['cheque'];
						$estado_pago=$row['estado_pago'];
						if ($estado_pago=='ENTREGAR'){
						$fecha_entrega="PENDIENTE";
						}else{
						$fecha_entrega=date("d-m-Y", strtotime($row['fecha_entrega']));
						}
						$beneficiario=$row['nombre_ing_egr'];
						$fecha_emision=$row['fecha_emision'];
						$fecha_pago=date("d-m-Y", strtotime($row['fecha_pago']));
						$valor_forma_pago=$row['valor_forma_pago'];
						$id_cuenta=$row['id_cuenta'];
						$cheque=$row['cheque'];
						$forma_pago=$row['codigo_forma_pago'];
						//para buscar el detalle de la cuenta bancaria
							$sql_cuenta_bancaria = "SELECT * FROM cuentas_bancarias where id_cuenta='".$id_cuenta."' ";
							$respuesta_cuenta_bancaria = mysqli_query($con,$sql_cuenta_bancaria);
							$row_cuenta_bancaria = mysqli_fetch_array($respuesta_cuenta_bancaria);
							$numero_cuenta=$row_cuenta_bancaria['numero_cuenta'];
							$tipo_cuenta=$row_cuenta_bancaria['id_tipo_cuenta'];
							$id_banco=$row_cuenta_bancaria['id_banco'];
							
						//para buscar el banco
							$sql_bancos = "SELECT * FROM bancos_ecuador where id_bancos= '".$id_banco."'";
							$respuesta_bancos = mysqli_query($con,$sql_bancos);
							$row_banco = mysqli_fetch_array($respuesta_bancos);	
							$nombre_banco=$row_banco['nombre_banco'];
							
							switch ($tipo_cuenta){
							case 1:
								$tipo_cuenta_pago='AHORROS';
								break;
							case 2:
								$tipo_cuenta_pago='CORRIENTE';
								break;
							case 3:
								$tipo_cuenta_pago='VIRTUAL';
								break;
							case 4:
								$tipo_cuenta_pago='TARJETA';
								default;
								$tipo_cuenta_pago='';
								}
							
							switch ($forma_pago){
							case '01':
								$forma_pago='EFECTIVO';
								break;
							case '02':
								$forma_pago='CHEQUE';
								break;
							case '03':
								$forma_pago='TRANSFERENCIA';
								break;
							case '04':
								$forma_pago='DÉBITO AUTOMÁTICO';
								break;
							case '05':
								$forma_pago='TARJETA DE CRÉDITO/DÉBITO';
								break;
								}
								
								$cuenta_bancaria = $nombre_banco."-".$tipo_cuenta_pago."-".$numero_cuenta;
					?>
					<tr>
						<input type="hidden" value="<?php echo $page;?>" id="pagina">
						<input type="hidden" id="fecha_entrega_actual_cheque<?php echo $id_forma_pago;?>" value="<?php echo $fecha_entrega;?>">
						<input type="hidden" id="estado_actual_cheque<?php echo $id_forma_pago;?>" value="<?php echo $estado_pago;?>">
						<input type="hidden" id="nombre_actual_cheque<?php echo $id_forma_pago;?>" value="<?php echo $beneficiario;?>">
						<input type="hidden" id="id_beneficiario_actual<?php echo $id_forma_pago;?>" value="<?php echo $id_beneficiario;?>">
						<input type="hidden" id="codigo_documento<?php echo $id_forma_pago;?>" value="<?php echo $codigo_documento;?>">
						<input type="hidden" id="id_beneficiario_final">
						<td><?php echo $numero_cheque; ?></td>
						<td><?php echo date("d-m-Y", strtotime($fecha_emision)); ?></td>
						<td class="col-xs-3"><textarea id="beneficiario_final_cheque<?php echo $id_forma_pago ?>" class="form-control text-center" title="Busque un nombre ya registrado como proveedor" onkeyup="buscar_beneficiarios('<?php echo $id_forma_pago ?>');" onchange="modificar_beneficiario('<?php echo $id_forma_pago ?>')"><?php echo $beneficiario ?></textarea></td>
						<td class="col-xs-2">
						<input id="fecha_entrega_cheque<?php echo $id_forma_pago ?>" class="form-control text-center" value="<?php echo $fecha_entrega ?>" onchange="modificar_fecha_entrega_cheque('<?php echo $id_forma_pago ?>')" <?php if ($estado_pago=='ENTREGAR'){ echo "readonly";} ?>>
						</td>
						<td class="col-xs-2"><?php echo $fecha_pago; ?></td>
						<td class="col-xs-2"><?php echo $cuenta_bancaria; ?></td>
						<td><?php echo number_format($valor_forma_pago,2,'.',''); ?></td>
						<td class="col-xs-2">
						<select class="form-control" name="estado_cheque" id="estado_cheque<?php echo $id_forma_pago ?>" onchange="modificar_estado_cheque('<?php echo $id_forma_pago ?>')" <?php if ($estado_pago =='ANULADO'){ echo "disabled";} ?>>
						<?php
							$estados_pagos=array("POR COBRAR"=>"POR COBRAR","ANULADO"=>"ANULADO","ENTREGAR"=>"ENTREGAR","PAGADO"=>"PAGADO");
							foreach ($estados_pagos as $estado ){
								if ($estado == $estado_pago){
								?>
								<option value="<?php echo $estado_pago ?>"selected><?php echo $estado_pago ?> </option>
								<?php
								}else{			
								?>
								<option value="<?php echo $estado ?>"><?php echo $estado ?> </option>
								<?php
								}
							}
							?>
						</select>
						</td>
						<td class='col-md-2'><span class="pull-right">
						<a href="../pdf/pdf_cheque.php?action=cheque&codigo_documento=<?php echo $id_forma_pago; ?>" class='btn btn-default btn-xs' title='Imprimir' target="_blank"><i class="glyphicon glyphicon-print"></i></a>
						</span></td>
						<td>
						<a href="#" class='btn btn-info btn-xs' title='Detalle del egreso' onclick="mostrar_detalle_egreso('<?php echo $codigo_documento; ?>')" data-toggle="modal" data-target="#detalle_ingreso_egreso"><i class="glyphicon glyphicon-list"></i> </a>
						</td>
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="10"><span class="pull-right">
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
