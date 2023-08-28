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
    register_graphql_enum_type('GalleryaVideoSourceEnum', [
      'values' => [
        'YOUTUBE' => [
          'value' => 'youtube'
        ],
        'VIMEO' => [
          'value' => 'vimeo'
        ],
      ],
    ]);

    register_graphql_object_type('GalleryaVideo', [
      'fields' => [
        'videoId' => [
          'type' => 'string',
        ],
        'videoSource' => [
          'type' => 'GalleryaVideoSourceEnum',
        ],
        'videoDisplay' => [
          'type' => 'boolean',
        ],
      ],
    ]);

    register_graphql_field('Product', 'galleryaVideo', [
      'type' => 'GalleryaVideo',
      'resolve' => fn ($source): array => [
        'videoId' => $source?->get_meta('_gallerya_video_id'),
        'videoSource' => $source?->get_meta('_gallerya_video_source'),
        'videoDisplay' => $source?->get_meta('_gallerya_video_display'),
      ],
    ]);

    register_graphql_connection(
      [
        'fromType' => 'ProductVariation',
        'toType' => 'MediaItem',
        'fromFieldName' => 'galleryImages',
        'resolve' => function ($source, array $args, $context, $info) {
          $variation_gallery_image_ids = WooCommerce::getVariationGalleryImages($source->databaseId);
          if (empty($variation_gallery_image_ids)) {
            return;
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
