const path = require('path');
const { merge } = require('webpack-merge');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

const entries = require('./config/entries');
const svgoconfig = require('./config/svgo.config');
const {
	getBuildPlugins,
	getOptimizationMinimizers,
} = require('./config/plugins');
const {
	getSvgIconSpriteRule,
	getSvgStaticAssetRule,
} = require('./config/loaders');

module.exports = (env, argv) => {
	const mode = argv && argv.mode ? argv.mode : 'production';
	const isProduction = mode === 'production';

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
	config.module.rules.unshift(getSvgIconSpriteRule({ svgoconfig }));

	// Handle static SVGs as assets
	config.module.rules.push(getSvgStaticAssetRule());

	config.plugins = config.plugins || [];
	config.plugins.push(...getBuildPlugins({ isProduction }));

	// Skip *-rtl.css emit from @wordpress/scripts default RtlCssPlugin.
	config.plugins = config.plugins.filter(
		(plugin) => plugin?.constructor?.name !== 'RtlCssPlugin'
	);

	config.optimization = config.optimization || {};
	config.optimization.minimizer = getOptimizationMinimizers({ svgoconfig });

	return config;
};
