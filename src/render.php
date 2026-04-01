<?php
/**
 * Server-side rendering of the NExT Related Posts Query block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks markup.
 * @var WP_Block $block      Block instance.
 *
 * @package NextRelatedPostsQuery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_post_id = get_the_ID();

if ( ! $current_post_id ) {
	return;
}

$number_of_posts    = isset( $attributes['numberOfPosts'] ) ? min( 12, absint( $attributes['numberOfPosts'] ) ) : 3;
$post_type_attr     = isset( $attributes['postType'] ) ? sanitize_key( $attributes['postType'] ) : '';
$allowed_taxonomies = isset( $attributes['taxonomies'] ) && is_array( $attributes['taxonomies'] )
	? array_map( 'sanitize_key', $attributes['taxonomies'] )
	: array();

$current_post = get_post( $current_post_id );

if ( ! $current_post ) {
	return;
}

// Use the selected post type, or fall back to the current post's type.
$query_post_type = ! empty( $post_type_attr ) && post_type_exists( $post_type_attr )
	? $post_type_attr
	: $current_post->post_type;

// next_related_posts_query_build_tax_query() is defined in next-related-posts-query.php.
$tax_query = next_related_posts_query_build_tax_query( $current_post_id, $allowed_taxonomies );

if ( empty( $tax_query ) ) {
	return;
}

$query_args = array(
	'post_type'      => $query_post_type,
	'post_status'    => 'publish',
	'posts_per_page' => $number_of_posts,
	'post__not_in'   => array( $current_post_id ),
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	'tax_query'      => $tax_query,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

$related_query = new WP_Query( $query_args );

if ( ! $related_query->have_posts() ) {
	wp_reset_postdata();
	return;
}

// Collect related post IDs.
$related_post_ids = array();
while ( $related_query->have_posts() ) {
	$related_query->the_post();
	$related_post_ids[] = get_the_ID();
}
wp_reset_postdata();

// Find the next/post-template inner block.
$post_template_block = null;
foreach ( $block->inner_blocks as $inner_block ) {
	if ( 'next/post-template' === $inner_block->name ) {
		$post_template_block = $inner_block;
		break;
	}
}

if ( ! $post_template_block ) {
	return;
}

// Render post-template once, passing all related post IDs via context.
// The post-template render.php handles the loop and the grid/flex container.
$inner_output = ( new WP_Block(
	$post_template_block->parsed_block,
	array( 'next/relatedPostIds' => $related_post_ids )
) )->render();

$wrapper_attributes = get_block_wrapper_attributes();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $wrapper_attributes is escaped by get_block_wrapper_attributes(); $inner_output is trusted WP_Block::render() output.
echo '<div ' . $wrapper_attributes . '>' . $inner_output . '</div>';
