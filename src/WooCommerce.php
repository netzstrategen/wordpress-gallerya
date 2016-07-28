<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\WooCommerce.
 */

namespace Netzstrategen\Gallerya;

/**
 * Administrative back-end functionality.
 */
class WooCommerce {

  /**
   * @implements admin_init
   */
  public static function init() {
    add_filter('pre_option_woocommerce_enable_lightbox', function () { return 'no'; });
    add_filter('woocommerce_product_settings', __CLASS__ . '::woocommerce_product_settings');
  }

  /**
   * @implements woocommerce_product_settings
   */
  public static function woocommerce_product_settings($settings) {
    return array_filter($settings, function ($setting) {
      return $setting['id'] !== 'woocommerce_enable_lightbox';
    });
  }

}
