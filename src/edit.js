import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	CheckboxControl,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useState, useEffect, useMemo, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

const TEMPLATE = [
	[
		'next/post-template',
		{},
		[
			[
				'core/group',
				{
					layout: { type: 'flex', orientation: 'vertical' },
				},
				[
					[ 'core/post-featured-image', { isLink: true } ],
					[ 'core/post-title', { level: 4, isLink: true } ],
					[ 'core/post-date' ],
					[ 'core/post-excerpt', { excerptLength: 20 } ],
				],
			],
		],
	],
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		numberOfPosts,
		postType,
		taxonomies,
		orderby,
		order,
	} = attributes;

	// Combined sort value used by the SelectControl: "orderby/order".
	const sortValue = `${ orderby }/${ order }`;
	const handleSortChange = ( value ) => {
		const [ newOrderby, newOrder ] = value.split( '/' );
		setAttributes( { orderby: newOrderby, order: newOrder } );
	};

	const [ relatedPosts, setRelatedPosts ] = useState( [] );
	const [ isLoading, setIsLoading ]       = useState( true );

	const postId = useSelect(
		( select ) => select( 'core/editor' )?.getCurrentPostId?.() || 0,
		[]
	);

	const currentPostType = useSelect(
		( select ) => select( 'core/editor' )?.getCurrentPost?.()?.type || 'post',
		[]
	);

	const postTypes = useSelect(
		( select ) => select( 'core' ).getPostTypes( { per_page: -1 } ),
		[]
	);

	const targetPostType = postType || currentPostType;

	const sourceTaxonomies = useSelect(
		( select ) =>
			select( 'core' ).getTaxonomies( {
				type: currentPostType,
				per_page: -1,
			} ),
		[ currentPostType ]
	);

	const targetTaxonomies = useSelect(
		( select ) =>
			select( 'core' ).getTaxonomies( {
				type: targetPostType,
				per_page: -1,
			} ),
		[ targetPostType ]
	);

	const availableTaxonomies = useMemo( () => {
		// When a specific post type is selected, show that type's taxonomies so the
		// user can choose which taxonomy to use for cross-type relationship matching.
		// When auto (same as current post type), show the current post's taxonomies.
		if ( ! postType || postType === currentPostType ) {
			return sourceTaxonomies || [];
		}
		return targetTaxonomies || [];
	}, [ postType, currentPostType, sourceTaxonomies, targetTaxonomies ] );

	const postTypeOptions = useMemo( () => {
		const options = [
			{
				label: __(
					'Auto (current post type)',
					'next-related-posts-query'
				),
				value: '',
			},
		];
		if ( postTypes ) {
			postTypes
				.filter(
					( type ) =>
						type.viewable !== false && type.slug !== 'attachment'
				)
				.forEach( ( type ) => {
					options.push( { label: type.name, value: type.slug } );
				} );
		}
		return options;
	}, [ postTypes ] );

	// Reset taxonomy selection when post type changes to avoid stale selections
	// from a different post type's taxonomy list.
	const prevPostTypeRef = useRef( postType );
	useEffect( () => {
		if ( prevPostTypeRef.current !== postType ) {
			prevPostTypeRef.current = postType;
			setAttributes( { taxonomies: [] } );
		}
	}, [ postType, setAttributes ] );

	const taxonomiesKey = JSON.stringify( taxonomies );

	useEffect( () => {
		if ( ! postId ) {
			setIsLoading( false );
			return;
		}

		setIsLoading( true );

		const params = new URLSearchParams( { per_page: numberOfPosts } );
		if ( postType ) params.set( 'post_type', postType );
		if ( taxonomies.length > 0 )
			params.set( 'taxonomies', taxonomies.join( ',' ) );
		params.set( 'orderby', orderby );
		params.set( 'order', order );

		apiFetch( {
			path: `/next-related-posts-query/v1/related/${ postId }?${ params.toString() }`,
		} )
			.then( ( posts ) => {
				setRelatedPosts( posts );
				setIsLoading( false );
			} )
			.catch( () => {
				setRelatedPosts( [] );
				setIsLoading( false );
			} );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ postId, numberOfPosts, postType, taxonomiesKey, orderby, order ] );

	const handleTaxonomyChange = ( slug, checked ) => {
		if ( checked ) {
			setAttributes( { taxonomies: [ ...taxonomies, slug ] } );
		} else {
			setAttributes( {
				taxonomies: taxonomies.filter( ( t ) => t !== slug ),
			} );
		}
	};

	const blockProps = useBlockProps();

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'wp-block-next-related-posts-query__template',
		},
		{
			template: TEMPLATE,
			templateLock: 'insert',
			allowedBlocks: [ 'next/post-template' ],
		}
	);

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __(
						'Query Settings',
						'next-related-posts-query'
					) }
				>
					<SelectControl
						label={ __(
							'Post Type',
							'next-related-posts-query'
						) }
						value={ postType }
						options={ postTypeOptions }
						onChange={ ( value ) =>
							setAttributes( { postType: value } )
						}
					/>
					{ availableTaxonomies.length > 0 && (
						<>
							<p
								style={ {
									marginBottom: '8px',
									fontWeight: 600,
								} }
							>
								{ __(
									'Taxonomies',
									'next-related-posts-query'
								) }
							</p>
							<p
								style={ {
									marginBottom: '8px',
									fontSize: '12px',
									color: '#757575',
								} }
							>
								{ __(
									'Leave all unchecked to use all taxonomies.',
									'next-related-posts-query'
								) }
							</p>
							{ availableTaxonomies.map( ( taxonomy ) => (
								<CheckboxControl
									key={ taxonomy.slug }
									label={ taxonomy.name }
									checked={ taxonomies.includes(
										taxonomy.slug
									) }
									onChange={ ( checked ) =>
										handleTaxonomyChange(
											taxonomy.slug,
											checked
										)
									}
								/>
							) ) }
						</>
					) }
				</PanelBody>
				<PanelBody
					title={ __(
						'Display Settings',
						'next-related-posts-query'
					) }
				>
					<RangeControl
						label={ __(
							'Number of posts',
							'next-related-posts-query'
						) }
						value={ numberOfPosts }
						onChange={ ( value ) =>
							setAttributes( { numberOfPosts: value } )
						}
						min={ 1 }
						max={ 12 }
					/>
					<SelectControl
						label={ __( '並び順', 'next-related-posts-query' ) }
						value={ sortValue }
						options={ [
							{
								label: __( '投稿順 (最新から)', 'next-related-posts-query' ),
								value: 'date/DESC',
							},
							{
								label: __( '投稿順 (過去から)', 'next-related-posts-query' ),
								value: 'date/ASC',
							},
							{
								label: __( 'A → Z', 'next-related-posts-query' ),
								value: 'title/ASC',
							},
							{
								label: __( 'Z → A', 'next-related-posts-query' ),
								value: 'title/DESC',
							},
						] }
						onChange={ handleSortChange }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ isLoading && (
					<Placeholder
						icon="admin-post"
						label={ __(
							'NExT Related Posts',
							'next-related-posts-query'
						) }
					>
						<Spinner />
					</Placeholder>
				) }
				{ ! isLoading && relatedPosts.length === 0 && (
					<Placeholder
						icon="admin-post"
						label={ __(
							'NExT Related Posts',
							'next-related-posts-query'
						) }
						instructions={ __(
							'No related posts found. Related posts will appear when this post shares categories or taxonomies with other published posts.',
							'next-related-posts-query'
						) }
					/>
				) }
				{ ! isLoading && relatedPosts.length > 0 && (
					<p className="wp-block-next-related-posts-query__info">
						{ __(
							'NExT Related Posts — Edit the template below. On the frontend each related post will repeat this layout.',
							'next-related-posts-query'
						) }
					</p>
				) }
				{ ! isLoading && <div { ...innerBlocksProps } /> }
			</div>
		</>
	);
}
