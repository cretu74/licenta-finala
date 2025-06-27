<?php
// Pornește sesiunea pentru a o accesa
session_start();

// Distruge toate datele sesiunii
session_unset();
session_destroy();

// Redirecționează utilizatorul la pagina de login
header("Location: login.php");
die;
?>
