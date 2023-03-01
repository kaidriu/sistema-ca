<?php
include("../conexiones/conectalogin.php");
include("../clases/lee_xml.php");
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$con = conenta_login();

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

//boton de cargar archivo con claves de accesso para varias documentos
if($action == 'archivo_documentos_electronicos'){
    foreach($_FILES["archivo"]['tmp_name'] as $key => $tmp_name){
		if($_FILES["archivo"]["name"][$key]){
		$filename = $_FILES["archivo"]["name"][$key]; //Obtenemos el nombre original del archivo
		$source = $_FILES["archivo"]["tmp_name"][$key]; //Obtenemos un nombre temporal del archivo
		$directorio = '../docs_temp/'; //Declaramos un  variable con la ruta donde guardaremos los archivos
		//Validamos si la ruta de destino existe, en caso de no existir la creamos
		if(!file_exists($directorio)){
			mkdir($directorio, 0777) or die("No se puede crear el directorio de extracci&oacute;n");	
		}
		
		//para obtener el tipo de archivo
		$target_dir="../docs_temp/";
		$archivo_name = time()."_".basename($_FILES["archivo"]["name"][$key]);
		$target_file = $target_dir . $archivo_name;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		
			if($imageFileType == "txt") {
					$dir=opendir($directorio); //Abrimos el directorio de destino
					$target_path = $directorio.'/documentos.txt'; //Indicamos la ruta de destino, así como el nombre del archivo

					//Movemos y validamos que el archivo se haya cargado correctamente
					//El primer campo es el origen y el segundo el destino
					if(move_uploaded_file($source, $target_path)) {	
						$dir_documento = "../docs_temp/documentos.txt";
						$rides_sri=new rides_sri();
						
						$arreglo = file($dir_documento);

						  $clave = array();
						  $i = 0;
						  foreach($arreglo as $fila){
							if($i>1){
								$columna = explode("\t",$fila);
								if(strlen($fila)>15){
								//$clave[$columna[9]]= $columna[9];
								$claves[]= $columna[9];
									if (is_array($claves)){
									$total_claves=count($claves);									
									}
								}
							}
							$i++;
						  }

					  if (is_array($claves)) {
						  $claves_a_consultar=array();
						  foreach ($claves as $key => $value) {
							$busca_clave_acceso_compras = mysqli_query($con, "SELECT * FROM encabezado_compra WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value."'");
							$contador_clave_acceso_compra = mysqli_num_rows($busca_clave_acceso_compras);
							
							$busca_clave_acceso_rv = mysqli_query($con, "SELECT * FROM encabezado_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value."'");
							$contador_clave_acceso_rv = mysqli_num_rows($busca_clave_acceso_rv);							
							
							$busca_clave_acceso_venta = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value."'");
							$contador_clave_acceso_venta = mysqli_num_rows($busca_clave_acceso_venta);							
							
							$busca_clave_acceso_rc = mysqli_query($con, "SELECT * FROM encabezado_retencion WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value."'");
							$contador_clave_acceso_rc = mysqli_num_rows($busca_clave_acceso_rc);
							
							$total_registros=$contador_clave_acceso_compra+$contador_clave_acceso_rv+$contador_clave_acceso_venta+$contador_clave_acceso_rc;
							
								if ($total_registros==0){
									$claves_a_consultar[]=$value;
								}
						  }
					  }
					 
					 $procesados_ahora=0;
					foreach ($claves_a_consultar as $clave) {								
						echo $rides_sri->lee_clave_acceso($clave, $ruc_empresa, $id_usuario, $con);
						$procesados_ahora=$procesados_ahora+1;
					}						
						   							  
							echo "Documentos en archivo: " . ($total_claves) . "<br>";
							echo "Documentos registrados ahora: " . ($procesados_ahora). "<br>";
							echo "Documentos registrados anteriormente: ". ($total_claves-$procesados_ahora) . "<br>";
							
							  
							  
						} else {	
						$errors []= "Ha ocurrido un error, por favor inténtelo de nuevo.<br>";
					}
					closedir($dir); //Cerramos el directorio de destino
			}else{
				$errors []= "El archivo $filename no es txt, por lo tanto no se procesó. <br>";
			}
		}else{
				$errors []= "Seleccione el archivo txt descargado del SRI para subir los documentos.";
		}
	}
}

//boton de cargar una clave de accesso 
if($action == 'clave_compra_individual'){
		if (empty($_GET['clave_acceso'])) {
           $errors []= "Ingrese una clave de acceso";
        }  else if (!empty($_GET['clave_acceso'])){
		// escaping, additionally removing everything that could be (html/javascript-) code
			$clave_acceso=mysqli_real_escape_string($con,(strip_tags($_GET["clave_acceso"],ENT_QUOTES)));	
			$rides_sri=new rides_sri();

			$busca_clave_acceso_compras = mysqli_query($con, "SELECT * FROM encabezado_compra WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$clave_acceso."'");
			$contador_clave_acceso_compra = mysqli_num_rows($busca_clave_acceso_compras);
			
			$busca_clave_acceso_rv = mysqli_query($con, "SELECT * FROM encabezado_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$clave_acceso."'");
			$contador_clave_acceso_rv = mysqli_num_rows($busca_clave_acceso_rv);
			
			$busca_clave_acceso_venta = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$clave_acceso."'");
			$contador_clave_acceso_venta = mysqli_num_rows($busca_clave_acceso_venta);
		
			$busca_clave_acceso_rc = mysqli_query($con, "SELECT * FROM encabezado_retencion WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$clave_acceso."'");
			$contador_clave_acceso_rc = mysqli_num_rows($busca_clave_acceso_rc);
				
			$total_registros=$contador_clave_acceso_compra+$contador_clave_acceso_rv+$contador_clave_acceso_venta+$contador_clave_acceso_rc;
			
			if ($total_registros==0){
				echo $rides_sri->lee_clave_acceso($clave_acceso, $ruc_empresa, $id_usuario, $con);
			}else{
				echo "El documento ya ha sido registrado con anterioridad.";
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