<?php
/**
 * Update version.
 *
 * @package    WP_Tabs
 */

update_option( 'wp_tabs_version', WP_TABS_VERSION );
update_option( 'wp_tabs_db_version', WP_TABS_VERSION );

// Delete transient to load new data of remommended plugins.
if ( get_transient( 'sptabs_plugins' ) !== false ) {
	delete_transient( 'sptabs_plugins' );
}
