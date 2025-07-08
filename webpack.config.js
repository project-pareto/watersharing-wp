const path = require('path');

// css extraction and minification
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = [
	{
		mode: isProduction ? 'production' : 'development',
		devtool: isProduction ? false : 'source-map',
		entry: {
			'watersharing': [ './assets/src/js/main.js', './assets/src/scss/frontend.scss' ],
			'watersharing-admin': [ './assets/src/scss/editor.scss' ],
		},
		output: {
			path: path.resolve(__dirname, 'assets/dist/js/'),
			filename: '[name].min.js',
			clean: true,
			devtoolNamespace: 'watersharing-wp',
			devtoolModuleFilenameTemplate: 'webpack://watersharing-wp/[resource-path]'
		},
		module: {
			rules: [
				// js babelization
				{
					test: /\.(js|jsx)$/,
					exclude: /node_modules/,
					loader: 'babel-loader'
				},
				// sass compilation
				{
					test: /\.(sass|scss)$/,
					use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
				},
				// loader for webfonts (only required if loading custom fonts)
				{
					test: /\.(woff|woff2|eot|ttf|otf)$/,
					type: 'asset/resource',
					generator: {
						filename: './css/build/font/[name][ext]',
					}
				},
				// loader for images and icons (only required if css references image files)
				{
					test: /\.(png|jpg|gif)$/,
					type: 'asset/resource',
					generator: {
						filename: './css/build/img/[name][ext]',
					}
				},
			]
		},
		plugins: [
			new RemoveEmptyScriptsPlugin(),
			// css extraction into dedicated file
			new MiniCssExtractPlugin({
				filename: '../css/[name].min.css'
			}),
		],
		optimization: {
			// minification - only performed when mode = production
			minimizer: [
				// js minification - special syntax enabling webpack 5 default terser-webpack-plugin
				`...`,
				// css minification
				new CssMinimizerPlugin(),
			]
		},
	}
];
