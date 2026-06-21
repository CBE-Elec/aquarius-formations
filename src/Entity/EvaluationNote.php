<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Entite representant la note d'un eleve pour une competence dans une evaluation.
 *
 * @ContentEntityType(
 *   id = "aquarius_evaluation_note",
 *   label = @Translation("Note d'évaluation"),
 *   label_collection = @Translation("Notes d'évaluation"),
 *   label_singular = @Translation("note d'évaluation"),
 *   label_plural = @Translation("notes d'évaluation"),
 *   label_count = @PluralTranslation(
 *     singular = "@count note d'évaluation",
 *     plural = "@count notes d'évaluation",
 *   ),
 *   base_table = "aquarius_evaluation_note",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 * )
 */
final class EvaluationNote extends ContentEntityBase implements EvaluationNoteInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getNoteLabel(): string {
    $labels = [
      self::NOTE_NON_TRAVAILLEE => 'Non travaillee',
      self::NOTE_TRAVAILLEE => 'Travaillee',
      self::NOTE_REUSSIE => 'Reussie',
    ];

    return $labels[$this->get('note')->value] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Reference a l'evaluation parente (noeud evaluation_formation).
    $fields['evaluation'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Evaluation'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', [
        'target_bundles' => ['evaluation_formation' => 'evaluation_formation'],
      ])
      ->setDisplayOptions('view', ['label' => 'above', 'weight' => 0])
      ->setDisplayConfigurable('view', TRUE);

    // Titre de la competence evaluee.
    $fields['competence_label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Competence'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', ['label' => 'above', 'weight' => 1])
      ->setDisplayConfigurable('view', TRUE);

    // Critere de reussite de la competence.
    $fields['competence_critere'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Critere de reussite'))
      ->setRequired(FALSE)
      ->setDisplayOptions('view', ['label' => 'above', 'weight' => 2])
      ->setDisplayConfigurable('view', TRUE);

    // Note attribuee : non travaillee / travaillee / reussie.
    $fields['note'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Note'))
      ->setRequired(TRUE)
      ->setSetting('allowed_values', [
        EvaluationNoteInterface::NOTE_NON_TRAVAILLEE => 'Non travaillee',
        EvaluationNoteInterface::NOTE_TRAVAILLEE => 'Travaillee',
        EvaluationNoteInterface::NOTE_REUSSIE => 'Reussie',
      ])
      ->setDefaultValue(EvaluationNoteInterface::NOTE_NON_TRAVAILLEE)
      ->setDisplayOptions('view', ['label' => 'above', 'weight' => 3])
      ->setDisplayConfigurable('view', TRUE);

    // Encadrant qui a saisi la note.
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Encadrant'))
      ->setRequired(FALSE)
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('view', ['label' => 'above', 'weight' => 4])
      ->setDisplayConfigurable('view', TRUE);

    // Horodatage de modification.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Modifie le'));

    return $fields;
  }

}
