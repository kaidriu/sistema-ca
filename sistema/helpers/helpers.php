<?php
function dep($data){ //para depurar o mostrar array de forma entendible
    $format = print_r('<pre>');
    $format .= print_r($data);
    $format .= print_r('</pre>');
    return $format;
}

function status_servidor(){
    $status="ok";
    if($status=="ok"){
    return true;
    }else{
    return false;
    }

}

//para transformar numeros a letras
function numero_letras($valor)
{
    require_once("libraries/core/numero_letras.php");
    $result = num_letras(number_format($valor,2,'.',''));
    return $result;
}

//para validar fechas separadas en excel en cada columna
function validar_fecha($fecha_validar)
{
	if (count($fecha_validar) == 3 && checkdate($fecha_validar[1], $fecha_validar[0], $fecha_validar[2])) {
		return true;
	}
	return false;
}

//compara dos fechas
function comparar_fecha($fecha_vence_array){
	if($fecha_vence_array[2] > date('Y', time())){
		return true;
	}else if($fecha_vence_array[2] == date('Y', time()) && $fecha_vence_array[1] > date('m', time()) ){
		return true;
	}else if($fecha_vence_array[2] == date('Y', time()) && $fecha_vence_array[1] == date('m', time()) && $fecha_vence_array[0] > date('d', time()) ){
		return true;
	}
	return false;
}

//para mostrar mensaje de error enviando array con los mensajes
function mensaje_error($mensajes){
        ?>
        <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php
        foreach ($mensajes as $error) {
            echo $error . "<br>";
        }
        ?>
        </div>
        <?php
    }

//para generar un codigo unico

function codigo_aleatorio($n){
	$a = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0");
	$name = NULL;
	$e = count($a) - 1; //cuenta el número de elementos del arreglo y le resta 1
	for($i=1;$i<=$n;$i++){
		$m = rand(0,$e); //devuelve un número randómico entre 0 y el número de elementos
		$name .= $a[$m];
	}
	return $name;
}

//Envio de correos desde camagare

