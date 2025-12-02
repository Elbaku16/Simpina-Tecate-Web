<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPINNA | Ayuda</title>
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/header-responsive.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/ayuda.css">
</head>
<body>
    
<header> 
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header.php'); ?>
</header>

<main class="ayuda-layout">
  
  <div class="ayuda-header">
      <h1>Centro de Ayuda</h1>
      <p>Información de contacto y números de emergencia en Tecate.</p>
  </div>

  <div class="ayuda-container">
      
      <section class="info-card contact-card">
          <div class="card-header">
              <h2>Oficinas SIMPINNA</h2>
              <div class="divider"></div>
          </div>
          
          <div class="info-item">
              <div class="icon-box"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
              <div>
                  <strong>Ubicación:</strong>
                  <p>Secretaría del Ayuntamiento<br>Pdte. Pascual Ortiz Rubio 1310, Zona Centro<br>Tecate, B.C.</p>
              </div>
          </div>

          <div class="info-item">
              <div class="icon-box"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
              <div>
                  <strong>Teléfono:</strong>
                  <p>(665) 654-9212</p>
              </div>
          </div>

          <div class="info-item">
              <div class="icon-box"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></div>
              <div>
                  <strong>Email:</strong>
                  <p>simpinna@tecate.gob.mx</p>
              </div>
          </div>

          <a href="https://www.facebook.com/simpinnatkt/" target="_blank" class="btn-social">
              <svg style="width:18px; margin-right:8px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
              Visitar Facebook
          </a>
      </section>

      <section class="info-card community-card">
          <div class="card-header">
              <h2>Comunidad Tecatense</h2>
              <div class="divider"></div>
          </div>
          
          <p class="text-body">
             <strong>Sé parte del cambio.</strong> Súmate difundiendo los derechos desde tus redes sociales, escuela o familia. Tu voz cuenta.
          </p>

          <div class="quote-box">
              <p>¡Tus acciones tienen el potencial de traer <strong>Armonía y Paz</strong>!</p>
          </div>

          <p class="text-body">
             Juntos somos fundamentales. Únete con el hashtag:
          </p>
          <div class="hashtag-pill">#SimpinnaTecate</div>
      </section>

  </div>

  <section class="emergency-strip">
      <div class="emergency-header">
          <h3>¿Necesitas ayuda urgente?</h3>
          <p>Si tú o alguien que conoces está en peligro, marca estos números gratis.</p>
      </div>
      
      <div class="emergency-images">
          <img src="/front-end/assets/img/inicio/911.png" alt="911 Emergencias" class="emg-img">
          <img src="/front-end/assets/img/inicio/089.png" alt="089 Denuncia Anónima" class="emg-img">
          <img src="/front-end/assets/img/global/linea de la vida.jpeg" alt="075 Línea de la Vida" class="emg-img">
      </div>

      <button class="btn-directory" onclick="openContactModal()">
          Ver más números de ayuda en Tecate
      </button>
  </section>

  <div id="contactModal" class="contact-modal" onclick="closeContactModalOnOverlay(event)">
    <div class="modal-container">
      <button class="modal-close" onclick="closeContactModal()">&times;</button>
      
      <div class="modal-header">
          <h2>Directorio de Protección - Tecate</h2>
      </div>
      
      <div class="directory-grid">
          
          <div class="dir-group">
              <h4>Emergencias</h4>
              <div class="dir-card emergency">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                          <line x1="12" y1="9" x2="12" y2="13"/>
                          <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                      </div>
                      <span class="d-num">911</span>
                  </div>
                  <span class="d-title">Policía, Bomberos, Ambulancia</span>
              </div>

              <div class="dir-card emergency">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                      </div>
                      <span class="d-num">089</span>
                  </div>
                  <span class="d-title">Denuncia Anónima</span>
                  <small>Reportes de peligro o violencia anónimos.</small>
              </div>
          </div>

          <div class="dir-group">
              <h4>Salud Mental</h4>
              <div class="dir-card mental-health">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                        </svg>
                      </div>
                      <span class="d-num">075</span>
                  </div>
                  <span class="d-title">Línea Estatal de Crisis 24/7</span>
                  <small>Apoyo en crisis emocionales.</small>
              </div>

              <div class="dir-card mental-health">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <circle cx="12" cy="12" r="10"/>
                          <line x1="12" y1="8" x2="12" y2="12"/>
                          <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                      </div>
                      <span class="d-num">800-911-2000</span>
                  </div>
                  <span class="d-title">Línea de la Vida (Nacional)</span>
                  <small>Prevención del suicidio y adicciones.</small>
              </div>

              <div class="dir-card mental-health">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                          <line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                      </div>
                      <span class="d-num">665-120-4036</span>
                  </div>
                  <span class="d-title">TKTiende (WhatsApp)</span>
              </div>
          </div>

          <div class="dir-group">
              <h4>Instituciones</h4>
              <div class="dir-card municipal">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/>
                          <path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
                        </svg>
                      </div>
                      <span class="d-num">665-654-9200</span>
                  </div>
                  <span class="d-title">Presidencia Municipal</span>
              </div>

              <div class="dir-card municipal">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                      </div>
                      <span class="d-num">665-654-9212</span>
                  </div>
                  <span class="d-title">SIMPINNA Tecate</span>
              </div>

              <div class="dir-card social">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                          <circle cx="9" cy="7" r="4"/>
                          <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                          <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                      </div>
                      <span class="d-num">079</span>
                  </div>
                  <span class="d-title">INMUJERES</span>
                  <small>Atención especializada a mujeres.</small>
              </div>
          </div>

          <div class="dir-group">
              <h4>Justicia</h4>
              <div class="dir-card social">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                          <polyline points="9 12 11 14 15 10"/>
                        </svg>
                      </div>
                      <span class="d-num">088</span>
                  </div>
                  <span class="d-title">Guardia Nacional</span>
                  <small>Denuncia violencia cibernética contra NNA.</small>
              </div>

              <div class="dir-card dif">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                          <circle cx="12" cy="7" r="4"/>
                        </svg>
                      </div>
                      <span class="d-num">800-34322-20</span>
                  </div>
                  <span class="d-title">Fiscalía General BC</span>
                  <small>Denuncia virtual en fgebc.gob.mx</small>
              </div>

              <div class="dir-card dif">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                          <circle cx="8.5" cy="7" r="4"/>
                          <line x1="20" y1="8" x2="20" y2="14"/>
                          <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                      </div>
                      <span class="d-num">400-5533-000</span>
                  </div>
                  <span class="d-title">Contra la Trata</span>
                  <small>Secuestro, extorsión, abuso.</small>
              </div>
          </div>

          <div class="dir-group">
              <h4>Servicios</h4>
              <div class="dir-card services">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                        </svg>
                      </div>
                      <span class="d-num">073</span>
                  </div>
                  <span class="d-title">C.E.S.P.T. (Agua)</span>
              </div>

              <div class="dir-card services">
                  <div class="d-top">
                      <div class="contact-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                      </div>
                      <span class="d-num">071</span>
                  </div>
                  <span class="d-title">C.F.E. (Electricidad)</span>
              </div>
          </div>

      </div>

      <p class="modal-footer">
        Si alguien que amas o si tú necesitas ayuda, marca a estos números desde tu casa o desde algún celular aunque no tenga crédito.
      </p>
    </div>
  </div>

</main>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/footer.php'; ?>
</footer>

<script type="module">
    import { openContactModal, closeContactModal, closeContactModalOnOverlay } from "/front-end/scripts/ayuda/modal-ayuda.js";
    window.openContactModal = openContactModal;
    window.closeContactModal = closeContactModal;
    window.closeContactModalOnOverlay = closeContactModalOnOverlay;
</script>
<script src="/front-end/scripts/header-menu.js"></script>

</body>
</html>