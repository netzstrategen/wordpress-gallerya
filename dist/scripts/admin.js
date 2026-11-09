/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
(function ($) {
  function renderMediaUploader(variation_id) {
    // Prepare an instance of the wp media frame.
    const file_frame = wp.media.frames.file_frame = wp.media({
      frame: 'post',
      state: 'insert',
      multiple: true,
      library: {
        type: 'image'
      }
    });
    file_frame.on('open', function () {
      // Remove the placeholder image.
      $(`#variation_gallery_${variation_id} .variation_gallery__image--placeholder`).remove();
    });
    file_frame.on('insert', function () {
      const image_ids = file_frame.state().get('selection').map(attachment => attachment.id);

      // Add the selected images to the variation gallery.
      $.ajax({
        type: 'post',
        dataType: 'json',
        url: gallerya_admin.ajaxurl,
        data: {
          action: 'gallerya_add_variation_image',
          nonce: gallerya_admin.nonce,
          variation_id: variation_id,
          image_ids: image_ids
        },
        success: function (response) {
          if (response && true === response.success) {
            const $images = $(`#variation_gallery_${response.variation_id} .variation_gallery__images`);
            response.images.forEach(item => $images.append(item.html));
            initSortable();
            $images.sortable('refresh');
          } else {
            console.error(response.data);
          }
        }
      });
    });

    // Now display the actual file_frame
    file_frame.open();
  }
  function saveOrder($images) {
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: gallerya_admin.ajaxurl,
      data: {
        action: 'gallerya_sort_variation_images',
        nonce: gallerya_admin.nonce,
        variation_id: $images.closest('.variation_gallery').data('variation_id'),
        image_ids: $images.sortable('toArray', {
          attribute: 'data-image_id'
        })
      },
      success: function (response) {
        if (!response || true !== response.success) {
          console.error(response.data);
        }
      }
    });
  }

  // Same drag and drop behaviour as the WooCommerce product gallery.
  function initSortable() {
    $('.variation_gallery__images').not('.ui-sortable').sortable({
      items: '.variation_gallery__image:not(.variation_gallery__image--placeholder)',
      cursor: 'move',
      scrollSensitivity: 40,
      forcePlaceholderSize: true,
      forceHelperSize: false,
      helper: 'clone',
      opacity: 0.65,
      placeholder: 'variation_gallery__image--sortable-placeholder',
      update: function () {
        saveOrder($(this));
      }
    });
  }
  const $product_data = $('#woocommerce-product-data');
  $product_data.on('woocommerce_variations_loaded woocommerce_variations_added', initSortable);

  // Adds one or more images to the variation gallery.
  $product_data.on('click', '.variation_gallery__add-image', function () {
    const variation_id = $(this).data('variation_id');
    // Appends a temporary placeholder image.
    $(`#variation_gallery_${variation_id} .variation_gallery__images`).append('<li class="variation_gallery__image variation_gallery__image--placeholder"></li>');

    // Display the media uploader.
    renderMediaUploader(variation_id);
  });

  // Removes an image from the variation gallery.
  $product_data.on('click', '.variation_gallery__delete', function () {
    const $image = $(this).closest('.variation_gallery__image');
    if ($image.hasClass('disabled')) {
      return;
    }
    $image.addClass('disabled');
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: gallerya_admin.ajaxurl,
      data: {
        action: 'gallerya_remove_variation_image',
        nonce: gallerya_admin.nonce,
        variation_id: $image.data('variation_id'),
        image_id: $image.data('image_id')
      },
      success: function (response) {
        if (response && true === response.success) {
          $(`#variation_${response.variation_id}_image_${response.image_id}`).remove();
        } else {
          $image.removeClass('disabled');
          console.error(response.data);
        }
      }
    });
  });
})(jQuery);
/******/ })()
;