<?php

namespace BEA\Theme\Framework\Services;

use BEA\Theme\Framework\Service;
use BEA\Theme\Framework\Service_Container;
use BEA\Theme\Framework\Tools\Assets as Assets_Tools;
use function json_last_error;
use const JSON_ERROR_NONE;

/**
 * Class Assets
 *
 * @package BEA\Theme\Framework
 */
class Assets implements Service {

	/**
	 * @var Assets_Tools
	 */
	private $assets_tools;

	public $partial_assets = [];

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
		 * Add hooks for the scripts and styles to hook on
		 */
		add_action( 'wp', [ $this, 'register_assets' ] );
		add_action( 'init', [ $this, 'register_partial_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'add_theme_capabilities_script' ], 0 );
		add_filter( 'stylesheet_uri', [ $this, 'stylesheet_uri' ] );
		add_filter( 'wp_login_page_theme_css', [ $this, 'login_stylesheet_uri' ] );
		add_filter( 'render_block', [ $this, 'enqueue_partial_assets' ], 1000, 2 );
		add_filter( 'wp_enqueue_scripts', [ $this, 'enqueue_template_assets' ], 1000, 1 );
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
		$theme = wp_get_theme();

		// Do not add a versioning query param in assets URLs if minified
		$version = $this->is_minified() ? null : $theme->get( 'Version' );

		// Js
		$file       = $this->is_minified() ? $this->get_min_file( 'js' ) : 'app.js';
		$asset_data = $this->get_asset_data( $file );
		$this->assets_tools->register_script(
			'scripts',
			'dist/' . $file,
			array_merge( [ 'jquery' ], $asset_data['dependencies'] ), // ensure jQuery dependency is set even if not declared explicitly in the JS
			$asset_data['version'],
			[ 'strategy' => 'defer' ]
		);

		wp_add_inline_script(
			'scripts',
			'const THEME_DATA = ' . wp_json_encode(
				[
					'themeUri' => get_template_directory_uri(),
				]
			),
			'before'
		);

		// CSS
		wp_register_style( 'theme-style', get_stylesheet_uri(), [], $version );
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
		if ( $this->is_minified() ) {
			$file = $this->get_min_file( 'css' );
			if ( ! empty( $file ) && file_exists( \get_theme_file_path( '/dist/' . $file ) ) ) {
				return \get_theme_file_uri( '/dist/' . $file );
			}
		}

		if ( file_exists( \get_theme_file_path( '/dist/app.css' ) ) ) {
			return \get_theme_file_uri( '/dist/app.css' );
		}

		return $stylesheet_uri;
	}

	/**
	 * Return JS/CSS .min file based on assets.json
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_min_file( string $type ): string {
		if ( empty( $type ) ) {
			return '';
		}

		if ( ! file_exists( \get_theme_file_path( '/dist/assets.json' ) ) ) {
			return '';
		}

		$json   = file_get_contents( \get_theme_file_path( '/dist/assets.json' ) ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$assets = json_decode( $json, true );

		if ( empty( $assets ) || JSON_ERROR_NONE !== json_last_error() ) {
			return '';
		}

		switch ( $type ) {
			case 'css':
				$file = $assets['app.css'];
				break;
			case 'editor.css':
				$file = $assets['editor.css'];
				break;
			case 'login':
				$file = $assets['login.css'];
				break;
			case 'editor.js':
				$file = $assets['editor.js'];
				break;
			case 'js':
				$file = $assets['app.js'];
				break;
			default:
				$file = null;
				break;
		}

		// Custom type
		if ( ! empty( $assets[ $type ] ) ) {
			$file = $assets[ $type ];
		}

		if ( empty( $file ) ) {
			return '';
		}

		return $file;
	}

	/**
	 * Retrieve data for a compiled asset file.
	 *
	 * Asset data are produced by the webpack dependencies extraction plugin. They contain for each asset the list of
	 * dependencies use by the asset and a hash representing the current version of the asset.
	 *
	 * @param string $file The asset name including its extension, eg: app.js, app-min.js
	 *
	 * @return array{dependencies: string[], version:string} The asset data if available or an array with the default keys.
	 */
	public function get_asset_data( string $file ): array {
		static $cache_data;

		$empty_asset_data = [
			'dependencies' => [],
			'version'      => '',
		];

		$file = trim( $file );
		if ( empty( $file ) ) {
			return $empty_asset_data;
		}

		if ( isset( $cache_data[ $file ] ) ) {
			return $cache_data[ $file ];
		}

		$filename = strtok( $file, '.' );
		$file     = sprintf( '/dist/%s.asset.php', $filename );
		if ( ! file_exists( \get_theme_file_path( $file ) ) ) {
			$cache_data[ $file ] = $empty_asset_data;
			return $cache_data[ $file ];
		}

		$cache_data[ $file ] = require \get_theme_file_path( $file );

		return $cache_data[ $file ];
	}

	/**
	 * Check if we are on minified environment.
	 *
	 * @return bool
	 * @author Nicolas JUEN
	 */
	public function is_minified(): bool {
		return ( ! defined( 'SCRIPT_DEBUG' ) || SCRIPT_DEBUG === false );
	}

	/**
	 * Change login CSS URL
	 * @return string
	 */
	public function login_stylesheet_uri(): string {
		return $this->is_minified() ? 'dist/' . $this->get_min_file( 'login' ) : 'dist/login.css';
	}

	/**
	 * Add theme capabilities script
	 */
	public function add_theme_capabilities_script(): void {
		$name = 'theme-capabilities-script';

		$this->assets_tools->register_script( $name, '', [], null, [ 'in_footer' => false ] );
		$this->assets_tools->enqueue_script( $name );

		$this->assets_tools->add_inline_script( $name, "(function() {const html=document.documentElement;html.classList.add('js');if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches && !window.location.hash.includes('no-js-animation')){html.classList.add('js-animation');}})();" );
	}

	/**
	 * Load custom block styles only when the block is used.
	 */
	public function register_partial_assets(): void {
		$folders = [
			'wp-block'   => [ 'prefix' => 'wp-block' ],
			'wp-pattern' => [ 'prefix' => 'wp-pattern' ],
			'template'   => [ 'prefix' => '' ],
		];
		$exts    = [
			'css' => function ( $class_name, $file_uri, $version ) {
				wp_register_style(
					'theme-' . $class_name,
					$file_uri,
					[ 'theme-style' ],
					$version,
				);
			},
			'js'  => function ( $class_name, $file_uri, $version ) {
				wp_register_script(
					'theme-' . $class_name,
					$file_uri,
					[ ! is_admin() ? 'scripts' : 'theme-admin-editor-script' ],
					$version,
					true
				);
			},
		];

		foreach ( $exts as $ext => $callback ) {
			if ( ! isset( $this->partial_assets[ $ext ] ) ) {
				$this->partial_assets[ $ext ] = [];
			}

			foreach ( $folders as $folder => $options ) {
				$files = glob( get_template_directory() . '/dist/' . $folder . '/*.' . $ext );

				foreach ( $files as $file ) {
					if ( empty( $file ) || ! is_readable( $file ) ) {
						continue;
					}

					$version = null;

					// take only the first part (remove extension and hash for css files)
					$name = explode( '.', basename( $file ) )[0];
					// only js file can have -min suffix
					if ( str_contains( $name, '-min' ) ) {
						$version = $this->get_asset_data( $name )['version'];
						$name    = str_replace( '-min', '', $name );
					}
					// class name (ex: button -> wp-block-button)
					$class_name = ( ! empty( $options['prefix'] ) ? $options['prefix'] . '-' : '' ) . $name;
					// file uri
					$file_uri = \get_theme_file_uri( '/dist/' . $folder . '/' . basename( $file ) );
					// store the class name to detect it later
					$this->partial_assets[ $ext ][ $class_name ] = 'dist/' . $folder . '/' . basename( $file );

					// enqueue the assets
					$callback( $class_name, $file_uri, $version );
				}
			}
		}
	}

	/**
	 * Register partial assets based on the block class names
	 */
	public function enqueue_partial_assets( $block_content, $block ): string {
		$class_names = [];

		if ( ! empty( $block['blockName'] ) ) {
			// remove core/ prefix (ex: core/button -> button)
			$block_name = str_replace( 'core/', '', $block['blockName'] );
			// replace / with - (ex: beapi/icon -> beapi-icon)
			$block_name = str_replace( '/', '-', $block_name );
			// add wp-block- prefix (ex: button -> wp-block-button, beapi-icon -> wp-block-beapi-icon)
			$class_names[] = 'wp-block-' . $block_name;
		}

		if ( ! empty( $block['attrs']['className'] ) ) {
			$class_names = array_merge( $class_names, explode( ' ', $block['attrs']['className'] ) );
		}

		foreach ( $class_names as $class_name ) {
			if ( array_key_exists( $class_name, $this->partial_assets['css'] ) ) {
				wp_enqueue_style( 'theme-' . $class_name );
			}

			if ( array_key_exists( $class_name, $this->partial_assets['js'] ) ) {
				wp_enqueue_script( 'theme-' . $class_name );
			}
		}

		return $block_content;
	}

	/**
	 * Enqueue template assets based on the body class
	 */
	public function enqueue_template_assets(): void {
		$body_classes = get_body_class();

		foreach ( $body_classes as $body_class ) {
			if ( array_key_exists( $body_class, $this->partial_assets['css'] ) ) {
				wp_enqueue_style( 'theme-' . $body_class );
			}

			if ( array_key_exists( $body_class, $this->partial_assets['js'] ) ) {
				wp_enqueue_script( 'theme-' . $class_name );
			}
		}
	}
}
