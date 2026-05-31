import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'NExT Related Posts Query', () => {
	test( 'プラグインが有効化されている', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'plugins.php' );
		const pluginRow = page.locator( 'tr[data-slug="next-related-posts-query"]' );
		await expect( pluginRow ).toHaveClass( /active/ );
	} );
} );
