<?php
namespace Netzstrategen\Gallerya;
?>

<div class="gallerya gallerya--grid">
  <ul class="js-gallerya-lightbox">
    <?php foreach ($images as $image):
      $caption = apply_filters('gallerya/image_caption', $image->post_excerpt, $image->ID);
    ?>
      <li>
        <figure class="gallerya__image">
          <a href="<?= wp_get_attachment_image_src($image->ID, apply_filters('gallerya/image_size_lightbox', 'large'))[0] ?>" <?= !empty($caption) ? 'data-caption="' . esc_attr($caption) . '"' : '' ?>>
            <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_grid', 'medium')) ?>
          <?php if (!empty($caption)): ?>
            <figcaption class="gallerya__image__caption"><?= $caption ?></figcaption>
          <?php endif; ?>
          </a>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
