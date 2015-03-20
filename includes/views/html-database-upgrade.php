<?php

if ( isset( $_POST['wwm_do_db_upgrade'] ) ) {
	wwmas_do_database_upgrade();
} else {
	?>
	<div class="update-nag">
		<p><?php _e( 'Press the button below to migrate your surveys to the newest version', 'awesome-surveys' ); ?></p>
		<p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=awesome-surveys' ); ?>">
				<input class="button-primary" name="wwm_do_db_upgrade" type="submit" value="<?php _e( 'upgrade database', 'awesome-surveys' ) ?>">
			</form>
		</p>
	</div>
	<?php
}