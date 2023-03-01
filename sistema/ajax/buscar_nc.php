<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	include("../validadores/periodo_contable.php");
	require_once("../helpers/helpers.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$fecha_registro=date("Y-m-d H:i:s");
//PARA ELIMINAR NC ECHAS
if (isset($_POST['id_nc'])){
		$id_nc=intval($_POST['id_nc']);
		$busca_datos_nc = "SELECT enc.id_registro_contable as id_registro_contable, enc.serie_nc as serie_nc, enc.secuencial_nc as secuencial_nc, enc.fecha_nc as fecha_nc, cl.ruc as ruc_cliente, enc.ruc_empresa as ruc_empresa FROM encabezado_nc enc, clientes cl WHERE enc.id_encabezado_nc = $id_nc and enc.id_cliente=cl.id ";
		$result = $con->query($busca_datos_nc);
		$datos_nc = mysqli_fetch_array($result);
		$serie_nc =$datos_nc['serie_nc'];
		$secuencial =$datos_nc['secuencial_nc'];

		include("../clases/anular_registros.php");
		$anular_asiento_contable = new anular_registros(); 

		$id_registro_contable=$datos_nc['id_registro_contable'];
		$anio_documento=date("Y", strtotime($datos_nc['fecha_nc']));
		$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);

		
		//eliminar la nc y los datos de la nc 
		if ($delete=mysqli_query($con,"DELETE FROM encabezado_nc WHERE id_encabezado_nc = $id_nc") 
			&& $delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_nc WHERE ruc_empresa = '$ruc_empresa' and serie_nc = '$serie_nc' and secuencial_nc = '$secuencial'")
			&& $delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_nc WHERE ruc_empresa = '$ruc_empresa' and serie_nc = '$serie_nc' and secuencial_nc = '$secuencial'")){
			echo "<script>
				$.notify('Nota de crédito eliminada.','success')
				</script>";
		}else {
			echo "<script>
				$.notify('Lo siento algo ha salido mal intenta nuevamente','error')
				</script>";
			
		}
	}

//PARA ANULAR nc ECHAS
if (isset($_POST['id_nc_anular'])){
		$id_usuario = $_SESSION['id_usuario'];
		$id_nc=intval($_POST['id_nc_anular']);
		
		$busca_datos_nc = "SELECT * FROM encabezado_nc WHERE id_encabezado_nc = $id_nc ";
		$result = $con->query($busca_datos_nc);
		$datos_nc = mysqli_fetch_array($result);
		$serie_nc =$datos_nc['serie_nc'];
		$secuencial =$datos_nc['secuencial_nc'];
		$mes_periodo = date("m", strtotime($datos_nc['fecha_nc']));
		$anio_periodo = date("Y", strtotime($datos_nc['fecha_nc']));
		$ruc_empresa_periodo = $datos_nc['ruc_empresa'];
		
		//anular la nc y los datos de la nc
		if ($delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_nc WHERE ruc_empresa = '$ruc_empresa' and serie_nc = '$serie_nc' and secuencial_nc = '$secuencial'")
			&& $delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_nc WHERE ruc_empresa = '$ruc_empresa' and serie_nc = '$serie_nc' and secuencial_nc = '$secuencial'")
			&& $anular=mysqli_query($con,"UPDATE encabezado_nc SET fecha_registro='$fecha_registro', estado_sri='ANULADA', total_nc ='0.00', id_usuario= '$id_usuario' WHERE id_encabezado_nc = $id_nc ")		
			){
			echo "<script>
				$.notify('Nota de crédito anulada.','success')
				</script>";
		}else {
			echo "<script>
				$.notify('Lo siento algo ha salido mal intenta nuevamente','error')
				</script>";
		}
	}
	
	
