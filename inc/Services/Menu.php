<?php

namespace BeAPI\Theme\Framework\Services;

use BeAPI\Theme\Framework\Service;
use BeAPI\Theme\Framework\Service_Container;

/**
 * Class Menu
 *
 * @package BeAPI\Theme\Framework
 */
class Menu implements Service {
	/**
	 * @param Service_Container $container
	 */
	public function register( Service_Container $container ): void {}

	/**
	 * @param Service_Container $container
	 */
	public function boot( Service_Container $container ): void {
		add_theme_support( 'menus' );

		$this->register_menus();
	}

	/**
	 * @return string
	 */
	public function get_service_name(): string {
		return 'menu';
	}

	public function register_menus(): void {
		$nav_menu = [
			'mega-menu' => __( 'Main menu', 'beapi-blocks-theme' ),
		];
		register_nav_menus( $nav_menu );
	}
}
