/**
 * Custom webpack module rules for SVG handling (sprite icons vs static assets).
 *
 * @package
 */

const path = require('path');

const themeRoot = path.resolve(__dirname, '..');
const iconsDir = path.resolve(themeRoot, 'src/img/icons');
const staticSvgDir = path.resolve(themeRoot, 'src/img/static');

/**
 * SVG rule for `src/img/icons`: extracted sprite + SVGO, evaluated before wp-scripts default SVG handling.
 *
 * @param {Object} options            Options.
 * @param {Object} options.svgoconfig SVGO config (expects `.plugins` for svgo-loader).
 * @return {import('webpack').RuleSetRule} Webpack rule for icon sprites.
 */
const getSvgIconSpriteRule = ({ svgoconfig }) => ({
	test: /\.svg$/,
	include: iconsDir,
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

/**
 * SVG rule for `src/img/static`: emit as hashed asset files.
 *
 * @return {import('webpack').RuleSetRule} Webpack rule for static SVG assets.
 */
const getSvgStaticAssetRule = () => ({
	test: /\.svg$/,
	include: staticSvgDir,
	type: 'asset/resource',
	generator: {
		filename: 'assets/[hash][ext][query]',
	},
});

module.exports = {
	getSvgIconSpriteRule,
	getSvgStaticAssetRule,
};
