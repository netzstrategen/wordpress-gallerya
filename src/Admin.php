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
    Settings::init();
  }

}
