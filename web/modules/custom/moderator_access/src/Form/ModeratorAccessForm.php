<?php

namespace Drupal\moderator_access\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for managing WG moderator role assignments.
 */
class ModeratorAccessForm extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a moderator access form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'moderator_access_manage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#type' => 'item',
      '#markup' => $this->t('Grant or revoke moderator (WG) access.'),
    ];

    $form['add_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Grant moderator access'),
      '#open' => TRUE,
    ];

    $form['add_section']['account_identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username or email'),
      '#required' => TRUE,
      '#description' => $this->t('Enter an existing user account username or email address.'),
    ];

    $form['add_section']['grant'] = [
      '#type' => 'submit',
      '#value' => $this->t('Grant moderator access'),
      '#submit' => ['::grantModeratorAccessSubmit'],
    ];

    $header = [
      'uid' => $this->t('UID'),
      'username' => $this->t('Username'),
      'email' => $this->t('Email'),
      'status' => $this->t('Status'),
      'actions' => $this->t('Actions'),
    ];

    $form['moderators'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No moderators found.'),
    ];

    $uids = $this->entityTypeManager
      ->getStorage('user')
      ->getQuery()
      ->condition('roles', 'wg')
      ->sort('uid', 'ASC')
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($uids)) {
      $users = User::loadMultiple($uids);
      foreach ($users as $user) {
        $uid = (int) $user->id();
        $is_current_user = $uid === (int) $this->currentUser()->id();

        $form['moderators'][$uid]['uid'] = [
          '#markup' => (string) $uid,
        ];
        $form['moderators'][$uid]['username'] = [
          '#markup' => $user->getAccountName(),
        ];
        $form['moderators'][$uid]['email'] = [
          '#markup' => $user->getEmail(),
        ];
        $form['moderators'][$uid]['status'] = [
          '#markup' => $user->isActive() ? $this->t('Active') : $this->t('Blocked'),
        ];

        if ($is_current_user) {
          $form['moderators'][$uid]['actions'] = [
            '#markup' => $this->t('Current user'),
          ];
        }
        else {
          $form['moderators'][$uid]['actions'] = [
            'remove' => [
              '#type' => 'submit',
              '#value' => $this->t('Remove moderator access'),
              '#name' => 'remove_wg_' . $uid,
              '#submit' => ['::removeModeratorAccessSubmit'],
              '#limit_validation_errors' => [],
              '#remove_uid' => $uid,
            ],
          ];
        }
      }
    }

    return $form;
  }

  /**
   * Submit handler for granting moderator access.
   */
  public function grantModeratorAccessSubmit(array &$form, FormStateInterface $form_state) {
    $identifier = trim((string) $form_state->getValue('account_identifier'));

    if ($identifier === '') {
      $this->messenger()->addError($this->t('Please enter a username or email.'));
      return;
    }

    $storage = $this->entityTypeManager->getStorage('user');
    $query = $storage->getQuery()->accessCheck(FALSE);

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
      $query->condition('mail', $identifier);
    }
    else {
      $query->condition('name', $identifier);
    }

    $uids = $query->range(0, 1)->execute();
    if (empty($uids)) {
      $this->messenger()->addError($this->t('No user found for "@value".', ['@value' => $identifier]));
      return;
    }

    $uid = (int) reset($uids);
    $user = User::load($uid);
    if (!$user) {
      $this->messenger()->addError($this->t('Unable to load selected user.'));
      return;
    }

    if ($user->hasRole('wg')) {
      $this->messenger()->addStatus($this->t('User %name already has moderator access.', ['%name' => $user->getAccountName()]));
      return;
    }

    $user->addRole('wg');
    $user->save();
    $this->messenger()->addStatus($this->t('Moderator access granted to %name.', ['%name' => $user->getAccountName()]));
    $form_state->setRebuild(TRUE);
  }

  /**
   * Submit handler for removing moderator access.
   */
  public function removeModeratorAccessSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $uid = isset($trigger['#remove_uid']) ? (int) $trigger['#remove_uid'] : 0;

    if ($uid <= 0) {
      $this->messenger()->addError($this->t('Invalid user selected.'));
      return;
    }

    if ($uid === (int) $this->currentUser()->id()) {
      $this->messenger()->addError($this->t('You cannot remove your own moderator access from this page.'));
      return;
    }

    $user = User::load($uid);
    if (!$user) {
      $this->messenger()->addError($this->t('Unable to load selected user.'));
      return;
    }

    if (!$user->hasRole('wg')) {
      $this->messenger()->addStatus($this->t('User %name is not a moderator.', ['%name' => $user->getAccountName()]));
      return;
    }

    $user->removeRole('wg');
    $user->save();
    $this->messenger()->addStatus($this->t('Moderator access removed from %name.', ['%name' => $user->getAccountName()]));
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No default submit action; custom submit handlers are used.
  }

}
