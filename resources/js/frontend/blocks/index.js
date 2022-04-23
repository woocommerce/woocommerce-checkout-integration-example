/*
 * Checkout block integration code that is built by Webpack.
 */

/*
 * External dependencies.
 */
import { registerPlugin } from '@wordpress/plugins';
import { getSetting } from '@woocommerce/settings';
import { registerPaymentMethodExtensionCallbacks } from '@woocommerce/blocks-registry';
import { ExperimentalOrderMeta } from '@woocommerce/blocks-checkout';

/**
 * Callback that runs on every payment method to control its visibility in the Checkout block.
 */
const getPaymentMethodCallbacks = () => {

	const { available_gateways: allGateways } = getSetting( 'cie_data' );
	const callBacksConfig                     = {};

	if ( ! allGateways.length ) {
		return;
	}

	allGateways.forEach( ( gatewayName ) => {

		callBacksConfig[ gatewayName ] = ( args ) => {

			const visibilityData = args.cart.extensions[ 'checkout-integration-example' ].gateway_visibility;

			if ( gatewayName in visibilityData ) {
				const { is_visible: isVisible } = visibilityData[ gatewayName ];
				return isVisible;
			}

			return true;
		};
	} );

	return callBacksConfig;
}

/*
 * Registers a callback that runs on every payment method to control its visibility in the Checkout block.
 */
registerPaymentMethodExtensionCallbacks(
	'checkout-integration-example',
	getPaymentMethodCallbacks()
);
