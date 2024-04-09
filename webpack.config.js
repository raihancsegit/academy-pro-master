const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const path = require('path');

const ACADEMY_PRO_VERSION = '1.7.4';

const config = {
	...defaultConfig,
	entry: {
		tutorBookingCommon: path.resolve(
			__dirname,
			'dev_academy-pro/tutor-booking-common.js'
		),
	},
	output: {
		filename: `[name].${ACADEMY_PRO_VERSION}.js`,
		path: path.resolve(__dirname, 'assets/build'),
	},
	plugins: [...defaultConfig.plugins, new CleanWebpackPlugin()],
	resolve: {
		alias: {
			...defaultConfig.resolve.alias,
			'@Components': path.resolve(
				__dirname,
				'../academy/dev_academy/components/'
			),
			'@Containers': path.resolve(
				__dirname,
				'../academy/dev_academy/containers/'
			),
			'@Global': path.resolve(
				__dirname,
				'../academy/dev_academy/global/'
			),
			'@Utils': path.resolve(__dirname, '../academy/dev_academy/utils/'),
		},
	},
};

module.exports = config;
