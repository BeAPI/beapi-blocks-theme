const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const WebpackPHPManifestPlugin = require('webpack-php-manifest');
const ESLintPlugin = require('eslint-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const WebpackBar = require('webpackbar');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const BundleAnalyzerPlugin =
	require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const WebpackThemeJsonPlugin = require('./webpack-theme-json-plugin');
const WatchedGlobEntriesPlugin = require('webpack-watched-glob-entries-plugin');
const SpriteHashPlugin = require('./webpack-sprite-hash-plugin');

module.exports = {
	get(mode) {
		const plugins = [
			new WebpackThemeJsonPlugin({
				watch: mode !== 'production',
			}),
			new SpriteHashPlugin(),
			new CleanWebpackPlugin(),
			new ESLintPlugin({
				overrideConfigFile: path.resolve(__dirname, '../.eslintrc'),
				context: path.resolve(__dirname, '../src/js'),
				files: '**/*.js',
			}),
			new SpriteLoaderPlugin({
				plainSprite: true,
			}),
			new StyleLintPlugin({
				configFile: path.resolve(__dirname, '../.stylelintrc'),
				context: path.resolve(__dirname, '../src/scss'),
				files: '**/*.scss',
			}),
			new WebpackBar({
				color: '#ffe600',
			}),
			new DependencyExtractionWebpackPlugin(),
		];

		if (mode === 'production') {
			plugins.push(
				new BundleAnalyzerPlugin({
					analyzerMode: 'json',
					generateStatsFile: true,
				})
			);
			plugins.push(
				new MiniCssExtractPlugin({
					filename: '[name].[contenthash:8].min.css',
				})
			);
		} else {
			plugins.push(
				new WatchedGlobEntriesPlugin(),
				new MiniCssExtractPlugin({
					filename: '[name].css',
				})
			);
		}

		plugins.push(
			new WebpackPHPManifestPlugin({
				output: 'assets',
			}),
			new RemoveEmptyScriptsPlugin()
		);

		return plugins;
	},
};
