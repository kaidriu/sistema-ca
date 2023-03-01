<?php
include("../conexiones/conectalogin.php");
$con = conenta_login();
session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
	
		$c = mysqli_real_escape_string($con,(strip_tags($_GET['term'], ENT_QUOTES)));
		 $aColumns = array('nombre_cuenta','codigo_cuenta');//Columnas de busqueda
		 $sTable = "plan_cuentas";
		 $sWhere = "WHERE ruc_empresa = '". $ruc_empresa ."' and nivel_cuenta = '5' " ;
		if ( $_GET['term'] != "" )
		{
			$sWhere = "WHERE (ruc_empresa = '". $ruc_empresa ."' and nivel_cuenta = '5' AND ";
			
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= $aColumns[$i]." LIKE '%".$c."%' AND ruc_empresa = '". $ruc_empresa ."' and nivel_cuenta = '5' OR ";
			}
			
			$sWhere = substr_replace( $sWhere, "AND ruc_empresa = '". $ruc_empresa ."' and nivel_cuenta = '5' ", -3 );
			$sWhere .= ')';
		}
		$sWhere.=" order by nombre_cuenta asc";

		//pagination variables
		$page = 1;
		$per_page = 50; //how much records you want to show
		//$adjacents  = 10; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		$count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		//main query to fetch the data
		$sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){
			$arreglo_cuentas = array();
			if (mysqli_num_rows($query) ==0){
				array_push($arreglo_cuentas,"No hay datos");
			}else{
			while($palabras = mysqli_fetch_array($query)){
				$id_cuenta=$palabras['id_cuenta'];
				    $row_array['id_cuenta']=$id_cuenta;
					$row_array['value'] = $palabras['codigo_cuenta']." - ".$palabras['nombre_cuenta'];
					$row_array['nombre_cuenta']=$palabras['nombre_cuenta'];
					$row_array['codigo_cuenta']=$palabras['codigo_cuenta'];
				array_push($arreglo_cuentas,$row_array);
			}
			}
			echo json_encode($arreglo_cuentas);
			mysqli_close($con);
		}
?>