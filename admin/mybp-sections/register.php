<?php global $is_linking, $linking_error, $current_user; ?>


<div id="bp_link">

    <?php if ( !empty( $linking_error ) ) : ?>
        <div class="error">
            <?php echo __( $linking_error ); ?>
        </div>
    <?php endif; ?>
    <div class="clearfix" data-equalizer>

        <div class="columns large-6" data-equalizer-watch>
            <h3 class="attn left">Access your site through the BruteProtect Dashboard, it's free
                <p>All you need is an access token, which you can generate below</p></h3>

            <form action="" method="post" class="regform" id="link_site_form">
                <label for="access_token"><strong>Access Token:</strong>
                    <input type="text" id="access_token" name="access_token"/>
                </label>
                <br>

                <?php $nonce_link_site = wp_create_nonce( 'brute_link_site' ); ?>
                <input type="hidden" name="brute_nonce" value="<?php echo $nonce_link_site; ?>"/>
                <input type="hidden" name="brute_action" value="link_owner_to_site">

                <input type="submit" value="Link this site" class="button orange right" id="link_site_button">

                <a href="<?php echo MYBP_URL; ?>dashboard/get_token" id="get_token"
                   style="display: inline-block; padding-top: 22px;"><em>Generate your access token</em></a>


            </form>


        </div><?php // lrg7 ?>

        <div class="columns large-6 linkcreds" data-equalizer-watch>

            <h3>Some quick instructions</h3>

            <p>Congrats, the <i class="fa fa-check-square "></i> up above means our free brute force protection service,
                BruteProtect Shield is working.</p>

            <p>Your access token is only required to connect this website to <a href="http://my.bruteprotect.com"
                                                                                target="_blank">my.bruteprotect.com</a>,
                giving you free access to our pro services, and control of your website from within the BruteProtect
                dashboard.</p>

            <p>Registration at <a href="http://my.bruteprotect.com" target="_blank">my.bruteprotect.com</a> through
                WordPress.com is required; just click "Generate your access token" to get started.</p>

        </div><?php // lrg5 ?>

    </div><?php // clearfix ?>
    <script>
        jQuery(window).load(function () {
            function validate_access_token() {
                var input_access_token = jQuery('#access_token').val();
                alert(input_access_token);
                return false;
            }

            jQuery("#get_token").click(function (e) {
                e.preventDefault();
                open_token_popup();
            });
            jQuery("#link_site_button").click(function (e) {
                e.preventDefault();
                var access_token = jQuery("#access_token").val();
                if (access_token == "") {
                    open_token_popup();
                } else {
                    jQuery("#link_site_form").submit();
                }
            });
        });


        function open_token_popup() {
            var url = jQuery("#get_token").attr("href");
            var window_name = "get_token";
            var left = (screen.width / 2) - (540 / 2);
            var top = (screen.height / 2) - (380 / 2);
            var specs = "width=540, height=380, location=0, menubar=0, resizable=1, scrollbars=1, status=0, toolbar=0, " +
                "top=" + top + ", left=" + left;
            window.open(url, window_name, specs);
        }
    </script>
</div>