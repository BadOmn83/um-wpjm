<?php
/**
 * Uninstall UM WP Job Manager
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_wpjm_path' ) ) {
	define( 'um_wpjm_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_wpjm_url' ) ) {
	define( 'um_wpjm_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_wpjm_plugin' ) ) {
	define( 'um_wpjm_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', [] );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_wpjm\core\Setup' ) ) {
		require_once um_wpjm_path . 'includes/core/class-setup.php';
	}

	$jb_setup = new um_ext\um_wpjm\core\Setup();

	//remove settings
	foreach ( $jb_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	update_option( 'um_options', $options );

	delete_option( 'um_wpjm_last_version_upgrade' );
	delete_option( 'um_wpjm_version' );
}