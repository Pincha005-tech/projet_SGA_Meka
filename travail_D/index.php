<?php
/**
 * Page principale du planning (TD SGA — UPC).
 *
 * À chaque chargement : charge salles / promotions / cours / options depuis data/*.txt,
 * construit les créneaux (lun–ven matin & après-midi), génère le planning puis
 * l’enregistre dans planning.txt et l’affiche en tableau HTML.
 *
 * Accès réservé aux utilisateurs connectés (voir auth.php).
 */
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/fonctions.php";

auth_require_login();

$messages = [];
$errors = [];

// --- Chargement des données persistées (fichiers sérialisés) ---
$salles = charger_salles("data/salles.txt");
if ($salles === null) {
    $errors[] = "Erreur lors du chargement des salles.";
} else {
    $messages[] = "Fichier des salles chargé avec succès.";
}

$promotions = charger_promotions("data/promotions.txt");
if ($promotions === null) {
    $errors[] = "Erreur lors du chargement des promotions.";
} else {
    $messages[] = "Fichier des promotions chargé avec succès.";
}

$cours = charger_cours("data/cours.txt");
if ($cours === null) {
    $errors[] = "Erreur lors du chargement des cours.";
} else {
    $messages[] = "Fichier des cours chargé avec succès.";
}

$options = charger_options("data/options.txt");
if ($options === null) {
    $errors[] = "Erreur lors du chargement des options.";
} else {
    $messages[] = "Fichier des options chargé avec succès.";
}

$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
$creneaux = [];
foreach ($jours as $jour) {
    $creneaux[] = "$jour 8h00-12h00";
    $creneaux[] = "$jour 13h00-17h00";
}
$messages[] = "Créneaux horaires définis correctement.";

// --- Génération et sauvegarde du planning (écrase planning.txt à chaque visite) ---
$planning = [];
if (empty($errors)) {
    $planning = generer_planning($salles, $promotions, $cours, $options, $creneaux);
    if (empty($planning)) {
        $errors[] = "Aucun planning n'a pu être généré. Vérifiez vos données.";
    } else {
        $messages[] = "Planning généré avec succès.";
        sauvegarder_planning($planning, "planning.txt");
        $messages[] = "Planning sauvegardé dans planning.txt.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Planning SGA - UPC</title>
  <!-- Styles locaux à cette page (tableau planning + messages) -->
  <style>
    :root {
      --bg: #f8fafc;
      --card: #ffffff;
      --text: #0f172a;
      --muted: #475569;
      --success-bg: #dcfce7;
      --success-tx: #14532d;
      --error-bg: #fee2e2;
      --error-tx: #7f1d1d;
      --line: #e2e8f0;
      --primary: #0f4fa8;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      color: var(--text);
      background: var(--bg);
    }
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 16px;
    }
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
    h1 {
      margin: 0;
      font-size: 1.35rem;
    }
    .meta {
      margin: 0;
      color: var(--muted);
      font-size: 0.9rem;
    }
    .logout {
      display: inline-block;
      padding: 10px 12px;
      border-radius: 10px;
      text-decoration: none;
      background: var(--primary);
      color: #fff;
      font-weight: 700;
      font-size: 0.9rem;
    }
    .card {
      background: var(--card);
      border-radius: 12px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
      padding: 14px;
      margin-bottom: 14px;
    }
    .msg, .err {
      border-radius: 8px;
      padding: 8px 10px;
      margin-bottom: 8px;
      font-size: 0.92rem;
    }
    .msg { background: var(--success-bg); color: var(--success-tx); }
    .err { background: var(--error-bg); color: var(--error-tx); }
    .table-wrap {
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 620px;
    }
    th, td {
      border: 1px solid var(--line);
      padding: 8px;
      text-align: left;
      font-size: 0.92rem;
    }
    th {
      background: #eff6ff;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div>
        <h1>Gestion des auditoires - Génération du planning</h1>
        <p class="meta">Connecté: <?php echo htmlspecialchars($_SESSION["auth_user"]["username"], ENT_QUOTES, "UTF-8"); ?></p>
      </div>
      <span style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <a class="logout" href="accueil.php" style="background:#64748b;">Accueil</a>
        <a class="logout" href="logout.php">Déconnexion</a>
      </span>
    </div>

    <div class="card">
      <?php foreach ($messages as $message): ?>
        <div class="msg"><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></div>
      <?php endforeach; ?>
      <?php foreach ($errors as $error): ?>
        <div class="err"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($planning)): ?>
      <div class="card">
        <h2>Planning généré</h2>
        <div class="table-wrap">
          <table>
            <tr><th>Créneau</th><th>Salle</th><th>Cours</th><th>Groupe</th></tr>
            <?php foreach ($planning as $affectation): ?>
              <tr>
                <td><?php echo htmlspecialchars($affectation["creneau"], ENT_QUOTES, "UTF-8"); ?></td>
                <td><?php echo htmlspecialchars($affectation["salle"], ENT_QUOTES, "UTF-8"); ?></td>
                <td><?php echo htmlspecialchars($affectation["cours"], ENT_QUOTES, "UTF-8"); ?></td>
                <td><?php echo htmlspecialchars($affectation["groupe"], ENT_QUOTES, "UTF-8"); ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
