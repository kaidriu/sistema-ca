<?php
require_once("../conexiones/conectalogin.php");
require_once("../validadores/valida_varios_mails.php");
require_once("../validadores/ruc.php");
require_once("../validadores/cedula.php");
require_once("../ajax/pagination.php"); //include pagination file
require_once("../helpers/helpers.php"); //include pagination file
$con = conenta_login();
session_start();
$ruc_empresa = $_SESSION['ruc_empresa'];
$id_usuario = $_SESSION['id_usuario'];
ini_set('date.timezone', 'America/Guayaquil');
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

if ($action == 'eliminar_cliente') {
    $id_cliente=intval($_POST['id_cliente']);
    //CONTAR CUANTOS CLIENTES HAY PARA ELIMINAR
    $query_clientes=mysqli_query($con, "select ruc from clientes where id='".$id_cliente."'");
    $resultado_clientes=mysqli_fetch_array($query_clientes);
    $ruc_cliente=$resultado_clientes['ruc'];
    
    $query_contar_clientes=mysqli_query($con, "select * from clientes where ruc='".$ruc_cliente."'");
    $count_ruc=mysqli_num_rows($query_contar_clientes);
    
    $query_facturas_emitidas=mysqli_query($con, "select * from encabezado_factura where id_cliente='".$id_cliente."'");
    $count_facturas_emitidas=mysqli_num_rows($query_facturas_emitidas);
    
    if ($count_facturas_emitidas > 0){
        echo "<script>$.notify('No se puede eliminar. Existen registros realizados con este cliente.','error')</script>";
    }else{
            if ($deleteuno=mysqli_query($con,"DELETE FROM clientes WHERE id='".$id_cliente."'")){
                echo "<script>$.notify('Cliente eliminado.','success')</script>";
            } else{
                echo "<script>$.notify('Lo siento algo ha salido mal intenta nuevamente.','error')</script>";
            }
    
    }

 }

