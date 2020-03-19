<?php

namespace Netzstrategen\Gallerya;

/**
 * Manages all cache table handling.
 */
class Cache {

  /**
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'gallerya';

  /**
   * Creates a cache database table.
   *
   * This table is only used as a caching method for reducing queries
   * on the product listing template.
   */
  public static function createCacheTable() {
    global $wpdb;
    global $charset_collate;
    $table_name = $wpdb->prefix . self::PREFIX . '_cache';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `product_id` bigint(20) NOT NULL,
      `queried_data` BLOB,
      PRIMARY KEY (`id`),
      KEY (`product_id`)
    )$charset_collate;";
    $wpdb->query($sql);
  }

  /**
   * Removes the cache database table on the plugin uninstall hook.
   */
  public static function dropCacheTable() {
    global $wpdb;
    $table_name = $wpdb->prefix . self::PREFIX . '_cache';
    $sql = "DROP TABLE IF EXISTS `{$table_name}`;";
    $wpdb->query($sql);
  }

  /**
   * Inserts/updates cache table when a product has been edited.
   */
  public static function updateCacheObject($product_id) {
    $product = wc_get_product($product_id);
    self::setCacheObject($product);
  }

  /**
   * Checks if product id is already cached.
   */
  public static function getCachedQuery($product) {
    // Queries for existing product record in cache table.
    $cached_object = self::queryForProduct($product->get_id());

    // Inserts new record, if none was found.
    if (empty($cached_object)) {
      $cached_object = self::setCacheObject($product);
    }

    return $cached_object;
  }

  /**
   * Queries cache table for an existing record according to product_id.
   */
  private static function queryForProduct($product_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . self::PREFIX . '_cache';
    $sql = $wpdb->prepare("SELECT * FROM {$table_name} WHERE product_id=%d", $product_id);
    return $wpdb->get_row($sql);
  }

  /**
   * Sets this product entry in the cache table.
   */
  private static function setCacheObject($product) {
    // Gets all attachments for this product slider.
    $images = self::getProductSliderImageIds($product);

    $slider_image_size = has_image_size('woocommerce_thumbnail') ? 'woocommerce_thumbnail' : 'medium';
    $slider_image_src = apply_filters('gallerya/image_size_product_variation_slider', $slider_image_size);
    $transparent_pixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    $queried_data = [];
    foreach ($images as $index => $image) {
      $queried_data[$index]['image_markup'] = wp_get_attachment_image($image->ID, $slider_image_src, FALSE, $index ? [
        'src' => $transparent_pixel,
        'srcset' => $transparent_pixel,
        'data-flickity-lazyload-src' => wp_get_attachment_image_url($image->ID, $slider_image_src),
        'data-flickity-lazyload-srcset' => wp_get_attachment_image_srcset($image->ID, $slider_image_src),
      ] : []);
    }

    // Injects cached object.
    self::injectQueriedData($queried_data, $product->get_id());

    // Returns queried data.
    return $queried_data;
  }

  /**
   * Returns all attachments for this product slider.
   */
  private static function getProductSliderImageIds($product) {
    // Stores ttachments ids.
    $attachment_ids = [];

    // Sets the main product image as first entry.
    $attachment_ids[] = $product->get_image_id();

    // Gets the first image of each product variation.
    $variations = $product->get_available_variations();
    foreach ($variations as $variation) {
      $attachment_ids[] = $variation['image_id'];
    }
    $attachment_ids = array_unique($attachment_ids);

    // Query for the post type attachments.
    $args['post_type'] = 'attachment';
    $args['include'] = $attachment_ids;
    $args['orderby'] = 'post__in';

    // Returns attachments collection.
    return get_posts($args);
  }

  /**
   * Inserts or updates product record in cache table.
   */
  private static function injectQueriedData($queried_data, $product_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . self::PREFIX . '_cache';

    // Updates existing record if found.
    $existing_record = self::queryForProduct($product_id);
    if (!empty($existing_record)) {
      $sql = $wpdb->prepare("UPDATE {$table_name} SET query_data=%s WHERE product_id=%d", json_encode($queried_data), $product_id);
      return $wpdb->get_results($sql);
    }
    // Inserts new record as no previous was found for this product.
    $sql = $wpdb->prepare("INSERT INTO {$table_name} (`product_id`, `queried_data`) VALUES (%d, %s)", $product_id, json_encode($queried_data));
    return $wpdb->get_results($sql);
  }

}
