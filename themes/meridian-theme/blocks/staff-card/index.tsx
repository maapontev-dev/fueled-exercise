import { registerBlockType } from '@wordpress/blocks';
import type { BlockConfiguration } from '@wordpress/blocks';

import { BlockEdit } from './edit';
import blockMetadata from './block.json';

const metadata = blockMetadata as BlockConfiguration<{ postId: number }>;

registerBlockType(metadata, {
	edit: BlockEdit,
	save: () => null,
});
