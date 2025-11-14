const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const srcPath = path.resolve(__dirname, '../src');
const nodeModulesPath = path.resolve(__dirname, '../node_modules');
const browserslistConfig = require('@wordpress/browserslist-config');

function isEditor(loaderContext) {
	return loaderContext.resource.indexOf('editor.scss') > -1;
}

module.exports = {
	get(mode) {
		const isProduction = mode === 'production';

		return [
			{
				test: /\.(woff|woff2)$/,
				type: 'asset/resource',
				include: [
					srcPath + '/fonts',
					nodeModulesPath + '/@fontsource-variable',
					nodeModulesPath + '/@fontsource',
				],
				generator: {
					filename: 'fonts/[name][ext][query]',
				},
			},
			{
				test: /\.(png|jpe?g|gif|svg|avif|webp)$/,
				type: 'asset/resource',
				exclude: /icons/,
				include: srcPath + '/img',
				generator: {
					filename: 'images/[name][ext][query]',
				},
			},
			{
				test: /\.js$/i,
				include: srcPath + '/js',
				use: {
					loader: 'esbuild-loader',
					options: {
						loader: 'js',
						target: 'es2016',
						legalComments: 'inline',
					},
				},
			},
			{
				test: /\.(scss|css)$/,
				include: srcPath + '/scss',
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							publicPath: (resourcePath) => {
								// Calculate relative path from output CSS file location to dist root
								// This ensures images resolve correctly regardless of CSS file depth
								// The resourcePath is the source SCSS file path

								// Get relative path from src/scss to determine entry structure
								const relativeToScss = path.relative(
									path.join(srcPath, 'scss'),
									resourcePath
								);

								// Remove file extension and normalize path separators
								const entryPath = relativeToScss
									.replace(/\.(scss|css)$/, '')
									.replace(/\\/g, '/');

								// Check if this is a common file (root level) or nested
								// Common files like 'common/style.scss' become 'app.css' at root
								// Nested files like 'wp-block/button.scss' become 'wp-block/button.css'
								const isCommon =
									entryPath.startsWith('common/');

								if (isCommon) {
									// Root-level CSS files (app.css, editor.css, etc.)
									return './';
								}

								// Calculate depth for nested CSS files
								// Entry path like 'wp-block/button' has depth 1
								const depth = (entryPath.match(/\//g) || [])
									.length;

								// Return '../' repeated for each level of nesting
								// This ensures images in dist/images/ resolve correctly
								return depth > 0 ? '../'.repeat(depth) : './';
							},
						},
					},
					{
						loader: 'css-loader',
						options: {
							url: true,
							esModule: false,
							importLoaders: 1,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							postcssOptions(loaderContext) {
								const obj = {
									plugins: {
										'postcss-import': {},
										'postcss-preset-env': {
											browsers: browserslistConfig,
											stage: 2,
											features: {
												// https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_logical_properties_and_values
												// https://stackoverflow.com/questions/64565180/how-to-prevent-postcss-preset-env-from-removing-css-logical-properties#answer-66966232
												// Use stage 2 features + disable logical properties and values rule
												'logical-properties-and-values': false,
											},
										},
										'postcss-pxtorem': {
											propWhiteList: [],
										},
										'postcss-sort-media-queries': {},
									},
								};

								if (isProduction && !isEditor(loaderContext)) {
									obj.plugins.cssnano = {};
								}

								return obj;
							},
						},
					},
					{
						loader: 'sass-loader',
						options: {
							sassOptions(loaderContext) {
								const obj = {
									quietDeps: true,
									sourceMap: true,
								};

								if (isProduction && isEditor(loaderContext)) {
									obj.outputStyle = 'expanded';
								}

								return obj;
							},
						},
					},
				],
			},
			{
				test: /\.svg$/,
				include: srcPath + '/img/icons',
				use: [
					{
						loader: 'svg-sprite-loader',
						options: {
							extract: true,
							publicPath: 'icons/',
							spriteFilename: (svgPath) =>
								`${
									/icons([\\|/])(.*?)\1/gm.exec(svgPath)[2]
								}.svg`,
							symbolId: (filePath) =>
								`icon-${path.basename(filePath).slice(0, -4)}`,
						},
					},
					{
						loader: 'svgo-loader',
					},
				],
			},
		];
	},
};
