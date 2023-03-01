	<?php
//para traer los datos de la sucursal y rellenar en los campos mediante el cambio de serie
		include("../conexiones/conectalogin.php");
		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		if (isset($_GET['serie_va'])){
		$serie_viene =$_GET['serie_va'];
		$conexion = conenta_login();
		$sql = mysqli_query($conexion, "SELECT * FROM sucursales where ruc_empresa ='".$ruc_empresa."' and serie ='".$serie_viene."'");
		$info_secuenciales = mysqli_fetch_array($sql);

		$data= array('direccion_sucursal'=> $info_secuenciales['direccion_sucursal'], 'id_sucursal'=>$info_secuenciales['id_sucursal'],
			'nombre_sucursal'=> $info_secuenciales['nombre_sucursal'], 'moneda_sucursal'=>$info_secuenciales['moneda_sucursal'], 'inicial_factura'=>$info_secuenciales['inicial_factura'],
			'inicial_nc'=>$info_secuenciales['inicial_nc'], 'inicial_nd'=> $info_secuenciales['inicial_nd'], 'inicial_gr'=>$info_secuenciales['inicial_gr'],
		'inicial_cr'=>$info_secuenciales['inicial_cr'], 'inicial_liq'=>$info_secuenciales['inicial_liq'], 'decimal_doc'=>$info_secuenciales['decimal_doc'],
		'decimal_cant'=> $info_secuenciales['decimal_cant'], 'inicial_proforma'=>$info_secuenciales['inicial_proforma'], 'impuestos_recibo'=>$info_secuenciales['impuestos_recibo']);
	
		if ($sql){
			$arrResponse = array("status" => true, "data" => $data);
		}else{
			$arrResponse = array("status" => false, "msg" => 'Datos no encontrados');
		}
		
	echo json_encode($arrResponse);//, JSON_UNESCAPED_UNICODE
	die();
}
?>		
		