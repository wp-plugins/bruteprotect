<?php
global $privacy_opt_in, $remote_security_options, $local_host, $bruteprotect_api_key;
global $register_error, $linking_error, $linking_success;
global $privacy_success;
global $wordpress_success;
global $whitelist_success;
$brute_ip_whitelist = get_site_option( 'brute_ip_whitelist' );
$admins             = get_site_option( 'brute_dashboard_widget_admin_only' );
$iframe_url         = get_mybp_iframe_url( $privacy_opt_in );
include( 'header.php' );
?>

<div id="bruteapi" class="new_ui row">

<header class="uiheader clearfix" data-equalizer>

	<div class="columns large-9 medium-8 small-12 logogroup" data-equalizer-watch>

		<h2 class="status">
			<div class="logo">
				<img src="<?PHP echo MYBP_URL; ?>assets/images/bruteprotect-dark.png" alt="BruteProtect">
				<span class="msg"><i class="fa fa-check-square "></i> <span>BruteProtect is working</span></span>
			</div><?php // logo ?>
		</h2>

	</div>
	<!-- // logogroup -->

	<div class="columns large-3 medium-4 small-12 btngroup" data-equalizer-watch>
		<?php if ( ! empty( $iframe_url ) ) : ?>
			<form action="" method="post" class="regform" id="disconnect_bp">
				<input type="hidden" name="brute_action" value="unlink_owner_from_site"/>
				<input type="submit" value="Disconnect Site" class="button" id="disconnect_bp_button"/>
			</form>
			<script>
				jQuery(document).ready(function () {
					jQuery("#disconnect_bp_button").click(function (e) {
						e.preventDefault();
						var d = confirm("Are you sure you want to disconnect this site from your my.bruteprotect.com account? BruteProtect Shield will still be active.");
						if (d) {
							jQuery("#disconnect_bp").submit();
						}
					});
				});
			</script>
		<?php endif; ?>

	</div>
	<!-- // btngroup -->

</header>


<div class="brutecontainer columns large-12 finalstep">


<div class="hover apipanel" data-equalizer data-equalizer-watch>

<div class="front" data-equalizer-watch>
<div class="frontinner" data-equalizer-watch>

