<?php
/**
 * Traitement POST du formulaire formulaires/form_salle.php : ajoute ou met à jour
 * une entrée dans data/salles.txt (tableau sérialisé identifiant → capacité).
 */
require_once __DIR__ . "/auth.php";
auth_require_login();

$PAGE_TITLE = "Enregistrement d'une salle";
require __DIR__ . "/inc/layout_top.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo '<div class="sga-card"><p class="sga-lead">Aucune donnée reçue.</p><div class="sga-actions"><a class="sga-btn sga-btn-primary" href="formulaires/form_salle.php">Ouvrir le formulaire</a></div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$nom = trim($_POST["nom"] ?? "");
$capacite = (int)($_POST["capacite"] ?? 0);

if ($nom === "" || $capacite < 1) {
    echo '<div class="sga-card"><div class="sga-alert sga-alert-error">Nom ou capacité invalide.</div><div class="sga-actions"><a class="sga-btn sga-btn-primary" href="formulaires/form_salle.php">Retour au formulaire</a></div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$fichier = __DIR__ . "/data/salles.txt";
if (!file_exists($fichier)) {
    echo '<div class="sga-card"><div class="sga-alert sga-alert-error">Fichier data/salles.txt introuvable.</div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$salles = unserialize(file_get_contents($fichier));
if (!is_array($salles)) {
    $salles = [];
}

$salles[$nom] = ["capacite" => $capacite];
file_put_contents($fichier, serialize($salles));

$hNom = htmlspecialchars($nom, ENT_QUOTES, "UTF-8");
?>

<div class="sga-card">
  <div class="sga-alert sga-alert-success">
    Salle <strong><?php echo $hNom; ?></strong> enregistrée avec succès (capacité : <?php echo (int)$capacite; ?>).
  </div>
  <div class="sga-actions">
    <a class="sga-btn sga-btn-primary" href="formulaires/form_salle.php">Ajouter une autre salle</a>
    <a class="sga-btn sga-btn-secondary" href="accueil.php">Accueil</a>
  </div>
</div>

<?php require __DIR__ . "/inc/layout_bottom.php"; ?>
