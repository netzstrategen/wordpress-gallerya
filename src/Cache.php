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
  public static function getCachedVariationSliderAttachmentIds($product) {
    // Checks for cached content, avoiding too many queries.
    $attachment_ids = get_transient(self::VARIATION_SLIDER_CACHE_KEY_PREFIX . $product->get_id());
    if (empty($attachment_ids)) {
      // Builds cache data, so next time it will load faster.
      $attachment_ids = self::setProductVariationTransients($product);
    }
    return $attachment_ids;
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
