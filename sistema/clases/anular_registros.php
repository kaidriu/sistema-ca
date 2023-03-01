<?php
 class anular_registros {
	 
	 //para anular un asiento contable cuando se anula una factura, retencion, compras...
	  public function anular_asiento_contable($con, $numero_asiento, $ruc_empresa, $id_usuario, $anio_asiento){
		ini_set('date.timezone','America/Guayaquil');
		$fecha_registro=date("Y-m-d H:i:s");
			if ($numero_asiento>0){
			$consulta_tipo_diario = mysqli_query($con, "SELECT * FROM encabezado_diario WHERE year(fecha_asiento)='".$anio_asiento."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."' and tipo='CIERRE'");
			$contar_registros=mysqli_num_rows($consulta_tipo_diario);
				if ($contar_registros>0){
					return "NO";
				}else{
				$update_encabezado = mysqli_query($con, "UPDATE encabezado_diario SET estado='Anulado', id_usuario='".$id_usuario."', fecha_registro='".$fecha_registro."' WHERE numero_asiento='".$numero_asiento."' and mid(ruc_empresa,1,12) = '".substr($ruc_empresa,0,12)."'");
					return "Registro contable anulado";
				 }
			}else{
				return false;
			}
		}		
	 }

?>