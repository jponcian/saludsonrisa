<?php
session_start();

// Destruir todas las variables de sesión.
session_unset();

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir al login
header('Location: ../login.html');
exit();
?>