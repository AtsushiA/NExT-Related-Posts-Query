<?php

namespace NextRelatedPostsQuery\Tests\Integration;

use WP_UnitTestCase;

class SampleIntegrationTest extends WP_UnitTestCase {
	public function test_plugin_is_active(): void {
		$this->assertTrue( is_plugin_active( 'NExT-Related-Posts-Query/next-related-posts-query.php' ) );
	}
}
