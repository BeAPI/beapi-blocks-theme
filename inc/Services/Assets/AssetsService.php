<?php

namespace BEA\Theme\Framework\Services\Assets;

use BEA\Theme\Framework\Tools\Assets as AssetsTools;
use BEA\Theme\Framework\Service;
use Pimple\Container;

class AssetsService implements Service {

	public function register( Container $container ): void {
		$container[ AssetsTools::class ]   = new AssetsTools();
		$container[ Assets::class ]        = new Assets( $container[ AssetsTools::class ] );
		$container[ AssetsJsAsync::class ] = new AssetsJsAsync();
	}

	public function boot( Container $container ): void {
		add_action( 'wp', [ $container[ Assets::class ], 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $container[ Assets::class ], 'enqueue_scripts' ] );
		add_action( 'wp_print_styles', [ $container[ Assets::class ], 'enqueue_styles' ] );
		add_filter( 'stylesheet_uri', [ $container[ Assets::class ], 'stylesheet_uri' ] );
		add_filter( 'wp_login_page_theme_css', [ $container[ Assets::class ], 'login_stylesheet_uri' ] );

		if ( current_theme_supports( 'async-js' ) && ! is_admin() ) {
			add_filter( 'script_loader_tag', [ $container[ AssetsJsAsync::class ], 'script_loader_tag' ], 20, 2 );
		}
	}
}