<?php
/**
 * Formulaire — nouveau cours : envoi POST vers ../ajouter_cours.php.
 */
require_once __DIR__ . "/../auth.php";
auth_require_login();

$PAGE_TITLE = "Ajouter un cours";
$CSS_HREF = "../css/app.css";
$LINK_ACCUEIL = "../accueil.php";
$LINK_PLANNING = "../index.php";
$LINK_LOGOUT = "../logout.php";
require __DIR__ . "/../inc/layout_top.php";
?>

<div class="sga-card">
  <h1 class="sga-title">Ajouter un cours</h1>
  <p class="sga-lead">Les données sont enregistrées dans le fichier cours du projet.</p>
  <form class="sga-form" method="post" action="../ajouter_cours.php">
    <label for="intitule">Intitulé du cours</label>
    <input type="text" id="intitule" name="intitule" required>

    <label for="promotion">Promotion (code groupe)</label>
    <input type="text" id="promotion" name="promotion" required placeholder="ex. L1">

    <label for="jour">Jour</label>
    <input type="text" id="jour" name="jour" required placeholder="ex. Lundi">

    <label for="heure">Heure</label>
    <input type="text" id="heure" name="heure" required placeholder="ex. 8h00-12h00">

    <label for="titulaire">Titulaire</label>
    <input type="text" id="titulaire" name="titulaire">

    <label for="collaborateur">Collaborateur</label>
    <input type="text" id="collaborateur" name="collaborateur">

    <label for="local">Local</label>
    <input type="text" id="local" name="local">

    <div class="sga-actions">
      <button type="submit" class="sga-btn sga-btn-primary">Enregistrer</button>
      <a class="sga-btn sga-btn-secondary" href="../accueil.php">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . "/../inc/layout_bottom.php"; ?>
