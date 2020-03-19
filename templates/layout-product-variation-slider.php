<?php
namespace Netzstrategen\Gallerya;

$show_page_dots = FALSE;
?>

<div class="gallerya gallerya--product-variation-slider" data-gallerya-page-dots="<?= (int) $show_page_dots ?>">
  <ul class="js-gallerya-slider">
    <?php foreach ($attachments as $attachment) : ?>
      <?php if (!empty($attachment->image_markup)) : ?>
        <li>
          <figure class="gallerya__image">
            <?= $attachment->image_markup; ?>
          </figure>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</div>
