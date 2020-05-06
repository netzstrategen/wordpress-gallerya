(function ($) {
  function renderMediaUploader(variation_id) {
    let file_frame;

    if (file_frame !== undefined) {
      file_frame.open();
      return;
    }

    // Prepare an instance of the wp media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      frame: 'post',
      state: 'insert',
      multiple: true,
      library: {
        type: 'image',
      },
    });

    file_frame.on('open', function () {
      // Remove the placeholder image.
      $(`#variation_gallery_${variation_id} .variation_gallery__image--placeholder`).remove();
    });

    file_frame.on('insert', function (e) {
      // Retrieve the selected images data returned from the Media Uploader.
      const attachments = file_frame.state().get('selection').map(
        function (attachment) {
          attachment.toJSON();
          return attachment;
      });

      const images = attachments.map(function (item) {
        return {
          image_id: item.attributes.id,
          thumbnail_url: item.attributes.sizes.thumbnail.url,
        };
      });

      // Add the selected images to the variation gallery.
      $.ajax({
        type: 'post',
        dataType: 'json',
        url: gallerya_admin.ajaxurl,
        data: {
          action: 'gallerya_add_variation_image',
          nonce: gallerya_admin.nonce,
          variation_id: variation_id,
          images: images,
        },
        success: function (response) {
          // Append additional product images.
          if (response && true === response.success) {
            response.images.forEach(item => {
              $(`#variation_gallery_${response.variation_id} .variation_gallery__images`).append(
              `<li
                id="variation_${response.variation_id}_image_${item.image_id}"
                class="variation_gallery__image"
                data-image_id="${item.image_id}"
                data-variation_id="${response.variation_id}"
              >
                <img src="${item.thumbnail_url}" />
              </li>`);
            });
          }
          else {
            console.error(response.data);
          }
        }
      });

    });

    // Now display the actual file_frame
    file_frame.open();
  }

  $('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
    // Adds one or more images to the variation gallery.
    $('.variation_gallery').on('click', '.variation_gallery__add-image', function () {
      const variation_id = $(this).data('variation_id');
      // Appends a temporary placeholder image.
      $(`#variation_gallery_${variation_id} .variation_gallery__images`).append(
        '<li class="variation_gallery__image variation_gallery__image--placeholder"></li>'
      );

      // Display the media uploader.
      renderMediaUploader(variation_id);
    });

    // Removes an image from the variation gallery.
    $('.variation_gallery').on('click', '.variation_gallery__image', function () {
      const $this = $(this);

      if (
        $this.hasClass('variation_gallery__placeholder') ||
        $this.hasClass('disabled')
      ) {
        return;
      }

      $this.css('opacity', '0.3');
      $this.addClass('disabled');

      $.ajax({
        type: 'post',
        dataType: 'json',
        url: gallerya_admin.ajaxurl,
        data: {
          action: 'gallerya_remove_variation_image',
          nonce: gallerya_admin.nonce,
          variation_id: $this.data('variation_id'),
          image_id: $this.data('image_id'),
        },
        success: function (response) {
          if (response && true === response.success) {
            $(`#variation_${response.variation_id}_image_${response.image_id}`).remove();
          }
          else {
            console.error(response.data);
          }
        }
      });
    });
  });

})(jQuery);
