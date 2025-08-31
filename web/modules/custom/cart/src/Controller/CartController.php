<?php

namespace Drupal\cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Cart routes.
 */
class CartController extends ControllerBase {

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
  public function add(Node $node, Request $request) {
    // $title = $node->label();
    $current_user_id = $this->currentUser()->id();
    $storage = $this->entityTypeManager()->getStorage('node');
    $cart_ids = $this->entityTypeManager()->getStorage('node')->getQuery()->accessCheck(FALSE)->condition('type', 'cart')
      ->condition('field_user', $current_user_id)->execute();
    if (empty($cart_ids)) {
      $cart_item = $storage->create(
        [
          'type' => 'cart',
          'title' => 'Cart Item',
          'field_user' => $this->currentUser()->id(),
          'field_product' => $node->id(),
        ]
      );
      $cart_item->save();
      $this->messenger()->addMessage($node->label() . 'saved to your cart');
      return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
    }
    $cart = $storage->load(reset($cart_ids));
    $cart->field_product[] = ['target_id' => $node->id()];
    $cart->save();
    $this->messenger()->addMessage($node->label() . ' saved to your cart');
    $referer = $request->headers->get('referer');
    if (strpos($referer, '/cart-items') !== FALSE) {
      return $this->redirect('view.cart_items.page_1');
    }
    else {
      return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
    }
  }

  public function delete(Node $node) {
    $storage = $this->entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()->accessCheck(FALSE);
    $query->condition('type', 'cart');
    $query->condition('field_user', $this->currentUser()->id());
    $cart_id = $query->execute();
    // dump($cart_items);
    if (!empty($cart_id)) {
      $cart = $storage->load(reset($cart_id));
      foreach ($cart->get('field_product') as $delta => $item) {
        if ($item->target_id == $node->id()) {
          $cart->get('field_product')->removeItem($delta);
          $this->messenger()->addMessage('Item deleted from cart');
          break;
        }
      }
      $cart->save();
    }
    else {
      $this->messenger()->addMessage('Item not found.');
    }
    return $this->redirect('view.cart_items.page_1');
  }

}
