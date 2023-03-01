<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	require_once("../helpers/helpers.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$fecha_registro=date("Y-m-d H:i:s");
	if (isset($_SESSION['id_usuario'])){
		$delete_factura_tmp = mysqli_query($con, "DELETE FROM factura_tmp WHERE id_usuario = '".$id_usuario."'");
		$delete_adicional_tmp = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."'");
	}
//PARA ELIMINAR lc ECHAS
if (isset($_POST['id_lc'])){
		$id_lc=intval($_POST['id_lc']);
					$busca_datos_lc = mysqli_query($con, "SELECT * FROM encabezado_liquidacion WHERE id_encabezado_liq = '".$id_lc."' ");
					$datos_lc = mysqli_fetch_array($busca_datos_lc);
					$codigo_unico=$datos_lc['codigo_unico'];
		
		//eliminar la liq y los datos de la liq
			$delete_encabezado=mysqli_query($con,"DELETE FROM encabezado_liquidacion WHERE id_encabezado_liq = '".$id_lc."' ");
			$delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_liquidacion WHERE codigo_unico = '".$codigo_unico."' ");
			$delete_pago=mysqli_query($con,"DELETE FROM formas_pago_liquidacion WHERE codigo_unico = '".$codigo_unico."'");	
			$delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_liquidacion WHERE codigo_unico = '".$codigo_unico."'");
			

		if ($delete_encabezado && $delete_detalle && $delete_pago && $delete_adicional){
			echo "<script>
				$.notify('Liquidación eliminada exitosamente','success')
				</script>";
		}else {
			echo "<script>
				$.notify('Lo siento algo ha salido mal intenta nuevamente','error')
				</script>";
		}
	}


