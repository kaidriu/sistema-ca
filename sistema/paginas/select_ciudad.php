<?php
include("../conexiones/conectalogin.php");
$codigo_provincia = $_POST['codigo_provincia'];
$conexion = conenta_login();
$res = mysqli_query($conexion, "SELECT * FROM ciudad where cod_prov = '".$codigo_provincia."';");
?>							
<option value="0">Seleccione una ciudad</option>
<?php
while($p = mysqli_fetch_assoc($res)){
?>
<option value="<?php echo $p['codigo'] ?>"><?php echo $p['nombre'] ?> </option>
<?php
}
?>
