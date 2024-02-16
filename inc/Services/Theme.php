<?php

namespace BeAPI\Theme\Framework\Services;

use BeAPI\Theme\Framework\Service;
use BeAPI\Theme\Framework\Service_Container;


class Theme implements Service {

	/**
	 * @param Service_Container $container
	 */
	public function register( Service_Container $container ): void {
	}

	/**
	 * @param Service_Container $container
	 */
	public function boot( Service_Container $container ): void {

		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
		remove_action( 'wp_footer', 'the_block_template_skip_link' );
		add_action( 'wp_body_open', [ $this, 'template_skip_link' ] );
		add_action( 'wp_head', [ $this, 'dark_mode' ] );

		$this->after_setup_theme();
	}

	/**
	 * @return string
	 */
	public function get_service_name(): string {
		return 'theme';
	}

	/**
	 * After setup theme
	 */
	public function after_setup_theme(): void {
		/**
		 * Init the supports.
		 */
		$this->add_theme_supports();
		$this->remove_theme_supports();
		$this->add_images_sizes();

		/**
		 * Load translations.
		 */
		$this->i18n();
	}

	/**
	 * Set theme supports
	 */
	private function add_theme_supports(): void {
		// Add the theme support basic elements
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			[
				'comment-list',
				'comment-form',
				'search-form',
				'gallery',
				'caption',
				'script',
				'style',
			]
		);
		add_theme_support( 'title-tag' );
		add_theme_support( 'async-js' );
		add_theme_support( 'yoast-seo-breadcrumbs' );
		add_theme_support( 'block-template-parts' );
		add_post_type_support( 'page', 'excerpt' );
	}

	/**
	 * Remove theme supports
	 * @return void
	 */
	private function remove_theme_supports(): void {
		// remove the theme support basic elements
		remove_theme_support( 'core-block-patterns' );
	}

	/**
	 * i18n
	 */
	private function i18n(): void {
		// Load theme texdomain
		load_theme_textdomain( 'beapi-blocks-theme', \get_theme_file_path( '/languages' ) );
	}

	/**
	 * Register custom images sizes
	 */
	private function add_images_sizes(): void {
		add_image_size( 'large-square', 1024, 1024, true );
	}

	/** Add Custom Skip links
	 * @return void
	 */
	public function template_skip_link(): void {
		ob_start();
		get_template_part( 'components/parts/common/skip-links' );
		echo wp_kses_post( ob_get_clean() );
	}

	/** Add Custom Dark mode
	 * @return void
	 */

	function dark_mode() {
		/**
		 * Enqueue the skip-link script.
		 */
		ob_start();
		?>
		<script>
			( function() {
				const darkMode = localStorage.getItem( 'theme-dark-mode' ) || 'auto';
				if ( darkMode === 'true' || darkMode === 'auto' && window.matchMedia( '(prefers-color-scheme: dark)' ).matches) {
					document.body.classList.add( 'dark-mode' );
				}
			}() );
		</script>
		<?php
		$dark_mode_script = wp_remove_surrounding_empty_script_tags( ob_get_clean() );
		$dark_mode_script_handle    = 'theme-dark-mode';
		wp_register_script( $dark_mode_script_handle, false, array(), false, array( 'in_footer' => false ) );
		wp_add_inline_script( $dark_mode_script_handle, $dark_mode_script );
		wp_enqueue_script( $dark_mode_script_handle );
	}
}
