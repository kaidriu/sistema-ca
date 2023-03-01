<!DOCTYPE html>
<html lang="es" class="no-js"> <!--<![endif]-->
    <head>
	<link rel="shortcut icon" type="image/png" href="./img/logofinal.png"/>
        <!-- Mobile Specific Meta -->
		<title>CaMaGaRe | Facturación Electrónica Ecuador</title>
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximun-scale=1.0, minimun-scale=1.0"/>﻿
		<meta name="description" content="Somos la plataforma que ofrece el mejor software de facturacion electronica en Ecuador"/> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <!-- Meta Keyword -->
        <meta name="keywords" content="Facturacion Electronica Ecuador / CaMaGaRe, facturas, retenciones, compras, ventas, ingresos, egresos, notas de credito, liquidaciones compras y servicios, declaraciones, impuestos, renta, iva"/>
        <!-- meta character set -->
        <meta charset="utf-8"/>
		<meta name="google-site-verification" content="FkjaLXW9ce-s4jWh3UoOGiijcRRqu_-2A6yc0jH2nRs" />
     
        <!--
        Google Fonts
        ============================================= -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700" rel="stylesheet" type="text/css">
        <!--
        CSS
        ============================================= -->
        <!-- Fontawesome -->
        <link rel="stylesheet" href="csss/font-awesome.min.css">
        <!-- Bootstrap -->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!-- Fancybox -->
        <link rel="stylesheet" href="css/jquery.fancybox.css">
        <!-- owl carousel -->
        <link rel="stylesheet" href="css/owl.carousel.css">
        <!-- Animate -->
        <link rel="stylesheet" href="css/animate.css">
        <!-- Main Stylesheet -->
        <link rel="stylesheet" href="css/main.css">
        <!-- Main Responsive -->
        <link rel="stylesheet" href="css/responsive.css">
		<!-- Modernizer Script for old Browsers -->
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
		<script src='https://www.google.com/recaptcha/api.js'></script>
		
		 <style type="text/css">
			 nav.navbar {
				background-color: #5DADE2;
			 }
			.navbar-default {
				background-color:#2980B9; opacity: 0.8;
				border-color:transparent;
				box-shadow:none;	
			}
		
			header {
				width: 100%;
				line-height: 50px;
				height: 50px;

				.navbar-inner {
					border:0;
					border-radius: 0;
					background: blue;
					padding:0;
					margin:0;
					height: inherit;
				}
			}
		</style>
    </head>
    <body>
        <!--
        Fixed Navigation
        ==================================== -->
<header id="navigation" class="navbar-fixed-top">
<nav class="navbar navbar-info">
  <div class="container-fluid" style="text-align:center;">
     <!-- para dispositivo moviles -->
			<div class="navbar-header">               
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">                       
				<span class="sr-only">Toggle navigation</span>                       
				<span class="icon-bar"></span>                       
				<span class="icon-bar"></span>                       
				<span class="icon-bar"></span>                   
				</button>                 
				<!-- /responsive nav button -->                  
				<!-- logo -->				                  
				<a style="float:right; position: relative; top:-33px; cursor: pointer" class="navbar-brand" onclick="sesion('home');"><img src="img/logoca.png" alt="Logo" width="120px"></a>
				<!-- /logo -->          
			</div>
	 <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	 <ul class="nav navbar-nav navbar-left">	
			<li><a style="color:#FDFEFE; border-radius: 10px; cursor: pointer" onclick="sesion('acerca');"><span class="glyphicon glyphicon-tasks"></span> Acerca de</a></li>			
			<li><a style="color:#FDFEFE; border-radius: 10px; cursor: pointer" onclick="sesion('servicios');"><span class="glyphicon glyphicon-tasks"></span> Servicios</a></li>                           
			<li><a style="color:#FDFEFE; border-radius: 10px; cursor: pointer" onclick="sesion('planes');"><span class="glyphicon glyphicon-usd"></span> Planes</a></li>                          
			<li><a style="color:#FDFEFE; border-radius: 10px; cursor: pointer" onclick="sesion('contactos');"><span class="glyphicon glyphicon-envelope"></span> Contáctanos</a></li>                          
      		<li><a style="color:#FDFEFE; border-radius: 10px; cursor: pointer" href="https://api.whatsapp.com/send?phone=593958924831&text=Mensaje desde CaMaGaRe" target="_blank" title="Enviar mensaje por whastapp"> WhatsApp<img src="img/whatsapp.png" alt="Logo" width="18px"></a></li>
			<li><span class="text"><i class="glyphicon glyphicon-phone-alt"></i> VENTAS: <b>095-892-4831</b></span></li>
			
	  </ul>
	  
	  <ul class="nav navbar-nav navbar-right">
	  <div class="btn-group">
		  <a type="button" class="btn btn-primary" href="../sistema/index.php"><span class="glyphicon glyphicon-log-in"> </span> Ingresar al Sistema</a>
		  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
			<span class="sr-only">Toggle Dropdown</span>
		  </button>
		  <ul class="dropdown-menu">
			<li><a href="../documentos/index.php"><span class="glyphicon glyphicon-file"></span> Descargar Documentos</a></li>
			<li role="separator" class="divider"></li>
			<li><a href="https://v2.camagare.com"><span class="glyphicon glyphicon-file"></span> Roles</a></li>
		  </ul>
		</div>
		</ul>
    </div><!-- /.navbar-collapse -->
   </div><!-- /.container-fluid -->