function sendEmail($data)
{
    require_once("../documentos_mail/phpmailer.php");
    require_once("../documentos_mail/smtp.php");
    require_once("../documentos_mail/exception.php");
    $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
    $asunto = $data['asunto'];
    $emailDestino = explode(',', $data['receptor']);
    $empresa = $data['empresa'];
    
    $remitente = $data['emisor'];
    $correo_host = $data['host'];
    $correo_pass = $data['pass'];
    $correo_port = $data['port'];

    //ENVIO DE CORREO
    // ---------- datos de la cuenta de Gmail -------------------------------
    $phpmailer->Username = $remitente;
    $phpmailer->Password = $correo_pass; 
    //-----------------------------------------------------------------------
    $phpmailer->SMTPDebug = 0;
    $phpmailer->SMTPSecure = false;//'ssl';
    $phpmailer->Host = $correo_host; // GMail
    $phpmailer->Port = $correo_port;
    $phpmailer->IsSMTP(); // use SMTP
    $phpmailer->SMTPAuth = true;
    $phpmailer->setFrom($phpmailer->Username,$empresa);

    for($i = 0; $i < count($emailDestino); $i++) {
        $phpmailer->AddAddress($emailDestino[$i]);
    }
       
    $phpmailer->Subject = $asunto;
    
    if(isset($data['pdf'])){
    $phpmailer->addAttachment($data['pdf']);
    }
    if(isset($data['xml'])){
        $phpmailer->addAttachment($data['xml']);
    }
                   
    ob_start();
    require_once("../documentos_mail/".$data['template'].".php");
    $mensaje = ob_get_clean();
    //$mensaje = "mensaje de garcia";
    $phpmailer->Body = $mensaje;
    $phpmailer->IsHTML(true);
    return $phpmailer->Send();
}

    function datos_empresa($ruc_empresa, $con){
        $busca_empresa = "SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."'";
        $resultado_de_la_busqueda = $con->query($busca_empresa);
        $row=mysqli_fetch_array($resultado_de_la_busqueda);
        return $row;
    }
    
    function datos_correo($ruc_empresa, $con){
        $busca_info_fe = "SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ";
        $result = $con->query($busca_info_fe);
        //$rowcount=mysqli_num_rows($result);
        $info_fe=mysqli_fetch_array($result);
        $correoHost =empty($info_fe['correo_host'])?0:1;
        $correoPass =empty($info_fe['correo_pass'])?0:1;
        $correoPort =empty($info_fe['correo_port'])?0:1;
        $correoRemitente =empty($info_fe['correo_remitente'])?0:1;
        if (($correoHost + $correoPass + $correoPort + $correoRemitente) ==4){
        return $info_fe;
        }else{
        $correoHost = "smtp.camagare.com";
        $correoPass = "CmGr1980";
        $correoPort = "587";
        $correoRemitente = "facturacion@camagare.com";
        
        $info_fe=array('correo_port'=> $correoPort, 'correo_host'=> $correoHost, 'correo_pass'=> $correoPass, 'correo_remitente'=> $correoRemitente); 
        return $info_fe;
        }
        
    }



    //Elimina exceso de espacios entre palabras
    function strClean($strCadena){
        $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $strCadena);
        $string = trim($string); //Elimina espacios en blanco al inicio y al final
        $string = stripslashes($string); // Elimina las \ invertidas
        $string = str_ireplace("<script>","",$string);
        $string = str_ireplace("</script>","",$string);
        $string = str_ireplace("<script src>","",$string);
        $string = str_ireplace("<script type=>","",$string);
        $string = str_ireplace("SELECT * FROM","",$string);
        $string = str_ireplace("DELETE FROM","",$string);
        $string = str_ireplace("INSERT INTO","",$string);
        $string = str_ireplace("SELECT COUNT(*) FROM","",$string);
        $string = str_ireplace("DROP TABLE","",$string);
        $string = str_ireplace("OR '1'='1","",$string);
        $string = str_ireplace('OR "1"="1"',"",$string);
        $string = str_ireplace('OR ´1´=´1´',"",$string);
        $string = str_ireplace("is NULL; --","",$string);
        $string = str_ireplace("is NULL; --","",$string);
        $string = str_ireplace("LIKE '","",$string);
        $string = str_ireplace('LIKE "',"",$string);
        $string = str_ireplace("LIKE ´","",$string);
        $string = str_ireplace("OR 'a'='a","",$string);
        $string = str_ireplace('OR "a"="a',"",$string);
        $string = str_ireplace("OR ´a´=´a","",$string);
        $string = str_ireplace("OR ´a´=´a","",$string);
        $string = str_ireplace("--","",$string);
        $string = str_ireplace("^","",$string);
        $string = str_ireplace("[","",$string);
        $string = str_ireplace("]","",$string);
        $string = str_ireplace("==","",$string);
        $string = str_ireplace("'","",$string);
        return $string;
    }

    function clear_cadena(string $cadena){
        //Reemplazamos la A y a
        $cadena = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $cadena
        );
 
        //Reemplazamos la E y e
        $cadena = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $cadena );
 
        //Reemplazamos la I y i
        $cadena = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $cadena );
 
        //Reemplazamos la O y o
        $cadena = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $cadena );
 
        //Reemplazamos la U y u
        $cadena = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $cadena );
 
        //Reemplazamos la N, n, C y c
        $cadena = str_replace(
        array('Ñ', 'ñ', 'Ç', 'ç',',','.',';',':'),
        array('N', 'n', 'C', 'c','','','',''),
        $cadena
        );
        return $cadena;
    }

    //Genera un token
    function token()
    {
        $r1 = bin2hex(random_bytes(10));
        $r2 = bin2hex(random_bytes(10));
        $r3 = bin2hex(random_bytes(10));
        $r4 = bin2hex(random_bytes(10));
        $token = $r1.'-'.$r2.'-'.$r3.'-'.$r4;
        return $token;
    }
    
    function responsable_translado(){
        $responsable = array(['codigo'=>'1', 'nombre'=>"Asesor"],
                                ['codigo'=>'2', 'nombre'=>"Logística"],
                                ['codigo'=>'3', 'nombre'=>"Oficina"]);
        return $responsable;
    }

    function Meses(){
        $meses = array(['codigo'=>'01', 'nombre'=>"Enero"],
        ['codigo'=>'02', 'nombre'=>"Febrero"],
        ['codigo'=>'03', 'nombre'=>"Marzo"],
        ['codigo'=>'04', 'nombre'=>"Abril"],
        ['codigo'=>'05', 'nombre'=>"Mayo"],
        ['codigo'=>'06', 'nombre'=>"Junio"],
        ['codigo'=>'07', 'nombre'=>"Julio"],
        ['codigo'=>'08', 'nombre'=>"Agosto"],
        ['codigo'=>'09', 'nombre'=>"Septiembre"],
        ['codigo'=>'10', 'nombre'=>"Octubre"],
        ['codigo'=>'11', 'nombre'=>"Noviembre"],
        ['codigo'=>'12', 'nombre'=>"Diciembre"]);
        return $meses;
    }

    function anios($up, $down){
    //anos arriba al actual, y anos abajo al actual
    $hoy=date("Y");
    $anios =array();
    $down=$down+1;
        for ($i=$hoy+$up; $i > $hoy-$down; $i--){
            $anios[] =$i;
        }
        return $anios;
    }

