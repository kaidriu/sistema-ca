	<?php
	include("../conexiones/conectalogin.php");
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$con = conenta_login();
		
	$cuentas_contables =  new cuentas_contables();

	//para sacar el siguiente codigo de cuenta que continuaria 
	if (isset($_POST['opcion']) && ($_POST['opcion']=='siguiente_codigo_cuenta')){
	$id_cuenta = $_POST['id_cuenta'];
	$nivel= $_POST['nivel'];
		$siguiente_codigo = $cuentas_contables->siguiente_codigo($con, $id_cuenta, $ruc_empresa);	
		echo ($siguiente_codigo);
		
	}
	
	//clase para identificar el numero que continua de la cuenta
	class cuentas_contables{
		
		public function siguiente_codigo($con, $id_cuenta, $ruc_empresa){	
		
		$consulta_cuentas = mysqli_query($con, "SELECT * FROM plan_cuentas WHERE id_cuenta ='".$id_cuenta."' ");
		$row_codigo_cuenta=mysqli_fetch_array($consulta_cuentas);
		$codigo_cuenta = $row_codigo_cuenta['codigo_cuenta'];
		$nivel_salida = $row_codigo_cuenta['nivel_cuenta']+1;

		switch ($nivel_salida) {
			case "2":
				$mid_largo="1";
				$mid_inicial_salida="3";
				$mid_largo_salida="1";
				$codigo_inicial = substr($codigo_cuenta,0,3);
				break;
			case "3":
				$mid_largo="3";
				$mid_inicial_salida="5";
				$mid_largo_salida="2";
				$codigo_inicial = substr($codigo_cuenta,0,6);
				break;
			case "4":
				$mid_largo="6";
				$mid_inicial_salida="8";
				$mid_largo_salida="2";
				$codigo_inicial = substr($codigo_cuenta,0,9);
				break;
			case "5":
				$mid_largo="9";
				$mid_inicial_salida="11";
				$mid_largo_salida="3";
				$codigo_inicial = substr($codigo_cuenta,0,13);
				break;
				}
	
			$consulta_cuentas = mysqli_query($con, "SELECT max(mid(codigo_cuenta, $mid_inicial_salida, $mid_largo_salida)) as ultimo FROM plan_cuentas WHERE ruc_empresa ='".$ruc_empresa."' and nivel_cuenta='".$nivel_salida."' and mid(codigo_cuenta,1, $mid_largo) = '".$codigo_cuenta."' ");
			$row_cuentas=mysqli_fetch_array($consulta_cuentas);
			$inicial =1;
			$siguiente_codigo =$row_cuentas['ultimo']+1;
			
				$serie_inicio_fin=array();
				foreach(range($inicial, $siguiente_codigo) as $toda_la_serie ){
				$serie_inicio_fin[]= intval($toda_la_serie);
				}
			
				$solo_registrados = array();
				$todas_cuentas = mysqli_query($con, "SELECT mid(codigo_cuenta, $mid_inicial_salida, $mid_largo_salida) as codigos FROM plan_cuentas WHERE ruc_empresa ='".$ruc_empresa."' and nivel_cuenta='".$nivel_salida."' and mid(codigo_cuenta,1, $mid_largo) = '".$codigo_cuenta."' ");
				while ($todos_las_encontrados=mysqli_fetch_array($todas_cuentas)){
				$solo_registrados[] = intval($todos_las_encontrados['codigos']);
				}
				
				$codigos_faltantes = array_diff($serie_inicio_fin,$solo_registrados);
				if ($codigos_faltantes == false){
					
					if ($nivel_salida=="1"){
						$numero_final=str_pad($siguiente_codigo,1,"0",STR_PAD_LEFT);
					}
					if ($nivel_salida=="2"){
						$numero_final=str_pad($siguiente_codigo,1,"0",STR_PAD_LEFT);
					}
					if ($nivel_salida=="3"){
						$numero_final=str_pad($siguiente_codigo,2,"00",STR_PAD_LEFT);
					}
					if ($nivel_salida=="4"){
						$numero_final=str_pad($siguiente_codigo,2,"00",STR_PAD_LEFT);
					}
					if ($nivel_salida=="5"){
						$numero_final=str_pad($siguiente_codigo,3,"000",STR_PAD_LEFT);
					}
					
				return $codigo_inicial.".".$numero_final;				
				}else{
					$codigo_faltante= min($codigos_faltantes);
					if ($nivel_salida=="1"){
						$numero_faltante=str_pad($codigo_faltante,1,"0",STR_PAD_LEFT);
					}
					if ($nivel_salida=="2"){
						$numero_faltante=str_pad($codigo_faltante,1,"0",STR_PAD_LEFT);
					}
					if ($nivel_salida=="3"){
						$numero_faltante=str_pad($codigo_faltante,2,"00",STR_PAD_LEFT);
					}
					if ($nivel_salida=="4"){
						$numero_faltante=str_pad($codigo_faltante,2,"00",STR_PAD_LEFT);
					}
					if ($nivel_salida=="5"){
						$numero_faltante=str_pad($codigo_faltante,3,"000",STR_PAD_LEFT);
					}
				return $codigo_inicial.".".$numero_faltante;
				}
		
		}
						
	}
?>		
		