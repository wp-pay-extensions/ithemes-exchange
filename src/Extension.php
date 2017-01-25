<?php

/**
 * Title: Exchange iDEAL Add-On
 * Description:
 * Copyright Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Stefan Boonstra
 * @version 1.1.4
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_IThemesExchange_Extension {

	/**
	 * The add-on's slug.
	 *
	 * @var string
	 */
	public static $slug = 'pronamic-ideal';

	/**
	 * Options group.
	 *
	 * @const string
	 */
	const OPTION_GROUP = 'pronamic_ithemes_exchange_ideal_addon';

	/**
	 * The option key that stores the configuration ID.
	 *
	 * @const string
	 */
	const CONFIGURATION_OPTION_KEY = 'pronamic_ithemes_exchange_ideal_addon_configuration';

	/**
	 * The option key that stores the iDEAL payment button text.
	 *
	 * @const string
	 */
	const BUTTON_TITLE_OPTION_KEY = 'pronamic_ithemes_exchange_ideal_addon_button_title';

	/**
	 * The option key that stores the payment method.
	 *
	 * @const string
	 */
	const PAYMENT_METHOD_OPTION_KEY = 'pronamic_ithemes_exchange_ideal_addon_payment_method';

	//////////////////////////////////////////////////

	/**
	 * Bootstrap.
	 */
	public static function bootstrap() {
		add_action( 'it_exchange_register_addons', array( __CLASS__, 'init' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize.
	 */
	public static function init() {
		$slug = self::$slug;

		$options = array(
			'name'              => __( 'iDEAL', 'pronamic_ideal' ),
			'description'       => __( 'Adds the ability for users to checkout with iDEAL.', 'pronamic_ideal' ),
			'author'            => 'Pronamic',
			'author_url'        => 'http://www.pronamic.eu/wordpress-plugins/pronamic-ideal/',
			'icon'              => plugins_url( 'images/icon-50x50.png', Pronamic_WP_Pay_Plugin::$file ),
			// @see https://github.com/wp-plugins/ithemes-exchange/blob/1.7.16/core-addons/load.php#L42
			'wizard-icon'       => plugins_url( 'images/icon-50x50.png', Pronamic_WP_Pay_Plugin::$file ),
			'file'              => dirname( __FILE__ ) . '/../views/add-on.php',
			'category'          => 'transaction-methods',
			'supports'          => array( 'transaction_status' => true ),
			'settings-callback' => array( __CLASS__, 'settings' ),
		);

		it_exchange_register_addon( $slug, $options );

		// Actions
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

		add_action( 'template_redirect', array( __CLASS__, 'process_payment' ), 11 );

		add_action( "pronamic_payment_status_update_{$slug}", array( __CLASS__, 'status_update' ), 10, 2 );

		add_action( "it_exchange_print_{$slug}_wizard_settings", array( __CLASS__, 'wizard_settings' ) );

		add_action( "it_exchange_save_{$slug}_wizard_settings", array( __CLASS__, 'save_wizard_settings' ) );

		// Filters
		add_filter( 'pronamic_payment_source_text_' . $slug, array( __CLASS__, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . $slug,   array( __CLASS__, 'source_description' ), 10, 2 );

		add_filter( "it_exchange_get_{$slug}_make_payment_button", array( __CLASS__, 'make_payment_button' ) );

		add_filter( "it_exchange_{$slug}_transaction_is_cleared_for_delivery", array( __CLASS__, 'transaction_is_cleared_for_delivery' ), 10, 2 );
	}

	//////////////////////////////////////////////////

	/**
	 * Register settings.
	 */
	public static function register_settings() {
		add_settings_section(
			self::OPTION_GROUP, // id
			null, // title
			'__return_false', // callback
			self::OPTION_GROUP // page
		);

		add_settings_field(
			self::BUTTON_TITLE_OPTION_KEY, // id
			__( 'Title', 'pronamic_ideal' ), // title
			array( __CLASS__, 'input_text' ), // callback
			self::OPTION_GROUP, // page
			self::OPTION_GROUP, // section
			array(
				'label_for' => self::BUTTON_TITLE_OPTION_KEY,
				'classes'   => array( 'regular-text' ),
				'default'   => self::get_gateway_button_title(),
			) // args
		);

		add_settings_field(
			self::CONFIGURATION_OPTION_KEY, // id
			__( 'iDEAL Configuration', 'pronamic_ideal' ), // title
			array( __CLASS__, 'input_select' ), // callback
			self::OPTION_GROUP, // page
			self::OPTION_GROUP, // section
			array(
				'label_for' => self::CONFIGURATION_OPTION_KEY,
				'options'   => Pronamic_WP_Pay_Plugin::get_config_select_options(),
			) // args
		);

		add_settings_field(
			self::PAYMENT_METHOD_OPTION_KEY, // id
			__( 'Payment Method', 'pronamic_ideal' ), // title
			array( __CLASS__, 'input_select' ), // callback
			self::OPTION_GROUP, // page
			self::OPTION_GROUP, // section
			array(
				'label_for' => self::PAYMENT_METHOD_OPTION_KEY,
				'options'   => self::get_payment_method_select_options(),
				'default'   => '0',
			) // args
		);

		register_setting( self::OPTION_GROUP, self::BUTTON_TITLE_OPTION_KEY );
		register_setting( self::OPTION_GROUP, self::CONFIGURATION_OPTION_KEY );
		register_setting( self::OPTION_GROUP, self::PAYMENT_METHOD_OPTION_KEY );
	}

	/**
	 * Input text.
	 *
	 * @param array $args
	 */
	public static function input_text( $args ) {
		$name = $args['label_for'];

		$classes = array();
		if ( isset( $args['classes'] ) ) {
			$classes = $args['classes'];
		}

		$default = '';
		if ( isset( $args['default'] ) ) {
			$default = $args['default'];
		}

		printf(
			'<input name="%s" id="%s" type="text" class="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( get_option( $name, $default ) )
		);
	}

	/**
	 * Input select.
	 *
	 * @param array $args
	 */
	public static function input_select( $args ) {
		$name = $args['label_for'];

		$classes = array();
		if ( isset( $args['classes'] ) ) {
			$classes = $args['classes'];
		}

		$options = array();
		if ( isset( $args['options'] ) ) {
			$options = $args['options'];
		}

		printf(
			'<select name="%s" id="%s" class="%s">',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( implode( ' ', $classes ) )
		);

		$current_value = get_option( $name );

		if ( empty( $current_value ) && isset( $args['default'] ) ) {
			$current_value = $args['default'];
		}

		foreach ( $options as $option_key => $option ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option_key ),
				selected( $option_key, $current_value, false ),
				esc_attr( $option )
			);
		}

		echo '</select>';
	}

	/**
	 * Payment method select options
	 */
	public static function get_payment_method_select_options() {
		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( self::get_gateway_configuration_id() );

		if ( $gateway ) {
			$payment_method_field = $gateway->get_payment_method_field();

			if ( $payment_method_field ) {
				return $payment_method_field['choices'][0]['options'];
			}
		}

		return array(
			'' => _x( 'All', 'Payment method field', 'pronamic_ideal' ),
		);
	}

	/**
	 * Addon settings.
	 */
	public static function settings() {
		include dirname( __FILE__ ) . '/../views/html-admin-page-settings.php';
	}

	/**
	 * Wizard settings.
	 */
	public static function wizard_settings() {
		include dirname( __FILE__ ) . '/../views/html-admin-wizard-settings.php';
	}

	/**
	 * Save wizard settings.
	 *
	 * @param array $errors
	 *
	 * @return array $errors
	 */
	public static function save_wizard_settings( $errors ) {
		$title          = filter_input( INPUT_POST, self::BUTTON_TITLE_OPTION_KEY , FILTER_SANITIZE_STRING );
		$config_id      = filter_input( INPUT_POST, self::CONFIGURATION_OPTION_KEY, FILTER_VALIDATE_INT );
		$payment_method = filter_input( INPUT_POST, self::PAYMENT_METHOD_OPTION_KEY, FILTER_SANITIZE_STRING );

		update_option( self::BUTTON_TITLE_OPTION_KEY, $title );
		update_option( self::CONFIGURATION_OPTION_KEY, $config_id );
		update_option( self::PAYMENT_METHOD_OPTION_KEY, $payment_method );

		return $errors;
	}

	//////////////////////////////////////////////////

	/**
	 * Get the iDEAL gateway title.
	 *
	 * @return string $button_title
	 */
	public static function get_gateway_button_title() {
		return get_option( self::BUTTON_TITLE_OPTION_KEY, __( 'Pay with iDEAL', 'pronamic_ideal' ) );
	}

	/**
	 * Get the iDEAL gateway configuration ID.
	 *
	 * @return string $configuration_id
	 */
	public static function get_gateway_configuration_id() {
		return get_option( self::CONFIGURATION_OPTION_KEY, 0 );
	}

	/**
	 * Get the iDEAL gateway payment method.
	 *
	 * @return string $payment_method
	 */
	public static function get_gateway_payment_method() {
		$payment_method = get_option( self::PAYMENT_METHOD_OPTION_KEY, '' );

		if ( '0' === $payment_method ) {
			return null;
		}

		return $payment_method;
	}

	//////////////////////////////////////////////////

	/**
	 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
	 *
	 * @since unreleased
	 *
	 * @param boolean $cleared passed in through WP filter. Ignored here.
	 * @param mixed   $transaction
	 *
	 * @return boolean
	 */
	public static function transaction_is_cleared_for_delivery( $cleared, $transaction ) {
		return Pronamic_WP_Pay_Extensions_IThemesExchange_IThemesExchange::ORDER_STATUS_PAID === it_exchange_get_transaction_status( $transaction );
	}

	/**
	 * Build the iDEAL payment form.
	 */
	public static function make_payment_button() {
		// Return early if cart total is <= 0
		// @see https://github.com/wp-plugins/ithemes-exchange/blob/1.11.8/core-addons/transaction-methods/paypal-standard/init.php#L359-L362
		// @see https://github.com/wp-plugins/ithemes-exchange/blob/1.11.8/api/cart.php#L781-L809
		$cart_total = it_exchange_get_cart_total( false );
		if ( $cart_total <= 0 ) {
			return;
		}

		// Cart total > 0
		$payment_form = '';

		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( self::get_gateway_configuration_id() );

		if ( $gateway ) {
			$gateway->set_payment_method( self::get_gateway_payment_method() );

			$payment_form .= '<form action="' . it_exchange_get_page_url( 'transaction' ) . '" method="post">';
			$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="' . self::$slug . '" />';
			$payment_form .= $gateway->get_input_html();
			$payment_form .= wp_nonce_field( 'pronamic-ideal-checkout', '_pronamic_ideal_nonce', true, false );
			$payment_form .= '<input type="submit" name="pronamic_ideal_process_payment" value="' . self::get_gateway_button_title() . '" />';
			$payment_form .= '</form>';
		}

		return $payment_form;
	}

	//////////////////////////////////////////////////

	/**
	 * Check if an iDEAL payment needs to be processed.
	 */
	public static function process_payment() {
		$do_process_payment = filter_input( INPUT_POST, 'pronamic_ideal_process_payment', FILTER_SANITIZE_STRING );

		if ( strlen( $do_process_payment ) <= 0 ) {
			return;
		}

		// Prepare transaction data
		$unique_hash        = it_exchange_create_unique_hash();
		$current_customer   = it_exchange_get_current_customer();
		$transaction_object = it_exchange_generate_transaction_object();

		if ( ! $transaction_object instanceof stdClass ) {
			return;
		}

		it_exchange_add_transient_transaction( self::$slug, $unique_hash, $current_customer->ID, $transaction_object );

		$configuration_id = self::get_gateway_configuration_id();

		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $configuration_id );

		if ( $gateway ) {
			$data = new Pronamic_WP_Pay_Extensions_IThemesExchange_PaymentData( $unique_hash, $transaction_object );

			$payment_method = self::get_gateway_payment_method();

			$gateway->set_payment_method( $payment_method );

			$payment = Pronamic_WP_Pay_Plugin::start( $configuration_id, $gateway, $data, $payment_method );

			$error = $gateway->get_error();

			if ( is_wp_error( $error ) ) {
				Pronamic_WP_Pay_Plugin::render_errors( $error );
			} else {
				$gateway->redirect( $payment );
			}

			exit;
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Update the status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 * @param bool                 $can_redirect (optional, defaults to false)
	 */
	public static function status_update( Pronamic_Pay_Payment $payment, $can_redirect = false ) {
		// Create empty payment data object to be able to get the URLs
		$empty_data = new Pronamic_WP_Pay_Extensions_IThemesExchange_PaymentData( 0, new stdClass() );

		switch ( $payment->get_status() ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED :
				$url = $empty_data->get_cancel_url();

				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED :
				$url = $empty_data->get_error_url();

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE :
				$url = $empty_data->get_error_url();

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS :
				$transient_transaction = it_exchange_get_transient_transaction( self::$slug, $payment->get_source_id() );

				// Create transaction
				$transaction_id = it_exchange_add_transaction(
					self::$slug,
					$payment->get_source_id(),
					Pronamic_WP_Pay_Extensions_IThemesExchange_IThemesExchange::ORDER_STATUS_PAID,
					$transient_transaction['customer_id'],
					$transient_transaction['transaction_object']
				);

				// A transaction ID is numeric on success
				if ( ! is_numeric( $transaction_id ) ) {
					$url = $empty_data->get_error_url();

					break;
				}

				$data = new Pronamic_WP_Pay_Extensions_IThemesExchange_PaymentData( $transaction_id, new stdClass() );

				$url = $data->get_success_url();

				it_exchange_empty_shopping_cart();

				break;
			case Pronamic_WP_Pay_Statuses::OPEN :
			default :
				$url = $empty_data->get_normal_return_url();

				break;
		}

		if ( $can_redirect ) {
			wp_redirect( $url );

			exit;
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Source column
	 *
	 * @param string                  $text
	 * @param Pronamic_WP_Pay_Payment $payment
	 *
	 * @return string $text
	 */
	public static function source_text( $text, Pronamic_WP_Pay_Payment $payment ) {
		$text  = '';
		$text .= __( 'iThemes Exchange', 'pronamic_ideal' ) . '<br />';
		$text .= sprintf( __( 'Order #%s', 'pronamic_ideal' ), $payment->source_id );

		return $text;
	}

	/**
	 * Source description.
	 */
	public static function source_description( $description, Pronamic_Pay_Payment $payment ) {
		$description = __( 'iThemes Exchange Order', 'pronamic_ideal' );

		return $description;
	}
}
