<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\WooCommerce.
 */

namespace Netzstrategen\Gallerya;

/**
 * WooCommerce integration.
 */
class WooCommerce {

  /**
   * Adds data-srcset attributes and data-sizes to the image wrap to make images
   * reponsive in lightGallery.
   *
   * And adds data-sizes attributes to image wrap so images are not enlarged more
   * than the original size named 'full'.
   *
   * @see https://sachinchoolur.github.io/lightGallery/demos/responsive.html
   *
   * @implements woocommerce_single_product_image_thumbnail_html
   */
  public static function woocommerce_single_product_image_thumbnail_html($html, $thumbnail_id) {
    $srcset = wp_get_attachment_image_srcset($thumbnail_id, 'shop_single');
    $srcsizes = wp_get_attachment_image_sizes($thumbnail_id, 'full');
    return preg_replace('/(<a\s+)/i', '<a data-srcset="' . $srcset . '" data-sizes="' . $srcsizes . '" ', $html);
  }

}
