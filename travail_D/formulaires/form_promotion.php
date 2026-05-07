<?php
/**
 * Formulaire — nouvelle promotion : envoi POST vers ../ajouter_prom.php.
 */
require_once __DIR__ . "/../auth.php";
auth_require_login();

$PAGE_TITLE = "Ajouter une promotion";
$CSS_HREF = "../css/app.css";
$LINK_ACCUEIL = "../accueil.php";
$LINK_PLANNING = "../index.php";
$LINK_LOGOUT = "../logout.php";
require __DIR__ . "/../inc/layout_top.php";
?>

<div class="sga-card">
  <h1 class="sga-title">Ajouter une promotion</h1>
  <p class="sga-lead">Code promotion (ex. L1, L2) et effectif total.</p>
  <form class="sga-form" method="post" action="../ajouter_prom.php">
    <label for="nom">Nom de la promotion</label>
    <input type="text" id="nom" name="nom" required placeholder="ex. L2" autocomplete="off">

    <label for="effectif">Effectif</label>
    <input type="number" id="effectif" name="effectif" required min="1">

    <div class="sga-actions">
      <button type="submit" class="sga-btn sga-btn-primary">Enregistrer</button>
      <a class="sga-btn sga-btn-secondary" href="../accueil.php">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . "/../inc/layout_bottom.php"; ?>
