<?php
namespace Netzstrategen\Gallerya;

$show_page_dots = FALSE;
?>

<div class="gallerya gallerya--product-variation-slider" data-gallerya-page-dots="<?= (int) $show_page_dots ?>">
  <ul class="js-gallerya-slider">
    <?php foreach ($images as $index => $image) : ?>
      <li>
        <figure class="gallerya__image">
          <?= $image['markup'] ?>
        </figure>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
