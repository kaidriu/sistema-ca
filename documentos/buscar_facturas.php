<?php
	/* Connect To Database*/
	include("../sistema/conexiones/conectalogin.php");
	include ("../sistema/ajax/pagination.php"); //include pagination file
	$con = conenta_login();

//PARA BUSCAR LAS FACTURAS
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'ajax'){
		// escaping, additionally removing everything that could be (html/javascript-) code
		$q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		$ordenado = "fecha_factura";//mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		$por = "desc";//mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		$ruc_cliente_proveedor = mysqli_real_escape_string($con,(strip_tags($_GET['ruc_cliente_proveedor'], ENT_QUOTES)));
			
		$id_encabezado_factura=array();
		
		$id_encabezado_factura_contar=array();
		$sql_clientes = "SELECT * FROM clientes WHERE substr(ruc,1,10) ='". substr($ruc_cliente_proveedor,0,10) ."' ";
		$datos_clientes = $con->query($sql_clientes);
			

			$datos_clientes_contar = $con->query($sql_clientes);
		
			while ($row_clientes_contar=mysqli_fetch_array($datos_clientes_contar)){
			$id_cliente_contar=intval($row_clientes_contar['id']);

			$sql_facturas_contar = "SELECT * FROM encabezado_factura WHERE id_cliente ='". $id_cliente_contar ."' ";
			$datos_facturas_contar = $con->query($sql_facturas_contar);
			while ($row_facturas_contar=mysqli_fetch_array($datos_facturas_contar)){
			$id_encabezado_factura_contar[]=$row_facturas_contar['id_encabezado_factura'];
			}
			}
			
		
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
			$per_page =10; //how much records you want to show
			$adjacents  = 10; //gap between pages after number of adjacents
			$offset = ($page - 1) * $per_page;
			$numrows = count($id_encabezado_factura_contar);
			$total_pages = ceil($numrows/$per_page);
			$reload = 'entrada.php';
		
		while ($row_clientes=mysqli_fetch_array($datos_clientes)){
		$id_cliente=intval($row_clientes['id']);

		 $aColumns = array('secuencial_factura','serie_factura','nombre','nombre_comercial');//Columnas de busqueda
		 $sTable = "encabezado_factura, empresas";
		 $sWhere = "WHERE id_cliente ='". $id_cliente ."' and encabezado_factura.ruc_empresa=empresas.ruc";
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (id_cliente ='". $id_cliente ."' and encabezado_factura.ruc_empresa=empresas.ruc AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND id_cliente ='". $id_cliente ."' and encabezado_factura.ruc_empresa=empresas.ruc OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND id_cliente ='". $id_cliente ."' and encabezado_factura.ruc_empresa=empresas.ruc ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by $ordenado $por";
		
		$sql_facturas = "SELECT * FROM $sTable $sWhere LIMIT $offset, $per_page ";
		$datos_facturas = $con->query($sql_facturas);
		while ($row_facturas=mysqli_fetch_array($datos_facturas)){
		$id_encabezado_factura[]=$row_facturas['id_encabezado_factura'];
		}
		}

		?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
				<th >Fecha</th>
				<th >Emisor</th>
				<th >Número factura</th>														
				<th >Estado</th>								
				<th class='text-right'>Descargar</th>								
				<input type="hidden" value="<?php echo $page;?>" id="pagina">
				</tr>
				<?php
		
		for ($i=0; $i < count($id_encabezado_factura); $i++ ){
		//main query to fetch the data
		$sql_facturas_encontradas = "SELECT * FROM encabezado_factura ef, empresas em WHERE ef.id_encabezado_factura='". $id_encabezado_factura[$i] ."' and ef.ruc_empresa=em.ruc ";
		$query = mysqli_query($con, $sql_facturas_encontradas);
				
		while ($row_facturas_encontradas=mysqli_fetch_array($query)){
		$id_encabezado=$row_facturas_encontradas['id_encabezado_factura'];
		$fecha_factura=$row_facturas_encontradas['fecha_factura'];
		$serie_factura=$row_facturas_encontradas['serie_factura'];
		$secuencial_factura=$row_facturas_encontradas['secuencial_factura'];
		$nombre_cliente_factura=$row_facturas_encontradas['nombre'];
		$estado_sri=$row_facturas_encontradas['estado_sri'];
										
					//estado sri
					switch ($estado_sri) {
					case "PENDIENTE":
						$label_class_sri='label-warning';
						$estado='PENDIENTE DE AUTORIZAR EN EL SRI';
						break;
					case "ANULADA":
						$label_class_sri='label-danger';
						$estado='FACTURA ANULADA';
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
		<td><?php echo date("d/m/Y", strtotime($fecha_factura)); ?></td>
		<td class='col-md-4'><?php echo strtoupper ($nombre_cliente_factura); ?></td>
		<td><?php echo $serie_factura; ?>-<?php echo str_pad($secuencial_factura,9,"000000000",STR_PAD_LEFT); ?></td>
		<td><span class="label <?php echo $label_class_sri;?>"><?php echo $estado; ?></span></td>
		<td class='col-md-2'><span class="pull-right">
			<a href="../sistema/ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado) ?>&tipo_documento=factura&tipo_archivo=pdf" class='btn btn-default btn-xs' title='Ver'>Pdf</i> </a>
			<a href="../sistema/ajax/imprime_documento.php?id_documento=<?php echo base64_encode($id_encabezado) ?>&tipo_documento=factura&tipo_archivo=xml" class='btn btn-default btn-xs' title='Ver'>Xml</i> </a>
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