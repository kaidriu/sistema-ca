<?php
include_once("../conexiones/conectalogin.php");
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
if(($action == 'info_ruc') && isset($_POST['numero']) && (!empty($_POST['numero'] ))){
$consulta_ruc= new consultaRuc();
$matches = $consulta_ruc->info_ruc($_POST['numero']);

foreach($matches as $fila){
		$columna = explode("\t",$fila);
		$info_razon_social[$columna[1]]= $columna[1];
		$info_nombre_comercial[$columna[2]]= $columna[2];
		$info_obligado[$columna[9]]= $columna[9];
		$info_tipo_contribuyente[$columna[10]]= $columna[10];
		$info_dir[$columna[14]]= $columna[14];				
		$info_dir[$columna[15]]= $columna[15];
		$info_dir[$columna[16]]= $columna[16];
		$info_provincia[$columna[17]]= $columna[17];
	  }
	  
	  $lleva_contabilidad = ($info_obligado[$columna[9]])== "S"?"SI":"NO";
	  $tipo_contribuyente = (isset($info_tipo_contribuyente[$columna[10]]))?$info_tipo_contribuyente[$columna[10]]:"";
	  if ($tipo_contribuyente=="SOCIEDADES"){
		  $tipo_contribuyente="03";
	  }
	  if ($tipo_contribuyente=="PERSONAS NATURALES" && $lleva_contabilidad=="SI"){
		  $tipo_contribuyente="02";
	  }
	  if ($tipo_contribuyente=="PERSONAS NATURALES" && $lleva_contabilidad=="NO"){
		  $tipo_contribuyente="01";
	  }
	  $con = conenta_login();
	  $select_provincia   = mysqli_query($con, "SELECT codigo FROM provincia WHERE nombre='".utf8_encode($info_dir[$columna[14]])."' ");
	  $row_provincia= mysqli_fetch_array($select_provincia);
	  $codigo_provincia=$row_provincia['codigo'];

	  $select_ciudad   = mysqli_query($con, "SELECT codigo FROM ciudad WHERE nombre='".utf8_encode($info_dir[$columna[15]])."' ");
	  $row_ciudad= mysqli_fetch_array($select_ciudad);
	  $codigo_ciudad=$row_ciudad['codigo'];

	$direccion=utf8_encode($info_dir[$columna[14]]." ".$info_dir[$columna[15]]." ".$info_dir[$columna[16]]);	  
	$datos_ruc[] = array('nombre'=>utf8_encode($info_razon_social[$columna[1]]),'tipo'=>$tipo_contribuyente, 'direccion'=> $direccion, 
	'nombre_comercial'=>$info_nombre_comercial[$columna[2]], 'codigo_provincia'=>$codigo_provincia , 'codigo_ciudad'=>$codigo_ciudad);

	header('Content-Type: application/json');
	echo json_encode($datos_ruc);

}


class consultaRuc{

//para buscar por provincia dependiendo del codigo de provincia segun el ruc
	public function info_ruc($ruc){
			$info_ruc = array();
			$codigo_provincia = substr($ruc, 0, 2);
			switch ($codigo_provincia) {
				case "01":
					$provincia = "AZUAY";
					break;
				case "02":
					$provincia = "BOLIVAR";
					break;
				case "03":
					$provincia = "CANAR";
					break;
				case "04":
					$provincia = "CARCHI";
					break;
				case "05":
					$provincia = "COTOPAXI";
					break;
				case "06":
					$provincia = "CHIMBORAZO";
					break;
				case "07":
					$provincia = "EL_ORO";
					break;
				case "08":
					$provincia = "ESMERALDAS";
					break;
				case "09":
					$provincia = "GUAYAS";
					break;
				case "10":
					$provincia = "IMBABURA";
					break;
				case "11":
					$provincia = "LOJA";
					break;
				case "12":
					$provincia = "LOS_RIOS";
					break;
				case "13":
					$provincia = "MANABI";
					break;
				case "14":
					$provincia = "MORONA_SANTIAGO";
					break;
				case "15":
					$provincia = "NAPO";
					break;
				case "16":
					$provincia = "PASTAZA";
					break;
				case "17":
					$provincia = "PICHINCHA";
					break;
				case "18":
					$provincia = "TUNGURAHUA";
					break;
				case "19":
					$provincia = "ZAMORA_CHINCHIPE";
					break;
				case "20":
					$provincia = "GALAPAGOS";
					break;
				case "21":
					$provincia = "SUCUMBIOS";
					break;
				case "22":
					$provincia = "ORELLANA";
					break;
				case "23":
					$provincia = "SANTO_DOMINGO";
					break;
				case "24":
					$provincia = "SANTA_ELENA";
					break;
			}
			
		$handle = @fopen("../ruc_ecuador/".$provincia.".txt", "r");
		if ($handle){
			while (!feof($handle)){
				$buffer = fgets($handle);
				if(strpos($buffer, $ruc) !== FALSE)
					$info_ruc[] = $buffer;			
			}
			fclose($handle);
		}

			  $columna=null;
			  foreach($info_ruc as $fila){
				$columna = explode("\t",$fila);
				$ruc_encontrado[$columna[0]]= $columna[0];
			  }
			  
			  
			  if (isset($ruc_encontrado[$columna[0]])) {
					return $info_ruc;
				}else{
					return $this->buscar_all($ruc);
				}	  
					  
		}
		
		//para buscar en todas las provincias
		public function buscar_all($ruc){
			$info_ruc = array();
			$provincias=array('AZUAY','BOLIVAR','CANAR','CARCHI','CHIMBORAZO','COTOPAXI','EL_ORO','ESMERALDAS','GALAPAGOS','GUAYAS','IMBABURA','LOJA','LOS_RIOS',
			'MANABI','MORONA_SANTIAGO','NAPO','ORELLANA','PASTAZA','PICHINCHA','SANTA_ELENA','SANTO_DOMINGO','SUCUMBIOS','TUNGURAHUA','ZAMORA_CHINCHIPE');
			
			for ( $i=0 ; $i<count($provincias) ; $i++ ){
					$handle = @fopen("../ruc_ecuador/".$provincias[$i].".txt", "r");
					if ($handle){
						while (!feof($handle)){
							$buffer = fgets($handle);
							if(strpos($buffer, $ruc) !== FALSE)
								$info_ruc[] = $buffer;			
						}
						fclose($handle);
					}
			
				}
					  
			$columna=null;
			  foreach($info_ruc as $fila){
				$columna = explode("\t",$fila);
				$ruc_encontrado[$columna[0]]= $columna[0];
			  }
			  
			  
			  if (isset($ruc_encontrado[$columna[0]])) {
					return $info_ruc;
				}else{
					return exit;
				}	  
					  
		}

}

?>