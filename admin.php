<?php

/////////////////////////////////////////////////////////////////////
// Admin Dashboard Widget
/////////////////////////////////////////////////////////////////////

add_action( 'wp_dashboard_setup', 'bruteprotect_dashboard_widgets' );
add_action( 'wp_network_dashboard_setup', 'bruteprotect_dashboard_widgets' );

function bruteprotect_dashboard_widgets() {
	
	if(is_multisite() && !is_network_admin()) {
		$brute_dashboard_widget_hide = get_site_option('brute_dashboard_widget_hide');
		if($brute_dashboard_widget_hide == 1) { return; }
	}
	
	global $wp_meta_boxes;
	wp_add_dashboard_widget( 'bruteprotect_dashboard_widget', 'BruteProtect Stats', 'bruteprotect_dashboard_widget' );
}

function bruteprotect_dashboard_widget() {
	$key = get_site_option( 'bruteprotect_api_key' );
	$ckval = get_site_option( 'bruteprotect_ckval' );

	if( $key && !$ckval ) {
		$response = brute_call( 'check_key' );

		if( $response['ckval'] )
			update_site_option( 'bruteprotect_ckval', $response['ckval'] );
	}

	$stats = wp_remote_get( get_bruteprotect_host() . "get_stats.php?key=" . $key );

	if( !is_wp_error( $stats ) ) {
		print_r( $stats['body'] );
		return;
	}
}

function bruteprotect_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__) . '/bruteprotect.php' ) )
		$links[] = '<a href="' . esc_url( admin_url( 'plugins.php?page=bruteprotect-config' ) ) . '">' . __( 'Settings' ) . '</a>';

	return $links;
}
add_filter( 'plugin_action_links', 'bruteprotect_plugin_action_links', 10, 2 );


add_action( 'admin_menu', 'bruteprotect_admin_menu_non_multisite' );
add_action( 'network_admin_menu', 'bruteprotect_admin_menu' );

function bruteprotect_admin_menu_non_multisite() {
	if(is_multisite()) {
		add_submenu_page( 'plugins.php', __( 'BruteProtect' ), __( 'BruteProtect' ), 'manage_options', 'bruteprotect-config', 'bruteprotect_conf_ms_notice' );
		return;
	}
	bruteprotect_admin_menu();
}
function bruteprotect_admin_menu() {
	add_submenu_page( 'plugins.php', __( 'BruteProtect' ), __( 'BruteProtect' ), 'manage_options', 'bruteprotect-config', 'bruteprotect_conf' );

	$key = get_site_option( 'bruteprotect_api_key' );
	$error = get_site_option( 'bruteprotect_error' );

	if ( !$key && ( isset( $_GET['page'] ) && 'bruteprotect-config' != $_GET['page'] ) ) {
		function bruteprotect_warning() {
			echo "
			<div id='bruteprotect-warning' class='error fade'><p><strong>" . __( 'BruteProtect is almost ready.' ) . "</strong> " . sprintf( __( 'You must <a href="%1$s">enter your BruteProtect API key</a> for it to work.  <a href="%1$s">Obtain a key for free</a>.' ), esc_url( admin_url( 'plugins.php?page=bruteprotect-config' ) ) ) . "</p></div>
			";
		}
		add_action( 'admin_notices', 'bruteprotect_warning' );
		return;
	} elseif ( $error && $_GET['page'] != 'bruteprotect-config' ) {
		function bruteprotect_invalid_key_warning() {
			echo "
			<div id='bruteprotect-warning' class='error fade'><p><strong>" . __( 'There is a problem with your BruteProtect API key' ) . "</strong> " . sprintf( __( ' <a href="%1$s">Please correct the error</a>, your site will not be protected until you do.' ), esc_url( admin_url( 'plugins.php?page=bruteprotect-config' ) ) )."</p></div>
			";
		}
		add_action( 'admin_notices', 'bruteprotect_invalid_key_warning' );
		return;
	}

	if ( function_exists( 'loginLockdown_install' ) ) {
		function bruteprotect_ll_warning() {
			echo "
			<div id='bruteprotect-warning' class='updated fade'><p><strong>" . __( 'Please de-activate Login Lockdown' ) . "</strong> " . sprintf( __( 'It is not necessary to run both BruteProtect and Login Lockdown.  We recommend that you <a href="%1$s">deactivate Login Lockdown</a> now.' ), esc_url( admin_url( 'plugins.php' ) ) ) . "</p></div>
			";
		}
		add_action( 'admin_notices', 'bruteprotect_ll_warning' );
		return;
	}

	if ( function_exists( 'limit_login_setup' ) ) {
		function bruteprotect_limlog_warning() {
			echo "
			<div id='bruteprotect-warning' class='updated fade'><p><strong>" . __( 'Please de-activate Limit Login Attempts' ) . "</strong> " . sprintf( __( 'It is not necessary to run both BruteProtect and Limit Login Attempts.  We recommend that you <a href="%1$s">deactivate Limit Login Attempts</a> now.' ), esc_url( admin_url( 'plugins.php' ) ) ) . "</p></div>
			";
		}
		add_action( 'admin_notices', 'bruteprotect_limlog_warning' );
		return;
	}
}


