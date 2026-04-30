/**
 * Webpack plugins and optimization minimizers for the theme build.
 *
 * @package
 */

const WebpackPHPManifestPlugin = require('webpack-php-manifest');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const WatchedGlobEntriesPlugin = require('webpack-watched-glob-entries-plugin');
const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const WebpackThemeJsonPlugin = require('./webpack-theme-json-plugin');
const BundleAnalyzerPlugin =
	require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

/**
 * Plugins appended to the merged webpack config.
 *
 * @param {Object}  options
 * @param {boolean} options.isProduction Whether webpack runs in production mode.
 * @param {boolean} options.analyze      Whether bundle analysis is enabled (ANALYZE env).
 * @return {import('webpack').WebpackPluginInstance[]} Plugins to append to the config.
 */
const getBuildPlugins = ({ isProduction, analyze }) => {
	const list = [
		new WebpackThemeJsonPlugin({
			watch: !isProduction,
		}),
		new SpriteLoaderPlugin({
			plainSprite: true,
		}),
		new WebpackPHPManifestPlugin({
			output: 'assets',
		}),
		new RemoveEmptyScriptsPlugin(),
	];

	if (!isProduction) {
		list.push(new WatchedGlobEntriesPlugin());
	}

	if (analyze) {
		list.push(
			new BundleAnalyzerPlugin({
				analyzerMode: 'json',
				generateStatsFile: true,
			})
		);
	}

	return list;
};

/**
 * Optimization minimizers (image pipeline + JS).
 *
 * @param {Object} options            Options.
 * @param {Object} options.svgoconfig SVGO options passed to imagemin-svgo.
 * @return {import('webpack').WebpackPluginInstance[]} Minimizer plugin instances.
 */
const getOptimizationMinimizers = ({ svgoconfig }) => [
	new ImageMinimizerPlugin({
		minimizer: {
			implementation: ImageMinimizerPlugin.imageminMinify,
			options: {
				plugins: [
					['gifsicle', { interlaced: true }],
					['jpegtran', { progressive: true }],
					['optipng', { optimizationLevel: 5 }],
					['svgo', { svgoconfig }],
				],
			},
		},
	}),
	new TerserPlugin({
		parallel: true,
		terserOptions: {
			format: {
				comments: /translators:/i,
			},
			compress: {
				passes: 2,
			},
			mangle: {
				reserved: ['__', '_n', '_nx', '_x'],
			},
		},
		extractComments: false,
	}),
];

module.exports = {
	getBuildPlugins,
	getOptimizationMinimizers,
};
