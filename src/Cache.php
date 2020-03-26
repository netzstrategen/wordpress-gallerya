<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\Cache.
 */

namespace Netzstrategen\Gallerya;

/**
 * Adds transient cache.
 */
class Cache {

  /**
   * The slider variation transient key prefix.
   *
   * @var string
   */
  const VARIATION_SLIDER_CACHE_KEY_PREFIX = Plugin::PREFIX . '_variation_attachments_for_';

  /**
   * Queries for cached markup, if none found, creates it for next time.
   */
  public static function getCachedVariationSliderMarkup($product) {
    // Checks for cached content, avoiding too many queries.
    $variation_attachments = get_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product->get_id());
    if (!empty($variation_attachments)) {
      $variation_attachments = json_decode($variation_attachments, TRUE);
    }
    else {
      // Builds cache data, so next time it will load faster.
      $variation_attachments = self::setProductVariationTransients($product);
    }
    return $variation_attachments;
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

    // Queries for the actual attachment type posts.
    $args = [
      'post_type' => 'attachment',
      'include' => $attachment_ids,
      'orderby' => 'post__in',
    ];
    $images = get_posts($args);

    // Builds cache data and sets transient.
    $slider_image_size = has_image_size('woocommerce_thumbnail') ? 'woocommerce_thumbnail' : 'medium';
    $slider_image_src = apply_filters(Plugin::PREFIX . '/image_size_product_variation_slider', $slider_image_size);
    $transparent_pixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    foreach ($images as $key => $image) {
      // TODO: Remove wrapping product link if we have multiple images.
      // Needs to be done through removing and re-adding hooks in Plugin.php:
      // woocommerce_template_loop_product_link_open() needs to be removed from woocommerce_before_shop_loop_item
      // and re-added to woocommerce_bevore_shop_look_item_title with lower priority, eg. like 20.
      $variation_attachments[$key]['media-id-handle'] = Plugin::PREFIX . '-variation-attachment-handle-' . $image->ID;
      $variation_attachments[$key]['markup'] = wp_get_attachment_image($image->ID, $slider_image_src, FALSE, $key ? [
        'src' => $transparent_pixel,
        'srcset' => $transparent_pixel,
        'data-flickity-lazyload-src' => wp_get_attachment_image_url($image->ID, $slider_image_src),
        'data-flickity-lazyload-srcset' => wp_get_attachment_image_srcset($image->ID, $slider_image_src),
      ] : []);
    }

    set_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product->get_id(), json_encode($variation_attachments));
    return $variation_attachments;
  }

  /**
   * Deletes variation slider transient certain product id.
   */
  public static function flushVariationAttachmentsTransients($product_id) {
    delete_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product_id);
  }

  /**
   * Delete according variation transient on media deletion.
   */
  public static function maybeDeleteVariationAttachmentTransient($id) {
    global $wpdb;
    $handle = Plugin::PREFIX . '-variation-attachment-handle-' . $id;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_value LIKE '%{$handle}%';");
  }

}
