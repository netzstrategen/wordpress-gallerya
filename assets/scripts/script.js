(function($) {
  $(document).ready(function () {

    if ($('.js-gallerya-slider').length > 0 && typeof $.fn.flickity === 'function') {
      var arrowShape = 'M85,50.36033a2.72075,2.72075,0,0,0-2.74945-2.68906H24.01177L47.61119,24.59022a2.65667,2.65667,0,0,0,0-3.80232,2.79411,2.79411,0,0,0-3.88955,0L15.80559,48.09077a2.64614,2.64614,0,0,0,0,3.80232L43.729,79.21211a2.79185,2.79185,0,0,0,3.88771,0,2.64613,2.64613,0,0,0,0-3.80233L24.756,53.04939h57.4946A2.72075,2.72075,0,0,0,85,50.36033Z';
      $('.js-gallerya-slider').each(function(index, element) {
        var navigation = $(this).closest('.gallerya--slider').attr('data-gallerya-navigation');
        var thumbnails = $(this).closest('.gallerya--slider').find('.js-gallerya-thumbnail-slider');
        var count = $(this).closest('.gallerya--slider').find('[data-gallerya-count]');
        var sliderArgs = {
          cellAlign: 'left',
          contain: true,
          wrapAround: true,
          imagesLoaded: true,
          watchCSS: true,
          arrowShape: arrowShape
        };
        if (navigation == false || thumbnails.length > 0) {
          sliderArgs['pageDots'] = false;
        }
        $(this).flickity(sliderArgs);
        var sliderData = $(this).data('flickity');
        if (thumbnails.length > 0) {
          var thumbnailsArgs = {
            asNavFor: element,
            contain: true,
            pageDots: false,
            imagesLoaded: true,
            groupCells: true,
            arrowShape: arrowShape
          };
          thumbnails.flickity(thumbnailsArgs);

          $(this).on('select.flickity', function () {
            var index = sliderData.selectedIndex;
            var className = 'is-currently-selected';
            thumbnails.find('.flickity-slider li').removeClass(className)
              .eq(index).addClass(className);
          });
        }
        if (count) {
          $(this).on('select.flickity', function () {
            var slideNumber = sliderData.selectedIndex + 1;
            count.text(slideNumber + '/' + sliderData.slides.length);
          });
        }
      });
    }

    if (typeof $.fn.lightGallery === 'function') {
      $('.js-gallerya-lightbox').lightGallery({
        thumbnail: true,
        showThumbByDefault: false,
        subHtmlSelectorRelative: true,
        selector: '.gallerya__image > a'
      });
      $('.woocommerce-product-gallery').lightGallery({
        thumbnail: true,
        showThumbByDefault: false,
        subHtmlSelectorRelative: true,
        selector: '.woocommerce-product-gallery__image > a'
      });
    }

    if ($('.js-gallerya-product-thumbnail-slider').length > 0 && typeof $.fn.flickity === 'function') {
      const $thumbnailSliderEl = $('.js-gallerya-product-thumbnail-slider .flex-control-thumbs');
      const thumbnailSliderArgs = {
        contain: true,
        pageDots: false,
        imagesLoaded: true,
        groupCells: true,
      };
      $thumbnailSliderEl.addClass('flickity'); // Adjust styling before slider init.
      const $thumbnailSlider = $thumbnailSliderEl.flickity(thumbnailSliderArgs);

      // Sync thumbnail slider with gallery image changes triggered through the choice of a product variation.
      $('.single_variation_wrap').on('show_variation', function (event, variation) {
        // Get the index of the variation thumbnail in the thumbnail slider.
        const thumbnailIndex = $thumbnailSliderEl.find('img[src="'+variation.image.gallery_thumbnail_src+'"]').parent().index();
        // Select the variation thumbnail in the thumbnail slider.
        $thumbnailSlider.flickity('select', thumbnailIndex);
      });
    }

    /**
     * Modifies data-srcset of product gallery first image on variation change.
     *
     * lightGallery gets the srcset from the anchor element that wraps the
     * image. On variation change, WooCommerce changes the srcset of the first
     * product image with the variation one, but not the srcset of the wrapping
     * anchor element.
     */
    $('.variations_form').on('woocommerce_variation_has_changed', function() {
      const firstImage = $('.woocommerce-product-gallery__image img').eq(0);
      if (firstImage.length) {
        firstImage.parents('[data-srcset]').attr('data-srcset', firstImage.attr('srcset'));
      }
    });

  });
})(jQuery);
