<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">

    <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/header-responsive.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/lenin.css"> <title>SIMPINNA | Inicio</title>
</head>
<body>
    <header> 
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header.php'); ?>
    </header>
    
    <main>
        <section class="hero-banner">
            <div class="hero-container">
                <div class="hero-text-content">
                    <span class="hero-badge">Bienvenido a SIMPINNA</span>
                    <h1 class="hero-title">Sistema Municipal de Protección Integral de Niñas, Niños y Adolescentes</h1>
                    <p class="hero-subtitle">Velamos por el bienestar y los derechos de la niñez y adolescencia en Tecate, construyendo un entorno seguro para su desarrollo.</p>
                    
                    <div class="hero-buttons">
                        <a href="/front-end/frames/inicio/seleccion-encuesta.php" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 11l3 3L22 4"></path>
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                            </svg>
                            Contestar encuesta
                        </a>
                        <a href="/front-end/frames/inicio/contacto.php" class="btn-secondary">
                            Contáctanos
                        </a>
                    </div>
                </div>
                
                <div class="hero-image-content">
                    <div class="hero-icon-group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 010 7.75"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="info-section">
            <div class="container">
                <div class="section-header-center">
                    <h2>Nuestra Labor</h2>
                    <p>Trabajamos para garantizar el pleno ejercicio de los derechos de la infancia.</p>
                </div>

                <div class="info-grid">
                    <div class="info-card">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 010 7.75"></path>
                            </svg>
                        </div>
                        <h3>¿Qué es SIMPINNA?</h3>
                        <p>Es el órgano encargado de coordinar, articular y promover las políticas públicas para la protección integral de niñas, niños y adolescentes en el municipio.</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        </div>
                        <h3>Nuestros Valores</h3>
                        <p>Actuamos bajo los principios de interés superior de la niñez, igualdad, no discriminación y participación inclusiva en todos los ámbitos sociales.</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h3>Nuestra Misión</h3>
                        <p>Garantizar que cada niño, niña y adolescente en Tecate viva en un entorno libre de violencia y con acceso pleno a sus derechos humanos.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="encuesta-section">
            <div class="container">
                <div class="encuesta-content">
                    <div class="encuesta-left">
                        <span class="encuesta-badge">Participación Ciudadana</span>
                        <h2>Ayúdanos a identificar desafíos y oportunidades del Municipio.</h2>
                        <p>Tu opinión es fundamental. Al responder esta encuesta anónima, nos ayudas a identificar áreas de oportunidad para mejorar la protección de tus derechos.</p>

                        <ul class="encuesta-beneficios">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Es completamente anónima y confidencial
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Toma menos de 5 minutos
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Contribuyes a mejorar tu comunidad
                            </li>
                        </ul>

                        <a href="/front-end/frames/inicio/seleccion-encuesta.php" class="btn-primary">
                            Iniciar encuesta ahora <i class="fa-solid fa-arrow-pointer"></i>
                        </a>
                    </div>

                    <div class="encuesta-right">
                        <div class="encuesta-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ayuda-cta-section">
            <div class="container">
                <div class="ayuda-cta-box">
                    <h2>¿Necesitas ayuda o quieres reportar algo?</h2>
                    <p>Si conoces alguna situación de riesgo o vulneración de derechos, repórtala de manera segura.</p>

                    <a href="/front-end/frames/inicio/contacto.php" class="btn-outline-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
                        </svg>
                        Enviar Reporte
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/footer.php'); ?>
    </footer>

    <script src="/front-end/scripts/header-menu.js"></script>
      <script src="https://kit.fontawesome.com/cba4ea3b6f.js" crossorigin="anonymous"></script>

</body>
</html>