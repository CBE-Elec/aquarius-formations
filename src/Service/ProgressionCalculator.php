<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Service;

/**
 * Calcule la progression d'un eleve selon les evaluations de competences.
 */
final class ProgressionCalculator {

  /**
   * Calcule le pourcentage de progression a partir des notes.
   *
   * Valeurs attendues : non_travaillee, travaillee, reussie.
   */
  public function calculatePercentage(array $notes): float {
    if ($notes === []) {
      return 0.0;
    }

    $successful = 0;
    foreach ($notes as $note) {
      if ($note === 'reussie') {
        $successful++;
      }
    }

    return round(($successful / count($notes)) * 100, 2);
  }

  /**
   * Retourne la meilleure note pour une competence.
   */
  public function getBestNote(array $notes): string {
    $rank = [
      'non_travaillee' => 0,
      'travaillee' => 1,
      'reussie' => 2,
    ];

    $best = 'non_travaillee';
    foreach ($notes as $note) {
      if (isset($rank[$note]) && $rank[$note] > $rank[$best]) {
        $best = $note;
      }
    }

    return $best;
  }

}
