<?php
/**
 * Traitement POST du formulaire formulaires/form_cours.php :
 * ajoute une ligne au tableau sérialisé data/cours.txt (id unique + champs du formulaire).
 */
require_once __DIR__ . "/auth.php";
auth_require_login();

$PAGE_TITLE = "Enregistrement d'un cours";
require __DIR__ . "/inc/layout_top.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo '<div class="sga-card"><p class="sga-lead">Aucune donnée reçue.</p><div class="sga-actions"><a class="sga-btn sga-btn-primary" href="formulaires/form_cours.php">Ouvrir le formulaire</a></div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$cours = [
    "id" => uniqid("C"),
    "intitule" => trim($_POST["intitule"] ?? ""),
    "promotion" => trim($_POST["promotion"] ?? ""),
    "jour" => trim($_POST["jour"] ?? ""),
    "heure" => trim($_POST["heure"] ?? ""),
    "titulaire" => trim($_POST["titulaire"] ?? ""),
    "collaborateur" => trim($_POST["collaborateur"] ?? ""),
    "local" => trim($_POST["local"] ?? "")
];

if ($cours["intitule"] === "" || $cours["promotion"] === "") {
    echo '<div class="sga-card"><div class="sga-alert sga-alert-error">Intitulé ou promotion manquant.</div><div class="sga-actions"><a class="sga-btn sga-btn-primary" href="formulaires/form_cours.php">Retour au formulaire</a></div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$fichier = __DIR__ . "/data/cours.txt";
if (!file_exists($fichier)) {
    echo '<div class="sga-card"><div class="sga-alert sga-alert-error">Fichier data/cours.txt introuvable.</div></div>';
    require __DIR__ . "/inc/layout_bottom.php";
    exit;
}

$coursData = unserialize(file_get_contents($fichier));
if (!is_array($coursData)) {
    $coursData = [];
}

$coursData[] = $cours;
file_put_contents($fichier, serialize($coursData));

$hInt = htmlspecialchars($cours["intitule"], ENT_QUOTES, "UTF-8");
?>

<div class="sga-card">
  <div class="sga-alert sga-alert-success">
    Cours <strong><?php echo $hInt; ?></strong> enregistré avec succès.
  </div>
  <div class="sga-actions">
    <a class="sga-btn sga-btn-primary" href="formulaires/form_cours.php">Ajouter un autre cours</a>
    <a class="sga-btn sga-btn-secondary" href="accueil.php">Accueil</a>
  </div>
</div>

<?php require __DIR__ . "/inc/layout_bottom.php"; ?>
