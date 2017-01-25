<?php

/**
 * Title: iThemes Exchange
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Stefan Boonstra
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_IThemesExchange_IThemesExchange {
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

	//////////////////////////////////////////////////

	/**
	 * Check if iThemes Exchange is active (Automattic/developer style)
	 *
	 * @see https://github.com/wp-plugins/ithemes-exchange/blob/1.7.14/init.php#L18
	 * @see https://github.com/Automattic/developer/blob/1.1.2/developer.php#L73
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return class_exists( 'IT_Exchange' );
	}
}
