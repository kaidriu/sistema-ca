<?php
//Para mostrar el detalle de los adicionales de la factura -->
function muestra_adicionales_factura($serie_factura, $secuencial_factura, $id_usuario, $con, $id_cliente){	
				$busca_empresa_detalle = mysqli_query($con, "SELECT * FROM clientes WHERE id = '".$id_cliente."' ");
				$datos_detalle = mysqli_fetch_array($busca_empresa_detalle);
				$email=$datos_detalle['email'];
				$direccion=$datos_detalle['direccion'];
				$telefono=$datos_detalle['telefono'];
				$ruc_empresa=$datos_detalle['ruc_empresa'];
				
				/*
				$busca_regimen = mysqli_query($con, "SELECT * FROM config_electronicos WHERE ruc_empresa = '".$ruc_empresa."' ");
				$datos_regimen = mysqli_fetch_array($busca_regimen);
				$negocio_popular=$datos_regimen['negocio_popular'];
				$regimen_rimpe=$datos_regimen['regimen_rimpe'];
				*/

				$delete_mail_adicional_tmp = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."' and concepto='Email'");
				$delete_direccion_adicional_tmp = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."' and concepto='Dirección'");
				$delete_telefono_adicional_tmp = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."' and concepto='Teléfono'");
				//$delete_negocio_popular = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."' and concepto='Contribuyente' and detalle='Negocio Popular - Régimen RIMPE'");
				//$delete_regimen_rimpe = mysqli_query($con, "DELETE FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."' and concepto='Contribuyente' and detalle='Régimen RIMPE'");
				
				$detalle_adicional_uno = mysqli_query($con, "INSERT INTO adicional_tmp VALUES (null, '".$id_usuario."', '".$serie_factura."', '".$secuencial_factura."', 'Email','".$email."')");
				$detalle_adicional_uno = mysqli_query($con, "INSERT INTO adicional_tmp VALUES (null, '".$id_usuario."', '".$serie_factura."', '".$secuencial_factura."', 'Dirección','".$direccion."')");
				$detalle_adicional_uno = mysqli_query($con, "INSERT INTO adicional_tmp VALUES (null, '".$id_usuario."', '".$serie_factura."', '".$secuencial_factura."', 'Teléfono','".$telefono."')");
				
				/*
				if($negocio_popular=='SI'){ 
				$detalle_adicional_uno = mysqli_query($con, "INSERT INTO adicional_tmp VALUES (null, '".$id_usuario."', '".$serie_factura."', '".$secuencial_factura."', 'Contribuyente','Negocio Popular - Régimen RIMPE')");
				}
				if($regimen_rimpe=='SI'){
				$detalle_adicional_uno = mysqli_query($con, "INSERT INTO adicional_tmp VALUES (null, '".$id_usuario."', '".$serie_factura."', '".$secuencial_factura."', 'Contribuyente','Régimen RIMPE')");
				}
				*/
			//}
?>
						<div class="table-responsive">
						<table class="table table-bordered">

									<tr class="info">
										<td class='col-xs-3'>
										 <input type="text" class="form-control input-sm" id="adicional_concepto" name="adicional_concepto" placeholder="Concepto">
										</td>
										<td class="col-xs-7">
										<input type="text" class="form-control input-sm" id="adicional_descripcion" name="adicional_descripcion" placeholder="Descripción del detalle">
										</td>
										<td class="text-center"><a class='btn btn-info btn-sm' title='Agregar' onclick="agregar_info_adicional()"><i class="glyphicon glyphicon-plus"></i></a></td>
									</tr>
						<?php
							$muestra_adicional_tmp = "SELECT * FROM adicional_tmp WHERE id_usuario = '".$id_usuario."' and serie_factura = '".$serie_factura."' and secuencial_factura = '".$secuencial_factura."'";
							$query = mysqli_query($con, $muestra_adicional_tmp);
							while ($detalle_info_adicional = mysqli_fetch_array($query)){
								$id_info_adicional=$detalle_info_adicional['id_ad_tmp'];
								$concepto=$detalle_info_adicional['concepto'];
								$detalle=$detalle_info_adicional['detalle'];
						?>				
									<tr>
									<input type="hidden" id="id_cliente_adicional" value="<?php echo $id_cliente;?>">
									<input type="hidden" id="serie_adicional" value="<?php echo $serie_factura;?>">
									<input type="hidden" id="secuencial_adicional" value="<?php echo $secuencial_factura;?>">									
									<td><?php echo $concepto; ?></td>
									<td><?php echo $detalle; ?></td>
									<?php
									if ($concepto=="Email" || $concepto=="Dirección" || $concepto=="Teléfono" || $concepto=="Agente de Retención" || $concepto=="Régimen"){
									?>
									<td class='text-center'></td>
									<?php
									}else{
									?>
									<td class='text-center'><a href="#" class='btn btn-danger btn-sm' title='Eliminar' onclick="eliminar_detalle_info_adicional('<?php echo $id_info_adicional;?>')"><i class="glyphicon glyphicon-remove"></i></a></td>
									<?php
									}
									?>
									</tr>
								
							<?php
							
							}
						?>
						</table>
						</div>
<?php
}

?>