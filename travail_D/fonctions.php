<?php

/**
 * SYSTÈME DE GESTION DES AUDITOIRES (SGA)
 * Fonctions PHP procédurales pour gérer salles, promotions, cours et planning
 */

// ============================================================================
// SECTION 1: FONCTIONS DE CHARGEMENT DES DONNÉES (Q5)
// ============================================================================

/**
 * Charge les salles depuis le fichier TXT
 * @param string $chemin_fichier Chemin du fichier salles.txt
 * @return array|null Tableau associatif des salles ou null en cas d'erreur
 */
function charger_salles($chemin_fichier) {
    if (!file_exists($chemin_fichier)) {
        error_log("Erreur : fichier $chemin_fichier introuvable.");
        return null;
    }
    $contenu = file_get_contents($chemin_fichier);
    if ($contenu === false) {
        error_log("Erreur de lecture du fichier $chemin_fichier.");
        return null;
    }
    $salles = unserialize($contenu);
    if ($salles === false) {
        error_log("Erreur de désérialisation dans $chemin_fichier.");
        return null;
    }
    return $salles;
}

/**
 * Charge les promotions depuis le fichier TXT
 * @param string $chemin_fichier Chemin du fichier promotions.txt
 * @return array|null Tableau associatif des promotions ou null en cas d'erreur
 */
function charger_promotions($chemin_fichier) {
    if (!file_exists($chemin_fichier)) {
        error_log("Erreur : fichier $chemin_fichier introuvable.");
        return null;
    }
    $contenu = file_get_contents($chemin_fichier);
    if ($contenu === false) {
        error_log("Erreur de lecture du fichier $chemin_fichier.");
        return null;
    }
    $promotions = unserialize($contenu);
    if ($promotions === false) {
        error_log("Erreur de désérialisation dans $chemin_fichier.");
        return null;
    }
    return $promotions;
}

/**
 * Charge les cours depuis le fichier TXT
 * @param string $chemin_fichier Chemin du fichier cours.txt
 * @return array|null Tableau des cours ou null en cas d'erreur
 */
function charger_cours($chemin_fichier) {
    if (!file_exists($chemin_fichier)) {
        error_log("Erreur : fichier $chemin_fichier introuvable.");
        return null;
    }
    $contenu = file_get_contents($chemin_fichier);
    if ($contenu === false) {
        error_log("Erreur de lecture du fichier $chemin_fichier.");
        return null;
    }
    $cours = unserialize($contenu);
    if ($cours === false) {
        error_log("Erreur de désérialisation dans $chemin_fichier.");
        return null;
    }
    return $cours;
}

/**
 * Charge les options depuis le fichier TXT
 * @param string $chemin_fichier Chemin du fichier options.txt
 * @return array|null Tableau des options par niveau ou null en cas d'erreur
 */
function charger_options($chemin_fichier) {
    if (!file_exists($chemin_fichier)) {
        error_log("Erreur : fichier $chemin_fichier introuvable.");
        return null;
    }
    $contenu = file_get_contents($chemin_fichier);
    if ($contenu === false) {
        error_log("Erreur de lecture du fichier $chemin_fichier.");
        return null;
    }
    $options = unserialize($contenu);
    if ($options === false) {
        error_log("Erreur de désérialisation dans $chemin_fichier.");
        return null;
    }
    return $options;
}

/**
 * Charge le planning depuis le fichier TXT
 * @param string $chemin_fichier Chemin du fichier planning.txt
 * @return array|null Tableau du planning ou null en cas d'erreur ou fichier absent
 */
function charger_planning($chemin_fichier) {
    if (!file_exists($chemin_fichier)) {
        return null;
    }
    $lines = file($chemin_fichier, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        error_log("Erreur de lecture du fichier $chemin_fichier.");
        return null;
    }
    $planning = [];
    foreach ($lines as $line) {
        list($creneau, $salle, $cours, $groupe) = explode(" | ", $line);
        $planning[] = [
            'creneau' => $creneau,
            'salle' => $salle,
            'cours' => $cours,
            'groupe' => $groupe
        ];
    }
    return $planning;
}


function salle_disponible($planning, $id_salle, $creneau) {
    foreach ($planning as $affectation) {
        if ($affectation['salle'] === $id_salle && $affectation['creneau'] === $creneau) {
            return false;
        }
    }
    return true;
}

function capacite_suffisante($salles, $id_salle, $effectif) {
    return $effectif <= $salles[$id_salle]['capacite'];
}

function creneau_libre_groupe($planning, $id_groupe, $creneau) {
    foreach ($planning as $affectation) {
        if ($affectation['groupe'] === $id_groupe && $affectation['creneau'] === $creneau) {
            return false;
        }
    }
    return true;
}

