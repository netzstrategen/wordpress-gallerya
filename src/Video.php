<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\Video.
 */

namespace Netzstrategen\Gallerya;

/**
 * Product gallery video support integration.
 */
class Video {

  /**
   * TRUE when product featured image has been replaced with video.
   *
   * @var bool
   */
  private static $featuredImageReplaced = FALSE;

  public static function init() {
    // Adds video player to single product gallery.
    add_filter('woocommerce_single_product_image_thumbnail_html', __CLASS__ . '::replaceFeaturedImageWithVideo', 10, 2);
    // Adds video ID to the list of images in the product images gallery.
    add_filter('woocommerce_product_get_gallery_image_ids', __CLASS__ . '::woocommerce_product_get_gallery_image_ids');
  }

  /**
   * Adds video player to single product gallery.
   *
   * @implements woocommerce_single_product_image_thumbnail_html
   */
  public static function replaceFeaturedImageWithVideo($html, $thumbnail_id) {
    global $product;

    if (!$product) {
      return $html;
    }

    // Check to make sure we're only targeting the featured image.
    $product_id = $product->get_id();
    if (
      static::checkDisplayVideoThumb($product_id) &&
      $thumbnail_id === $product->get_image_id() &&
      !static::$featuredImageReplaced
    ) {
      static::$featuredImageReplaced = TRUE;
      $video_id = get_post_meta($product_id, '_' . Plugin::PREFIX . '_video_id', TRUE);
      $video_source = get_post_meta($product_id, '_' . Plugin::PREFIX . '_video_source', TRUE);

      if ($video_id && $video_source) {
        ob_start();
        if ($video_source === 'youtube') {
          $video_url = sprintf(
              'https://www.youtube-nocookie.com/embed/%s/?enablejsapi=1&origin=%s&rel=0',
              $video_id,
              get_site_url()
            );
          $video_thumb = sprintf('https://img.youtube.com/vi/%s/mqdefault.jpg', $video_id);
        }
        elseif ($video_source === 'vimeo') {
          $video_url = sprintf('https://player.vimeo.com/video/%s', $video_id);
          $video_thumb = static::getVimeoThumb($product_id, $video_id);
        }
        ?>
        <div class="woocommerce-product-gallery__image has-video gallerya__featured-content">
          <div
            class="gallerya__video-content <?= $video_source ?>"
            data-video-thumb="<?= $video_thumb; ?>"
          >
            <iframe
              id="video_<?php echo $product_id; ?>"
              src="<?php echo $video_url; ?>"
              frameborder="0"
              allow="autoplay; fullscreen"
              allowfullscreen
            >
            </iframe>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
      }
    }
    return $html;
  }

  /**
   * Checks if video thumbnail should be displayed for a given product.
   *
   * @param int $product_id
   *   Product unique identifier.
   *
   * @return bool
   *   TRUE if video thumbnail should be added to thumbnails slider.
   */
  public static function checkDisplayVideoThumb($product_id) {
    return get_post_meta($product_id, '_' . Plugin::PREFIX . '_video_display', TRUE) === 'yes';
  }

  /**
   * Gets a vimeo thumbnail url.
   *
   * We save the retrieved video thumbnail in a transient with a expiry time of
   * one day to reduce the requests to the vimeo API.
   *
   * @param int $product_id
   *   Product unique identifier.
   * @param string $video_id
   *   A vimeo video id.
   *
   * @return string
   *   URL of video thumbnail.
   */
  public static function getVimeoThumb($product_id, $video_id) {
    $transient_key = Plugin::PREFIX . '_video_thumb_' . $product_id;

    if (FALSE !== ($video_thumb = get_site_transient($transient_key))) {
      return $video_thumb;
    }

    $video_thumb = '';
    if ($data = file_get_contents("http://vimeo.com/api/v2/video/$video_id.json")) {
      $json_data = json_decode($data);
      if ($json_data) {
        $video_thumb = $json_data[0]->thumbnail_medium ?? '';
      }
    };

    if ($video_thumb) {
      set_site_transient($transient_key, $video_thumb, DAY_IN_SECONDS);
    }

    return $video_thumb;
  }

  /**
   * Adds video ID to the list of images in the product images gallery.
   *
   * @implements woocommerce_product_get_gallery_image_ids
   */
  public static function woocommerce_product_get_gallery_image_ids($value) {
    global $product;

    if (!$product) {
      return $value;
    }

    $product_id = $product->get_id();
    if (static::checkDisplayVideoThumb($product_id)) {
      $featured_image = $product->get_image_id();
      $video_id = get_post_meta($product_id, '_' . Plugin::PREFIX . '_video_id', TRUE);

      if ($video_id && $featured_image) {
        $value = array_merge([$featured_image], $value);
      }
    }
    return $value;
  }

  /**
   * Adds custom product data panels.
   *
   * @implements woocommerce_product_data_panels
   */
  public static function woocommerce_product_data_panels() {
    // Adds the tab content for 'Videos'.
    echo '<div id="_' . Plugin::PREFIX . '_videos_product_data" class="panel woocommerce_options_panel hidden">';
    woocommerce_wp_text_input([
      'id' => '_' . Plugin::PREFIX . '_video_id',
      'value' => get_post_meta(get_the_ID(), '_' . Plugin::PREFIX . '_video_id', TRUE),
      'label' => __('Video ID', Plugin::L10N),
      'description' => __('ID of video for Vimeo or YouTube', Plugin::L10N),
    ]);
    woocommerce_wp_select([
      'id' => '_' . Plugin::PREFIX . '_video_source',
      'value' => get_post_meta(get_the_ID(), '_' . Plugin::PREFIX . '_video_source', TRUE),
      'label' => __('Video source', Plugin::L10N),
      'options' => [
        '' => __('Please select', Plugin::L10N),
        'vimeo' => 'Vimeo',
        'youtube' => 'YouTube',
      ],
    ]);
    woocommerce_wp_checkbox([
      'id' => '_' . Plugin::PREFIX . '_video_display',
      'value' => get_post_meta(get_the_ID(), '_' . Plugin::PREFIX . '_video_display', TRUE),
      'label' => __('Display video', Plugin::L10N),
    ]);
    echo '</div>';
  }

  /**
   * Adds custom product data tabs.
   *
   * @implements woocommerce_product_data_tabs
   */
  public static function woocommerce_product_data_tabs($tabs) {
    // Adds a 'Videos' tab.
    $tabs['_' . Plugin::PREFIX . '_videos'] = [
      'label' => __('Videos', Plugin::L10N),
      'target' => '_' . Plugin::PREFIX . '_videos_product_data',
      'class' => ['show_if_simple', 'show_if_variable'],
      'priority' => 21,
    ];

    return $tabs;
  }

  /**
   * Sets icons for custom product data tabs.
   *
   * @implements admin_head
   */
  public static function addCustomIconsToTabs() {
    // Adds icon for the 'Videos' tab.
    echo '<style>
      #woocommerce-product-data .wc-tabs ._' . Plugin::L10N . '_videos_tab a:before {
        content: "\f236";
      }
    </style>';
  }

}