//PARA BUSCAR LAS liquidaciones
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'ajax'){
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		 $aColumns = array('fecha_liquidacion','secuencial_liquidacion', 'serie_liquidacion','razon_social','nombre_comercial','ruc_proveedor', 'estado_sri');//Columnas de busqueda
		 $sTable = "encabezado_liquidacion as el, proveedores as pro";
		 $sWhere = "WHERE el.ruc_empresa ='".  $ruc_empresa ." '  AND el.id_proveedor = pro.id_proveedor " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (el.ruc_empresa ='".  $ruc_empresa ." ' AND el.id_proveedor = pro.id_proveedor AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND el.ruc_empresa ='".  $ruc_empresa ." '  AND el.id_proveedor = pro.id_proveedor OR ";
			}
			
			$sWhere = substr_replace( $sWhere, " AND el.ruc_empresa ='".  $ruc_empresa ." '  AND el.id_proveedor = pro.id_proveedor ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by $ordenado $por";
		include ("../ajax/pagination.php"); //include pagination file
		//pagination variables
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 20; //how much records you want to show
		$adjacents  = 10; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		//Count the total number of row in your table*/
		$count_query = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere ");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = '../liquidacion_compra_servicio.php';
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
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_liquidacion");'>Fecha</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("razon_social");'>Proveedor</button></th>
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_liquidacion");'>Número</button></th>								
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Total</button></th>								
				<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado_sri");'>Estado SRI</button></th>													
				<th class='text-right'>Opciones</th>								
				<input type="hidden" value="<?php echo $page;?>" id="pagina">
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$id_encabezado_liquidacion=$row['id_encabezado_liq'];
						$fecha_liquidacion=$row['fecha_liquidacion'];
						$serie_liquidacion=$row['serie_liquidacion'];
						$secuencial_liquidacion=$row['secuencial_liquidacion'];
						$nombre_proveedor_liquidacion=$row['razon_social'];
						$ruc_proveedor=$row['ruc_proveedor'];
						$estado_sri=$row['estado_sri'];
						$total_liquidacion=$row['total_liquidacion'];
						$id_proveedor=$row['id_proveedor'];
						$mail = $row['mail_proveedor'];
						$estado_mail = $row['estado_mail'];
						$aut_sri = $row['aut_sri'];
						
						$buscar_aut = mysqli_query($con, "SELECT count(*) as num_aut FROM encabezado_compra  WHERE aut_sri='".$aut_sri."' ");
						$row_aut= mysqli_fetch_array($buscar_aut);
						$num_aut = $row_aut['num_aut'];	
						
						$numero_liq = $serie_liquidacion . "-" . str_pad($secuencial_liquidacion, 9, "000000000", STR_PAD_LEFT);
					//estado sri
					switch ($estado_sri) {
					case "PENDIENTE":
						$label_class_sri='label-warning';
						break;
					case "ANULADA":
						$label_class_sri='label-danger';
						break;
					case "NO APLICA":
						$label_class_sri='label-info';
						break;
					case "AUTORIZADO":
						$label_class_sri='label-success';
						break;
						}
									
						
					//estado mail
					switch ($estado_mail) {
					case "PENDIENTE":
						$estado_mail_final='btn btn-default btn-xs';
						break;
					case "ENVIADO":
						$estado_mail_final='btn btn-info btn-xs';
						break;
						}
					
					?>
					<input type="hidden" value="<?php echo $ruc_proveedor;?>" id="ruc_cliente<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo $aut_sri;?>" id="aut_sri<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo $mail;?>" id="mail_cliente<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo $id_encabezado_liquidacion;?>" id="id_encabezado_liquidacion<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo $serie_liquidacion;?>" id="serie_liquidacion<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo $secuencial_liquidacion;?>" id="secuencial_liquidacion<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_liquidacion));?>" id="fecha_liquidacion<?php echo $id_encabezado_liquidacion;?>">
					<input type="hidden" value="<?php echo $total_liquidacion ?>" id="total_liquidacion<?php echo $id_encabezado_liquidacion;?>">
					<tr>
						<td><?php echo date("d/m/Y", strtotime($fecha_liquidacion)); ?></td>
						<td class='col-md-3'><?php echo strtoupper ($nombre_proveedor_liquidacion); ?></td>
						<td><?php echo $serie_liquidacion; ?>-<?php echo str_pad($secuencial_liquidacion,9,"000000000",STR_PAD_LEFT); ?></td>
						<td><?php echo number_format($total_liquidacion,2,'.',''); ?></td>
						<td><span class="label <?php echo $label_class_sri;?>"><?php echo $estado_sri; ?></span></td>
									
					<td class='col-md-3'><span class="pull-right">
					
					<?php
					//PARA ENVIAR AL SRI
						switch ($estado_sri) {
							case "PENDIENTE";
							case "DEVUELTA";
							?>						
							<a href="#" class='btn btn-success btn-xs' onclick="enviar_liquidacion_sri('<?php echo $id_encabezado_liquidacion;?>');" title='Enviar al SRI' data-toggle="modal" data-target="#EnviarDocumentosSri"><i class="glyphicon glyphicon-send"></i></a>
							<?php
							break;
							}

					switch ($estado_sri) {
								case "AUTORIZADO";
								case "ANULADA";
					if ($num_aut==0){
					?>
					<a href="#" onclick="enviar_liquidacion_compras('<?php echo $aut_sri;?>');" title='Registrar en compras'  class='btn btn-info btn-xs' ><i class="glyphicon glyphicon-share-alt"></i> </a>
					<?php
					}
					$status_servidor=status_servidor(); //esto le puse que venga de helpers cuando el servidor no quiere descargar las facturas
					if($status_servidor){
					?>
					<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_liquidacion) ?>&tipo_documento=liquidacion&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Ver' download target="_blank">Pdf</i> </a>
					<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_liquidacion) ?>&tipo_documento=liquidacion&tipo_archivo=xml" class='btn btn-default btn-xs' title='Ver' download target="_blank">Xml</i> </a>
					<?php
					}else{
						?>
					<a href="http://64.225.69.65:8000/liquidaciones_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_proveedor ?>/LIQ<?php echo $numero_liq ?>.pdf" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Pdf</i> </a>
					<a href="http://64.225.69.65:8000/liquidaciones_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_proveedor ?>/LIQ<?php echo $numero_liq ?>.xml" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Xml</i> </a>
					<?php
					}
					break;
				}
					
					//para anular una liquidación autorizada por el sri
					switch ($estado_sri) {
							case "AUTORIZADO";
					?>
					<a href="#" class='btn btn-warning btn-xs' title='Anular liquidación' data-toggle="modal" data-target="#AnularDocumentosSri" onclick="pasa_codigo_anular_lc('<?php echo $id_encabezado_liquidacion; ?>')"><i class="glyphicon glyphicon-remove"></i> </a>
					<?php
					}
					//PARA mostrar detalle de la liquidación
						switch ($estado_sri) {
							case "PENDIENTE";
							case "DEVUELTA";
							case "AUTORIZADO";
							?>	
							<a href="#" class='btn btn-info btn-xs' title='Detalle liquidación' onclick="detalle_liquidacion('<?php echo $id_encabezado_liquidacion; ?>')" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list"></i> </a>
							<?php
							break;
							}
					?>
					<?php
					if ($estado_sri == "PENDIENTE"){
					?>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar liquidación' onclick="eliminar_liquidacion('<?php echo $id_encabezado_liquidacion; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					<?php
					}
					if ($estado_sri =="AUTORIZADO"){
					?>
					<a href="#" class="<?php echo $estado_mail_final;?>" onclick="enviar_liquidacion_mail('<?php echo $id_encabezado_liquidacion;?>');" title='Enviar por mail al proveedor' data-toggle="modal" data-target="#EnviarDocumentosMail"><i class="glyphicon glyphicon-envelope"></i> </a>
					<?php
					}
					?>					
					</span></td>
					
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
?>