<?php

namespace BEA\Theme\Framework\Services;

use BEA\Theme\Framework\Framework;
use BEA\Theme\Framework\Service;
use BEA\Theme\Framework\Service_Container;
use BEA\Theme\Framework\Tools\Assets as Assets_Tools;

use Beapi\IconBlock\Icon\Collection;
use Beapi\IconBlock\Icon\CollectionItemsFactory;
use function Beapi\IconBlock\register_icon_collection;

/**
 * The editor service
 */
class Editor implements Service {
	/**
	 * Assets tools
	 *
	 * @var Assets_Tools $assets_tools
	 */
	private $assets_tools;

	/**
	 * Assets
	 *
	 * @var Assets $assets
	 */
	private $assets;

	/**
	 * Register the service
	 *
	 * @param Service_Container $container The service container.
	 */
	public function register( Service_Container $container ): void {
		$this->assets_tools = new Assets_Tools();
		$this->assets       = Framework::get_container()->get_service( 'assets' );
	}

	/**
	 * Get the service name
	 *
	 * @return string The service name.
	 */
	public function get_service_name(): string {
		return 'editor';
	}

	/**
	 * Boot the service
	 *
	 * @param Service_Container $container The service container.
	 */
	public function boot( Service_Container $container ): void {
		$this->after_theme_setup();
		/**
		 * Load editor style css for admin and frontend
		 */
		add_action( 'init', [ $this, 'style' ] );

		/**
		 * Register custom block style
		 */
		$this->register_custom_block_styles();

		/**
		 * Customize theme.json settings
		 */
		add_filter( 'wp_theme_json_data_theme', [ $this, 'filter_theme_json_theme' ], 10, 1 );

		/**
		 * Load editor JS for ADMIN
		 */
		add_action( 'enqueue_block_editor_assets', [ $this, 'admin_editor_script' ] );

		/**
		 * White list of gutenberg blocks
		 */
		add_filter( 'allowed_block_types_all', [ $this, 'gutenberg_blocks_allowed' ], 10, 2 );

		/**
		 * Setup icon block collections
		 */
		add_action( 'init', [ $this, 'register_icon_block_collections' ], 11 );

		/**
		 * Register binding sources
		 */
		add_action( 'init', [ $this, 'register_binding_sources' ], 11 );
	}

	/**
	 * Register :
	 *  - theme_supports
	 *  - color palettes
	 *  - font sizes
	 *  - etc.
	 */
	private function after_theme_setup(): void {}

	/**
	 * Register custom block styles
	 */
	private function register_custom_block_styles() {
		for ( $i = 1; $i <= 6; $i++ ) {
			$style = [
				'name'  => 'h' . (string) $i,
				'label' => sprintf( 'Style H%s', (string) $i ),
			];

			// post-title
			register_block_style(
				'core/post-title',
				$style
			);

			// heading
			register_block_style(
				'core/heading',
				$style
			);

			// paragraph
			register_block_style(
				'core/paragraph',
				$style
			);
		}

		// paragraph
		foreach ( [ 'core/paragraph', 'core/post-date' ] as $block_name ) {
			register_block_style(
				$block_name,
				[
					'name'  => 'label',
					'label' => __( 'Label', 'beapi-frontend-framework' ),
				]
			);
		}

		foreach ( [ 'core/paragraph', 'core/post-excerpt' ] as $block_name ) {
			register_block_style(
				$block_name,
				[
					'name'  => 'small',
					'label' => __( 'Small', 'beapi-frontend-framework' ),
				]
			);

			register_block_style(
				$block_name,
				[
					'name'  => 'large',
					'label' => __( 'Large', 'beapi-frontend-framework' ),
				]
			);

			register_block_style(
				$block_name,
				[
					'name'  => 'huge',
					'label' => __( 'Huge', 'beapi-frontend-framework' ),
				]
			);
		}

		// post-terms
		register_block_style(
			'core/post-terms',
			[
				'name'  => 'tag',
				'label' => __( 'Tag', 'beapi-frontend-framework' ),
			]
		);
		register_block_style(
			'core/post-terms',
			[
				'name'  => 'label',
				'label' => __( 'Label', 'beapi-frontend-framework' ),
			]
		);

		foreach (
			[
				'link',
				'link-arrow-right',
			]
			as
			$style_name
		) {
			// button
			register_block_style(
				'core/button',
				[
					'name'  => $style_name,
					'label' => str_replace( '-', ' ', ucfirst( $style_name ) ),
				]
			);
		}
	}

	/**
	 * Editor style
	 */
	public function style(): void {
		$file = $this->assets->is_minified() ? $this->assets->get_min_file( 'editor.css' ) : 'editor.css';

		/**
		 * Do not enqueue a inexistant file on admin
		 */
		if ( ! is_file( get_theme_file_path( 'dist/' . $file ) ) ) {
			return;
		}

		add_editor_style( 'dist/' . $file );

		/**
		 * Load all partial assets
		 */
		if ( ! empty( $this->assets->partial_assets['css'] ) ) {
			foreach ( $this->assets->partial_assets['css'] as $class_name => $file ) {
				add_editor_style( $file );
			}
		}
	}

	/**
	 * Theme.json settings
	 * See https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/theme-json-living/
	 *
	 * @param WP_Theme_JSON_Data $theme_json Class to access and update the underlying data.
	 *
	 * @return WP_Theme_JSON_Data
	 */
	public function filter_theme_json_theme( \WP_Theme_JSON_Data $theme_json ): \WP_Theme_JSON_Data {
		$custom_theme_json = [];

		return $theme_json->update_with( $custom_theme_json );
	}

