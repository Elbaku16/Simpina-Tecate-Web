<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin.css">

    <title>Iniciar Sesion</title>
</head>
<body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'); ?>
    </header>
    
   <main class="login-container">
        <h2 class="login-title">Inicio de sesión</h2>

        <form class="login-form" method="POST" action="/SIMPINNA/back-end/login.php"  autocomplete="off">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="off> 
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-login">Iniciar sesión</button>

            <p class="login-note">Solo personal autorizado SIMPINNA</p>
        </form>
    </main>



    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
    </footer>
</body>
</html>