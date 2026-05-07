<?php
/**
 * Début du gabarit HTML commun (header + navigation + ouverture zone contenu).
 *
 * Définir avant inclusion : $PAGE_TITLE, éventuellement $CSS_HREF, $LINK_ACCUEIL,
 * $LINK_PLANNING, $LINK_LOGOUT, $WRAP_CLASS (classe CSS du conteneur principal).
 */
if (!isset($PAGE_TITLE)) {
    $PAGE_TITLE = "SGA UPC";
}
if (!isset($CSS_HREF)) {
    $CSS_HREF = "css/app.css";
}
if (!isset($LINK_ACCUEIL)) {
    $LINK_ACCUEIL = "accueil.php";
}
if (!isset($LINK_PLANNING)) {
    $LINK_PLANNING = "index.php";
}
if (!isset($LINK_LOGOUT)) {
    $LINK_LOGOUT = "logout.php";
}
if (!isset($WRAP_CLASS)) {
    $WRAP_CLASS = "sga-wrap";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($PAGE_TITLE, ENT_QUOTES, "UTF-8"); ?></title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($CSS_HREF, ENT_QUOTES, "UTF-8"); ?>">
</head>
<body class="sga-page">
  <header class="sga-header">
    <div class="sga-header-inner">
      <span class="sga-logo">SGA · UPC</span>
      <nav class="sga-nav">
        <a href="<?php echo htmlspecialchars($LINK_ACCUEIL, ENT_QUOTES, "UTF-8"); ?>">Accueil</a>
        <a href="<?php echo htmlspecialchars($LINK_PLANNING, ENT_QUOTES, "UTF-8"); ?>">Planning</a>
        <a href="<?php echo htmlspecialchars($LINK_LOGOUT, ENT_QUOTES, "UTF-8"); ?>" class="sga-nav-out">Déconnexion</a>
      </nav>
    </div>
  </header>
  <div class="<?php echo htmlspecialchars($WRAP_CLASS, ENT_QUOTES, "UTF-8"); ?>">
