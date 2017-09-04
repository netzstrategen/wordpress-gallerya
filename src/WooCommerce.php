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
    // Add WooCommerce v3.x Gallery Lightbox support
    // @see https://woocommerce.wordpress.com/2017/02/28/adding-support-for-woocommerce-2-7s-new-gallery-feature-to-your-theme/
    // @see https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)-in-3.0.0
    add_theme_support('wc-product-gallery-lightbox');

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
