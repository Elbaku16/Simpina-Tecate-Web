<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/inicio.css">
        <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/lenin.css">


    <title>SIMPINNA</title>
</head>
<body>
    <header> 
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
    </header>
    <main>
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-left">
                    <img src="/SIMPINNA/front-end/assets/img/ninos-ilustracion.png" alt="Niños felices" class="hero-image">
                    <p class="hero-text">
                        Protegiendo los derechos y el bienestar de las niñas,<br>
                        niños y adolescentes de Tecate, con acciones que<br>
                        construyen un futuro seguro e inclusivo.
                    </p>
                </div>
                
                <div class="hero-right">
                    <div class="encuesta-card">
                        <h2>¿Conoces tus derechos?</h2>
                        <p>¡Pongamoslo a prueba contestando las siguientes preguntas!</p>
                        
                        <img src="/SIMPINNA/front-end/assets/img/encuesta-icon.png" alt="Encuesta" class="encuesta-icon">
                        
                        <a href="/SIMPINNA/front-end/frames/encuesta.php" class="btn-encuesta">Encuesta</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
    </footer>

</body>
</html>