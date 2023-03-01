<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

	if($action == 'ajax'){
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		 $aColumns = array('codigo_producto', 'nombre_producto', 'referencia','lote','fecha_registro','fecha_vencimiento');//Columnas de busqueda
		 $sTable = "inventarios as inv, usuarios as us";
		$sWhere = "WHERE inv.ruc_empresa ='".  $ruc_empresa ." '  and inv.operacion='SALIDA' and us.id=inv.id_usuario " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (inv.ruc_empresa ='".  $ruc_empresa ." ' and inv.operacion='SALIDA' and us.id=inv.id_usuario AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND inv.ruc_empresa = '".  $ruc_empresa ."' and inv.operacion='SALIDA' and us.id=inv.id_usuario OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND inv.ruc_empresa = '".  $ruc_empresa ."' and inv.operacion='SALIDA' and us.id=inv.id_usuario ", -3 );
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
		$reload = '../entradas_inventario.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			?>
		<div class="table-responsive">
			<div class="panel panel-warning">
			  <table class="table table-hover">
				<tr  class="warning">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("codigo_producto");'>Código</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("nombre_producto");'>Producto</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("cantidad_salida");'>Cantidad</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("id_medida");'>Medida</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("precio");'>Precio</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("lote");'>Lote</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("referencia");'>Referencia</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("id_bodega");'>Bodega</button></th>
					<th class='col-xs-1' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("fecha_registro");'>Salida</button></th>
					<th class='col-xs-1' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("fecha_vencimiento");'>Vencimiento</button></th>
					<th class='col-xs-1' style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-warning" onclick='ordenar("id_usuario");'>Usuario</button></th>
					<th class='text-right'>Opciones</th>
				</tr>
				<?php
				while ($row=mysqli_fetch_array($query)){
						$id_inventario=$row['id_inventario'];
						$codigo_producto=strtoupper ($row['codigo_producto']);
						$nombre_producto=strtoupper ($row['nombre_producto']);
						$cantidad_salida=$row['cantidad_salida'];
						$id_medida=$row['id_medida'];
						$lote=strtoupper ($row['lote']);
						$id_bodega=strtoupper ($row['id_bodega']);
						$tipo_registro=$row['tipo_registro'];
						$id_producto=$row['id_producto'];
						//buscar unidades de medida
						$busca_medida = "SELECT * FROM unidad_medida WHERE id_medida = $id_medida";
						 $result_medida = $con->query($busca_medida);
						 $row_medida = mysqli_fetch_array($result_medida);
						 $unidad_medida=$row_medida['nombre_medida'];
						 $precio=$row['precio'];
						 
						$fecha_salida=$row['fecha_registro'];
						$fecha_vencimiento=$row['fecha_vencimiento'];
						$referencia=strtoupper ($row['referencia']);
						$usuario=$row['nombre'];
						 
						//buscar bodegas
						$busca_bodega = "SELECT * FROM bodega WHERE id_bodega = $id_bodega";
						 $result_bodega = $con->query($busca_bodega);
						 $row_bodega = mysqli_fetch_array($result_bodega);
						 $nombre_bodega=$row_bodega['nombre_bodega'];
					?>
					<input type="hidden" value="<?php echo $id_inventario;?>" id="id_inventario<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $codigo_producto;?>" id="codigo_producto<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $nombre_producto;?>" id="nombre_producto<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo  date("d-m-Y", strtotime($fecha_salida));?>" id="fecha_salida<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo  date("d-m-Y", strtotime($fecha_vencimiento));?>" id="fecha_caducidad<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $cantidad_salida;?>" id="cantidad<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $precio;?>" id="precio<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $unidad_medida;?>" id="medida<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $lote;?>" id="lote<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $id_bodega;?>" id="bodega<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $referencia;?>" id="referencia<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $tipo_registro;?>" id="tipo_registro<?php echo $id_inventario;?>">
					<input type="hidden" value="<?php echo $id_producto;?>" id="id_producto<?php echo $id_inventario;?>">
					<tr>
						<td><?php echo $codigo_producto; ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($nombre_producto); ?></td>
						<td><?php echo number_format($cantidad_salida,4,'.','');?></td>
						<td><?php echo strtoupper ($unidad_medida); ?></td>
						<td><?php echo number_format($precio,4,'.','');?></td>
						<td><?php echo $lote; ?></td>
						<td class='col-xs-2'><?php echo strtoupper ($referencia); ?></td>
						<td><?php echo strtoupper ($nombre_bodega); ?></td>
						<td><?php echo date("d-m-Y", strtotime($fecha_salida)); ?></td>
						<td><?php echo date("d-m-Y", strtotime($fecha_vencimiento)); ?></td>
						<td class='col-xs-1'><?php echo $usuario; ?></td>
					<td class='text-right'>
					<?php
					if ($tipo_registro=="M"){
					?>
					<a href="#" class='btn btn-info btn-xs' title='Editar salida' onclick="obtener_datos('<?php echo $id_inventario;?>');" data-toggle="modal" data-target="#EditarSalida"><i class="glyphicon glyphicon-edit"></i></a>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar salida' onclick="eliminar_salida('<?php echo $id_inventario;?>');"><i class="glyphicon glyphicon-trash"></i></a> 
					<?php
					}
					if ($tipo_registro=="A"){
					?>	
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar salida' onclick="eliminar_salida('<?php echo $id_inventario;?>');"><i class="glyphicon glyphicon-trash"></i></a> 
					<?php
					}
					if ($tipo_registro=="T"){
					?>
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar salida' onclick="eliminar_salida('<?php echo $id_inventario;?>');"><i class="glyphicon glyphicon-trash"></i></a> 
					<?php
					}
					if ($tipo_registro=="C"){
					?>	
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar salida' onclick="eliminar_salida('<?php echo $id_inventario;?>');"><i class="glyphicon glyphicon-trash"></i></a> 
					<?php
					}
					if ($tipo_registro=="P"){
						?>	
						<a href="#" class='btn btn-danger btn-xs' title='Eliminar salida' onclick="eliminar_salida('<?php echo $id_inventario;?>');"><i class="glyphicon glyphicon-trash"></i></a> 
						<?php
						}
					?>			
					</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan=12 ><span class="pull-right">
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
		//para eliminar una salida de inventarios
	if ($action == 'eliminar_salida'){
	if (!empty($_GET['id_salida'])){
	$id_salida=mysqli_real_escape_string($con,(strip_tags($_GET["id_salida"],ENT_QUOTES)));
	
	//buscar ese producto para saber si ya hay salidas y si hay mas salidas que entradas no se puede eliminar		
	$busca_datos_producto = "SELECT * FROM inventarios WHERE ruc_empresa='".$ruc_empresa."' and id_inventario='".$id_salida."'";
	$result_datos_producto = $con->query($busca_datos_producto);
	$datos_producto = mysqli_fetch_array($result_datos_producto);
	$codigo_producto= $datos_producto['codigo_producto'];
	$nombre_producto= $datos_producto['nombre_producto'];
	$id_bodega= $datos_producto['id_bodega'];
	$cantidad_entrada= $datos_producto['cantidad_entrada'];
	$tipo_operacion= $datos_producto['operacion'];
	$id_registro_compra= $datos_producto['id_documento_venta'];
	$id_producto= $datos_producto['id_producto'];
	$tipo_registro= $datos_producto['tipo_registro'];
	$codigo_registro= $datos_producto['id_documento_venta'];
	
	include_once("../clases/saldo_producto_y_conversion.php");
	$saldo_producto_factura = new saldo_producto_y_conversion();
	$saldo_final= $saldo_producto_factura->existencias_productos($id_bodega, $id_producto, $con);
		
		if($delete=mysqli_query($con,"DELETE FROM inventarios WHERE id_inventario = '".$id_salida."'")){
			echo "<script>
			$.notify('La salida ha sido eliminada satisfactoriamente.','success');
			setTimeout(function (){location.reload()}, 1000);
			</script>";
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
	}
}

if (isset($errors)){			
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Error!</strong> 
					<?php
						foreach ($errors as $error) {
								echo $error;
							}
						?>
			</div>
			<?php
			}
			if (isset($messages)){
				
				?>
				<div class="alert alert-success" role="alert">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>¡Bien hecho!</strong>
						<?php
							foreach ($messages as $message) {
									echo $message;
								}
							?>
				</div>
				<?php
			}

?>