</nav>
</header>

        <!--
        End Fixed Navigation
        ==================================== -->

        <!--
        Home Slider
        ==================================== -->
        <section id="home">     
            <div id="home-carousel" class="carousel slide" data-interval="false">
                <ol class="carousel-indicators">
                    <li data-target="#home-carousel" data-slide-to="0" class="active"></li>
                    <li data-target="#home-carousel" data-slide-to="1"></li>
                    <li data-target="#home-carousel" data-slide-to="2"></li>
					<li data-target="#home-carousel" data-slide-to="3"></li>
					<li data-target="#home-carousel" data-slide-to="4"></li>
                </ol>
                <!--/.carousel-indicators-->

                <div class="carousel-inner">
                    <div class="item active"  style="background-image: url('img/slider/principal.png'); background-repeat: no-repeat; background-size: 100% 100%;" >
                    </div> 

                    <div class="item" style="background-image: url('img/slider/Foto web 2 final.jpg')">                
                        <div class="carousel-caption">
                            <div class="animated bounceInDown" style='border-radius: 10px; background: rgba(94, 164, 190, 0.6);'>
                                <h3>CaMaGaRe <br>Impuestos.</h3>
                                 <p>Declaciones de IVA, RENTA, ICE, Anexos...</p>
                            </div>
                        </div>
                    </div>

                    <div class="item" style="background-image: url('img/slider/Foto web 3 final.jpg')">                 
                         <div class="carousel-caption">
                            <div class="animated bounceInUp" style='border-radius: 10px; background: rgba(94, 164, 190, 0.6);'>
                                <h3>CaMaGaRe <br>Nómina.</h3>
                                 <p>Roles de pagos, provisiones, Décimos, Rol de Utilidades, IESS, MT.</p>
                            </div>
                        </div>
                    </div>
					
					<div class="item" style="background-image: url('img/slider/Foto web 1 final.jpg')">                 
                         <div class="carousel-caption">
                            <div class="animated bounceInUp" style='border-radius: 10px; background: rgba(94, 164, 190, 0.6);'>
                                <h3>CaMaGaRe <br>Inventario.</h3>
                                 <p>Entradas, salidas, devoluciones, kardex.</p>
                            </div>
                        </div>
                    </div>
					
					<div class="item" style="background-image: url('img/slider/Foto web 5 final.jpg')">                 
                         <div class="carousel-caption">
                            <div class="animated bounceInUp" style='border-radius: 10px; background: rgba(94, 164, 190, 0.6);'>
                                <h3>CaMaGaRe <br>Contabilidad.</h3>
                                 <p>Diarios, mayores, balances...</p>
                            </div>
                        </div>
                    </div>
					
                </div>

                <!--/.carousel-inner  style='border-radius: 10px;'-->
					<nav id="nav-arrows" class="nav-arrows hidden-ms hidden-md visible-md visible-lg" >
						<a class="sl-next" href="#home-carousel" data-slide="next" style='border-radius: 10px;'><i class="fa fa-angle-right fa-3x"></i><span class="glyphicon glyphicon-forward"></span> </a>
					</nav>
            </div>
        </section>
        <!--
        End #home Slider
        ========================== -->
 <!--
        #acerca de
        ========================== -->
        <section id="acerca">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title text-center wow fadeInDown" >
                            <h4><b>CaMaGaRe</b></h4>    
                            <h4>Somos el sistema online para facturación electrónica en el Ecuador</h4>
                        </div>
                    </div>
                </div>
                <div class="row">
				<div class="col-sm-6 col-sm-10 wow fadeInLeft" data-wow-delay="0.5s">
                 <div class="panel panel-info">
					<div class="panel-heading"><h2>Misión</h2></div>
							<div class="panel-body">
                                <p>
								Somos el software online que ayuda a organizar la gestión administrativa y contable
								de los emprendimientos y empresas Pymes en el Ecuador de manera fácil, sencilla y amigable, 
								sin tener conocimientos técnicos de contabilidad o administración.
								</p>
                            </div>
                        </div>
                </div>
				<div class="col-sm-6 col-sm-10 wow fadeInLeft" data-wow-delay="0.5s">
                 <div class="panel panel-info">
					<div class="panel-heading"><h2>Visión</h2></div>
							<div class="panel-body">
                                <p>
								Hacer que la tecnología de internet trabaje de 
								forma sincronizada y automática a favor de los emprendimientos
								y empresas Pymes en el Ecuador y permita al usuario tomar decisiones
								rápidas y acertadas.</p>
                            </div>
                        </div>
                </div>
				</div>	
            </div> <!-- end .container -->
        </section>
        <!--
        End #acerca
        ========================== -->
        
        <!--
        #service
        ========================== -->
        <section id="servicios">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title text-center wow fadeInDown">
                            <h2>Servicios</h2>    
                            <p>Servicios Administrativos, Contables y Tributarios.</p>
                        </div>
                    </div>
                </div>
                <div class="row">
				<div class="col-sm-3 col-sm-12 wow fadeInLeft">
                        <div class="media">
                            <a href="#contactos" class="pull-center" title="Contratar facturación electónica">
                                <img src="img/icons/monitor.png" class="media-object" alt="Monitor">
                            </a>
                            <div class="media-body">
                                <h3>Facturación electrónica</h3>
                                <p>- Facturas</p>
                                <p>- Retenciones</p>
                                <p>- Notas de crédito</p>
								<p>- Notas de débito</p>
								<p>- Guías de remisión</p>
								<p>- Liquidaciónes de compras y servicios</p>
								<p class="text-left"><a title="Contratar servicio" class='btn btn-primary btn-md' href="#contactos"><span class="glyphicon glyphicon-plus"></span> Mas info... </a></p>
                            </div>
                        </div>

                </div>
				
				
					<div class="col-md-3 col-sm-12 wow fadeInLeft" data-wow-delay="0.2s">
                        <div class="media">
                            <a href="#contactos" class="pull-center" title="Contratar servicios">
                                 <img src="img/icons/ruler.png" class="media-object" alt="Ruler">
                            </a>
                            <div class="media-body">
                                <h3>Impuestos</h3>
                                <p>- Declaraciones IVA</p>
								<p>- Declaraciones ICE</p>
								<p>- Declaraciones RENTA</p>
								<p>- Declaraciones ISD</p>
                                <p>- Anexos</p>
								<p class="text-left"><a title="Contratar servicio" class='btn btn-primary btn-md' href="#contactos"><span class="glyphicon glyphicon-plus"></span> Mas info... </a></p>
                            </div>
                        </div>
                    </div>
					
					<div class="col-md-3 col-sm-12 wow fadeInRight">
                        <div class="media">
                            <a href="#contactos" class="pull-center" title="Contratar servicio">
                                <img src="img/icons/cog.png" class="media-object" alt="Cog">
                            </a>
                            <div class="media-body">
                                <h3>Outsourcing Contable</h3>
                                <p>- Transacciones contables</p>
                                <p>- Supervisión Contable</p>
                                <p>- Actualización de Contabilidad</p>
								<p>- Roles de pagos</p>
                                <p>- Control de personal</p>
                                <p>- IESS</p>
								<p class="text-left"><a title="Contratar servicio" class='btn btn-primary btn-md' href="#contactos"><span class="glyphicon glyphicon-plus"></span> Mas info... </a></p>
                            </div>
                        </div>

                    </div>
					<div class="col-md-3 col-sm-12 wow fadeInLeft">
                        <div class="media">
                            <a href="#contactos" class="pull-center" title="Contratar servicio">
                                <img src="img/icons/monitor.png" class="media-object" alt="Monitor">
                            </a>
                            <div class="media-body">
                                <h3>Control interno</h3>
                                <p>- Inventarios</p>
                                <p>- Cuentas por cobrar</p>
                                <p>- Cuentas por pagar</p>
								<p>- Bancos</p>
								<p class="text-left"><a title="Contratar servicio" class='btn btn-primary btn-md' href="#contactos"><span class="glyphicon glyphicon-plus"></span> Mas info... </a></p>
                            </div>
                        </div>

                    </div>
				</div>	

            </div> <!-- end .container -->
        </section>
        <!--
        End #service
        ========================== -->
        <section id="planes">
		<div class="container">
        <div class="row">
            <div class="section-title text-center wow fadeInDown">
                <h2>Planes</h2>    
                <p>CaMaGaRe</p>
            </div>
            
			   <div class="row">
				<div class="col-sm-3 col-sm-12 wow fadeInLeft" data-wow-delay="0.5s">
                 <div class="panel panel-primary">
					<div class="panel-heading"><h2><span class="glyphicon glyphicon-pawn"></span> Independiente</h2></div>
							<div class="panel-body">
								<p> Ideal para emprendedores, profesionales 
								personas naturales que no estan obligados
								a llevar contabilidad.</p>
                                <p> - Usuarios y sucursales ilimitados</p>
								<p> - Soporte técnico y actualizaciones sin costo</p>
								<p> - Documentos electrónicos ilimitados: Facturas, 
								notas de crédito, guías de remisión	y liquidaciones
								de compras y servicios</p>
								<p> - Registro de compras y retenciones por ventas</p>
								<p> - Reporte de ventas, notas de crédito, retenciones de ventas,
								gráfico para análisis de ventas</p>
								<p> - Declaraciones de IVA</p>
								<p> - Módulos adicionales a medida según cotización</p>
                            
							<div class="alert alert-info" role="alert">
							  <b>Mensual $ 20.00<br></b>
							  <b>Anual $ 200.00</b>
							</div>
							</div>
						</div>
                </div>
				<div class="col-sm-3 col-sm-12 wow fadeInLeft" data-wow-delay="0.5s">
                 <div class="panel panel-primary">
					<div class="panel-heading"><h2><span class="glyphicon glyphicon-queen"></span> Comercial</h2></div>
							<div class="panel-body">
								<p> Ideal para negocios con local comercial de 
								personas naturales que no estan obligados a
								llevar contabilidad.</p>
                                <p> - Usuarios ilimitados y local comercial principal</p>
								<p> - Soporte técnico y actualizaciones sin costo</p>
								<p> - Documentos electrónicos ilimitados: Facturas, 
								notas de crédito, guías de remisión	y liquidaciones
								de compras y servicios</p>
								<p> - Registro de compras y retenciones por ventas</p>
								<p> - Declaraciones de IVA</p>
								<p> - Inventarios</p>
								<p> - Ingresos, egresos, cuentas por cobrar y cuentas por pagar</p>
								<p> - Reporte de ventas, notas de crédito, retenciones de ventas,
								gráfico para análisis de compras y ventas, y existencias de inventario</p>
								<p> - Módulos adicionales a medida según cotización</p>
							<div class="alert alert-info" role="alert">
							  <b>Mensual $ 40.00<br></b>
							  <b>Anual $ 400.00</b>
							</div>
							</div>
                        </div>
                </div>
				<div class="col-sm-3 col-sm-12 wow fadeInLeft" data-wow-delay="0.5s">
                 <div class="panel panel-primary">
					<div class="panel-heading"><h2><span class="glyphicon glyphicon-king"></span> Societario</h2></div>
							<div class="panel-body">
								<p> Ideal para Pymes y personas naturales obligados
								a llevar contabilidad.</p>
                                <p> - Usuarios y sucursales ilimitados</p>
								<p> - Soporte técnico y actualizaciones sin costo</p>
								<p> - Documentos electrónicos ilimitados: Facturas, 
								 retenciones en compras, notas de crédito, guías de remisión y liquidaciones
								de compras y servicios</p>
								<p> - Registro de compras y retenciones por ventas</p>
								<p> - Inventarios</p>
								<p> - Consignaciones en ventas</p>
								<p> - Ingresos, egresos, cuentas por cobrar y cuentas por pagar</p>
								<p> - Declaraciones de IVA, declaraciones de Retenciones y ATS</p>
								<p> - Reporte de ventas, notas de crédito, retenciones de ventas,
								gráfico para análisis de compras y ventas, existencias de inventario, 
								consignaciones en ventas y reporte diario de caja</p>
								<p> - Módulos adicionales a medida según cotización</p>
                           <div class="alert alert-info" role="alert">
							  <b>Mensual $ 60.00<br></b>
							  <b>Anual $ 600.00</b>
							</div>
							</div>
                        </div>
                </div>
				<div class="col-sm-3 col-sm-12 wow fadeInLeft" data-wow-delay="0.5s">
                 <div class="panel panel-primary">
					<div class="panel-heading"><h2><span class="glyphicon glyphicon-knight"></span> Especiales</h2></div>
							<div class="panel-body">
								<p> - Módulo contabilidad 
								<br><span class="badge">Mensual $ 30.00</span>
								<br><span class="badge">Anual $ 300.00</span>
								</p>
								<p> - Facturación programada mensual: permite generar de manera automática 
								todo lo que la empresa factura de manera fija mensual a sus clientes 
								<br><span class="badge">Mensual $ 5.00</span>
								<br><span class="badge">Anual $ 50.00</span>
								</p>
                                <p> - Módulo especial de facturación en restaurantes: 
								permite facturar individualmente desde cada mesa y dividir
								la cuenta para varios clientes
								<br><span class="badge">Mensual $ 20.00</span>
								<br><span class="badge">Anual $ 200.00</span>
								</p>
								<p> - Módulo especial para facturación en centros educativos: permite tener cuentas individuales por cada 
								estudiante e ir agregando servicios y productos para su facturación mensual
								<br><span class="badge">Mensual $ 50.00</span>
								<br><span class="badge">Anual $ 500.00</span>
								</p>
								<p> - Módulo especial para facturación en mecánicas: permite tener órdenes de 
								servicio e ir agregando productos de acuerdo al consumo del cliente
								<br><span class="badge">Mensual $ 10.00</span>
								<br><span class="badge">Anual $ 100.00</span>
								</p>
								<p> - Todos los módulos adicionales se integran de manera automática al sistema y al plan contratado</p>
								<p> - Otros módulos adicionales a medida según cotización
								
								</p>
                           
							</div>
                        </div>
                </div>
			</div>	
			
		
			</div>
			</div>
        </section>
        <!--
        End #Planes
        ========================== -->

        <!--
        #contact
        ========================== -->
        <section id="contactos">
            <div class="container">
                <div class="row">
                    <div class="section-title text-center wow fadeInDown">
                        <h2>Contáctenos</h2>
                        <p>Estaremos gustosos en ayudarle.</p>
                    </div>
                    <div class="col-md-6 col-sm-6 wow fadeInLeft">
					<div class="panel panel-info">

                        <div class="contact-form clearfix">
						<div class="modal-body">
                            <form  action="#home" method="post" id="guardar_contacto">
                                <div class="input-field">
                                    <input type="text" class="form-control" name="nombre" placeholder="Su nombre" required>
                                </div>
                                <div class="input-field">
                                    <input type="email" class="form-control" name="correo" placeholder="Su Email" required>
                                </div>
                                <div class="input-field message">
                                    <textarea class="form-control" name="mensaje" placeholder="Su requerimiento" required></textarea>
                                <div id="resultados_ajax"></div>
								</div>	
								<div class="g-recaptcha" data-sitekey="6LdJLE0UAAAAAC6nmvCHnevGLAoRLwlq28XJdkXp"></div>
								<button type="submit" class="btn btn-primary" id="guardar_datos">Enviar correo</button>
							</form>
						</div>
						</div>
                    </div> <!-- end .contact-form -->
						
                    </div> <!-- .col-md-8 -->

                    <div class="col-md-3 col-sm-3 wow fadeInRight">
                        <div class="contact-details">
                            <span>Comunícate con nosotros</span>
                            <p><span class="glyphicon glyphicon-phone"></span> 095-892-4831 <br> 
							<span class="glyphicon glyphicon-phone"></span> 022-812-975</p>
							<p><span class="glyphicon glyphicon-envelope"></span> info@camagare.com</p>
							<p><a href="https://api.whatsapp.com/send?phone=593958924831&text=Mensaje desde CaMaGaRe" target="_blank" title="Enviar mensaje por whastapp"><img src="img/whatsapp.png" alt="Logo" width="30px"> Whatsapp</a></p>
                        </div> <!-- end .contact-details -->

                    </div> <!-- .col-md-4 -->
                </div>
            </div>
        </section>
        <!--
        End #contact
        ========================== -->
