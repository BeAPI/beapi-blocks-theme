<?php

namespace BEA\Theme\Framework\Services\Acf;

use BEA\Theme\Framework\Service;
use Pimple\Container;

class AcfService implements Service {

	public function register( Container $container ): void {
		$container[ Acf::class ] = new Acf();
	}

	public function boot( Container $container ): void {
		add_action( 'template_redirect', [ $container[ Acf::class ], 'warning' ], 0 );
		add_action( 'init', [ $container[ Acf::class ], 'init' ], 0 );
		add_action( 'init', [ $container[ Acf::class ], 'init_acf' ] );
	}
}