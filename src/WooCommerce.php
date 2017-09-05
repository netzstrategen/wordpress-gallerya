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
    // Fixes compatibility with woocommerce v3.x
    add_filter('woocommerce_single_product_image_thumbnail_html', __CLASS__ . '::woocommerce_single_product_image_thumbnail_html');
  }

  /**
   * @implements woocommerce_product_settings
   */
  public static function woocommerce_product_settings($settings) {
    return array_filter($settings, function ($setting) {
      return $setting['id'] !== 'woocommerce_enable_lightbox';
    });
  }

  /**
   * @implements woocommerce_single_product_image_thumbnail_html
   */
  public static function woocommerce_single_product_image_thumbnail_html ($html) {
    return str_replace('wp-post-image', 'wp-post-image zoom', $html);
  }

}
