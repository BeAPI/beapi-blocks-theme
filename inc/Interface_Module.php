<?php
namespace BeAPI\Theme\Framework;

/**
 * Interface Interface_Module
 *
 * @package BeAPI\Theme\Framework
 */
interface Interface_Module {

	/**
	 * Register the module
	 *
	 * @param Service_Container $container
	 */
	public function register( Service_Container $container );
}
