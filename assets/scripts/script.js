(function ($) {
  $(document).ready(function () {

    // Adds slide navigation through variation thumbnails for variable products
    // displayed in products listings.
    if ($('.js-gallerya-slider').length > 0 && typeof $.fn.flickity === 'function') {
      const arrowShape = 'M85,50.36033a2.72075,2.72075,0,0,0-2.74945-2.68906H24.01177L47.61119,24.59022a2.65667,2.65667,0,0,0,0-3.80232,2.79411,2.79411,0,0,0-3.88955,0L15.80559,48.09077a2.64614,2.64614,0,0,0,0,3.80232L43.729,79.21211a2.79185,2.79185,0,0,0,3.88771,0,2.64613,2.64613,0,0,0,0-3.80233L24.756,53.04939h57.4946A2.72075,2.72075,0,0,0,85,50.36033Z';

      $('.js-gallerya-slider').each(function (index, element) {
        const galleryaSlider = $(this).closest('.gallerya--slider, .gallerya--product-variation-slider');
        const navigation = galleryaSlider.data('gallerya-navigation');
        const pageDots = galleryaSlider.data('galleryaPageDots');
        const thumbnails = galleryaSlider.find('.js-gallerya-thumbnail-slider');
        const count = galleryaSlider.find('[data-gallerya-count]');
        const sliderArgs = {
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

        const flickityData = $(this).data('flickity');
        if (thumbnails.length > 0) {
          const thumbnailsArgs = {
            asNavFor: element,
            contain: true,
            pageDots: false,
            imagesLoaded: true,
            groupCells: true,
            arrowShape: arrowShape,
          };
          thumbnails.flickity(thumbnailsArgs);

          $(this).on('select.flickity', function () {
            const index = flickityData.selectedIndex;
            const className = 'is-currently-selected';
            thumbnails.find('.flickity-slider li').removeClass(className)
              .eq(index).addClass(className);
          });
        }
        if (count) {
          $(this).on('select.flickity', function () {
            const slideNumber = flickityData.selectedIndex + 1;
            count.text(slideNumber + '/' + flickityData.slides.length);
          });
        }
      });

      $('.woocommerce-loop-product__link').on('click', function (e) {
        // Prevent clicks onto slider arrows to bubble through to wrapping product link.
        if ($(e.target).closest('.flickity-prev-next-button').length !== 0){
          event.stopPropagation();
          return false;
        }
      });
    }

    // Adds lightbox functionality to product gallery.
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

    // Adds thumbnails slider to product gallery on product detail page.
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
      let noCols = 4;

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

      // If less thumbs than slider viewport, don't show prev/next arrows.
      const numberOfThumbnails = $thumbnailSliderEl.find('li').length;
      thumbnailSliderArgs.prevNextButtons = numberOfThumbnails > noCols;

      // Add video player and video thumbnail to the product gallery.
      $thumbnailSliderEl
        .addClass('flickity')
        .on('ready.flickity', function () {
          setProductGalleryVideoSlide();
        })
        .on('settle.flickity', function () {
          setProductGalleryVideoThumbnail();
        })
        .on('staticClick.flickity', function (event, pointer, cellElement, cellIndex) {
          if ($(cellElement).hasClass('slider-thumb-video')) {
            setVideoPlayerUrl(cellIndex);
          }
        });
      // Initialise the thumbnail slider.
      const $thumbnailSlider = $thumbnailSliderEl.flickity(thumbnailSliderArgs);
      const $singleVariation = $('.single_variation_wrap');
      // Initially no slide is hidden.
      $thumbnailSliderEl.hiddenSlides = 0;

      // Selects the slide in the product gallery when the user hovers over the
      // thumbnail images.
      const productGallery = $('.woocommerce-product-gallery').data('flexslider');
      if (productGallery) {
        $thumbnailSlider.on('mouseover', 'li', function (event) {
          productGallery.flexslider($(event.currentTarget).index());
        });
      }

      // Reacts to product variation selection.
      $singleVariation.on('show_variation', function (event, variation) {
        restoreSliderThumbs($thumbnailSliderEl);
        // If selected variation has its own images gallery, only show those
        // and hide any other thumbnails.
        const productGallery = product_variation_images.gallery;
        const selectedVariationImages = product_variation_images[variation.variation_id];
        if (selectedVariationImages.length > 1) {
          $thumbnailSliderEl.find('img').each(function () {
            const $this = $(this);
            const thumb = $this.attr('src');
            // Hide the image if its not in the selected variation gallery.
            if (!selectedVariationImages.includes(thumb)) {
              $this.parent().hide();
              $thumbnailSliderEl.hiddenSlides++;
            }
          });
          if ($thumbnailSliderEl.hiddenSlides) {
            $thumbnailSliderEl.flickity('reposition');
          }
        }
        // Get the index of the variation thumbnail in the thumbnail slider.
        const thumbnailIndex = $thumbnailSliderEl
          .find('img[src="' + variation.image.gallery_thumbnail_src + '"]')
          .parent()
          .index();
        // Select the variation thumbnail in the thumbnail slider.
        $thumbnailSliderEl.flickity('selectCell', thumbnailIndex);
      });

      // Reacts to product variation selection reset.
      $singleVariation.on('hide_variation', function (event) {
        if ($thumbnailSliderEl.hiddenSlides) {
          restoreSliderThumbs($thumbnailSliderEl);
          $thumbnailSliderEl.hiddenSlides = 0;
        }
      });
    }
    else {
      // Flickity is not active.
      setProductGalleryVideoSlide();
      setProductGalleryVideoThumbnail();
      $('.single-product-summary .flex-control-nav li').on('click', function () {
        const $this = $(this);
        if ($this.hasClass('slider-thumb-video')) {
          setVideoPlayerUrl($this.index());
        }
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
    $('.variations_form').on('woocommerce_variation_has_changed', function () {
      const firstImage = $('.woocommerce-product-gallery__image img').eq(0);
      if (firstImage.length) {
        firstImage.parents('[data-srcset]').attr('data-srcset', firstImage.attr('srcset'));
      }
    });

    /**
     * Swaps first and second slide of product gallery, if first is a video.
     */
    function setProductGalleryVideoSlide() {
      // Video should be the second element in the product gallery.
      const $galleryWraper = $('.woocommerce-product-gallery .woocommerce-product-gallery__wrapper');
      const $galleryElements = $galleryWraper.children('div');
      if ($galleryElements.length <= 1) {
        return;
      }
      const $firstGalleryElement = $galleryElements.first();
      if ($firstGalleryElement.hasClass('has-video')) {
        // Set video as second slide.
        $firstGalleryElement
          .detach()
          .insertAfter($galleryWraper.children('div').first());
      }
    }

    /**
     * Swaps first and second thumbnail of product gallery, if first is a video.
     */
    function setProductGalleryVideoThumbnail() {
      const $videoContent = $('.woocommerce-product-gallery .gallerya__video-content').first();
      // If there's no video content, exit.
      if ($videoContent.length < 1) {
        return;
      }
      const videoThumbSrc = $videoContent.data('video-thumb');
      const $sliderThumbs = $('.flex-control-nav li img');
      // Video thumb should be the second element in the slider,
      // unless there's only one slide.
      const videoThumbPos = $sliderThumbs.length > 1 ? 1 : 0;
      const videoThumb = $sliderThumbs.eq(videoThumbPos);
      // Avoid switching thumbnails if second thumb is already the video.
      if (videoThumb.attr('src') !== videoThumbSrc) {
        $sliderThumbs.first().attr('src', videoThumb.attr('src'));
        videoThumb
          .attr('src', videoThumbSrc)
          // Ensure lazy loaded image is the video thumbnail.
          .attr('data-lazy-src', videoThumbSrc)
          .parent().addClass('slider-thumb-video');
      }
    }

    /**
     * Sets the URL of the video slide referred by a video thumbnail.
     *
     * Initially we don't set video URL in the video slide iframe to avoid
     * loading it if unless it is displayed.
     * If the thumbnail clicked refers to a video slide, then we assign
     * the video URL to the player iframe.
     *
     * @param {int} slideIndex
     *   Index of the thumbnail and the slide in the product gallery.
     */
    function setVideoPlayerUrl(slideIndex) {
      const slide = $('.woocommerce-product-gallery .woocommerce-product-gallery__image').eq(slideIndex);
      if (!$(slide).hasClass('has-video')) {
        return;
      }
      const $videoPlayer = $(slide).find('iframe').first();
      if (!$videoPlayer.attr('src')) {
        $videoPlayer.attr('src', $(slide).data('video-url'));
      }
    }

    /**
     * Restores hidden slides in the given flickity slider.
     *
     * @param {object} $slider
     *   jQuery object for the flickity slider.
     */
    function restoreSliderThumbs($slider) {
      if ($slider.hiddenSlides) {
        $slider.find('li').show();
        $slider.flickity('reposition');
        $slider.flickity('selectCell', 0);
        $slider.hiddenSlides = 0;
      }
    }

  });
})(jQuery);