function obtenir_effectif_groupe($id_groupe, $promotions, $options) {
    if (isset($promotions[$id_groupe]['effectif'])) {
        return (int)$promotions[$id_groupe]['effectif'];
    }

    foreach ($options as $niveau_options) {
        if (!is_array($niveau_options)) {
            continue;
        }
        foreach ($niveau_options as $option) {
            if (
                is_array($option) &&
                isset($option['id'], $option['effectif']) &&
                $option['id'] === $id_groupe
            ) {
                return (int)$option['effectif'];
            }
        }
    }

    return null;
}


function generer_planning($salles, $promotions, $cours, $options, $creneaux_disponibles) {
    $planning = [];

    foreach ($cours as $cours_item) {
        $id_groupe = $cours_item['promotion'];
        $effectif = obtenir_effectif_groupe($id_groupe, $promotions, $options);
        if ($effectif === null) {
            continue;
        }

        foreach ($creneaux_disponibles as $creneau) {
            foreach ($salles as $id_salle => $salle) {
                if (capacite_suffisante($salles, $id_salle, $effectif) &&
                    salle_disponible($planning, $id_salle, $creneau) &&
                    creneau_libre_groupe($planning, $id_groupe, $creneau)) {

                    $planning[] = [
                        'creneau' => $creneau,
                        'salle'   => $id_salle,
                        'cours'   => $cours_item['id'],
                        'groupe'  => $id_groupe
                    ];
                    break 2; // on sort dès qu’on a trouvé une salle
                }
            }
        }
    }
    return $planning;
}


function sauvegarder_planning($planning, $chemin_fichier) {
    $f = fopen($chemin_fichier, "w");
    foreach ($planning as $affectation) {
        $ligne = $affectation['creneau']." | ".$affectation['salle']." | ".$affectation['cours']." | ".$affectation['groupe']."\n";
        fwrite($f, $ligne);
    }
    fclose($f);
}


function detecter_conflits($chemin_fichier) {
    if (!file_exists($chemin_fichier)) {
        echo "❌ Fichier planning introuvable.";
        return;
    }

    $planning = file($chemin_fichier, FILE_IGNORE_NEW_LINES);
    $salles_creneaux = [];
    $groupes_creneaux = [];
    $conflits = [];

    foreach ($planning as $ligne) {
        list($creneau, $salle, $cours, $groupe) = explode(" | ", $ligne);

        $cleSalle = $salle."-".$creneau;
        $cleGroupe = $groupe."-".$creneau;

        if (isset($salles_creneaux[$cleSalle])) {
            $conflits[] = "Conflit : Salle $salle déjà occupée au créneau $creneau.";
        } else {
            $salles_creneaux[$cleSalle] = true;
        }

        if (isset($groupes_creneaux[$cleGroupe])) {
            $conflits[] = "Conflit : Groupe $groupe déjà affecté au créneau $creneau.";
        } else {
            $groupes_creneaux[$cleGroupe] = true;
        }
    }

    if (empty($conflits)) {
        echo "✔ Aucun conflit détecté.";
    } else {
        echo "<h3>Conflits détectés :</h3><ul>";
        foreach ($conflits as $c) {
            echo "<li>$c</li>";
        }
        echo "</ul>";
    }
}


function rapport_occupation($chemin_planning, $salles, $creneaux, $chemin_rapport) {
    $planning = file($chemin_planning, FILE_IGNORE_NEW_LINES);
    $occupation = [];

    foreach ($salles as $id => $salle) {
        $occupation[$id] = 0;
    }

    foreach ($planning as $ligne) {
        list($creneau, $salle, $cours, $groupe) = explode(" | ", $ligne);
        if (isset($occupation[$salle])) {
            $occupation[$salle]++;
        }
    }

    $f = fopen($chemin_rapport, "w");
    foreach ($salles as $id => $salle) {
        $total = count($creneaux);
        $occupes = $occupation[$id];
        $libres = $total - $occupes;
        $taux = ($occupes / $total) * 100;

        fwrite($f, "Salle $id : $occupes occupés, $libres libres, taux = $taux %\n");
    }
    fclose($f);

    echo "✔ Rapport généré dans $chemin_rapport.";
}


function modifier_affectation($chemin_fichier, $cours_id, $nouvelle_salle, $nouveau_creneau) {
    $planning = file($chemin_fichier, FILE_IGNORE_NEW_LINES);
    $nouveau_planning = [];
    $modifie = false;

    foreach ($planning as $ligne) {
        list($creneau, $salle, $cours, $groupe) = explode(" | ", $ligne);
        if ($cours == $cours_id) {
            $creneau = $nouveau_creneau;
            $salle = $nouvelle_salle;
            $modifie = true;
        }
        $nouveau_planning[] = "$creneau | $salle | $cours | $groupe";
    }

    if ($modifie) {
        file_put_contents($chemin_fichier, implode("\n", $nouveau_planning));
        echo "✔ Affectation du cours $cours_id modifiée.";
    } else {
        echo "❌ Cours $cours_id introuvable dans le planning.";
    }
}





?>
