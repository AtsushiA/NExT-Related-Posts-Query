import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';
import PostTemplateEdit from './post-template/edit';
import PostTemplateSave from './post-template/save';
import postTemplateMetadata from './post-template/block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save: Save,
} );

registerBlockType( postTemplateMetadata.name, {
	edit: PostTemplateEdit,
	save: PostTemplateSave,
} );
