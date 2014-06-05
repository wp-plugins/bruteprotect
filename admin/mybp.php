<?php
/**
 * Displays the 3 step configuration page for BruteProtect and connection to my.bruteprotect.com
 *
 * @package BruteProtect
 *
 * @since 1.0
 */
$host = $this->brute_get_local_host();
global $current_user;

//Load in options...
$remote_security_options = array(
	'remote_monitoring' => __( 'Remotely monitor your site uptime and scan for malware; Remotely track the versions of WordPress, plugins, & themes you have installed; Remotely update your site' ),
	'remote_login'      => __( 'Provide a secure login gateway for your site' ),
);

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	require 'mybp-post_processing.php';
}

$key         = get_site_option( 'bruteprotect_api_key' );
$invalid_key = false;
delete_site_option( 'bruteprotect_error' );
$response = $this->brute_call( 'check_key' );
if ( isset( $response['error'] ) ) :
	if ( $response['error'] == 'Invalid API Key' || $response['error'] == 'API Key Required' ) :
		$invalid_key = 'invalid';
	endif;
	if ( $response['error'] == 'Host match error' ) :
		$invalid_key = 'host';
	endif;
endif;

if ( ! $this->check_bruteprotect_access() ) : //server cannot access API
	$invalid_key = 'server_access';
endif;
bruteprotect_save_pro_info( $response );

$brute_dashboard_widget_hide       = get_site_option( 'brute_dashboard_widget_hide' );
$brute_dashboard_widget_admin_only = get_site_option( 'brute_dashboard_widget_admin_only' );
$privacy_opt_in                    = get_site_option( 'brute_privacy_opt_in' );

$key         = get_site_option( 'bruteprotect_api_key' );
$invalid_key = false;
delete_site_option( 'bruteprotect_error' );

$response = $this->brute_call( 'check_key' );

if ( isset( $response['error'] ) ) :
	if ( $response['error'] == 'Invalid API Key' ) :
		$invalid_key = 'invalid';
	endif;
	if ( $response['error'] == 'Host match error' ) :
		$invalid_key = 'host';
	endif;
	if ( $response['error'] == 'API Key Required' ) :
		$invalid_key = 'missing';
	endif;
endif;

if ( ! $this->check_bruteprotect_access() ) : //server cannot access API
	$invalid_key = 'server_access';
endif;

$is_subdomain_install = false;
$wp_site_url = get_site_url();
$wp_site_url_parts = parse_url( $wp_site_url );
if( isset( $wp_site_url_parts ) && is_array( $wp_site_url_parts ) && $wp_site_url_parts[ 'path' ] && $wp_site_url_parts[ 'path' ] != '/' ) {
	$is_subdomain_install = true;
}

bruteprotect_save_pro_info( $response );

$bruteprotect_step1 = false;
$bruteprotect_step2 = false;
$bruteprotect_1_and_2 = false;

if ( false == $invalid_key ) {
	$bruteprotect_step1 = true;
}
if( is_array($privacy_opt_in) && isset( $privacy_opt_in['remote_monitoring'] )) {
	$bruteprotect_step2 = true;
}
if ( $bruteprotect_step1 && $bruteprotect_step2 ) {
	$bruteprotect_1_and_2 = true;
}

$is_privacy_saved = false;
if ( $privacy_opt_in && in_array( true, $privacy_opt_in ) ) { $is_privacy_saved = true; } 

$brute_ip_whitelist = get_site_option( 'brute_ip_whitelist' );

include 'mybp-sections/privacy_update.php';


/////////////////////////////////////////////////////////////////////
// 
// START Output
// 
/////////////////////////////////////////////////////////////////////
?>


<div class="wrap">
<div id="bruteapi">

<h2 id="header">

	<?php if ( ! is_multisite() && ! $is_subdomain_install ) : ?><a href="http://support.bruteprotect.com/" target="_blank" class="right orange button">Get Support</a><a href="https://my.bruteprotect.com" target="_blank" class="right blue button">Go to My BruteProtect</a><?php endif; ?>
	<img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect"
	     width="250"> &nbsp;
	Setup
</h2>
<div class="brutecontainer">
<?php if ( !$bruteprotect_1_and_2 ): // if the user hasn't completed steps 1 & 2 yet, put those first
	
if ( $invalid_key != false ) : // select which key form to show
	include 'mybp-sections/get_key.php'; 
else : 
	include 'mybp-sections/api_key_update.php'; 
endif; 

//now render the privacy settings
bruteprotect_privacyupdate_html( $remote_security_options, $privacy_opt_in, $is_privacy_saved );

echo '<div class="clear">&nbsp;</div>';

endif //end sections 1&2 ?>

	
<?php if ( ! is_multisite() && ! $is_subdomain_install ) : ?>
	<div class="box left clear framed">

		<?php include 'mybp-sections/link_site.php'; ?>

	<br clear="all" />

	</div> <!-- end box -->
	<br clear="all" />

<?php endif; /*multisite if */ 

if ( $bruteprotect_1_and_2 ): // if the user has completed steps 1 & 2 yet, put those last
	
if ( $invalid_key != false ) : // select which key form to show
	include 'mybp-sections/get_key.php'; 
else : 
	include 'mybp-sections/api_key_update.php'; 
endif; 

//now render the privacy settings
bruteprotect_privacyupdate_html( $remote_security_options, $privacy_opt_in, $is_privacy_saved );
echo '<br /><br clear="all" />';
endif; //end sections 1&2 

//whitelist settings
include 'mybp-sections/whitelist.php';

//visibility settings
include 'mybp-sections/visibility.php';

//clef settings
include 'mybp-sections/clef.php';

?>




<div class="clr">&nbsp;</div>


</div>
<!-- end brute container -->
</div>

<script>
	jQuery(document).ready(function ($) {
		$('#gotapikey').click(function () {
			$('.getapikey').hide();
			$('.gotapikey').show();
		});

		$('#getapikey').click(function () {
			$('.gotapikey').hide();
			$('.invalidkey').hide();
			$('.getnewkey').show();
			$('.getapikey').show();
		});

		var checked_boxes = jQuery('.bp_privacy_opt_in_checkbox:checked').length;
		if (checked_boxes == <?php echo count( $remote_security_options ) ?>) {
			jQuery("#bruteprotect_permissions_description").hide();
		}

	});
</script>