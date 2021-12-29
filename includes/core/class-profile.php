<?php
namespace um_ext\um_wpjm\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile
 *
 * @package um_ext\um_wpjm\core
 */
class Profile {


	/**
	 * Profile constructor.
	 */
	function __construct() {
		add_filter( 'um_profile_tabs', [ $this, 'add_profile_tab' ], 802 );
		add_filter( 'um_user_profile_tabs', [ $this, 'check_profile_tab_privacy' ], 1000, 1 );

		add_action( 'um_profile_content_wpjm_default', [ &$this, 'profile_tab_content' ], 10, 1 );
	}


	/**
	 * Add profile tab
	 *
	 * @param array $tabs
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	function add_profile_tab( $tabs ) {
		$tabs['wpjm'] = [
			'name'  => __( 'Jobs', 'um-wpjm' ),
			'icon'  => 'um-faicon-list-alt',
		];

		return $tabs;
	}


	/**
	 * Add tabs based on user
	 *
	 * @param array $tabs
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	function check_profile_tab_privacy( $tabs ) {
		if ( empty( $tabs['wpjm'] ) ) {
			return $tabs;
		}

		$user_id = um_user( 'ID' );
		if ( ! $user_id ) {
			return $tabs;
		}

		if ( um_user( 'disable_jobs_tab' ) ) {
			unset( $tabs['wpjm'] );
			return $tabs;
		}

		return $tabs;
	}

	/**
	 * @param array $args
	 *
	 * @since 1.0
	 */
	function profile_tab_content( $args ) {
		// Custom WP query jobs
		$args_jobs = array(
			'post_type' => array('job_listing'),
			'post_status' => array('publish'),
			'posts_per_page' => get_option( 'job_manager_per_page' ),
			'order' => 'DESC',
			'orderby' => 'date',
			'author' => um_profile_id(),
			'meta_query' => array(
				array(
					'key'     => '_filled',
					'value'   => '0',
				),
			),
		);

		$jobs = new \WP_Query( $args_jobs );

		if ( $jobs->have_posts() ) {
			get_job_manager_template( 'job-listings-start.php' );
			while ( $jobs->have_posts() ) {
				$jobs->the_post();
				get_job_manager_template_part( 'content', 'job_listing' );
			}
			get_job_manager_template( 'job-listings-end.php' );
		} else {
			do_action( 'job_manager_output_jobs_no_results' );
		}

		wp_reset_postdata();
	}
}