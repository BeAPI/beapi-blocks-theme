<?php

namespace BeAPI\Theme\Framework\Services;

use BeAPI\Theme\Framework\Framework;
use BeAPI\Theme\Framework\Service;
use BeAPI\Theme\Framework\Service_Container;
use BeAPI\Theme\Framework\Tools\Assets as Assets_Tools;

class Editor implements Service {
	/**
	 * @var Assets_Tools $assets_tools
	 */
	private $assets_tools;

	/**
	 * @var Assets;
	 */
	private $assets;

	/**
	 * @param Service_Container $container
	 */
	public function register( Service_Container $container ): void {
		$this->assets_tools = new Assets_Tools();
		$this->assets       = Framework::get_container()->get_service( 'assets' );
	}

	/**
	 * @return string
	 */
	public function get_service_name(): string {
		return 'editor';
	}

	/**
	 * @param Service_Container $container
	 */
	public function boot( Service_Container $container ): void {
		$this->after_theme_setup();
		/**
		 * Load editor style css for admin and frontend
		 */
		$this->style();

		/**
		 * Register custom block style
		 */
		$this->register_custom_block_styles();

		/**
		 * Load editor JS for ADMIN
		 */
		add_action( 'enqueue_block_editor_assets', [ $this, 'admin_editor_script' ] );

		/**
		 * Register custom images sizes
		 */
		add_filter( 'image_size_names_choose', [ $this, 'gutenberg_images_sizes' ] );

		/**
		 * White list of gutenberg blocks
		 */
		//add_filter( 'allowed_block_types_all', [ $this, 'gutenberg_blocks_allowed' ], 10, 2 );
	}

	/**
	 * Register :
	 *  - theme_supports
	 *  - color palettes
	 *  - font sizes
	 *  - etc.
	 *
	 */
	private function after_theme_setup(): void {
	}

	/**
	 * editor style
	 */
	private function style(): void {
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

	private function register_custom_block_styles() {
		// Buttons
		register_block_style(
			'core/button',
			[
				'name'  => 'border',
				'label' => __( 'Border', 'beapi-blocks-theme' ),
			]
		);
		register_block_style(
			'core/button',
			[
				'name'  => 'download',
				'label' => __( 'Download', 'beapi-blocks-theme' ),
			]
		);

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
		if ( ! is_array( $allowed_blocks ) ) {
			return [];
		}

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
