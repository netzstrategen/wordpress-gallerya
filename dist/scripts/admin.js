/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
(function ($) {
  const queues = {};
  let file_frame;
  let target_variation_id;
  function showError(response) {
    window.alert(response && typeof response.data === 'string' ? response.data : gallerya_admin.i18n.error);
  }

  // Runs a variation's gallery requests one after another, so an older request can't overwrite a newer one.
  // getData is called when the request is sent, so queued requests use the latest state.
  function request(variation_id, action, getData) {
    const send = () => $.ajax({
      type: 'post',
      dataType: 'json',
      url: gallerya_admin.ajaxurl,
      data: $.extend({
        action: action,
        nonce: gallerya_admin.nonce,
        variation_id: variation_id
      }, getData())
    }).then(response => response && true === response.success ? response : $.Deferred().reject(response));
    queues[variation_id] = (queues[variation_id] || $.when()).then(send, send);
    return queues[variation_id];
  }
  function addImages(variation_id, image_ids) {
    request(variation_id, 'gallerya_add_variation_image', () => ({
      image_ids: image_ids
    })).done(response => {
      const $images = $(`#variation_gallery_${variation_id} .variation_gallery__images`);
      response.images.forEach(item => $images.append(item.html));
      initSortable();
      $images.sortable('refresh');
    }).fail(showError);
  }
  function openMediaFrame(variation_id) {
    target_variation_id = variation_id;
    if (!file_frame) {
      file_frame = wp.media({
        frame: 'post',
        state: 'insert',
        multiple: true,
        library: {
          type: 'image'
        }
      });
      // The frame is shared by all variations, so start each one without the previous selection.
      file_frame.on('open', () => file_frame.state('insert').get('selection').reset());
      file_frame.on('insert', () => {
        addImages(target_variation_id, file_frame.state().get('selection').map(attachment => attachment.id));
      });
    }
    file_frame.open();
  }
  function saveOrder($images) {
    request($images.closest('.variation_gallery').data('variation_id'), 'gallerya_sort_variation_images', () => ({
      image_ids: $images.sortable('toArray', {
        attribute: 'data-image_id'
      })
    })).fail(showError);
  }

  // Same drag and drop behaviour as the WooCommerce product gallery.
  function initSortable() {
    $('.variation_gallery__images').not('.ui-sortable').sortable({
      items: '.variation_gallery__image',
      // Images being deleted can't be dragged; "items" alone doesn't stop already registered items.
      cancel: '.disabled, input, textarea, button, select, option',
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
    openMediaFrame($(this).data('variation_id'));
  });

  // Removes an image from the variation gallery.
  $product_data.on('click', '.variation_gallery__delete', function () {
    const $image = $(this).closest('.variation_gallery__image');
    if ($image.hasClass('disabled')) {
      return;
    }
    $image.addClass('disabled');
    request($image.data('variation_id'), 'gallerya_remove_variation_image', () => ({
      image_id: $image.data('image_id')
    })).done(() => $image.remove()).fail(response => {
      $image.removeClass('disabled');
      showError(response);
    });
  });
})(jQuery);
/******/ })()
;