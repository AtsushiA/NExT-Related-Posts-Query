import { registerBlockType } from '@wordpress/blocks';
import { loop } from '@wordpress/icons';
import './style.scss';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';
import PostTemplateEdit from './post-template/edit';
import PostTemplateSave from './post-template/save';
import postTemplateMetadata from './post-template/block.json';

registerBlockType( metadata.name, {
	icon: loop,
	edit: Edit,
	save: Save,
} );

registerBlockType( postTemplateMetadata.name, {
	edit: PostTemplateEdit,
	save: PostTemplateSave,
} );
