<?php
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
if(($action == 'leer_ruc') && isset($_GET['numero']) && (!empty($_GET['numero'] ))){

$consulta_ruc= new consultaRuc();
$matches = $consulta_ruc->info_ruc($_GET['numero']);
$detalles=$matches;

foreach($matches as $fila){
		$columna = explode("\t",$fila);
		$info_numero_ruc[$columna[0]]= $columna[0];
		$info_razon_social[$columna[1]]= $columna[1];
		$info_nombre_comercial[$columna[2]]= $columna[2];
		$info_estado_contribuyente[$columna[3]]= $columna[3];
		$info_clase_contribuyente[$columna[4]]= $columna[4];
		$info_obligado[$columna[9]]= $columna[9];
		$info_tipo_contribuyente[$columna[10]]= $columna[10];
		$info_nombre_fantasia[$columna[12]]= $columna[12];
		$info_dir[$columna[13]]= $columna[13];				
		$info_dir[$columna[14]]= $columna[14];
		$info_dir[$columna[15]]= $columna[15];
		$info_estado_establecimiento[$columna[16]]= $columna[16];
		$info_provincia[$columna[17]]= $columna[17];
		$info_canton[$columna[18]]= $columna[18];
		$info_parroquia[$columna[19]]= $columna[19];
		$info_actividad[$columna[21]]= $columna[21];
	  }

	//nombre
			  if (isset($info_razon_social)){
				  if (is_array($info_razon_social)) {
					foreach ($info_razon_social as $razon_social => $value) {	
						$messages[]= "Razón social: "."<strong>".utf8_encode($razon_social)."</strong><br>";
					}
				  }
			  }
//nombre comercial
			  if (isset($info_nombre_comercial)){
				  if (is_array($info_nombre_comercial)) {
					foreach ($info_nombre_comercial as $nombre_comercial => $value) {	
						$messages[]= "Nombre comercial: "."<strong>".utf8_encode($nombre_comercial)."</strong><br>";
					}
				  }
			  }
//ruc
			  if (isset($info_numero_ruc)){
				  if (is_array($info_numero_ruc)) {
					foreach ($info_numero_ruc as $numero_ruc => $value) {	
						$messages[]= "Ruc: "."<strong>".utf8_encode($numero_ruc)."</strong><br>";
					}
				  }
			  }
//estado contribuyente
			  if (isset($info_estado_contribuyente)){
				  if (is_array($info_estado_contribuyente)) {
					foreach ($info_estado_contribuyente as $estado_contribuyente => $value) {	
						$messages[]= "Estado contribuyente: "."<strong>".utf8_encode($estado_contribuyente)."</strong><br>";
					}
				  }
			  }
			  
//clase
		if (isset($info_clase_contribuyente)){
			  if (is_array($info_clase_contribuyente)) {
				foreach ($info_clase_contribuyente as $clase_contribuyente => $value) {	
					$messages[]= "Clase contribuyente: "."<strong>".$clase_contribuyente."</strong><br>";
				}
			  }	
		}
//obligado
if (isset($info_obligado)){
			  if (is_array($info_obligado)) {
				foreach ($info_obligado as $obligado => $value) {
 if ($obligado=="S"){
	 $obligado="SI";
 }else{
	$obligado="NO"; 
 }
				
					$messages[]= "Contabilidad: "."<strong>".$obligado."</strong><br>";
				}
			  }	
}
//tipo
if (isset($info_tipo_contribuyente)){
			  if (is_array($info_tipo_contribuyente)) {
				foreach ($info_tipo_contribuyente as $tipo_contribuyente => $value) {	
					$messages[]= "Tipo contribuyente: "."<strong>".$tipo_contribuyente."</strong><br>";
				}
			  }	
}			  

//actividad
	if (isset($info_actividad)){
				  if (is_array($info_actividad)) {
					foreach ($info_actividad as $actividad => $value) {	
						$messages[]= "Actividad: "."<strong>".utf8_encode($actividad)."</strong><br>";
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
						<?php
							foreach ($messages as $message) {
									echo $message;
								}
							?>
				</div>
				<?php
			}
		
		
		
?>
<div class="panel panel-info">
<div class="table-responsive">
 <table class="table">
	<tr  class="info">
		<th>No.</th>
		<th>Dirección establecimiento</th>
		<th>Provincia/Cantón/Parroquia</th>
		<th>Nombre de fantasía</th>
		<th>Estado</th>
		<th>Est.</th>
	</tr>
<?php
$numero=1;
					
foreach($detalles as $fila){
		$columna = explode("\t",$fila);
      if (($info[$columna[16]]= $columna[16])=="ABI"){
		  $est_est="ABIERTO";
		  $label_class='label-success';
	  }
	  if (($info[$columna[16]]= $columna[16])=="CER"){
		  $est_est="CERRADO";
		  $label_class='label-danger';
	  }
	  ?>
		<tr>
			<td><?php echo $numero;?></td>		
			<td><?php echo utf8_encode($info[$columna[13]]= $columna[13] ." ". $info[$columna[14]]= $columna[14] ." Y ". $info[$columna[15]]= $columna[15]);?></td>
			<td><?php echo utf8_encode($info[$columna[17]]= $columna[17] ." / ". $info[$columna[18]]= $columna[18] ." / ". $info[$columna[19]]= $columna[19]);?></td>				
			<td><?php echo utf8_encode($info[$columna[12]]= $columna[12]);?></td>
			<td><span class="label <?php echo $label_class;?>"><?php echo $est_est; ?></span></td>
			<td><?php echo utf8_encode($info[$columna[11]]= $columna[11]);?></td>			
		</tr>
		<?php
	  $numero++;
	  }
	  
	 ?>
</table>
</div>
</div>
<?php	 

}else{
	?>
	<div class="alert alert-danger" role="alert">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>Error!</strong> Ingrese dato para buscar.
	</div>
	<?php
}


class consultaRuc{

	public function info_ruc($dato){
			$info = array();
			$provincias=array('AZUAY','BOLIVAR','CANAR','CARCHI','CHIMBORAZO','COTOPAXI','EL_ORO','ESMERALDAS','GALAPAGOS','GUAYAS','IMBABURA','LOJA','LOS_RIOS',
			'MANABI','MORONA_SANTIAGO','NAPO','ORELLANA','PASTAZA','PICHINCHA','SANTA_ELENA','SANTO_DOMINGO','SUCUMBIOS','TUNGURAHUA','ZAMORA_CHINCHIPE');
			
			for ( $i=0 ; $i<count($provincias) ; $i++ ){
					$handle = @fopen("../ruc_ecuador/".$provincias[$i].".txt", "r");
					if ($handle){
						while (!feof($handle)){
							$buffer = fgets($handle);
							if(strpos($buffer, $dato) !== FALSE)
								$info[] = $buffer;			
						}
						fclose($handle);
					}
			
				}
					  return $info;
		}

}

?>