//buscar clientes
if ($action == 'buscar_clientes') {
    $query_comparten_clientes=mysqli_query($con, "select * from configuracion_facturacion where ruc_empresa ='".$ruc_empresa."' ");
    $row_comparten=mysqli_fetch_array($query_comparten_clientes);
    $comparte_clientes=$row_comparten['clientes'];
    
    if ($comparte_clientes=="SI"){
    $condicion_ruc_empresa=	"mid(ruc_empresa,1,12) = '". substr($ruc_empresa,0,12) ."'";
    }else{
    $condicion_ruc_empresa=	"ruc_empresa = '". $ruc_empresa ."'";
    }

    	
    // escaping, additionally removing everything that could be (html/javascript-) code
     $q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
     $ordenado = mysqli_real_escape_string($con,(strip_tags($_GET['ordenado'], ENT_QUOTES)));
     $por = mysqli_real_escape_string($con,(strip_tags($_GET['por'], ENT_QUOTES)));
     $aColumns = array('nombre','ruc','email','direccion','telefono');//Columnas de busqueda
     $sTable = "clientes";
     $sWhere = "WHERE $condicion_ruc_empresa";

     $text_buscar = explode(' ',$q);
     $like="";
     for ( $i=0 ; $i<count($text_buscar) ; $i++ )
     {
         $like .= "%".$text_buscar[$i];
     }

    if ( $_GET['q'] != "" )
    {
        $sWhere = "WHERE ($condicion_ruc_empresa AND ";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= $aColumns[$i]." LIKE '".$like."%' AND $condicion_ruc_empresa OR ";
        }
        $sWhere = substr_replace( $sWhere, "AND $condicion_ruc_empresa ", -3 );
        $sWhere .= ')';
    }
    $sWhere.=" order by $ordenado $por";
    
    
    //pagination variables
    $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
    $per_page = 20; //how much records you want to show
    $adjacents  = 4; //gap between pages after number of adjacents
    $offset = ($page - 1) * $per_page;
    //Count the total number of row in your table*/
    $count_query   = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
    $row= mysqli_fetch_array($count_query);
    $numrows = $row['numrows'];
    $total_pages = ceil($numrows/$per_page);
    $reload = '../clientes.php';
    //main query to fetch the data
    $sql="SELECT * FROM  $sTable $sWhere LIMIT $offset,$per_page";
    $query = mysqli_query($con, $sql);
    //loop through fetched data
    if ($numrows>0){
        
        ?>
        <div class="panel panel-info">
        <div class="table-responsive">
          <table class="table">
            <tr  class="info">
                <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("nombre");'>Nombre</button></th>
                <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("ruc");'>Ruc/Cedula</button></th>
                <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("telefono");'>Teléfono</button></th>
                <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("email");'>Email</button></th>
                <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("direccion");'>Dirección</button></th>
                <th style ="padding: 0px;"><button style ="border-radius: 0px; border:0;" class="list-group-item list-group-item-info" onclick='ordenar("plazo");'>Crédito</button></th>
                <th class='text-right'>Opciones</th>				
            </tr>
            <?php
            while ($row=mysqli_fetch_array($query)){
                    $id_cliente=$row['id'];
                    $nombre_cliente=strtoupper($row['nombre']);
                    $ruc_cliente=$row['ruc'];
                    $telefono_cliente=$row['telefono'];
                    $email_cliente=strtolower($row['email']);
                    $direccion_cliente=strtoupper($row['direccion']);
                    $tipo_id=$row['tipo_id'];
                    $plazo=$row['plazo'];
                    $provincia=$row['provincia'];
                    $ciudad=$row['ciudad'];
                ?>
                <input type="hidden" value="<?php echo $nombre_cliente;?>" id="nombre_cliente<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $ruc_cliente;?>" id="ruc_cliente<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $telefono_cliente;?>" id="telefono_cliente<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $email_cliente;?>" id="email_cliente<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $direccion_cliente;?>" id="direccion_cliente<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $tipo_id;?>" id="tipo_id_cliente<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $plazo;?>" id="plazo_pago<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $provincia;?>" id="provincia<?php echo $id_cliente;?>">
                <input type="hidden" value="<?php echo $ciudad;?>" id="ciudad<?php echo $id_cliente;?>">
                <tr>						
                    <td><?php echo $nombre_cliente; ?></td>
                    <td><?php echo $ruc_cliente; ?></td>
                    <td><?php echo $telefono_cliente; ?></td>
                    <td><?php echo $email_cliente;?></td>
                    <td><?php echo $direccion_cliente;?></td>
                    <td><?php echo $plazo." Días";?></td>
                <td ><span class="pull-right">
                <a href="#" class="btn btn-info btn-xs" title="Editar cliente" onclick="editar_cliente('<?php echo $id_cliente;?>');" data-toggle="modal" data-target="#nuevoCliente"><i class="glyphicon glyphicon-edit"></i></a> 
                <a href="#" class="btn btn-danger btn-xs" title="Eliminar cliente" onclick="eliminar_cliente('<?php echo $id_cliente;?>');"><i class="glyphicon glyphicon-trash"></i></a> 	
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="8"><span class="pull-right">
                <?php
                 echo paginate($reload, $page, $total_pages, $adjacents);
                ?></span></td>
            </tr>
          </table>
        </div>
        </div>
        <?php
    }

}

