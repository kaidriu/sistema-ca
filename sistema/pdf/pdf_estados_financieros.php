<?php
require('../ajax/informes_contables.php');
require('../pdf/funciones_pdf.php');

$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];

//$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
$action = $_POST['nombre_informe'];

//1 es balance general
if ($action == '1') {
	$desde = $_POST['fecha_desde'];
	$hasta = $_POST['fecha_hasta'];
	$nivel = $_POST['nivel'];
	$control_errores= control_errores($con, $ruc_empresa, $desde, $hasta, 'excel');
	if ($nivel == '0') {
		$nivel_cuenta = "";
	} else {
		$nivel_cuenta = " and nivel_cuenta = " . $nivel;
	}
	$generar_balance = generar_balance($con, $ruc_empresa, $id_usuario, $desde, $hasta, '1', '3');
	$sql_detalle_balance = mysqli_query($con, "SELECT nivel_cuenta as nivel, codigo_cuenta as codigo_cuenta, nombre_cuenta as nombre_cuenta, sum(valor) as valor FROM balances_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' $nivel_cuenta group by codigo_cuenta, nivel_cuenta");
	$sql_totales_activo_pasivo_patrimonio = mysqli_query($con, "SELECT nombre_cuenta as nombre_cuenta, sum(round(valor,2)) as valor, codigo_cuenta as codigo_cuenta  FROM balances_tmp WHERE ruc_empresa = '" . $ruc_empresa . "' and nivel_cuenta='1' group by codigo_cuenta");
				

//para buscar la imagen
$busca_imagen = mysqli_query($con,"SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' ");
$datos_imagen=mysqli_fetch_assoc($busca_imagen);
$imagen = "../logos_empresas/".$datos_imagen['logo_sucursal'];

$busca_empresa = mysqli_query($con,"SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."' ");
$datos_empresa=mysqli_fetch_assoc($busca_empresa);
$nombre_empresa = $datos_empresa['nombre'];
$rep_legal= $datos_empresa['nom_rep_legal'];
$nombre_contador= $datos_empresa['nombre_contador'];
$cedula_rep_legal= $datos_empresa['ced_rep_legal'];
$ruc_contador= $datos_empresa['ruc_contador'];


$nombre_reporte="ESTADO DE SITUACIÓN FINANCIERA";
$html_encabezado='<p align="center">'.utf8_decode($nombre_empresa).'</p><br>
				  <p align="center">'.utf8_decode($nombre_reporte).'</p><br>
				  <p align="center">Del: '.date("d-m-Y", strtotime($desde)).' al '. date("d-m-Y", strtotime($hasta)) .'</p><br>';


$pdf = new funciones_pdf( 'P', 'mm', 'A4' );//P
$imagen_optimizada = $pdf->imagen_optimizada($imagen, $width=200, $height=200);
imagejpeg($imagen_optimizada, '../docs_temp/'.$ruc_empresa.'.jpg');
$pdf->AddPage();//es importante agregar esta linea para saber la pagina inicial
$pdf->SetFont('Arial','',9);//esta tambien es importante
$pdf->detalle_html($html_encabezado);
$pdf->Image('../docs_temp/'.$ruc_empresa.'.jpg', 10, 10, 30, 30, 'jpg', '');
$pdf->SetWidths(array(30,60,20,20,20,20,20));
$pdf->Row_tabla(array(utf8_decode('Código'),'Cuenta','Nivel 5','Nivel 4','Nivel 3','Nivel 2','Nivel 1'));
while ($row_detalle_balance=mysqli_fetch_assoc($sql_detalle_balance)){
		$codigo_cuenta = $row_detalle_balance['codigo_cuenta'];
		$nombre_cuenta = strtoupper($row_detalle_balance['nombre_cuenta']);
		$nivel = $row_detalle_balance['nivel'];
		$valor = $row_detalle_balance['valor'];
		if ($valor != 0) {
			if (substr($codigo_cuenta, 0, 1) == 1) {
				$valor = $valor;
			} else {
				$valor = $valor * -1;
			}

		$pdf->Row_tabla(array('[ '.$codigo_cuenta.' ]',utf8_decode($nombre_cuenta), 
		$nivel =='5'?number_format($valor, 2, '.', ''):"", 
		$nivel =='4'?number_format($valor, 2, '.', ''):"",
		$nivel =='3'?number_format($valor, 2, '.', ''):"",
		$nivel =='2'?number_format($valor, 2, '.', ''):"",
		$nivel =='1'?number_format($valor, 2, '.', ''):""));
		}
}
$pdf->Ln();
	$suma_activo = array();
	$suma_pasivo = array();
	$suma_patrimonio = array();
	$pdf->SetWidths(array(90,30));
	while ($row_totales = mysqli_fetch_array($sql_totales_activo_pasivo_patrimonio)) {
		$codigo_cuenta = $row_totales['codigo_cuenta'];
		$nombre_cuenta = strtoupper($row_totales['nombre_cuenta']);
		//para poner los pasivos y patrimoio con signo positivo
		if (substr($codigo_cuenta, 0, 1) == 1) {
			$valor = number_format($row_totales['valor'], 2, '.', '');
		} else {
			$valor = number_format($row_totales['valor'] * -1, 2, '.', '');
		}
		if ($codigo_cuenta == "1") {
			$suma_activo[] = $valor;
		} else {
			$suma_activo[] = 0;
		}

		if ($codigo_cuenta == "2") {
			$suma_pasivo[] = $valor;
		} else {
			$suma_pasivo[] = 0;
		}

		if ($codigo_cuenta == "3") {
			$suma_patrimonio[] = $valor;
		} else {
			$suma_patrimonio[] = 0;
		}

	$pdf->Row_tabla(array(utf8_decode($nombre_cuenta), number_format($valor, 2, '.', '')));
	}

	$suma_pasivo_patrimonio = array_sum($suma_pasivo) + array_sum($suma_patrimonio);
	$resultado_diferencia = number_format(array_sum($suma_activo), 2, '.', '') - number_format($suma_pasivo_patrimonio, 2, '.', '');

	if (array_sum($suma_activo) == $suma_pasivo_patrimonio) {
		$diferencias = "";
	} else {
		$diferencias = $resultado_diferencia == 0 ? "" : "Diferencia: " . number_format($resultado_diferencia, 2, '.', '');
	}

//para sacar la utilidad
$resultado_utilidad = utilidad_perdida($con, $ruc_empresa, $id_usuario, $desde, $hasta);

$pdf->Row_tabla(array(utf8_decode($resultado_utilidad['resultado']), number_format($resultado_utilidad['valor'], 2, '.', '')));
$pdf->SetWidths(array(120));
$pdf->Row_tabla(array($diferencias));

if(!empty($control_errores)){
$pdf->Ln();
$pdf->MultiCell(190, 5, utf8_decode($control_errores),1,1);
}

$pdf->Ln();
$pdf->Ln();
$pdf->SetWidths(array(95,95));

$pdf->Row_tabla(array('Gerente: '.strtoupper($rep_legal).' '.$cedula_rep_legal ,'Contador: '.strtoupper($nombre_contador).' '.$ruc_contador));

$pdf->SetY(5);
$pdf->Cell(0,5,utf8_decode('Pág:').$pdf->PageNo(),0,0,'R');

$pdf->Output("Estado_situacion_financiera.pdf","D");
unlink('../docs_temp/'.$ruc_empresa.'.jpg');
}
?>
