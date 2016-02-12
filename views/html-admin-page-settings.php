<div class="wrap">
	<h1><?php esc_html_e( 'iDEAL', 'pronamic_ideal' ); ?></h1>

	<div class="it-exchange-return-to-addons">
		<p>
			<a href="<?php echo esc_attr( remove_query_arg( 'add-on-settings' ) ); ?>">&larr; <?php esc_html_e( 'Back to Add-ons', 'pronamic_ideal' ); ?></a>
		</p>
	</div>

	<form action="options.php" method="post">
		<?php settings_fields( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::OPTION_GROUP ); ?>

		<?php do_settings_sections( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::OPTION_GROUP ); ?>

		<?php submit_button(); ?>
	</form>
</div>
