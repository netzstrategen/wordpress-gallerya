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
      // Handles Ajax requests to save the order of the variation gallery.
      add_action('wp_ajax_gallerya_sort_variation_images', __CLASS__ . '::galleryaSortVariationImages');

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
    $images_block = '';
    foreach (WooCommerce::getVariationGalleryImages($variation->ID) as $image_id) {
      $images_block .= static::renderVariationImage($variation->ID, (int) $image_id);
    }
    $images_block = sprintf('
      <div id="variation_gallery_%d" class="variation_gallery" data-variation_id="%d">
        <p class="variation_gallery__title">%s</p>
        <ul class="variation_gallery__images">
        %s
        </ul>
        <p><span class="button variation_gallery__add-image" data-variation_id="%d">%s</span></p>
      </div>',
      $variation->ID,
      $variation->ID,
      __('Additional images', Plugin::L10N),
      $images_block,
      $variation->ID,
      __('Add image', Plugin::L10N)
    );

    echo $images_block;
  }

  /**
   * Renders a variation gallery item with its delete button and image info tooltip.
   *
   * @param int $variation_id
   *   The variation ID.
   * @param int $image_id
   *   The attachment ID.
   *
   * @return string
   *   The list item markup.
   */
  public static function renderVariationImage(int $variation_id, int $image_id): string {
    $delete_label = __('Delete image', Plugin::L10N);

    return sprintf('
      <li id="variation_%1$d_image_%2$d" class="variation_gallery__image" data-variation_id="%1$d" data-image_id="%2$d">
        %3$s
        <button type="button" class="variation_gallery__delete" title="%4$s"><span class="screen-reader-text">%5$s</span></button>
        <div class="variation_gallery__info">
          %6$s
          <span class="variation_gallery__info-text">%7$s: %8$s<br><br>%9$s: %10$s</span>
        </div>
      </li>',
      $variation_id,
      $image_id,
      wp_get_attachment_image($image_id, 'thumbnail', FALSE),
      esc_attr($delete_label),
      esc_html($delete_label),
      esc_html__('More Info...', Plugin::L10N),
      esc_html__('Name', Plugin::L10N),
      esc_html(wp_basename((string) get_attached_file($image_id))),
      esc_html__('Alt', Plugin::L10N),
      esc_html((string) get_post_meta($image_id, '_wp_attachment_image_alt', TRUE))
    );
  }

  /**
   * Handles Ajax requests to add an image to the variation gallery.
   *
   * @implements wp_ajax_gallerya_add_variation_image
   */
  public static function galleryaAddVariationImage() {
    $variation_id = static::verifyVariationRequest();
    $variation_gallery = WooCommerce::getVariationGalleryImages($variation_id);

    $images = [];
    foreach ((array) ($_POST['image_ids'] ?? []) as $image_id) {
      $image_id = (int) $image_id;
      if ($image_id && !in_array($image_id, $variation_gallery)) {
        $variation_gallery[] = $image_id;
        $images[] = [
          'image_id' => $image_id,
          'html' => static::renderVariationImage($variation_id, $image_id),
        ];
      }
    }
    update_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', $variation_gallery);

    wp_send_json([
      'success' => TRUE,
      'variation_id' => $variation_id,
      'images' => $images,
    ]);
  }

  /**
   * Handles Ajax requests to remove an image from the variation gallery.
   *
   * @implements wp_ajax_gallerya_remove_variation_image
   */
  public static function galleryaRemoveVariationImage() {
    $variation_id = static::verifyVariationRequest();
    $image_id = (int) ($_POST['image_id'] ?? 0);
    $variation_gallery = WooCommerce::getVariationGalleryImages($variation_id);

    if (in_array($image_id, $variation_gallery)) {
      $variation_gallery = array_values(array_diff($variation_gallery, [$image_id]));
      update_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', $variation_gallery);
    }
    wp_send_json([
      'success' => TRUE,
      'variation_id' => $variation_id,
      'image_id' => $image_id,
    ]);
  }

  /**
   * Handles Ajax requests to save the order of the variation gallery.
   *
   * @implements wp_ajax_gallerya_sort_variation_images
   */
  public static function galleryaSortVariationImages() {
    $variation_id = static::verifyVariationRequest();
    $variation_gallery = array_map('intval', WooCommerce::getVariationGalleryImages($variation_id));
    $sorted = array_unique(array_map('intval', (array) ($_POST['image_ids'] ?? [])));

    // Drops unknown IDs and keeps images missing from the request at the end.
    $sorted = array_values(array_intersect($sorted, $variation_gallery));
    $sorted = array_merge($sorted, array_values(array_diff($variation_gallery, $sorted)));
    update_post_meta($variation_id, '_' . Plugin::PREFIX . '_attachment_ids', $sorted);

    wp_send_json([
      'success' => TRUE,
      'variation_id' => $variation_id,
      'image_ids' => $sorted,
    ]);
  }

  /**
   * Verifies nonce and permissions of a variation gallery Ajax request.
   *
   * @return int
   *   The variation ID.
   */
  private static function verifyVariationRequest(): int {
    if (!check_ajax_referer('gallerya-admin-ajax', 'nonce', FALSE)) {
      wp_send_json_error(__('Nonce value cannot be verified.', Plugin::L10N));
    }
    $variation_id = (int) ($_POST['variation_id'] ?? 0);
    if (!$variation_id || !current_user_can('edit_products')) {
      wp_send_json_error(__('You are not allowed to edit this variation.', Plugin::L10N));
    }
    return $variation_id;
  }

  /**
   * Enqueues assets on the product edit screen.
   *
   * @implements admin_enqueue_scripts
   */
  public static function admin_enqueue_scripts($hook_suffix) {
    if (!in_array($hook_suffix, ['post.php', 'post-new.php'], TRUE) || get_current_screen()->post_type !== 'product') {
      return;
    }
    $style = '/dist/styles/admin.min.css';
    $script = '/dist/scripts/admin.min.js';
    wp_enqueue_style('gallerya-admin', Plugin::getBaseUrl() . $style, [], filemtime(Plugin::getBasePath() . $style));
    wp_enqueue_script('gallerya-admin', Plugin::getBaseUrl() . $script, ['jquery', 'jquery-ui-sortable'], filemtime(Plugin::getBasePath() . $script), TRUE);
    wp_localize_script('gallerya-admin', 'gallerya_admin', [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('gallerya-admin-ajax'),
    ]);
  }

}
