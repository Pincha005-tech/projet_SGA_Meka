<?php
/**
 * Traitement POST du formulaire formulaires/form_promotion.php :
 * écrit dans data/promotions.txt (code promotion → effectif).
 */
require_once __DIR__ . "/auth.php";
auth_require_login();

$PAGE_TITLE = "Enregistrement d'une promotion";
require __DIR__ . "/inc/layout_top.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo '<div class="sga-card"><p class="sga-lead">Aucune donnée reçue.</p><div class="sga-actions"><a class="sga-btn sga-btn-primary" href="formulaires/form_promotion.php">Ouvrir le formulaire</a></div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$nom = trim($_POST["nom"] ?? "");
$effectif = (int)($_POST["effectif"] ?? 0);

if ($nom === "" || $effectif < 1) {
    echo '<div class="sga-card"><div class="sga-alert sga-alert-error">Nom ou effectif invalide.</div><div class="sga-actions"><a class="sga-btn sga-btn-primary" href="formulaires/form_promotion.php">Retour au formulaire</a></div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$fichier = __DIR__ . "/data/promotions.txt";
if (!file_exists($fichier)) {
    echo '<div class="sga-card"><div class="sga-alert sga-alert-error">Fichier data/promotions.txt introuvable.</div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$promotions = unserialize(file_get_contents($fichier));
if (!is_array($promotions)) {
    $promotions = [];
}

$promotions[$nom] = ["effectif" => $effectif];
file_put_contents($fichier, serialize($promotions));

$hNom = htmlspecialchars($nom, ENT_QUOTES, "UTF-8");
?>

<div class="sga-card">
  <div class="sga-alert sga-alert-success">
    Promotion <strong><?php echo $hNom; ?></strong> enregistrée (effectif : <?php echo (int)$effectif; ?>).
  </div>
  <div class="sga-actions">
    <a class="sga-btn sga-btn-primary" href="formulaires/form_promotion.php">Ajouter une autre promotion</a>
    <a class="sga-btn sga-btn-secondary" href="accueil.php">Accueil</a>
  </div>
</div>

<?php require __DIR__ . "/inc/layout_bottom.php"; ?>
