<?php
namespace Netzstrategen\Gallerya;

$group_size = 6;
?>

<div class="gallerya gallerya--slider">
  <ul class="js-gallerya-slider js-gallerya-lightbox">
    <?php foreach (array_chunk($images, $group_size) as $image_group): ?>
      <li>
        <div class="gallerya__image-group">
        <?php foreach ($image_group as $image):
          $caption = apply_filters('gallerya/image_caption', $image->post_excerpt, $image->ID);
        ?>
          <figure class="gallerya__image">
            <a href="<?= wp_get_attachment_image_src($image->ID, apply_filters('gallerya/image_size_lightbox', 'large'))[0] ?>" <?= !empty($caption) ? 'data-sub-html=".gallerya__image__caption"' : '' ?>>
              <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_grid_slider', 'thumbnail')) ?>
            <?php if (!empty($caption)): ?>
              <figcaption class="gallerya__image__caption"><?= $caption ?></figcaption>
            <?php endif; ?>
            </a>
          </figure>
        <?php endforeach; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
