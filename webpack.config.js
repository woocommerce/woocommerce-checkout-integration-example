const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require('path');

const wcDepMap = {
    '@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
    '@woocommerce/settings': ['wc', 'wcSettings'],
    '@woocommerce/block-data': ['wc', 'wcBlocksData'],
    '@woocommerce/shared-context': ['wc', 'wcBlocksSharedContext'],
    '@woocommerce/shared-hocs': ['wc', 'wcBlocksSharedHocs'],
    '@woocommerce/price-format': ['wc', 'priceFormat'],
    '@woocommerce/blocks-checkout': ['wc', 'blocksCheckout'],
};

const wcHandleMap = {
    '@woocommerce/blocks-registry': 'wc-blocks-registry',
    '@woocommerce/settings': 'wc-settings',
    '@woocommerce/block-settings': 'wc-settings',
    '@woocommerce/block-data': 'wc-blocks-data-store',
    '@woocommerce/shared-context': 'wc-blocks-shared-context',
    '@woocommerce/shared-hocs': 'wc-blocks-shared-hocs',
    '@woocommerce/price-format': 'wc-price-format',
    '@woocommerce/blocks-checkout': 'wc-blocks-checkout',
};

const requestToExternal = (request) => {
    if (wcDepMap[request]) {
        return wcDepMap[request];
    }
};

const requestToHandle = (request) => {
    if (wcHandleMap[request]) {
        return wcHandleMap[request];
    }
};

// Export configuration.
module.exports = {
    ...defaultConfig,
    entry: {
        'frontend/blocks': '/resources/js/frontend/blocks/index.js',
    },
    output: {
        path: path.resolve( __dirname, 'assets/dist' ),
        filename: '[name].js',
    },
    optimization: {
        ...defaultConfig.optimization,
        splitChunks: {
            cacheGroups: {
                default: false
            }
        },
        minimize: false // Handled by Grunt.
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            (plugin) =>
                plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
        ),
        new WooCommerceDependencyExtractionWebpackPlugin({
            requestToExternal,
            requestToHandle
        })
    ]
};
