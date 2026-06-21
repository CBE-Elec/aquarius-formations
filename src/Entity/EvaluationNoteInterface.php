<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Interface pour l'entite EvaluationNote.
 */
interface EvaluationNoteInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Valeurs autorisees pour le champ note.
   */
  const NOTE_NON_TRAVAILLEE = 'non_travaillee';
  const NOTE_TRAVAILLEE = 'travaillee';
  const NOTE_REUSSIE = 'reussie';

  /**
   * Retourne le libelle de la note.
   */
  public function getNoteLabel(): string;

}
