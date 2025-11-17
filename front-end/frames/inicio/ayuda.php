<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/ayuda.css">
    <title>SIMPINNA | Ayuda</title>
</head>
<body>
    
<header> 
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
</header>
<main class="ayuda-layout">
  <section class="ayuda-grid">

    <!--Info de dpto de Simpinna-->
    <div class="card-amarillo" role="region" aria-label="Información de contacto">
      <h1 class="card-title">SIMPINNA | Sistema Municipal de Protección Integral de los Derechos de las Niñas, Niños y Adolescentes</h1>

      <p>
        En las oficinas de la Secretaría del Ayuntamiento:
        <strong>Pdte. Pascual Ortiz Rubio 1310, Zona Centro, C.P. 21400</strong><br>
        <strong>Tecate, Baja California, México</strong>
      </p>

      <p><strong>Teléfono:</strong> (665) 654-9212</p>
      <p><strong>Email:</strong> simpinna@tecate.gob.mx</p>
      <p class="fb-line">
        <strong>Facebook:</strong> Simpinna Tecate —
        <a href="https://www.facebook.com/profile.php?id=61552813675496" target="_blank" rel="noopener">
          Haz click para entrar al Facebook
        </a>
      </p>
    </div>

    <!--Carta de comunidad-->
    <div class="card-amarillo card-comunidad" role="region" aria-label="Súmate a la comunidad Tecatense">
      <h2 class="card-subtitle">Súmate a la comunidad Tecatense</h2>

      <p>
        <strong>Sé parte</strong> de la comunidad Tecatense que se están sumando con sus acciones, difundiendo desde sus redes sociales,
        blogs, radio, por internet, videos, etcétera, con tu estilo único, con tu grupo de amistades, tus ideas creativas,
        compartiendo los Derechos a través de acciones de manera positiva, para sensibilizar y concientizar; en las escuelas,
        en la familia, en el trabajo, en los parques y en donde se requiera.
      </p>

      <p class="accent">
        ¡Tus acciones tienen el potencial de mejorar, traer <strong>Armonía y Paz</strong>! <strong>¡Haz la prueba y verás!</strong>
      </p>

      <p>
        <strong>¡Juntos somos parte fundamental para lograrlo!</strong><br>
        Nos encantaría saber de ti, con el <strong>#SimpinnaTecate</strong>.
      </p>
    </div>

  </section>

  <!--Bloques de numeros de emergency-->
  <section class="emergency-cta" aria-label="Números de emergencia">
    <p class="emergency-text">
      Si alguien que amas o si tú necesitas ayuda,
      marca a estos números desde tu casa o desde algún celular aunque no tenga crédito
    </p>

    <div class="emergency-images">
      <img src="/SIMPINNA/front-end/assets/img/inicio/911.png" alt="911 Emergencias" class="emg-img">
      <img src="/SIMPINNA/front-end/assets/img/inicio/089.png" alt="089 Denuncia Anónima Baja California" class="emg-img">
      <img src="/SIMPINNA/front-end/assets/img/global/linea de la vida.jpeg" alt="075 Línea de la Vida" class="emg-img">
    </div>

    <!-- Botón para abrir modal -->
    <button class="btn-more-numbers" onclick="openContactModal()">
      Mirar más números de ayuda en Tecate
    </button>
  </section>

  <!-- Modal con números de contacto -->
  <div id="contactModal" class="contact-modal" onclick="closeContactModalOnOverlay(event)">
    <div class="modal-content">
      <button class="modal-close" onclick="closeContactModal()">&times;</button>
      
      <h2 class="modal-title">Red de Protección - Números de Ayuda en Tecate</h2>
      
      <div class="contact-grid">
        
        <!-- Emergencias -->
        <div class="contact-card emergency">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <h3>911</h3>
          <p class="contact-subtitle">Policía, Bomberos o Ambulancia</p>
        </div>

        <div class="contact-card emergency">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
            </svg>
          </div>
          <h3>089</h3>
          <p class="contact-subtitle">Denuncia Anónima</p>
          <p class="contact-detail">Cuando veas a alguien haciendo cosas peligrosas, violentas o dañinas y quieras permanecer en el anonimato</p>
        </div>

        <!-- Salud Mental -->
        <div class="contact-card mental-health">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
          </div>
          <h3>075</h3>
          <p class="contact-subtitle">Línea Estatal de Crisis 24/7</p>
          <p class="contact-detail">Si necesitas ayuda porque tú o alguien está en crisis o enojado</p>
        </div>

        <div class="contact-card mental-health">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
              <line x1="12" y1="18" x2="12.01" y2="18"/>
            </svg>
          </div>
          <h3>TKTiende</h3>
          <p class="contact-subtitle">Por WhatsApp</p>
          <p class="contact-detail">665-120-4036</p>
        </div>

        <!-- Instituciones Municipales -->
        <div class="contact-card municipal">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 21h18"/>
              <path d="M5 21V7l8-4v18"/>
              <path d="M19 21V11l-6-4"/>
              <path d="M9 9v.01"/>
              <path d="M9 12v.01"/>
              <path d="M9 15v.01"/>
              <path d="M9 18v.01"/>
            </svg>
          </div>
          <h3>665-654-9200</h3>
          <p class="contact-subtitle">Presidencia Municipal</p>
        </div>

        <div class="contact-card municipal">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
          </div>
          <h3>SIMPINNA Tecate</h3>
          <p class="contact-subtitle">Sistema Municipal de Protección</p>
          <p class="contact-detail">simpinna@tecate.gob.mx</p>
          <p class="contact-detail">665-654-9212</p>
        </div>

        <!-- Servicios Básicos -->
        <div class="contact-card services">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
            </svg>
          </div>
          <h3>073</h3>
          <p class="contact-subtitle">C.E.S.P.T. (Agua)</p>
        </div>

        <div class="contact-card services">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
          </div>
          <h3>071</h3>
          <p class="contact-subtitle">C.F.E. (Electricidad)</p>
        </div>

        <!-- Apoyo Social -->
        <div class="contact-card social">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87"/>
              <path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <h3>079</h3>
          <p class="contact-subtitle">INMUJERES</p>
          <p class="contact-detail">Especializado en atención para mujeres en situación de violencia que requieran apoyo psicológico</p>
        </div>

        <div class="contact-card social">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <h3>800-911-2000</h3>
          <p class="contact-subtitle">Línea de la Vida(Nacional)</p>
          <p class="contact-detail">Intervención en crisis (ideación e intento de suicidio)</p>
        </div>

        <div class="contact-card social">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <polyline points="9 12 11 14 15 10"/>
            </svg>
          </div>
          <h3>088</h3>
          <p class="contact-subtitle">Guardia Nacional</p>
          <p class="contact-detail">Denuncia de violencia en línea contra Niñas, Niños y Adolescentes</p>
        </div>

        <!-- DIF -->
        <div class="contact-card dif">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <h3>800-34322-20</h3>
          <p class="contact-subtitle">FISCALÍA GENERAL DEL ESTADO DE BAJA CALIFORNIA</p>
          <p class="contact-detail">www.fgebc.gob.mx</p>
          <p class="contact-detail">Denuncia Virtual de la Fiscalía, escanea el código QR</p>
        </div>

        <div class="contact-card dif">
          <div class="contact-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="8.5" cy="7" r="4"/>
              <line x1="20" y1="8" x2="20" y2="14"/>
              <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
          </div>
          <h3>400-5533-000</h3>
          <p class="contact-subtitle">Línea Nacional contra la Trata de Personas</p>
          <p class="contact-detail">Delitos como: Secuestro, Extorsión, Desapariciones, Homicidio, Feminicidio, Fraude cibernético, Violencia contra NNA, Abuso Sexual, Línea Anónima</p>
        </div>

      </div>

      <p class="modal-footer">
        Si alguien que amas o si tú necesitas ayuda, marca a estos números desde tu casa o desde algún celular aunque no tenga crédito.
      </p>
    </div>
  </div>
</main>


<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

<script type="module">
    import { 
        openContactModal, 
        closeContactModal, 
        closeContactModalOnOverlay 
    } from "/SIMPINNA/front-end/scripts/ayuda/modal-ayuda.js";

    // Hacer funciones accesibles desde onclick=""
    window.openContactModal = openContactModal;
    window.closeContactModal = closeContactModal;
    window.closeContactModalOnOverlay = closeContactModalOnOverlay;
</script>

</body>
</html>