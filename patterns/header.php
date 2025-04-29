<?php
/**
 * Title: Header
 * Slug: beapi-blocks-theme/header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Header with site title and navigation.
 *
 * @package WordPress
 * @subpackage BeAPI Blocks Theme
 * @since BeAPI Blocks Theme 1.0
 */

?>
<!-- wp:group {
	"align": "full",
	"layout": {
		"type": "default"
	}
} -->
<div class="wp-block-group alignfull">
	<!-- wp:group {
		"className": "has-background-color",
		"style": {
			"spacing": {
				"padding": {
					"top": "var:preset|spacing|md",
					"bottom": "var:preset|spacing|md"
				}
			}
		},
		"backgroundColor": "white",
		"layout": {
			"type": "constrained"
		}
	} -->
	<div class="wp-block-group has-background-color has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md)">
		<!-- wp:group {
			"align": "wide",
			"layout": {
				"type": "flex",
				"flexWrap": "nowrap",
				"justifyContent": "space-between"
			}
		} -->
		<div class="wp-block-group alignwide">
			<!-- wp:group {
				"layout": {
					"type": "flex",
					"flexWrap": "nowrap"
				}
			} -->
			<div class="wp-block-group">
				<!-- /wp:group -->

			<!-- wp:group {
				"style": {
					"spacing": {
						"blockGap": "var:preset|spacing|10"
					}
				},
				"layout": {
					"type": "flex",
					"flexWrap": "nowrap",
					"justifyContent": "right"
				}
			} -->
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
