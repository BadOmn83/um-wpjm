<?php
/*
Plugin Name: Ultimate Member - WP Job Manager integration
Plugin URI: 
Description: Integrates Ultimate Member with WP Job Manager
Version: 1.0.0
Author: Vantage Plugins
Author URI: http://vantageplugins.com
Text Domain: um-wpjm
Domain Path: /languages
UM version: 2.1.7
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_wpjm_url', plugin_dir_url( __FILE__ ) );
define( 'um_wpjm_path', plugin_dir_path( __FILE__ ) );
define( 'um_wpjm_plugin', plugin_basename( __FILE__ ) );
define( 'um_wpjm_extension', $plugin_data['Name'] );
define( 'um_wpjm_version', $plugin_data['Version'] );
define( 'um_wpjm_textdomain', 'um-wpjm' );

define( 'um_wpjm_requires', '2.1.7' );

function um_wpjm_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_wpjm_textdomain, WP_LANG_DIR . '/plugins/' . um_wpjm_textdomain . '-' . $locale . '.mo');
	load_plugin_textdomain( um_wpjm_textdomain, false, dirname( plugin_basename(  __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'um_wpjm_plugins_loaded', 0 );


add_action( 'plugins_loaded', 'um_wpjm_check_dependencies', -20 );

if ( ! function_exists( 'um_wpjm_check_dependencies' ) ) {
	function um_wpjm_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_wpjm_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-wpjm' ), um_wpjm_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_wpjm_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_wpjm_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-wpjm' ), um_wpjm_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_wpjm_dependencies' );

			} elseif ( ! class_exists('WP_Job_Manager') ) {
				//UM is not active
				function um_wpjm_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'Sorry. You must activate the <strong>WP Job Manager</strong> plugin to use the %s.', 'um-wpjm' ), um_wpjm_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_wpjm_dependencies' );
			} else {
				require_once um_wpjm_path . 'includes/core/um-wpjm-init.php';
			}
		}
	}
}


if ( ! function_exists( 'um_wpjm_activation_hook' ) ) {
	function um_wpjm_activation_hook() {
		//first install
		$version = get_option( 'um_wpjm_version' );
		if ( ! $version ) {
			update_option( 'um_wpjm_last_version_upgrade', um_wpjm_version );
		}

		if ( $version != um_wpjm_version ) {
			update_option( 'um_wpjm_version', um_wpjm_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_wpjm\core\Setup' ) ) {
			require_once um_wpjm_path . 'includes/core/class-setup.php';
		}

		$fmwp_setup = new um_ext\um_wpjm\core\Setup();
		$fmwp_setup->run_setup();
	}
}
register_activation_hook( um_wpjm_plugin, 'um_wpjm_activation_hook' );