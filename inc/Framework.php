<?php

namespace BeAPI\Theme\Framework;

use BeAPI\Theme\Framework\Services\Acf;
use BeAPI\Theme\Framework\Services\Assets;
use BeAPI\Theme\Framework\Services\Performance;
use BeAPI\Theme\Framework\Services\Assets_JS_Async;
use BeAPI\Theme\Framework\Services\Editor;
use BeAPI\Theme\Framework\Services\Editor_Patterns;
use BeAPI\Theme\Framework\Services\Menu;
use BeAPI\Theme\Framework\Services\Sidebar;
use BeAPI\Theme\Framework\Services\Svg;
use BeAPI\Theme\Framework\Services\Theme;
use BeAPI\Theme\Framework\Tools\Body_Class;
use BeAPI\Theme\Framework\Tools\Template_Parts;

/**
 * Class Framework
 *
 * @package BeAPI\Theme\Framework
 */
class Framework {
	/**
	 * @var Service_Container
	 */
	protected static $container;

	/**
	 * @var array $services
	 */
	protected static $services = [
		// Services
		Theme::class,
		Assets::class,
		Performance::class,
		Assets_JS_Async::class,
		Editor::class,
		Editor_Patterns::class,
		Svg::class,
		Acf::class,
		Menu::class,

		// Services as Tools
		Body_Class::class,
	];

	/**
	 * @return Service_Container
	 */
	public static function get_container(): Service_Container {
		if ( is_null( self::$container ) ) {
			self::$container = new Service_Container();
			array_map( [ __CLASS__, 'register_service' ], self::$services );
		}

		return self::$container;
	}

	/**
	 * Register Service
	 *
	 * @param $name
	 */
	public static function register_service( $name ): void {
		self::get_container()->register_service( $name );
	}
}
