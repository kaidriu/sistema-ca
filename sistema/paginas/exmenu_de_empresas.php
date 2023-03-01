<html lang="es">
<head>
<style type="text/css">
ul li ul {
   display: none;
}
</style>
<title>Menú</title>
<link rel="shortcut icon" type="image/png" href="../image/logofinal.png"/>
		<script src="../js/menu_responsive.js"></script>
		<?php include("../head.php");?>
		<script src="../js/cerrar_sesion.js"></script>
</head>
<?php
include("../conexiones/conectalogin.php");
include_once("../helpers/helpers.php");
ini_set('date.timezone','America/Guayaquil');
//include("../includes/cierre_sesion.php");
$conexion = conenta_login();
//aqui se hace la sesion de usuario de empresas cuando se da clic en la empresa con la que se desea trabajar
if (isset($_POST['id_usuario']) && isset($_POST['id_empresa']) && isset($_POST['ruc_empresa']) ){
	session_destroy();
	session_start();
	$_SESSION['id_usuario'] = $_POST['id_usuario'];
	$_SESSION['id_empresa'] = $_POST['id_empresa'];
	$_SESSION['ruc_empresa'] = $_POST['ruc_empresa'];
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa = $_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$_SESSION["ultimoAcceso"] = date("Y-n-j H:i:s");
}

//cuando se da clic en un iten del submenu
if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa']) ){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa = $_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	$_SESSION["ultimoAcceso"] = date("Y-n-j H:i:s");
}

