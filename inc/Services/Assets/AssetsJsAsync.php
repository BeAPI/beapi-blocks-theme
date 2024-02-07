<?php

namespace BEA\Theme\Framework\Services\Assets;

/**
 * Class Assets_JS_Async
 *
 * @package BEA\Theme\Framework
 */
class AssetsJsAsync {

	/**
	 * JS handlers for the script.
	 * The script styles to async load
	 * @var array
	 */
	private $js_handlers = [ 'scripts' => 'async' ];

	/**
	 * @param string $handler
	 * @param string $type : async/defer or a combination of
	 */
	public function add_handler( string $handler, string $type = 'async' ): void {
		$this->js_handlers[ $handler ] = $type;
	}

	/**
	 * Replace default generated WP Link Tag
	 *
	 * @param string $html The link tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 *
	 * @return string
	 * @author Nicolas JUEN
	 */
	public function script_loader_tag( string $html, string $handle ): string {
		if ( ! isset( $this->js_handlers[ $handle ] ) ) {
			return $html;
		}

		return str_replace( ' src', sprintf( ' %s src', esc_attr( $this->js_handlers[ $handle ] ) ), $html );
	}
}
