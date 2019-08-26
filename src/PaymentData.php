<?php

namespace Pronamic\WordPress\Pay\Extensions\IThemesExchange;

use Pronamic\WordPress\Pay\Payments\PaymentData as Pay_PaymentData;
use Pronamic\WordPress\Pay\Payments\Item;
use Pronamic\WordPress\Pay\Payments\Items;
use stdClass;

/**
 * Title: iThemes Exchange payment data
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Stefan Boonstra
 * @version 2.0.1
 * @since   1.0.0
 */
class PaymentData extends Pay_PaymentData {
	/**
	 * Unique hash with which the transaction data can be retrieved
	 *
	 * @var string
	 */
	private $unique_hash;

	/**
	 * Transaction object
	 *
	 * @var stdClass
	 */
	private $transaction_object;

	/**
	 * Constructs and initializes an Easy Digital Downloads iDEAL data proxy
	 *
	 * @param string   $unique_hash
	 * @param stdClass $transaction_object
	 */
	public function __construct( $unique_hash, stdClass $transaction_object ) {
		parent::__construct();

		$this->unique_hash        = $unique_hash;
		$this->transaction_object = $transaction_object;
	}

	/**
	 * Get source ID
	 *
	 * @return string $source_id
	 */
	public function get_source_id() {
		return $this->unique_hash;
	}

	/**
	 * Get source indicator
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 *
	 * @return string
	 */
	public function get_source() {
		return Extension::$slug;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function get_title() {
		/* translators: %s: order id */
		return sprintf( __( 'iThemes Exchange order %s', 'pronamic_ideal' ), $this->get_order_id() );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		/* translators: %s: order id */
		$description = sprintf( __( 'Order #%s', 'pronamic_ideal' ), $this->get_order_id() );

		return $description . ' - ' . $this->transaction_object->description;
	}

	/**
	 * Get order ID
	 *
	 * @return string
	 */
	public function get_order_id() {
		return $this->unique_hash;
	}

	/**
	 * Get items
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_items()
	 *
	 * @return Items
	 */
	public function get_items() {
		// Items
		$items = new Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount)
		$item = new Item();
		$item->set_number( $this->unique_hash );
		$item->set_description( $this->get_description() );
		$item->set_price( $this->transaction_object->total );
		$item->set_quantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	/**
	 * Get currency
	 *
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return $this->transaction_object->currency;
	}

	/**
	 * Get email address
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->transaction_object->shipping_address['email'];
	}

	/**
	 * Get first name.
	 *
	 * @return string
	 */
	public function get_first_name() {
		$shipping_address = $this->transaction_object->shipping_address;

		if ( is_array( $shipping_address ) && isset( $shipping_address['first-name'] ) ) {
			return $shipping_address['first-name'];
		}
	}

	/**
	 * Get last name.
	 *
	 * @return string
	 */
	public function get_last_name() {
		$shipping_address = $this->transaction_object->shipping_address;

		if ( is_array( $shipping_address ) && isset( $shipping_address['last-name'] ) ) {
			return $shipping_address['last-name'];
		}
	}

	/**
	 * Get customer name
	 *
	 * @return string
	 */
	public function get_customer_name() {
		$name = '';

		$shipping_address = $this->transaction_object->shipping_address;

		if ( is_array( $shipping_address ) ) {
			if ( isset( $shipping_address['first-name'] ) ) {
				$name .= $shipping_address['first-name'];

				if ( isset( $shipping_address['last-name'] ) ) {
					$name .= ' ' . $shipping_address['last-name'];
				}
			}
		}

		return $name;
	}

	/**
	 * Get address
	 *
	 * @return string
	 */
	public function get_address() {
		return sprintf(
			'%s %s',
			$this->transaction_object->shipping_address['address1'],
			$this->transaction_object->shipping_address['address2']
		);
	}

	/**
	 * Get city
	 *
	 * @return string
	 */
	public function get_city() {
		return $this->transaction_object->shipping_address['city'];
	}

	/**
	 * Get zip
	 *
	 * @return string
	 */
	public function get_zip() {
		return $this->transaction_object->shipping_address['zip'];
	}

	/**
	 * Get home URL
	 *
	 * @return string
	 */
	public function get_normal_return_url() {

		return home_url();
	}

	/**
	 * Get cancel URL
	 *
	 * @return string
	 */
	public function get_cancel_url() {

		return home_url();
	}

	/**
	 * Get success URL
	 *
	 * @return string
	 */
	public function get_success_url() {

		$page_url = it_exchange_get_transaction_confirmation_url( $this->unique_hash );

		if ( false === $page_url ) {
			return home_url();
		}

		return $page_url;
	}

	/**
	 * Get error URL
	 *
	 * @return string
	 */
	public function get_error_url() {

		return home_url();
	}
}
