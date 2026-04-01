<?php
/**
 * Server-side rendering of the NExT Post Template block.
 * Acts as the grid/flex container for all related post items.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks markup (the per-post template).
 * @var WP_Block $block      Block instance.
 *
 * @package NextRelatedPostsQuery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$related_post_ids = isset( $block->context['next/relatedPostIds'] )
	? array_map( 'absint', (array) $block->context['next/relatedPostIds'] )
	: array();

if ( empty( $related_post_ids ) ) {
	return;
}

$layout       = isset( $attributes['layout'] ) ? $attributes['layout'] : array(
	'type'        => 'grid',
	'columnCount' => 3,
);
$layout_type  = isset( $layout['type'] ) ? $layout['type'] : 'grid';
$column_count = isset( $layout['columnCount'] ) ? max( 1, (int) $layout['columnCount'] ) : 3;

// Set display and column layout via inline style.
if ( 'grid' === $layout_type ) {
	$layout_style = sprintf(
		'display:grid;grid-template-columns:repeat(%d,minmax(0,1fr));',
		$column_count
	);
} else {
	$layout_style = 'display:flex;flex-direction:column;';
}

// Add gap as a CSS custom property so it can be overridden by unitone's --unitone--gap.


$block_gap = isset( $attributes['style']['spacing']['blockGap'] )
	? sanitize_text_field( $attributes['style']['spacing']['blockGap'] )
	: null;

if ( null !== $block_gap && '' !== $block_gap ) {
	// Convert preset reference format "var:preset|spacing|30" → "var(--wp--preset--spacing--30)".
	$gap_value     = preg_replace( '/^var:preset\|([^|]+)\|(.+)$/', 'var(--wp--preset--$1--$2)', $block_gap );
	$layout_style .= '--next-post-template--gap:' . esc_attr( $gap_value ) . ';';
}

$is_link = ! empty( $attributes['isLink'] );

$wrapper_attributes = get_block_wrapper_attributes( array( 'style' => $layout_style ) );

$output = '';
foreach ( $related_post_ids as $related_id ) {
	$related_post = get_post( $related_id );
	if ( ! $related_post ) {
		continue;
	}

	// Set up post data so inner blocks (post-title, post-date, etc.) work correctly.
	$GLOBALS['post'] = $related_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $related_post );

	$item_output = '';
	foreach ( $block->inner_blocks as $inner_block ) {
		$item_output .= ( new WP_Block(
			$inner_block->parsed_block,
			array(
				'postId'   => $related_id,
				'postType' => get_post_type( $related_id ),
			)
		) )->render();
	}

	if ( $is_link ) {
		// Wrap each post item in a relative-positioned container and inject an
		// absolutely-positioned overlay link so the entire card becomes clickable.
		$item_url   = esc_url( get_permalink( $related_id ) );
		$item_label = esc_attr( get_the_title( $related_id ) );
		$output    .= '<div class="wp-block-next-post-template__item">';
		$output    .= '<a class="wp-block-next-post-template__item-link" href="' . $item_url . '" aria-label="' . $item_label . '" tabindex="-1" aria-hidden="true"></a>';
		$output    .= $item_output;
		$output    .= '</div>';
	} else {
		$output .= $item_output;
	}
}

wp_reset_postdata();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is safely escaped; $output is trusted WP_Block::render() output with escaped URLs.
echo '<div ' . $wrapper_attributes . '>' . $output . '</div>';
