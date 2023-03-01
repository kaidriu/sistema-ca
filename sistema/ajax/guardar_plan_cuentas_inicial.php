<?php				
		/* Connect To Database*/
		include("../conexiones/conectalogin.php");
		include("../validadores/generador_codigo_unico.php");
		$con = conenta_login();
		$codigo_unico=codigo_unico(20);
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date('Y-m-d H:i:s');

		session_start();
		$ruc_empresa = $_SESSION['ruc_empresa'];
		$id_usuario = $_SESSION['id_usuario'];
		$cuentas_iniciales = array("1" => "ACTIVOS","2" => "PASIVOS","3" => "PATRIMONIO","4" => "INGRESOS","5" => "COSTOS","6" => "GASTOS" ,"7" => "RESUMEN RESULTADOS");
		
		$codigo=array();
		for($i = 1; $i <= 7; ++$i) {
		$buscar_cuenta=mysqli_query($con, "SELECT * FROM plan_cuentas WHERE ruc_empresa='".$ruc_empresa."' and codigo_cuenta='".$i."'");
		$contar_cuentas_registradas=mysqli_num_rows($buscar_cuenta);		
		if ($contar_cuentas_registradas==0){
				$codigo[]=$i;
			}
		}

		foreach ($codigo as $valor){
			$contar_cuentas_registradas=mysqli_num_fields($buscar_cuenta);
				$guardar_cuenta=mysqli_query($con, "INSERT INTO plan_cuentas VALUES (null,'".$valor."','1','".$cuentas_iniciales[$valor]."','','','".$ruc_empresa."','".$id_usuario."','".$fecha_registro."','".$codigo_unico."')");
		}
		
		$messages[] = "Las cuentas han sido ingresadas satisfactoriamente.";
		
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