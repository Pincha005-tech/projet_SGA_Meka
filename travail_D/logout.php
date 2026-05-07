<?php
/**
 * Détruit la session PHP et renvoie vers la page de connexion.
 */
require_once __DIR__ . "/auth.php";
auth_logout();
header("Location: login.php");
exit;
