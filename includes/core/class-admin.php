<?php
namespace um_ext\um_wpjm\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Admin
 *
 * @package um_ext\um_wpjm\core
 */
class Admin {


	/**
	 * Admin constructor.
	 */
	function __construct() {
		add_filter( 'um_admin_role_metaboxes', [ &$this, 'add_role_metabox' ], 10, 1 );

		add_filter( 'um_settings_structure', [ &$this, 'extend_settings' ], 10, 1 );
	}


	/**
	 * Creates options in Role page
	 *
	 * @param array $roles_metaboxes
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	function add_role_metabox( $roles_metaboxes ) {
		$roles_metaboxes[] = [
			'id'        => 'um-admin-form-wpjm{' . um_wpjm_path . '}',
			'title'     => __( 'WP Job Manager', 'um-wpjm' ),
			'callback'  => [ UM()->metabox(), 'load_metabox_role' ],
			'screen'    => 'um_role_meta',
			'context'   => 'normal',
			'priority'  => 'default'
		];

		return $roles_metaboxes;
	}


	/**
	 * Extend settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	function extend_settings( $settings ) {
		$key = ! empty( $settings['extensions']['sections'] ) ? 'wpjm' : '';
		$settings['extensions']['sections'][ $key ] = [
			'title'     => __( 'WP Job Manager', 'um-wpjm' ),
			'fields'    => [
				[
					'id'        => 'account_tab_wpjm',
					'type'      => 'checkbox',
					'label'     => __( 'Account Tab', 'um-wpjm' ),
					'tooltip'   => __( 'Show or hide an account tab that shows the jobs dashboard.', 'um-wpjm' ),
				],
				[
					'id'        => 'account_tab_wpjm',
					'type'      => 'checkbox',
					'label'     => __( 'Account Tab', 'um-wpjm' ),
					'tooltip'   => __( 'Show or hide an account tab that shows the jobs dashboard.', 'um-wpjm' ),
				],
			],
		];

		return $settings;
	}
}