<?php
	/* Connect To Database*/
	include("../conexiones/conectalogin.php");
	$con = conenta_login();
	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];

	//PARA ELIMINAR LAS CARGAS
if (isset($_POST['codigo'])){
	$codigo=$_POST['codigo'];
	$delete = mysqli_query($con, "DELETE FROM plan_cuentas WHERE codigo_unico = '".$codigo."' and id_cuenta NOT IN(SELECT id_cuenta FROM detalle_diario_contable)");
	echo (mysqli_error($con));
		if ($delete){
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Bien hecho!</strong> Se eliminó correctamente la carga.
			</div>
			<?php
		}else {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Error!</strong> Lo siento algo ha salido mal intenta nuevamente.
			</div>
			<?php
			
		}
	}
	
	
//PARA BUSCAR LAS CARGAS
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if($action == 'cargas_plan_de_cuentas'){

		$query= mysqli_query($con,"SELECT * FROM  plan_cuentas WHERE ruc_empresa ='".  $ruc_empresa ." ' group by codigo_unico");
		$numrows = mysqli_num_rows($query);
		//loop through fetched data
		if ($numrows>0){
			?>
			<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>Fecha carga</th>
					<th class='text-right'>Opciones</th>
					
				</tr>
				<?php
				while ($row=mysqli_fetch_array($query)){
						$codigo_unico=$row['codigo_unico'];
						$fecha_registro=$row['fecha_registro'];
					?>
					<input type="hidden" value="<?php echo $codigo_unico;?>" id="codigo_unico<?php echo $codigo_unico;?>">
						<tr>
						<td><?php echo $fecha_registro; ?></td>
					<td><span class="pull-right">
					<a href="#" class='btn btn-danger btn-xs' title='Eliminar' onclick="eliminar_carga('<?php echo $codigo_unico; ?>')"><i class="glyphicon glyphicon-erase"></i> </a>
					</span></td>
					
					</tr>
				<?php
				}
				?>
			  </table>
			</div>
			</div>
			<?php
		}
	}
?>