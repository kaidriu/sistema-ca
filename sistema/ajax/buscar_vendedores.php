<?php
include("../conexiones/conectalogin.php");
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	
	 if ($action == 'eliminar_vendedor'){
        $id_vendedor=intval($_GET['id_vendedor']);
		//CONTAR CUANTOS vendedores HAY PARA ELIMINAR
	
		$query_vendedores=mysqli_query($con, "select * from vendedores_ventas where id_vendedor='".$id_vendedor."'");
		$count_vendedores=mysqli_num_rows($query_vendedores);
		
		if ($count_vendedores > 0){
			echo "<script>$.notify('No se puede eliminar. Existen resgistros con este vendedor.','error')</script>";
		}else{
				if ($delete=mysqli_query($con,"DELETE FROM vendedores WHERE id_vendedor='".$id_vendedor."'")){
					echo "<script>$.notify('Vendedor eliminado.','success')</script>";
				} else{
					echo "<script>$.notify('Lo siento algo ha salido mal intenta nuevamente.','error')</script>";
				}
		
		}

	 }

		
	if($action == 'buscar_vendedores'){
		$condicion_ruc_empresa=	"mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'";

		// escaping, additionally removing everything that could be (html/javascript-) code
         $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		 $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
		 $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
		 $aColumns = array('nombre','ruc','correo','numero_id','telefono');//Columnas de busqueda
		 $sTable = "vendedores";
		 $sWhere = "WHERE $condicion_ruc_empresa";
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE ($condicion_ruc_empresa AND ";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$q."%' AND $condicion_ruc_empresa OR ";
			}
			$sWhere = substr_replace( $sWhere, "AND $condicion_ruc_empresa ", -3 );
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
		$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = '../vendedores.php';
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			
			?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table">
				<tr  class="info">
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre");'>Nombre</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("numero_id");'>Ruc/Cedula</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("telefono");'>Teléfono</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("correo");'>Email</button></th>
					<th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("direccion");'>Dirección</button></th>
					<th class='text-right'>Opciones</th>				
				</tr>
				<?php
				while ($row=mysqli_fetch_array($query)){
						$id_vendedor=$row['id_vendedor'];
						$nombre_vendedor=$row['nombre'];
						$numero_id=$row['numero_id'];
						$telefono=$row['telefono'];
						$correo=$row['correo'];
						$direccion=$row['direccion'];
						$tipo_id=$row['tipo_id'];
					?>
					<input type="hidden" value="<?php echo $nombre_vendedor;?>" id="nombre_vendedor<?php echo $id_vendedor;?>">
					<input type="hidden" value="<?php echo $numero_id;?>" id="numero_id<?php echo $id_vendedor;?>">
					<input type="hidden" value="<?php echo $telefono;?>" id="telefono<?php echo $id_vendedor;?>">
					<input type="hidden" value="<?php echo $correo;?>" id="correo<?php echo $id_vendedor;?>">
					<input type="hidden" value="<?php echo $direccion;?>" id="direccion<?php echo $id_vendedor;?>">
					<input type="hidden" value="<?php echo $tipo_id;?>" id="tipo_id<?php echo $id_vendedor;?>">
					<tr>						
						<td><?php echo ucwords($nombre_vendedor); ?></td>
						<td><?php echo $numero_id; ?></td>
						<td><?php echo $telefono; ?></td>
						<td><?php echo $correo;?></td>
						<td><?php echo $direccion;?></td>
					<td ><span class="pull-right">
					<a href="#" class='btn btn-info btn-xs' title='Editar vendedor' onclick="obtener_datos('<?php echo $id_vendedor; ?>');" data-toggle="modal" data-target="#editar_vendedor"><i class="glyphicon glyphicon-edit"></i></a> 
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar vendedor' onclick="eliminar_vendedor('<?php echo $id_vendedor; ?>');"><i class="glyphicon glyphicon-trash"></i></a> 	
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="8"><span class="pull-right">
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