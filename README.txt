=== Gallerya ===
Contributors: netzstrategen, fabianmarz, juanlopez4691, lucapipolo, tha_sun, gyopiazza, Mauricio-Urrego, christianbaltazar, bogdanarizancu, geisthanen, nabiabdi
Tags: gallery, image gallery, video gallery, carousel, lightbox, slider, responsive gallery, image slider, lightgallery, flickity
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 3.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change the native post gallery to be displayed as a slider with lightbox support.

== Description ==

Gallerya transforms the WordPress native post gallery into a full fledged slideshow with features like images thumbnails stripe and lightbox support.

== Installation ==

1. Upload the entire gallerya folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

= Requirements =

* PHP 7.0 or later.

== Changelog ==

= 3.2.0 =
Adds GraphQL support for video field.

= 3.1.0 =
Adds compatibility with woo-product-gallery-slider.

= 3.0.0 =
Updated dev dependencies.
Fixed some issues.

= 2.9.2 && 2.9.3 =
2023-02-23

* Fix contributors Readme.txt.

= 2.9.1 =
2023-02-23

* Fix wp-graphql support incorrect return statament edges.

= 2.9.0 =
2022-09-14

* Added wp-graphql support for product variation additional gallery images.

= 2.8.6 =
2022-05-27

* Fixed product variation slider overriding images where srcset is not available.

= 2.8.5 =
2022-05-18

* Fixed fatal error in single product view.

= 2.8.4 =
2022-05-16

* Fixed compatibility with PHP 8.0.
 
= 2.8.3 =
2021-12-03

* Hidden gallery in the background when the light modal is open.

= 2.8.2 =
2021-11-24

* Fixed no thumbnail on initial video slide.

= 2.8.1 =
2021-11-16

* Added video support for lightbox.

= 2.8.0 =
2021-10-20

* Added link to variation in product teaser slider.

= 2.7.14 =
2021-05-20

* Fixed backend javascript error on advanced-bulk-editor plugin.

= 2.7.13 =
2021-04-28

* Fixed extraneous non-existing variation images in product teasers.

= 2.7.12 =
2021-04-08

* Fixed images groups in lightbox mode.

= 2.7.11 =
2020-12-04

* Fixed keyboard navigation sync between product slider and lightbox slider.

= 2.7.10 =
2020-11-25

* Fixed main product image was duplicated if also used in a variation.

= 2.7.9 =
2020-11-24

* Fixed default image slider thumbnail not lazy loaded.
* Updated dependencies with vulnerabilities.

= 2.7.8 =
2020-11-09

* Fixed JS errors and removed obsolete lightGallery assets.

= 2.7.7 =
2020-11-09

* Fixed first product image showing blank square when a video is added.

= 2.7.6 =
2020-10-01

* Fixed variation main image was shown after its additional images.
* Fixed hiding images unrelated to selected variation was breaking the gallery slider.
* Fixed synchronicity between thumbnails slider and gallery images.

= 2.7.5 =
2020-09-30

* Fixed images from inactive products variations were displayed in the gallery slider.

= 2.7.4 =
2020-09-28

* Added use image alt as lightbox caption, fallback to image title.
* Fixed product featured image was excluded from variation thumbnails slider on products listing pages.

= 2.7.3 =
2020-09-16

* Fixed PHP notice thrown when retrieving thumbnails of variable product with no variations.

= 2.7.2 =
2020-09-16

* Fixed wrong reference to click event for slideshow navigation buttons.
* Fixed empty slide was added to lightbox images set.

= 2.7.1 =
2020-09-04

* Fixed translations were not loaded.
* Removed debug code.

= 2.7.0 =
2020-09-04

* Activated social sharing from lightbox popup.
* Fixed position of lightbox thumbnails.
* Added fancybox library.
* Removed lightgallery.

= 2.6.1 =
2020-08-04

* Fixed product variations images were duplicated.

= 2.6.0 =
2020-07-30

* Fixed thumbnail slider prev/next arrows shown even when disabled because not enough thumbnails.

= 2.5.0 =
2020-06-22

* Updated thumbnails to select the product gallery image on mouseover.

= 2.4.0 =
2020-05-25

* Added additional images to product variations gallery.
* Fixed video preview image was horizontally cropped.
* Fixed product gallery video support compatibility with themes.
* Refactored JS scripts.

= 2.3.3 =
2020-05-19

* Fixed video thumbnail was not correctly added to the slider.
* Fixed styling of video thumbnail.
* Refactored product gallery thumbnails styles.
* Fixed video player was loaded even if video is not displayed.

= 2.3.2 =
2020-05-05

