<?php

namespace BEA\Theme\Framework\Services\Theme;

/**
 * Class Performance
 *
 * @package BEA\Theme\Framework
 */
class Performance {

	/**
	 * Register all the Theme assets
	 */
	public function register_assets(): void {
		if ( is_admin() ) {
			return;
		}
		// Passive touchstart if jequery is used
		if ( wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_add_inline_script( 'jquery', $this->passive_touchstart() );
		}
	}

	/**
	 * Add passsive touchstart for performance
	 * @return string
	 */
	public function passive_touchstart(): string {
		return 'jQuery.event.special.touchstart={setup:function(e,t,s){this.addEventListener("touchstart",s,{passive:!t.includes("noPreventDefault")})}},jQuery.event.special.touchmove={setup:function(e,t,s){this.addEventListener("touchmove",s,{passive:!t.includes("noPreventDefault")})}},jQuery.event.special.wheel={setup:function(e,t,s){this.addEventListener("wheel",s,{passive:!0})}},jQuery.event.special.mousewheel={setup:function(e,t,s){this.addEventListener("mousewheel",s,{passive:!0})}};';
	}

	/** Optimize srcset maximum width contextually
	 * @see https://developer.wordpress.org/reference/hooks/wp_calculate_image_sizes/
	 * instead of using the default max_srcset_image_width of 2048px, we can use a custom value
	 * based on the current image size and multiply it by 2 (or 3) for retina displays
	 * example : In request a 'medium' size of 300px, the max_srcset_image_width will be 600px
	 * in this case, the browser will only load images up to 600px wide if retina.
	 *
	 * @param $max_srcset_image_width
	 * @param $sizes_array
	 *
	 * @return int
	 */
	public function optimize_srcset_maximum_width( $max_srcset_image_width, $sizes_array ): int {
		return $sizes_array[0] * 2;
	}
}
