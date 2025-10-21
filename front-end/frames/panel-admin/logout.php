<?php
session_start();
session_unset();  
session_destroy(); // destruye la sesion
header("Location: /SIMPINNA/front-end/frames/panel-admin/login.php");
exit;
?>
