<?php
namespace um_ext\um_wpjm\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Integrations
 *
 * @package um_ext\um_wpjm\core
 */
class Integrations {


	/**
	 * Integrations constructor.
	 */
	function __construct() {
		// UM: Social Activity integration
		add_filter( 'um_activity_global_actions', [ &$this, 'social_activity_action' ], 10, 1 );
		add_action( 'job_submission_after_create_account', [ &$this, 'social_activity_new_user' ], 10, 1 );
		add_action( 'job_submission_after_create_account', [ &$this, 'maybe_verify' ], 11, 1 );
		add_action( 'save_post', [ &$this,  'um_activity_new_wpjm_job' ], 9999, 1 );
		add_action( 'delete_post', [ &$this, 'um_activity_wpjm_delete_job' ], 9999, 1 );

		// UM: Notifications integration
		add_filter( 'um_notifications_core_log_types', [ &$this, 'add_notifications' ], 300, 1 );
		add_action( 'publish_job_listing', [&$this, 'um_notification_after_job_is_approved' ], 10, 2 );
		add_action( 'expired_job_listing', [&$this, 'um_notification_after_job_is_expired' ], 10, 1 );

		// UM: Messaging integration
		add_filter( 'um_messaging_settings_fields', [ &$this, 'add_messaging_settings' ], 10, 1 );
		add_action( 'job_application_end', [ &$this, 'add_private_message_button' ], 10, 1 );
	}


	/**
	 * Add new activity action
	 *
	 * @param array $actions
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	function social_activity_action( $actions ) {
		$actions['new-wpjm-job'] = __( 'New job', 'um-wpjm' );
		$actions['wpjm-job-filled'] = __( 'Job is filled', 'um-wpjm' );
		return $actions;
	}

	/**
	 * Add new user activity post
	 *
	 * @param array $user_id
	 */
	function social_activity_new_user( $user_id ) {
		do_action( 'um_after_user_is_approved', $user_id );
	}


	/**
	 * Maybe auto-verify user after registration on posting job
	 * based on UM role settings
	 *
	 * @param $user_id
	 */
	function maybe_verify( $user_id ) {
		if ( function_exists( 'um_verified_registration_complete' ) ) {
			um_verified_registration_complete( $user_id );
		}
	}

	/**
	 *
	 * @param int $job_id
	 */
	function um_activity_new_wpjm_job( $job_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( get_post_type( $job_id ) != 'job_listing' || get_post_status( $job_id ) != 'publish' ) {
			return;
		}

		if ( ! UM()->options()->get( 'activity-new-wpjm-job' ) ) {
			return;
		}

		$job = get_post( $job_id );

		$user_id = $job->post_author;

		// avoid double posts
		$already_posted = get_user_meta( $user_id, 'um_activity_published_job_' . $job_id );
		if ( ! empty( $already_posted ) ) {
			return;
		}

		um_fetch_user( $user_id );
		$author_name = um_user( 'display_name' );
		$author_profile = um_user_profile_url();
		$job_location = get_the_job_location( $job_id );

		$file = um_wpjm_path . '/templates/activity/new-wpjm-job.php';
			$theme_file = get_stylesheet_directory() . '/ultimate-member/um-wpjm/activity/new-wpjm-job.php';
			if ( file_exists( $theme_file ) ) {
				$file = $theme_file;
			}

		$post_id = UM()->Activity_API()->api()->save(
			array(
				'template'          => 'new-wpjm-job',
				'custom_path'       => $file,
				'wall_id'           => 0,
				'author'            => $user_id,
				'author_name'       => $author_name,
				'author_profile'    => $author_profile,
				'post_title'        => '<span class="post-title">' . $job->post_title . '</span><span class="post-meta">' . $job_location . '</span>',
				'post_url'          => get_permalink( $job ),
				'post_excerpt'      => '<span class="post-excerpt">' . $job->post_content . '</span>',
			)
		);

		update_user_meta( $user_id, 'um_activity_published_job_' . $job_id, $post_id );
	}

