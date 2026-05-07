<?php
/**
 * Tableau de bord après connexion : liens vers les formulaires de saisie,
 * la consultation des données et la page de génération du planning (index.php).
 */
require_once __DIR__ . "/auth.php";
auth_require_login();

// Variables pour inc/layout_top.php (titre + grille élargie)
$PAGE_TITLE = "Accueil — Gestion des auditoires";
$WRAP_CLASS = "sga-wrap sga-accueil-main";
require __DIR__ . "/inc/layout_top.php";

$user = htmlspecialchars($_SESSION["auth_user"]["username"] ?? "", ENT_QUOTES, "UTF-8");
?>

<div class="sga-card">
  <h1 class="sga-title">Gestion des auditoires et horaires</h1>
  <p class="sga-lead">Université Protestante au Congo — Faculté des Sciences Informatiques</p>
  <p class="sga-lead">Connecté : <strong><?php echo $user; ?></strong></p>
  <p class="sga-lead"><strong>Saisie</strong> — ajouter des entrées.</p>

  <div class="sga-menu-grid">
    <a href="formulaires/form_salle.php">Ajouter une salle</a>
    <a href="formulaires/form_promotion.php">Ajouter une promotion</a>
    <a href="formulaires/form_cours.php">Ajouter un cours</a>
    <a href="index.php">Générer / voir le planning</a>
  </div>

  <p class="sga-lead sga-lead-spaced"><strong>Consultation</strong> — voir ce qui est enregistré dans les fichiers.</p>

  <div class="sga-menu-grid sga-menu-grid-accent">
    <a href="consultation.php">Voir salles, promotions et cours</a>
  </div>
</div>

<?php require __DIR__ . "/inc/layout_bottom.php"; ?>
