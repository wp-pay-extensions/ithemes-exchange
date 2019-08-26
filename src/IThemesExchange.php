<?php

namespace Pronamic\WordPress\Pay\Extensions\IThemesExchange;

/**
 * Title: iThemes Exchange
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Stefan Boonstra
 * @version 2.0.0
 * @since   1.0.0
 */
class IThemesExchange {
	/**
	 * Order status pending
	 *
	 * @var string
	 */
	const ORDER_STATUS_PENDING = 'pending';

	/**
	 * Order status paid
	 *
	 * @var string
	 */
	const ORDER_STATUS_PAID = 'paid';

	/**
	 * Order status refunded
	 *
	 * @var string
	 */
	const ORDER_STATUS_REFUNDED = 'refunded';

	/**
	 * Order status voided
	 *
	 * @var string
	 */
	const ORDER_STATUS_VOIDED = 'voided';

	/**
	 * Check if iThemes Exchange is active (Automattic/developer style)
	 *
	 * @link https://github.com/wp-plugins/ithemes-exchange/blob/1.7.14/init.php#L18
	 * @link https://github.com/Automattic/developer/blob/1.1.2/developer.php#L73
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return class_exists( 'IT_Exchange' );
	}
}
