<?php
namespace Netzstrategen\Gallerya;

$show_page_dots = FALSE;
$slider_image_size = has_image_size('woocommerce_thumbnail') ? 'woocommerce_thumbnail' : 'medium';
// Prevent wrong images height calculation caused by lazy loading.
$image_attr = apply_filters('gallerya_lazyload_image_attributes', [
  'data-no-lazy' => '1',
  'class' => "no-lazy attachment-$slider_image_size size-$slider_image_size",
]);
?>

<div class="gallerya gallerya--product-variation-slider" data-gallerya-page-dots="<?= (int) $show_page_dots ?>">
  <ul class="js-gallerya-slider">
    <?php foreach ($images as $index => $image):
    ?>
      <li>
        <figure class="gallerya__image">
          <?= wp_get_attachment_image($image->ID, apply_filters('gallerya/image_size_product_variation_slider', $slider_image_size), FALSE, $index ? [] : $image_attr) ?>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
