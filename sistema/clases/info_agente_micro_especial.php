<?php
$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';

if(($action == 'info_agente_micro_especial') && isset($_POST['ruc_proveedor']) && (!empty($_POST['ruc_proveedor'] ))){

$ruc_a_buscar = $_POST['ruc_proveedor'];
$carpeta_agente = file_get_contents('../agente_micro_especial/agente_retencion.txt');
$resultado_agente = strpos($carpeta_agente, $ruc_a_buscar);

$carpeta_contribuyente_especial = file_get_contents('../agente_micro_especial/contribuyente_especial.txt');
$resultado_contriguyente_especial = strpos($carpeta_contribuyente_especial, $ruc_a_buscar);

$carpeta_rimpe = file_get_contents('../agente_micro_especial/rimpe.txt');
$resultado_rimpe = strpos($carpeta_rimpe, $ruc_a_buscar);

$carpeta_negocio_popular = file_get_contents('../agente_micro_especial/negocio_popular.txt');
$resultado_negocio_popular = strpos($carpeta_negocio_popular, $ruc_a_buscar);

if ($resultado_contriguyente_especial) {
    ?>
	<div class="alert alert-warning" role="alert" style ="padding: 4px; margin-bottom: 5px; margin-top: -5px;">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong> El proveedor es contribuyente especial </strong>
	</div>
	<?php
	}

if ($resultado_agente){
		?>
		<div class="alert alert-warning" role="alert" style ="padding: 4px; margin-bottom: 5px; margin-top: -5px;">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong> El proveedor es Agente de Retención</strong>
		</div>
		<?php
}

if ($resultado_rimpe) {
		?>
		<div class="alert alert-warning" role="alert" style ="padding: 4px; margin-bottom: 5px; margin-top: -5px;">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong> El proveedor es contribuyente régimen RIMPE (cód ret 343)</strong>
		</div>
		<?php
	}

if ($resultado_negocio_popular) {
		?>
		<div class="alert alert-warning" role="alert" style ="padding: 4px; margin-bottom: 5px; margin-top: -5px;">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong> El proveedor es contribuyente RIMPE NEGOCIO POPULAR (cód ret 332)</strong>
		</div>
		<?php
	}

}
?>
