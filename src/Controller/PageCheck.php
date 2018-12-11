<?php

namespace Drupal\site_api_key\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides route responses for the site_api_key module.
 */
class PageCheck extends ControllerBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
	
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('entity_type.manager'), $container->get('config.factory')
    );
  }

  /**
   * Callback function for pagejson.
   */
  public function pagejson($key, $nid) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $json_data = [
      'status' => FALSE,
      'data' => '',
    ];
    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/', $path);
    $key = '';
    if (count($arg) === 4) {
      $key = $arg[2];
      $siteapikey = $this->configFactory->get('system.site')->get('siteapikey');
      if ($key != $siteapikey) {
        // Access denied.
        $json_data['data'] = 'Access Denied';
        return new JsonResponse($json_data);
      }

      $nid = $arg[3];
      $node = $node_storage->load($nid);
      // Validate its content type page.
      $node_type = $node->bundle();
      $node_status = $node->isPublished();
      if (trim($node_type) != trim('page') && !empty($node_status)) {
        $json_data['data'] = 'Access Denied';
        $json_data['status'] = FALSE;

      }
      else {
        $serializer = \Drupal::service('serializer');
        $data = $serializer->serialize($node, 'json', ['plugin_id' => 'entity']);
        $json_data['data'] = $data;
        $json_data['status'] = TRUE;

      }

      return new JsonResponse($json_data);
    }
    else {
      $json_data['data'] = 'Access Denied';
      return new JsonResponse($json_data);
    }
  }

}
