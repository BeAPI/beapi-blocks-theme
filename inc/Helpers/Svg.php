<?php
namespace BeAPI\Theme\Framework\Helpers\Svg;

use BeAPI\Theme\Framework\Services\Svg;

/**
 * @usage BeAPI\Theme\Framework\Helpers\Svg\get_the_icon( 'like' );
 *
 * @param string $icon_class
 * @param array $additionnal_classes
 *
 * @return string
 */
function get_the_icon( string $icon_class, $additionnal_classes = [] ): string {
	/**
	* @var Svg $svg
	*/
	$svg = \BeAPI\Theme\Framework\Framework::get_container()->get_service( 'svg' );
	return false !== $svg ? $svg->get_the_icon( $icon_class, $additionnal_classes ) : '';
}

/**
 * @usage BeAPI\Theme\Framework\Helpers\Svg\the_icon( 'like' );
 *
 * @param string $icon_class
 * @param array  $additionnal_classes
 */
function the_icon( string $icon_class, $additionnal_classes = [] ): void {
	/**
	* @var Svg $svg
	*/
	$svg = \BeAPI\Theme\Framework\Framework::get_container()->get_service( 'svg' );
	false !== $svg ? $svg->the_icon( $icon_class, $additionnal_classes ) : '';
}
