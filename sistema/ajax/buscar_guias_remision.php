<?php
header('Content-Type: text/html; charset=UTF-8');
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	require_once("../helpers/helpers.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$fecha_registro=date("Y-m-d H:i:s");
//PARA ELIMINAR GUIAS DE REMISION HECHAS
if (isset($_POST['id_guia'])){
		$id_guia=intval($_POST['id_guia']);
		$busca_datos_guia = "SELECT eg.serie_gr as serie_guia, eg.secuencial_gr as secuencial_guia, eg.fecha_gr as fecha_guia, cl.ruc as ruc_cliente, eg.ruc_empresa as ruc_empresa FROM encabezado_gr eg, clientes cl WHERE eg.id_encabezado_gr = $id_guia and eg.id_cliente=cl.id ";
		$result = $con->query($busca_datos_guia);
		$datos_guia = mysqli_fetch_array($result);
		$serie_guia =$datos_guia['serie_guia'];
		$secuencial =$datos_guia['secuencial_guia'];
		$ruc_empresa_periodo = $datos_guia['ruc_empresa'];
		$ruc_del_cliente = $datos_guia['ruc_cliente'];;
			
		//eliminar la guia y los datos de la guia
		if ($delete=mysqli_query($con,"DELETE FROM encabezado_gr WHERE id_encabezado_gr = '".$id_guia."'") && $delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_gr WHERE ruc_empresa = '".$ruc_empresa."' and serie_gr = '".$serie_guia."' and secuencial_gr = '".$secuencial."'") && $delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_gr WHERE ruc_empresa = '".$ruc_empresa."' and serie_gr = '".$serie_guia."' and secuencial_gr = '".$secuencial."'")){
				echo "<script>
				$.notify('Guía de remisión eliminada exitosamente','success')
				</script>";
		}else {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Error!</strong> Lo siento algo ha salido mal intenta nuevamente.
			</div>
			<?php
			
		}
	}

