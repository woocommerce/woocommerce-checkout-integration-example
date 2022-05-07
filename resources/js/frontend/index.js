/*
 * External dependencies.
 */
import { getSetting } from '@woocommerce/settings';
import { registerPaymentMethodExtensionCallbacks } from '@woocommerce/blocks-registry';

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

			const visibilityData        = args.cart.extensions[ 'checkout-integration-example' ]?.gateway_visibility;
			const gatewayVisibilityData = visibilityData ? visibilityData.find( ( data ) => gatewayName === data.gateway ) : false;

			if ( gatewayVisibilityData ) {
				const { is_visible: isVisible } = gatewayVisibilityData;
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
