<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupInterface;

/**
 * Fournit les pages d'administration Aquarius Formations.
 */
final class FormationsAdminController extends ControllerBase {

  /**
   * Retourne la page d'ensemble d'administration.
   */
  public function overview(): array {
    return [
      '#type' => 'container',
      'intro' => [
        '#markup' => $this->t('Le module Aquarius Formations est installe. Configurez les parametres puis commencez la mise en place des formations.'),
      ],
    ];
  }

  /**
   * Retourne la liste des evaluations d'une formation.
   */
  public function evaluations(GroupInterface $group): array {
    $header = [
      $this->t('Titre'),
      $this->t('Eleve'),
      $this->t("Date de l'evaluation"),
      $this->t('Operations'),
    ];

    $rows = [];
    $node_storage = $this->entityTypeManager()->getStorage('node');
    $node_ids = $node_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'evaluation_formation')
      ->condition('field_eval_formation.target_id', $group->id())
      ->sort('created', 'DESC')
      ->execute();

    if ($node_ids !== []) {
      /** @var \Drupal\node\NodeInterface[] $nodes */
      $nodes = $node_storage->loadMultiple($node_ids);
      foreach ($nodes as $node) {
        $eleve_name = '-';
        if (!$node->get('field_eval_eleve')->isEmpty() && $node->get('field_eval_eleve')->entity) {
          $eleve_name = $node->get('field_eval_eleve')->entity->label();
        }

        $date = '';
        if (!$node->get('field_eval_date')->isEmpty()) {
          $date = $node->get('field_eval_date')->value;
        }

        $operations = Link::fromTextAndUrl(
          $this->t('Modifier'),
          Url::fromRoute('aquarius_formations.evaluation.modifier', [
            'group' => $group->id(),
            'node' => $node->id(),
          ])
        )->toString();

        $rows[] = [
          $node->label(),
          $eleve_name,
          $date,
          ['data' => ['#markup' => $operations]],
        ];
      }
    }

    $build = [];
    $build['header'] = [
      '#markup' => '<p>' . $this->t('Formation : @formation', ['@formation' => $group->label()]) . '</p>',
    ];
    $build['actions'] = [
      '#type' => 'container',
      'add' => Link::fromTextAndUrl(
        $this->t('Ajouter une evaluation'),
        Url::fromRoute('aquarius_formations.evaluation.ajouter', ['group' => $group->id()])
      )->toRenderable(),
    ];
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('Aucune evaluation pour cette formation.'),
    ];

    return $build;
  }

}
