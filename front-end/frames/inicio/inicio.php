<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/header-responsive.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/lenin.css">
    <title>SIMPINNA | Inicio</title>
</head>
<body>
    <header> 
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
    </header>
    
    <main>
        <section class="hero-banner">
            <div class="hero-overlay">
                <div class="hero-container">
                    <div class="hero-text-content">
                        <h1 class="hero-title">Sistema Municipal de Protección Integral de Niñas, Niños y Adolescentes</h1>
                        <p class="hero-subtitle">Velamos por el bienestar y los derechos de las niñas, niños y adolescentes de Tecate</p>
                        <div class="hero-buttons">
                            <a href="/SIMPINNA/front-end/frames/inicio/seleccion-encuesta.php" class="btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                                Contestar encuesta
                            </a>
                            <a href="/SIMPINNA/front-end/frames/inicio/contacto.php" class="btn-secondary">
                                Contáctanos
                            </a>
                        </div>
                    </div>
                    <div class="hero-image-content">
                        <div class="hero-icon-group">
                            <svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 010 7.75"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="info-section">
            <div class="container">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 010 7.75"></path>
                            </svg>
                        </div>
                        <h3>¿Qué es SIMPINNA?</h3>
                        <p>Sistema diseñado para la protección integral de los derechos de niñas, niños y adolescentes en Tecate.</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5"></path>
                                <path d="M2 12l10 5 10-5"></path>
                            </svg>
                        </div>
                        <h3>Nuestros Valores</h3>
                        <p>Respeto, inclusión, protección y promoción de los derechos fundamentales de la infancia.</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <h3>Nuestra Misión</h3>
                        <p>Construir un futuro seguro e inclusivo para todas las niñas, niños y adolescentes.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="encuesta-section">
            <div class="container">
                <div class="encuesta-content">
                    <div class="encuesta-left">
                        <span class="encuesta-badge">Tu voz importa</span>
                        <h2>Ayúdanos a conocer tu situación</h2>
                        <p>Queremos saber cómo te sientes y cómo están tus derechos. Esta encuesta nos ayuda a entender mejor las necesidades de las niñas, niños y adolescentes en Tecate.</p>
                        <ul class="encuesta-beneficios">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Es completamente anónima y segura
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Solo toma unos minutos
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Nos ayuda a proteger tus derechos
                            </li>
                        </ul>
                        <a href="/SIMPINNA/front-end/frames/inicio/seleccion-encuesta.php" class="btn-primary">
                            Contestar encuesta
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                    <div class="encuesta-right">
                        <div class="encuesta-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
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

        <section class="ayuda-section">
            <div class="container">
                <div class="ayuda-box">
                    <h2>¿Necesitas ayuda o quieres reportar algo?</h2>
                    <p>Si conoces alguna situación donde se violen los derechos de niñas, niños o adolescentes, repórtala de manera segura y confidencial.</p>
                    <a href="/SIMPINNA/front-end/frames/inicio/contacto.php" class="btn-ayuda">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
                        </svg>
                        Enviar reporte
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
  
    </footer>
    <script src="https://kit.fontawesome.com/cba4ea3b6f.js" crossorigin="anonymous"></script>
    <script src="/SIMPINNA/front-end/scripts/header-menu.js"></script>
</body>
</html>
