<?php


namespace BEA\Theme\Framework\Services;

use BEA\Theme\Framework\Service;
use BEA\Theme\Framework\Service_Container;

class Editor_Patterns implements Service {
	/**
	 * @param Service_Container $container
	 */
	public function register( Service_Container $container ): void {
	}

	/**
	 * @return string
	 */
	public function get_service_name(): string {
		return 'editor-patterns';
	}

	/**
	 * @param Service_Container $container
	 */
	public function boot( Service_Container $container ): void {
		\add_action( 'init', [ $this, 'register_categories' ], 10 );
		\add_action( 'init', [ $this, 'register_patterns' ], 11 );
	}

	/**
	 * Register the patterns categories
	 */
	public function register_categories(): void {

		/**
		 * usage : 'common' => [ 'label' => __( 'Common', 'beapi-frontend-framework' ) ]
		 */
		$pattern_categories = [
			'common' => [ 'label' => __( 'Common', 'beapi-frontend-framework' ) ],
		];

		foreach ( $pattern_categories as $name => $properties ) {
			if ( \WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
				continue;
			}
			register_block_pattern_category( $name, $properties );
		}
	}
}
