<?php
include("../conexiones/conectalogin.php");
include("../validadores/generador_codigo_unico.php");
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
ini_set('date.timezone','America/Guayaquil');
$fecha_registro=date("Y-m-d H:i:s");

if($action == 'libro_diario'){
	//$tipo_busqueda = mysqli_real_escape_string($con,(strip_tags($_REQUEST['busqueda'], ENT_QUOTES)));
	 $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
	 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
	 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
	// $aColumns = array('fecha_asiento','numero_asiento','concepto_general','tipo','detalle_item');//Columnas de busqueda
	$aColumns = array('fecha_asiento','numero_asiento','concepto_general','tipo');//Columnas de busqueda
	$sTable = "encabezado_diario as enc_dia ";
	//$sTable = "encabezado_diario as enc_dia LEFT JOIN detalle_diario_contable as det_dia ON det_dia.codigo_unico=enc_dia.codigo_unico ";
	 $sWhere = "WHERE enc_dia.ruc_empresa = '".$ruc_empresa."' " ;
	if ( $_GET['q'] != "" )
	{
		$sWhere = "WHERE enc_dia.ruc_empresa = '".$ruc_empresa."' AND ";
		
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND enc_dia.ruc_empresa = '".$ruc_empresa."' OR ";
		}
		$sWhere = substr_replace( $sWhere, "AND enc_dia.ruc_empresa = '".$ruc_empresa."' ", -3 );
	}
	$sWhere.=" order by $ordenado $por";// group by enc_dia.numero_asiento order by $ordenado $por

	include ("../ajax/pagination.php"); //include pagination file
	//pagination variables
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
	$per_page = 20; //how much records you want to show
	$adjacents  = 4; //gap between pages after number of adjacents
	$offset = ($page - 1) * $per_page;
	//Count the total number of row in your table*/
	$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
	$row= mysqli_fetch_array($count_query);
	$numrows = $row['numrows'];
	$total_pages = ceil($numrows/$per_page);
	$reload = '../libro_diario.php';
	//main query to fetch the data
	$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset, $per_page";
	$query = mysqli_query($con, $sql);
	//loop through fetched data
	if ($numrows>0){
		
		?>
		<div class="panel panel-info">
		<div class="table-responsive">
		  <table class="table">
			<tr  class="info">
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_asiento");'>Fecha</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_asiento");'>Asiento</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("tipo");'>Tipo</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("concepto_general");'>Concepto</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado");'>Estado</button></th>
				<th class='text-right'>Opciones</th>
			
			</tr>
			<?php
			while ($row=mysqli_fetch_array($query)){
					$id_diario=$row['id_diario'];
					$id_documento=$row['id_documento'];
					$codigo_unico=$row['codigo_unico'];
					$fecha_asiento=date('d-m-Y', strtotime($row['fecha_asiento']));
					$numero_asiento=$row['numero_asiento'];
					$concepto_general=$row['concepto_general'];
					$estado=$row['estado'];
					$tipo=$row['tipo'];
					switch ($estado) {
					case "Editado":
						$label_class='label-warning';
						break;
					case "Anulado":
						$label_class='label-danger';
						break;
					case "ok":
						$label_class='label-success';
						break;
						}
				?>
				<input type="hidden" value="<?php echo $numero_asiento;?>" id="numero_asiento<?php echo $id_diario;?>">
				<input type="hidden" value="<?php echo $concepto_general;?>" id="mod_concepto_general<?php echo $id_diario;?>">
				<input type="hidden" value="<?php echo $fecha_asiento;?>" id="mod_fecha_asiento<?php echo $id_diario;?>">
				<input type="hidden" value="<?php echo $codigo_unico;?>" id="mod_codigo_unico<?php echo $id_diario;?>">
				<input type="hidden" value="<?php echo $id_documento;?>" id="mod_id_documento<?php echo $id_diario;?>">
				<input type="hidden" value="<?php echo $tipo;?>" id="mod_tipo<?php echo $id_diario;?>">
				<tr>						
					<td class='col-md-2'><?php echo $fecha_asiento; ?></td>
					<td><?php echo $numero_asiento; ?></td>
					<td><?php echo $tipo; ?></td>
					<td><?php echo $concepto_general; ?></td>
					<td><span class="label <?php echo $label_class;?>"><?php echo strtoupper ($estado); ?></span></td>
				<td class='col-md-3'><span class="pull-right">
				<a href="../pdf/pdf_diario_contable.php?action=diario_contable&id_diario=<?php echo $id_diario ?>" class='btn btn-default btn-xs' title='Pdf' target="_blank"><img src="../image/pdf.ico" width="18" height="18"></a>
				<a href="../excel/reporte_diario_contable_excel.php?action=diario_contable&id_diario=<?php echo $id_diario ?>" class='btn btn-success btn-xs' title='Excel' target="_blank"><img src="../image/excel.ico" width="18" height="18"></a>					
				<a href="#" class='btn btn-info btn-xs' title='Detalle asiento' onclick="detalle_asiento('<?php echo $id_diario;?>');" data-toggle="modal" data-target="#detalleDocumentoContable"><i class="glyphicon glyphicon-list"></i></a> 
				<a href="#" class='btn btn-info btn-xs' title='Editar asiento' onclick="obtener_datos('<?php echo $id_diario;?>');" data-toggle="modal" data-target="#NuevoDiarioContable"><i class="glyphicon glyphicon-refresh"></i></a>
				<?php
				if ($tipo=='DIARIO'){
				?>
				<a href="#" class='btn btn-info btn-xs' title='Duplicar asiento' onclick="duplicar_asiento('<?php echo $id_diario;?>');" ><i class="glyphicon glyphicon-duplicate"></i></a> 
				<?php
				}
				?>
				<a href="#" class='btn btn-danger btn-xs' title='Eliminar asiento' onclick="eliminar_asiento('<?php echo $id_diario;?>');"><i class="glyphicon glyphicon-erase"></i></a> 	
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="8"><span class="pull-right">
				<?php
				 echo paginate($reload, $page, $total_pages, $adjacents);
				?>
				</span></td>
			</tr>
		  </table>
		</div>
		</div>
		<?php
	}
}


