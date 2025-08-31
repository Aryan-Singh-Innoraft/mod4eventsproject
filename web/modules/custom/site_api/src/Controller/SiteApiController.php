<?php

namespace Drupal\site_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Site api routes.
 */
final class SiteApiController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function content(Request $request) {
    // Getting stored token from config.
    $config = $this->config('site_api.settings');
    $storedToken = $config->get('bearer');

    // If no token is provided.
    $authHeader = $request->headers->get('authorization');
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      return new JsonResponse(['error' => 'Unauthorized No token provided.'], 401);
    }
    // If wrong token is provided.
    $providedToken = $matches[1];
    if ($providedToken !== $storedToken) {
      return new JsonResponse(['error' => 'Invalid Credentials.'], 403);
    }

    // Get all published nodes nid.
    $nids = \Drupal::entityQuery('node')->condition('status', 1)->accessCheck(FALSE)->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
    $data = [];

    foreach ($nodes as $node) {
      $image = NULL;
      if ($node->hasField('banner_image') && !$node->get('banner_image')->isEmpty()) {
        // $imageFile = $node->get('banner_image')->entity;
        $image = [
          'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($node->get('banner_image')->entity->getFileUri()),
          'alt' => $node->get('banner_image')->first()->alt ?? '',
        ];
      }

      $data[] = [
        'title' => $node->label(),
        'nid' => $node->id(),
        'image' => $image,
        'node_url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
      ];
    }
















    return new JsonResponse($data);
  }

}
