<?php

namespace BEA\Theme\Framework\Services\Svg;

use BEA\Theme\Framework\Service;
use Pimple\Container;

class SvgService implements Service {

	public function register( Container $container ): void {
		$container[ Svg::class ] = new Svg();
	}

	public function boot( Container $container ): void {
		add_filter( 'wp_kses_allowed_html', [ $container[ Svg::class ], 'allow_svg_tag' ] );
	}
}