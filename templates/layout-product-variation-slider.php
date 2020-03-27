<?php

namespace Netzstrategen\Gallerya;

$show_page_dots = FALSE;
$slider_image_size = has_image_size('woocommerce_thumbnail') ? 'woocommerce_thumbnail' : 'medium';
$slider_image_src = apply_filters('gallerya/image_size_product_variation_slider', $slider_image_size);
$transparent_pixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
?>

<div class="gallerya gallerya--product-variation-slider" data-gallerya-page-dots="<?= (int) $show_page_dots ?>">
  <ul class="js-gallerya-slider">
    <?php foreach ($attachment_ids as $index => $id) : ?>
      <li>
        <figure class="gallerya__image">
          <?= wp_get_attachment_image($id, $slider_image_src, FALSE, $index ? [
            'src' => $transparent_pixel,
            'srcset' => $transparent_pixel,
            'data-flickity-lazyload-src' => wp_get_attachment_image_url($id, $slider_image_src),
            'data-flickity-lazyload-srcset' => wp_get_attachment_image_srcset($id, $slider_image_src),
          ] : []) ?>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
