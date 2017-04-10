<?php
namespace Netzstrategen\Gallerya;

$default_layout = apply_filters('gallerya/default_layout', Plugin::DEFAULT_LAYOUT);
?>

<script type="text/html" id="tmpl-<?= Plugin::PREFIX ?>-settings">
  <label class="setting">
    <span><?= __('Layout', Plugin::L10N) ?></span>
    <select data-setting="layout">
      <option value="default"><?= sprintf(__('- Default (%s) -', Plugin::L10N), $default_layout) ?></option>
      <option value="slider"><?= __('Slider', Plugin::L10N) ?></option>
      <option value="grid"><?= __('Grid', Plugin::L10N) ?></option>
      <option value="grid-slider"><?= __('Grid slider', Plugin::L10N) ?></option>
    </select>
  </label>
</script>

<script>
  jQuery(document).ready(function($) {
    if (typeof _ !== 'function') {
      return;
    }
    _.extend(wp.media.gallery.defaults, {
      layout: 'default'
    });
    wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
      template: function(view) {
        return wp.media.template('<?= Plugin::PREFIX ?>-settings')(view);
      }
    });
  });
</script>
