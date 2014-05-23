<?php
// provide a pro-linking form if the api key is not invalid
if ( false == $invalid_key ) : ?>
<?php if ( bruteprotect_is_pro() && get_user_meta( $current_user->ID, 'bruteprotect_user_linked', true ) ) : ?>
	<iframe src="<?php echo MYBP_URL . 'wp/dash/' . get_site_option( 'bruteprotect_site_id' ) . '/' . get_user_meta( $current_user->ID, 'bruteprotect_user_linked', true ); ?>" width="100%" height="780px" ></iframe>

<?php else: ?>
	<h3 class="orange">Step 3: Sign-in / Register with My BruteProtect</h3>

				<a href="<?php echo BRUTEPROTECT_PLUGIN_URL ?>/images/screen-welcome.jpg" target="_blank" class="welcomescreen">
				<img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>/images/screen-welcome-thumb.jpg"  alt="BruteProtect Dashboard Screenshot"  />
				<small><em>Click or tap to zoom</em></small>
				</a>

				


		<div class="loginbox">

	<p><em>Enter your My BruteProtect username / password to link this site to your account.</em></p>
	<?php if ( ! empty( $linking_status ) ) : ?>
		<p><strong><?php echo $linking_status; ?></strong></p>
	<?php endif; ?>
	<form action="" method="post" class="regform">
		<label for="email"><strong>Email:</strong>
		<input type="text" name="username"/>
		</label>

		<label for="password"><strong>Password:</strong>
		<input type="password" name="password"/>
		</label>
		<br/><br/>
		<input type="hidden" name="brute_action" value="link_owner_to_site"/>
		<input type="submit" value="Link Site" class="button orange"/>
		<a href="https://my.bruteprotect.com/login/register/" target="blank" class=" blue button reg">Register
			(offsite)</a>

		<div class="clr">&nbsp;</div>
	</form>

	</div><?php // login box ?>


<?php endif; ?>
<?php else : ?>

<h3 class="orange">Step 3: Sign-in / Register with My BruteProtect</h3>
<p class="complete">Please complete Steps 1 and 2 above, before you sign-in!</p>

<?php endif; ?>