const path = require('path');
const WatchedGlobEntriesPlugin = require('webpack-watched-glob-entries-plugin');

const dynamicEntries = WatchedGlobEntriesPlugin.getEntries(
	[
		path.resolve(__dirname, '../src/scss/**/*.scss'),
		path.resolve(__dirname, '../src/js/**/*.js'),
	],
	{
		ignore: [
			path.resolve(__dirname, '../src/scss/common/**/*.scss'),
			path.resolve(__dirname, '../src/js/common/**/*.js'),
		],
	}
);

module.exports = function () {
	return Object.assign(
		{
			app: ['./src/js/common/index.js', './src/scss/common/style.scss'],
			wpgb: './src/js/common/wpgb.js',
			editor: [
				'./src/js/common/editor.js',
				'./src/scss/common/editor.scss',
			],
			'post-build': './src/js/common/post-build.js',
			login: './src/scss/common/login.scss',
		},
		dynamicEntries()
	);
};
