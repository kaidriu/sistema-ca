<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	include_once("../clases/saldo_producto_y_conversion.php");
	include("../ajax/pagination.php");
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$con = conenta_login();

	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

	if($action == 'limpiar_tabla_tmp'){
		$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."'");
	}


if($action == 'mostrar_consulta'){
	$desde=date('Y/m/d', strtotime($_GET['fecha_desde']));
	$hasta=date('Y/m/d', strtotime($_GET['fecha_hasta']));
	$ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
	$por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
	$tipo = mysqli_real_escape_string($con,(strip_tags($_GET['tipo'], ENT_QUOTES)));
	$producto = mysqli_real_escape_string($con,(strip_tags($_GET['producto'], ENT_QUOTES)));
	$marca = mysqli_real_escape_string($con,(strip_tags($_GET['marca'], ENT_QUOTES)));
	$lote = mysqli_real_escape_string($con,(strip_tags($_GET['lote'], ENT_QUOTES)));
	$caducidad = mysqli_real_escape_string($con,(strip_tags($_GET['caducidad'], ENT_QUOTES)));
	$referencia = mysqli_real_escape_string($con,(strip_tags($_GET['referencia'], ENT_QUOTES)));

	switch ($tipo) {
		case "1"://entradas
			$data=entradas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia);
			entradas_view($data);
		break;
		case "2"://salidas
			$data=salidas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia);
			salidas_view($data);
		break;
		case "3"://existencia en general
			$data=existencia_general($producto, $ordenado, $por, $ruc_empresa, $con, $hasta, $id_usuario, $marca, $lote, $caducidad);
			existencia_general_view($data);
		break;
		case "4"://existencia caducidad
			$data=existencia_caducidad($producto, $ordenado, $por, $ruc_empresa, $con, $hasta, $id_usuario, $marca, $lote, $caducidad);
			existencia_caducidad_view($data);
		break;
		case "5"://existencia lote
			$data=existencia_lote($producto, $ordenado, $por, $ruc_empresa, $con, $hasta, $id_usuario, $marca, $lote, $caducidad);
			existencia_lote_view($data);
		break;
	}
	
}


function entradas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia){
	if (empty($producto)){
		$condicion_producto="";
		}else{
		$condicion_producto = " and inv.id_producto =" . $producto;
	}

	if (empty($marca)){
		$condicion_marca="";
		}else{
		$condicion_marca=" and mar_pro.id_marca=".$marca;
	}

	if (empty($lote)){
		$condicion_lote="";
		}else{
		$condicion_lote=" and inv.lote LIKE '%" . $lote . "%' ";
	} 
	
	if (empty($caducidad)){
		$condicion_caducidad="";
		}else{
		$condicion_caducidad=" and inv.fecha_vencimiento LIKE '%" . $caducidad . "%' ";
	} 

	if (empty($referencia)){
		$condicion_referencia="";
		}else{
		$condicion_referencia = " and inv.referencia LIKE '%" . $referencia . "%' ";
	}
	$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='ENTRADA' 
		and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' 
		and inv.cantidad_entrada > 0 $condicion_producto $condicion_marca $condicion_lote $condicion_caducidad $condicion_referencia  
		order by $ordenado $por" ;

    $sTable = "inventarios as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida 
	INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega 
	INNER JOIN usuarios as usu ON usu.id=inv.id_usuario 
	LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto 
	LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
   
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
   $reload = '../reporte_inventarios.php';
   //main query to fetch the data
   $sql="SELECT mar.nombre_marca as marca, round(inv.costo_unitario, 4) as costo_unitario, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, round(inv.cantidad_entrada,4) as cantidad_entrada, med.nombre_medida as medida, inv.referencia as referencia, bod.nombre_bodega as bodega,
   inv.fecha_registro as fecha_registro, inv.fecha_vencimiento as fecha_vencimiento, inv.lote as lote, usu.nombre as usuario
   FROM  $sTable $sWhere LIMIT $offset, $per_page";
   $query = mysqli_query($con, $sql);
   $data=array('query'=>$query, 'reload'=> $reload, 'page'=> $page, 'total_pages'=>$total_pages, 'adjacents'=>$adjacents, 'numrows'=>$numrows);
   return $data;
}

