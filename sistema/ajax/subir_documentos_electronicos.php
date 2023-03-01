<?php
include("../conexiones/conectalogin.php");
include("../clases/lee_xml.php");
require_once("../helpers/helpers.php");
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
						//$rides_sri=new rides_sri();
						$arreglo = file($dir_documento);
						$array_datos=array();
						  $i = 0;
						  foreach($arreglo as $fila){
							if($i>1){
								$columna = explode("\t",$fila);
								if(strlen($fila)>15){
								$array_datos[] = array('clave_acceso'=>$columna[9], 'ruc_emisor'=>$columna[2], 'ruc_receptor'=>$columna[8]);
								}
							}
							$i++;
						  }
				  
					  $claves_consultadas = claves_por_consultar($con, $ruc_empresa, $array_datos, $id_usuario);
						echo $claves_consultadas;
 
						} else {	
						$errors []= "Ha ocurrido un error, por favor inténtelo de nuevooo.<br>";
					}
					closedir($dir); //Cerramos el directorio de destino
			}else{
				$errors []= "El archivo $filename no es txt, por lo tanto no se procesó. <br>";
			}
		}else{
				$errors []= "Seleccione el archivo txt descargado del SRI para cargar los documentos.";
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
			$object_xml= $rides_sri->lee_ride($clave_acceso);

			if ($object_xml){
			$tipo_documento = $object_xml->infoTributaria->codDoc;
			$ruc_emisor = substr($object_xml->infoTributaria->ruc,0,10)."001";
			
			switch ($tipo_documento) {
					case "01":
						$ruc_comprador = substr($object_xml->infoFactura->identificacionComprador,0,10)."001";
						break;
					case "03":
						$ruc_comprador = $object_xml->infoLiquidacionCompra->identificacionProveedor;
						break;
					case "04":
						$ruc_comprador =substr($object_xml->infoNotaCredito->identificacionComprador,0,10)."001";
						break;
					case "05":
						$ruc_comprador =substr($object_xml->infoNotaDebito->identificacionComprador,0,10)."001";
						break;
					case "07":
						$ruc_comprador =substr($object_xml->infoCompRetencion->identificacionSujetoRetenido,0,10)."001";
						break;
						}
					
			$array_datos[] = array('clave_acceso'=>$clave_acceso, 'ruc_emisor'=>$ruc_emisor, 'ruc_receptor'=>$ruc_comprador);
			

			$claves_consultadas = claves_por_consultar($con, $ruc_empresa, $array_datos, $id_usuario);
			echo $claves_consultadas;
			}else{
				echo "No hay respuesta del SRI para el documento solicitado, intente de nuevo.";
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
			
	function claves_por_consultar($con, $ruc_empresa, $array_datos, $id_usuario){
		$rides_sri=new rides_sri();
		$total_registros=0;
		$claves_a_consultar=array();
			
		foreach ($array_datos as $value) {
		$busca_clave_acceso_compras = mysqli_query($con, "SELECT * FROM encabezado_compra WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value['clave_acceso']."'");
		$contador_clave_acceso_compra = mysqli_num_rows($busca_clave_acceso_compras);

		if ($contador_clave_acceso_compra == 0){
			$busca_clave_acceso_rv = mysqli_query($con, "SELECT * FROM encabezado_retencion_venta WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value['clave_acceso']."'");
			$contador_clave_acceso_rv = mysqli_num_rows($busca_clave_acceso_rv);							
			
				if ($contador_clave_acceso_rv == 0){	
				$busca_clave_acceso_venta = mysqli_query($con, "SELECT * FROM encabezado_factura WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value['clave_acceso']."'");
				$contador_clave_acceso_venta = mysqli_num_rows($busca_clave_acceso_venta);							
					//esto es para cuando mi empresa emite una factura de venta y la quiero registrar como compra de mi misma empresa
					if ($contador_clave_acceso_venta>0 && ($value['ruc_emisor']==$value['ruc_receptor']) && (substr($ruc_empresa,0,12)==substr($value['ruc_receptor'],0,12))){
					$contador_clave_acceso_venta=0;
					}
						if ($contador_clave_acceso_venta == 0){
						$busca_clave_acceso_rc = mysqli_query($con, "SELECT * FROM encabezado_retencion WHERE mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and aut_sri='".$value['clave_acceso']."'");
						$contador_clave_acceso_rc = mysqli_num_rows($busca_clave_acceso_rc);
							if ($contador_clave_acceso_rc == 0){
								$claves_a_consultar[]=$value['clave_acceso'];
							}						
						
						}
				}
		}
	  }
	  
	  //para contar las filas del archivo
				if (is_array($array_datos)){
					$total_claves=count($array_datos);									
				}
	  
					$procesados_ahora=0;
					$documento_resgistrado=array();
					foreach ($claves_a_consultar as $clave) {								
						$documento_resgistrado[]= $rides_sri->lee_clave_acceso($clave, $ruc_empresa, $id_usuario, $con);
							$procesados_ahora++;
					}
						?>
					<td>
					<span class="label label-info">Documentos en el archivos: <?php echo $total_claves; ?></span>
					<span class="label label-success">Documentos registrados ahora: <?php echo $procesados_ahora; ?></span>
					<span class="label label-warning">Documentos registrados anteriormente: <?php echo ($total_claves-$procesados_ahora); ?></span><br>
					<?php
					foreach ($documento_resgistrado as $message) {
						?>
						<span><?php echo ($message); ?></span>
						<?php
						}
					?>
					</td>
						<?php	
						
	}
?>