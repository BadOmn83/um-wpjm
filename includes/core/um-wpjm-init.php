<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_WPJM
 */
class UM_WPJM {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_WPJM
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_WPJM constructor.
	 */
	function __construct() {
		add_filter( 'plugins_loaded', [ &$this, 'init' ] );

		add_filter( 'um_call_object_WPJM', [ &$this, 'get_this' ] );
		add_filter( 'um_settings_default_values', [ &$this, 'default_settings' ], 10, 1 );
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @param $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * Init
	 */
	function init() {
		$this->account();
		$this->profile();
		$this->integrations();

		if ( is_admin() ) {
			$this->admin();
		}
	}


	/**
	 * @return um_ext\um_wpjm\core\Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_wpjm_setup'] ) ) {
			UM()->classes['um_wpjm_setup'] = new um_ext\um_wpjm\core\Setup();
		}
		return UM()->classes['um_wpjm_setup'];
	}


	/**
	 * @return um_ext\um_wpjm\core\Profile()
	 */
	function profile() {
		if ( empty( UM()->classes['um_wpjm_profile'] ) ) {
			UM()->classes['um_wpjm_profile'] = new um_ext\um_wpjm\core\Profile();
		}
		return UM()->classes['um_wpjm_profile'];
	}


	/**
	 * @return um_ext\um_wpjm\core\Account()
	 */
	function account() {
		if ( empty( UM()->classes['um_wpjm_account'] ) ) {
			UM()->classes['um_wpjm_account'] = new um_ext\um_wpjm\core\Account();
		}
		return UM()->classes['um_wpjm_account'];
	}


	/**
	 * @return um_ext\um_wpjm\core\Integrations()
	 */
	function integrations() {
		if ( empty( UM()->classes['um_wpjm_integrations'] ) ) {
			UM()->classes['um_wpjm_integrations'] = new um_ext\um_wpjm\core\Integrations();
		}
		return UM()->classes['um_wpjm_integrations'];
	}


	/**
	 * @return um_ext\um_wpjm\core\Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_wpjm_admin'] ) ) {
			UM()->classes['um_wpjm_admin'] = new um_ext\um_wpjm\core\Admin();
		}
		return UM()->classes['um_wpjm_admin'];
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_wpjm', -10, 1 );
function um_init_wpjm() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'WPJM', true );
	}
}