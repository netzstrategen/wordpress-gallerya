<?php
namespace Netzstrategen\Gallerya;

$show_navigation = count($images) >= $nav_count_min;
$slider_image_size = has_image_size('post-thumbnail') ? 'post-thumbnail' : 'large';
?>

<div class="gallerya gallerya--slider">
  <ul class="js-gallerya-slider js-gallerya-lightbox">
    <?php $image_attr = Plugin::lazyLoadIsActive() ? ['data-no-lazy' => '1'] : []; ?>
    <?php foreach ($images as $index => $image):
      $caption = apply_filters('gallerya/image_caption', $image->post_excerpt, $image->ID);
    ?>
      <li>
        <figure class="gallerya__image">
          <a href="<?= wp_get_attachment_image_src($image->ID, apply_filters('gallerya/image_size_lightbox', 'large'))[0] ?>" <?= !empty($caption) ? 'data-sub-html="' . esc_attr($caption) . '"' : '' ?>>
            <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_slider', $slider_image_size), FALSE, !$index ?: $image_attr) ?>
          <?php if (!empty($caption)): ?>
            <figcaption class="gallerya__image__caption"><?= $caption ?></figcaption>
          <?php endif; ?>
          </a>
        </figure>
      </li>
      <?php $image_attr = []; ?>
    <?php endforeach; ?>
  </ul>
  <?php if ($show_navigation): ?>
    <ul class="gallerya--slider__nav  js-gallerya-thumbnail-slider">
      <?php foreach ($images as $image): ?>
        <li>
          <figure class="gallerya__image">
            <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_slider_thumbnail', 'thumbnail'), FALSE, $image_attr) ?>
          </figure>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