//guardar o editar clientes
if ($action == 'guardar_cliente') {
    $id_cliente = intval($_POST['id_cliente']);
    $tipo_id = $_POST['tipo_id'];
    if($tipo_id=="07"){
        $ruc_cliente = "9999999999999";
        $nombre_cliente = "CONSUMIDOR FINAL";
    }else{
        $ruc_cliente = strClean($_POST['ruc']);
        $nombre_cliente = strClean($_POST['nombre']); 
    }

    if($ruc_cliente == "9999999999999"){
        $tipo_id = "07"; 
    }

    $email_cliente = strClean($_POST['email']);
    $direccion_cliente = strClean($_POST['direccion']);
    $telefono_cliente = strClean($_POST['telefono']);
    $plazo_cliente = intval($_POST['plazo']);
    $provincia = $_POST['provincia'];
    $ciudad = $_POST['ciudad'];

    if (empty($ruc_cliente)) {
        echo "<script>
            $.notify('Ingrese número de identificación','error');
            </script>";
    } else if ($tipo_id=="05" && validacedula($ruc_cliente)!="cedula correcta") {
    echo "<script>
        $.notify('Cedula Incorrecta','error');
        </script>";
    } else if ($tipo_id=="04" && validaRuc($ruc_cliente)!="correcto") {
    echo "<script>
        $.notify('Ruc Incorrecto','error');
        </script>";
    } else if (empty($nombre_cliente)) {
        echo "<script>
        $.notify('Ingrese nombre del cliente','error');
        </script>";   
    } else if (empty($email_cliente)) {
        echo "<script>
        $.notify('Ingrese émail del cliente','error');
        </script>";
    } else if (validar_mails($email_cliente)=='error') {
        echo "<script>
        $.notify('Error en mail, puede ingresar varios correos separados por coma y espacio','error');
        </script>";    
    } else if ($plazo_cliente <0) {
        echo "<script>
        $.notify('Ingrese días de plazo','error');
        </script>";
    } else {
        if (empty($id_cliente)) {
            $busca_cliente = mysqli_query($con, "SELECT * FROM clientes WHERE ruc = '".$ruc_cliente."' and ruc_empresa = '".$ruc_empresa."'");
            $count = mysqli_num_rows($busca_cliente);
            if ($count > 0) {
                echo "<script>
                $.notify('El cliente ya esta registrado','error');
                </script>";
            }else{
            $guarda_cliente = mysqli_query($con, "INSERT INTO clientes (ruc_empresa,
                                                                        nombre,
                                                                        tipo_id,
                                                                        ruc,
                                                                        telefono,
                                                                        email,
                                                                        direccion,
                                                                        fecha_agregado,
                                                                        plazo,
                                                                        id_usuario,
                                                                        provincia,
                                                                        ciudad)
                                                                            VALUES ('" . $ruc_empresa . "',
                                                                                    '" . $nombre_cliente . "',
                                                                                    '" . $tipo_id . "',
                                                                                    '" . $ruc_cliente . "',
                                                                                    '" . $telefono_cliente . "',
                                                                                    '" . $email_cliente . "',
                                                                                    '" . $direccion_cliente . "',
                                                                                    '".date("Y-m-d H:i:s")."',
                                                                                    '" . $plazo_cliente . "',
                                                                                    '" . $id_usuario . "',
                                                                                    '" . $provincia . "',
                                                                                    '" . $ciudad . "')");
               
               if($guarda_cliente){
               echo "<script>
                $.notify('Cliente registrado','success');
                document.querySelector('#guardar_cliente').reset();
                load(1);
                </script>";
               }else{
                echo "<script>
                $.notify('No se admite caracteres especiales','error');
                </script>";
               }
            }
        } else {
            //modificar el cliente
            $busca_cliente = mysqli_query($con, "SELECT * FROM clientes WHERE id != '".$id_cliente."' and ruc = '".$ruc_cliente."' and ruc_empresa = '".$ruc_empresa."'");
            $count = mysqli_num_rows($busca_cliente);
            if ($count > 0) {
                echo "<script>
                $.notify('El cliente ya esta registrado','error');
                </script>";
            }else{
            $update_cliente = mysqli_query($con, "UPDATE clientes SET nombre='" . $nombre_cliente . "',
                                                                        tipo_id='" . $tipo_id . "',
                                                                        ruc='" . $ruc_cliente . "',
                                                                        telefono='" . $telefono_cliente . "',
                                                                        email='" . $email_cliente . "',
                                                                        direccion='" . $direccion_cliente . "',
                                                                        fecha_agregado='".date("Y-m-d H:i:s")."',
                                                                    plazo='" . $plazo_cliente . "',
                                                                    id_usuario='" . $id_usuario . "',
                                                                    provincia='" . $provincia . "',
                                                                    ciudad='" . $ciudad . "'
                                                                    WHERE id='" . $id_cliente . "'");
                if($update_cliente){
                    echo "<script>
                    $.notify('Cliente actualizado','success');
                    setTimeout(function () {location.reload()}, 1000);
                        </script>";
                    }else{
                        echo "<script>
                        $.notify('No se admite caracteres especiales','error');
                        </script>";
                    }
                //setTimeout(function (){location.reload()}, 1000);
                }
        }
    }
}
    
?>