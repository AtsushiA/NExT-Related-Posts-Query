import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'NExT Related Posts Query', () => {
	test( 'プラグインが有効化されている', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'plugins.php' );
		// data-slug は WordPress がプラグイン名を sanitize_title() した値
		const pluginRow = page.locator( 'tr[data-slug="next-related-posts-query-block"]' );
		await expect( pluginRow ).toHaveClass( /active/ );
	} );
} );