function passGenerator($lenght=10){
        $pass="";
    $longitudPass=$lenght;
    $cadena="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
    //$cadena="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
    $longitudCadena=strlen($cadena);

    for($i=1; $i<=$longitudPass; $i++){
        $pos=rand(0,$longitudCadena-1);
        $pass.=substr($cadena,$pos,1);
    }
    return $pass;
    }

    function numAleatorio($lenght=5){
    $pass="";
    $longitudPass=$lenght;
    $cadena="123456789";
    $longitudCadena=strlen($cadena);

    for($i=1; $i<=$longitudPass; $i++){
        $pos=rand(0,$longitudCadena-1);
        $pass.=substr($cadena,$pos,1);
    }
    return $pass;
    }

    //para sacar el formato d valor monetario con decimales
function formatMoney($cantidad, $decimales){
    $cantidad=number_format($cantidad,$decimales,".",",");
    return $cantidad;  
}

 
function encrypt_decrypt($action, $string)
{
    $output = false;
   // global $encryption_method;
    // Pull the hashing method that will be used
    // Hash the password
    $secret_key = 'AA74CDCC2BBRT935136HH7B63C27';
    $key = hash('sha256', $secret_key);
    if ($action == 'encrypt') {
        // Generate a random string, hash it and get the first 16 character of the hashed string which will be ised as the IV
        /*
        $str = "qwertyuiopasdfghjklzxcvbnm,./;'\\[]-=`!@#\$%^&*()_+{}|\":?><0123456789QWERTYUIOPASDFGHJKLZXCVBNM";
        */
        $str = "0123456789QWERTYUIOPASDFGHJKLZXCVBNM";
        $shuffled = str_shuffle($str);
        $iv = substr(hash('sha256', $shuffled), 0, 16);
        $output = openssl_encrypt($string, "AES-256-CBC", $key, 0, $iv);
        $output = base64_encode($output);
        // Tidy up the string so that it survives the transport 100%
        $ivoutput = $iv . $output;
        // Concat the IV with the encrypted message
        return $ivoutput;
    } else {
        if ($action == 'decrypt') {
            $iv = substr($string, 0, 16);
            // Extract the IV from the encrypted string
            $string = substr($string, 16);
            // The rest of the encrypted string is the message
            $output = openssl_decrypt(base64_decode($string), "AES-256-CBC", $key, 0, $iv);
            return $output;
        }
    }
}


function validador_ruc($validaRuc)
{			
    if(is_numeric($validaRuc)){ 
    $total_caracteres=strlen($validaRuc);// se suma el total de caracteres
    $array_ruc=str_split($validaRuc);//para hacer array el numero de ruc
    if($total_caracteres==13){//compruebo que tenga 13 digitos el ruc
    //para calcular el ruc de una persona natural
    /*
    if ($array_ruc['2'] < 6 ){
        $value = validador_cedula(substr($validaRuc, 0, 10));	
    return $value;
    }
    //para calcular el ruc de una persona juridica
        
    if ($array_ruc['2'] == 9) {	
    $suma = (($array_ruc['0']*4) + ($array_ruc['1']*3) + ($array_ruc['2']*2) + ($array_ruc['3']*7) + ($array_ruc['4']*6) + ($array_ruc['5']*5) + ($array_ruc['6']*4) + ($array_ruc['7']*3) + ($array_ruc['8']*2)) ;
    $total = ($suma/11);	//11 es el modulo para calcular persona juridica
    $decimales = explode('.',$total);
    $entero = $decimales[0];
    $residuo = ($suma - 11 * $entero) ;
    $digito_verificador = ($residuo == 0) ? 0 : (11 - $residuo); 
    $value = ($array_ruc['9'] == $digito_verificador)? 'true' : 'false' ;					
    return $value;
    }
    //para calcular el ruc de una persona juridica publica
    if ($array_ruc['2'] == 6) {			
    $suma = (($array_ruc['0']*3) + ($array_ruc['1']*2) + ($array_ruc['2']*7) + ($array_ruc['3']*6) + ($array_ruc['4']*5) + ($array_ruc['5']*4) + ($array_ruc['6']*3) + ($array_ruc['7']*2) + ($array_ruc['8']*0)) ;
    $total = ($suma/11);	//11 es el modulo para calcular persona juridica
    $decimales = explode('.',$total);
    $entero = $decimales[0];
    $residuo = ($suma - 11 * $entero) ;
    $digito_verificador = ($residuo == 0) ? 0 : (11 - $residuo); 
    $value = ($array_ruc['8'] == $digito_verificador)? 'true' : 'false' ;					
    return $value;
    }
    */
    
    }else{
    $value="false";
    return $value;
    }
    }else{
    $value="false";
    return $value;
    }

}