function entradas_view($data){
	$query=$data['query'];
	$reload=$data['reload'];
	$page=$data['page'];
	$total_pages=$data['total_pages'];
	$adjacents=$data['adjacents'];
	$numrows=$data['numrows'];
   //loop through fetched data
   if($numrows>0){
	   ?>
   <div class="table-responsive">
	   <div class="panel panel-info">
		 <table class="table table-hover">
		   <tr  class="info">
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_entrada");'>Cantidad</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Medida</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_marca");'>Marca</button></th> 
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("costo_unitario");'>Costo</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote");'>Lote</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("referencia");'>Referencia</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Bodega</button></th>
			   <th class="col-xs-1" style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_registro");'>Fecha_registro</button></th>
			   <th class="col-xs-1" style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_vencimiento");'>Caducidad</button></th>
			   <th class="col-xs-1" style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Usuario</button></th>
		   </tr>
		   <?php
		   while ($row=mysqli_fetch_array($query)){			   
			   ?>					
			   <tr>
				   <td><?php echo strtoupper($row['codigo_producto']); ?></td>
				   <td class="col-xs-2"><?php echo strtoupper($row['nombre_producto']); ?></td>
				   <td><?php echo number_format($row['cantidad_entrada'],4,'.',''); ?></td>
				   <td><?php echo strtoupper($row['medida']); ?></td>
				   <td><?php echo strtoupper($row['marca']); ?></td>
				   <td><?php echo number_format($row['costo_unitario'],4,'.',''); ?></td>
				   <td><?php echo $row['lote']; ?></td>
				   <td class="col-xs-2"><?php echo strtoupper($row['referencia']); ?></td>
				   <td><?php echo strtoupper($row['bodega']); ?></td>
				   <td><?php echo date("d-m-Y", strtotime($row['fecha_registro'])); ?></td>
				   <td><?php echo date("d-m-Y", strtotime($row['fecha_vencimiento'])); ?></td>
				   <td class="col-xs-1"><?php echo strtoupper($row['usuario']); ?></td>
			   </tr>
			   <?php
		   }
		   ?>
		   <tr>
			   <td colspan="12"><span class="pull-right">
			   <?php
				echo paginate($reload, $page, $total_pages, $adjacents);
			   ?>
			   </span></td>
		   </tr>
		 </table>
	   </div>
	   </div>
	   <?php
   }else{
	?>
	<div class="alert alert-danger" role="alert">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>Mensaje! </strong> 
			<?php
				echo "No hay datos para mostrar.";
			?>
	</div>
	<?php
	   
   }
   
}

