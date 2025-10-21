<?php
session_start();
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /SIMPINNA/front-end/frames/panel-admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin.css">
  <title>Panel Admin</title>
</head>
<body>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'); ?>

  <main style="padding: 2rem;">
    <h1>Panel administrativo</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user']); ?>.</p>

    <p><a href="/SIMPINNA/back-end/logout.php" class="btn btn-secondary">Cerrar sesión</a></p>

  </main>

  <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
</body>
</html>