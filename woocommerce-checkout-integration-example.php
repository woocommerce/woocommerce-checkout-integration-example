<?php
/**
* Plugin Name: WooCommerce Checkout Integration Example 1.
* Plugin URI: https://woocommerce.com/
* Description: An example to help you integrate an existing legacy WooCommerce plugin with the new, block-based checkout.
* Version: 1.0.0
* Author: WooCommerce
*
* Text Domain: woocommerce-checkout-integration-example
* Domain Path: /languages/
*
* Requires PHP: 7.0
*
* Requires at least: 5.9
* Tested up to: 5.9
*
* WC requires at least: 6.3
* WC tested up to: 6.4
*
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @class    WC_CIE
 * @version  1.0.0
 */
class WC_CIE {

	const VERSION = '1.0.0';

	/**
	 * Plugin version getter.
	 *
	 * @return string
	 */
	public static function get_plugin_version() {
		return self::VERSION;
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 */
	public static function get_plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Initializes everything.
	 *
	 * @return void
	 */
	public static function initialize() {

		// Define constants.
		define( 'WC_CIE_ABSPATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		// Conditionally hide shipping options. Works with the shortcode-based and block-based checkout.
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'filter_shipping_methods' ), 10, 2 );

		// Load our integration.
		add_action( 'init', array( __CLASS__, 'load_integration' ) );
	}

	/**
	 * Extends the Store API and loads the script that integrates with the Checkout block.
	 *
	 * @return void
	 */
	public static function load_integration() {

		if ( ! did_action( 'woocommerce_blocks_loaded' ) ) {
			return;
		}

		require_once WC_CIE_ABSPATH . 'includes/api/class-wc-cie-store-api-integration.php';
		require_once WC_CIE_ABSPATH . 'includes/blocks/class-wc-cie-blocks-integration.php';

		WC_CIE_Store_API_Integration::initialize();

		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function( $registry ) {
				$registry->register( WC_CIE_Blocks_Integration::instance() );
			}
		);
	}

	/**
	 * Indicates whether a payment gateway is visible.
	 *
	 * @param  array  $gateway_id
	 * @return boolean
	 */
	public static function is_payment_gateway_visible( $gateway_id ) {

		$is_visible = true;

		if ( 'bacs' === $gateway_id ) {

			if ( ! isset( WC()->cart ) ) {
				return $is_visible;
			}

			$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

			if ( empty( $chosen_methods ) ) {
				return $is_visible;
			}

			foreach ( $chosen_methods as $chosen_method ) {
				if ( 0 === strpos( $chosen_method, 'free_shipping' ) ) {
					$is_visible = false;
					break;
				}
			}
		}

		return $is_visible;
	}

	/**
	 * Indicates whether a shipping rate is visible.
	 *
	 * @param  array  $method_id
	 * @return boolean
	 */
	public static function is_shipping_rate_visible( $method_id ) {

		$is_visible = true;

		if ( 'free_shipping' === $method_id ) {

			if ( ! isset( WC()->cart ) ) {
				return $is_visible;
			}

			if ( WC()->cart->get_cart_contents_total() < 100 ) {
				$is_visible = false;
			}
		}

		return $is_visible;
	}

	/**
	 * Retrieves all the payment gateways enabled in WooCommerce.
	 *
	 * @param  boolean  $visible_only  Returns only the visible payment gateways.
	 *
	 * @return array
	 */
	public static function get_payment_gateways( $visible_only = false ) {

		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( $visible_only ) {
			foreach ( $gateways as $gateway_id => $gateway ) {
				if ( ! self::is_payment_gateway_visible( $gateway_id ) ) {
					unset( $gateways[ $gateway_id ] );
				}
			}
		}

		return $gateways;
	}

	/**
	 * Hides shipping methods conditionally.
	 *
	 * @param  array  $rates
	 * @param  array  $package
	 * @return array
	 */
	public static function filter_shipping_methods( $rates, $package ) {

		foreach ( $rates as $rate_id => $rate ) {
			if ( ! self::is_shipping_rate_visible( $rate->get_method_id() ) ) {
				unset( $rates[ $rate_id ] );
			}
		}

		return $rates;
	}
}

WC_CIE::initialize();
