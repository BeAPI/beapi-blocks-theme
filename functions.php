<?php
/**
 * Local
 *
 * @package    BeAPI Blocks Theme
 * @subpackage BeAPI Blocks Theme
 * @since      BeAPI Blocks Theme 1.0
 */

$autoload_path = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoload_path ) ) {
	require_once $autoload_path;
}

/**
 * Load all services
 */
add_action(
	'after_setup_theme',
	function () {
		// Boot the service, at after_setup_theme.
		\BEA\Theme\Framework\Framework::get_container()->boot_services();
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
require_once __DIR__ . '/inc/Helpers/Custom_Menu_Walker.php';
require_once __DIR__ . '/inc/Helpers/Sugar_Calendar.php';
