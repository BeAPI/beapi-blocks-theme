<?php

namespace BEA\Theme\Framework\Services\Editor;

use BEA\Theme\Framework\Services\Assets\Assets;
use BEA\Theme\Framework\Tools\Assets as AssetsTools;
use BEA\Theme\Framework\Service;
use Pimple\Container;

class EditorService implements Service {

	public function register( Container $container ): void {
		$container[ Editor::class ]         = new Editor( $container[ Assets::class ], $container[ AssetsTools::class ] );
		$container[ EditorPatterns::class ] = new EditorPatterns();
	}

	public function boot( Container $container ): void {
		$container[ Editor::class ]->after_theme_setup();
		$container[ Editor::class ]->style();
		$container[ Editor::class ]->register_custom_block_styles();
		add_action( 'enqueue_block_editor_assets', [ $container[ Editor::class ], 'admin_editor_script' ] );
		add_filter( 'image_size_names_choose', [ $container[ Editor::class ], 'gutenberg_images_sizes' ] );
		add_filter( 'allowed_block_types_all', [ $container[ Editor::class ], 'gutenberg_blocks_allowed' ], 10, 2 );

		\add_action( 'init', [ $container[ EditorPatterns::class ], 'register_categories' ], 10 );
		//\add_action( 'init', [ $container[ EditorPatterns::class ], 'register_patterns' ], 11 );
	}
}