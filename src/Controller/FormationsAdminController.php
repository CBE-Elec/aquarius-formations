<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Controller;

use Drupal\Core\Controller\ControllerBase;

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

}
