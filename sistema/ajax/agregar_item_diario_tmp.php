<?php
/* Connect To Database*/
include("../conexiones/conectalogin.php");

$con = conenta_login();
session_start();
$id_usuario = $_SESSION['id_usuario'];
$ruc_empresa = $_SESSION['ruc_empresa'];
if (isset($_POST['id_cuenta'])){$id_cuenta=$_POST['id_cuenta'];}
if (isset($_POST['cod_cuenta'])){$cod_cuenta = $_POST['cod_cuenta'];}
if (isset($_POST['cuenta_diario'])){$cuenta_diario=$_POST['cuenta_diario'];}
if (isset($_POST['debe_diario'])){$debe_diario=$_POST['debe_diario'];}
if (isset($_POST['haber_cuenta'])){$haber_diario=$_POST['haber_cuenta'];}
if (isset($_POST['det_cuenta'])){$det_cuenta=$_POST['det_cuenta'];}

//para guardar en el temporal
if (isset($_POST['detalle_diario'])){
$insert_tmp=mysqli_query($con, "INSERT INTO detalle_diario_tmp VALUES (null,'".$ruc_empresa."', '".$id_cuenta."', '".$cod_cuenta."', '".$cuenta_diario."', '".$debe_diario."', '".$haber_diario."', '".$det_cuenta."','".$id_usuario."')");
}

//para eliminar todos los datos del tmp
if (isset($_GET['borrar_todo'])){
$delete_todo=mysqli_query($con, "DELETE FROM detalle_diario_tmp WHERE id_usuario='".$id_usuario."' and ruc_empresa='".$ruc_empresa."'");
exit;
}

//para eliminar un iten
if (isset($_GET['id_diario'])){
$id_tmp=intval($_GET['id_diario']);	
$delete=mysqli_query($con, "DELETE FROM detalle_diario_tmp WHERE id_detalle_cuenta='".$id_tmp."'");
}

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

//actualiza cuenta contable
if (isset($_POST['action']) && $_POST['action'] == "actualizar_ceuntas_asiento") {
$id_tmp=$_POST["id_item"];
$id_cuenta=$_POST["id_cuenta"];
$codigo_cuenta=$_POST["codigo_cuenta"];
$nombre_cuenta=$_POST["nombre_cuenta"];
$actualiza_detalle=mysqli_query($con, "UPDATE detalle_diario_tmp SET id_cuenta='".$id_cuenta."', codigo_cuenta='".$codigo_cuenta."', cuenta='".$nombre_cuenta."' WHERE id_detalle_cuenta='".$id_tmp."'");
}
//actualiza debe
if (isset($_POST['action']) && $_POST['action'] == "actualizar_debe") {
$id_tmp=$_POST["id_item"];
$debe=$_POST["debe"];
$actualiza_detalle=mysqli_query($con, "UPDATE detalle_diario_tmp SET debe='".$debe."', haber='0.00' WHERE id_detalle_cuenta='".$id_tmp."'");
}
//actualiza haber
if (isset($_POST['action']) && $_POST['action'] == "actualizar_haber") {
$id_tmp=$_POST["id_item"];
$haber=$_POST["haber"];
$actualiza_detalle=mysqli_query($con, "UPDATE detalle_diario_tmp SET debe='0.00', haber='".$haber."' WHERE id_detalle_cuenta='".$id_tmp."'");
}

//actualiza detalle
if (isset($_POST['action']) && $_POST['action'] == "actualizar_item_asiento") {
$id_tmp=$_POST["id_item"];
$detalle_item=$_POST["detalle_item"];
$actualiza_detalle=mysqli_query($con, "UPDATE detalle_diario_tmp SET detalle_item='".$detalle_item."' WHERE id_detalle_cuenta='".$id_tmp."'");
}

