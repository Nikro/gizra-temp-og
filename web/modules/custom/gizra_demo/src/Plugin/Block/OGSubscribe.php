<?php

namespace Drupal\gizra_demo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\og\Og;
use Drupal\og\OgMembershipInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Group Welcome / Subscribe' block.
 *
 * @Block(
 *  id = "og_welcome_subscribe",
 *  admin_label = @Translation("Group Subscribe"),
 *  category = @Translation("Gizra")
 * )
 */
class OGSubscribe extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Drupal account to use for checking for access to search.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Creates a custom subscribe block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account of currently logged-in user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->routeMatch->getParameter('node');

    // You can never be too sure - let's check everything.
    if (!empty($node) && Og::isGroup('node', $node->bundle()) &&
      $this->account->isAuthenticated()) {

      // We want to present 2 versions of the text.
      if (!Og::isMember($node, $this->account, [OgMembershipInterface::STATE_ACTIVE, OgMembershipInterface::STATE_PENDING])) {
        $parameters = [
          'entity_type_id' => 'node',
          'group' => $node->id(),
        ];
        $url = Url::fromRoute('og.subscribe', $parameters);

        $build['subscribe']['#markup'] = $this->t('Hi @name, <a href="@url">click here</a> if you would like to subscribe to this group called @title', [
          '@name' => $this->account->getDisplayName(),
          '@title' => $node->getTitle(),
          '@url' => $url->toString(),
        ]);
      }
      else {
        $parameters = [
          'entity_type_id' => 'node',
          'group' => $node->id(),
        ];
        $url = Url::fromRoute('og.unsubscribe', $parameters);
        $build['subscribe']['#markup'] = $this->t('Hi @name, you are already a member (or pending). If you want to unsubscribe - <a href="@url">click here</a>.', [
          '@name' => $this->account->getDisplayName(),
          '@url' => $url->toString(),
        ]);
      }

    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // For now, let's skip caching.
    // @todo: switch to contextual node-based caching.
    return 0;
  }

}
