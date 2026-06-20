<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure les parametres Aquarius Formations.
 */
final class AquariusFormationsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'aquarius_formations_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['aquarius_formations.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('aquarius_formations.settings');

    $form['enable_attendance_indicator'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Activer l''indicateur d''assiduite'),
      '#default_value' => (bool) $config->get('enable_attendance_indicator'),
      '#description' => $this->t('Si active, le module expose des indicateurs d''assiduite pour les eleves.'),
    ];

    $form['evaluation_input_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Mode de saisie des evaluations'),
      '#options' => [
        'single' => $this->t('Evaluation individuelle'),
        'batch' => $this->t('Evaluation par lot (usage futur)'),
      ],
      '#default_value' => (string) ($config->get('evaluation_input_mode') ?? 'single'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory->getEditable('aquarius_formations.settings')
      ->set('enable_attendance_indicator', (bool) $form_state->getValue('enable_attendance_indicator'))
      ->set('evaluation_input_mode', (string) $form_state->getValue('evaluation_input_mode'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
