<?php
namespace Netzstrategen\Gallerya;

$group_size = 6;
$slider_image_size = has_image_size('post-thumbnail') ? 'post-thumbnail' : 'large';
// Prevent wrong images height calculation caused by lazy loading.
$image_attr = apply_filters('gallerya_lazyload_image_attributes', [
  'data-no-lazy' => '1',
  'class' => "no-lazy attachment-$slider_image_size size-$slider_image_size",
]);
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
            <a data-fancybox="<?=$group_id?>" href="<?= wp_get_attachment_image_src($image->ID, apply_filters('gallerya/image_size_lightbox', 'large'))[0] ?>" <?= !empty($caption) ? 'data-caption="' . esc_attr($caption) . '"' : '' ?>>
              <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_grid_slider', 'thumbnail'), FALSE, $image_attr) ?>
            </a>
          </figure>
        <?php endforeach; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
