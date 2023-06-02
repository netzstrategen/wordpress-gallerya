<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\Plugin.
 */

namespace Netzstrategen\Gallerya;

/**
 * Main front-end functionality.
 */
class Plugin {

  /**
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'gallerya';

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = self::PREFIX;

  /**
   * The default gallery layout.
   *
   * @var string
   */
  const DEFAULT_LAYOUT = 'slider';

  /**
   * @var string
   */
  private static $baseUrl;

  /**
   * @implements init
   */
  public static function init() {
    add_action('wp_enqueue_scripts', __CLASS__ . '::wp_enqueue_scripts');
    add_filter('post_gallery', __CLASS__ . '::post_gallery', 10, 2);
    add_action('print_media_templates', __CLASS__ . '::print_media_templates');

    // Adds WooCommerce specific features.
    if (static::isPluginActive('woocommerce/woocommerce.php')) {
      add_filter('woocommerce_get_settings_gallerya', __NAMESPACE__ . '\WooCommerce::woocommerce_get_settings_gallerya');

      $slider_bullet_nav_enabled = get_option('_' . Plugin::L10N . '_product_thumbnail_slider_bullet_nav_enabled');

      // Adds CSS class to single product image galleries to enable thumbnail slider via JS.
      if (get_option('_' . Plugin::L10N . '_product_thumbnail_slider_enabled') === 'yes' && $slider_bullet_nav_enabled !== 'yes') {
        add_filter('woocommerce_single_product_image_gallery_classes', __NAMESPACE__ . '\WooCommerce::woocommerce_single_product_image_gallery_classes');
      }

      // Exchanges thumbnails with bullets as control nav for the single product galleries.
      if ($slider_bullet_nav_enabled === 'yes') {
        add_filter('woocommerce_single_product_carousel_options', __NAMESPACE__ . '\WooCommerce::woocommerce_single_product_carousel_options');
      }

      // Adds thumbnail slider with variation images to products on listing pages.
      if (get_option('_' . Plugin::L10N . '_product_variation_slider_enabled') === 'yes') {
        remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
        add_action( 'woocommerce_before_shop_loop_item_title', __NAMESPACE__ . '\WooCommerce::woocommerce_template_loop_product_thumbnail', 10);
      }

      // Ensures new product are saved before updating its meta data.
      add_action('woocommerce_process_product_meta', __NAMESPACE__ . '\WooCommerce::saveNewProductBeforeMetaUpdate', 1);
      // Saves custom fields for simple products.
      add_action('woocommerce_process_product_meta', __NAMESPACE__ . '\WooCommerce::woocommerce_process_product_meta');

      // Adds data-srcset attributes to image links to make them reponsive in
      // lightGallery.
      // @todo Make lightGallery properly respect srcset & sizes in JavaScript
      // instead of duplicating that information in HTML.
      // @see https://github.com/netzstrategen/wordpress-gallerya/pull/11#issuecomment-355664739
      // Only add the filter if the plugin woo-product-gallery-slider is not active as it already include this and creates a conflict.
      if (!is_plugin_active('woo-product-gallery-slider/woo-product-gallery-slider.php')) {
        add_filter('woocommerce_single_product_image_thumbnail_html', __NAMESPACE__ . '\WooCommerce::woocommerce_single_product_image_thumbnail_html', 10, 2);
      }
      // Adds additional images from product variation gallery.
      add_filter('woocommerce_product_get_gallery_image_ids', __NAMESPACE__ . '\WooCommerce::woocommerce_product_get_gallery_image_ids', 10, 2);
      add_action('woocommerce_product_thumbnails', __NAMESPACE__ . '\WooCommerce::woocommerce_product_thumbnails', 21);

      // Adds video support to product gallery.
      Video::init();
    }

    // Adds 'no-lazy' as default class for images not to be lazy-loaded by plugin bj-lazy-load.
    if (static::isPluginActive('bj-lazy-load/bj-lazy-load.php')) {
      add_filter('bjll/skip_classes', __CLASS__ . '::bjll_skip_classes');
    }

    // Adds wp-graphql support for product variation additional gallery images.
    add_filter('graphql_register_types', __NAMESPACE__ . '\GraphQL::graphql_register_types');

  }

