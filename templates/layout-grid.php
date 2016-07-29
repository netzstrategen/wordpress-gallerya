<?php
namespace Netzstrategen\Gallerya;
?>

<div class="gallerya gallerya--grid">
  <ul class="js-gallerya-slider js-gallerya-lightbox">
    <?php foreach ($images as $image): ?>
      <li>
        <figure class="gallerya__image">
          <a href="<?= wp_get_attachment_image_src($image->ID, apply_filters('gallerya/image_size_lightbox', 'large'))[0] ?>" data-sub-html=".gallerya__image__caption">
            <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_grid', 'medium')) ?>
          <?php if ($image->post_excerpt): ?>
            <figcaption class="gallerya__image__caption"><?= apply_filters('gallerya/image_caption', $image->post_excerpt, $image->ID) ?></figcaption>
          <?php endif; ?>
          </a>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
