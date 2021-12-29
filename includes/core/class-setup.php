<?php
namespace um_ext\um_wpjm\core;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Class Setup
 *
 * @package um_ext\um_wpjm\core
 */
class Setup {


	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	var $settings_defaults;


	/**
	 * Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = [
			'profile_tab_wpjm'            => 1,
			'profile_tab_wpjm_privacy'    => 0,
			'account_tab_wpjm'            => 1,
			'job_show_pm_button'                => 0,
		];

		$notification_types_templates = array(
			'job_listing_approved'  => __( 'Your <a href="{job_uri}">job</a> is now approved.', 'um-wpjm' ),
			'job_listing_expired'  => __( 'Your <a href="{job_uri}">job</a> is now expired.', 'um-wpjm' ),
		);

		foreach ( $notification_types_templates as $k => $template ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $template;
		}
	}


	/**
	 * @since 1.0
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', [] );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * @since 1.0
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}