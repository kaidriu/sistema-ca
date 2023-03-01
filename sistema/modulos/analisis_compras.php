<?php
session_start();
if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_empresa']) && isset($_SESSION['ruc_empresa'])){
	$id_usuario = $_SESSION['id_usuario'];
	$id_empresa =$_SESSION['id_empresa'];
	$ruc_empresa = $_SESSION['ruc_empresa'];
	
?>
<html lang="es">
<head>
<title>Gráficos compras</title>
<?php include("../paginas/menu_de_empresas.php");?>
</head>
<body>
		<div class="row">
			<div class="col-xs-2">
			<div class="panel panel-default">
				 <div class="panel-heading">  
				<form class="form-horizontal" >
					<input type="hidden" id="mes">
					<input type="hidden" id="suma">
					<div class="form-group">
					<label class="col-sm-2 control-label">Año</label>
						<div class="col-sm-9">
							<select class="form-control" name="anio_periodo" id="anio_periodo">
								<option value="<?php echo date("Y") ?>"> <?php echo date("Y") ?></option>
								<?php for ($i = $anio2=date("Y")-1; $i > $anio1=date("Y")-5; $i+= -1) {
								?> 
								<option value="<?php echo $i ?>"> <?php echo $i ?></option>
								<?php }  ?> 
							</select>
						</div>
					</div>
					<div class="form-group">
					<label class="col-sm-2 control-label">Tipo</label>
					<div class="col-sm-9">
						<select class="form-control" name="tipo" id="tipo">
							<option value="line" > Lineal</option>
							<option value="column" > Columnas</option>
							<option value="bar" selected> Barras</option>
							<option value="area" > Area</option>
							<option value="spline" > Invertido</option>
						</select>
					</div>
					</div>
					<div class="form-group">
					<label class="col-sm-2 control-label"></label>
						<div class="col-sm-10">
						<button type="button" class="btn btn-info" onclick='mostrar_char();'><span class="glyphicon glyphicon-search" ></span> Mostrar </button>
						</div>
					</div>
					<div class="form-group">
					<label class="col-sm-4 control-label"></label>
						<div class="col-sm-8">
						<div id="loader"></div>
						</div>
					</div>
					</form>
				</div>
				
					
			</div>
						
			</div>
			<div id="resultados"></div>
			<div class="col-xs-9">
				<div id="container" style="min-width: 300px; max-width: 1200px; height: 500px; margin: 1 auto"></div>
			</div>
			
		</div>

</body>
</html>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<?php
}else{
header('Location: ../includes/logout.php');
exit;
}
?>

<script >
//para cuando se seleecione el anio
function mostrar_char(){
		var anio = $("#anio_periodo").val();
		var tipo_char = $("#tipo").val();
		$.ajax({
			url:'../ajax/analisis_compras.php?action=analisis_compras&anio='+anio,
			 beforeSend: function(objeto){
			 $('#loader').html('<img src="../image/ajax-loader.gif">');
		  },
			success:function(data){
					$.each(data, function(i, item) {
						grafico(item.meses, anio, tipo_char, item.sumas);
						$('#loader').html('');
					});
			}
		})
}

function grafico(meses, anio, tipo_char, sumas){
Highcharts.chart('container', {
  chart: {
    type: tipo_char//'line' column, bar
  },
  title: {
    text: 'Detalle de compras'
  },
  subtitle: {
    text: 'Mensual'
  },
  xAxis: {
    categories: meses,
    title: {
      text: 'Meses'
    }
  },
  yAxis: {
    min: 0,
    title: {
      text: 'Moneda (Dólares)',
      align: 'high'
    },
    labels: {
      overflow: 'justify'
    }
  },
  tooltip: {
    valueSuffix: ' Dólares'
  },
  plotOptions: {
    bar: {
      dataLabels: {
        enabled: true
      }
    }
  },
  legend: {
    layout: 'vertical',
    align: 'right',
    verticalAlign: 'top',
    x: -80,
    y: 10,
    floating: true,
    borderWidth: 2,
    backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
    shadow: true
  },
  credits: {
    enabled: false
  },
 series: [{
    name: [anio],
    data: sumas
  },  
]
});
}
</script>
