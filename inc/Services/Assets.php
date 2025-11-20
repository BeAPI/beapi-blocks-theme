<?php

namespace BEA\Theme\Framework\Services;

use BEA\Theme\Framework\Service;
use BEA\Theme\Framework\Service_Container;
use BEA\Theme\Framework\Tools\Assets as Assets_Tools;

/**
 * Class Assets
 *
 * @package BEA\Theme\Framework
 */
class Assets implements Service {

	public const BUILD_DIR = '/dist';

	/**
	 * @var Assets_Tools
	 */
	private $assets_tools;

	/**
	 * @var array
	 */
	public $partial_assets = [
		// css array will contain [ 'wp-block-button' => [ 'path_from_theme_root' => 'dist/wp-block/button.css', 'version' => null ] ]
		// version is null because hash is already in file name
		'css' => [],
		// js array will contain [ 'wp-block-button' => [ 'path_from_theme_root' => 'dist/wp-block/button.js', 'version' => '360bb6f45cb7dbc6a187' ] ]
		// version is extracted from block-name.asset.php file
		'js'  => [],
	];

	/**
	 * @param Service_Container $container
	 */
	public function register( Service_Container $container ): void {
		$this->assets_tools = new Assets_Tools();
	}

	/**
	 * @param Service_Container $container
	 */
	public function boot( Service_Container $container ): void {
		/**
		 * Fill partial assets array
		 */
		$this->fill_partial_assets_array();

		/**
		 * Add hooks for the scripts and styles to hook on
		 */
		add_action( 'wp', [ $this, 'register_assets' ] );
		add_action( 'init', [ $this, 'register_partial_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'add_theme_capabilities_script' ] );
		add_filter( 'stylesheet_uri', [ $this, 'stylesheet_uri' ] );
		add_filter( 'wp_login_page_theme_css', [ $this, 'login_stylesheet_uri' ] );
		add_filter( 'block_type_metadata', [ $this, 'add_block_assets_to_metadata' ], 1000, 1 );
		add_filter( 'render_block', [ $this, 'enqueue_pattern_assets' ], 1000, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_template_assets' ], 1000, 1 );
	}

	/**
	 * @return string
	 */
	public function get_service_name(): string {
		return 'assets';
	}

	/**
	 * Register all the Theme assets
	 */
	public function register_assets(): void {
		if ( is_admin() ) {
			return;
		}

		// Js
		$asset = $this->get_asset_file( 'app', 'js' );
		if ( $asset ) {
			$this->assets_tools->register_script(
				'scripts',
				$asset['file'],
				array_merge( [ 'jquery' ], $asset['dependencies'] ), // ensure jQuery dependency is set even if not declared explicitly in the JS
				$asset['version'],
				[ 'strategy' => 'defer' ]
			);

			wp_add_inline_script(
				'scripts',
				'window.beapi = ' . wp_json_encode(
					[
						'theme' => [
							'templateDirectoryUri' => get_template_directory_uri(),
						],
					]
				) . ';',
				'before'
			);
		}

		// Do not add a versioning query param in assets URLs if minified
		$style = $this->get_asset_file( 'app', 'css' );
		if ( $style ) {
			$version = $this->is_dev() ? filemtime( get_theme_file_path( $style['file'] ) ) : null;
			wp_register_style( 'theme-style', get_stylesheet_uri(), [], $version );
		}
	}

	/**
	 * Enqueue the scripts
	 */
	public function enqueue_scripts(): void {
		// JS
		$this->assets_tools->enqueue_script( 'scripts' );
	}

	/**
	 * Enqueue the styles
	 */
	public function enqueue_styles(): void {
		// CSS
		$this->assets_tools->enqueue_style( 'theme-style' );
	}

	/**
	 * The stylesheet uri based on the dev or not constant
	 *
	 * @param string $stylesheet_uri
	 *
	 * @return string
	 * @author Nicolas Juen
	 */
	public function stylesheet_uri( string $stylesheet_uri ): string {
		$asset = $this->get_asset_file( 'app', 'css' );

		return $asset ? \get_theme_file_uri( $asset['file'] ) : $stylesheet_uri;
	}

	/**
	 * Get asset file data.
	 *
	 * @param string $name the name of the asset.
	 * @param string $type the type of the asset. Accept 'css' or 'js'
	 *
	 * @return array{file: string, version: ?string, dependencies: array}|false asset's data or false if the file is not readable.
	 */
	public function get_asset_file( string $name, string $type ) {
		static $assets = [];

		// Check cache if the asset has already been loaded.
		$key = $name . ':' . $type;
		if ( isset( $assets[ $key ] ) ) {
			return $assets[ $key ];
		}

		// Default asset data array
		$asset_file = [
			'file'         => '',
			'version'      => null,
			'dependencies' => [],
		];

		$path = match ( $type ) {
			'js' => $this->get_assets_manifest()['js'][ $name ] ?? '',
			'css' => $this->get_assets_manifest()['css'][ $name ] ?? '',
			default => ''
		};

		if ( empty( $path ) || ! is_readable( get_theme_file_path( self::BUILD_DIR . '/' . $path ) ) ) {
			$assets[ $key ] = false;

			return $assets[ $key ];
		}

		$asset_file['file']    = self::BUILD_DIR . '/' . $path;
		$asset_file['version'] = $this->is_dev() ? filemtime( get_theme_file_path( $asset_file['file'] ) ) : null;

		if ( 'js' === $type ) {
			$asset_data_file = str_replace( '.js', '.asset.php', $path );
			$asset_data_path = get_theme_file_path( self::BUILD_DIR . '/' . $asset_data_file );
			if ( is_readable( $asset_data_path ) ) {
				$asset_data                 = require $asset_data_path;
				$asset_file['version']      = $asset_data['version'];
				$asset_file['dependencies'] = $asset_data['dependencies'];
			}
		}

		$assets[ $key ] = $asset_file;

		return $asset_file;
	}

	/**
	 * Check if we are on minified environment.
	 *
	 * @return bool
	 * @author Nicolas JUEN
	 */
	public function is_dev(): bool {
		return wp_is_development_mode( 'all' );
	}

	/**
	 * Change login CSS URL
	 *
	 * @return string
	 */
	public function login_stylesheet_uri(): string {
		$asset = $this->get_asset_file( 'login', 'css' );

		return $asset ? $asset['file'] : '';
	}

	/**
	 * Add theme capabilities script
	 *
	 * @return void
	 */
	public function add_theme_capabilities_script(): void {
		wp_print_inline_script_tag( "(function() {const html=document.documentElement;html.classList.add('js');if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches && !window.location.hash.includes('no-js-animation')){html.classList.add('js-animation');}})();" );
	}

	/**
	 * Fill partials assets array with the css and js files contained in
	 * the dist/wp-block, dist/wp-pattern and dist/template folders.
	 *
	 * @return void
	 */
	public function fill_partial_assets_array(): void {
		$partial_assets_prefix_mapping = [
			'wp-block'   => 'wp-block-',
			'wp-pattern' => 'wp-pattern-',
			'template'   => '',
		];

		$supported_partial_assets = array_keys( $partial_assets_prefix_mapping );

		foreach ( $this->get_assets_manifest() as $asset_type => $assets ) {
			foreach ( $assets as $asset_name => $asset_file ) {
				// Ignore assets whose name doesn't contain a `/`
				// ex: wp-block/button -> Ok
				// ex: app -> Ko
				if ( ! str_contains( $asset_name, '/' ) ) {
					continue;
				}

				// Split the asset name on the `/` and check if the prefix is supported
				// ex: wp-block/button -> Prefix: wp-block, Basename: button
				[ $asset_prefix, $asset_basename ] = explode( '/', $asset_name, 2 );
				if ( ! in_array( $asset_prefix, $supported_partial_assets, true ) ) {
					continue;
				}

				$asset = $this->get_asset_file( $asset_name, $asset_type );
				if ( ! $asset ) {
					continue;
				}

				// Build class name key
				// ex: button -> wp-block-button
				// ex: hidden-share -> wp-pattern-hidden-share
				// ex: home -> home
				$class_name = ( $partial_assets_prefix_mapping[ $asset_prefix ] ?? '' ) . str_replace( '/', '-', $asset_basename );

				$this->partial_assets[ $asset_type ][ $class_name ] = [
					'path_from_theme_root' => $asset['file'],
					'version'              => $asset['version'],
				];
			}
		}
	}

	/**
	 * Register all existing partial assets.
	 *
	 * @return void
	 */
	public function register_partial_assets(): void {
		foreach ( $this->partial_assets as $asset_type => $assets ) {
			$method = 'register_partial_' . $asset_type . '_assets';

			if ( ! method_exists( $this, $method ) ) {
				continue;
			}

			foreach ( $assets as $class_name => $data ) {
				$this->$method( $class_name, $data['path_from_theme_root'], $data['version'] );
			}
		}
	}

	/**
	 * Inject our custom assets (js/css) to the blocks' metadata.
	 *
	 * @param array $metadata
	 *
	 * @return array
	 */
	public function add_block_assets_to_metadata( $metadata ): array {
		$class_name = 'wp-block-' . $this->get_block_formated_name( $metadata['name'] );
		$has_css    = isset( $this->partial_assets['css'][ $class_name ] );
		$has_js     = isset( $this->partial_assets['js'][ $class_name ] );

		if ( ! $has_css && ! $has_js ) {
			return $metadata;
		}

		if ( $has_css ) {
			$style = ! empty( $metadata['style'] ) ? $metadata['style'] : [];

			if ( ! is_array( $style ) ) {
				$style = [ $style ];
			}

			$metadata['style'] = \array_merge( $style, [ 'theme-' . $class_name ] );
		}

		if ( $has_js ) {
			$view_script = ! empty( $metadata['viewScript'] ) ? $metadata['viewScript'] : [];

			if ( ! is_array( $view_script ) ) {
				$view_script = [ $view_script ];
			}

			$metadata['viewScript'] = \array_merge( $view_script, [ 'theme-' . $class_name ] );
		}

		return $metadata;
	}

	/**
	 * Enqueue pattern assets based on the block class names
	 * ex: <div class="wp-block-group wp-pattern-card"> will enqueue wp-pattern-card.css and wp-pattern-card.js files if they exist
	 *
	 * @param string $block_content
	 * @param array $block
	 *
	 * @return string
	 */
	public function enqueue_pattern_assets( $block_content, $block ): string {
		if ( empty( $block['attrs']['className'] ) ) {
			return $block_content;
		}

		foreach ( explode( ' ', $block['attrs']['className'] ) as $class_name ) {
			if ( ! str_starts_with( $class_name, 'wp-pattern-' ) ) {
				continue;
			}

			if ( isset( $this->partial_assets['css'][ $class_name ] ) ) {
				$this->assets_tools->enqueue_style( 'theme-' . $class_name );
			}

			if ( isset( $this->partial_assets['js'][ $class_name ] ) ) {
				$this->assets_tools->enqueue_script( 'theme-' . $class_name );
			}
		}

		return $block_content;
	}

	/**
	 * Enqueue template assets based on the body class
	 * ex: <body class="home"> will enqueue the home.css file and home.js file if they exist
	 *
	 * @return void
	 */
	public function enqueue_template_assets(): void {
		$body_classes = get_body_class();

		foreach ( $body_classes as $body_class ) {
			if ( isset( $this->partial_assets['css'][ $body_class ] ) ) {
				$this->assets_tools->enqueue_style( 'theme-' . $body_class );
			}

			if ( isset( $this->partial_assets['js'][ $body_class ] ) ) {
				$this->assets_tools->enqueue_script( 'theme-' . $body_class );
			}
		}
	}

	/**
	 * Get the formated block name
	 * ex: core/button -> button
	 * ex: beapi/icon -> beapi-icon
	 *
	 * @param string $block_name
	 *
	 * @return string
	 */
	private function get_block_formated_name( string $block_name ): string {
		if ( empty( $block_name ) ) {
			return '';
		}

		// remove core/ prefix (ex: core/button -> button)
		$block_name = str_replace( 'core/', '', $block_name );

		// replace / with - (ex: beapi/icon -> beapi-icon)
		$block_name = str_replace( '/', '-', $block_name );

		return $block_name;
	}

	/**
	 * Register partial CSS assets.
	 *
	 * @param string $class_name
	 * @param string $path_from_theme_root
	 * @param string $version
	 *
	 * @return void
	 */
	private function register_partial_css_assets( $class_name, $path_from_theme_root, $version ): void {
		$this->assets_tools->register_style(
			'theme-' . $class_name,
			$path_from_theme_root,
			[ 'theme-style' ],
			$version,
		);

		wp_style_add_data(
			'theme-' . $class_name,
			'path',
			get_theme_file_path( $path_from_theme_root )
		);
	}

	/**
	 * Register partial JS assets.
	 *
	 * @param string $class_name
	 * @param string $path_from_theme_root
	 * @param string $version
	 *
	 * @return void
	 */
	private function register_partial_js_assets( $class_name, $path_from_theme_root, $version ): void {
		$this->assets_tools->register_script(
			'theme-' . $class_name,
			$path_from_theme_root,
			[ ! is_admin() ? 'scripts' : 'theme-admin-editor-script' ],
			$version,
			[ 'strategy' => 'defer' ]
		);
	}

	/**
	 * Load assets manifest.
	 *
	 * @return array{js: array, css: array}
	 */
	private function get_assets_manifest(): array {
		static $manifest;

		if ( is_array( $manifest ) ) {
			return $manifest;
		}

		if ( ! class_exists( \WebpackBuiltFiles::class ) ) {
			$assets_file_path = get_theme_file_path( self::BUILD_DIR . '/assets.php' );
			if ( ! is_readable( $assets_file_path ) ) {
				$manifest = [
					'js'  => [],
					'css' => [],
				];

				return $manifest;
			}

			require_once $assets_file_path;
		}

		//phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$manifest = [
			'js'  => \WebpackBuiltFiles::$jsFiles,
			'css' => \WebpackBuiltFiles::$cssFiles,
		];

		//phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $manifest;
	}
}