  /**
   * @implements wp_enqueue_scripts
   */
  public static function wp_enqueue_scripts() {
    wp_enqueue_style('flickity', static::getBaseUrl() . '/assets/lib/styles/flickity.min.css');
    wp_enqueue_style('fancybox', static::getBaseUrl() . '/assets/lib/styles/jquery.fancybox.min.css');
    wp_enqueue_style('gallerya-custom', static::getBaseUrl() . '/dist/styles/style.min.css');

    wp_enqueue_script('flickity', static::getBaseUrl() . '/assets/lib/scripts/flickity.min.js', ['jquery'], '2.0.9', TRUE);
    wp_enqueue_script('fancybox', static::getBaseUrl() . '/assets/lib/scripts/jquery.fancybox.min.js', ['jquery'], '3.5.7', TRUE);
    wp_localize_script('fancybox', 'fancyboxTranslations', [
      'language' => strstr(get_bloginfo('language'), '-', TRUE),
      'de' => [
        'close' => __('Close', Plugin::L10N),
        'next' => __('Next', Plugin::L10N),
        'prev' => __('Previous', Plugin::L10N),
        'error' => __('The requested content cannot be loaded. <br/> Please try again later.', Plugin::L10N),
        'play_start' => __('Start slideshow', Plugin::L10N),
        'play_stop' => __('Pause slideshow', Plugin::L10N),
        'full_screen' => __('Full screen', Plugin::L10N),
        'thumbs' => __('Thumbnails', Plugin::L10N),
        'download' => __('Download', Plugin::L10N),
        'share' => __('Share', Plugin::L10N),
        'zoom' => __('Zoom', Plugin::L10N),
      ]
    ]);
    wp_enqueue_script('gallerya-custom', static::getBaseUrl() . '/dist/scripts/script.min.js', [], FALSE, TRUE);
  }

  /**
   * @implements post_gallery
   */
  public static function post_gallery($output, $atts = []) {
    extract(shortcode_atts([
      'include' => '',
      'layout' => apply_filters('gallerya/default_layout', self::DEFAULT_LAYOUT),
    ], $atts));
    $args['post_type'] = 'attachment';
    $args['include'] = $include;
    $args['orderby'] = 'post__in';
    ob_start();
    static::renderTemplate(['templates/layout-' . $layout . '.php'], [
      'images' => get_posts($args),
      'nav_count_min' => apply_filters('gallerya/nav_count_min', 6),
      'group_id' => uniqid('gallerya-', TRUE),
    ]);
    $output = ob_get_clean();
    return $output;
  }

  /**
   * @implements print_media_templates
   */
  public static function print_media_templates() {
    static::renderTemplate(['templates/settings.php']);
  }

  /**
   * Adds 'no-lazy' as default class for images not to be lazy-loaded by plugin bj-lazy-load.
   *
   * @implements bjll/skip_classes
   */
  public static function bjll_skip_classes($skip_classes) {
    if (!in_array('no-lazy', $skip_classes)) {
      $skip_classes[] = 'no-lazy';
    }
    return $skip_classes;
  }

  /**
   * Renders a given plugin template, optionally overridden by the theme.
   *
   * WordPress offers no built-in function to allow plugins to render templates
   * with custom variables, respecting possibly existing theme template overrides.
   * Inspired by Drupal (5-7).
   *
   * @param array $template_subpathnames
   *   An prioritized list of template (sub)pathnames within the plugin/theme to
   *   discover; the first existing wins.
   * @param array $variables
   *   An associative array of template variables to provide to the template.
   *
   * @throws \InvalidArgumentException
   *   If none of the $template_subpathnames files exist in the plugin itself.
   */
  public static function renderTemplate(array $template_subpathnames, array $variables = []) {
    $template_pathname = locate_template($template_subpathnames, FALSE, FALSE);
    extract($variables, EXTR_SKIP | EXTR_REFS);
    if ($template_pathname !== '') {
      include $template_pathname;
    }
    else {
      while ($template_pathname = current($template_subpathnames)) {
        if (file_exists($template_pathname = static::getBasePath() . '/' . $template_pathname)) {
          include $template_pathname;
          return;
        }
        next($template_subpathnames);
      }
      throw new \InvalidArgumentException("Missing template '$template_pathname'");
    }
  }

  /**
   * Loads the plugin textdomain.
   */
  public static function loadTextdomain() {
    load_plugin_textdomain(static::L10N, FALSE, static::L10N . '/languages/');
  }

  /**
   * Checks if the given plugin is active.
   *
   * This replicates WordPress is_plugin_active() method, which only works
   * in admin pages.
   *
   * @see https://codex.wordpress.org/Function_Reference/is_plugin_active
   *
   * @param string $plugin
   *   Relative path to plugin starting from plugins folder.
   * @return bool
   *   TRUE if the plugin is active.
   */
  public static function isPluginActive($plugin) {
    return in_array($plugin, (array) get_option('active_plugins', []));
  }

  /**
   * The base URL path to this plugin's folder.
   *
   * Uses plugins_url() instead of plugin_dir_url() to avoid a trailing slash.
   */
  public static function getBaseUrl() {
    if (!isset(static::$baseUrl)) {
      static::$baseUrl = plugins_url('', static::getBasePath() . '/plugin.php');
    }
    return static::$baseUrl;
  }

  /**
   * The absolute filesystem base path of this plugin.
   *
   * @return string
   */
  public static function getBasePath() {
    return dirname(__DIR__);
  }

}
