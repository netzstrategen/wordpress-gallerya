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
      'id' => '_' . Plugin::PREFIX . '_product_thumbnail_slider_enabled',
      'name' => __('Enable thumbnails slider for all product image galleries', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'checkbox',
      'id' => '_' . Plugin::PREFIX . '_product_thumbnail_slider_bullet_nav_enabled',
      'name' => __('Use bullets instead of thumbnails', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'sectionend',
      'id' => Plugin::PREFIX,
    ];
    $settings[] = [
      'type' => 'title',
      'name' => __('Product variation slider settings', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'checkbox',
      'id' => '_' . Plugin::PREFIX . '_product_variation_slider_enabled',
      'name' => __('Enable thumbnails slider on product listing pages', Plugin::L10N),
    ];
    $settings[] = [
      'type' => 'sectionend',
      'id' => Plugin::PREFIX,
    ];
    return $settings;
  }

  /**
   * Ensures new product are saved before updating its meta data.
   *
   * New products are still not saved when updated_post_meta hook is called.
   * Since we can not check if the meta keys were changed before running
   * our custom functions (see updateDeliveryTime and updateSalePercentage),
   * we are forcing the post to be saved before updating the meta keys.
   *
   * @implements woocommerce_process_product_meta
   */
  public static function saveNewProductBeforeMetaUpdate($post_id) {
    $product = wc_get_product($post_id);
    $product->save();
  }

  /**
   * Saves custom fields for simple products.
   *
   * @implements woocommerce_process_product_meta
   */
  public static function woocommerce_process_product_meta($post_id) {
    $transients = [
      '_' . Plugin::PREFIX . '_video_id' => Plugin::PREFIX . '_video_thumb_',
    ];

    $custom_fields = [
      '_' . Plugin::PREFIX . '_video_id',
      '_' . Plugin::PREFIX . '_video_source',
    ];

    foreach ($custom_fields as $field) {
      if (isset($_POST[$field])) {
        if (!is_array($_POST[$field]) && $_POST[$field]) {
          update_post_meta($post_id, $field, $_POST[$field]);
        }
        else {
          delete_post_meta($post_id, $field);
        }
        if (isset($transients[$field])) {
          // Flush related transients.
          delete_site_transient($transients[$field] . $post_id);
        }
      }
    }

    $custom_fields_checkbox = [
      '_' . Plugin::PREFIX . '_video_display',
    ];

    foreach ($custom_fields_checkbox as $field) {
      if (isset($_POST[$field])) {
        $value = !is_array($_POST[$field]) && wc_string_to_bool($_POST[$field]) ? 'yes' : 'no';
        update_post_meta($post_id, $field, $value);
      }
      else {
        delete_post_meta($post_id, $field);
      }
    }
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
    global $product, $wpdb;
    $attachment_ids = [];

    if ($product->is_type('variable')) {
      // Add the main product image.
      $attachment_ids[] = $product->get_image_id();
      // Add the first image of each product variation.
      // Avoid calling $product->get_available_variations() as this would fully
      // load and render all of the product variations.
      $variation_ids = $product->get_visible_children();
      $placeholders = implode(',', array_fill(0, count($variation_ids), '%d'));
      $attachment_ids = array_unique(
        array_merge(
          $attachment_ids,
          $wpdb->get_col(
            $wpdb->prepare(
              "SELECT pm.meta_value AS attachment_id
              FROM wp_posts p
              INNER JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id'
              WHERE p.ID IN ($placeholders)
              ORDER BY p.menu_order ASC",
              $variation_ids
            )
          )
        )
      );
    }

    // Only render slider if there is more than one image.
    if (count($attachment_ids) > 1) {
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

}
