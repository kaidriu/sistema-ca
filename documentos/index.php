<!DOCTYPE html>
<html lang="es">
<script src='https://www.google.com/recaptcha/api.js'></script>
<head>
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>CaMaGaRe</title>
	<link rel="shortcut icon" type="image/png" href="/web/img/logofinal.png"/>
	<?php include("../sistema/head.php");?>
</head>
<body style=" background-color: hsla(140, 30%, 61%, 0.67);padding: 2px;">
<?php
include("entrada.php");
session_start();
if(isset($_POST['entrada'])){
	$cedula= $_POST['cedula'];
	$datos_cliente_proveedor = valida_entrada($cedula);
	
	$recaptcha = $_POST["g-recaptcha-response"];
 
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => '6LdJLE0UAAAAAE2s7Zzf8XhWHYftv6zfPaHo-ZA_',
			'response' => $recaptcha
		);
		$options = array(
			'http' => array (
				'method' => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success = json_decode($verify);


	if($datos_cliente_proveedor != false && $captcha_success->success){
	echo muestra_menu($datos_cliente_proveedor, $cedula);   
	}else{
        echo "<script>alert('Marque la casilla de no soy un robot o verifique Cedula/Ruc/Pasaporte.')</script>";
		echo "<script>window.close();</script>";
		echo form_entrada();
	}
	
}else{
	echo form_entrada();
}
?>
<?php include("../sistema/pie.php");?>
	
</body>
	
</html>

<script>
$(document).ready(function(){
			load(1);
});

function load(page){
			var ruc_cliente_proveedor= $("#ruc_cliente_proveedor").val();
			var por= $("#por").val();
			var ordenado= $("#ordenado").val();
			var q= $("#q").val();
			var r= $("#r").val();
			var n= $("#n").val();
			var g= $("#g").val();
			$("#loader_facturas").fadeIn('slow');
			
			//PARA BUSCAR LAS FACTURAS
			$.ajax({
				url:'buscar_facturas.php?action=ajax&page='+page+'&q='+q+"&ruc_cliente_proveedor="+ruc_cliente_proveedor,
				 beforeSend: function(objeto){
				 $('#loader_facturas').html('<img src="../sistema/image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_facturas").html(data).fadeIn('slow');
					$('#loader_facturas').html('');
				}
			});
			
			//PARA BUSCAR LAS RETENCIONES
			$.ajax({
				url:'buscar_retenciones.php?action=ajax&page='+page+'&r='+r+"&ruc_cliente_proveedor="+ruc_cliente_proveedor,
				 beforeSend: function(objeto){
				 $('#loader_retenciones').html('<img src="../sistema/image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_retenciones").html(data).fadeIn('slow');
					$('#loader_retenciones').html('');
				}
			});
			//PARA BUSCAR LAS NOTAS DE CREDITO
			$.ajax({
				url:'buscar_notas_credito.php?action=ajax&page='+page+'&n='+n+"&ruc_cliente_proveedor="+ruc_cliente_proveedor,
				 beforeSend: function(objeto){
				 $('#loader_notas_de_credito').html('<img src="../sistema/image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_notas_de_credito").html(data).fadeIn('slow');
					$('#loader_notas_de_credito').html('');
				}
			});
			//PARA BUSCAR LAS GUIAS DE REMISION
			$.ajax({
				url:'buscar_guias_remision.php?action=ajax&page='+page+'&g='+g+"&ruc_cliente_proveedor="+ruc_cliente_proveedor,
				 beforeSend: function(objeto){
				 $('#loader_guias_de_remision').html('<img src="../sistema/image/ajax-loader.gif"> Cargando...');
			  },
				success:function(data){
					$(".outer_div_guias_de_remision").html(data).fadeIn('slow');
					$('#loader_guias_de_remision').html('');
				}
			});
			
};

</script>
