<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	include("../clases/anular_registros.php");
	require_once("../helpers/helpers.php");
	$anular_asiento_contable = new anular_registros(); 
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$fecha_registro=date("Y-m-d H:i:s");
//PARA ELIMINAR RETENCIONES HECHAS
if (isset($_POST['id_retencion'])){
		$id_retencion=intval($_POST['id_retencion']);
		$busca_datos_retencion = mysqli_query($con,"SELECT * FROM encabezado_retencion WHERE id_encabezado_retencion = '".$id_retencion."'");
		$datos_retencion = mysqli_fetch_array($busca_datos_retencion);
		$serie_retencion =$datos_retencion['serie_retencion'];
		$secuencial =$datos_retencion['secuencial_retencion'];
		/*
		$mes_periodo = date("m", strtotime($datos_retencion['fecha_emision']));
		$anio_periodo = date("Y", strtotime($datos_retencion['fecha_emision']));
		$ruc_empresa_periodo = $datos_retencion['ruc_empresa'];
		$ruc_del_proveedor = $datos_retencion['ruc_proveedor'];
		*/

		$id_registro_contable=$datos_retencion['id_registro_contable'];
		$anio_documento=date("Y", strtotime($datos_retencion['fecha_emision']));
		$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
		
		
		//eliminar la factura y los datos de la factura
		if ($delete=mysqli_query($con,"DELETE FROM encabezado_retencion WHERE id_encabezado_retencion = '".$id_retencion."'") 
			&& $delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_retencion WHERE ruc_empresa = '".$ruc_empresa."' and serie_retencion = '".$serie_retencion."' and secuencial_retencion = '".$secuencial."'")
			&& $delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_retencion WHERE ruc_empresa = '".$ruc_empresa."' and serie_retencion = '".$serie_retencion."' and secuencial_retencion = '".$secuencial."'")){
			echo "<script>
				$.notify('Retención eliminada','success')
				</script>";
		}else {
			echo "<script>
				$.notify('Lo siento algo ha salido mal intenta nuevamente.','error')
				</script>";			
		}
	}

//PARA ANULAR RETENCIONES HECHAS
if (isset($_POST['id_retencion_anular'])){
		$id_usuario = $_SESSION['id_usuario'];
		$id_factura=intval($_POST['id_retencion_anular']);
		
		$busca_datos_retencion = "SELECT * FROM encabezado_retencion WHERE id_encabezado_retencion = $id_factura ";
		$result = $con->query($busca_datos_retencion);
		$datos_retencion = mysqli_fetch_array($result);
		$serie_retencion =$datos_retencion['serie_retencion'];
		$secuencial =$datos_retencion['secuencial_retencion'];
		$mes_periodo = date("m", strtotime($datos_retencion['fecha_emision']));
		$anio_periodo = date("Y", strtotime($datos_retencion['fecha_emision']));
		$ruc_empresa_periodo = $datos_retencion['ruc_empresa'];
		
		$id_registro_contable=$datos_retencion['id_registro_contable'];
		$anio_documento=date("Y", strtotime($datos_retencion['fecha_emision']));
		$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);
		
		
		//anular la factura y los datos de la factura
		if ($delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_retencion WHERE ruc_empresa = '$ruc_empresa' and serie_retencion = '$serie_retencion' and secuencial_retencion = $secuencial")
			&& $delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_retencion WHERE ruc_empresa = '$ruc_empresa' and serie_retencion = '$serie_retencion' and secuencial_retencion = $secuencial")
			&& $anular=mysqli_query($con,"UPDATE encabezado_retencion SET estado_sri='ANULADA', total_retencion ='0.00', id_usuario= '$id_usuario' WHERE id_encabezado_retencion = $id_factura ")		
			){
			echo "<script>alert('Retención anulada exitosamente.')</script>";
			echo "<script>window.close();</script>";
		}else {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Error!</strong> Lo siento algo ha salido mal intenta nuevamente.
			</div>
			<?php
			
		}
	}
	
	
