<?php
include("../conexiones/conectalogin.php");
		$con = conenta_login();
if (isset($_POST['id_documento'])){
		$id_documento=mysqli_real_escape_string($con,(strip_tags($_POST["id_documento"],ENT_QUOTES)));
		$busca_sustento_tributario = "SELECT * FROM sustento_tributario order by nombre_sustento asc ";
		$resultado_sustento = $con->query($busca_sustento_tributario);
		$count= mysqli_num_rows($resultado_sustento);
		
		?>							
		<option value="">Seleccione sustento tributario</option>
		<?php
		while ($row_sustento_tributario = mysqli_fetch_array($resultado_sustento)){
			$tipo_comprobante = $row_sustento_tributario['tipo_comprobante'];
			$codigo_sustento = $row_sustento_tributario['codigo_sustento'];
			$nombre_sustento = $row_sustento_tributario['nombre_sustento'];
			$array_fila = explode(",",$tipo_comprobante);//traigo los datos de cada fila ya sin comas a un array
			$contador_fila=count($array_fila);//cuento cuantos datos hay en cada fila

			for ($i = 0; $i < $contador_fila; $i++){
				if ($array_fila[$i]==intval($id_documento)){
					?>
					<option value="<?php echo $codigo_sustento ?>"><?php echo $nombre_sustento ?></option>
					<?php
				}
			
			}
		}
}
?>