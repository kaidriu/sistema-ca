<?php
if (!isset($_SESSION["ultimoAcceso"])){
	session_start();
}
	$fechaGuardada = $_SESSION["ultimoAcceso"]; 
	ini_set('date.timezone','America/Guayaquil');
    $ahora = date("Y-n-j H:i:s"); 
    $tiempo_transcurrido = (strtotime($ahora)-strtotime($fechaGuardada)); 

    //comparamos el tiempo transcurrido 
     if($tiempo_transcurrido >= 90000) { 
     //si pasaron 10 minutos o más
		//echo "<script>alert('Han pasado 10 mínutos de inactividad , vuelva a ingresar.')</script>";
		//echo "<script>window.close();</script>";
      session_destroy(); // destruyo la sesión 
      header('Location: /sistema/index.php'); //envío al usuario a la pag. de autenticación 
      //sino, actualizo la fecha de la sesión 
    }else { 
    $_SESSION["ultimoAcceso"] = $ahora; 
   } 
?>
