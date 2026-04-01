<?php
/**
 * Plugin Name:       NExT Related Posts Query Block
 * Description:       Displays related posts that share the same categories and taxonomies as the current post. Works like a Query Loop with inner blocks.
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Author:            NExT-Season, WordPress Telex
 * Author URI:        https://next-season.net
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       next-related-posts-query
 *
 * @package NextRelatedPostsQuery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 */
if ( ! function_exists( 'next_related_posts_query_block_init' ) ) {
	function next_related_posts_query_block_init() {
		register_block_type( __DIR__ . '/build/' );
		register_block_type( __DIR__ . '/build/post-template/' );
	}
}
add_action( 'init', 'next_related_posts_query_block_init' );

/**
 * REST API endpoint for fetching related posts in the editor.
 */
if ( ! function_exists( 'next_related_posts_query_register_rest_route' ) ) {
	function next_related_posts_query_register_rest_route() {
		register_rest_route( 'next-related-posts-query/v1', '/related/(?P<post_id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => 'next_related_posts_query_rest_callback',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'args'                => array(
				'post_id'    => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
				'per_page'   => array(
					'default'           => 3,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param ) && (int) $param > 0 && (int) $param <= 12;
					},
				),
				'post_type'  => array(
					'default'           => '',
					'sanitize_callback' => 'sanitize_key',
				),
				'taxonomies' => array(
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );
	}
}
add_action( 'rest_api_init', 'next_related_posts_query_register_rest_route' );

if ( ! function_exists( 'next_related_posts_query_rest_callback' ) ) {
	function next_related_posts_query_rest_callback( $request ) {
		$post_id          = (int) $request->get_param( 'post_id' );
		$per_page         = (int) $request->get_param( 'per_page' );
		$post_type_param  = $request->get_param( 'post_type' );
		$taxonomies_param = $request->get_param( 'taxonomies' );
		$post             = get_post( $post_id );

		if ( ! $post ) {
			return new WP_REST_Response( array(), 200 );
		}

		// Determine post type to query.
		$query_post_type = ! empty( $post_type_param ) && post_type_exists( $post_type_param )
			? $post_type_param
			: $post->post_type;

		// Parse selected taxonomies.
		$allowed_taxonomies = array();
		if ( ! empty( $taxonomies_param ) ) {
			$allowed_taxonomies = array_map( 'sanitize_key', explode( ',', $taxonomies_param ) );
		}

		$tax_query = next_related_posts_query_build_tax_query( $post_id, $allowed_taxonomies );

		if ( empty( $tax_query ) ) {
			return new WP_REST_Response( array(), 200 );
		}

		$args = array(
			'post_type'      => $query_post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'post__not_in'   => array( $post_id ),
			'tax_query'      => $tax_query,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query = new WP_Query( $args );
		$posts = array();

		foreach ( $query->posts as $related_post ) {
			$thumbnail = get_the_post_thumbnail_url( $related_post->ID, 'medium' );
			$posts[]   = array(
				'id'        => $related_post->ID,
				'title'     => get_the_title( $related_post->ID ),
				'excerpt'   => wp_trim_words( get_the_excerpt( $related_post ), 20 ),
				'date'      => get_the_date( '', $related_post ),
				'link'      => get_permalink( $related_post->ID ),
				'thumbnail' => $thumbnail ? $thumbnail : '',
			);
		}

		return new WP_REST_Response( $posts, 200 );
	}
}

if ( ! function_exists( 'next_related_posts_query_build_tax_query' ) ) {
	/**
	 * Builds a tax_query array for finding related posts.
	 *
	 * @param int   $post_id            The source post ID.
	 * @param array $allowed_taxonomies Optional. Limit to these taxonomy slugs. Empty = use all.
	 * @return array
	 */
	function next_related_posts_query_build_tax_query( $post_id, $allowed_taxonomies = array() ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return array();
		}

		$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
		$tax_query  = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! $taxonomy->public ) {
				continue;
			}

			// Filter to selected taxonomies if specified.
			if ( ! empty( $allowed_taxonomies ) && ! in_array( $taxonomy->name, $allowed_taxonomies, true ) ) {
				continue;
			}

			$terms = wp_get_post_terms( $post_id, $taxonomy->name, array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy->name,
					'field'    => 'term_id',
					'terms'    => $terms,
				);
			}
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'OR';
		}

		return $tax_query;
	}
}
