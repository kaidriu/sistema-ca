<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
		session_start();
		$id_usuario = $_SESSION['id_usuario'];
		$ruc_empresa = $_SESSION['ruc_empresa'];
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

	if($action == 'buscar_bodegas'){		
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $aColumns = array('id_bodega', 'nombre_bodega');//Columnas de busqueda
		 $sTable = "bodega";
		$sWhere = "WHERE ruc_empresa ='".  $ruc_empresa ." '  " ;
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (ruc_empresa ='".  $ruc_empresa ." ' AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND ruc_empresa = '".  $ruc_empresa ."' OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '".  $ruc_empresa ."' ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by nombre_bodega asc ";
		
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
		$reload = '../bodegas.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			?>
		<div class="table-responsive">
			<div class="panel panel-info">
			  <table class="table table-hover">
				<tr  class="info">
					<th>id</th>
					<th>Nombre</th>
					<th class='text-right'>Opciones</th>
				</tr>
				<?php
				while ($row=mysqli_fetch_array($query)){
						$id_bodega=$row['id_bodega'];
						$nombre_bodega=strtoupper ($row['nombre_bodega']);					
					?>					
					<input type="hidden" value="<?php echo $id_bodega;?>" id="id_bodega<?php echo $id_bodega;?>">
					<input type="hidden" value="<?php echo $nombre_bodega;?>" id="nombre_bodega<?php echo $id_bodega;?>">
					<tr>
						<td><?php echo $id_bodega; ?></td>
						<td class='col-xs-4'><?php echo $nombre_bodega; ?></td>

					<td class='text-right'>
					<a href="#" class='btn btn-info btn-md' title='Editar bodega' onclick="obtener_datos('<?php echo $id_bodega;?>');" data-toggle="modal" data-target="#bodegas"><i class="glyphicon glyphicon-edit"></i></a> 
					<a href="#" class='btn btn-danger btn-md' title='Eliminar bodega' onclick="eliminar_bodega('<?php echo $id_bodega;?>');"><i class="glyphicon glyphicon-trash"></i></a> 
					</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan=3 ><span class="pull-right">
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

	//inicio guardar y editar categoria
if ($action == 'guardarYeditar_bodegas'){
	
	//guardar
	if (empty($_POST['id_bodega'])){
	if (empty($_POST['nombre_bodega'])){
           $errors[] = "Ingrese nombre de la bodega";
		}else if (!empty($_POST['nombre_bodega'])){
		$nombre_bodega=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_bodega"],ENT_QUOTES)));
	//para ver si esta repetido
		 $busca_bodegas = "SELECT * FROM bodega WHERE ruc_empresa = '$ruc_empresa' and nombre_bodega = '$nombre_bodega'";
		 $result = $con->query($busca_bodegas);
		 $count = mysqli_num_rows($result);
		 if ($count == 1){
		$errors []= "El nombre de la bodega que intenta guardar ya esta registrado.".mysqli_error($con);
		}else{
		
		$sql="INSERT INTO bodega VALUES (NULL, '$ruc_empresa', '$nombre_bodega')";
		$query_new_insert = mysqli_query($con,$sql);
			if ($query_new_insert){
				$messages[] = "Nueva bodega registrada satisfactoriamente.";	
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";	
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		}
		}else {
			$errors []= "Error desconocido.";
		}
	}
//editar
if (!empty($_POST['id_bodega'])){
	if (empty($_POST['nombre_bodega'])){
           $errors[] = "Ingrese nombre de la bodega que sea modificar";
		}else if (!empty($_POST['nombre_bodega'])){
		$nombre_bodega=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_bodega"],ENT_QUOTES)));
		$id_bodega=mysqli_real_escape_string($con,(strip_tags($_POST["id_bodega"],ENT_QUOTES)));
	//para ver si esta repetido
		 $busca_bodegas = "SELECT * FROM bodega WHERE ruc_empresa = '$ruc_empresa' and nombre_bodega = '$nombre_bodega'";
				 $result = $con->query($busca_bodegas);
				 $count = mysqli_num_rows($result);
				 if ($count == 1){
				$errors []= "El nombre de la bodega que intenta guardar ya esta registrado.".mysqli_error($con);
				}else{
		
		$sql="UPDATE bodega SET nombre_bodega='".$nombre_bodega."' WHERE id_bodega='".$id_bodega."'";
		$query_new_insert = mysqli_query($con,$sql);
			if ($query_new_insert){
				$messages[] = "La bodega ha sido modificada satisfactoriamente.";	
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";	
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
		}
		}else {
			$errors []= "Error desconocido.";
		}
	}

}
//fin guardar y editar categoria
		
if ($action == 'eliminar_bodega'){
	if (!empty($_GET['id_bodega'])){
	$id_bodega=mysqli_real_escape_string($con,(strip_tags($_GET["id_bodega"],ENT_QUOTES)));

	$buscar_bodega=mysqli_query($con,"SELECT count(*) AS numrows FROM inventarios WHERE id_bodega = '".$id_bodega."'");
	$contar_bodega=mysqli_fetch_array($buscar_bodega);
	$numrows = $contar_bodega['numrows'];
	if ($numrows>0){
		$errors []= "No es posible eliminar, existen registros con esta bodega.";
	}else{
		if($delete=mysqli_query($con,"DELETE FROM bodega WHERE id_bodega = $id_bodega")){
			$messages[] = "La bodega ha sido eliminada satisfactoriamente.";	
				echo "<script>setTimeout(function () {location.reload()}, 60 * 20)</script>";	
			} else{
				$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
			}
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