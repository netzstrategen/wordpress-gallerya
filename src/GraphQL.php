<?php

/**
 * @file
 * Contains \Netzstrategen\Gallerya\GraphQL.
 */

namespace Netzstrategen\Gallerya;

use WPGraphQL\Data\Connection\PostObjectConnectionResolver;

/**
 * GraphQL integration.
 */
class GraphQL {

  /**
   * @implements graphql_register_types
   */
  public static function graphql_register_types() {
    register_graphql_connection(
      [
        'fromType' => 'ProductVariation',
        'toType' => 'MediaItem',
        'fromFieldName' => 'galleryImages',
        'resolve' => function ($source, array $args, $context, $info) {
          $variation_gallery_image_ids = Woocommerce::getVariationGalleryImages($source->databaseId);
          if (empty($variation_gallery_image_ids)) {
            return ['nodes' => []];
          }
          $resolver = new PostObjectConnectionResolver($source, $args, $context, $info, 'attachment');
          $resolver->set_query_arg('post_type', 'attachment');
          $resolver->set_query_arg('post__in', $variation_gallery_image_ids);

          // Change default ordering.
          if (isset($resolver->get_query_args()['orderby'])) {
            $resolver->set_query_arg('orderby', 'post__in');
          }

          return $resolver->get_connection();
        },
      ]
    );
  }

}