//PARA BUSCAR LAS NC
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'ajax'){
		// escaping, additionally removing everything that could be (html/javascript-) code
		$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $aColumns = array('enc.fecha_nc','enc.secuencial_nc', 'enc.serie_nc','enc.factura_modificada','cl.nombre');//Columnas de busqueda
		 $sTable = "encabezado_nc as enc INNER JOIN clientes as cl ON cl.id=enc.id_cliente";
		 $sWhere = "WHERE enc.ruc_empresa ='".  $ruc_empresa ." '  " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (enc.ruc_empresa ='".  $ruc_empresa ." ' AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND enc.ruc_empresa = '".  $ruc_empresa ."' OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND enc.ruc_empresa = '".  $ruc_empresa ."' ", -3 );
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
		$reload = '../facturas.php';
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
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_nc");'>Fecha</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("id_cliente");'>Cliente</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_nc");'>Número</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("factura_modificada");'>Documento modificado</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("total_nc");'>Total</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado_sri");'>Estado SRI</button></th>
					<th class='text-right'>Opciones</th>
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$id_encabezado_nc=$row['id_encabezado_nc'];
						$fecha_nc=$row['fecha_nc'];
						$serie_nc=$row['serie_nc'];
						$secuencial_nc=$row['secuencial_nc'];
						$factura_modificada=$row['factura_modificada'];
						$nombre_cliente_nc=$row['nombre'];
						$ruc_cliente=$row['ruc'];
						$tipo_nc=$row['tipo_nc'];
						$estado_sri=$row['estado_sri'];
						$total_nc=$row['total_nc'];
						$id_cliente=$row['id'];
						$ambiente=$row['ambiente'];
						$mail = $row['email'];
						$estado_mail = $row['estado_mail'];
						$aut_sri = $row['aut_sri'];	
						$numero_nc = $serie_nc . "-" . str_pad($secuencial_nc, 9, "000000000", STR_PAD_LEFT);					
					
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
					
					//tipo factura
					switch ($tipo_nc) {
					case "ELECTRÓNICA":
						$label_class_tipo='label-primary';
						break;
					case "FÍSICA":
						$label_class_tipo='label-info';;
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
					<input type="hidden" value="<?php echo $ruc_cliente;?>" id="ruc_cliente<?php echo $id_encabezado_nc;?>">
					<input type="hidden" value="<?php echo $aut_sri;?>" id="aut_sri<?php echo $id_encabezado_nc;?>">
					<input type="hidden" value="<?php echo $mail;?>" id="mail_cliente<?php echo $id_encabezado_nc;?>">
					<input type="hidden" value="<?php echo $id_encabezado_nc;?>" id="id_encabezado_nc<?php echo $id_encabezado_nc;?>">
					<input type="hidden" value="<?php echo $serie_nc;?>" id="serie_nc<?php echo $id_encabezado_nc;?>">
					<input type="hidden" value="<?php echo $secuencial_nc;?>" id="secuencial_nc<?php echo $id_encabezado_nc;?>">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_nc));?>" id="fecha_nc<?php echo $id_encabezado_nc;?>">
					<tr>
						<td><?php echo date("d/m/Y", strtotime($fecha_nc)); ?></td>
						<td><?php echo $nombre_cliente_nc; ?></td>
						<td><?php echo $serie_nc; ?>-<?php echo str_pad($secuencial_nc,9,"000000000",STR_PAD_LEFT); ?></td>
						<td><?php echo $factura_modificada; ?></td>
						<td><?php echo $total_nc; ?></td>
						<td><span class="label <?php echo $label_class_sri;?>"><?php echo $estado_sri; ?></span></td>
					<td class='col-md-2'><span class="pull-right">
					
					<?php
					//PARA ENVIAR AL SRI
					if ($tipo_nc=="ELECTRÓNICA"){
						switch ($estado_sri) {
							case "PENDIENTE";
							case "DEVUELTA";
							?>	
							<a href="#" class='btn btn-success btn-xs' onclick="enviar_nc_sri('<?php echo $id_encabezado_nc;?>');" title='Enviar al SRI' data-toggle="modal" data-target="#EnviarDocumentosSri"><i class="glyphicon glyphicon-send"></i></a>
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
						<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_nc) ?>&tipo_documento=nc&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Ver' download target="_blank">Pdf</i> </a>
						<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_nc) ?>&tipo_documento=nc&tipo_archivo=xml" class='btn btn-default btn-xs' title='Ver' download target="_blank">Xml</i> </a>
						<?php
						}else{
						?>
						<a href="http://64.225.69.65:8000/nc_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_cliente ?>/NC<?php echo $numero_nc ?>.pdf" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Pdf</i> </a>
						<a href="http://64.225.69.65:8000/nc_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_cliente ?>/NC<?php echo $numero_nc ?>.xml" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Xml</i> </a>
						<?php
						}
						break;
					}

					//para anular una nc autorizada por el sri
					if ($tipo_nc=="ELECTRÓNICA" && $estado_sri == "AUTORIZADO" && $id_usuario >0){
					?>
					<a href="#" class='btn btn-warning btn-xs' title='Anular nota de crédito' data-toggle="modal" data-target="#AnularDocumentosSri" onclick="pasa_codigo_anular_nc_e('<?php echo $id_encabezado_nc; ?>')"><i class="glyphicon glyphicon-remove"></i> </a>
					<?php
					}
						
					//para cuando esta anulada y ambiente de produccion
					if ($tipo_nc=="ELECTRÓNICA" && $estado_sri == "PENDIENTE"){
					?>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar nota de crédito' onclick="eliminar_nc('<?php echo $id_encabezado_nc; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					<?php
					}
					//para cuando son facturas fisicas
					if ($tipo_nc=="FÍSICA"){
					?>
					<a href="#" class='btn btn-info btn-xs' onclick="enviar_nc_mail('<?php echo $id_encabezado_nc;?>');" title='Enviar por mail al cliente' data-toggle="modal" data-target="#EnviarDocumentosMail"><i class="glyphicon glyphicon-envelope"></i> </a>
					<a href="#" class='btn btn-warning btn-xs' title='Anular nota de crédito' onclick="anular_nc('<?php echo $id_encabezado_nc; ?>')"><i class="glyphicon glyphicon-remove"></i> </a>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar nota de crédito' onclick="eliminar_nc('<?php echo $id_encabezado_nc; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					<?php
					}
					
					if ($tipo_nc=="ELECTRÓNICA" && $estado_sri =="AUTORIZADO"){
					?>
					<a href="#" class="<?php echo $estado_mail_final;?>" onclick="enviar_nc_mail('<?php echo $id_encabezado_nc;?>');" title='Enviar por mail al cliente' data-toggle="modal" data-target="#EnviarDocumentosMail"><i class="glyphicon glyphicon-envelope"></i> </a>
					<?php
					}
					?>
					<a href="#" class='btn btn-info btn-xs' title='Detalle factura' onclick="detalle_nc('<?php echo $id_encabezado_nc; ?>')" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list"></i></a>					
					</span></td>
					
					</tr>
				<?php
				}
				?>
				<tr>
					<td colspan=9 ><span class="pull-right">
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