if($action == 'cargar_detalle_diario'){
$codigo_unico=mysqli_real_escape_string($con,(strip_tags($_GET["codigo_unico"],ENT_QUOTES)));
$delete_tmp = mysqli_query($con, "DELETE FROM detalle_diario_tmp WHERE ruc_empresa = '".$ruc_empresa."' and id_usuario ='".	$id_usuario."'");
$query_tmp = mysqli_query($con, "INSERT INTO detalle_diario_tmp (id_detalle_cuenta, ruc_empresa, id_cuenta, codigo_cuenta, cuenta, debe, haber, detalle_item, id_usuario) 
SELECT null, '".$ruc_empresa."', det.id_cuenta, plan.codigo_cuenta, plan.nombre_cuenta, det.debe, det.haber, det.detalle_item, '".$id_usuario."' FROM detalle_diario_contable as det INNER JOIN plan_cuentas as plan ON plan.id_cuenta= det.id_cuenta WHERE det.ruc_empresa ='". $ruc_empresa ."' and det.codigo_unico='".$codigo_unico."'");
}

?>
<div class="panel panel-info" style="margin-bottom: -30px; overflow-y: scroll; height: 250px;" >
   <div class="table-responsive">
  <table class="table table-hover">
  <tr class="info">
	<th style ="padding: 2px;">Código</th>
	<th style ="padding: 2px;">Cuenta</th>
	<th class='text-center' style ="padding: 2px;">Debe</th>
	<th class='text-right' style ="padding: 2px;">Haber</th>
	<th class='text-center' style ="padding: 2px;">Detalle</th>
	<th class='text-right' style ="padding: 2px;">Eliminar</th>
</tr>
<?php
// PARA MOSTRAR LOS ITEMS 
	$subtotal_debe=0;
	$subtotal_haber=0;
	$diferencia_debe=null;
	$diferencia_haber=null;
	$sql=mysqli_query($con, "select * FROM detalle_diario_tmp WHERE id_usuario = '". $id_usuario ."' and ruc_empresa='".$ruc_empresa."' ");
	while ($row=mysqli_fetch_array($sql)){
			$id_tmp=$row["id_detalle_cuenta"];
			$id_cuenta=$row['id_cuenta'];
			$codigo_cuenta=$row['codigo_cuenta'];
			$cuenta=$row['cuenta'];
			$debe=number_format($row['debe'],2,'.','');
			$haber=number_format($row['haber'],2,'.','');
			$detalle=$row['detalle_item'];
		    $subtotal_debe+=$debe;
			$subtotal_haber+=$haber;
			$diferencia_debe=($subtotal_debe>$subtotal_haber)?0:($subtotal_haber-$subtotal_debe);
			$diferencia_haber=($subtotal_haber>$subtotal_debe)?0:($subtotal_debe-$subtotal_haber);
				?>
				<input type="hidden" id="detalle_original<?php echo $id_tmp;?>" value="<?php echo $detalle;?>">
				<input type="hidden" id="id_cuenta_modificar<?php echo $id_tmp;?>" value="<?php echo $id_cuenta;?>">
				<input type="hidden" id="cuenta_actual<?php echo $id_tmp;?>" value="<?php echo $cuenta;?>">
				<input type="hidden" id="codigo_actual<?php echo $id_tmp;?>" value="<?php echo $codigo_cuenta;?>">
				<input type="hidden" id="debe_actual<?php echo $id_tmp;?>" value="<?php echo $debe;?>">
				<input type="hidden" id="haber_actual<?php echo $id_tmp;?>" value="<?php echo $haber;?>">
				<tr>
					<td class='text-left col-sm-2' style ="padding: 2px;"><input style="height:25px;" type="text" class="form-control text-left" id="modificar_codigo_cuenta<?php echo $id_tmp;?>" onkeyup="buscar_cuenta_modificar('<?php echo $id_tmp;?>');" onchange="actualizar_cuenta_modificar('<?php echo $id_tmp;?>');" value="<?php echo $codigo_cuenta;?>"></td>
					<td class='text-left col-sm-4' style ="padding: 2px;"><input style="height:25px;" id="modificar_cuenta<?php echo $id_tmp;?>" class="form-control" value="<?php echo strtoupper($cuenta);?>" readonly></td>
					<td class='text-right' style ="padding: 2px;"><input style="height:25px;" type="text" class="form-control text-right" id="modificar_debe<?php echo $id_tmp;?>" onchange="modificar_debe('<?php echo $id_tmp;?>');" value="<?php echo $debe;?>"></td>
					<td class='text-right' style ="padding: 2px;"><input style="height:25px;" type="text" class="form-control text-right" id="modificar_haber<?php echo $id_tmp;?>" onchange="modificar_haber('<?php echo $id_tmp;?>');" value="<?php echo $haber;?>"></td>
					<td class="text-left col-sm-3" style ="padding: 2px;"><input type="text" style="height:25px;" class="form-control" id="detalle_asiento<?php echo $id_tmp;?>" onchange="modificar_detalle_directo('<?php echo $id_tmp;?>');" value="<?php echo strtoupper($detalle);?>"></td>
					<td class='text-right' style ="padding: 2px;">
					<a href="#" class='btn btn-danger btn-xs' onclick="eliminar_item_diario('<?php echo $id_tmp; ?>')" title ="Eliminar item"><i class="glyphicon glyphicon-trash"></i></a>
					</td>
				</tr>		
				<?php
			}
			?>
			<tr class="info">
				<input type="hidden" value="<?php echo number_format($subtotal_debe,2,'.','');?>" id="subtotal_debe">
				<input type="hidden" value="<?php echo number_format($subtotal_haber,2,'.','');?>" id="subtotal_haber">
				<td class='text-right' style ="padding: 2px;" colspan="2">Sumas: </td>
				<td class='text-right' style ="padding: 2px;"><?php echo number_format($subtotal_debe,2,'.','');?></td>
				<td class='text-right'style ="padding: 2px;" ><?php echo number_format($subtotal_haber,2,'.','');?></td>
				<td style ="padding: 2px;" colspan="2"></td>
			</tr>
		<tr class="info">
			<td class='text-right' style ="padding: 2px;" colspan="2">Diferencias: </td>
			<td class='text-right' style ="padding: 2px;"><?php echo number_format($diferencia_debe,2,'.','');?></td>
			<td class='text-right'style ="padding: 2px;" ><?php echo number_format($diferencia_haber,2,'.','');?></td>
			<td style ="padding: 2px;" colspan="2"></td>
		</tr>
			
</table>
	
</div>
</div>
