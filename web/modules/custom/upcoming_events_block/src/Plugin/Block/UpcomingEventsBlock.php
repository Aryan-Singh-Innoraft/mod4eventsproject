<?php

namespace Drupal\upcoming_events_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides an upcoming events block.
 *
 * @Block(
 *   id = "upcoming_events_block_upcoming_events",
 *   admin_label = @Translation("Upcoming Events"),
 *   category = @Translation("Custom"),
 * )
 */
class UpcomingEventsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The file url generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Helps in matching routes.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Gets the curent logged in user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUrlGeneratorInterface $file_url_generator, EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, AccountProxyInterface $account_proxy) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUrlGenerator = $file_url_generator;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->currentUser = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_url_generator'),
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    $nid = $node instanceof Node ? $node->id() : 0;
    $current_user_id = $this->currentUser->id();
    // dump($current_user_name);
    $query = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(FALSE);
    $query->condition('uid', $current_user_id, '!=');
    $query->condition('type', 'events');
    $query->condition('field_event_date', date('Y-m-d'), '>');
    if ($nid) {
      $query->condition('nid', $nid, '!=');
    }
    $query->range(0, 5);
    $nids = $query->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $items = [];
    // $file_url_generator = \Drupal::service('file_url_generator');

    foreach ($nodes as $node) {
      // dump($email);
      $body = $node->get('body')->value;
      if (!$node->get('field_banner_image')->isEmpty()) {
        $file = $node->get('field_banner_image')->entity;
        $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
      $items[] = [
        'title' => [
          '#type' => 'link',
          '#title' => $node->label(),
          // '#body' => $node->values->body,
          '#url' => $node->toUrl('canonical'),
        ],
        'body' => ['#markup' => $body],
        'image' => [
          '#theme' => 'image',
          '#uri' => $image_url,
          '#alt' => $node->label(),
          '#title' => $node->label(),
        ],
      ];
    }
    return $items;
  }

}