* Fixed PHP error when product object is not defined.

= 2.3.1 =
2020-05-04

* Fixed YouTube unrelated videos where displayed after video is played.

= 2.3.0 =
2020-04-30

* Added video support to product gallery and thumbnails slider.
* Added ES6 support.
* Refactored and recompiled scripts with ES6 support.

= 2.2.10 =
2020-04-28

* Fixed product variations could not be selected.

= 2.2.9 =
2020-04-08

* Fixed variations thumbnails were not unique on products listing pages.

= 2.2.8 =
2020-03-30

* Fixed variations thumbnails retrieval to improve performance on products listing pages.
* Bumped plugin version.

= 2.2.7 =
2020-03-24

* Fixed grid layout was overlapping preceding content on mobile.
* Fixed slider layout collapsing on mobile.

= 2.2.6 =
2020-03-18

* Fixed slider and grid slider layouts were broken.
* Fixed custom 'no-lazy' class was not preventing lazy loading.

= 2.2.5 =
2020-03-17

* Changed slider arrows of product variation sliders.

= 2.2.4 =
2020-03-10

* Fixed layout of WordPress image galleries was broken.

= 2.2.3 =
2020-03-09

* Fixed simple product thumbnails were not lazy loaded.

= 2.2.2 =
2020-03-04

* Fixed readme file.

= 2.2.1 =
2020-03-04

* Added lazy loading to product variation sliders on products listing pages.
* Updated library flickity to 2.1.0 to add lazyLoad support.

= 2.2.0 =
2020-02-13

* Added product variation thumbnails slider in product listing pages.

= 2.1.1 =
2020-02-12

* Fixed 'tested up to' WordPress version in readme file.

= 2.1.0 =
2020-02-06

* Added product thumbnails slider for WooCommerce.

= 2.0.3 =
2019-08-28

* Fixed incomplete release tarball.

= 2.0.2 =
2019-08-12

* Fixed images appear larger than their original size in WooCommerce product gallery.

= 2.0.1 =
2019-08-06

* Fixed lightGallery displays wrong product image after changing variation.

= 2.0.0 =
2019-03-08

* Removed aggregation of third party frontend libraries.

= 1.10.2 =
2019-01-25

* Fixed missing lightGallery image assets.
* Added pull request template.

= 1.10.1 =
2018-10-16

* Added .wp-release.conf file.
* Added able to toggle slider thumbnails, dots, count.
* Fixed images are not visible on mobile.

= 1.9.15 =
2018-10-05

* Added basic readme file.
* Ensures plugin bj-lazy-load skips images with class 'no-lazy'.
* Fixed wrong query run when gallerya is used as a widget and a title is added to it.
* Fixed gallery slider had wrong height if bj-lazy-load plugin was enabled.
* Fixed other images than products appear in lightgallery.
* Fixed too large image files loaded in WooCommerce product gallery (not responsive).
* Fixed wrong resources folder name on Bower file.
* Images attributes incorrectly applied in slider templates.
* Fixed wrong slider image height with plugin wp-rocket lazyload option enabled.
* Fixed lg-thumbnail and lg-zoom lightbox plugins were wrongly overridden in bower file.
* Fixed wrong lightbox selector for woocommerce 3.x on multiple product images.
* Updated for plugin woocommerce major version 3.x. (product gallery lightbox support)
* Fixed original upload being used as slider image.
* Updated library flickity to 2.0.9 for compatibility with WordPress 4.9.8.
* Fixed margins of slider image due to third-party plugin overrides.
* Fixed underscore is not always defined.
* Fixed grid slider layout on Safari.
* Fixed images was not ordered following the include parameter order.
* Added filter for showing thumbnail navigation.
* Fixed prev-next button was too big.
* Fixed wrong lightGallery captions.
* Fixed some minor styling issues.
* Fixed thumbnail images are stretched in Safari.
* Updated flickity to 2.0.5.
* Removed slider caption on grid slider layout.
* Fixed added max-height inheritance to keep layout.
* Fixed reset padding to prevent distortion.
* Fixed layout for landscape and portrait images.
* Changed the slider navigation thumbnail filter name.
* Added zoom functionality.
* Changed register scripts in footer and added deps.
* Changed register scripts in footer and added deps.
* Added grid slider layout.
* Changed function order to match hook order.
* Fixed settings are not loaded if frontend page builders are used.
* Added correct layout for nav arrows on mobile.
* Fixed output caption only if available.
* Added filter for image captions.
* Added highlight current selected slide.
* Updated plugin gallerya to 1.3.0.
* Added plugin gallerya 1.2.2.
