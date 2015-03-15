<?php

if ( isset( $_POST['wwm_do_db_upgrade'] ) ) {
	wwmas_do_database_upgrade();
} else {
	echo '<input class="button-primary" name="wwm_do_db_upgrade" type="submit" value="' . __( 'upgrade database', 'awesome-surveys' ) . '">';
}