<div id="horizontalTab">
	<ul>
		<li><a href="#tab-1">My BruteProtect <i class="fa fa-plus"></i><i class="fa fa-minus"></i></a></li>
		<li><a href="#tab-2">API &amp; Privacy Settings <i class="fa fa-plus"></i><i class="fa fa-minus"></i></a></li>
		<li><a href="#tab-3">Whitelist IPs <i class="fa fa-plus"></i><i class="fa fa-minus"></i></a></li>
		<li><a href="#tab-4">WordPress Settings <i class="fa fa-plus"></i><i class="fa fa-minus"></i></a></li>
	</ul>


	<div id="tab-1" class="dashholder">


		<div class="step a3 clearfix">


			<?php if ( false == $iframe_url ) : ?>

				<?php include( 'register.php' ); ?>

			<?php else : ?>

				<?php if ( ! empty( $linking_success ) ) : ?>
					<div class="alert-box success">
						<?php _e( $linking_success ); ?>
					</div>
				<?php endif; ?>

				<iframe src="<?php echo $iframe_url; ?>" width="100%" height="780"></iframe>

			<?php endif; ?>

		</div> <?php // end step ?>


	</div> <?php // tab 1 ?>


	<div id="tab-2" class="apioptions">

		<?php if ( ! empty( $privacy_success ) ) : ?>
			<div class="alert-box success">
				<?php _e( $privacy_success ); ?>
			</div>
		<?php endif; ?>

		<h3 class="attn">API Key for: <em><?php echo $local_host; ?></em></h3>


		<form action="" method="post" class="apiholder clearfix" id="remove_api_key_form">
			<input type="text" name="brute_api_key" value="<?php echo $bruteprotect_api_key; ?>" id="brute_api_key"
			       disabled="disabled"/>
			<input type="hidden" name="brute_action" value="remove_key"/>
			<input type="submit" value="Remove API Key" class="button green alignright" id="remove_api_key_button"/>

			<script>
				jQuery(document).ready(function () {
					jQuery("#remove_api_key_button").click(function (e) {
						e.preventDefault();
						var d = confirm("Removing your API key will remove any pro features you have as well as brute force protection. \n\n You can generate a new key in the future.\n\nAre you sure you want to remove your API key?");
						if (d) {
							jQuery("#remove_api_key_form").submit();
						}
					});
				});
			</script>
		</form>


		<h3 class="attn">Privacy Settings</h3>


		<form action="#tab-2" method="post" accept-charset="utf-8" id="bp-settings-form">

			<input type="hidden" name="privacy_opt_in[submitted]" value="1" id="privacy_opt_in[submitted]">

			<div class="row checkrow" data-equalizer>

				<?php if ( is_array( $remote_security_options ) ) : ?>
					<?php foreach ( $remote_security_options as $key => $desc ) : ?>
						<?php $checked = ( isset( $privacy_opt_in[ $key ] ) ) ? 'checked="checked"' : ''; ?>
						<div class="row checkrow" data-equalizer>

							<div class="columns large-1 medium-12 small-12 checkholder" data-equalizer-watch>
								<input name="privacy_opt_in[<?php echo $key; ?>]" type="checkbox" value="1"
								       class="bp_privacy_opt_in_checkbox" <?php echo $checked; ?> />
							</div><?php // lrg-1 ?>

							<div class="columns large-11 medium-12 small-12" data-equalizer-watch>
								<label for="privacy_opt_in[<?php echo $key; ?>]"
								       class="setting"><?php echo $desc; ?></label>
							</div><?php // lrg-11 ?>

						</div><!-- row -->
					<?php endforeach; ?>
				<?php endif; ?>

			</div>
			<!-- row -->

			<div class="row">
				<input type="submit" value="Save Settings" class="right permission">
			</div>
			<!-- row -->

		</form>


	</div> <?php // tab 2 ?>



	<div id="tab-3" class="whitelistoptions">

		<?php if ( ! empty( $whitelist_success ) ) : ?>
			<div class="alert-box success">
				<?php _e( $whitelist_success ); ?>
			</div>
		<?php endif; ?>

		<h3 class="attn">IP Whitelist: Always allow access from the following IP's</h3>


		<h4>Your current IP address is: <strong><?php echo $this->brute_get_ip(); ?></strong></h4>

		<p>Enter one IPv4 per line, * for wildcard octet<br/>
			<em>(ie: <code>192.168.0.1</code>
				and <code>192.168.*.*</code> are valid, <code>192.168.*</code> and <code>192.168.*.1</code> are invalid)</em>
		</p>

		<form action="#tab-3" method="post" class="clearfix">

			<textarea name="brute_ip_whitelist" class="ipholder"><?php echo $brute_ip_whitelist ?></textarea>

			<input type="hidden" name="brute_action" value="update_brute_whitelist"><br>
			<input type="submit" value="Save IP Whitelist" class="button blue alignright">
		</form>


	</div><?php // tab 3 ?>



	<div id="tab-4" class="bpwpoptions clearfix">
		<?php if ( ! empty( $wordpress_success ) ) : ?>
			<div class="alert-box success">
				<?php _e( $wordpress_success ); ?>
			</div>
		<?php endif; ?>
		<h3 class="attn">BruteProtect dashboard widget should be visible to...</h3>


		<form action="#tab-4" method="post" accept-charset="utf-8" id="bp-settings-form">
			<select name="brute_dashboard_widget_admin_only" id="brute_dashboard_widget_admin_only">
				<option value="0" <?php if ( $admins == '0' ) {
					echo 'selected="selected"';
				} ?>>All users who can see the dashboard
				</option>
				<option value="1" <?php if ( $admins == '1' ) {
					echo 'selected="selected"';
				} ?>>Admins Only
				</option>
			</select>
			<input type="hidden" name="brute_action" value="general_update" id="brute_action">
			<input type="submit" value="Save Changes" class="button button-primary blue alignright">
		</form>

	</div> <?php // tab 4 ?>





</div> <?php // horizontalTab ?>

<!-- put these in the footer file -->
<script src="<?PHP echo MYBP_URL; ?>assets/js/jquery.responsiveTabs.min.js" type="text/javascript"></script>

<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#horizontalTab').responsiveTabs({
		});

		jQuery('#start-rotation').on('click', function () {
			jQuery('#horizontalTab').responsiveTabs('active');
		});
		jQuery('#stop-rotation').on('click', function () {
			jQuery('#horizontalTab').responsiveTabs('stopRotation');
		});
		jQuery('#start-rotation').on('click', function () {
			jQuery('#horizontalTab').responsiveTabs('active');
		});
		jQuery('.select-tab').on('click', function () {
			jQuery('#horizontalTab').responsiveTabs('activate', jQuery(this).val());
		});

	});
</script>


</div><?php // frontinner ?>
</div> <?php // front of flip ?>

</div> <?php // hover ?>


</div>
<!-- // brute container -->
</div> <!-- // brute api -->
