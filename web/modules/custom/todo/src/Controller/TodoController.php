<?php

namespace Drupal\todo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;

/**
 * Returns responses for Todo routes.
 */
class TodoController extends ControllerBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Gets the curent logged in user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $account_proxy) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * Builds the response.
   */
  public function delete($item) {
    $response = new AjaxResponse();
    $storage = $this->entityTypeManager()->getStorage('node');
    $node = $storage->load($item);
    if ($node && $node->bundle() == 'todo_item') {
      $node->delete();
      $response->addCommand(new RemoveCommand('tr:has(#todo-done' . $item));
    }
    return $response;
  }

}
