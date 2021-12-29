<?php
namespace um_ext\um_wpjm\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Account
 *
 * @package um_ext\um_wpjm\core
 */
class Account {


	/**
	 * Account constructor.
	 */
	function __construct() {
		add_filter( 'um_account_page_default_tabs_hook', [ &$this, 'add_account_tab' ], 10, 1 );
		add_filter( 'um_account_content_hook_wpjm', [ &$this, 'account_tab' ], 60, 2 );

		add_filter( 'um_account_scripts_dependencies', [ &$this, 'add_js_scripts' ], 10, 1 );
	}


	/**
	 * @param array $tabs
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	function add_account_tab( $tabs ) {
		if ( empty( $tabs[500]['wpjm'] ) ) {
			$tabs[500]['wpjm'] = [
				'icon'          => 'um-faicon-list-alt',
				'title'         => __( 'Jobs Dashboard', 'um-wpjm' ),
				'show_button'   => false,
			];
		}

		return $tabs;
	}


	/**
	 * @param string $output
	 * @param array $shortcode_args
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	function account_tab( $output, $shortcode_args ) {
		if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
			$output .= '<div class="um-clear"></div><br />' . do_shortcode( '[job_dashboard]' );
		} else {
			$output .= '<div class="um-clear"></div><br />' . apply_shortcodes( '[job_dashboard]' );
		}

		return $output;
	}


	/**
	 * @param array $scripts
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	function add_js_scripts( $scripts ) {
		wp_register_script('um-wpjm-account', um_wpjm_url . 'assets/js/account' . UM()->enqueue()->suffix . '.js', [ 'wp-hooks' ], um_wpjm_version, true );

		$scripts[] = 'um-wpjm-account';
		return $scripts;
	}
}