<?php
include("../conexiones/conectalogin.php");
require('../pdf/funciones_pdf.php');
require_once('../helpers/helpers.php');

//include("../core/db.php");
//$db = new db();
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario_actual = $_SESSION['id_usuario'];
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
if (isset($_GET['action']) && isset($_GET['codigo_unico']) && $action=="consignacion_ventas"){
$codigo_unico=$_GET['codigo_unico'];
$busca_encabezado=mysqli_query($con, "SELECT enc_con.fecha_consignacion as fecha_consignacion, 
enc_con.fecha_registro as fecha_registro, enc_con.numero_consignacion as numero_consignacion,
cli.nombre as nombre, usu.nombre as usuario, enc_con.observaciones as observaciones, cli.direccion as direccion, 
enc_con.punto_partida as punto_partida, enc_con.punto_llegada as punto_llegada, enc_con.responsable as responsable, 
enc_con.fecha_entrega as fecha_entrega, enc_con.hora_entrega as hora_entrega, enc_con.traslado_por as traslado_por FROM encabezado_consignacion as enc_con 
INNER JOIN clientes as cli ON enc_con.id_cli_pro=cli.id INNER JOIN usuarios as usu ON usu.id=enc_con.id_usuario WHERE enc_con.codigo_unico = '".$codigo_unico."' ");
$row_encabezados = mysqli_fetch_array($busca_encabezado);
$fecha_emision = date("d-m-Y", strtotime($row_encabezados['fecha_consignacion']));
$fecha_registro = date("H:i:s", strtotime($row_encabezados['fecha_registro']));
$numero_consignacion = $row_encabezados['numero_consignacion'];
$cliente = $row_encabezados['nombre'];
$direccion_cliente = $row_encabezados['direccion'];
$observaciones = $row_encabezados['observaciones'];
$punto_partida = $row_encabezados['punto_partida'];
$punto_llegada = $row_encabezados['punto_llegada'];
$responsable_traslado = $row_encabezados['responsable'];
$usuario = $row_encabezados['usuario'];
$fecha_entrega = $row_encabezados['fecha_entrega']==0?"___/___/_____":date("d-m-Y", strtotime($row_encabezados['fecha_entrega']));
$hora_entrega = $row_encabezados['hora_entrega']==0?"___/___":date("H:i", strtotime($row_encabezados['hora_entrega']));
$traslado_por = $row_encabezados['traslado_por']==0?"1":$row_encabezados['traslado_por'];
foreach (responsable_translado() as $resp){
	if ($traslado_por == $resp['codigo']) {
		$traslado_por_final = utf8_decode($resp['nombre']);
	}
}

//totl cantidades
$busca_totales=mysqli_query($con, "SELECT sum(cant_consignacion) as cantidad FROM detalle_consignacion WHERE codigo_unico = '".$codigo_unico."' ");
$row_totales = mysqli_fetch_array($busca_totales);
$total_consignadas=$row_totales['cantidad'];

//para buscar la imagen
$busca_imagen = mysqli_query($con,"SELECT * FROM sucursales WHERE ruc_empresa = '".$ruc_empresa."' ");
$datos_imagen=mysqli_fetch_assoc($busca_imagen);
$imagen = "../logos_empresas/".$datos_imagen['logo_sucursal'];

$busca_empresa = mysqli_query($con,"SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."' ");
$datos_empresa=mysqli_fetch_assoc($busca_empresa);
$nombre_empresa = $datos_empresa['nombre_comercial'];
$html_encabezado='<p align="center">'.$nombre_empresa.'</p>
				  <p align="center">'.utf8_decode('PRODUCTOS EN CONSIGNACIÓN / DOCUMENTO DE TRASLADO').'</p><br>';

$detalle_consignacion = mysqli_query($con,"SELECT codigo_producto, nombre_producto, lote, nup, FORMAT(cant_consignacion,0) as cant_consignacion FROM detalle_consignacion as det_con WHERE det_con.codigo_unico = '".$codigo_unico."' ");

$pdf = new funciones_pdf( 'P', 'mm', 'A4');//P
//$pdf->AliasNbPages();
$imagen_optimizada = $pdf->imagen_optimizada($imagen, $width=200, $height=200);
imagejpeg($imagen_optimizada, '../docs_temp/'.$ruc_empresa.'.jpg');
$pdf->AddPage();//es importante agregar esta linea para saber la pagina inicial
$pdf->SetFont('Arial','B',10);//esta tambien es importante
$prop = array('HeaderColor'=>array(213, 219, 219),'color1'=>array(253, 254, 254),'color2'=>array(253, 254, 254),'padding'=>2);

$pdf->detalle_html($html_encabezado);
$pdf->Cell(50);
$pdf->Cell(140,5,'DIS-PR-01-R02',1,1,'L');
$pdf->Cell(50);
$pdf->Cell(140,5,'No. documento: 001-001-'.$numero_consignacion,1,1,'L');
$pdf->Cell(50);
$pdf->Cell(140,5,utf8_decode('Fecha emisión: ').$fecha_emision." Hora:".$fecha_registro,1,1,'L');
$pdf->Cell(50);
$pdf->Cell(140,5,'Punto de partida: '.utf8_decode($punto_partida),1,1,'L');
$pdf->Cell(50);
$pdf->Cell(140,5,'Punto de llegada: '.utf8_decode($punto_llegada),1,1,'L');
$pdf->Cell(50);
$pdf->Cell(140,5,'Asesor: '.utf8_decode($responsable_traslado),1,1,'L');
$pdf->Cell(50);
$pdf->MultiCell(140,5,'Cliente/Receptor: '.utf8_decode($cliente),1,1);
$pdf->Cell(50);
$pdf->MultiCell(140,5,utf8_decode('Dirección cliente: ').utf8_decode($direccion_cliente),1,1);
$pdf->Cell(50);
$pdf->Cell(140,5,'Fecha entrega: '.$fecha_entrega." Hora entrega: ".$hora_entrega. " Responsable traslado: ".$traslado_por_final,1,1,'L');

$pdf->Image('../docs_temp/'.$ruc_empresa.'.jpg', 20, 20, 30, 30, 'jpg', '');

$pdf->Ln();
$pdf->SetFont('Arial','B',7);//esta tambien es importante
$pdf->SetWidths(array(30,80,20,20,10,10,10,10));
$pdf->Row_tabla(array(utf8_decode('Código'),utf8_decode('Descripción'),'Lote', 'Nup','Cant','Ret','Fac','Acon'));
while ($row_detalle=mysqli_fetch_assoc($detalle_consignacion)){
$pdf->Row_tabla(array($row_detalle['codigo_producto'],utf8_decode($row_detalle['nombre_producto']), 
utf8_decode($row_detalle['lote']),$row_detalle['nup'],$row_detalle['cant_consignacion'],
'','',''));
}
$pdf->Cell(190,5,'Total consignaciones: '.number_format($total_consignadas,0,'.','') ,1,1,'L');

$pdf->MultiCell(190, 5, 'Observaciones:'.utf8_decode($observaciones),1,1);
$pdf->Ln();

//$pdf->Cell(5);
//$pdf->Cell(140,5,"EMITIDO POR: ".utf8_decode(strtoupper($usuario)),0,0,'L');
$pdf->detalle_html('<p align="center"></p><hr>');
$pdf->detalle_html('<p align="center"> EMITIDO POR: '.utf8_decode(strtoupper($usuario)).'                                    LOGISTICA                           RECIBIDO POR</p><br>');

$pdf->detalle_html(utf8_decode('<p align="center"> VERIFICACIÓN DE ACONDICIONAMIENTO POR:                                                                                         '));
$pdf->detalle_html('<br><br>');

$pdf->SetY(5);
$pdf->Cell(0,5,utf8_decode('Pág:').$pdf->PageNo(),0,0,'R');


$pdf->Output("CV N. ".$numero_consignacion.".pdf","D");
unlink('../docs_temp/'.$ruc_empresa.'.jpg');
}

?>
