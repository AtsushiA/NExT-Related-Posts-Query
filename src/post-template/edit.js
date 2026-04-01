import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { list, grid } from '@wordpress/icons';
import { useMemo } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const { layout = {} } = attributes;
	const layoutType  = layout.type        || 'grid';
	const columnCount = layout.columnCount || 3;

	const previewStyle = useMemo( () => {
		if ( layoutType === 'flex' ) {
			return {
				display: 'flex',
				flexDirection: 'column',
				gap: '1.5em',
			};
		}
		return {
			display: 'grid',
			gridTemplateColumns: `repeat(${ columnCount }, minmax(0, 1fr))`,
			gap: '1.5em',
		};
	}, [ layoutType, columnCount ] );

	const blockProps = useBlockProps( { style: previewStyle } );
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		templateLock: false,
	} );

	return (
		<>
			<BlockControls group="block">
				<ToolbarGroup>
					<ToolbarButton
						icon={ list }
						label={ __( 'List view', 'next-related-posts-query' ) }
						isPressed={ layoutType === 'flex' }
						onClick={ () =>
							setAttributes( {
								layout: {
									type: 'flex',
									orientation: 'vertical',
								},
							} )
						}
					/>
					<ToolbarButton
						icon={ grid }
						label={ __( 'Grid view', 'next-related-posts-query' ) }
						isPressed={ layoutType === 'grid' }
						onClick={ () =>
							setAttributes( {
								layout: {
									type: 'grid',
									columnCount,
								},
							} )
						}
					/>
				</ToolbarGroup>
			</BlockControls>

			{ layoutType === 'grid' && (
				<InspectorControls>
					<PanelBody
						title={ __( 'Layout', 'next-related-posts-query' ) }
					>
						<RangeControl
							label={ __(
								'Columns',
								'next-related-posts-query'
							) }
							value={ columnCount }
							onChange={ ( value ) =>
								setAttributes( {
									layout: {
										type: 'grid',
										columnCount: value,
									},
								} )
							}
							min={ 1 }
							max={ 4 }
						/>
					</PanelBody>
				</InspectorControls>
			) }

			<div { ...innerBlocksProps } />
		</>
	);
}
