<?php
/* Connect To Database*/
include("../conexiones/conectalogin.php");
$con = conenta_login();
session_start();
$id_usuario = $_SESSION['id_usuario'];
$ruc_empresa = $_SESSION['ruc_empresa'];

$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
if($action == 'facturar'){
ini_set('date.timezone','America/Guayaquil');

		if (empty($_POST['sucursal_facturar'])) {
           $errors[] = "Seleccione sucursal de la cual desea facturar.";
		}else if (empty($_POST['mes_facturar'])) {
           $errors[] = "Seleccione mes";
		}else if (empty($_POST['anio_facturar'])) {
           $errors[] = "Seleccione año";
		}else if (empty($_POST['sucursal_alumno_facturar'])) {
           $errors[] = "Seleccione campus";
		}else if (empty($_POST['paralelo_alumno_facturar'])) {
           $errors[] = "Ingrese nivel.";
		}else if (empty($_POST['periodo_facturar'])) {
           $errors[] = "Seleccione un periodo que desea facturar.";
        } else if (!empty($_POST['sucursal_facturar']) && !empty($_POST['mes_facturar'])  && !empty($_POST['anio_facturar']) 
		&& !empty($_POST['sucursal_alumno_facturar']) && !empty($_POST['paralelo_alumno_facturar']) && !empty($_POST['periodo_facturar']))
		{

			$sucursal_facturar=mysqli_real_escape_string($con,(strip_tags($_POST["sucursal_facturar"],ENT_QUOTES)));
			$mes_facturar=mysqli_real_escape_string($con,(strip_tags($_POST["mes_facturar"],ENT_QUOTES)));
			$anio_facturar=mysqli_real_escape_string($con,(strip_tags($_POST["anio_facturar"],ENT_QUOTES)));
			$sucursal_alumno_facturar=mysqli_real_escape_string($con,(strip_tags($_POST["sucursal_alumno_facturar"],ENT_QUOTES)));
			$paralelo_alumno_facturar=mysqli_real_escape_string($con,(strip_tags($_POST["paralelo_alumno_facturar"],ENT_QUOTES)));
			$periodo_facturar=mysqli_real_escape_string($con,(strip_tags($_POST["periodo_facturar"],ENT_QUOTES)));
		ini_set('date.timezone','America/Guayaquil');
?>
 <form class="form-horizontal" method="POST" id="guardar_facturas" name="guardar_facturas">
<div class="panel panel-info">
   <div class="table-responsive">
   <table class="table">
  <tr class="info">
	<th>N</th>
	<th>ALUMNO</th>
	<th>A QUIEN FACTURAR</th>
	<th>VALORES</th>
	<th>DESCUENTO</th>
	<th>ESTADO</th>
	<th>FACTURAR</th>
</tr>
<?php

// PARA MOSTRAR LOS ESTUDIANTES A QUIENES SE LES VA A FACTURAR
	$sql_alumnos=mysqli_query($con, "SELECT al.id_cliente as id_cliente, al.id_alumno as id_alumno, cl.nombre as clientes, al.nombres_alumno as nombres, al.apellidos_alumno as apellidos FROM alumnos al, clientes cl WHERE al.ruc_empresa='$ruc_empresa' and al.sucursal_alumno = $sucursal_alumno_facturar and al.paralelo_alumno = $paralelo_alumno_facturar and al.estado_alumno='1' and al.id_cliente>0 and al.id_cliente= cl.id order by al.apellidos_alumno asc ");
	$numero=0;
	$suma_valores=0;
	while ($row=mysqli_fetch_array($sql_alumnos)){
	$id_alumno=$row["id_alumno"];
	$nombres_alumnos= $row['apellidos'] ." ". $row['nombres'];
	$id_cliente=$row['id_cliente'];
	$clientes=$row['clientes'];
	$numero = $numero + 1;

	//para mostrar los valores
			$suma_valores = array();
			$sql_valores=mysqli_query($con, "SELECT sum(pf.cant_producto * pf.precio_producto) as valores FROM detalle_por_facturar pf, productos_servicios ps WHERE ps.id=pf.id_producto and pf.id_referencia= $id_alumno and pf.cuando_facturar = '$periodo_facturar' and pf.ruc_empresa='$ruc_empresa' group by pf.id_producto");
			while ($row_valores=mysqli_fetch_array($sql_valores)){
	        $suma_valor = $row_valores['valores'];
			$suma_valores[]=$suma_valor;
			}
			$total_valores = $suma_valores;
			$resultado_suma=0; 
			Foreach ($total_valores as $valor){ 
			$resultado_suma=$resultado_suma+$valor; 
			} 
 //PARA CUANDO ES SOLO UNA VEZ
			$suma_valores_unavez = array();
			$sql_valores=mysqli_query($con, "SELECT sum(pf.cant_producto * pf.precio_producto) as valores FROM detalle_por_facturar pf, productos_servicios ps WHERE ps.id=pf.id_producto and pf.id_referencia= $id_alumno and pf.cuando_facturar = '03' and pf.ruc_empresa='$ruc_empresa' group by pf.id_producto");
			while ($row_valores=mysqli_fetch_array($sql_valores)){
	        $suma_valor = $row_valores['valores'];
			$suma_valores_unavez[]=$suma_valor;
			}
			$total_valores_unavez = $suma_valores_unavez;
			$resultado_suma_unavez=0; 
			Foreach ($total_valores_unavez as $valor_unavez){ 
			$resultado_suma_unavez=$resultado_suma_unavez+$valor_unavez; 
			}
	//para mostrar los descuentos
	$sql_descuentos=mysqli_query($con, "SELECT sum(valor_descuento) as valdes FROM descuentos_programados WHERE id_referencia= $id_alumno and mes_descuento = '$mes_facturar' and anio_descuento = '$anio_facturar' and ruc_empresa='$ruc_empresa'");
	$row_descuentos=mysqli_fetch_array($sql_descuentos);
	$valor_descontado=$row_descuentos['valdes'];
		
	//para ver si el cliente ya esta facturado ene este periodo
	$sql_cliente_facturado=mysqli_query($con, "SELECT * FROM encabezado_factura WHERE id_cliente= $id_cliente and month(fecha_factura) = '$mes_facturar' and year(fecha_factura) = '$anio_facturar' and ruc_empresa='$ruc_empresa'");
	$row_facturados = mysqli_num_rows($sql_cliente_facturado);
	if ($row_facturados>0){
		$label_class_estado='label-warning';
		$row_facturados ="FACTURADO";
	}else{
		$label_class_estado='label-success';
		$row_facturados ="POR FACTURAR";
	}
	if ($resultado_suma+$resultado_suma_unavez==0){
		$label_class_estado='label-danger';
		$row_facturados ="SIN VALORES";
	}
	
	$total_factura = $resultado_suma+$resultado_suma_unavez - $valor_descontado;
		?>
		<input type="hidden" name="sucursal_facturar" value="<?php echo $sucursal_facturar;?>">
		<input type="hidden" name="mes_facturar" value="<?php echo $mes_facturar;?>">
		<input type="hidden" name="anio_facturar" value="<?php echo $anio_facturar;?>">
		<input type="hidden" name="sucursal_alumno_facturar" value="<?php echo $sucursal_alumno_facturar;?>">
		<input type="hidden" name="paralelo_alumno_facturar" value="<?php echo $paralelo_alumno_facturar;?>">
		<input type="hidden" name="periodo_facturar" value="<?php echo $periodo_facturar;?>">
		<tr>
			<td><?php echo ($numero);?></td>
			<td><?php echo strtoupper($nombres_alumnos);?></td>
			<td><?php echo strtoupper($clientes);?></td>
			<td><?php echo number_format(($resultado_suma+$resultado_suma_unavez),2,'.','');?></td>
			<td><?php echo number_format(($valor_descontado),2,'.','');?></td>
			<td><span class="label <?php echo $label_class_estado;?>"><?php echo $row_facturados; ?></span></td>
			<?php
			if (($resultado_suma+$resultado_suma_unavez)>0 and $row_facturados == "POR FACTURAR"){
			?>
			<td><input type="checkbox" class="form-control" name="aplica_factura[]"   value="<?php echo $id_alumno;?>" checked></td>
			<?php
			}else if (($resultado_suma+$resultado_suma_unavez)==0 and $row_facturados == "SIN VALORES") {
			?>
			<td><input type="checkbox" class="form-control" name="aplica_factura[]"   value="<?php echo $id_alumno;?>"disabled></td>
		    <?php
			}else{
			?>
			<td><input type="checkbox" class="form-control" name="aplica_factura[]"   value="<?php echo $id_alumno;?>"></td> 
			<?php
			}
			?>
		</tr>		

		<?php
}
?>
</table>
</div>
</div>

<!-- <div class="panel panel-info">
<div class="panel panel-body">
-->

<div class="form-group">
<label class="col-md-2 control-label">Fecha factura</label>
<div class='col-sm-2'>
  <input type="text" class="form-control input-sm" name="fecha_facturar_alumnos" id="fecha_facturar_alumnos" value="<?php echo date("d-m-Y");?>">
</div>
<div class="col-sm-2">
<input type="submit" class="btn btn-primary" name="generar_facturas" value="Generar facturas" onclick="reload()">
</div>
</div>
</form>
<div id="resultados_guardar_facturas" ></div><!-- Carga los datos ajax del detalle de la factura -->	
<!--</div>
</div> -->



<?php
}else {
			$errors []= "Error desconocido.";
		}
}
		if (isset($errors)){
			
			?>
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Error!</strong> 
					<?php
						foreach ($errors as $error) {
								echo $error;
							}
						?>
			</div>
			<?php
			}
			if (isset($messages)){
				
				?>
				<div class="alert alert-success" role="alert">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>¡Bien hecho!</strong>
						<?php
							foreach ($messages as $message) {
									echo $message;
								}
							?>
				</div>
				<?php
			}
?>

<script>
	
	//para guardar los descuentos
$(function() {
$( "#guardar_facturas" ).submit(function( event ) {
	 $('#generar_facturas').attr("disabled", true);
	 var parametros = $(this).serialize();
			$.ajax({
         type: "POST",
         url: "../ajax/guardar_facturas_alumnos.php?action=guardar_facturas",
         data: parametros,
		 beforeSend: function(objeto){
			$("#resultados_guardar_facturas").html("Mensaje: Guardando...");
		  },
			success: function(datos){
			$("#resultados_guardar_facturas").html(datos);
			$('#generar_facturas').attr("disabled", false);
			
			 for (i=0;i<document.guardar_facturas.elements.length;i++) 
			  if(document.guardar_facturas.elements[i].type == "checkbox")	
				 document.guardar_facturas.elements[i].checked=0 
			}
			});
			event.preventDefault();
		});
		
});


</script>