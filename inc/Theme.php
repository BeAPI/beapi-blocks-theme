<?php

namespace BEA\Theme\Framework;

use Pimple\Container;

class Theme {

	/**
	 * @var \WP_Theme
	 */
	protected $wp_theme;

	/**
	 * @var ServiceCollection
	 */
	protected $services;

	/**
	 * @var Container
	 */
	protected $container;

	public function __construct( \WP_Theme $wp_theme, ServiceCollection $collection, ?Container $container = null ) {
		$this->wp_theme  = $wp_theme;
		$this->services  = $collection;
		$this->container = $container ?? new Container();
	}

	public function boot(): void {
		$this->container['wp_theme'] = $this->wp_theme;

		foreach ( $this->services->all() as $service ) {
			$service->register( $this->container );
		}

		foreach ( $this->services->all() as $service ) {
			$service->boot( $this->container );
		}
	}
}