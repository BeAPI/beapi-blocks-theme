<?php
namespace BEA\Theme\Framework;

use Pimple\Container;

/**
 * Interface Service
 *
 * @package BEA\Theme\Framework
 */
interface Service {

	/**
	 * Register the service
	 *
	 * @param Container $container
	 */
	public function register( Container $container ): void;

	/**
	 * Boot the service
	 *
	 * @param Container $container
	 */
	public function boot( Container $container ): void;
}
