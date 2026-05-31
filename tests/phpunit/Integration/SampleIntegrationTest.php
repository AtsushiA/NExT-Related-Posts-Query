<?php

namespace NextRelatedPostsQuery\Tests\Integration;

use WP_UnitTestCase;

class SampleIntegrationTest extends WP_UnitTestCase {
	public function test_blocks_are_registered(): void {
		$registry = \WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'next/related-posts-query' ) );
		$this->assertTrue( $registry->is_registered( 'next/post-template' ) );
	}
}