function salidas($producto, $ordenado, $por, $ruc_empresa, $con, $desde, $hasta, $marca, $lote, $caducidad, $referencia){
	if (empty($producto)){
		$condicion_producto="";
		}else{
		$condicion_producto = " and inv.id_producto =" . $producto;
	}
	
	if (empty($marca)){
		$condicion_marca="";
		}else{
		$condicion_marca=" and mar_pro.id_marca=".$marca;
	}

	if (empty($lote)){
		$condicion_lote="";
		}else{
		$condicion_lote=" and inv.lote LIKE '%" . $lote . "%' ";
	} 
	
	if (empty($caducidad)){
		$condicion_caducidad="";
		}else{
		$condicion_caducidad=" and inv.fecha_vencimiento LIKE '%" . $caducidad . "%' ";
	} 

	if (empty($referencia)){
		$condicion_referencia="";
		}else{
		$condicion_referencia = " and inv.referencia LIKE '%" . $referencia . "%' ";
	}

	
	$sWhere = " WHERE inv.ruc_empresa ='". $ruc_empresa ." ' and inv.operacion='SALIDA' 
	and DATE_FORMAT(inv.fecha_registro, '%Y/%m/%d') BETWEEN '".$desde."' and '".$hasta."' 
	and inv.cantidad_salida > 0 $condicion_producto $condicion_marca $condicion_lote $condicion_caducidad $condicion_referencia 
	order by $ordenado $por" ;

    $sTable = "inventarios as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega INNER JOIN usuarios as usu ON usu.id=inv.id_usuario LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
   
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
   $reload = '../reporte_inventarios.php';
   //main query to fetch the data
   $sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, round(inv.cantidad_salida, 4) as cantidad_salida, med.nombre_medida as medida, round(inv.precio,4) as precio, inv.referencia as referencia, bod.nombre_bodega as bodega,
   inv.fecha_registro as fecha_registro, inv.fecha_vencimiento as fecha_vencimiento, inv.lote as lote, usu.nombre as usuario
   FROM  $sTable $sWhere LIMIT $offset,$per_page";
   $query = mysqli_query($con, $sql);
   $data=array('query'=>$query, 'reload'=> $reload, 'page'=> $page, 'total_pages'=>$total_pages, 'adjacents'=>$adjacents, 'numrows'=>$numrows);
   return $data;
}
   //loop through fetched data
   function salidas_view($data){
	$query=$data['query'];
	$reload=$data['reload'];
	$page=$data['page'];
	$total_pages=$data['total_pages'];
	$adjacents=$data['adjacents'];
	$numrows=$data['numrows'];
	if($numrows>0){
	   ?>
   <div class="table-responsive">
	   <div class="panel panel-info">
		 <table class="table table-hover">
		   <tr  class="info">
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_salida");'>Cantidad</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Medida</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_marca");'>Marca</button></th> 
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("precio");'>Precio_venta</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote");'>Lote</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("referencia");'>Referencia</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Bodega</button></th>
			   <th class="col-xs-1" style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_registro");'>Fecha_registro</button></th>
			   <th class="col-xs-1" style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_vencimiento");'>Caducidad</button></th>
			   <th class="col-xs-1" style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Usuario</button></th>
		   </tr>
		   <?php
		   while ($row=mysqli_fetch_array($query)){			   
			   ?>					
			   <tr>
				   <td><?php echo strtoupper($row['codigo_producto']); ?></td>
				   <td class="col-xs-2"><?php echo strtoupper($row['nombre_producto']); ?></td>
				   <td><?php echo number_format($row['cantidad_salida'],4,'.',''); ?></td>
				   <td><?php echo strtoupper($row['medida']); ?></td>
				   <td><?php echo strtoupper($row['marca']); ?></td>
				   <td><?php echo number_format($row['precio'],4,'.',''); ?></td>
				   <td><?php echo $row['lote']; ?></td>
				   <td class="col-xs-2"><?php echo strtoupper($row['referencia']); ?></td>
				   <td><?php echo strtoupper($row['bodega']); ?></td>
				   <td><?php echo date("d-m-Y", strtotime($row['fecha_registro'])); ?></td>
				   <td><?php echo date("d-m-Y", strtotime($row['fecha_vencimiento'])); ?></td>
				   <td class="col-xs-1"><?php echo strtoupper($row['usuario']); ?></td>
			   </tr>
			   <?php
		   }
		   ?>
		   <tr>
			   <td colspan="12"><span class="pull-right">
			   <?php
				echo paginate($reload, $page, $total_pages, $adjacents);
			   ?>
			   </span></td>
		   </tr>
		 </table>
	   </div>
	   </div>
	   <?php
	   }else{
		?>
		<div class="alert alert-danger" role="alert">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
				<strong>Mensaje! </strong> 
				<?php
					echo "No hay datos para mostrar.";
				?>
		</div>
		<?php   
	   }
   
}

function existencia_general($producto, $ordenado, $por, $ruc_empresa, $con, $hasta, $id_usuario, $marca, $lote, $caducidad){
$saldo_producto = new saldo_producto_y_conversion();
if (empty($marca)){
	$condicion_marca="";
	}else{
	$condicion_marca=" and mar_pro.id_marca=".$marca;
}
if (empty($producto)){
	$condicion_producto="";
}else{
	$condicion_producto= "and id_producto='".$producto."'";
}

if (empty($lote)){
	$condicion_lote="";
}else{
	$condicion_lote= "and lote LIKE '%" . $lote . "%' ";
}

if (empty($caducidad)){
	$condicion_caducidad="";
}else{
	$condicion_caducidad= "and fecha_vencimiento LIKE '%" . $caducidad . "%' ";
}

$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."'");
$query_guarda_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
SELECT null, id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_vencimiento,ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida), lote, '".$id_usuario."' FROM inventarios WHERE ruc_empresa ='".$ruc_empresa."' and DATE_FORMAT(fecha_registro, '%Y/%m/%d') <= '".$hasta."' 
$condicion_producto $condicion_lote $condicion_caducidad group by id_producto, id_medida, id_bodega");

