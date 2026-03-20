<?php

namespace Drupal\candidate_approval\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Listing of pending candidate user registrations with Approve/Decline actions.
 */
class CandidateApprovalForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    MailManagerInterface $mail_manager,
    DateFormatterInterface $date_formatter
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->mailManager       = $mail_manager;
    $this->dateFormatter     = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.mail'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'candidate_approval_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $storage = $this->entityTypeManager->getStorage('user');

    $uids = $storage->getQuery()
      ->condition('roles', 'candidate')
      ->condition('field_candidate_approval_status', 'pending')
      ->accessCheck(FALSE)
      ->sort('created', 'ASC')
      ->execute();

    $form['intro'] = [
      '#markup' => '<p>' . $this->t(
        'Candidate registrations listed below are awaiting approval. '
        . 'Approve a registration to make the profile visible to companies. '
        . 'Decline a registration to reject it; the candidate will be notified and their profile scheduled for deletion in 30 days.'
      ) . '</p>',
    ];

    if (empty($uids)) {
      $form['empty'] = [
        '#markup' => '<p>' . $this->t('There are no candidate registrations pending approval.') . '</p>',
      ];
      return $form;
    }

    $form['candidates'] = [
      '#type'   => 'table',
      '#header' => [
        $this->t('UID'),
        $this->t('Username'),
        $this->t('Name'),
        $this->t('Email'),
        $this->t('Date submitted'),
        $this->t('Profile'),
        $this->t('Approve'),
        $this->t('Decline'),
      ],
    ];

    $users = $storage->loadMultiple($uids);

    foreach ($users as $user) {
      $uid = (int) $user->id();
      $first = $user->hasField('field_first_name') ? ($user->get('field_first_name')->value ?? '') : '';
      $last  = $user->hasField('field_surname') ? ($user->get('field_surname')->value ?? '') : '';
      $name  = trim("$first $last") ?: $user->getDisplayName();
      $email = $user->getEmail();
      $date  = $this->dateFormatter->format($user->getCreatedTime(), 'short');

      $profile_link = Link::fromTextAndUrl($this->t('View'), Url::fromRoute('entity.user.canonical', ['user' => $uid]))->toRenderable();

      $form['candidates'][$uid]['uid'] = ['#plain_text' => (string) $uid];
      $form['candidates'][$uid]['username'] = ['#plain_text' => $user->getAccountName()];
      $form['candidates'][$uid]['name']  = ['#plain_text' => $name];
      $form['candidates'][$uid]['email'] = ['#plain_text' => $email];
      $form['candidates'][$uid]['date']  = ['#plain_text' => $date];
      $form['candidates'][$uid]['profile'] = $profile_link;

      $form['candidates'][$uid]['approve'] = [
        '#type'       => 'submit',
        '#value'      => $this->t('Approve'),
        '#name'       => 'approve_' . $uid,
        '#attributes' => ['class' => ['button', 'button--primary', 'button--small']],
      ];

      $form['candidates'][$uid]['decline'] = [
        '#type'       => 'submit',
        '#value'      => $this->t('Decline'),
        '#name'       => 'decline_' . $uid,
        '#attributes' => ['class' => ['button', 'button--small']],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Identifies which Approve or Decline button was clicked by inspecting the
   * triggering element's #name, then processes the corresponding action.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $trigger      = $form_state->getTriggeringElement();
    $trigger_name = $trigger['#name'] ?? '';

    if (!preg_match('/^(approve|decline)_(\d+)$/', $trigger_name, $matches)) {
      return;
    }

    $action = $matches[1];
    $uid    = (int) $matches[2];

    /** @var \Drupal\user\UserInterface|null $candidate */
    $candidate = $this->entityTypeManager->getStorage('user')->load($uid);
    if (!$candidate || !$candidate->hasRole('candidate')) {
      $this->messenger()->addError($this->t('Candidate account not found.'));
      $form_state->setRedirect('candidate_approval.pending_list');
      return;
    }

    $to       = $candidate->getEmail();
    $first    = $candidate->hasField('field_first_name') ? ($candidate->get('field_first_name')->value ?: $candidate->getDisplayName()) : $candidate->getDisplayName();
    $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();

    if ($action === 'approve') {
      $candidate->set('field_candidate_approval_status', 'approved');
      $candidate->set('field_candidate_delete_on', NULL);
      $candidate->activate();
      $candidate->save();

      $this->mailManager->mail(
        'candidate_approval',
        'approved',
        $to,
        $langcode,
        ['candidate_name' => $first],
        NULL,
        TRUE
      );

      $this->messenger()->addStatus($this->t(
        'The registration for @name has been approved. Their profile is now visible to companies and they have been notified by email.',
        ['@name' => $first]
      ));
    }
    else {
      $deletion_date = date('Y-m-d', strtotime('+30 days'));

      $candidate->set('field_candidate_approval_status', 'declined');
      $candidate->set('field_candidate_delete_on', $deletion_date);
      $candidate->activate();
      $candidate->save();

      $this->mailManager->mail(
        'candidate_approval',
        'declined',
        $to,
        $langcode,
        ['candidate_name' => $first],
        NULL,
        TRUE
      );

      $this->messenger()->addStatus($this->t(
        'The registration for @name has been declined. Their profile will be deleted on @date unless they resubmit. They have been notified by email.',
        [
          '@name' => $first,
          '@date' => date('d F Y', strtotime('+30 days')),
        ]
      ));
    }

    $form_state->setRedirect('candidate_approval.pending_list');
  }

}
