<?php
		include("../conexiones/conectalogin.php");
		//include("../conexiones/conecta_ftp.php");
		$con = conenta_login();
		//$con_ftp = conecta_ftp();
	
	if (empty($_POST['ruc_empresa'])) {
           $errors[] = "Vuelva a ingresar al sistema";
		}else if (empty($_POST['serie_sucursal'])) {
           $errors[] = "Seleccione una sucursal de la empresa.";
		}else if (empty($_POST['moneda_sucursal'])) {
           $errors[] = "Seleccione un tipo de moneda";
		}else if (empty($_POST['decimales_cantidad'])) {
           $errors[] = "Seleccione cuantos decimales quiere aplicar en la cantidad";
		}else if (empty($_POST['decimales_documento'])) {
           $errors[] = "Seleccione cuantos decimales quiere aplicar en el precio";
		}else if (empty($_POST['dir_sucursal'])) {
           $errors[] = "Ingrese la dirección de la sucursal seleccionada";
		}else if (empty($_POST['nombre_sucursal'])) {
           $errors[] = "Ingrese nombre de la sucursal seleccionada";
		}else if (empty($_POST['inicial_factura'])) {
           $errors[] = "Ingrese el número inicial para las facturas electrónicas";
		}else if (!is_numeric($_POST['inicial_factura'])) {
           $errors[] = "Ingrese el número inicial para las facturas electrónicas";
		}else if (!is_numeric($_POST['inicial_nc'])) {
           $errors[] = "Ingrese el número inicial para las notas de crédito electrónicas";
		}else if (!is_numeric($_POST['inicial_nd'])) {
           $errors[] = "Ingrese el número inicial para las notas de débito electrónicas";
		}else if (!is_numeric($_POST['inicial_gr'])) {
           $errors[] = "Ingrese el número inicial para las guías de remisión electrónicas";
		}else if (!is_numeric($_POST['inicial_cr'])) {
           $errors[] = "Ingrese el número inicial para los comprobantes de retención electrónicas";
		}else if (!is_numeric($_POST['inicial_liq'])) {
           $errors[] = "Ingrese el número inicial para las liquidaciones de compras electrónicas";
		}else if (!is_numeric($_POST['inicial_proforma'])) {
			$errors[] = "Ingrese el número inicial para las proformas";
        }else if (!empty($_POST['ruc_empresa'])&& !empty($_POST['serie_sucursal'])
		&& !empty($_POST['moneda_sucursal'])&& !empty($_POST['dir_sucursal']) && !empty($_POST['nombre_sucursal']) && !empty($_POST['decimales_documento'])
		&& !empty($_POST['inicial_factura']) && !empty($_POST['decimales_cantidad']) && !empty($_POST['inicial_liq']) && !empty($_POST['inicial_proforma'])){

		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		$serie_sucursal=mysqli_real_escape_string($con,(strip_tags($_POST["serie_sucursal"],ENT_QUOTES)));
		$ruc_empresa=mysqli_real_escape_string($con,(strip_tags($_POST["ruc_empresa"],ENT_QUOTES)));
		$moneda_sucursal=mysqli_real_escape_string($con,(strip_tags($_POST["moneda_sucursal"],ENT_QUOTES)));
		$nombre_sucursal=mysqli_real_escape_string($con,(strip_tags($_POST["nombre_sucursal"],ENT_QUOTES)));
		$dir_sucursal=mysqli_real_escape_string($con,(strip_tags($_POST["dir_sucursal"],ENT_QUOTES)));
		$inicial_factura=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_factura"],ENT_QUOTES)));
		$inicial_nc=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_nc"],ENT_QUOTES)));
		$inicial_nd=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_nd"],ENT_QUOTES)));
		$inicial_gr=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_gr"],ENT_QUOTES)));
		$inicial_cr=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_cr"],ENT_QUOTES)));
		$inicial_liq=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_liq"],ENT_QUOTES)));
		$inicial_proforma=mysqli_real_escape_string($con,(strip_tags($_POST["inicial_proforma"],ENT_QUOTES)));
		$decimales=mysqli_real_escape_string($con,(strip_tags($_POST["decimales_documento"],ENT_QUOTES)));
		$decimales_cantidad=mysqli_real_escape_string($con,(strip_tags($_POST["decimales_cantidad"],ENT_QUOTES)));
		$impuestos_recibo=mysqli_real_escape_string($con,(strip_tags($_POST["impuestos_recibo"],ENT_QUOTES)));

		//consultar si hay un registro de esta empresa para modificar o guardar nuevo
			$busca_sucursal = "SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' and serie='".$serie_sucursal."' ";
			$result = $con->query($busca_sucursal);
			
			
		if (!empty($_FILES["logo_sucursal"]["name"])){			
		$b = explode(".",$_FILES['logo_sucursal']['name']); //divide la cadena por el punto y lo guarda en un arreglo
		$e = count($b); //calcula el número de elementos del arreglo b
		$ext_file = $b[$e-1]; //captura la extensión del archivo.
		$nombre_logo_sucursal = nombre_archivo(10) . "."  . $ext_file; //crea el path de destino del archivo
		$logo_sucursal = "../logos_empresas/".$nombre_logo_sucursal;
		$target_dir="../logos_empresas/";
		$archivo_name = time()."_".basename($_FILES["logo_sucursal"]["name"]);
		$target_file = $target_dir . $archivo_name;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		$imageFileZise=$_FILES["logo_sucursal"]["size"];
	
		if(($imageFileType != "jpg" && $imageFileType != "jpeg") and $imageFileZise>0) {
		$errors[]= "Lo sentimos, sólo se permiten archivos JPG , JPEG".mysqli_error($con);
		}else if ($imageFileZise > 1001048576) {//1048576 byte=1MB
			$errors[]= "Lo sentimos, pero el logo es demasiado grande. Selecciona un logo de menos de 1MB".mysqli_error($con);
		}else if(!move_uploaded_file($_FILES['logo_sucursal']['tmp_name'],$logo_sucursal)){
		$errors []= "Error al cargar el logo, revise el tipo de archivo.".mysqli_error($con);	
		}else{
			
			$nombre_archivo = mysqli_fetch_array($result);
			$nombre_logo= $nombre_archivo['logo_sucursal'];
			$logo_eliminado ="../logos_empresas/".$nombre_logo;

			if ($nombre_logo !=null){
					unlink($logo_eliminado);
			}
			
			$count = mysqli_num_rows($result);
			if ( $count>0){

				
				$ftp_server = "64.225.69.65";
				$ftp_user_name = "char";
				$ftp_user_pass = "CmGr1980";

				$conn_id = ftp_connect($ftp_server);
				if (@ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)) {
				ftp_pasv($conn_id, true);
				$local_file=$logo_sucursal;
				$server_file="/ftp_documentos/logos_empresa/".$nombre_logo_sucursal;

				if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY)) {
					ftp_chmod($conn_id, 0644, $server_file);
				
						$query_update= mysqli_query($con,"UPDATE sucursales SET direccion_sucursal='".$dir_sucursal."',
						 moneda_sucursal='".$moneda_sucursal."',logo_sucursal='".$nombre_logo_sucursal."', 
						 inicial_factura='".$inicial_factura."', inicial_nc='".$inicial_nc."',	
						 inicial_nd='".$inicial_nd."', inicial_gr='".$inicial_gr."', 
						 inicial_cr='".$inicial_cr."', decimal_doc ='".$decimales."',
						 nombre_sucursal='".$nombre_sucursal."', decimal_cant= '".$decimales_cantidad."', 
						 inicial_liq= '".$inicial_liq."', inicial_proforma= '".$inicial_proforma."', 
						 impuestos_recibo='".$impuestos_recibo."' 
						 WHERE ruc_empresa='".$ruc_empresa."' and serie = '".$serie_sucursal."' ");
						if ($query_update){
						echo "<script>
						$.notify('Los datos se actualizaron correctamente.','success');
						setTimeout(function (){location.href ='../modulos/config_docs_electronicos.php'}, 1000);
						</script>";
						} else{
							$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
						}
					
						
					}else{
					echo "<script>
					$.notify('El logo no se actualizó, vuelva a intentarlo.','error');
					</script>";
					}
					

				}else{
					echo "<script>
					$.notify('No hay conexion con el servidor ftp.','error');
					</script>";
				}

				ftp_close($conn_id);
				
									
			}
		}
		}else{		
			$count = mysqli_num_rows($result);
			if ( $count>0){
					$sql="UPDATE sucursales SET direccion_sucursal='".$dir_sucursal."', moneda_sucursal='".$moneda_sucursal."', inicial_factura='".$inicial_factura."', inicial_nc='".$inicial_nc."',
					inicial_nd='".$inicial_nd."', inicial_gr='".$inicial_gr."', inicial_cr='".$inicial_cr."', 
					decimal_doc ='".$decimales."',nombre_sucursal='".$nombre_sucursal."', 
					decimal_cant= '".$decimales_cantidad."', inicial_liq= '".$inicial_liq."', 
					inicial_proforma= '".$inicial_proforma."', impuestos_recibo='".$impuestos_recibo."'
					 WHERE ruc_empresa='".$ruc_empresa."' and serie = '".$serie_sucursal."' ";
					$query_update = mysqli_query($con,$sql);
					if ($query_update){
						echo "<script>
						$.notify('Los datos se actualizaron correctamente.','success');
						setTimeout(function (){location.href ='../modulos/config_docs_electronicos.php'}, 1000);
						</script>";
					} else{
						$errors []= "Lo siento algo ha salido mal intenta nuevamente.".mysqli_error($con);
					}
			}
		}
		}else {
			$errors []= "Error desconocido.";
		}
		

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
	$name = NULL;
	$e = count($a) - 1; //cuenta el número de elementos del arreglo y le resta 1
	for($i=1;$i<=$n;$i++){
		$m = rand(0,$e); //devuelve un número randómico entre 0 y el número de elementos
		$name .= $a[$m];
	}
	return $name;
}
?>