	/**
	 * @param int $job_id
	 */
	function um_activity_wpjm_delete_job( $job_id ) {
		$post = get_post( $job_id );

		if ( $post->post_type != 'job_listing' ) {
			return;
		}

		global $wpdb;
		$published_job_post = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
				FROM {$wpdb->usermeta} 
				WHERE meta_key = %s",
			'um_activity_published_job_' . $job_id
		) );

		if ( ! empty( $published_job_post ) ) {
			wp_delete_post( $published_job_post->meta_value );
			delete_user_meta( $published_job_post->user_id, 'um_activity_published_job_' . $job_id );
		}

		$filled_job_post = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
				FROM {$wpdb->usermeta} 
				WHERE meta_key = %s",
			'um_activity_filled_job_' . $job_id
		) );

		if ( ! empty( $filled_job_post ) ) {
			wp_delete_post( $filled_job_post->meta_value );
			delete_user_meta( $filled_job_post->user_id, 'um_activity_filled_job_' . $job_id );
		}
	}

	/**
	 * Adds a notification type
	 *
	 * @param array $logs
	 *
	 * @return array
	 */
	function add_notifications( $logs ) {
		$logs['job_listing_approved'] = array(
			'title'         => __( 'Your job is approved', 'um-wpjm' ),
			'account_desc'  => __( 'When your job gets approved status', 'um-wpjm' ),
		);
		$logs['job_listing_expired'] = array(
			'title'         => __( 'Your job is expired', 'um-wpjm' ),
			'account_desc'  => __( 'When your job gets expired status', 'um-wpjm' ),
		);
		return $logs;
	}

	/**
	 * Send a web notification after user's job is approved
	 *
	 * @param int $job_id
	 * @param \WP_Post $job
	 */
	function um_notification_after_job_is_approved( $job_id, $job ) {
		$user_id = $job->post_author;
		um_fetch_user( $user_id );

		$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
		$vars['member'] = um_user( 'display_name' );
		$url = um_user_profile_url();
		$vars['notification_uri'] = $url;
		$vars['job_uri'] = get_permalink( $job );

		UM()->Notifications_API()->api()->store_notification( $user_id, 'job_listing_approved', $vars );
	}

	/**
	 * Send a web notification after user's job is expired
	 *
	 * @param $job_id
	 */
	function um_notification_after_job_is_expired( $job_id ) {
		$job = get_post( $job_id );

		if ( ! empty( $job ) && ! is_wp_error( $job ) ) {
			$user_id = $job->post_author;
			um_fetch_user( $user_id );

			$vars['photo'] = um_get_avatar_url( get_avatar( $user_id, 40 ) );
			$vars['member'] = um_user( 'display_name' );
			$url = um_user_profile_url();
			$vars['notification_uri'] = $url;
			$vars['job_uri'] = get_permalink( $job );

			UM()->Notifications_API()->api()->store_notification( $user_id, 'job_listing_expired', $vars );
		}
	}

	/**
	 * @param array $settings_fields
	 *
	 * @return array
	 */
	function add_messaging_settings( $settings_fields ) {
		$settings_fields[] = array(
			'id'        => 'job_show_pm_button',
			'type'      => 'checkbox',
			'label'     => __( 'Show messages button in individual job post', 'um-wpjm' ),
			'tooltip'   => __( 'Start private messaging with a job author.', 'um-wpjm' ),
		);

		return $settings_fields;
	}


	/**
	 * @param int $job_id
	 */
	public function add_private_message_button( $job_id ) {

		if ( empty( UM()->classes['um_messaging_main_api'] ) ) {
			return;
		}

		if ( ! UM()->options()->get( 'job_show_pm_button' ) ) {
			return;
		}

		$job = get_post( $job_id );

		if ( empty( $job ) || is_wp_error( $job ) ) {
			return;
		}

		if ( is_user_logged_in() && get_current_user_id() === (int) $job->post_author ) {
			return;
		}

		if ( version_compare( get_bloginfo( 'version' ), '5.4', '<' ) ) {
			echo do_shortcode( '[ultimatemember_message_button user_id="' . $job->post_author . '"]' );
		} else {
			echo apply_shortcodes( '[ultimatemember_message_button user_id="' . $job->post_author . '"]' );
		}
	}
}