	/**
	 * Editor script
	 */
	public function admin_editor_script(): void {
		$file     = $this->assets->is_minified() ? $this->assets->get_min_file( 'editor.js' ) : 'editor.js';
		$filepath = 'dist/' . $file;

		if ( ! file_exists( get_theme_file_path( $filepath ) ) ) {
			return;
		}

		$asset_data = $this->assets->get_asset_data( $file );
		$this->assets_tools->register_script(
			'theme-admin-editor-script',
			$filepath,
			$asset_data['dependencies'],
			$asset_data['version'],
			[ 'in_footer' => true ]
		);

		$this->assets_tools->add_inline_script(
			'theme-admin-editor-script',
			'const BFFEditorSettings = ' . wp_json_encode(
				apply_filters(
					'bff_editor_custom_settings',
					[
						'disableAllBlocksStyles'  => [
							'core/separator',
							'core/quote',
							'core/pullquote',
							'core/table',
							'core/image',
						],
						'disabledBlocksStyles'    => [
							// 'core/button' => [ 'outline' ]
						],
						'allowedBlocksVariations' => [
							'core/embed' => [ 'youtube', 'vimeo', 'dailymotion' ],
						],
					]
				)
			),
			'before'
		);

		$this->assets_tools->enqueue_script( 'theme-admin-editor-script' );

		foreach ( $this->assets->partial_assets['js'] as $class_name => $file ) {
			$this->assets_tools->enqueue_script( 'theme-' . $class_name );
		}
	}

	/**
	 * Allow some core Gutenberg blocks
	 *
	 * @param bool|array               $allowed_blocks The allowed blocks.
	 * @param \WP_Block_Editor_Context $block_editor_context The block editor context.
	 *
	 * @return array The allowed blocks.
	 */
	public function gutenberg_blocks_allowed( $allowed_blocks, \WP_Block_Editor_Context $block_editor_context ): array {
		// If boolean, get explicit list of allowed blocks.
		if ( is_bool( $allowed_blocks ) ) {
			$allowed_blocks = $allowed_blocks ? array_keys( \WP_Block_Type_Registry::get_instance()->get_all_registered() ) : [];
		}

		// List of disallowed blocks.
		$disallowed_blocks = [];

		// Remove disallowed blocks from allowed blocks.
		foreach ( $disallowed_blocks as $block ) {
			if ( in_array( $block, $allowed_blocks, true ) ) {
				unset( $allowed_blocks[ array_search( $block, $allowed_blocks, true ) ] );
			}
		}

		return array_values( $allowed_blocks );
	}

	/**
	 * Register icon block collections
	 *
	 * @return void
	 */
	public function register_icon_block_collections(): void {
		if ( ! defined( 'BEAPI_ICON_DIR' ) ) {
			return;
		}

		// Register icon theme.
		$sprite_file = get_theme_file_path( '/dist/icons/sprite.svg' );

		if ( is_readable( $sprite_file ) ) {
			try {
				$theme_collection = Collection::from_sprite(
					'icon-theme',
					$sprite_file,
					[
						'label' => __( 'Theme', 'beapi-frontend-framework' ),
					]
				);

				register_icon_collection( $theme_collection );
			} catch ( \Exception $e ) { // phpcs:ignore
			}
		}

		// Register icon theme.
		$social_file = get_theme_file_path( '/dist/icons/social.svg' );

		if ( is_readable( $social_file ) ) {
			try {
				$theme_collection = Collection::from_sprite(
					'icon-social',
					$social_file,
					[
						'label' => __( 'Social', 'beapi-frontend-framework' ),
					]
				);

				register_icon_collection( $theme_collection );
			} catch ( \Exception $e ) { // phpcs:ignore
			}
		}

		// Register collections with icons from media library.
		$query = new \WP_Query(
			[
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image/svg+xml',
				'posts_per_page' => 500, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'no_found_rows'  => true,
			]
		);
		if ( $query->have_posts() ) {
			$media_collection = new Collection( 'mediatheque', __( 'Media library', 'beapi-frontend-framework' ) );
			foreach ( $query->posts as $svg ) {
				$path = get_attached_file( $svg->ID );

				if ( empty( $path ) ) {
					continue;
				}

				try {
					$items = CollectionItemsFactory::from_file(
						$path,
						[
							'name'  => $svg->post_name,
							'label' => get_the_title( $svg ),
						]
					);
					array_map( [ $media_collection, 'add' ], $items );
				} catch ( \Exception $e ) { // phpcs:ignore
				}
			}
			register_icon_collection( $media_collection );
		}
	}

	/**
	 * Register binding sources
	 *
	 * @return void
	 */
	public function register_binding_sources(): void {
		// Post type label.
		\register_block_bindings_source(
			'beapi-blocks-theme/post-type',
			[
				'label'              => __( 'Post type', 'beapi-blocks-theme' ),
				'get_value_callback' => function ( array $source_args, $block_instance ) {
					$post_id          = $block_instance->context['postId'];
					$post_type_object = \get_post_type_object( \get_post_type( $post_id ) );
					if ( $post_type_object ) {
						return \esc_html( $post_type_object->labels->singular_name );
					}
				},
				'uses_context'       => [ 'postId' ],
			]
		);
	}
}
