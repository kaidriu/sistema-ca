<?php
include("../conexiones/conectalogin.php");
require "../excel/lib/PHPExcel/PHPExcel/IOFactory.php";
include("../validadores/generador_codigo_unico.php");
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
$con = conenta_login();
$codigo_unico=codigo_unico(20);

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

//boton de cargar archivo 
if($action == 'archivo_excel_plan_de_cuentas'){

$nombre_archivo=$_FILES['archivo']['name'];
$archivo_guardado=$_FILES['archivo']['tmp_name'];


$directorio = '../docs_temp/'; //Declaramos un  variable con la ruta donde guardaremos los archivos
$dir=opendir($directorio); //Abrimos el directorio de destino
$target_path = $directorio.'/plancuentas.xlsx';

$imageFileType = pathinfo($nombre_archivo,PATHINFO_EXTENSION);

if($imageFileType == "xlsx") {

	if(move_uploaded_file($archivo_guardado, $target_path)){
		$objPHPExcel = PHPExcel_IOFactory::load('../docs_temp/plancuentas.xlsx');
		$objPHPExcel->setActiveSheetIndex(0);
		$numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
		?>
		<div class="panel panel-info">
			<div class="table-responsive">
			  <table class="table table-hover">
				<tr  class="info">
					<th>Código</th>
					<th>Cuenta</th>
					<th>Nivel</th>
					<th>Código SRI</th>
					<th>Código Supercias</th>
					<th>Observación</th>
				</tr>
				<?php
				$codigos_cuentas=array();
				for ($c=1; $c<=$numRows; $c++){
					$codigos_cuentas[]=$objPHPExcel->getActiveSheet()->getCell('A'.$c)->getCalculatedValue();
				}
			
				$estados_acumulados=array();
				$nota_nivel_acumulado=array();
				$datos_procesados = array();
				
				for ($i=2; $i<=$numRows; $i++){
					$codigo=$objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
					$cuenta=$objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
					$nivel_obtenido=$objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
					$codigo_sri=$objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
					$codigo_supercias=$objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();

					$estado=array();
					$nivel_inferior=array();
					$largo_codigo = strlen($codigo);
					switch ($largo_codigo) {
					case '1':
						$nivel='1';
						$uno = (!is_numeric(substr($codigo,0,1)))?1:0;
						$estado[]=$uno;
						break;
					case '3':
						$nivel='2';
						$uno = (!is_numeric(substr($codigo,0,1)))?1:0;
						$dos = (!is_numeric(substr($codigo,2,1)))?1:0;
						$nivel_inferior[] = (array_search(substr($codigo,0,1), $codigos_cuentas)==null)?"Falta cuenta nivel 1 ":"";
						$estado[]=$uno.$dos;
						break;
					case '6':
						$nivel='3';
						$uno = (!is_numeric(substr($codigo,0,1)))?1:0;
						$dos = (!is_numeric(substr($codigo,2,1)))?1:0;
						$tres = (!is_numeric(substr($codigo,4,2)))?1:0;
						$nivel_inferior[] = (array_search(substr($codigo,0,1), $codigos_cuentas)==null)?"Falta cuenta nivel 1 ":"";
						$nivel_inferior[] = (array_search(substr($codigo,0,3), $codigos_cuentas)==null)?"Falta cuenta nivel 2 ":"";
						$estado[]=$uno.$dos.$tres;
						break;
					case '9':
						$nivel='4';
						$uno = (!is_numeric(substr($codigo,0,1)))?1:0;
						$dos = (!is_numeric(substr($codigo,2,1)))?1:0;
						$tres = (!is_numeric(substr($codigo,4,2)))?1:0;
						$cuatro = (!is_numeric(substr($codigo,7,2)))?1:0;
						$nivel_inferior[] = (array_search(substr($codigo,0,1), $codigos_cuentas)==null)?"Falta cuenta nivel 1 ":"";
						$nivel_inferior[] = (array_search(substr($codigo,0,3), $codigos_cuentas)==null)?"Falta cuenta nivel 2 ":"";
						$nivel_inferior[] = (array_search(substr($codigo,0,6), $codigos_cuentas)==null)?"Falta cuenta nivel 3 ":"";
						$estado[]=$uno.$dos.$tres.$cuatro;
						break;
					case '13':
						$nivel='5';
						$uno = (!is_numeric(substr($codigo,0,1)))?1:0;
						$dos = (!is_numeric(substr($codigo,2,1)))?1:0;
						$tres = (!is_numeric(substr($codigo,4,2)))?1:0;
						$cuatro = (!is_numeric(substr($codigo,7,2)))?1:0;
						$cinco = (!is_numeric(substr($codigo,10,3)))?1:0;
						$nivel_inferior[] = (array_search(substr($codigo,0,1), $codigos_cuentas)==null)?"Falta cuenta nivel 1 ":"";
						$nivel_inferior[] = (array_search(substr($codigo,0,3), $codigos_cuentas)==null)?"Falta cuenta nivel 2 ":"";
						$nivel_inferior[] = (array_search(substr($codigo,0,6), $codigos_cuentas)==null)?"Falta cuenta nivel 3 ":"";
						$nivel_inferior[] = (array_search(substr($codigo,0,9), $codigos_cuentas)==null)?"Falta cuenta nivel 4 ":"";
						$estado[] =$uno.$dos.$tres.$cuatro.$cinco;
						break;
					default:
						$nivel='0';
						$estado[]=1;
						}
						
					$suma_estados = array_sum($estado);
					$estados_acumulados[] = array_sum($estado);
					
						
					if ($suma_estados>0){
						$estado_final ="Error en código de cuenta contable.";
					}else{
						$estado_final ="";
					}
					
					$nota_nivel_inferior="";
					foreach ($nivel_inferior as $aviso_nivel_inferior){
						$nota_nivel_inferior .= $aviso_nivel_inferior;
					}
					
					$nota_nivel_acumulado[]= $nota_nivel_inferior;
					?>
						<tr>
						<td><?php echo $codigo; ?></td>
						<td><?php echo $cuenta; ?></td>
						<td><?php echo $nivel; ?></td>
						<td><?php echo $codigo_sri; ?></td>
						<td><?php echo $codigo_supercias; ?></td>
						<td><?php echo $estado_final.$nota_nivel_inferior; ?></td>
						</tr>
					<?php
					
					$datos_procesados[] = array('codigo'=>$codigo, 'cuenta'=> $cuenta, 'nivel'=> $nivel, 'codigo_sri'=> $codigo_sri, 'codigo_supercias'=> $codigo_supercias );
				}
							
				$estados_finales = array_sum($estados_acumulados);
				$notas_niveles="";
					foreach ($nota_nivel_acumulado as $aviso_niveles){
						$notas_niveles .= $aviso_niveles;
					}

				if ($estados_finales==0 && $notas_niveles==""){
					$total_registros=count($datos_procesados)-1;
					ini_set('date.timezone','America/Guayaquil');
					$fecha_registro=date('Y-m-d H:i:s');
					$total_guardadas=array();
						for ($g=0; $g <= $total_registros; $g++){ 
							$consultar_cuenta=mysqli_query($con, "SELECT * FROM plan_cuentas WHERE codigo_cuenta= '".$datos_procesados[$g]['codigo']."' and ruc_empresa='".$ruc_empresa."' ");
							$contar_codigos_registrados=mysqli_num_rows($consultar_cuenta);
							if ($contar_codigos_registrados==0 && $datos_procesados[$g]['codigo'] !=""){
								$guardar_cuenta=mysqli_query($con, "INSERT INTO plan_cuentas VALUES (null,'".$datos_procesados[$g]['codigo']."','".$datos_procesados[$g]['nivel']."','".$datos_procesados[$g]['cuenta']."','".$datos_procesados[$g]['codigo_sri']."','".$datos_procesados[$g]['codigo_supercias']."','".$ruc_empresa."','".$id_usuario."','".$fecha_registro."','".$codigo_unico."')");
							$total_guardadas[]=1;
							}
						}
						
						$suma_registros= array_sum($total_guardadas);
						
						if ($suma_registros>0){
						echo "<script>
						var total_registros = '$suma_registros';
						$.notify(total_registros+' Cuenta(s) han sido guardadas.','success');
						setTimeout(function (){location.href ='../modulos/plan_de_cuentas.php'}, 2000);
						</script>";
						}else{
							echo "<script>$.notify('Cuentas registradas con anterioridad.','error');
						</script>";
						}
						
						
					}else{
						echo "<script>$.notify('No se puede guardar, existen errores en los registros, revisar en la columna oservaciones.','error');
						</script>";
					}
					
				?>
				</table>
			</div>
		</div>
				<?php
				
		
	}else{
		$errors []= "El archivo no se pudo cargar.";
	}
	
	closedir($dir);
	
	}else{
		$errors []= "El archivo $nombre_archivo no es de tipo excel. <br>";
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