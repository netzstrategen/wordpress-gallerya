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
   * @implements admin_init
   */
  public static function init() {
    add_action('print_media_templates', __CLASS__ . '::print_media_templates');
  }

  /**
   * @implements print_media_templates
   */
  public static function print_media_templates() {
    Plugin::renderTemplate(['templates/admin-settings.php']);
  }

}
