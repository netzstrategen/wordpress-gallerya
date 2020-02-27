(function($) {
  $(document).ready(function () {

    if ($('.js-gallerya-slider').length > 0 && typeof $.fn.flickity === 'function') {
      var arrowShape = 'M85,50.36033a2.72075,2.72075,0,0,0-2.74945-2.68906H24.01177L47.61119,24.59022a2.65667,2.65667,0,0,0,0-3.80232,2.79411,2.79411,0,0,0-3.88955,0L15.80559,48.09077a2.64614,2.64614,0,0,0,0,3.80232L43.729,79.21211a2.79185,2.79185,0,0,0,3.88771,0,2.64613,2.64613,0,0,0,0-3.80233L24.756,53.04939h57.4946A2.72075,2.72075,0,0,0,85,50.36033Z';
      $('.js-gallerya-slider').each(function(index, element) {
        var galleryaSlider = $(this).closest('.gallerya--slider, .gallerya--product-variation-slider');
        var navigation = galleryaSlider.data('gallerya-navigation');
        var pageDots = galleryaSlider.data('galleryaPageDots');
        var thumbnails = galleryaSlider.find('.js-gallerya-thumbnail-slider');
        var count = galleryaSlider.find('[data-gallerya-count]');
        var sliderArgs = {
          cellAlign: 'left',
          contain: true,
          wrapAround: true,
          imagesLoaded: true,
          watchCSS: true,
          lazyLoad: true,
        };
        if (!galleryaSlider.hasClass('gallerya--product-variation-slider')) {
          sliderArgs.arrowShape = arrowShape;
        }
        if (typeof pageDots !== 'undefined') {
          // Let the pageDots property be overriden by a data-attribute.
          sliderArgs.pageDots = pageDots == true;
        }
        else if (typeof navigation === 'undefined' || navigation == false || thumbnails.length > 0) {
          sliderArgs.pageDots = false;
        }
        // Adjust styling before slider init.
        $(this).addClass('flickity');
        $(this).flickity(sliderArgs);
        var flickityData = $(this).data('flickity');
        if (thumbnails.length > 0) {
          var thumbnailsArgs = {
            asNavFor: element,
            contain: true,
            pageDots: false,
            imagesLoaded: true,
            groupCells: true,
            arrowShape: arrowShape,
          };
          thumbnails.flickity(thumbnailsArgs);

          $(this).on('select.flickity', function () {
            var index = flickityData.selectedIndex;
            var className = 'is-currently-selected';
            thumbnails.find('.flickity-slider li').removeClass(className)
              .eq(index).addClass(className);
          });
        }
        if (count) {
          $(this).on('select.flickity', function () {
            var slideNumber = flickityData.selectedIndex + 1;
            count.text(slideNumber + '/' + flickityData.slides.length);
          });
        }
      });

      $('.woocommerce-loop-product__link').on('click', function(e) {
        // Prevent clicks onto slider arrows to bubble through to wrapping product link.
        if ($(e.target).closest('.flickity-prev-next-button').length !== 0){
          event.stopPropagation();
          return false;
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
      const $thumbnailSliderEl = $('.js-gallerya-product-thumbnail-slider').parent().find('.flex-control-thumbs').first();
      if (!$thumbnailSliderEl.length) {
        return;
      }

      const thumbnailSliderArgs = {
        contain: true,
        pageDots: false,
        imagesLoaded: true,
        groupCells: true,
      };

      // Calculate # of cols, given the initial images width as the minimum, at least 4 cols.
      const thumbnailSpacingUnit  = parseInt(getComputedStyle($thumbnailSliderEl.get(0)).getPropertyValue('--thumbnail-spacing-unit'), 10);
      const thumbnailSliderMargin = parseInt(getComputedStyle($thumbnailSliderEl.get(0)).getPropertyValue('--thumbnail-slider-margin'), 10);
      const initialImgsWidth = $thumbnailSliderEl.find('li').first().width();
      const thumbnailSliderWidth = $thumbnailSliderEl.width();
      const maxCols = 7;
      var noCols = 4;
      if (initialImgsWidth > 0 && thumbnailSliderWidth > initialImgsWidth) {
        while (((thumbnailSliderWidth - (2 * thumbnailSliderMargin)) - (thumbnailSpacingUnit * noCols)) / (noCols + 1) >= initialImgsWidth) {
          noCols++;
          if (noCols === maxCols) {
            break;
          }
        }
        // Update # of cols in css vars.
        $thumbnailSliderEl.get(0).style.setProperty('--thumbnail-count', noCols);
      }

      // Adjust styling before slider init.
      $thumbnailSliderEl
        .addClass('flickity')
        .on('ready.flickity', function(event) {
          // Video should be the second element in the product gallery.
          const $galleryWraper = $('.sidebar-single-product .woocommerce-product-gallery__wrapper');
          const $galleryElements = $galleryWraper.children('div');
          if ($galleryElements.length <= 1) {
            return;
          }
          const $firstGalleryElement = $galleryElements.first();
          if ($firstGalleryElement.hasClass('has-video')) {
            $firstGalleryElement.detach().insertAfter($galleryWraper.children('div').first());
          }
        })
        .on('settle.flickity', function(event, index) {
          // Insert the video thumbnail in the thumbs slider.
          const $videoContent = $('.sidebar-single-product .gallerya__video-content');
          if ($videoContent.length) {
            const $sliderThumbs = $('.sidebar-single-product .flickity-slider li img');
            // Insert the video thumb in second position, if posible.
            const videoThumbPos = $sliderThumbs.length > 1 ? 1 : 0;
            const videoThumbSrc = $videoContent.data('video-thumb');

            $sliderThumb = $sliderThumbs.eq(videoThumbPos);
            $sliderThumb
              .attr('src', videoThumbSrc)
              // Ensure lazy loaded image is the video thumbnail.
              .attr('data-lazy-src',videoThumbSrc)
              .parent().addClass('slider-thumb-video');
          }
        });
      const $thumbnailSlider = $thumbnailSliderEl.flickity(thumbnailSliderArgs);

      // Sync thumbnail slider with gallery image changes triggered through the choice of a product variation.
      $('.single_variation_wrap').on('show_variation', function (event, variation) {
        // Get the index of the variation thumbnail in the thumbnail slider.
        const thumbnailIndex = $thumbnailSliderEl.find('img[src="' + variation.image.gallery_thumbnail_src + '"]').parent().index();
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
