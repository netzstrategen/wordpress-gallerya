<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\Settings.
 */

namespace Netzstrategen\Gallerya;

use WC_Admin_Settings;

/**
 * Configuration for the plugin settings sections.
 */
class Settings {

  /**
   * Initializes all our backend settings.
   *
   * @implements woocommerce_get_settings_pages
   */
  public static function woocommerce_get_settings_pages($settings) {
    add_action('woocommerce_settings_tabs_array', __CLASS__ . '::woocommerce_settings_tabs_array', 30);
    add_action('woocommerce_settings_gallerya', __CLASS__ . '::woocommerce_settings_gallerya');
    add_action('woocommerce_settings_save_gallerya', __CLASS__ . '::woocommerce_settings_save_gallerya');
    return $settings;
  }

  /**
   * Defines plugin configuration settings.
   *
   * @return array
   */
  public static function getSettings(): array {
    return apply_filters('woocommerce_get_settings_gallerya', []);
  }

  /**
   * Adds a Gallerya section tab.
   *
   * @implements woocommerce_settings_tabs_array
   */
  public static function woocommerce_settings_tabs_array(array $tabs): array {
    $tabs['gallerya'] = __('Gallerya', Plugin::L10N);
    return $tabs;
  }

  /**
   * Adds settings fields to corresponding WooCommerce settings section.
   *
   * @implements woocommerce_settings_<current_tab>
   */
  public static function woocommerce_settings_gallerya() {
    $settings = static::getSettings();
    WC_Admin_Settings::output_fields($settings);
  }

  /**
   * Triggers setting save.
   *
   * @implements woocommerce_settings_save_<current_tab>
   */
  public static function woocommerce_settings_save_gallerya() {
    $settings = static::getSettings();
    WC_Admin_Settings::save_fields($settings);
  }

}
