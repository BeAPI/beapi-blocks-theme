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
		$service_collection = new \BEA\Theme\Framework\ServiceCollection();
		$service_collection
			->add( new \BEA\Theme\Framework\Services\Acf\AcfService() )
			->add( new \BEA\Theme\Framework\Services\Assets\AssetsService() )
			->add( new \BEA\Theme\Framework\Services\Editor\EditorService() )
			->add( new \BEA\Theme\Framework\Services\Svg\SvgService() )
			->add( new \BEA\Theme\Framework\Services\Theme\ThemeService() );

		( new \BEA\Theme\Framework\Theme( wp_get_theme(), $service_collection ) )->boot();
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
