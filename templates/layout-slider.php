<?php
namespace Netzstrategen\Gallerya;

$show_navigation = count($images) > 5;
?>

<div class="gallerya gallerya--slider">
  <ul class="js-gallerya-slider js-gallerya-lightbox">
    <?php foreach ($images as $image):
      $caption = apply_filters('gallerya/image_caption', $image->post_excerpt, $image->ID);
    ?>
      <li>
        <figure class="gallerya__image">
          <a href="<?= wp_get_attachment_image_src($image->ID, apply_filters('gallerya/image_size_lightbox', 'large'))[0] ?>" <?= !empty($caption) ? 'data-sub-html=".gallerya__image__caption"' : '' ?>>
            <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_slider', 'post-thumbnail')) ?>
          <?php if (!empty($caption)): ?>
            <figcaption class="gallerya__image__caption"><?= $caption ?></figcaption>
          <?php endif; ?>
          </a>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($show_navigation): ?>
    <ul class="gallerya--slider__nav  js-gallerya-thumbnail-slider">
      <?php foreach ($images as $image): ?>
        <li>
          <figure class="gallerya__image">
            <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_slider_thumbnail', 'thumbnail')) ?>
          </figure>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