function validador_cedula($validaCedula)
{
	$array_cedula=array();
    $suma_pares=0;
    $array_cedula=str_split($validaCedula);//para hacer array el numero de cedula
    
    if(is_numeric($validaCedula)){ 
    $total_caracteres=strlen($validaCedula);// se suma el total de caracteres
    if($total_caracteres==10){//compruebo que tenga 10 digitos la cedula
    $nro_region=substr($validaCedula, 0,2);//extraigo los dos primeros caracteres de izq a der
    if($nro_region>=1 && $nro_region<=24){// compruebo a que region pertenece esta cedula//
    $ult_digito=substr($validaCedula, -1,1);//extraigo el ultimo digito de la cedula
    //extraigo los valores pares//
    $suma_pares=$array_cedula['1'] + $array_cedula['3'] + $array_cedula['5'] + $array_cedula['7'];

    //extraigo los valores impares//
    $array_impares=array('0'=>$array_cedula['0'],'1'=>$array_cedula['2'],'2'=>$array_cedula['4'],'3'=>$array_cedula['6'],'4'=>$array_cedula['8']);

    $suma_impares=0;
        for ($i=0; $i<count($array_impares);$i++){
            
            if(($array_impares[$i]*2)>9){
                $suma_impares = $suma_impares + ($array_impares[$i]*2)-9;
            }else{
                $suma_impares = $suma_impares + ($array_impares[$i]*2);
            }
        }
    
    $suma=($suma_pares + $suma_impares);

    $dis=substr($suma, 0,1);//extraigo el primer numero de la suma
    $dis=(($dis + 1)* 10);//luego ese numero lo multiplico x 10, consiguiendo asi la decena inmediata superior
    $digito=($dis - $suma);
    $digito==10?$digito='0':$digito=$digito;//si la suma nos resulta 10, el decimo digito es cero
    
    
    if ($digito==$ult_digito){//comparo los digitos final y ultimo
    
    $value="true";
    return $value;
    
    }else{
    $value="false";
    return $value;
    }


    }else{
    $value="false";
    return $value;
    }

    }else{
    $value="false";
    return $value;
    }

    }else{
    $value= "false";
    return $value;
    }

}

//tipos de ambientes para emision de comprobantes electronicos
function tipo_ambiente(){
    $result = array(['codigo'=>1,'nombre'=>'Pruebas'],['codigo'=>2,'nombre'=>'Producción']);
    return $result;
}

//tipo de identificacion para ventas
function identificacion_venta(){
    $result = array(['codigo'=>'04','nombre'=>'RUC'],
                    ['codigo'=>'05','nombre'=>'CEDULA'],
                    ['codigo'=>'06','nombre'=>'PASAPORTE'],
                    ['codigo'=>'07','nombre'=>'VENTA A CONSUMIDOR FINAL']);
    return $result;
}

//tipo de identificacion para compras
function identificacion_compra(){
    $result = array(['codigo'=>'01','nombre'=>'RUC'],
                    ['codigo'=>'02','nombre'=>'CEDULA'],
                    ['codigo'=>'03','nombre'=>'PASAPORTE/IDENTIFICACION DEL EXTERIOR']);
    return $result;
}

//tipo de tarjetas de credito
function tarjetas_de_credito(){
    $result = array(['codigo'=>'01','nombre'=>'AMERICAN EXPRESS'],
                    ['codigo'=>'02','nombre'=>'DINERS CLUB'],
                    ['codigo'=>'04','nombre'=>'MASTERCARD'],
                    ['codigo'=>'05','nombre'=>'VISA'],
                    ['codigo'=>'07','nombre'=>'OTRA TARJETA']);
    return $result;
}

