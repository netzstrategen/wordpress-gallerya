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
   * The slider variation transient key prefix.
   *
   * @var string
   */
  const VARIATION_SLIDER_CACHE_KEY_PREFIX = Plugin::PREFIX . '_variation_attachments_for_';

  /**
   * Adds woocommerce specific settings.
   *
   * @implements woocommerce_get_settings_gallerya
   */
  public static function woocommerce_get_settings_gallerya(array $settings): array {
    $settings[] = [
      'type' => 'title',
      'name' => __('Product thumbnails slider settings', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'checkbox',
      'id' => '_' . Plugin::L10N . '_product_thumbnail_slider_enabled',
      'name' => __('Enable thumbnails slider for all product image galleries', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'checkbox',
      'id' => '_' . Plugin::L10N . '_product_thumbnail_slider_bullet_nav_enabled',
      'name' => __('Use bullets instead of thumbnails', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'sectionend',
      'id' => Plugin::L10N,
    ];
    $settings[] = [
      'type' => 'title',
      'name' => __('Product variation slider settings', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'checkbox',
      'id' => '_' . Plugin::L10N . '_product_variation_slider_enabled',
      'name' => __('Enable thumbnails slider on product listing pages', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'sectionend',
      'id' => Plugin::L10N,
    ];
    return $settings;
  }

  /**
   * Adds data-srcset and data-sizes attributes to the wrapper to make images reponsive in lightGallery.
   *
   * Also adds data-sizes attributes to the image wrapper, so images are not
   * enlarged more than the original size ('full').
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

  /**
   * Adds CSS class to single product image galleries to enable thumbnail slider via JS.
   *
   * @implements woocommerce_single_product_image_gallery_classes
   */
  public static function woocommerce_single_product_image_gallery_classes($classes) {
    if (in_array('woocommerce-product-gallery--with-images', $classes)) {
      $classes[] = 'js-gallerya-product-thumbnail-slider';
    }
    return $classes;
  }

  /**
   * Exchanges thumbnails with bullets as control nav for the single product galleries.
   *
   * @implements woocommerce_single_product_carousel_options
   */
  public static function woocommerce_single_product_carousel_options($options) {
    $options['controlNav'] = TRUE;
    unset($options['manualControls']);
    return $options;
  }

  /**
   * Adds thumbnail slider with variation images to products on listing pages.
   *
   * @implements woocommerce_template_loop_product_thumbnail
   */
  public static function woocommerce_template_loop_product_thumbnail() {
    global $product;
    $render_slider = FALSE;

    if ($product->is_type('variable')) {
      // Checks for cached content, avoiding too many queries.
      $attachment_ids = get_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product->get_id());
      if (empty($attachment_ids)) {
        // Builds cache data, so next time it will load faster.
        $attachment_ids = self::setProductVariationTransients($product);
      }

      // Only render slider if there are more than one images.
      if (count($attachment_ids) > 1) {
        $render_slider = TRUE;
      }
    }

    if ($render_slider) {
      Plugin::renderTemplate(['templates/layout-product-variation-slider.php'], [
        'attachment_ids' => $attachment_ids,
      ]);
    }
    else {
      // This either has no more than one thumbnail or isn't a variable product,
      // output default thumbnail markup.
      woocommerce_template_loop_product_thumbnail();
    }
  }

  /**
   * Builds transient record for this variation slider.
   */
  private static function setProductVariationTransients($product) {
    $attachment_ids = [];
    // Gets the main product image.
    $attachment_ids[] = $product->get_image_id();

    // Gets the first image of each product variation.
    $variations = $product->get_available_variations();
    foreach ($variations as $variation) {
      $attachment_ids[] = $variation['image_id'];
    }
    $attachment_ids = array_unique($attachment_ids);

    set_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product->get_id(), $attachment_ids);
    return $attachment_ids;
  }

  /**
   * Deletes variation slider transient certain product id.
   */
  public static function flushVariationAttachmentsTransients($product_id) {
    delete_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product_id);
  }

}
