<?php

namespace BEA\Theme\Framework\Services\Editor;

use BEA\Theme\Framework\Framework;
use BEA\Theme\Framework\Service_Container;
use BEA\Theme\Framework\Services\Assets\Assets;
use BEA\Theme\Framework\Tools\Assets as AssetsTools;

class Editor {

	private $assets;

	private $assets_tools;

	public function __construct( Assets $assets, AssetsTools $assets_tools ) {
		$this->assets       = $assets;
		$this->assets_tools = $assets_tools;
	}

	/**
	 * Register :
	 *  - theme_supports
	 *  - color palettes
	 *  - font sizes
	 *  - etc.
	 *
	 */
	public function after_theme_setup(): void {
	}

	/**
	 * editor style
	 */
	public function style(): void {
		$file = $this->assets->is_minified() ? $this->assets->get_min_file( 'editor.css' ) : 'editor.css';

		/**
		 * Do not enqueue a inexistant file on admin
		 */
		if ( ! is_file( get_theme_file_path( 'dist/' . $file ) ) ) {
			return;
		}

		add_editor_style( 'dist/' . $file );
	}

	/**
	 * Editor script
	 */
	public function admin_editor_script(): void {
		$file     = $this->assets->is_minified() ? $this->assets->get_min_file( 'editor.js' ) : 'editor.js';
		$filepath = 'dist/' . $file;

		if ( ! file_exists( get_theme_file_path( $filepath ) ) ) {
			return;
		}

		$asset_data = $this->assets->get_asset_data( $file );
		$this->assets_tools->register_script(
			'theme-admin-editor-script',
			$filepath,
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);
		$this->assets_tools->enqueue_script( 'theme-admin-editor-script' );
	}

	/**
	 * Register custom block styles
	 */

	public function register_custom_block_styles() {
		// Buttons
		//      register_block_style(
		//          'core/button',
		//          [
		//              'name'  => 'reverse',
		//              'label' => __( 'Reverse', 'beapi-blocks-theme' ),
		//          ]
		//      );

		// Paragraph

		register_block_style(
			'core/paragraph',
			[
				'name'  => 'small',
				'label' => __( 'Small', 'beapi-blocks-theme' ),
			]
		);

		register_block_style(
			'core/paragraph',
			[
				'name'  => 'large',
				'label' => __( 'Large', 'beapi-blocks-theme' ),
			]
		);

		register_block_style(
			'core/paragraph',
			[
				'name'  => 'huge',
				'label' => __( 'Huge', 'beapi-blocks-theme' ),
			]
		);
	}

	/**
	 * Allow some core Gutenberg blocks
	 *
	 * @param bool|array $allowed_blocks
	 * @param \WP_Block_Editor_Context $block_editor_context
	 *
	 * @return array
	 */
	public function gutenberg_blocks_allowed( $allowed_blocks, \WP_Block_Editor_Context $block_editor_context ): array {

		$excluded = [
			'core/more',
		];

		// return the difference between the allowed blocks and the excluded ones
		return ( array_diff( $allowed_blocks, $excluded ) );
	}

	/**
	 * Display custom images sizes in Editor
	 *
	 * @param array $sizes Existing imgages sizes
	 *
	 * @return array
	 */
	public function gutenberg_images_sizes( $sizes ): array {
		return array_merge(
			$sizes,
			[
				'large-square' => __( 'Large square', 'beapi-blocks-theme' ),
			]
		);
	}
}