<!--
 
        #footer
        ========================== -->
        <footer id="footer" class="text-center">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="footer-logo wow fadeInDown">
                            <a href="https://www.facebook.com/Camagare-406200073152657/"><img src="./img/logofinal.png" alt="logo" width="100px"></a>
                        </div>

                        <div class="footer-social wow fadeInUp">
                            <h3>Visítanos en las redes sociales</h3>
							<h3>
							<div class="fb-like" data-href="https://developers.facebook.com/docs/plugins/" data-layout="button" data-action="like" data-size="small" data-show-faces="true" data-share="true"></div>
							</h3>                            
                        </div>

                        <div class="copyright">
                            <p> CaMaGaRe 2020</a></p>
                        </div>

                    </div>
                </div>
            </div>
        </footer>
        <!--
        End #footer
        ========================== -->
	<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v3.2';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

        <!--
        JavaScripts
        ========================== -->
        
        <!-- main jQuery -->
        <script src="js/vendor/jquery-1.11.1.min.js"></script>
        <!-- Bootstrap -->
        <script src="js/bootstrap.min.js"></script>
        <!-- jquery.nav -->
        <script src="js/jquery.nav.js"></script>
        <!-- Portfolio Filtering -->
        <script src="js/jquery.mixitup.min.js"></script>
        <!-- Fancybox -->
        <script src="js/jquery.fancybox.pack.js"></script>
        <!-- Parallax sections -->
        <script src="js/jquery.parallax-1.1.3.js"></script>
        <!-- jQuery Appear -->
        <script src="js/jquery.appear.js"></script>
        <!-- countTo -->
        <script src="js/jquery-countTo.js"></script>
        <!-- owl carousel -->
        <script src="js/owl.carousel.min.js"></script>
        <!-- WOW script -->
        <script src="js/wow.min.js"></script>
        <!-- theme custom scripts -->
        <script src="js/main.js"></script>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script>

function sesion(id) {
    location.hash = "#" + id;
	history.pushState("", document.title, window.location.pathname);
}


$( "#guardar_contacto" ).submit(function( event ) {
		  $('#guardar_datos').attr("disabled", true);
		 var parametros = $(this).serialize();
			 $.ajax({
					type: "POST",
					url: "php/guarda_contacto.php",
					data: parametros,
					 beforeSend: function(objeto){
						$("#resultados_ajax").html("Enviando mensaje...");
					  },
					success: function(datos){
					$("#resultados_ajax").html(datos);
					$('#guardar_datos').attr("disabled", false);
					setTimeout(function (){location.href ='https://www.camagare.com'}, 1000);
				  }
			});
		  event.preventDefault();
		})		
	</script>

	</body>
</html>

