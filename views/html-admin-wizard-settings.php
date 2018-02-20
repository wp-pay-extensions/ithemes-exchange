<?php

use Pronamic\WordPress\Pay\Extensions\IThemesExchange\Extension;

?>
<div class="field <?php echo esc_attr( Extension::$slug ); ?>-wizard">
	<h3><?php esc_html_e( 'iDEAL', 'pronamic_ideal' ); ?></h3>

	<?php settings_fields( Extension::OPTION_GROUP ); ?>

	<?php do_settings_sections( Extension::OPTION_GROUP ); ?>

	<input
		class="enable-<?php echo esc_attr( Extension::$slug ); ?>"
		name="it-exchange-transaction-methods[]"
		value="<?php echo esc_attr( Extension::$slug ); ?>"
		type="hidden"
	/>
</div>
