<?php
session_start();
session_unset();  
session_destroy(); // destruye la sesion
header('Location: ../../../index.php');
exit;
?>
