<?php

namespace BEA\Theme\Framework\Services\Theme;

/**
 * Class Menu
 *
 * @package BEA\Theme\Framework
 */
class Menu {

	public function register_menus(): void {
		$nav_menu = [
			'mega-menu' => __( 'Main menu', 'beapi-blocks-theme' ),
		];
		register_nav_menus( $nav_menu );
	}
}
