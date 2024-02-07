<?php

namespace BEA\Theme\Framework\Services\Theme;

use BEA\Theme\Framework\Service;
use Pimple\Container;

class ThemeService implements Service {

	public function register( Container $container ): void {
		$container[ Menu::class ]        = new Menu();
		$container[ Performance::class ] = new Performance();
		$container[ Theme::class ]       = new Theme();
	}

	public function boot( Container $container ): void {
		add_theme_support( 'menus' );
		$container[ Menu::class ]->register_menus();

		add_action( 'wp', [ $container[ Performance::class ], 'register_assets' ] );
		add_filter( 'max_srcset_image_width', [
			$container[ Performance::class ],
			'optimize_srcset_maximum_width'
		], 10, 2 );

		$container[ Theme::class ]->after_setup_theme();
	}
}