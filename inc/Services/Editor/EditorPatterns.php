<?php

namespace BEA\Theme\Framework\Services\Editor;

use BEA\Theme\Framework\Service_Container;

class EditorPatterns {

	/**
	 * Register the patterns categories
	 */
	public function register_categories(): void {

		/**
		 * usage : 'common' => [ 'label' => __( 'Common', 'beapi-blocks-theme' ) ]
		 */
		$pattern_categories = [
			'common' => [ 'label' => __( 'Common', 'beapi-blocks-theme' ) ],
		];

		foreach ( $pattern_categories as $name => $properties ) {
			if ( \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
				continue;
			}
			register_block_pattern_category( $name, $properties );
		}
	}
}
