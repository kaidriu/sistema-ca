<?php
//error_reporting(E_ALL);
//ini_set("display_errors",2);
	include("../conexiones/conectalogin.php");
	require_once("../helpers/helpers.php");
	$con = conenta_login();

	session_start();
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];	
		
	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	
	if($action == 'verificar_firma'){
		if (!isset($ruc_empresa)) {
			$errors[] = "Vuelva a ingresar al sistema";
		 }else if (empty($_FILES["archivo"]["name"])) {
			$errors[] = "Seleccione un archivo";
		}else if (empty($_POST['clave_firma'])) {
			$errors[] = "Ingrese la contraseña de la firma electrónica";
		 }else if (!empty($_POST['clave_firma'])){
 
		 $clave_firma=$_POST["clave_firma"];

			 if (!empty($_FILES["archivo"]["name"])){			
			 $b = explode(".",$_FILES['archivo']['name']); //divide la cadena por el punto y lo guarda en un arreglo
			 $e = count($b); //calcula el número de elementos del arreglo b
			 $ext_file = $b[$e-1]; //captura la extensión del archivo.
			 $nombre_archivo_firma = nombre_archivo(10).".".$ext_file; //crea el path de destino del archivo
			 $archivo_firma = "../facturacion_electronica/firma_digital/".$nombre_archivo_firma;
 
			 $target_dir="../facturacion_electronica/firma_digital/";
			 $archivo_name = time()."_".basename($_FILES["archivo"]["name"]);
			 $target_file = $target_dir . $archivo_name;
			 $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			 $imageFileZise=$_FILES["archivo"]["size"];
 
				 if(($imageFileType != "p12") && $imageFileZise>0) {
				 $errors[]= "Lo sentimos, sólo se permiten archivos .p12".mysqli_error($con);
				 }else if(!move_uploaded_file($_FILES['archivo']['tmp_name'],$archivo_firma)){
				 $errors []= "Error al cargar, revise el tipo de archivo.".mysqli_error($con);	
				 }else{
 
					 
					 $fname = $archivo_firma;
					 $f = fopen($fname, "r"); 
					 $cert = fread($f, filesize($fname)); 
					 fclose($f); 
					 $datos = array();
					 if(!openssl_pkcs12_read($cert, $datos, $clave_firma)){
						 $errors[]="Error en contraseña de firma electrónica";
					 }else{
					 $datos = openssl_x509_parse($datos['cert'],0);
					 $datos_firma=array('fecha_desde'=>date('d-m-Y', $datos['validFrom_time_t']), 
										 'fecha_hasta'=>date('d-m-Y', $datos['validTo_time_t']),
										 'emitido_para'=>$datos['subject']['commonName'], 'emitido_por'=>$datos['subject']['organizationName']);

						?>
						<input type="hidden" id="fecha_vencimiento" value="<?php echo date('d-m-Y', $datos['validTo_time_t']); ?>">
						<div class="alert alert-success" role="alert">
								<button type="button" class="close" data-dismiss="alert">&times;</button>
								<?php
											echo "<b>Fecha Emisión:</b> ".date('d-m-Y', $datos['validFrom_time_t'])."</br>";
											echo "<b>Fecha vencimiento:</b> ".date('d-m-Y', $datos['validTo_time_t'])."</br>";
											echo "<b>Emitido para:</b> ".$datos['subject']['commonName']."</br>";
											echo "<b>Emitido por:</b> ".$datos['subject']['organizationName'];
									?>
						</div>
						<?php
							unlink($fname);
					}
 		 
				 }		
			 }else{
				 $errors []= "Seleccione un archivo de firma electrónica .p12".mysqli_error($con);
			 }
		 
		 }else {
			 $errors []= "Error desconocido.";
		 }
	 }//fin del action
	
	if($action == 'guarda_actualiza_emisor'){
	   if (!isset($ruc_empresa)) {
           $errors[] = "Vuelva a ingresar al sistema";
		}else if (empty($_POST['tipo_ambiente'])) {
           $errors[] = "Seleccione el tipo de ambiente";
		}else if (empty($_POST['tipo_emision'])) {
           $errors[] = "Seleccione el tipo de emisión";
        }else if (!empty($_POST['tipo_ambiente'])&& !empty($_POST['tipo_emision'])){

		$correo_remitente=mysqli_real_escape_string($con,(strip_tags($_POST["correo_remitente"],ENT_QUOTES)));
		$correo_pass=$_POST["correo_pass"];
		$puerto_mail=mysqli_real_escape_string($con,(strip_tags($_POST["correo_port"],ENT_QUOTES)));
		$asunto_mail=mysqli_real_escape_string($con,(strip_tags($_POST["correo_asunto"],ENT_QUOTES)));
		$host_mail=mysqli_real_escape_string($con,(strip_tags($_POST["correo_host"],ENT_QUOTES)));
		$ssl=mysqli_real_escape_string($con,(strip_tags($_POST["ssl"],ENT_QUOTES)));
		$tipo_ambiente=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_ambiente"],ENT_QUOTES)));
		$tipo_emision=mysqli_real_escape_string($con,(strip_tags($_POST["tipo_emision"],ENT_QUOTES)));
		$resol_ce=mysqli_real_escape_string($con,(strip_tags($_POST["resol_ce"],ENT_QUOTES)));
		$agente_retencion=mysqli_real_escape_string($con,(strip_tags($_POST["agente_retencion"],ENT_QUOTES)));
		$regimen_micro="NO";
		$regimen_negocio_popular="NO";
		$regimen_rimpe=mysqli_real_escape_string($con,(strip_tags($_POST["regimen_rimpe"],ENT_QUOTES)));
			
		//consultar si hay un registro de esta empresa para modificar o guardar nuevo
		$busca_empresa = mysqli_query($con,"SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ");
		$count = mysqli_num_rows($busca_empresa);
			if ( $count>0){
				//actualiza
				$query_update=mysqli_query($con, "UPDATE config_electronicos SET id_usuario='".$id_usuario."', correo_asunto='".$asunto_mail."', correo_host='".$host_mail."', correo_pass='".$correo_pass."',
				correo_port='".$puerto_mail."', correo_remitente='".$correo_remitente."', ssl_hab='".$ssl."',tipo_ambiente='".$tipo_ambiente."',
				tipo_emision='".$tipo_emision."', resol_cont = '".$resol_ce."', agente_ret = '".$agente_retencion."', regimen_micro = '".$regimen_micro."', negocio_popular='".$regimen_negocio_popular."', regimen_rimpe='".$regimen_rimpe."' WHERE mid(ruc_empresa,1,12) = '". substr($ruc_empresa,0,12) ."'");
				if ($query_update){
					echo "<script>
					$.notify('Los datos se actualizaron correctamente.','success');
					setTimeout(function (){location.href ='../modulos/config_docs_electronicos.php'}, 1000);
					</script>";	
				} else{
					$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
				}
			}else{
				//nuevo
				$query_guarda_confi_electronica=mysqli_query($con, "INSERT INTO config_electronicos VALUES (null, '".$ruc_empresa."','".$id_usuario."','0','0','".$asunto_mail."',
				'".$host_mail."','".$correo_pass."','".$puerto_mail."','".$correo_remitente."','".$ssl."','".$tipo_ambiente."','".$tipo_emision."','".$resol_ce."','0','".$agente_retencion."','".$regimen_micro."', '".$regimen_negocio_popular."', '".$regimen_rimpe."')");
				if ($query_guarda_confi_electronica){
					echo "<script>
					$.notify('Los datos se guardaron correctamente.','success');
					setTimeout(function (){location.href ='../modulos/config_docs_electronicos.php'}, 1000);
					</script>";
				} else{
					$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
				}
			}
		
		
		}else {
			$errors []= "Error desconocido.";
		}
	}//fin del action
	
	
	if($action == 'guarda_actualiza_firma'){
	   if (!isset($ruc_empresa)) {
           $errors[] = "Vuelva a ingresar al sistema";
		}else if (empty($_POST['clave_firma'])) {
           $errors[] = "Ingrese la contraseña de la firma electrónica";
		}else if (empty($_POST['vence_firma'])) {
           $errors[] = "Ingrese la fecha de vencimiento de la firma digital";
		}else if (!date($_POST['vence_firma'])) {
           $errors[] = "Ingrese fecha de vencimiento correcta dd/mm/aaaa";
        }else if (!empty($_POST['clave_firma']) && !empty($_POST['vence_firma'])){

		$vence_firma=date('Y-m-d H:i:s', strtotime($_POST['vence_firma']));
		$clave_firma=$_POST["clave_firma"];
		
		$busca_empresa = mysqli_query($con,"SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ");
		$count = mysqli_num_rows($busca_empresa);
		$nombre_archivo=mysqli_fetch_array($busca_empresa);
		
		if ($count==0){
		$errors[]= "Primero debe registrar la información de emisor en la pestaña de arriba.".mysqli_error($con);
		}else{
			if (!empty($_FILES["archivo"]["name"])){			
			$b = explode(".",$_FILES['archivo']['name']); //divide la cadena por el punto y lo guarda en un arreglo
			$e = count($b); //calcula el número de elementos del arreglo b
			$ext_file = $b[$e-1]; //captura la extensión del archivo.
			$nombre_archivo_firma = nombre_archivo(10).".".$ext_file; //crea el path de destino del archivo
			$archivo_firma = "../facturacion_electronica/firma_digital/".$nombre_archivo_firma;

			$target_dir="../facturacion_electronica/firma_digital/";
			$archivo_name = time()."_".basename($_FILES["archivo"]["name"]);
			$target_file = $target_dir . $archivo_name;
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			$imageFileZise=$_FILES["archivo"]["size"];

				if(($imageFileType != "p12") && $imageFileZise>0) {
				$errors[]= "Lo sentimos, sólo se permiten archivos .p12".mysqli_error($con);
				}else if(!move_uploaded_file($_FILES['archivo']['tmp_name'],$archivo_firma)){
				$errors []= "Error al cargar, revise el tipo de archivo.".mysqli_error($con);	
				}else{
				
					$ftp_server = "64.225.69.65";
					$ftp_user_name = "char";
					$ftp_user_pass = "CmGr1980";

					$conn_id = ftp_connect($ftp_server);
					if (@ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)) {
					ftp_pasv($conn_id, true);
					$local_file=$archivo_firma;
					$server_file="/ftp_documentos/firma_digital/".$nombre_archivo_firma;

					if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY)) {
						ftp_chmod($conn_id, 0644, $server_file);
						
		$query_update = mysqli_query($con, "UPDATE config_electronicos SET id_usuario='".$id_usuario."', archivo_firma='".$nombre_archivo_firma."',
		pass_firma='".$clave_firma."', fecha_fin_firma ='".$vence_firma."' WHERE mid(ruc_empresa,1,12) = '". substr($ruc_empresa,0,12) ."'");
		if ($query_update){
		echo "<script>
		$.notify('La firma se actualizó correctamente.','success');
		setTimeout(function (){location.href ='../modulos/config_docs_electronicos.php'}, 1000);
		</script>";
		} else{
			$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
		}
	
						}else{
						echo "<script>
						$.notify('La firma no se actualizó, vuelva a intentarlo.','error');
						</script>";
						}

					}else{
						echo "<script>
						$.notify('No hay conexion con el servidor ftp.','error');
						</script>";
					}

					ftp_close($conn_id);	
		
		$archivo_eliminado ="../facturacion_electronica/firma_digital/".$nombre_archivo['archivo_firma'];
		unlink($archivo_eliminado);
		
				}		
			}else{
				$errors []= "Seleccione un archivo de firma electrónica .p12".mysqli_error($con);
			}
		}
		}else {
			$errors []= "Error desconocido.";
		}
	}//fin del action de actualizar firma
		

		if (isset($errors)){
			
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Error! </strong> 
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

function nombre_archivo($n){
	$a = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9","0");
	//$a = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0");
	$name = NULL;
	$e = count($a) - 1; //cuenta el número de elementos del arreglo y le resta 1
	for($i=1;$i<=$n;$i++){
		$m = rand(0,$e); //devuelve un número randómico entre 0 y el número de elementos
		$name .= $a[$m];
	}
	return $name;
}
?>