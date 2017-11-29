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
  }

  /**
   * @implements wp_enqueue_scripts
   */
  public static function wp_enqueue_scripts() {
    wp_enqueue_style('gallerya-style-libs', static::getBaseUrl() . '/dist/styles/libs.min.css');
    wp_enqueue_style('gallerya-style-custom', static::getBaseUrl() . '/dist/styles/style.min.css');

    wp_enqueue_script('gallerya-script-libs', static::getBaseUrl() . '/dist/scripts/libs.min.js', ['jquery'], FALSE, TRUE);
    wp_enqueue_script('gallerya-script-custom', static::getBaseUrl() . '/dist/scripts/script.min.js', ['gallerya-script-libs'], FALSE, TRUE);
  }

  /**
   * @implements post_gallery
   */
  public static function post_gallery($output = '', $atts) {
    $atts['post_type'] = 'attachment';
    $atts['post__in'] = $atts['include'];
    $atts['orderby'] = 'post__in';
    $layout = !empty($atts['layout']) ? $atts['layout'] : apply_filters('gallerya/default_layout', self::DEFAULT_LAYOUT);
    ob_start();
    static::renderTemplate(['templates/layout-' . $layout . '.php'], [
      'images' => get_posts($atts),
      'nav_count_min' => apply_filters('gallerya/nav_count_min', 6),
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
   * Checks if wp-rocket plugin is active and images lazyload option is set.
   *
   * @return bool
   */
  public static function isLazyLoadActive() {
    return is_plugin_active('wp-rocket/wp-rocket.php') && get_rocket_option('lazyload');
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