function bruteprotect_conf() {
	$host = brute_get_host();
	global $current_user;

	if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'get_api_key' && is_email( $_POST['email_address'] ) ) {
		global $wp_version;

		$post_host = get_bruteprotect_host() . '/get_key.php';
		$brute_ua = "WordPress/{$wp_version} | ";
		$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );

		$request['email'] = $_POST['email_address'];
		$request['site'] = $host;

		$args = array(
			'body'        => $request,
			'user-agent'  => $brute_ua,
			'httpversion' => '1.0',
			'timeout'     => 15
		);

		$response_json = wp_remote_post( $post_host, $args );

		?>
		<script type="text/javascript">
		<!--
		window.location = "plugins.php?page=bruteprotect-config&get_key=success"
		//-->
		</script>
		<?php
		exit;
	}

	if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_key' )
		update_site_option( 'bruteprotect_api_key', $_POST['brute_api_key'] );
	
	if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_brute_dashboard_widget_settings' )
		update_site_option( 'brute_dashboard_widget_hide', $_POST['brute_dashboard_widget_hide'] );
	
	$brute_dashboard_widget_hide = get_site_option('brute_dashboard_widget_hide');

	$key = get_site_option( 'bruteprotect_api_key' );
	$invalid_key = false;
	delete_site_option( 'bruteprotect_error' );

	$response = brute_call( 'check_key' );

	if( $response['error'] == 'Invalid API Key' || $response['error'] == 'API Key Required' )
		$invalid_key = 'invalid';

	if( $response['error'] == 'Host match error' )
		$invalid_key = 'host';

	if( $response['ckval'] )
		update_site_option( 'bruteprotect_ckval', $response['ckval'] );
	?>
<div class="wrap">
	<h2 style="clear: both; margin-bottom: 15px;"><img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; Configuration Options</h2>

	<?php if ( false != $key && $invalid_key == 'invalid' ) : ?>
		<div class="error below-h2" id="message"><p><?php _e( '<strong>Invalid API Key!</strong> You have entered an invalid API key. Please copy and paste it from the email you have received, or request a new key.' ); ?></p></div>
	<?php endif ?>

	<?php if ( false != $key && $invalid_key == 'host' ) : ?>
		<div class="error below-h2" id="message"><p><?php _e( '<strong>Invalid API Key!</strong> You have entered an API key which is not valid for this server.  Every site must have its own API key.' ); ?></p></div>
	<?php endif ?>

	<?php if ( false != $invalid_key ) : ?>
		<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid green; background-color: #eaffd6; margin-right: 20px; margin-bottom:20px;">
			<h3 style="display: block; background-color: green; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;">I <em>need</em> an API key for BruteProtect</h3>
			<form action="" method="post">
			<?php if ( $_GET['get_key'] == 'success' ) : ?>
				<strong style="font-size: 18px;"><?php _e( 'You have successfully requested an API key.  It should be arriving in your email shortly.<br /><br />Once you receive your key, you must enter it on this page to finish activating BruteProtect.' ); ?></strong>

			<?php else : ?>

				<p><?php _e( 'You must obtain an API key for every site or network you wish to protect with BruteProtect.  You will be generating a BruteProtect.com key for use on <strong><?php echo $host ?></strong>.  There is no cost for an BruteProtect key, and we will never sell your email.' ); ?></p>

				<strong><?php _e( 'Email Address' ); ?></strong><br />
				<input type="text" name="email_address" value="<?php echo $current_user->user_email ?>" id="brute_get_api_key" style="font-size: 18px; border: 1px solid #ccc; padding: 4px; width: 450px;" />
				<input type="hidden" name="brute_action" value="get_api_key" />
				<input type="submit" value="Get an API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
			<?php endif; ?>
			</form>
		</div>
	<?php else : ?>
		<div class="updated below-h2" id="message" style="border-color: green; color: green; background-color: #eaffd6;"><p><?php _e( '<strong>API key verified!</strong> Your BruteProtect account is active and your site is protected, you don\'t need to do anything else!' ); ?></p></div>
	<?php endif; ?>

	<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #0649fe; background-color: #cdf0fe;">
		<h3 style="display: block; background-color: #0649fe; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'I <em>have</em> an API key for BruteProtect' ); ?></h3>
		<form action="" method="post">
			<strong><?php _e( 'Enter your key: ' ); ?></strong><br />
			<input type="text" name="brute_api_key" value="<?php echo get_site_option('bruteprotect_api_key') ?>" id="brute_api_key" style="font-size: 18px; border: 1px solid #ccc; padding: 4px; width: 450px;" />
			<input type="hidden" name="brute_action" value="update_key" />
			<input type="submit" value="Save API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
		</form>
	</div>
	
	<?php if (is_multisite()): ?>
		<br class="clear" />
		<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #ccc; background-color: #e5e5e5; margin-top: 30px;">
			<h3 style="display: block; background-color: #555; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'Dashboard Widget Display' ); ?></h3>
			<form action="" method="post">
				<strong><?php _e( 'Display BruteProtect statistics: ' ); ?></strong><br />
				<select name="brute_dashboard_widget_hide" id="brute_dashboard_widget_hide">
					<option value="0">On network admin dashboard and on all blog dashboards</option>
					<option value="1" <?php if (isset($brute_dashboard_widget_hide) && $brute_dashboard_widget_hide == 1) { echo 'selected="selected"'; } ?>>On network admin dashboard only</option>
				</select>
				<input type="hidden" name="brute_action" value="update_brute_dashboard_widget_settings" /><br />
				<input type="submit" value="Save API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
			</form>
		</div>
	<?php endif ?>
	
</div>
	<?php
}

function bruteprotect_conf_ms_notice() {
	?>
	<div class="wrap">
		<h2 style="clear: both; margin-bottom: 15px;"><img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; Configuration Options</h2>
		<p style="font-size: 18px; padding-top: 20px;">
		<?php if (current_user_can('manage_network')): ?>
			<strong>BruteProtect only needs one API key per network.</strong>  <a href="<?php echo network_home_url('/wp-admin/network/plugins.php?page=bruteprotect-config') ?>">Manage your key here.</a>
		<?php else: ?>
			<strong>Sorry!</strong> Only super admins can configure BruteProtect.
		<?php endif ?>
		</p>
	</div>
	<?php 
}