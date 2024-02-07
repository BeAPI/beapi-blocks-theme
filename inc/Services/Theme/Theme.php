<?php

namespace BEA\Theme\Framework\Services\Theme;

use BEA\Theme\Framework\Service;
use BEA\Theme\Framework\Service_Container;


class Theme {

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
		add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'script', 'style' ] );
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
		load_theme_textdomain( 'framework-textdomain', \get_theme_file_path( '/languages' ) );
	}

	/**
	 * Register custom images sizes
	 */
	private function add_images_sizes(): void {
		add_image_size( 'large-square', 1024, 1024, true );
	}
}
