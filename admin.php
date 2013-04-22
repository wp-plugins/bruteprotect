<?php 

add_action( 'admin_menu', 'bruteprotect_admin_menu' );

function bruteprotect_admin_menu() {
	add_submenu_page('plugins.php', __('BruteProtect'), __('BruteProtect'), 'manage_options', 'bruteprotect-config', 'bruteprotect_conf');
	$key = get_option('bruteprotect_api_key');
	$error = get_option('bruteprotect_error');
	if ( !$key && $_GET['page'] != 'bruteprotect-config' ) {
		function bruteprotect_warning() {
			echo "
			<div id='bruteprotect-warning' class='error fade'><p><strong>".__('BruteProtect is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">enter your BruteProtect API key</a> for it to work.  <a href="%1$s">Obtain a key for free</a>.'), "plugins.php?page=bruteprotect-config")."</p></div>
			";
		}
		add_action('admin_notices', 'bruteprotect_warning');
		return;
	} elseif($error && $_GET['page'] != 'bruteprotect-config') {
		function bruteprotect_invalid_key_warning() {
			echo "
			<div id='bruteprotect-warning' class='error fade'><p><strong>".__('There is a problem with your BruteProtect API key')."</strong> ".sprintf(__(' <a href="%1$s">Please correct the error</a>, your site will not be protected until you do.'), "plugins.php?page=bruteprotect-config")."</p></div>
			";
		}
		add_action('admin_notices', 'bruteprotect_invalid_key_warning');
		return;
	}
	
	if(function_exists('loginLockdown_install')) :
		function bruteprotect_ll_warning() {
			echo "
			<div id='bruteprotect-warning' class='updated fade'><p><strong>".__('Please de-activate Login Lockdown')."</strong> ".sprintf(__('It is not necessary to run both BruteProtect and Login Lockdown.  We recommend that you <a href="%1$s">deactivate Login Lockdown</a> now.'), "plugins.php")."</p></div>
			";
		}
		add_action('admin_notices', 'bruteprotect_warning');
		return;
	endif;
	
	if(function_exists('limit_login_setup')) :
		function bruteprotect_limlog_warning() {
			echo "
			<div id='bruteprotect-warning' class='updated fade'><p><strong>".__('Please de-activate Limit Login Attempts')."</strong> ".sprintf(__('It is not necessary to run both BruteProtect and Limit Login Attempts.  We recommend that you <a href="%1$s">deactivate Limit Login Attempts</a> now.'), "plugins.php")."</p></div>
			";
		}
		add_action('admin_notices', 'bruteprotect_limlog_warning');
		return;
	endif;
}


function bruteprotect_conf() {
	$host = brute_get_host();
	global $current_user;
	
	if (isset($_POST['brute_action']) && $_POST['brute_action'] == 'get_api_key' && is_email($_POST['email_address'])) {
		global $wp_version;
		
		$post_host = 'http://api.bruteprotect.com/get_key.php';
		$brute_ua = "WordPress/{$wp_version} | ";
		$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );
	
		$request['email'] = $_POST['email_address'];
		$request['site'] = $host;
	
		$args = array(
			'body' => $request,
			'user-agent' => $brute_ua,
			'httpversion'	=> '1.0',
			'timeout'		=> 15
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
	if (isset($_POST['brute_action']) && $_POST['brute_action'] == 'update_key') {
		update_option('bruteprotect_api_key', $_POST['brute_api_key']);
	}
	
	
	$key = get_option('bruteprotect_api_key');
	delete_option('bruteprotect_error');
	
	$response = brute_call();
	
	if($response['error'] == 'Invalid API Key' || $response['error'] == 'API Key Required') {
		$invalid_key = 'invalid';
	}
	if($response['error'] == 'Host match error') {
		$invalid_key = 'host';
	}
	?>
	<div class="wrap">
	<h2><?php _e('BruteProtect Configuration'); ?></h2>
	<?php if ($key && $invalid_key == 'invalid'): ?>
		<div class="error below-h2" id="message"><p><?php _e( '<strong>Invalid API Key!</strong> You have entered an invalid API key. Please copy and paste it from the email you have received, or request a new key.' ); ?></p></div>
	<?php endif ?>
	<?php if ($key && $invalid_key == 'host'): ?>
		<div class="error below-h2" id="message"><p><?php _e( '<strong>Invalid API Key!</strong> You have entered an API key which is not valid for this server.  Every site must have its own API key.' ); ?></p></div>
	<?php endif ?>
	<?php if ($invalid_key): ?>
	<div style="display: block; width: 500px; float: left; padding: 10px; border: 3px solid green; border-radius: 5px; background-color: #eaffd6; margin-right: 20px;">
		<h3 style="display: block;background-color: green;color: #fff;margin-top: -10px;padding: 10px;margin-left: -10px;margin-right: -10px;">I <em>need</em> an API key for BruteProtect</h3>
		<form action="" method="post">

			<?php if ($_GET['get_key'] == 'success'): ?>
				<strong style="font-size: 18px;">You have successfully requested an API key.  It should be arriving in your email shortly.<br /><br />Once you receive your key, you must enter it on this page to finish activating BruteProtect.</strong>
			<?php else : ?>

				<p>You must obtain an API key for every site you wish to protect with BruteProtect.  You will be generating a BruteProtect.com key for use on <strong><?php echo $host ?></strong>.  There is no cost for an BruteProtect key, and we will never sell your email.</p>
				
				<strong>Email Address</strong><br />
				<input type="text" name="email_address" value="<?php echo $current_user->user_email ?>" id="brute_get_api_key" style="font-size: 18px; border: 2px solid #ccc; padding: 4px; width: 450px;" />
				<input type="hidden" name="brute_action" value="get_api_key" />
				<input type="submit" value="Get an API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
			<?php endif ?>
	</form>
	</div>
	<?php else: ?>
		<div class="updated below-h2" id="message" style="border-color: green; color: green; background-color: #eaffd6;"><p><?php _e( '<strong>API key verified!</strong> Your BruteProtect account is active and your site is protected, you don\'t need to do anything else!' ); ?></p></div>
	<?php endif ?>
	
	<div style="display: block; width: 500px; float: left; padding: 10px; border: 3px solid #0649fe; border-radius: 5px; background-color: #cdf0fe;">
		<h3 style="display: block;background-color: #0649fe;color: #fff;margin-top: -10px;padding: 10px;margin-left: -10px;margin-right: -10px;">I <em>have</em> an API key for BruteProtect</h3>
	<form action="" method="post">
		<strong>Enter your key: </strong><br />
		<input type="text" name="brute_api_key" value="<?php echo get_option('bruteprotect_api_key') ?>" id="brute_api_key" style="font-size: 18px; border: 2px solid #ccc; padding: 4px; width: 450px;" />
		<input type="hidden" name="brute_action" value="update_key" />
		<input type="submit" value="Save API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
	</form>
	</div>
</div>
	<?php 
}