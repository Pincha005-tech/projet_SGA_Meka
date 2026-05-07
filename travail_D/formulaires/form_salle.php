<?php
/**
 * Formulaire — nouvelle salle : envoi POST vers ../ajouter_salle.php.
 */
require_once __DIR__ . "/../auth.php";
auth_require_login();

// Chemins relatifs depuis formulaires/ vers css et gabarit
$PAGE_TITLE = "Ajouter une salle";
$CSS_HREF = "../css/app.css";
$LINK_ACCUEIL = "../accueil.php";
$LINK_PLANNING = "../index.php";
$LINK_LOGOUT = "../logout.php";
require __DIR__ . "/../inc/layout_top.php";
?>

<div class="sga-card">
  <h1 class="sga-title">Ajouter une salle</h1>
  <p class="sga-lead">Identifiant de la salle et capacité (étudiants).</p>
  <form class="sga-form" method="post" action="../ajouter_salle.php">
    <label for="nom">Identifiant / nom de la salle</label>
    <input type="text" id="nom" name="nom" required autocomplete="off">

    <label for="capacite">Capacité</label>
    <input type="number" id="capacite" name="capacite" required min="1">

    <div class="sga-actions">
      <button type="submit" class="sga-btn sga-btn-primary">Enregistrer</button>
      <a class="sga-btn sga-btn-secondary" href="../accueil.php">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . "/../inc/layout_bottom.php"; ?>
