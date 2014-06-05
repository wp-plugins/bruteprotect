<?php

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'unlink_owner_from_site' ) {
	global $current_user;
	delete_user_meta($current_user->ID, 'bruteprotect_user_linked');
	$bp_users = get_bruteprotect_users();
	if( empty( $bp_users ) ) {
		delete_site_option('bruteprotect_user_linked');
	}
	
	$action = 'unlink_owner_from_site';
	$additional_data = array(
		'wp_user_id' => strval(count( $current_user->ID )),
	);
	$sign = true;

	$response = $this->brute_call( $action, $additional_data, $sign );
	add_action( 'admin_notices', 'brute_site_unlinked_notice' );
}

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'get_api_key' && is_email( $_POST['email_address'] ) ) {
	global $wp_version;

	$post_host = $this->get_bruteprotect_host() . 'endpoints/get_key';
	$brute_ua  = "WordPress/{$wp_version} | ";
	$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );

	$request['email'] = $_POST['email_address'];
	$request['site']  = $host;

	$args = array(
		'body'        => $request,
		'user-agent'  => $brute_ua,
		'httpversion' => '1.0',
		'timeout'     => 15
	);

	$response_json = wp_remote_post( $post_host, $args );

	if ( $response_json['response']['code'] == 200 ) {
		$key = $response_json['body'];
		update_site_option( 'bruteprotect_api_key', $key );
		?>
		<script type="text/javascript">
			<!--
			window.location = "admin.php?page=bruteprotect-config&get_key=success";
			//-->
		</script>
	<?php
	} else {
		?>
		<script type="text/javascript">
			<!--
			window.location = "admin.php?page=bruteprotect-config&get_key=fail";
			//-->
		</script>
	<?php
	}
	exit;
}

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_key' ) {
	update_site_option( 'bruteprotect_api_key', $_POST['brute_api_key'] );
}

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'link_owner_to_site' ) {
	$action = 'link_owner_to_site';
	$core_update = brute_protect_get_core_update();
	$plugin_updates = bruteprotect_get_out_of_date_plugins();
	$theme_updates = bruteprotect_get_out_of_date_themes();
	$additional_data = array(
		'username'  => $_POST['username'],
		'password'  => $_POST['password'],
		'remote_id' => strval( $current_user->ID ),
		'core_update' => $core_update,
		'plugin_updates' => strval(count( $plugin_updates )),
		'theme_updates' => strval(count( $theme_updates )),
	);
	$sign = true;

	$response = $this->brute_call( $action, $additional_data, $sign );
	
	if ( $response['link_key'] ) {
		update_user_meta( $current_user->ID, 'bruteprotect_user_linked', $response['link_key'] );
		update_site_option( 'bruteprotect_user_linked', '1' );
	}
	$linking_status = $response['message'];
}

// save privacy settings
if ( isset( $_POST['privacy_opt_in']['submitted'] ) ) {
	unset( $_POST['privacy_opt_in']['submitted'] );
	update_site_option( 'brute_privacy_opt_in', $_POST['privacy_opt_in'] );
	$action          = 'update_settings';
	$additional_data = array();
	$sign            = true;
	$this->brute_call( $action, $additiional_data, $sign );
}

// process an general_update action which updates privacy settings. uses Bruteprotect::call()
if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'general_update' && current_user_can( 'manage_options' ) ) {

	// save dashboard widget settings
	if ( isset( $_POST['brute_dashboard_widget_hide'] ) ) {
		update_site_option( 'brute_dashboard_widget_hide', $_POST['brute_dashboard_widget_hide'] );
	}
	// save dashboard widget settings
	if ( isset( $_POST['brute_dashboard_widget_admin_only'] ) ) {
		update_site_option( 'brute_dashboard_widget_admin_only', $_POST['brute_dashboard_widget_admin_only'] );
	}

}

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_brute_whitelist' ) {
	//check the whitelist to make sure that it's clean
	$whitelist = $_POST['brute_ip_whitelist'];

	$wl_items = explode( PHP_EOL, $whitelist );

	if ( is_array( $wl_items ) ) :  foreach ( $wl_items as $key => $item ) :
		$item   = trim( $item );
		$ckitem = str_replace( '*', '1', $item );
		$ckval  = ip2long( $ckitem );
		if ( ! $ckval ) {
			unset( $wl_items[ $key ] );
			continue;
		}
		$exploded_item = explode( '.', $item );
		if ( $exploded_item[0] == '*' ) {
			unset( $wl_items[ $key ] );
		}

		if ( $exploded_item[1] == '*' && ! ( $exploded_item[2] == '*' && $exploded_item[3] == '*' ) ) {
			unset( $wl_items[ $key ] );
		}

		if ( $exploded_item[2] == '*' && $exploded_item[3] != '*' ) {
			unset( $wl_items[ $key ] );
		}

	endforeach; endif;

	$whitelist = implode( PHP_EOL, $wl_items );

	update_site_option( 'brute_ip_whitelist', $whitelist );
}
