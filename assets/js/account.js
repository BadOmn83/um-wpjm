wp.hooks.addAction( 'um_after_account_tab_changed', 'um_wpjm', function( tab_ ) {
	if ( 'wpjm' === tab_ ) {
		jb_responsive();
	}
});

wp.hooks.addAction( 'um_account_active_tab_inited', 'um_wpjm', function( tab_ ) {
	if ( 'wpjm' === tab_ ) {
		jb_responsive();
	}
});