<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$fecha_registro=date("Y-m-d H:i:s");
//PARA ELIMINAR RETENCIONES de ventas
if (isset($_POST['id_retencion'])){
		$id_retencion=$_POST['id_retencion'];
		$busca_datos_retencion = mysqli_query($con, "SELECT * FROM encabezado_retencion_venta WHERE id_encabezado_retencion = '".$id_retencion."' ");
		$datos_retencion = mysqli_fetch_array($busca_datos_retencion);
		$codigo_unico =$datos_retencion['codigo_unico'];
		
		include("../clases/anular_registros.php");
		$anular_asiento_contable = new anular_registros(); 

		$id_registro_contable=$datos_retencion['id_registro_contable'];
		$anio_documento=date("Y", strtotime($datos_retencion['fecha_emision']));
		$resultado_anular_documento=$anular_asiento_contable->anular_asiento_contable($con, $id_registro_contable, $ruc_empresa, $id_usuario, $anio_documento);

		//eliminar la retencion de venta y todo los detalles
		if ($delete=mysqli_query($con,"DELETE FROM encabezado_retencion_venta WHERE codigo_unico = '".$codigo_unico."'") 
			&& $delete_detalle=mysqli_query($con,"DELETE FROM cuerpo_retencion_venta WHERE codigo_unico = '".$codigo_unico."'")
			&& $delete_adicional=mysqli_query($con,"DELETE FROM detalle_adicional_retencion_venta WHERE codigo_unico = '".$codigo_unico."'")){
			echo "<script>
				$.notify('Retención eliminada exitosamente','success')
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

	
//PARA BUSCAR LAS RETENCIONES de venta
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'buscar'){
		// escaping, additionally removing everything that could be (html/javascript-) code
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $aColumns = array('secuencial_retencion', 'serie_retencion','nombre','ruc','numero_documento','fecha_emision');//Columnas de busqueda
		 $sTable = "encabezado_retencion_venta erv LEFT JOIN clientes cl ON erv.id_cliente = cl.id ";
		 $sWhere = "WHERE erv.ruc_empresa ='".  $ruc_empresa ." ' " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (erv.ruc_empresa ='".  $ruc_empresa ." ' AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND erv.ruc_empresa ='".  $ruc_empresa ." ' OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND erv.ruc_empresa = '".  $ruc_empresa ."' ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by erv.fecha_emision desc";
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
		$reload = '../retenciones_ventas.php';
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
					<th>Fecha retención</th>
					<th>Cliente</th>
					<th>No. Retención</th>
					<th>No. Factura/Liq/ND</th>
					<th>ISD</th>
					<th>Renta</th>
					<th>Iva</th>
					<th>Total</th>
					<th class='text-right'>Opciones</th>
					
				</tr>
				<?php

				while ($row=mysqli_fetch_array($query)){
						$id_encabezado_retencion=$row['id_encabezado_retencion'];
						$fecha_retencion=$row['fecha_emision'];
						$serie_retencion=$row['serie_retencion'];
						$secuencial_retencion=$row['secuencial_retencion'];
						$nombre_cliente=$row['nombre'];
						$codigo_unico=$row['codigo_unico'];
												
						$emision_retenido =substr($row['numero_documento'],0,3);
						$establecimiento_retenido =substr($row['numero_documento'],3,3);
						$secuencial_retenido =substr($row['numero_documento'],6,9);
						$numero_documento=$emision_retenido."-".$establecimiento_retenido."-".$secuencial_retenido;
						
						//para sacar la suma total de la ret
						$total_ret_renta = mysqli_query($con, "SELECT sum(valor_retenido) as total_renta FROM cuerpo_retencion_venta WHERE codigo_unico= '".$codigo_unico."' and impuesto='1' ");
						$row_total_ret_renta = mysqli_fetch_array($total_ret_renta);
						$total_renta=number_format($row_total_ret_renta['total_renta'],2,'.','');
						
						$total_ret_iva = mysqli_query($con, "SELECT sum(valor_retenido) as total_iva FROM cuerpo_retencion_venta WHERE codigo_unico= '".$codigo_unico."' and impuesto='2' ");
						$row_total_ret_iva = mysqli_fetch_array($total_ret_iva);
						$total_iva=number_format($row_total_ret_iva['total_iva'],2,'.','');
						
						$total_ret_isd = mysqli_query($con, "SELECT sum(valor_retenido) as total_isd FROM cuerpo_retencion_venta WHERE codigo_unico= '".$codigo_unico."' and impuesto='6' ");
						$row_total_ret_isd = mysqli_fetch_array($total_ret_isd);
						$total_isd=number_format($row_total_ret_isd['total_isd'],2,'.','');

					?>
					<input type="hidden" value="<?php echo $id_encabezado_retencion;?>" id="id_encabezado_retencion<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $serie_retencion;?>" id="serie_retencion<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo $secuencial_retencion;?>" id="secuencial_retencion<?php echo $id_encabezado_retencion;?>">
					<input type="hidden" value="<?php echo date("d-m-Y", strtotime($fecha_retencion));?>" id="fecha_retencion<?php echo $id_encabezado_retencion;?>">
					<tr>
						<td><?php echo date("d/m/Y", strtotime($fecha_retencion)); ?></td>
    					<td><?php echo strtoupper ($nombre_cliente); ?></td>
						<td><?php echo $serie_retencion; ?>-<?php echo str_pad($secuencial_retencion,9,"000000000",STR_PAD_LEFT); ?></td>
						<td><?php echo $numero_documento; ?></td>
						<td><?php echo $total_isd; ?></td>
						<td><?php echo $total_renta; ?></td>
						<td><?php echo $total_iva; ?></td>
						<td><?php echo number_format($total_renta + $total_iva + $total_isd,2,'.',''); ?></td>
					<td><span class="pull-right">
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar retencion' onclick="eliminar_retencion_ventas('<?php echo $id_encabezado_retencion; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					<a href="#" class='btn btn-info btn-xs' onclick="detalle_retencion_venta('<?php echo $id_encabezado_retencion; ?>')" title="Detalle documento" data-toggle="modal" data-target="#detalleDocumento"><i class="glyphicon glyphicon-list-alt"></i></a>
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