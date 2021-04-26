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
    global $product;

    $srcset = wp_get_attachment_image_srcset($thumbnail_id, 'shop_single');
    $srcsizes = wp_get_attachment_image_sizes($thumbnail_id, 'full');
    $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', TRUE);
    $caption = apply_filters('gallerya/image_caption', $image_alt ?: get_the_title($thumbnail_id), $thumbnail_id);

    return preg_replace('/(<a\s+)/i', '<a data-caption="' . esc_attr($caption) . '" data-srcset="' . $srcset . '" data-sizes="' . $srcsizes . '" ', $html);
  }

  /**
   * Adds additional images from product variation gallery.
   *
   * @implements woocommerce_product_get_gallery_image_ids
   */
  public static function woocommerce_product_get_gallery_image_ids($value, $obj) {
    global $product;

    if (!$product || $product->get_type() !== 'variable') {
      return $value;
    }

    $product_id = $product->get_id();

    // Retrieve all the variations images.
    $variations_images = static::getProductVariationsImages($product_id);
    // Prevent the main product image to appear duplicated, if it is also used
    // in some of the variations.
    if ($product_image = get_post_thumbnail_id($product_id)) {
      $variations_images = array_diff($variations_images, [$product_image]);
    }

    return array_unique(array_merge($value, $variations_images));
  }

  /**
   * Collects all related images URLs of a variable product variations.
   *
   * If current product has variations, we collect the URLs for its featured
   * image, the general gallery images and the images in each of its variations
   * galleries. We print this data as a JS object.
   *
   * When the user selects a variation in the frontend UI, we hide all images in
   * the product image gallery slider, except for the product features image,
   * the general gallery images and the selected variation gallery images (if
   * those exist).
   */
  public static function woocommerce_product_thumbnails() {
    global $product;

    if (!$product || $product->get_type() !== 'variable') {
      return;
    }

    // Get size of images for the produt thumbnails slider.
    $gallery_thumbnail = wc_get_image_size('gallery_thumbnail');
    $thumbnail_size = apply_filters('woocommerce_gallery_thumbnail_size', [$gallery_thumbnail['width'], $gallery_thumbnail['height']]);
    $variation_images = [];
    // Collect product main image and general images gallery.
    $variation_images['gallery'] = [
      wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), $thumbnail_size)[0],
    ];
    foreach ($product->get_gallery_image_ids('edit') as $image_id) {
      $variation_images['gallery'][] = wp_get_attachment_image_src($image_id, $thumbnail_size)[0];
    }

    // Collect product variations images.
    $variation_ids = $product->get_visible_children();
    foreach ($variation_ids as $variation_id) {
      $variation_images[$variation_id] =
        static::getVariationImagesUrls($variation_id, $thumbnail_size);
    }

    echo sprintf('<script>var product_variation_images = %s;</script>', json_encode($variation_images));
  }

  /**
   * Retrieves the images URLs for a given product variation.
   *
   * Collects the URLs of the variation main image and the images in the
   * variation gallery.
   *
   * @param int $variation_id
   *   The product variation ID.
   * @param string $size
   *   The size of the images to be retrieved.
   *
   * @return array
   *   The product variation images URls.
   */
  public static function getVariationImagesUrls($variation_id, $size = 'thumbnail'): array {
    $images_urls = [];
    foreach (static::getVariationImages($variation_id) as $image_id) {
      if ($image_src = wp_get_attachment_image_src($image_id, $size)) {
        $images_urls[] = $image_src[0];
      }
    }

    return $images_urls;
  }

  /**
   * Retrieves the images IDs for a given product variation.
   *
   * Collects the IDs of the variation main image and the images in the
   * variation gallery.
   *
   * @param int $variation_id
   *   The product variation ID.
   *
   * @return array
   *   The product variation images.
   */
  public static function getVariationImages($variation_id): array {
    $variation = wc_get_product($variation_id);
    return array_merge([$variation->get_image_id()], static::getVariationGalleryImages($variation_id));
  }

  /**
   * Retrieves the images IDs for all the variations of a given product.
   *
   * Collects the IDs of the variation main image and the images in the
   * variation gallery.
   *
   * @param array $product_id
   *   The parent product ID.
   *
   * @return array
   *   The product variation images IDs.
   */
  public static function getProductVariationsImages($product_id) {
    global $wpdb;

    $results = array_map('maybe_unserialize',
      $wpdb->get_col($wpdb->prepare(
        "
        SELECT DISTINCT pm.meta_value
        FROM wp_postmeta pm
        INNER JOIN wp_posts p ON p.ID = pm.post_id AND p.post_parent = %d
        WHERE pm.meta_key IN ('_thumbnail_id', '_gallerya_attachment_ids')
        AND p.post_status = 'publish'
        ",
        $product_id
      ))
    );

    $images = [];
    $thumbs_count = 0;
    foreach ($results as $result) {
      if (is_array($result)) {
        // Extra gallery images for the variation.
        $thumbs_count = count($result);
        $images = array_merge($images, $result);
      }
      elseif ($result) {
        // Main variation image. Ensure it is added before the extra ones.
        array_splice($images, -$thumbs_count, 0, $result);
        $thumbs_count = 0;
      }
    }

    return $images;
  }

  /**
   * Retrieves the IDs of the gallery images for a given product variation.
   *
   * @param int $variation_id
   *   The product variation ID.
   *
   * @return array
   *   The product variation gallery images.
   */
  public static function getVariationGalleryImages($variation_id): array {
    return get_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', TRUE) ?: [];
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

      if (count($variation_ids)) {
        $placeholders = implode(',', array_fill(0, count($variation_ids), '%d'));
        $attachment_ids = array_merge(
          $attachment_ids,
          $wpdb->get_col(
            $wpdb->prepare(
              "SELECT DISTINCT pm.meta_value AS attachment_id
              FROM wp_posts p
              INNER JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id'
              WHERE p.ID IN ($placeholders) AND pm.meta_value > 0
              ORDER BY p.menu_order ASC",
              $variation_ids
            )
          )
        );
      }
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
