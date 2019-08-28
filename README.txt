=== Gallerya ===
Contributors: netzstrategen, fabianmarz, juanlopez4691, lucapipolo, tha_sun
Tags: gallery, galleries, image, images, photo, album, responsive, responsive gallery, image gallery, photo gallery, carousel, image carousel, slider, image slider, slideshow, lightbox, fullscreen, zoom, media, foto, fotos, thumbnail, thumbnails, video, video gallery, lightgallery, flickity, jquery
Requires at least: 4.5
Tested up to: 4.9.8
Stable tag: 2.0.3
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

= 2.0.3 =
2019-08-28

Fixed incomplete release tarball.

= 2.0.2 =
2019-08-12

Fixed images appear larger than their original size in WooCommerce product gallery.

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
