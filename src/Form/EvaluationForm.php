<?php

declare(strict_types=1);

namespace Drupal\aquarius_formations\Form;

use Drupal\aquarius_formations\Entity\EvaluationNoteInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Formulaire de saisie d'une evaluation pour un eleve.
 *
 * Le formulaire est lie a une formation (groupe) via la route.
 * Il cree un noeud evaluation_formation ainsi qu'une entite
 * EvaluationNote par competence evaluee.
 */
final class EvaluationForm extends FormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountProxyInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'aquarius_formations_evaluation_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\group\Entity\GroupInterface|null $group
   *   La formation (groupe) a laquelle appartient l'evaluation.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?GroupInterface $group = NULL): array {
    if ($group === NULL) {
      $form['erreur'] = ['#markup' => $this->t('Formation introuvable.')];
      return $form;
    }

    $form_state->set('group_id', $group->id());

    // Charger et stocker les competences des la construction du formulaire
    // pour les reutiliser dans le submit sans second chargement.
    $competences = $this->loadCompetences($group);
    $form_state->set('competences', $competences);

    $form['#title'] = $this->t('Nouvelle evaluation — @formation', [
      '@formation' => $group->label(),
    ]);

    // --- Eleve ---
    $eleves = $this->loadElevesOptions($group);
    if (empty($eleves)) {
      $form['avertissement'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['messages', 'messages--warning']],
        'texte' => ['#markup' => $this->t('Aucun eleve inscrit dans cette formation.')],
      ];
    }

    $form['eleve'] = [
      '#type' => 'select',
      '#title' => $this->t('Eleve evalue'),
      '#required' => TRUE,
      '#options' => $eleves,
      '#empty_option' => $this->t('-- Choisir un eleve --'),
    ];

    // --- Date ---
    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t("Date de l'evaluation"),
      '#required' => TRUE,
      '#default_value' => date('Y-m-d'),
    ];

    // --- Conditions de pratique ---
    $form['conditions'] = [
      '#type' => 'radios',
      '#title' => $this->t('Conditions de pratique'),
      '#required' => FALSE,
      '#options' => [
        'piscine' => $this->t('Piscine'),
        'fosse' => $this->t('Fosse'),
        'lac' => $this->t('Lac'),
        'mer' => $this->t('Mer'),
      ],
    ];

    // --- Sortie associee ---
    $form['sortie'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Sortie associee'),
      '#target_type' => 'group',
      '#required' => FALSE,
      '#description' => $this->t('Sortie passee a associer a cette evaluation (optionnel).'),
      '#selection_settings' => [
        'target_bundles' => ['sortie'],
      ],
    ];

    // --- Commentaire ---
    $form['commentaire'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Commentaire'),
      '#rows' => 4,
      '#description' => $this->t('Commentaire visible par l\'eleve et les encadrants.'),
    ];

    // --- Competences a evaluer ---
    $form['notes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Competences a evaluer'),
      '#tree' => TRUE,
    ];

    if (empty($competences)) {
      $form['notes']['vide'] = [
        '#markup' => '<p>' . $this->t('Aucune competence n\'est definie pour cette formation. Configurez les competences avant de saisir une evaluation.') . '</p>',
      ];
    }
    else {
      foreach ($competences as $cid => $competence) {
        $form['notes'][$cid] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['eval-competence-ligne']],
        ];

        $form['notes'][$cid]['entete'] = [
          '#type' => 'item',
          '#title' => $competence['label'],
          '#markup' => $competence['critere'] ? ('<small class="eval-critere">' . $competence['critere'] . '</small>') : '',
        ];

        $form['notes'][$cid]['note'] = [
          '#type' => 'radios',
          '#title' => $this->t('Note'),
          '#title_display' => 'invisible',
          '#required' => TRUE,
          '#default_value' => EvaluationNoteInterface::NOTE_NON_TRAVAILLEE,
          '#options' => [
            EvaluationNoteInterface::NOTE_NON_TRAVAILLEE => $this->t('Non travaillee'),
            EvaluationNoteInterface::NOTE_TRAVAILLEE => $this->t('Travaillee'),
            EvaluationNoteInterface::NOTE_REUSSIE => $this->t('Reussie'),
          ],
          '#attributes' => ['class' => ['eval-note-radios']],
        ];
      }
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t("Enregistrer l'evaluation"),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $group_id = $form_state->get('group_id');
    $date = $form_state->getValue('date');

    // Une seule condition possible.
    $condition_value = $form_state->getValue('conditions');
    $conditions = $condition_value ? [['value' => $condition_value]] : [];

    // Creer le noeud evaluation_formation.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->create([
      'type' => 'evaluation_formation',
      'title' => $this->t('Evaluation du @date — @formation', [
        '@date' => $date,
        '@formation' => $form_state->get('group_id'),
      ]),
      'status' => 1,
      'uid' => $this->currentUser->id(),
      'field_eval_formation' => ['target_id' => $group_id],
      'field_eval_eleve' => ['target_id' => $form_state->getValue('eleve')],
      'field_eval_encadrant' => ['target_id' => $this->currentUser->id()],
      'field_eval_date' => ['value' => $date],
      'field_eval_conditions' => $conditions,
      'field_eval_sortie' => $form_state->getValue('sortie') ? ['target_id' => $form_state->getValue('sortie')] : NULL,
      'field_eval_commentaire' => ['value' => $form_state->getValue('commentaire') ?? '', 'format' => 'plain_text'],
    ]);
    $node->save();

    // Creer une entite EvaluationNote par competence.
    $note_storage = $this->entityTypeManager->getStorage('aquarius_evaluation_note');
    $competences = $form_state->get('competences') ?? [];
    $notes_values = $form_state->getValue('notes') ?? [];

    foreach ($competences as $cid => $competence) {
      $note_value = $notes_values[$cid]['note'] ?? EvaluationNoteInterface::NOTE_NON_TRAVAILLEE;

      $note_storage->create([
        'evaluation' => ['target_id' => $node->id()],
        'competence_label' => $competence['label'],
        'competence_critere' => $competence['critere'] ?? '',
        'note' => $note_value,
        'uid' => ['target_id' => $this->currentUser->id()],
      ])->save();
    }

    $this->messenger()->addStatus(
      $this->t("Evaluation du @date enregistree avec succes.", ['@date' => $date])
    );

    $form_state->setRedirect('aquarius_formations.formation.evaluations', [
      'group' => $group_id,
    ]);
  }

  /**
   * Charge les eleves inscrits dans le groupe (role formation-eleve).
   *
   * @return array<int, string>
   *   Tableau uid => nom d'affichage.
   */
  private function loadElevesOptions(GroupInterface $group): array {
    $options = [];

    foreach ($group->getMembers('formation-eleve') as $member) {
      $account = $member->getUser();
      if ($account !== NULL) {
        $options[(int) $account->id()] = $account->getDisplayName();
      }
    }

    return $options;
  }

  /**
   * Charge les competences associees a la formation.
   *
   * Point d'extension : cette methode sera remplacee ou surchargee
   * lorsque le modele de competences sera finalise (taxonomie ou entite).
   * Pour l'instant, elle retourne un tableau vide. Les competences peuvent
   * etre fournies par d'autres modules via un hook ou un service.
   *
   * @return array<string, array{label: string, critere: string}>
   *   Tableau indexe par identifiant de competence.
   */
  private function loadCompetences(GroupInterface $group): array {
    $competences = [];

    // Permettre aux modules tiers d'injecter des competences.
    \Drupal::moduleHandler()->invokeAll(
      'aquarius_formations_competences',
      [&$competences, $group]
    );

    return $competences;
  }

}
