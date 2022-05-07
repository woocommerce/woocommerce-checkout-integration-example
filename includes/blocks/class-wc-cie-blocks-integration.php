<?php

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks scripts
 *
 * @package WooCommerce Conditional Shipping and Payments
 * @since   1.13.0
 */
class WC_CIE_Blocks_Integration implements IntegrationInterface {


	/**
	 * The single instance of the class.
	 *
	 * @var WC_CIE_Blocks_Integration
	 */
	protected static $_instance = null;

	/**
	 * Main WC_CIE_Blocks_Integration instance. Ensures only one instance of WC_CIE_Blocks_Integration is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_CIE_Blocks_Integration
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Ops!', 'woocommerce-checkout-integration-example' ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Ops!', 'woocommerce-checkout-integration-example' ) );
	}

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'cie';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		$suffix            = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$script_path       = '/assets/js/frontend/blocks' . $suffix . '.js';
		$script_asset_path = WC_CIE_ABSPATH . 'assets/js/frontend/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => WC_CIE::get_plugin_version()
			);
		$script_url = WC_CIE::get_plugin_url() . $script_path;

		wp_register_script(
			'wc-cie-blocks',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		// Load JS translations.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wc-cie-blocks', 'woocommerce-checkout-integration-example', WC_CIE_ABSPATH . 'languages/' );
		}
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'wc-cie-blocks' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'wc-cie-blocks' );
	}

	/**
	 * Custom data made available to our script. Returns an array with all payment gateway ids enabled in WooCommerce.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'available_gateways' => array_keys( WC_CIE::get_payment_gateways() )
		);
	}
}