if (!isset($_SESSION['nivel'])){
		session_start();
}
if($_SESSION['nivel'] >= 1 && isset($id_usuario) && isset($id_empresa) && isset($ruc_empresa)){
	//para nombre de empresa
	$sql_nombre_empresa = mysqli_query($conexion, "SELECT * FROM empresas WHERE ruc = '".$ruc_empresa."' ;");
	$nombre_empresa = mysqli_fetch_array($sql_nombre_empresa);
	$nombre_compania = $nombre_empresa['nombre_comercial'];
	//para nombre de usuario
	$sql_usuario = mysqli_query($conexion, "SELECT * FROM usuarios  WHERE id = '".$id_usuario."' ;");
	$nombre_usuario = mysqli_fetch_array($sql_usuario);
	$nombre_usuario = $nombre_usuario['nombre'];
	//para limpiar los temporales de ese usuario
	$delete_factura_tmp = mysqli_query($conexion, "DELETE FROM factura_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_adicional_tmp = mysqli_query($conexion, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_propina_tasa_tmp = mysqli_query($conexion, "DELETE FROM propina_tasa_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_forma_pago_tmp = mysqli_query($conexion, "DELETE FROM forma_pago_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_ingresos_egresos_tmp = mysqli_query($conexion, "DELETE FROM ingresos_egresos_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_asientos_automaticos_tmp = mysqli_query($conexion, "DELETE FROM asientos_automaticos_tmp WHERE ruc_empresa = '".$ruc_empresa."'");
	$delete_balances_tmp = mysqli_query($conexion, "DELETE FROM balances_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_compra_tmp = mysqli_query($conexion, "DELETE FROM compra_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_contabilizar_tmp = mysqli_query($conexion, "DELETE FROM contabilizar_documentos_tmp WHERE ruc_empresa = '".$ruc_empresa."'");
	$delete_diario_tmp = mysqli_query($conexion, "DELETE FROM detalle_diario_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_existencias_inventario_tmp = mysqli_query($conexion, "DELETE FROM existencias_inventario_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_propina_tasa_tmp = mysqli_query($conexion, "DELETE FROM propina_tasa_tmp WHERE id_usuario = '".$id_usuario."'");
	$delete_retencion_tmp = mysqli_query($conexion, "DELETE FROM retencion_tmp WHERE id_usuario = '".$id_usuario."'");
	/*
	algunos temporales que estan sin id_usuario no se puede eliminar porque trabajan en base a ruc de empresa
	*/
	?>
	<input type="hidden" value="<?php echo $id_usuario;?>" id="id_usuario_accion">
	<input type="hidden" value="<?php echo $ruc_empresa;?>" id="ruc_empresa_accion">
	<?php
	
	//para mostrar aviso a las empresas
	$result_avisos_camagare = mysqli_query($conexion,"SELECT * FROM avisos_camagare WHERE ruc_empresa='".$ruc_empresa."'");
	$aviso_a_mostrar=array();
	while ($row_avisos_camagare = mysqli_fetch_array($result_avisos_camagare)){
		$aviso_mostrado=$row_avisos_camagare['detalle_aviso'];
		$aviso_a_mostrar[]=$aviso_mostrado." ";
	}
	?>
<body style="background: url('../image/logo.jpg'); background-size: 300px 300px; background-repeat: no-repeat ; background-position: center; background-color: hsla(146, 38%, 33%, 0.67);  background-attachment: fixed; padding: 2px;">

	<!-- class="navbar navbar-default sidebar" -->
	
<nav id="barra_menu" class="navbar navbar-default navbar-fixed-top" role="navigation" >
	<li class="list-group-item active" style="font-family:Georgia,serif,Times; font-size:12px; padding:0px; opacity:0.8; ">
	<div class="row">
	<div class="col-xs-4 text-left">
	<span class="glyphicon glyphicon-briefcase"></span> <?php echo ucwords(strtolower($nombre_compania)) ?>
	</div>
	<div class="col-xs-4 text-center">
	<span><marquee><?php foreach ($aviso_a_mostrar as $aviso){ echo $aviso;}?></marquee></span>
	</div>
	<div class="col-xs-4 text-right">
	<span class="glyphicon glyphicon-user"></span> <?php echo ucwords(strtolower($nombre_usuario)) ?>
	</div>
	</div>
	</li>
	
	<div class="container-fluid" style="text-align:left; margin-left: 10px;">
    <div class="navbar-header">
	<Button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1"><span class="glyphicon glyphicon-list-alt"></span> Menú</button>  
    </div>

    <div class="collapse navbar-collapse" id="bs-sidebar-navbar-collapse-1" style="font-family: Tahoma, Geneva, sans-serif; font-size:13px; padding: 0px;">
      <ul class="nav navbar-nav" style="margin-bottom: -6px; margin-top: -6px; margin-left: 5px;">
	  <li><a href="../index.php?menu=true" title="Menú inicial" onmouseover="this.style.color='blue';" onmouseout="this.style.color='black';"><span class="glyphicon glyphicon-home"></span> Inicio</a></li>
			<?php
				   // para cargar los modulos
					$sql_modulos = mysqli_query($conexion, "SELECT * FROM modulos_asignados mod_asi, modulos_menu mod_menu, iconos_bootstrap ico_boo WHERE mod_asi.id_usuario = '".$id_usuario."' and mod_asi.id_empresa = '".$id_empresa."' and mod_asi.id_modulo = mod_menu.id_modulo and ico_boo.id_icono=mod_menu.id_icono group by mod_asi.id_modulo order by mod_menu.nombre_modulo asc;");
						while($datos_modulos = mysqli_fetch_array($sql_modulos)){
						 $nombre_modulo = $datos_modulos['nombre_modulo'];
						 $icono_modulo = $datos_modulos['nombre_icono'];
						 $id_modulo = $datos_modulos['id_modulo'];
						 ?>
							<li class="dropdown">
							<a href="#" class="dropdown-toggle" id="menu_principal" style="border-radius: 2px;" data-toggle="dropdown" onmouseover="this.style.color='blue';" onmouseout="this.style.color='';"><span class="<?php echo  $icono_modulo ?>"></span> <?php echo  $nombre_modulo; ?> <span class="caret"></span></a> 
							  <ul role="menu" class="dropdown-menu scrollable-menu" style="width: 250px; padding: 0px; border-radius: 2px; margin-top: -6px; text-align:left; height: auto; max-height: 300px; overflow-x: hidden;">
								<?php
								//para los submodulos				
								$submodulos = mysqli_query($conexion, "SELECT * FROM modulos_asignados WHERE id_usuario = '".$id_usuario."' and id_empresa = '".$id_empresa."' and id_modulo = '".$id_modulo."' order by id_submodulo asc ");
								while($datos_id_submodulos = mysqli_fetch_assoc($submodulos)){
									$id_sudmodulo =$datos_id_submodulos['id_submodulo'];
									$respuesta_submodulos = mysqli_query($conexion, "SELECT * FROM submodulos_menu sub_menu, iconos_bootstrap ico_boo WHERE sub_menu.id_submodulo = '".$id_sudmodulo."' and sub_menu.id_icono=ico_boo.id_icono ");
									$datos_nombre_submodulos = mysqli_fetch_array($respuesta_submodulos);
									$nombre_sudmodulo =$datos_nombre_submodulos['nombre_submodulo'];
									$ruta_sudmodulo =$datos_nombre_submodulos['ruta'];
									$icono_sudmodulo =$datos_nombre_submodulos['nombre_icono'];
									?>
									<li><a href="<?php echo $ruta_sudmodulo ?>" onmouseover="this.style.color='blue';" onmouseout="this.style.color='';" id="sub_menu_principal" class="dropdown-item" style ="border-radius: 1px; opacity:0.9; padding: 5px;" ><span class="<?php echo $icono_sudmodulo ?>"></span> <?php echo $nombre_sudmodulo ?></a></li>
									<?php
								}
								?>
							  </ul>
							</li>
						<?php
						}
			
					?>
	  <li><a href="../includes/logout.php" title="Cerrar sesión" onmouseover="this.style.color='red';" onmouseout="this.style.color='black';"><span class="glyphicon glyphicon-off"></span> Salir</a></li>						
	  <!--
	  <li>
        <form class="navbar-form navbar-right">
          <input type="text" class="form-control" style="height: 28px; margin-top: 4px;" id="buscar_modulos_asignados" onkeyup='buscar_modulos();' placeholder="Buscar...">
	  </form>
	  </li>-->
	</ul>
    </div>
  </div>
</nav>

<div id="loader_accion"></div><!-- Carga los datos ajax -->
<div class='outer_div_accion'></div><!-- Carga los datos ajax -->
<div id="pantalla"></div>
	<!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
	<link rel="stylesheet" href="../css/jquery-ui.css">
	<script src="../js/jquery-ui.js"></script>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>
</body>
<?php
}else{
header('Location: ../includes/logout.php');
exit;
}
?>
</html>
<style>
.fixedHeight {
        padding: 1px;
		max-height: 200px;
		overflow: auto;
    }
</style>
<script type="text/javascript">
//para ver que tipo de pantalla es y dependiendo de eso se hace el espacio entre el menu y los modulos
	var numero_modulos = $("#total_modulos").val();
	var alto_barra_menu = $("#barra_menu").height()+5;
	//alert(barra_menu_alto);
	$('#pantalla').html('<li class="list-group-item active" style="height:'+alto_barra_menu+'px; opacity:0"></li>');
	/*
	if (barra_menu < 80){
		$('#pantalla').html('<li class="list-group-item active" style="padding:32px; opacity:0"></li>');					    		
	}else{
		$('#pantalla').html('<li class="list-group-item active" style="padding:58px; opacity:0"></li>');					    		
	}
	*/

$(document).ready(function() {
	//para guardar la accion del clic dado
	$('body #bs-sidebar-navbar-collapse-1').on('click', 'a', function(){
        var destino= $(this).attr('href');
		var ruc_empresa= $("#ruc_empresa_accion").val();;
		var id_usuario= $("#id_usuario_accion").val();
		$.ajax({
				url:'../ajax/opciones_acciones_usuarios.php?action=acciones_menu_empresas&origen=menu_empresa&ruc_empresa='+ruc_empresa+'&id_usuario='+id_usuario+'&destino='+destino,
				 beforeSend: function(objeto){
				 $('#loader_accion').html('');
			  },
				success:function(data){
					$(".outer_div_accion").html(data).fadeIn('slow');
				}
			});
      })
	  
   // Muestra y oculta los menús al pasar el mouse por arriba
   $('ul li:has(ul)').hover(
      function(e)
      {
         $(this).find('ul').css({display: "block"});
		 
      },
      function(e)
      {
         $(this).find('ul').css({display: "none"});
      }
   );
});

//para buscar los modulos desde el buscador de la barra del menu
function buscar_modulos(){
		$("#buscar_modulos_asignados").autocomplete({
			source:'../ajax/modulos_autocompletar.php',
			minLength: 2,
			select: function(event, ui) {
				event.preventDefault();
				$('#buscar_modulos_asignados').val(ui.item.nombre);
				window.location.href = ui.item.ruta;
			}
		});
				
		$( "#buscar_modulos_asignados" ).autocomplete("widget").addClass("fixedHeight");//para que aparezca la barra de desplazamiento en el buscar
				
		$("#buscar_modulos_asignados" ).on( "keydown", function( event ) {
			if (event.keyCode== $.ui.keyCode.UP || event.keyCode== $.ui.keyCode.DOWN || event.keyCode== $.ui.keyCode.DELETE )
			{
				$("#buscar_modulos_asignados" ).val("");				
			}
	});	
}


</script>