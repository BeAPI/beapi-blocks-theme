const path = require('path');
const { merge } = require('webpack-merge');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

const entries = require('./config/entries');
const svgoconfig = require('./config/svgo.config');
const WebpackPHPManifestPlugin = require('webpack-php-manifest');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const WebpackThemeJsonPlugin = require('./config/WebpackThemeJsonPlugin');
const WatchedGlobEntriesPlugin = require('webpack-watched-glob-entries-plugin');
const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');

const BundleAnalyzerPlugin =
	require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = (env, argv) => {
	const mode = argv && argv.mode ? argv.mode : 'production';
	const isProduction = mode === 'production';
	const analyze =
		process.env.ANALYZE === 'true' || process.env.ANALYZE === '1';

	const config = merge(defaultConfig, {
		mode,
		entry: entries,
		output: {
			path: path.resolve(__dirname, 'dist'),
			publicPath: '',
			clean: true,
			assetModuleFilename: 'assets/[hash][ext][query]',
			filename: '[name].js',
		},
		externals: {
			...(defaultConfig.externals || {}),
			jquery: 'window.jQuery',
		},
	});

	config.module = config.module || {};
	config.module.rules = config.module.rules || [];

	// Ensure SVG sprite handling for src/img/icons takes precedence over wp-scripts default SVG rule.
	config.module.rules.unshift({
		test: /\.svg$/,
		include: path.resolve(__dirname, 'src/img/icons'),
		use: [
			{
				loader: 'svg-sprite-loader',
				options: {
					extract: true,
					publicPath: 'icons/',
					spriteFilename: (svgPath) =>
						`${/icons([\\|/])(.*?)\1/gm.exec(svgPath)[2]}.svg`,
					symbolId: (filePath) =>
						`icon-${path.basename(filePath).slice(0, -4)}`,
				},
			},
			{
				loader: 'svgo-loader',
				options: {
					plugins: svgoconfig.plugins,
				},
			},
		],
	});

	// Handle static SVGs as assets
	config.module.rules.push({
		test: /\.svg$/,
		include: path.resolve(__dirname, 'src/img/static'),
		type: 'asset/resource',
		generator: {
			filename: 'assets/[hash][ext][query]',
		},
	});

	config.plugins = config.plugins || [];

	config.plugins.push(
		new WebpackThemeJsonPlugin({
			watch: !isProduction,
		}),
		new SpriteLoaderPlugin({
			plainSprite: true,
		}),
		new WebpackPHPManifestPlugin({
			output: 'assets',
		}),
		new RemoveEmptyScriptsPlugin()
	);

	if (!isProduction) {
		config.plugins.push(new WatchedGlobEntriesPlugin());
	}

	if (analyze) {
		config.plugins.push(
			new BundleAnalyzerPlugin({
				analyzerMode: 'json',
				generateStatsFile: true,
			})
		);
	}

	config.optimization = config.optimization || {};
	config.optimization.minimizer = [
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

	return config;
};
