<html lang="en">
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
      <h1 class="card-title">SMPINNA | Sistema Municipal de Protección Integral de los Derechos de las Niñas, Niños y Adolescentes</h1>

      <p>
        En las oficinas de la Secretaría del Ayuntamiento:
        <strong>Pdte. Pascual Ortiz Rubio 1310, Zona Centro, C.P. 21400</strong><br>
        <strong>Tecate, Baja California, México</strong>
      </p>

      <p><strong>Teléfono:</strong> (665) 654-9212</p>

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
    </div>
  </section>
</main>


<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>
</body>
</html>