//PARA BUSCAR LAS RETENCIONES
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'ajax'){
		// escaping, additionally removing everything that could be (html/javascript-) code
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		 $aColumns = array('fecha_emision','secuencial_retencion', 'serie_retencion','numero_comprobante','tipo_comprobante','razon_social','nombre_comercial');//Columnas de busqueda
		 $sTable = "encabezado_retencion er, comprobantes_autorizados ca, proveedores pr";
		 $sWhere = "WHERE er.ruc_empresa ='".  $ruc_empresa ." '  AND er.tipo_comprobante = ca.codigo_comprobante AND er.id_proveedor = pr.id_proveedor " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (er.ruc_empresa ='".  $ruc_empresa ." ' AND er.tipo_comprobante = ca.codigo_comprobante AND er.id_proveedor = pr.id_proveedor AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND er.ruc_empresa ='".  $ruc_empresa ." '  AND er.tipo_comprobante = ca.codigo_comprobante AND er.id_proveedor = pr.id_proveedor OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND er.ruc_empresa = '".  $ruc_empresa ."'   AND er.tipo_comprobante = ca.codigo_comprobante AND er.id_proveedor = pr.id_proveedor ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by $ordenado $por";
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
		$reload = '../retenciones_compras.php';
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
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_emision");'>Fecha retención</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_documento");'>Fecha documento</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("razon_social");'> Proveedor</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_retencion");'>Número retención</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("total_retencion");'>Total</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("tipo_comprobante");'>Documento</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_comprobante");'>#Documento</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado_sri");'>Estado SRI</button></th>
					<th class='text-right'>Opciones</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$id_encabezado_retencion=$row['id_encabezado_retencion'];
						$fecha_retencion=$row['fecha_emision'];
						$fecha_documento=$row['fecha_documento'];
						$serie_retencion=$row['serie_retencion'];
						$secuencial_retencion=$row['secuencial_retencion'];
						$nombre_proveedor=$row['razon_social'];
						$ruc_proveedor=$row['ruc_proveedor'];
						$tipo_documento=$row['comprobante'];
						$numero_comprobante=$row['numero_comprobante'];
						$estado_sri=$row['estado_sri'];
						$total_retencion=$row['total_retencion'];
						$ambiente=$row['ambiente'];
						$mail_proveedor = $row['mail_proveedor'];
						$estado_mail = $row['estado_mail'];
						$aut_sri = $row['aut_sri'];
						
						$numero_ret = $serie_retencion . "-" . str_pad($secuencial_retencion, 9, "000000000", STR_PAD_LEFT);
					
					//estado sri
					switch ($estado_sri) {
					case "PENDIENTE":
						$label_class_sri='label-warning';
						break;
					case "ANULADA":
						$label_class_sri='label-danger';;
						break;
					case "NO APLICA":
						$label_class_sri='label-info';;
						break;
					case "AUTORIZADO":
						$label_class_sri='label-success';
						break;
						}
					
					
					//ambiente
					switch ($ambiente) {
					case "0":
						$label_class_ambiente='label-warning';
						$tipo_ambiente = 'EMITIDA';
						break;
					case "1":
						$label_class_ambiente='label-info';
						$tipo_ambiente = 'PRUEBAS';
						break;
					case "2":
						$label_class_ambiente='label-success';
						$tipo_ambiente = 'PRODUCCIÓN';
						break;
						}
						
						//estado mail
					switch ($estado_mail) {
					case "PENDIENTE":
						$estado_mail_final='btn btn-default btn-xs';
						break;
					case "ENVIADO":
						$estado_mail_final='btn btn-info btn-xs';;
						break;
						}
					?>
					<input type="hidden" value="<?php echo $ruc_proveedor;?>" id="ruc_proveedor<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $aut_sri;?>" id="aut_sri<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $mail_proveedor;?>" id="mail_proveedor<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $id_encabezado_retencion;?>" id="id_encabezado_retencion<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $serie_retencion;?>" id="serie_retencion<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $secuencial_retencion;?>" id="secuencial_retencion<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_retencion));?>" id="fecha_retencion<?php echo $id_encabezado_retencion;?>">
					<tr>
						<td><?php echo date("d/m/Y", strtotime($fecha_retencion)); ?></td>
						<td><?php echo date("d/m/Y", strtotime($fecha_documento)); ?></td>
    					<td class="col-xs-2"><?php echo strtoupper ($nombre_proveedor); ?></td>
						<td><?php echo $serie_retencion; ?>-<?php echo str_pad($secuencial_retencion,9,"000000000",STR_PAD_LEFT); ?></td>
						<td><?php echo $total_retencion; ?></td>
						<td class="col-xs-1"><?php echo strtoupper ($tipo_documento); ?></td>
						<td class="col-xs-1"><?php echo $numero_comprobante; ?></td>
						<td><span class="label <?php echo $label_class_sri;?>"><?php echo $estado_sri; ?></span></td>
					<td><span class="pull-right">
					
					<?php
					//PARA ENVIAR AL SRI
					$tipo_retencion="ELECTRÓNICA";
					if ($tipo_retencion=="ELECTRÓNICA"){
						switch ($estado_sri) {
							case "PENDIENTE";
							case "DEVUELTA";
							?>	
							<a href="#" class='btn btn-success btn-xs' onclick="enviar_retencion_sri('<?php echo $id_encabezado_retencion;?>');" title='Enviar al SRI' data-toggle="modal" data-target="#EnviarDocumentosSri"><i class="glyphicon glyphicon-send"></i></a>
							<?php
							break;
							}
					}

					switch ($estado_sri) {
							case "AUTORIZADO";
							case "ANULADA";
					$status_servidor=status_servidor(); //esto le puse que venga de helpers cuando el servidor no quiere descargar las facturas
					if($status_servidor){
					?>
					<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_retencion) ?>&tipo_documento=retencion&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Ver' download target="_blank">Pdf</i> </a>
					<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_retencion) ?>&tipo_documento=retencion&tipo_archivo=xml" class='btn btn-default btn-xs' title='Ver' download target="_blank">Xml</i> </a>
					<?php
					}else{
						?>
					<a href="http://64.225.69.65:8000/retenciones_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_proveedor ?>/CR<?php echo $numero_ret ?>.pdf" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Pdf</i> </a>
					<a href="http://64.225.69.65:8000/retenciones_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_proveedor ?>/CR<?php echo $numero_ret ?>.xml" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Xml</i> </a>
						<?php
						}
						break;
					}
					//para anular una retencion autorizada por el sri
					if ($tipo_retencion=="ELECTRÓNICA" && $estado_sri == "AUTORIZADO" && $id_usuario >0){
					?>
					<a href="#" class='btn btn-warning btn-xs' title='Anular retención' data-toggle="modal" data-target="#AnularDocumentosSri" onclick="pasa_codigo_anular_retencion_e('<?php echo $id_encabezado_retencion; ?>')"><i class="glyphicon glyphicon-remove"></i> </a>
					<?php
					}
					
					//para cuando esta anulada y ambiente de produccion
					if ($tipo_retencion=="ELECTRÓNICA" && $estado_sri == "PENDIENTE" ){
					?>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar retencion' onclick="eliminar_retencion_compras('<?php echo $id_encabezado_retencion; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					<?php
					}
					?>
					<a href="#" class='btn btn-info btn-xs' onclick="detalle_retencion_compra('<?php echo $id_encabezado_retencion; ?>')" title="Detalle documento" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list-alt"></i></a>
					<?php
					if ($tipo_retencion=="ELECTRÓNICA" && $estado_sri =="AUTORIZADO"){
					?>
					<a href="#" class="<?php echo $estado_mail_final;?>" onclick="enviar_retencion_mail('<?php echo $id_encabezado_retencion;?>');" title='Enviar por mail al proveedor' data-toggle="modal" data-target="#EnviarDocumentosMail"><i class="glyphicon glyphicon-envelope"></i> </a>
					<?php
					}
					?>
					</span></td>
					
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan=10 ><span class="pull-right">
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