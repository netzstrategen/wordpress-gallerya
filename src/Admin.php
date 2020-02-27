<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\Admin.
 */

namespace Netzstrategen\Gallerya;

/**
 * Administrative back-end functionality.
 */
class Admin {

  /**
   * Plugin backend initialization method.
   *
   * @implements admin_init
   */
  public static function init() {
    // Adds WooCommerce specific features.
    if (Plugin::isPluginActive('woocommerce/woocommerce.php')) {
      // Adds custom product data tabs / panels.
      add_filter('woocommerce_product_data_tabs', __NAMESPACE__ . '\Video::woocommerce_product_data_tabs');
      add_action('woocommerce_product_data_panels', __NAMESPACE__ . '\Video::woocommerce_product_data_panels');
      add_action('admin_head', __NAMESPACE__ . '\Video::addCustomIconsToTabs');
    }
  }

}