//selet para buscar linea por linea y ver si la medida es igual al producto o sino modificar esa linea
	$resultado= array();
	$sql_filas = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp as exi_tmp LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=exi_tmp.id_producto WHERE exi_tmp.ruc_empresa = '".$ruc_empresa."' and exi_tmp.id_usuario = '".$id_usuario."' and exi_tmp.cantidad_salida>0 ");
	while ($row_temporales = mysqli_fetch_array($sql_filas)){
	$id_producto=$row_temporales["id_producto"];
	$codigo_producto=$row_temporales["codigo_producto"];
	$nombre_producto=$row_temporales["nombre_producto"];
		//obtener medida del producto
		$id_medida_salida= $row_temporales['id_unidad_medida'];
		
		$id_medida_entrada=$row_temporales["id_medida"];
		$cantidad_entrada_tmp = $row_temporales['cantidad_entrada'];
		$id_bodega = $row_temporales['id_bodega'];
		$caducidad = $row_temporales['fecha_caducidad'];
		$lote = $row_temporales['lote'];
	
		if ($id_medida_entrada != $id_medida_salida){
		$id_tmp=$row_temporales["id_existencia_tmp"];
		$cantidad_a_transformar = $row_temporales['cantidad_salida'];
		$total_saldo_producto= $saldo_producto->conversion($id_medida_entrada, $id_medida_salida, $id_producto, '0', $cantidad_a_transformar, $con, 'saldo');	
		$resultado[]= array('id_tmp'=>$id_tmp, 'id_producto'=>$id_producto, 'codigo_producto'=>$codigo_producto, 'nombre_producto'=>$nombre_producto, 'entrada'=>$cantidad_entrada_tmp, 'salida'=>$cantidad_a_transformar, 'id_bodega'=>$id_bodega, 'id_medida'=>$id_medida_salida, 'caducidad'=>$caducidad, 'saldo_convertido'=> $total_saldo_producto, 'lote'=> $lote );
		}
	}
	foreach ($resultado as $valor){
		$delete_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$valor['id_tmp']."';");
		$sql_actualizar=mysqli_query($con,"INSERT INTO existencias_inventario_tmp VALUES (null,'".$valor['id_producto']."','".$valor['codigo_producto']."','".$valor['nombre_producto']."','".$valor['entrada']."','".$valor['saldo_convertido']."','".$valor['id_bodega']."','".$valor['id_medida']."','".$valor['caducidad']."', '".$ruc_empresa."', '".$valor['saldo_convertido']."','".$valor['lote']."','".$id_usuario."' )");
	}
	//todos los id temporales traidos para luego borrarlos
	$ides= array();
	$sql_filas_borrar = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."'");
	while ($row_ides_temporales = mysqli_fetch_array($sql_filas_borrar)){
	$id_temp_iniciales=$row_ides_temporales["id_existencia_tmp"];
	$ides[]= array('id_tmp_iniciales'=>$id_temp_iniciales);
	}
	$query_actualiza_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
	SELECT null,id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_caducidad, ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida),lote, '".$id_usuario."'  FROM existencias_inventario_tmp WHERE ruc_empresa ='". $ruc_empresa ."' group by id_bodega, id_producto, id_medida");
//eliminar los ides tmp iniciales
	foreach ($ides as $id_tm){
	$delete_ides_tmp_iniciales = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$id_tm['id_tmp_iniciales']."';");
	}
	
	$sTable = "existencias_inventario_tmp as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
	$sWhere = "WHERE inv.ruc_empresa ='". $ruc_empresa ." ' $condicion_marca and inv.saldo_producto > 0 order by $ordenado $por" ;

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
   $reload = '../reporte_inventarios.php';
   //main query to fetch the data
   $sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, inv.cantidad_entrada as cantidad_entrada, inv.cantidad_salida as cantidad_salida, 
   inv.saldo_producto as existencia, med.nombre_medida as medida, bod.nombre_bodega as bodega FROM $sTable $sWhere LIMIT $offset, $per_page";
  $query = mysqli_query($con, $sql);
  $data=array('query'=>$query, 'reload'=> $reload, 'page'=> $page, 'total_pages'=>$total_pages, 'adjacents'=>$adjacents, 'numrows'=>$numrows);
  return $data;
}
function existencia_general_view($data){
	$query=$data['query'];
	$reload=$data['reload'];
	$page=$data['page'];
	$total_pages=$data['total_pages'];
	$adjacents=$data['adjacents'];
	$numrows=$data['numrows'];
	if($numrows>0){
   //loop through fetched data
	   ?>
   <div class="table-responsive">
	   <div class="panel panel-info">
		 <table class="table table-hover">
		   <tr  class="info">
		   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_entrada");'>Entrada</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_salida");'>Salida</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("saldo_producto");'>Existencia</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_marca");'>Marca</button></th>  
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Medida</button></th>
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Bodega</button></th>
		   </tr>
		   <?php
		   while ($row=mysqli_fetch_array($query)){			   
			   ?>					
			   <tr>
				   <td><?php echo strtoupper($row['codigo_producto']); ?></td>
				   <td class="col-xs-2"><?php echo strtoupper($row['nombre_producto']); ?></td>
				   <td><?php echo number_format($row['cantidad_entrada'],4,'.',''); ?></td>
				   <td><?php echo number_format($row['cantidad_salida'],4,'.',''); ?></td>
				   <td><?php echo number_format($row['existencia'],4,'.',''); ?></td>
				   <td><?php echo strtoupper($row['marca']); ?></td>
				   <td><?php echo strtoupper($row['medida']); ?></td>
				   <td><?php echo strtoupper($row['bodega']); ?></td>
			   </tr>
			   <?php
		   }
		   ?>
		   <tr>
			   <td colspan="10"><span class="pull-right">
			   <?php
				echo paginate($reload, $page, $total_pages, $adjacents);
			   ?>
			   </span></td>
		   </tr>
		 </table>
	   </div>
	   </div>
	   <?php
	   	   }else{
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Mensaje! </strong> 
					<?php
						echo "No hay datos para mostrar.";
					?>
			</div>
			<?php   
		   }
}

