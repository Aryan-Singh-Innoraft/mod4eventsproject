<?php

namespace Drupal\products\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Products routes.
 */
class BuyNowController extends ControllerBase {

  /**
   * The file url generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  public function __construct(FileUrlGeneratorInterface $file_url_generator) {
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_url_generator'),
    );
  }

  /**
   * Builds the response.
   */
  public function buynow(Node $node): array {
    $title = $node->label();
    $price = $node->get('field_price')->value;
    if (!$node->get('field_product_image')->isEmpty()) {
      $file = $node->get('field_product_image')->entity;
      $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    }
    // dump($title, $price, $image_url);
    $build['content'] = [
      '#theme' => 'buy_now_page',
      '#title' => $title,
      '#price' => $price,
      '#image_url' => $image_url,
    ];
    // $this->entityTypeManager->getStorage('node')->load($id).$node
    // $build['content'] = [
    //   '#type' => 'item',
    //   '#title' => $title,
    //   '#markup' => $price . '<br><img src="' . $image_url . '" alt="Product">',
    //   // '#allowed_tags' => ['br', 'img'],
    // ];
    return $build;
  }

}