//tipo de identificacion para LIQUIDACIONES DE COMPRAS, NOTAS DE CREDITO, NOTAS DE DEBITO, Y RETENCIONES
function identificacion_lc_nc_nd_ret(){
    $result  = array(['codigo'=>'04','nombre'=>'RUC'],
                                   ['codigo'=>'05','nombre'=>'CEDULA'],
                                   ['codigo'=>'06','nombre'=>'PASAPORTE'],
                                   ['codigo'=>'08','nombre'=>'IDENTIFICACION DEL EXTERIOR']);
    return $result;
}

//TIPOS DE IMPUESTOS
function impuesto(){
    $result = array(['codigo'=>2,'impuesto'=>'IVA'],['codigo'=>3,'impuesto'=>'ICE'],['codigo'=>5,'impuesto'=>'IRBPNR']);
    return $result;
}
//TARIFA IVA
function tarifa_iva(){
    $result = array(['codigo'=>0,'porcentaje'=>'0%'],
                    ['codigo'=>2,'porcentaje'=>'12%'],
                    ['codigo'=>6,'porcentaje'=>'No Objeto de Impuesto'],
                    ['codigo'=>7,'porcentaje'=>'Exento de IVA']);
    return $result;
}

//impuestos a retener
function impuesto_a_retener(){
    $result = array(['codigo'=>1,'impuesto'=>'RENTA'], ['codigo'=>2,'impuesto'=>'IVA'], ['codigo'=>6,'impuesto'=>'ISD']);
    return $result;
}

// denominacion para crear nuevos productos servicios o activos fijos
function tipo_producto(){
    $result = array(['codigo'=>'01','descripcion'=>'PRODUCTO'],['codigo'=>'02','descripcion'=>'SERVICIO'],['codigo'=>'03','descripcion'=>'ACTIVO FIJO']);
    return $result;
}

//formas de pagos ventas
function formas_de_pago(){
    $result = array(['codigo'=>'01','nombre'=>'SIN UTILIZACION DEL SISTEMA FINANCIERO'],
                    ['codigo'=>'15','nombre'=>'COMPENSACIÓN DE DEUDAS'],
                    ['codigo'=>'16','nombre'=>'TARJETA DE DÉBITO'],
                    ['codigo'=>'17','nombre'=>'DINERO ELECTRÓNICO'],
                    ['codigo'=>'18','nombre'=>'TARJETA PREPAGO'],
                    ['codigo'=>'19','nombre'=>'TARJETA DE CRÉDITO'],
                    ['codigo'=>'20','nombre'=>'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO'],
                    ['codigo'=>'21','nombre'=>'ENDOSO DE TÍTULOS']);
                    return $result;
}

//novedades sueldos LAS LETRAS SON  referencia para los calculos en la quincena o el rol
function novedades_sueldos(){
    $result = array(['codigo'=>'1','nombre'=>'Otros Ingresos'],
                    ['codigo'=>'2','nombre'=>'Descuento'],
                    ['codigo'=>'3','nombre'=>'Anticípo'],
                    ['codigo'=>'4','nombre'=>'Horas Nocturnas'],
                    ['codigo'=>'5','nombre'=>'Horas Suplementarias'],
                    ['codigo'=>'6','nombre'=>'Horas Extraordinarias'],
                    ['codigo'=>'7','nombre'=>'Préstamo Quirografario'],
                    ['codigo'=>'8','nombre'=>'Préstamo hipotecario'],
                    ['codigo'=>'9','nombre'=>'Préstamo Empresa'],
                    ['codigo'=>'10','nombre'=>'Días no laborados'],
                    ['codigo'=>'14','nombre'=>'Aviso de salida']);
                    return $result;
}

function motivo_salida_iess(){
    $motivo = array(['codigo'=>'T', 'nombre'=>"Terminación del contrato"],
                    ['codigo'=>'V', 'nombre'=>"Renuncia voluntaria"],
                    ['codigo'=>'B', 'nombre'=>"Visto bueno"],
                    ['codigo'=>'R', 'nombre'=>"Despido unilateral por parte del empleador"],
                    ['codigo'=>'S', 'nombre'=>"Suspensión de partida"],
                    ['codigo'=>'D', 'nombre'=>"Desaparición del puesto dentro de la estructura de la empresa"],
                    ['codigo'=>'I', 'nombre'=>"Incapacidad permanente del trabajador"],
                    ['codigo'=>'F', 'nombre'=>"Muerte del trabajador"],
                    ['codigo'=>'A', 'nombre'=>"Abandono voluntario"]);
    return $motivo;
}

?>