function existencia_caducidad($producto, $ordenado, $por, $ruc_empresa, $con, $hasta, $id_usuario, $marca, $lote, $caducidad){
	$saldo_producto = new saldo_producto_y_conversion();
	if (empty($marca)){
		$condicion_marca="";
		}else{
		$condicion_marca=" and mar_pro.id_marca=".$marca;
	}
	if (empty($producto)){
		$condicion_producto="";
	}else{
		$condicion_producto= "and id_producto='".$producto."'";
	}
	
	if (empty($caducidad)){
		$condicion_caducidad="";
	}else{
		$condicion_caducidad= "and fecha_vencimiento LIKE '%" . $caducidad . "%' ";
	}

	if (empty($lote)){
		$condicion_lote="";
	}else{
		$condicion_lote=" and lote LIKE '%" . $lote . "%' ";
	}

		
	$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."'");
	$query_guarda_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
	SELECT null, id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_vencimiento, ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida), lote, '".$id_usuario."' FROM inventarios WHERE ruc_empresa ='".$ruc_empresa."' 
	and DATE_FORMAT(fecha_registro, '%Y/%m/%d') <= '".$hasta."' $condicion_producto $condicion_caducidad $condicion_lote 
	group by id_bodega, id_producto, id_medida, fecha_vencimiento");
	
	//selet para buscar linea por linea y ver si la medida es igual al producto o sino modificar esa linea
		$resultado= array();
		$sql_filas = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp as exi_tmp LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=exi_tmp.id_producto WHERE exi_tmp.ruc_empresa = '".$ruc_empresa."' and exi_tmp.id_usuario = '".$id_usuario."' and exi_tmp.cantidad_salida>0 ");
		while ($row_temporales = mysqli_fetch_array($sql_filas)){
		$id_producto=$row_temporales["id_producto"];
		$codigo_producto=$row_temporales["codigo_producto"];
		$nombre_producto=$row_temporales["nombre_producto"];
			//obtener medida del producto
			$id_medida_salida= $row_temporales['id_unidad_medida'];
			
			$id_medida_entrada=$row_temporales["id_medida"];
			$cantidad_entrada_tmp = $row_temporales['cantidad_entrada'];
			$id_bodega = $row_temporales['id_bodega'];
			$caducidad = $row_temporales['fecha_caducidad'];
			$lote = $row_temporales['lote'];
		
			if ($id_medida_entrada != $id_medida_salida){
			$id_tmp=$row_temporales["id_existencia_tmp"];
			$cantidad_a_transformar = $row_temporales['cantidad_salida'];
			$total_saldo_producto= $saldo_producto->conversion($id_medida_entrada, $id_medida_salida, $id_producto, '0', $cantidad_a_transformar, $con, 'saldo');	
			$resultado[]= array('id_tmp'=>$id_tmp, 'id_producto'=>$id_producto, 'codigo_producto'=>$codigo_producto, 'nombre_producto'=>$nombre_producto, 'entrada'=>$cantidad_entrada_tmp, 'salida'=>$cantidad_a_transformar, 'id_bodega'=>$id_bodega, 'id_medida'=>$id_medida_salida, 'caducidad'=>$caducidad, 'saldo_convertido'=> $total_saldo_producto, 'lote'=> $lote );
			}
		}
		foreach ($resultado as $valor){
			$delete_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$valor['id_tmp']."';");
			$sql_actualizar=mysqli_query($con,"INSERT INTO existencias_inventario_tmp VALUES (null,'".$valor['id_producto']."','".$valor['codigo_producto']."','".$valor['nombre_producto']."','".$valor['entrada']."','".$valor['saldo_convertido']."','".$valor['id_bodega']."','".$valor['id_medida']."','".$valor['caducidad']."', '".$ruc_empresa."', '".$valor['saldo_convertido']."','".$valor['lote']."','".$id_usuario."' )");
		}
		//todos los id temporales traidos para luego borrarlos
		$ides= array();
		$sql_filas_borrar = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."'");
		while ($row_ides_temporales = mysqli_fetch_array($sql_filas_borrar)){
		$id_temp_iniciales=$row_ides_temporales["id_existencia_tmp"];
		$ides[]= array('id_tmp_iniciales'=>$id_temp_iniciales);
		}
		$query_actualiza_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
		SELECT null,id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_caducidad, ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida),lote, '".$id_usuario."'  FROM existencias_inventario_tmp WHERE ruc_empresa ='". $ruc_empresa ."' group by id_bodega, id_producto, id_medida, fecha_caducidad");
	//eliminar los ides tmp iniciales
		foreach ($ides as $id_tm){
		$delete_ides_tmp_iniciales = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$id_tm['id_tmp_iniciales']."';");
		}
		
	
		$sTable = "existencias_inventario_tmp as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
		$sWhere = "WHERE inv.ruc_empresa ='". $ruc_empresa ." ' $condicion_marca and inv.saldo_producto > 0 order by $ordenado $por" ;
	
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
	   $reload = '../reporte_inventarios.php';
	   //main query to fetch the data
	   $sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, inv.cantidad_entrada as cantidad_entrada, inv.cantidad_salida as cantidad_salida, 
	   inv.saldo_producto as existencia, med.nombre_medida as medida, bod.nombre_bodega as bodega, inv.fecha_caducidad as vencimiento, inv.lote as lote FROM $sTable $sWhere LIMIT $offset, $per_page";
	  $query = mysqli_query($con, $sql);
	  $data=array('query'=>$query, 'reload'=> $reload, 'page'=> $page, 'total_pages'=>$total_pages, 'adjacents'=>$adjacents, 'numrows'=>$numrows);
	  return $data;
	}

	function existencia_caducidad_view($data){
		$query=$data['query'];
		$reload=$data['reload'];
		$page=$data['page'];
		$total_pages=$data['total_pages'];
		$adjacents=$data['adjacents'];
		$numrows=$data['numrows'];
		if($numrows>0){
	   //loop through fetched data
		   ?>
	   <div class="table-responsive">
		   <div class="panel panel-info">
			 <table class="table table-hover">
			   <tr  class="info">
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_entrada");'>Entrada</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_salida");'>Salida</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("saldo_producto");'>Existencia</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_marca");'>Marca</button></th>  
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Medida</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Bodega</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote");'>Lote</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_caducidad");'>Vencimiento</button></th>
			   </tr>
			   <?php
			   while ($row=mysqli_fetch_array($query)){			   
				   ?>					
				   <tr>
					   <td><?php echo strtoupper($row['codigo_producto']); ?></td>
					   <td class="col-xs-2"><?php echo strtoupper($row['nombre_producto']); ?></td>
					   <td><?php echo number_format($row['cantidad_entrada'],4,'.',''); ?></td>
					   <td><?php echo number_format($row['cantidad_salida'],4,'.',''); ?></td>
					   <td><?php echo number_format($row['existencia'],4,'.',''); ?></td>
					   <td><?php echo strtoupper($row['marca']); ?></td>
					   <td><?php echo strtoupper($row['medida']); ?></td>
					   <td><?php echo strtoupper($row['bodega']); ?></td>
					   <td><?php echo strtoupper($row['lote']); ?></td>
					   <td><?php echo date('d-m-Y', strtotime($row['vencimiento'])); ?></td>
				   </tr>
				   <?php
			   }
			   ?>
			   <tr>
				   <td colspan="12"><span class="pull-right">
				   <?php
					echo paginate($reload, $page, $total_pages, $adjacents);
				   ?>
				   </span></td>
			   </tr>
			 </table>
		   </div>
		   </div>
		   <?php
		   	   }else{
				?>
				<div class="alert alert-danger" role="alert">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Mensaje! </strong> 
						<?php
							echo "No hay datos para mostrar.";
						?>
				</div>
				<?php   
			   }
}


