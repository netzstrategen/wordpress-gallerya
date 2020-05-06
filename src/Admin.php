<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\Admin.
 */

namespace Netzstrategen\Gallerya;

/**
 * Administrative back-end functionality.
 */
class Admin {

  /**
   * Plugin backend initialization method.
   *
   * @implements admin_init
   */
  public static function init() {
    // Adds WooCommerce specific features.
    if (Plugin::isPluginActive('woocommerce/woocommerce.php')) {
      // Adds custom product data tabs / panels.
      add_filter('woocommerce_product_data_tabs', __NAMESPACE__ . '\Video::woocommerce_product_data_tabs');
      add_action('woocommerce_product_data_panels', __NAMESPACE__ . '\Video::woocommerce_product_data_panels');
      add_action('admin_head', __NAMESPACE__ . '\Video::addCustomIconsToTabs');

      // Adds backend interface to manage variation gallery.
      add_action('woocommerce_variation_options', __CLASS__ . '::woocommerce_variation_options', 10, 3);

      // Handles Ajax requests to add an image to the variation gallery.
      add_action('wp_ajax_gallerya_add_variation_image', __CLASS__ . '::galleryaAddVariationImage');
      // Handles Ajax requests to remove an image from the variation gallery.
      add_action('wp_ajax_gallerya_remove_variation_image', __CLASS__ . '::galleryaRemoveVariationImage');

      // Enqueue admin side assets.
      add_action('admin_enqueue_scripts', __CLASS__ . '::admin_enqueue_scripts');
    }
  }

  /**
   * Adds backend interface to manage variation gallery.
   *
   * @implements woocommerce_variation_options
   */
  public static function woocommerce_variation_options($loop, $variation_data, $variation) {
    $images = get_post_meta($variation->ID, '_' . Plugin::PREFIX . '_attachment_ids', TRUE) ?: [];

    $images_block = '';
    foreach ($images as $image_id) {
      $images_block .= sprintf('
        <li id="variation_%d_image_%d" class="variation_gallery__image" data-variation_id="%d" data-image_id="%d">
          %s
        </li>',
        $variation->ID,
        $image_id,
        $variation->ID,
        $image_id,
        wp_get_attachment_image($image_id, 'thumbnail', FALSE)
      );
    }
    $images_block = sprintf('
      <div id="variation_gallery_%d" class="variation_gallery">
        <p class="variation_gallery__title">%s</p>
        <ul class="variation_gallery__images">
        %s
        </ul>
        <p><span class="button variation_gallery__add-image" data-variation_id="%d">%s</span></p>
      </div>',
      $variation->ID,
      __('Additional images', Plugin::L10N),
      $images_block,
      $variation->ID,
      __('Add image', Plugin::L10N)
    );

    echo $images_block;
  }

  /**
   * Handles Ajax requests to add an image to the variation gallery.
   *
   * @implements wp_ajax_gallerya_remove_variation_image
   */
  public static function galleryaAddVariationImage() {
    if (!wp_verify_nonce($_POST['nonce'], 'gallerya-admin-ajax')) {
      wp_send_json_error(__('Nonce value cannot be verified.', Plugin::L10N));
    }

    if (isset($_POST['variation_id']) && !empty($_POST['images'])) {
      // Add variation images as meta data.
      $variation_id = (int) $_POST['variation_id'];
      $variation_gallery = get_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', TRUE) ?: [];

      $images = [];
      foreach ($_POST['images'] as $image) {
        $image_id = (int) $image['image_id'];
        $thumbnail_url = $image['thumbnail_url'];
        if (!in_array($image_id, $variation_gallery)) {
          $variation_gallery[] = $image_id;
          update_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', $variation_gallery);
          $images[] = [
            'image_id' => $image_id,
            'thumbnail_url' => $thumbnail_url,
          ];
        }
      }

      wp_send_json([
        'success' => TRUE,
        'variation_id' => $_POST['variation_id'],
        'images' => $images,
      ]);
    }
  }

  /**
   * Handles Ajax requests to remove an image from the variation gallery.
   *
   * @implements wp_ajax_gallerya_remove_variation_image
   */
  public static function galleryaRemoveVariationImage() {
    if (!wp_verify_nonce($_POST['nonce'], 'gallerya-admin-ajax')) {
      wp_send_json_error(__('Nonce value cannot be verified.', Plugin::L10N));
    }

    if (isset($_POST['image_id']) && isset($_POST['variation_id'])) {
      $image_id = (int) $_POST['image_id'];
      $variation_id = (int) $_POST['variation_id'];

      $variation_gallery = get_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', TRUE) ?: [];

      if (in_array($image_id, $variation_gallery)) {
        $variation_gallery = array_diff($variation_gallery, [$image_id]);
        update_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', $variation_gallery);
      }
      wp_send_json([
        'success' => TRUE,
        'variation_id' => $_POST['variation_id'],
        'image_id' => $_POST['image_id'],
      ]);
    }
  }

  /**
   * Enqueues assets.
   *
   * @implements wp_enqueue_scripts
   */
  public static function admin_enqueue_scripts() {
    wp_enqueue_style('gallerya-admin', Plugin::getBaseUrl() . '/dist/styles/admin.min.css', []);
    wp_enqueue_script('gallerya-admin', Plugin::getBaseUrl() . '/dist/scripts/admin.min.js', [], FALSE, TRUE);
    wp_localize_script('gallerya-admin', 'gallerya_admin', [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('gallerya-admin-ajax'),
    ]);
  }

}
