<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Point central des verifications d'acces pour formations et evaluations.
 */
final class FormationAccessChecker {

  /**
   * L'utilisateur courant.
   */
  private AccountProxyInterface $currentUser;

  /**
   * Construit le service.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Verifie si un compte peut modifier une evaluation selon son auteur.
   */
  public function canEditEvaluation(AccountInterface $account, int $author_uid): bool {
    if ($account->hasPermission('edit any aquarius evaluations')) {
      return TRUE;
    }

    return $account->hasPermission('edit own aquarius evaluations') && ((int) $account->id() === $author_uid);
  }

  /**
   * Retourne TRUE si l'utilisateur courant peut acceder a l'ensemble admin.
   */
  public function canAccessOverview(): bool {
    return $this->currentUser->hasPermission('access aquarius formations overview');
  }

}