if($action == 'detalle_asientos'){
	//$tipo_busqueda = mysqli_real_escape_string($con,(strip_tags($_REQUEST['busqueda'], ENT_QUOTES)));
	 $d = mysqli_real_escape_string($con,(strip_tags($_REQUEST['d'], ENT_QUOTES)));
	 //$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
	 //$por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
	//$aColumns = array('det_dia.detalle_item');//Columnas de busqueda
	$aColumns = array('det_dia.detalle_item','plan.codigo_cuenta','plan.nombre_cuenta');//Columnas de busqueda
	//$sTable = "detalle_diario_contable as det_dia ";
	$sTable = "detalle_diario_contable as det_dia INNER JOIN plan_cuentas as plan ON plan.id_cuenta=det_dia.id_cuenta ";
	 $sWhere = "WHERE det_dia.ruc_empresa = '".$ruc_empresa."' " ;
	if ( $_GET['d'] != "" )
	{
		$sWhere = "WHERE det_dia.ruc_empresa = '".$ruc_empresa."' AND ";
		
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".$d."%' AND det_dia.ruc_empresa = '".$ruc_empresa."' OR ";
		}
		$sWhere = substr_replace( $sWhere, "AND det_dia.ruc_empresa = '".$ruc_empresa."' ", -3 );
	}
	$sWhere.=" order by id_detalle_cuenta desc ";//$ordenado $por

	include ("../ajax/pagination.php"); //include pagination file
	//pagination variables
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
	$per_page = 20; //how much records you want to show
	$adjacents  = 4; //gap between pages after number of adjacents
	$offset = ($page - 1) * $per_page;
	//Count the total number of row in your table*/
	$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
	$row= mysqli_fetch_array($count_query);
	$numrows = $row['numrows'];
	$total_pages = ceil($numrows/$per_page);
	$reload = '../libro_diario.php';
	//main query to fetch the data
	$sql="SELECT det_dia.detalle_item as detalle_item, det_dia.codigo_unico as codigo_unico, plan.codigo_cuenta as codigo_cuenta, plan.nombre_cuenta as nombre_cuenta, det_dia.debe as debe, det_dia.haber as haber FROM  $sTable $sWhere LIMIT $offset, $per_page";
	$query = mysqli_query($con, $sql);
	//loop through fetched data
	if ($numrows>0){
		
		?>
		<div class="panel panel-info">
		<div class="table-responsive">
		  <table class="table">
			<tr  class="info">
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("detalle_item");'>Detalle</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_asiento");'>Asiento</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_cuenta");'>Código</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_cuenta");'>Cuenta</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("debe");'>Debe</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("haber");'>Haber</button></th>
			
			</tr>
			<?php
			while ($row=mysqli_fetch_array($query)){
					$codigo_unico=$row['codigo_unico'];
					$detalle_item=$row['detalle_item'];
					$codigo_cuenta=$row['codigo_cuenta'];
					$nombre_cuenta=$row['nombre_cuenta'];
					$debe=$row['debe'];
					$haber=$row['haber'];
					$query_numero_asiento = mysqli_query($con, "SELECT * FROM encabezado_diario WHERE codigo_unico='".$codigo_unico."' and ruc_empresa='".$ruc_empresa."' ");
					$row_asiento=mysqli_fetch_array($query_numero_asiento);
					$numero_asiento=$row_asiento['numero_asiento'];

				?>
				<tr>						
					<td><?php echo $detalle_item; ?></td>
					<td><?php echo $numero_asiento; ?></td>
					<td><?php echo $codigo_cuenta; ?></td>
					<td><?php echo $nombre_cuenta; ?></td>
					<td><?php echo $debe; ?></td>
					<td><?php echo $haber; ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="8"><span class="pull-right">
				<?php
				 echo paginate($reload, $page, $total_pages, $adjacents);
				?>
				</span></td>
			</tr>
		  </table>
		</div>
		</div>
		<?php
	}
}

	if($action == 'duplicar_asiento'){
	$id_diario = mysqli_real_escape_string($con,(strip_tags($_GET['codigo_unico'], ENT_QUOTES)));
	$nuevo_codigo_unico=codigo_unico(20);
	$numero_diario=mysqli_query($con,"select max(numero_asiento) as asiento from encabezado_diario where ruc_empresa = '".$ruc_empresa."'");
	$row_numero_diario=mysqli_fetch_array($numero_diario);
	$numero_asiento=$row_numero_diario['asiento']+1;

	$sql_registro = mysqli_query($con, "SELECT * FROM encabezado_diario WHERE id_diario='".$id_diario."'");
	$row_registro = mysqli_fetch_array($sql_registro);
	$codigo_unico = $row_registro['codigo_unico'];
	
	$query_guarda_encabezado = mysqli_query($con, "INSERT INTO encabezado_diario (id_diario, ruc_empresa, codigo_unico, fecha_asiento, numero_asiento, concepto_general, estado, id_usuario, fecha_registro, tipo, id_documento, codigo_unico_bloque) 
	SELECT null, '".$ruc_empresa."', '".$nuevo_codigo_unico."', fecha_asiento, '".$numero_asiento."', concepto_general, 'ok', '".$id_usuario."', '".$fecha_registro."', 'DIARIO', '0', '".$nuevo_codigo_unico."' FROM encabezado_diario WHERE id_diario ='".$id_diario."'");

	$query_guarda_detalle = mysqli_query($con, "INSERT INTO detalle_diario_contable (id_detalle_cuenta, ruc_empresa, codigo_unico, id_cuenta, debe, haber, detalle_item, codigo_unico_bloque, id_cli_pro) 
	SELECT null, '".$ruc_empresa."', '".$nuevo_codigo_unico."', id_cuenta, debe, haber, detalle_item, '".$nuevo_codigo_unico."', id_cli_pro FROM detalle_diario_contable WHERE ruc_empresa = '".$ruc_empresa."' and codigo_unico='".$codigo_unico."'");

			if ($query_guarda_encabezado && $query_guarda_detalle){
				echo "<script>$.notify('Asiento contable duplicado.','success');
					</script>";
			} else{
				echo "<script>$.notify('Lo siento algo ha salido mal intenta nuevamente.','error')</script>";
			}
	
}

//eliminar numero de asiento de los registros de facturas, retenciones y compras, ingresos y egresos
if($action == 'eliminar_asiento'){
	$id_diario = mysqli_real_escape_string($con,(strip_tags($_GET['codigo_unico'], ENT_QUOTES)));
	$sql_registro = mysqli_query($con, "SELECT * FROM encabezado_diario WHERE id_diario='".$id_diario."'");
	$row_registro = mysqli_fetch_array($sql_registro);
	$tipo_registro = strtoupper($row_registro['tipo']);
	$numero_asiento = $row_registro['numero_asiento'];
	$codigo_unico = $row_registro['codigo_unico'];
	$update_encabezado = mysqli_query($con, "UPDATE encabezado_diario SET codigo_unico='' , estado='Anulado', id_usuario='".$id_usuario."', fecha_registro='".$fecha_registro."' WHERE id_diario='".$id_diario."'");
	$eliminar_asiento=eliminar_registro_asiento($con, $codigo_unico);
	
	switch ($tipo_registro) {
			case "VENTAS":
				$update_encabezado_ventas = mysqli_query($con, "UPDATE encabezado_factura SET id_registro_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and id_registro_contable='".$numero_asiento."'");
				break;
			case "NC_VENTAS":
				$update_encabezado_nc_ventas = mysqli_query($con, "UPDATE encabezado_nc SET id_registro_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and id_registro_contable='".$numero_asiento."'");
				break;
			case "RETENCIONES_VENTAS":
				$update_encabezado_ret_ventas = mysqli_query($con, "UPDATE encabezado_retencion_venta SET id_registro_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and id_registro_contable='".$numero_asiento."'");
				break;
			case "RETENCIONES_COMPRAS":
				$update_encabezado_ventas = mysqli_query($con, "UPDATE encabezado_retencion SET id_registro_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and id_registro_contable='".$numero_asiento."'");
				break;
			case "COMPRAS_SERVICIOS":
				$update_encabezado_ventas = mysqli_query($con, "UPDATE encabezado_compra SET id_registro_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and id_registro_contable='".$numero_asiento."'");
				break;
			case "INGRESOS":
				$update_encabezado_ingresos = mysqli_query($con, "UPDATE ingresos_egresos SET codigo_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and codigo_contable='".$numero_asiento."' and tipo_ing_egr='INGRESO'");
				break;
			case "EGRESOS":
				$update_encabezado_egresos = mysqli_query($con, "UPDATE ingresos_egresos SET codigo_contable='0' WHERE ruc_empresa='".$ruc_empresa."' and codigo_contable='".$numero_asiento."' and tipo_ing_egr='EGRESO'");
			break;
			}

			if ($update_encabezado){
				echo "<script>$.notify('Asiento contable anulado.','success');
					</script>";
			} else{
				echo "<script>$.notify('Lo siento algo ha salido mal intenta nuevamente.','error')</script>";
			}

}

function eliminar_registro_asiento($con, $codigo_unico){
	$eliminar_detalle_diario=mysqli_query($con,"DELETE FROM detalle_diario_contable WHERE codigo_unico='".$codigo_unico."'");
}
?>