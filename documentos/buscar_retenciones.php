<?php
	/* Connect To Database*/
	include("../sistema/conexiones/conectalogin.php");
	include ("../sistema/ajax/pagination.php"); //include pagination file
	$con = conenta_login();

//PARA BUSCAR LAS RETENCIONES
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'ajax'){
		// escaping, additionally removing everything that could be (html/javascript-) code
		$r = mysqli_real_escape_string($con,(strip_tags($_REQUEST['r'], ENT_QUOTES)));
		$ordenado = "fecha_emision";//mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		$por = "desc";//mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		$ruc_cliente_proveedor = mysqli_real_escape_string($con,(strip_tags($_GET['ruc_cliente_proveedor'], ENT_QUOTES)));
			
		$id_encabezado_retencion=array();
		
		$id_encabezado_retencion_contar=array();
		$sql_proveedores = "SELECT * FROM proveedores WHERE substr(ruc_proveedor,1,10) ='". substr($ruc_cliente_proveedor,0,10) ."' ";
		$datos_proveedores = $con->query($sql_proveedores);
		$datos_proveedores_contar = $con->query($sql_proveedores);
		
			while ($row_proveedores_contar=mysqli_fetch_array($datos_proveedores_contar)){
			$id_proveedor_contar=intval($row_proveedores_contar['id_proveedor']);

			$sql_retenciones_contar = "SELECT * FROM encabezado_retencion WHERE id_proveedor ='". $id_proveedor_contar ."' ";
			$datos_retenciones_contar = $con->query($sql_retenciones_contar);
			while ($row_retenciones_contar=mysqli_fetch_array($datos_retenciones_contar)){
			$id_encabezado_retencion_contar[]=$row_retenciones_contar['id_encabezado_retencion'];
			}
			}
			
			$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
			$per_page =10; //how much records you want to show
			$adjacents  = 10; //gap between pages after number of adjacents
			$offset = ($page - 1) * $per_page;
			$numrows = count($id_encabezado_retencion_contar);
			$total_pages = ceil($numrows/$per_page);
			$reload = 'entrada.php';
		
		while ($row_proveedores=mysqli_fetch_array($datos_proveedores)){
		$id_proveedor=intval($row_proveedores['id_proveedor']);

		 $aColumns = array('secuencial_retencion','numero_comprobante','serie_retencion','nombre','nombre_comercial');//Columnas de busqueda
		 $sTable = "encabezado_retencion er, empresas em";
		 $sWhere = "WHERE er.id_proveedor ='". $id_proveedor ."' and er.ruc_empresa=em.ruc";
		if ( $_GET['r'] != "" )
		{
			$sWhere = "WHERE (er.id_proveedor ='". $id_proveedor ."' and er.ruc_empresa=em.ruc AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$r."%' AND er.id_proveedor ='". $id_proveedor ."' and er.ruc_empresa=em.ruc OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND er.id_proveedor ='". $id_proveedor ."' and er.ruc_empresa=em.ruc ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by $ordenado $por";
		
		$sql_retencion = "SELECT * FROM $sTable $sWhere LIMIT $offset, $per_page ";
		$datos_retencion = $con->query($sql_retencion);
		while ($row_retenciones=mysqli_fetch_array($datos_retencion)){
		$id_encabezado_retencion[]=$row_retenciones['id_encabezado_retencion'];
		}
		}

		?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
				<th >Fecha</th>
				<th >Emisor</th>
				<th >Número retención</th>
				<th >Número factura</th>				
				<th >Estado</th>								
				<th class='text-right'>Descargar</th>								
				<input type="hidden" value="<?php echo $page;?>" id="pagina">
				</tr>
				<?php
		
		for ($i=0; $i < count($id_encabezado_retencion); $i++ ){
		//main query to fetch the data
		$sql_retenciones_encontradas = "SELECT * FROM encabezado_retencion er, empresas em WHERE er.id_encabezado_retencion='". $id_encabezado_retencion[$i] ."' and er.ruc_empresa=em.ruc ";
		$query = mysqli_query($con, $sql_retenciones_encontradas);
				
		while ($row_retenciones_encontradas=mysqli_fetch_array($query)){
		$id_encabezado=$row_retenciones_encontradas['id_encabezado_retencion'];
		$fecha_retencion=$row_retenciones_encontradas['fecha_emision'];
		$serie_retencion=$row_retenciones_encontradas['serie_retencion'];
		$secuencial_retencion=$row_retenciones_encontradas['secuencial_retencion'];
		$nombre_proveedor_retencion=$row_retenciones_encontradas['nombre'];
		$estado_sri=$row_retenciones_encontradas['estado_sri'];
		$numero_comprobante=$row_retenciones_encontradas['numero_comprobante'];
										
					//estado sri
					switch ($estado_sri) {
					case "PENDIENTE":
						$label_class_sri='label-warning';
						$estado='PENDIENTE DE AUTORIZAR EN EL SRI';
						break;
					case "ANULADA":
						$label_class_sri='label-danger';
						$estado='RETENCIÓN ANULADA';
						break;
					case "NO APLICA":
						$label_class_sri='label-info';
						$estado='FACTURA FÍSICA';
						break;
					case "AUTORIZADO":
						$label_class_sri='label-success';
						$estado='AUTORIZADA POR EL SRI';
						break;
						}
		?>
		<tr>
		<td><?php echo date("d/m/Y", strtotime($fecha_retencion)); ?></td>
		<td class='col-md-4'><?php echo strtoupper ($nombre_proveedor_retencion); ?></td>
		<td><?php echo $serie_retencion; ?>-<?php echo str_pad($secuencial_retencion,9,"000000000",STR_PAD_LEFT); ?></td>
		<td><?php echo $numero_comprobante; ?></td>
		<td><span class="label <?php echo $label_class_sri;?>"><?php echo $estado; ?></span></td>
		<td class='col-md-2'><span class="pull-right">
			<a href="../sistema/ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado) ?>&tipo_documento=retencion&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Ver'>Pdf</i> </a>
			<a href="../sistema/ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado) ?>&tipo_documento=retencion&tipo_archivo=xml" class='btn btn-default btn-xs' title='Ver'>Xml</i> </a>
		</td>
		</tr>
		
		<?php
		}
		}
			?>
			</table>
			</div>
			</div>
				<tr>
					<td colspan=5 ><span class="pull-right">
					<?php
					 echo paginate($reload, $page, $total_pages, $adjacents);
					?></span></td>
				</tr>
			  
			<?php
		
	}

?>