<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\WooCommerce.
 */

namespace Netzstrategen\Gallerya;

/**
 * WooCommerce custom functionality.
 */
class WooCommerce {

  /**
   * Adds srcset attributes to image links to make them reponsive on lightgallery.
   *
   * @see https://sachinchoolur.github.io/lightGallery/demos/responsive.html
   *
   * @implements woocommerce_single_product_image_thumbnail_html
   */
  public static function woocommerce_single_product_image_thumbnail_html($html, $thumbnail_id) {
    $srcset = wp_get_attachment_image_srcset($thumbnail_id, 'shop_single');
    return preg_replace('/(<a\s+)/i', '<a data-srcset="' . $srcset . '" ', $html);
  }

}