function existencia_lote($producto, $ordenado, $por, $ruc_empresa, $con, $hasta, $id_usuario, $marca, $lote, $caducidad){
	$saldo_producto = new saldo_producto_y_conversion();
	if (empty($marca)){
		$condicion_marca="";
		}else{
		$condicion_marca=" and mar_pro.id_marca=".$marca;
	}
	if (empty($producto)){
		$condicion_producto="";
	}else{
		$condicion_producto= "and id_producto='".$producto."'";
	}
	if (empty($lote)){
		$condicion_lote="";
	}else{
		$condicion_lote=" and lote LIKE '%" . $lote . "%' ";
	}

	if (empty($caducidad)){
		$condicion_caducidad="";
	}else{
		$condicion_caducidad= "and fecha_vencimiento LIKE '%" . $caducidad . "%' ";
	}
	
	$delete_inventario_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."'");
	$query_guarda_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
	SELECT null, id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_vencimiento, ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida), lote, '".$id_usuario."' FROM inventarios 
	WHERE ruc_empresa ='".$ruc_empresa."' and DATE_FORMAT(fecha_registro, '%Y/%m/%d') <= '".$hasta."' 
	$condicion_producto $condicion_lote $condicion_caducidad group by id_bodega, id_producto, id_medida, lote");
	
	//selet para buscar linea por linea y ver si la medida es igual al producto o sino modificar esa linea
		$resultado= array();
		$sql_filas = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp as exi_tmp LEFT JOIN productos_servicios as pro_ser ON pro_ser.id=exi_tmp.id_producto WHERE exi_tmp.ruc_empresa = '".$ruc_empresa."' and exi_tmp.id_usuario = '".$id_usuario."' and exi_tmp.cantidad_salida>0 ");
		while ($row_temporales = mysqli_fetch_array($sql_filas)){
		$id_producto=$row_temporales["id_producto"];
		$codigo_producto=$row_temporales["codigo_producto"];
		$nombre_producto=$row_temporales["nombre_producto"];
			//obtener medida del producto
			$id_medida_salida= $row_temporales['id_unidad_medida'];
			
			$id_medida_entrada=$row_temporales["id_medida"];
			$cantidad_entrada_tmp = $row_temporales['cantidad_entrada'];
			$id_bodega = $row_temporales['id_bodega'];
			$caducidad = $row_temporales['fecha_caducidad'];
			$lote = $row_temporales['lote'];
		
			if ($id_medida_entrada != $id_medida_salida){
			$id_tmp=$row_temporales["id_existencia_tmp"];
			$cantidad_a_transformar = $row_temporales['cantidad_salida'];
			$total_saldo_producto= $saldo_producto->conversion($id_medida_entrada, $id_medida_salida, $id_producto, '0', $cantidad_a_transformar, $con, 'saldo');	
			$resultado[]= array('id_tmp'=>$id_tmp, 'id_producto'=>$id_producto, 'codigo_producto'=>$codigo_producto, 'nombre_producto'=>$nombre_producto, 'entrada'=>$cantidad_entrada_tmp, 'salida'=>$cantidad_a_transformar, 'id_bodega'=>$id_bodega, 'id_medida'=>$id_medida_salida, 'caducidad'=>$caducidad, 'saldo_convertido'=> $total_saldo_producto, 'lote'=> $lote );
			}
		}
		foreach ($resultado as $valor){
			$delete_tmp = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$valor['id_tmp']."';");
			$sql_actualizar=mysqli_query($con,"INSERT INTO existencias_inventario_tmp VALUES (null,'".$valor['id_producto']."','".$valor['codigo_producto']."','".$valor['nombre_producto']."','".$valor['entrada']."','".$valor['saldo_convertido']."','".$valor['id_bodega']."','".$valor['id_medida']."','".$valor['caducidad']."', '".$ruc_empresa."', '".$valor['saldo_convertido']."','".$valor['lote']."','".$id_usuario."' )");
		}
		//todos los id temporales traidos para luego borrarlos
		$ides= array();
		$sql_filas_borrar = mysqli_query($con,"SELECT * FROM existencias_inventario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario = '".$id_usuario."'");
		while ($row_ides_temporales = mysqli_fetch_array($sql_filas_borrar)){
		$id_temp_iniciales=$row_ides_temporales["id_existencia_tmp"];
		$ides[]= array('id_tmp_iniciales'=>$id_temp_iniciales);
		}
		
		$query_actualiza_inventario_tmp = mysqli_query($con, "INSERT INTO existencias_inventario_tmp (id_existencia_tmp, id_producto, codigo_producto,nombre_producto,cantidad_entrada,cantidad_salida,id_bodega,id_medida,fecha_caducidad, ruc_empresa, saldo_producto,lote,id_usuario) 
		SELECT null,id_producto, codigo_producto, nombre_producto, sum(cantidad_entrada), sum(cantidad_salida), id_bodega, id_medida, fecha_caducidad, ruc_empresa, sum(cantidad_entrada)-sum(cantidad_salida),lote, '".$id_usuario."'  FROM existencias_inventario_tmp WHERE ruc_empresa ='". $ruc_empresa ."' group by id_bodega, id_producto, id_medida, lote");
		//eliminar los ides tmp iniciales
		
	foreach ($ides as $id_tm){
		$delete_ides_tmp_iniciales = mysqli_query($con, "DELETE FROM existencias_inventario_tmp WHERE id_existencia_tmp='".$id_tm['id_tmp_iniciales']."';");
		}
		
		$sTable = "existencias_inventario_tmp as inv INNER JOIN unidad_medida as med ON med.id_medida=inv.id_medida INNER JOIN bodega as bod ON bod.id_bodega=inv.id_bodega LEFT JOIN marca_producto as mar_pro ON mar_pro.id_producto=inv.id_producto LEFT JOIN marca as mar ON mar.id_marca=mar_pro.id_marca";
		$sWhere = "WHERE inv.ruc_empresa ='". $ruc_empresa ." ' $condicion_marca and inv.saldo_producto > 0 order by $ordenado $por" ;
	
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
	   $reload = '../reporte_inventarios.php';
	   //main query to fetch the data
	   $sql="SELECT mar.nombre_marca as marca, inv.codigo_producto as codigo_producto, inv.nombre_producto as nombre_producto, inv.cantidad_entrada as cantidad_entrada, inv.cantidad_salida as cantidad_salida, 
	   inv.saldo_producto as existencia, med.nombre_medida as medida, bod.nombre_bodega as bodega, inv.lote as lote, inv.fecha_caducidad as vencimiento FROM $sTable $sWhere LIMIT $offset, $per_page";
	  $query = mysqli_query($con, $sql);
	  $data=array('query'=>$query, 'reload'=> $reload, 'page'=> $page, 'total_pages'=>$total_pages, 'adjacents'=>$adjacents, 'numrows'=>$numrows);
	  return $data;
	}
	function existencia_lote_view($data){
		$query=$data['query'];
		$reload=$data['reload'];
		$page=$data['page'];
		$total_pages=$data['total_pages'];
		$adjacents=$data['adjacents'];
		$numrows=$data['numrows'];
		if($numrows>0){
	   //loop through fetched data
		   ?>
	   <div class="table-responsive">
		   <div class="panel panel-info">
			 <table class="table table-hover">
			   <tr  class="info">
			   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("codigo_producto");'>Código</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_producto");'>Producto</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_entrada");'>Entrada</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("cantidad_salida");'>Salida</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("saldo_producto");'>Existencia</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre_marca");'>Marca</button></th>  
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Medida</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" >Bodega</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("lote");'>Lote</button></th>
				   <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("fecha_caducidad");'>Vencimiento</button></th>
			   </tr>
			   <?php
			   while ($row=mysqli_fetch_array($query)){			   
				   ?>					
				   <tr>
					   <td><?php echo strtoupper($row['codigo_producto']); ?></td>
					   <td class="col-xs-2"><?php echo strtoupper($row['nombre_producto']); ?></td>
					   <td><?php echo number_format($row['cantidad_entrada'],4,'.',''); ?></td>
					   <td><?php echo number_format($row['cantidad_salida'],4,'.',''); ?></td>
					   <td><?php echo number_format($row['existencia'],4,'.',''); ?></td>
					   <td><?php echo strtoupper($row['marca']); ?></td>
					   <td><?php echo strtoupper($row['medida']); ?></td>
					   <td><?php echo strtoupper($row['bodega']); ?></td>
					   <td><?php echo strtoupper($row['lote']); ?></td>
					   <td><?php echo date('d-m-Y', strtotime($row['vencimiento'])); ?></td>
				   </tr>
				   <?php
			   }
			   ?>
			   <tr>
				   <td colspan="12"><span class="pull-right">
				   <?php
					echo paginate($reload, $page, $total_pages, $adjacents);
				   ?>
				   </span></td>
			   </tr>
			 </table>
		   </div>
		   </div>
		   <?php
		   	   }else{
				?>
				<div class="alert alert-danger" role="alert">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Mensaje! </strong> 
						<?php
							echo "No hay datos para mostrar.";
						?>
				</div>
				<?php   
			   }
}

?>

