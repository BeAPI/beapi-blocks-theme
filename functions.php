<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package beapi-blocks-theme
 * @since 0.0.1
 */

/**
 * Load all services
 */
add_action(
	'after_setup_theme',
	function () {
		// Boot the service, at after_setup_theme.
		\BeAPI\Theme\Framework\Framework::get_container()->boot_services();
	}
);
require_once __DIR__ . '/inc/Helpers/Svg.php';
require_once __DIR__ . '/inc/Helpers/Formatting/Escape.php';
require_once __DIR__ . '/inc/Helpers/Formatting/Image.php';
require_once __DIR__ . '/inc/Helpers/Formatting/Link.php';
require_once __DIR__ . '/inc/Helpers/Formatting/Share.php';
require_once __DIR__ . '/inc/Helpers/Formatting/Term.php';
require_once __DIR__ . '/inc/Helpers/Formatting/Text.php';
require_once __DIR__ . '/inc/Helpers/Pattern_Content.php';
