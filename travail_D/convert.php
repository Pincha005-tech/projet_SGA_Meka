<?php
/**
 * Utilitaire hors flux web principal : copie les données depuis les fichiers JSON
 * vers les .txt sérialisés utilisés par charger_*() et generer_planning().
 *
 * À lancer manuellement si vous éditez data/*.json et voulez resynchroniser les .txt.
 * Attention : planning.json → planning.txt ici est au format sérialisé PHP, différent
 * du fichier planning.txt racine produit par sauvegarder_planning() (lignes texte).
 */
$salles = json_decode(file_get_contents('data/salles.json'), true);
file_put_contents('data/salles.txt', serialize($salles));

$promotions = json_decode(file_get_contents('data/promotions.json'), true);
file_put_contents('data/promotions.txt', serialize($promotions));

$cours = json_decode(file_get_contents('data/cours.json'), true);
file_put_contents('data/cours.txt', serialize($cours));

$options = json_decode(file_get_contents('data/options.json'), true);
file_put_contents('data/options.txt', serialize($options));

$planning = json_decode(file_get_contents('data/planning.json'), true);
file_put_contents('data/planning.txt', serialize($planning));

echo "Conversion done.";
?>