<?php
/**
 * Lecture seule des données métier stockées dans data/*.txt (même source que le planning).
 * Affiche trois tableaux : salles, promotions, cours (pas d’édition ici).
 */
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/fonctions.php";
auth_require_login();

$PAGE_TITLE = "Consultation des données";
$WRAP_CLASS = "sga-wrap sga-accueil-main";
require __DIR__ . "/inc/layout_top.php";

// Normalisation en tableaux vides si fichier absent ou invalide
$salles = charger_salles(__DIR__ . "/data/salles.txt");
if (!is_array($salles)) {
    $salles = [];
}

$promotions = charger_promotions(__DIR__ . "/data/promotions.txt");
if (!is_array($promotions)) {
    $promotions = [];
}

$cours = charger_cours(__DIR__ . "/data/cours.txt");
if (!is_array($cours)) {
    $cours = [];
}
?>

<div class="sga-card sga-consult-block">
  <h1 class="sga-title">Données enregistrées</h1>
  <p class="sga-lead">Contenu actuel des fichiers utilisés pour générer le planning (<code>data/*.txt</code>).</p>
  <div class="sga-actions" style="margin-top: 0; margin-bottom: 20px;">
    <a class="sga-btn sga-btn-secondary" href="accueil.php">← Accueil</a>
    <a class="sga-btn sga-btn-primary" href="index.php">Voir le planning</a>
  </div>

  <h2 class="sga-subtitle" id="salles">Salles</h2>
  <?php if (empty($salles)): ?>
    <p class="sga-muted-text">Aucune salle enregistrée.</p>
  <?php else: ?>
    <div class="sga-table-wrap">
      <table class="sga-table">
        <thead>
          <tr><th>Identifiant</th><th>Capacité</th><th>Autres infos</th></tr>
        </thead>
        <tbody>
          <?php foreach ($salles as $id => $row): ?>
            <tr>
              <td><?php echo htmlspecialchars((string)$id, ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo isset($row["capacite"]) ? (int)$row["capacite"] : "—"; ?></td>
              <td><?php
                  $extra = $row;
                  unset($extra["capacite"]);
                  echo $extra === []
                      ? "—"
                      : htmlspecialchars(json_encode($extra, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8");
                  ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h2 class="sga-subtitle" id="promotions">Promotions</h2>
  <?php if (empty($promotions)): ?>
    <p class="sga-muted-text">Aucune promotion enregistrée.</p>
  <?php else: ?>
    <div class="sga-table-wrap">
      <table class="sga-table">
        <thead>
          <tr><th>Code</th><th>Effectif</th><th>Autres infos</th></tr>
        </thead>
        <tbody>
          <?php foreach ($promotions as $id => $row): ?>
            <tr>
              <td><?php echo htmlspecialchars((string)$id, ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo isset($row["effectif"]) ? (int)$row["effectif"] : "—"; ?></td>
              <td><?php
                  $extra = $row;
                  unset($extra["effectif"]);
                  echo $extra === []
                      ? "—"
                      : htmlspecialchars(json_encode($extra, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8");
                  ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h2 class="sga-subtitle" id="cours">Cours</h2>
  <?php if (empty($cours)): ?>
    <p class="sga-muted-text">Aucun cours enregistré.</p>
  <?php else: ?>
    <div class="sga-table-wrap">
      <table class="sga-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Intitulé</th>
            <th>Promotion</th>
            <th>Jour</th>
            <th>Heure</th>
            <th>Titulaire</th>
            <th>Collaborateur</th>
            <th>Local</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cours as $row): ?>
            <?php if (!is_array($row)) {
                continue;
            } ?>
            <tr>
              <td><?php echo htmlspecialchars((string)($row["id"] ?? "—"), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["intitule"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["promotion"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["jour"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["heure"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["titulaire"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["collaborateur"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
              <td><?php echo htmlspecialchars((string)($row["local"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . "/inc/layout_bottom.php"; ?>