//PARA ANULAR GUIAS DE REMISION HECHAS
if (isset($_POST['id_guia'])){
		$id_usuario = $_SESSION['id_usuario'];
		$id_guia=intval($_POST['id_guia']);
		
		$busca_datos_guia = "SELECT * FROM encabezado_gr WHERE id_encabezado_gr = $id_guia ";
		$result = $con->query($busca_datos_guia);
		$datos_guia = mysqli_fetch_array($result);
		$serie_guia =$datos_guia['serie_gr'];
		$secuencial =$datos_guia['secuencial_gr'];
		$ruc_empresa_periodo = $datos_guia['ruc_empresa'];
		
		//anular la guia y los datos de la guia
		if ($delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_gr WHERE ruc_empresa = '$ruc_empresa' and serie_gr = '$serie_guia' and secuencial_gr = '$secuencial'")
			&& $anular=mysqli_query($con,"UPDATE encabezado_gr SET estado_sri='ANULADA', id_usuario= '$id_usuario' WHERE id_encabezado_gr = $id_guia ")		
			){
				echo "<script>
				$.notify('Guía de remisión anulada exitosamente','success')
				</script>";
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
	
	
//PARA BUSCAR LAS GUIAS DE REMISION
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'ajax'){
		
		// escaping, additionally removing everything that could be (html/javascript-) code
		$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $aColumns = array('fecha_gr','secuencial_gr', 'serie_gr','nombre');//Columnas de busqueda
		 $sTable = "encabezado_gr as eg, clientes as cl";
		 $sWhere = "WHERE eg.ruc_empresa ='".  $ruc_empresa ." '  AND eg.id_cliente = cl.id " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (eg.ruc_empresa ='".  $ruc_empresa ." '  AND eg.id_cliente = cl.id AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND eg.ruc_empresa = '".  $ruc_empresa ."' AND eg.id_cliente = cl.id OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND eg.ruc_empresa = '".  $ruc_empresa ."' AND eg.id_cliente = cl.id ", -3 );
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
		$reload = '../guias_remision.php';
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
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_gr");'>Fecha</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("id_cliente");'>Cliente</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("id_transportista");'>Transportista</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("secuencial_gr");'>Número guía</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("factura_aplica");'>Número factura</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("estado_sri");'>Estado SRI</button></th>
					<th class='text-right'>Opciones</th>					
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$id_encabezado_gr=$row['id_encabezado_gr'];
						$id_transportista=$row['id_transportista'];
						$fecha_gr=$row['fecha_gr'];
						$serie_gr=$row['serie_gr'];
						$secuencial_gr=$row['secuencial_gr'];
						$nombre_cliente=$row['nombre'];
						$ruc_cliente=$row['ruc'];
						$tipo_gr=$row['tipo_gr'];
						$estado_sri=$row['estado_sri'];
						$factura_gr=$row['factura_aplica'];
						
						$busca_datos_transportista = mysqli_query($con,"SELECT nombre as nombre_transportista, ruc as ruc_transportista FROM clientes WHERE id = '".$id_transportista."' ");
						$resultado_transportista=mysqli_fetch_array($busca_datos_transportista);
						$nombre_transportista_gr=$resultado_transportista['nombre_transportista'];
						$ruc_transportista=$resultado_transportista['ruc_transportista'];
						
						$ambiente=$row['ambiente'];
						$mail = $row['email'];
						$estado_mail = $row['estado_mail'];
						$aut_sri = $row['aut_sri'];

						$numero_gr = $serie_gr . "-" . str_pad($secuencial_gr, 9, "000000000", STR_PAD_LEFT);
					
					
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
					switch ($tipo_gr) {
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
					<input type="hidden" value="<?php echo $ruc_cliente;?>" id="ruc_cliente<?php echo $id_encabezado_gr;?>">
					<input type="hidden" value="<?php echo $aut_sri;?>" id="aut_sri<?php echo $id_encabezado_gr;?>">
					<input type="hidden" value="<?php echo $mail;?>" id="mail_cliente<?php echo $id_encabezado_gr;?>">
					<input type="hidden" value="<?php echo $id_encabezado_gr;?>" id="id_encabezado_guia<?php echo $id_encabezado_gr;?>">
					<input type="hidden" value="<?php echo $serie_gr;?>" id="serie_guia<?php echo $id_encabezado_gr;?>">
					<input type="hidden" value="<?php echo $secuencial_gr;?>" id="secuencial_guia<?php echo $id_encabezado_gr;?>">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_gr));?>" id="fecha_guia<?php echo $id_encabezado_gr;?>">
					<tr>
						<td><?php echo date("d/m/Y", strtotime($fecha_gr)); ?></td>
						<td class='col-md-3'><?php echo strtoupper ($nombre_cliente); ?></td>
						<td class='col-md-3'><?php echo strtoupper ($nombre_transportista_gr); ?></td>
						<td class='col-md-2'><?php echo $serie_gr; ?>-<?php echo str_pad($secuencial_gr,9,"000000000",STR_PAD_LEFT); ?></td>
						<td class='col-md-1'><?php echo $factura_gr;?></td>
						<td><span class="label <?php echo $label_class_sri;?>"><?php echo $estado_sri; ?></span></td>
					
					<td class='col-sm-4'><span class="pull-right">
					
					<?php
					//PARA ENVIAR AL SRI
					if ($tipo_gr=="ELECTRÓNICA"){
						switch ($estado_sri) {
							case "PENDIENTE";
							case "DEVUELTA";
							?>	
							<a href="#" class='btn btn-success btn-xs' onclick="enviar_guia_sri('<?php echo $id_encabezado_gr;?>');" title='Enviar al SRI' data-toggle="modal" data-target="#EnviarDocumentosSri"><i class="glyphicon glyphicon-send"></i></a>
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
				    <a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_gr) ?>&tipo_documento=gr&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Ver' target="_blank" download>Pdf</i> </a>
					<a href="../ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado_gr) ?>&tipo_documento=gr&tipo_archivo=xml" class='btn btn-default btn-xs' title='Ver' target="_blank" download>Xml</i> </a>
					<?php
					}else{
						?>
					<a href="http://64.225.69.65:8000/guias_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_transportista ?>/GR<?php echo $numero_gr ?>.pdf" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Pdf</i> </a>
					<a href="http://64.225.69.65:8000/guias_autorizadas/<?php echo $ruc_empresa ?>/<?php echo $ruc_transportista ?>/GR<?php echo $numero_gr ?>.xml" class='btn btn-default btn-xs' title='Descargar' target="_blank" download>Xml</i> </a>
					<?php
					}
					break;
					}
					
					//para anular una guia autorizada por el sri
					if ($tipo_gr=="ELECTRÓNICA" && $estado_sri == "AUTORIZADO" && $id_usuario >0){
					?>
					<a href="#" class='btn btn-warning btn-xs' title='Anular guía' data-toggle="modal" data-target="#AnularDocumentosSri" onclick="pasa_codigo_anular_gr_e('<?php echo $id_encabezado_gr; ?>')"><i class="glyphicon glyphicon-remove"></i> </a>
					<?php
					}
					//para cuando esta anulada y ambiente de produccion
					if ($tipo_gr=="ELECTRÓNICA" && $estado_sri == "PENDIENTE"){
					?>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar guía' onclick="eliminar_guía('<?php echo $id_encabezado_gr; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					<?php
					}
					if ($tipo_gr=="ELECTRÓNICA" && $estado_sri =="AUTORIZADO"){
					?>
					<a href="#" class="<?php echo $estado_mail_final;?>" onclick="enviar_gr_mail('<?php echo $id_encabezado_gr;?>');" title='Enviar por mail al cliente' data-toggle="modal" data-target="#EnviarDocumentosMail"><i class="glyphicon glyphicon-envelope"></i> </a>
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