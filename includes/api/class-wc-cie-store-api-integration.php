<?php
/**
 * A class to add custom data to the store API cart resource.
 *
 * @package WooCommerce Checkout Integration Example
 * @since   1.0.0
 */

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Exceptions\InvalidCartException;
use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;

class WC_CIE_Store_API_Integration {

	/**
	 * Plugin identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'checkout-integration-example';

	/**
	 * Bootstraps the class and hooks required data.
	 */
	public static function initialize() {

		// Extend StoreAPI.
		self::extend_store();

		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'checkout_order_processed' ) );
	}

	/**
	 * Register cart data handler.
	 */
	public static function extend_store() {

		if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CartSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'data_callback'   => array( __CLASS__, 'extend_cart_data' ),
					'schema_callback' => array( __CLASS__, 'extend_cart_schema' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}
	}

	/**
	 * Adds extension data to cart route responses.
	 *
	 * @return array
	 */
	public static function extend_cart_data() {

		$cart_data = array(
			'gateway_visibility' => array()
		);


		$gateways         = WC_CIE::get_payment_gateways();
		$visible_gateways = WC_CIE::get_payment_gateways( true );

		$gateway_data = array();

		foreach ( $gateways as $gateway ) {
			$cart_data[ 'gateway_visibility' ][ $gateway->id ] = array(
				'is_visible' => isset( $visible_gateways[ $gateway->id ] )
			);
		}

		return $cart_data;
	}

	/**
	 * Register schema into cart endpoint.
	 *
	 * @return  array  Registered schema.
	 */
	public static function extend_cart_schema() {

		$schema = array(

			'gateway_visibility' => array(
				'description' => __( 'Payment gateway visibility data.', 'woocommerce-checkout-integration-example' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'is_visible'   => array(
						'description' => __( 'Whether the payment method is hidden in the checkout block.', 'woocommerce-checkout-integration-example' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					)
				)
			)
		);

		return $schema;
	}

	/**
	 * Validates the order payment gateway.
	 *
	 * @throws InvalidCartException
	 *
	 * @param  \WC_Order  $order  Order object.
	 */
	public static function checkout_order_processed( $order ) {

		// Bail out early.
		if ( ! $order->needs_payment() ) {
			return;
		}

		$chosen_gateway   = $order->get_payment_method();
		$visible_gateways = WC_CIE::get_payment_gateways( true );
		$is_invalid       = isset( $visible_gateways[ $chosen_gateway ] );

		// Return error if necessary.
		if ( $is_invalid ) {

			$errors = new \WP_Error();
			$code   = self::IDENTIFIER . '-error';

			$errors->add( $code, __( 'Invalid payment option.', 'woocommerce-checkout-integration-example' ) );

			throw new InvalidCartException(
				'woocommerce_cie_payment_error',
				$errors,
				409
			);
		}
	}
}
