<?php 

if(isset($_GET['bruteprotect_checkin']))
	bruteprotect_checkin();

function bruteprotect_checkin() {
	$ckval = get_site_option('bruteprotect_ckval');

	if(!$ckval || $ckval != $_GET['bruteprotect_checkin']) {
		return false;
	}

	if ( !function_exists( 'get_plugin_data' ) ) {
	    require_once ABSPATH . 'wp-admin/includes/admin.php';
	}

	$plugins = get_site_option('active_plugins');
		$t = plugin_dir_path(__FILE__);
	
		$t = preg_replace('/'. preg_quote('bruteprotect/', '/') . '$/', '', $t);

	if(is_array($plugins)) :  foreach($plugins as $pfile) :
		$pf = $t.$pfile;
		$pl['shortname'] = trim(plugin_dir_path($pfile), '/');
		$p = get_plugin_data($pf);
		$pl['name'] = $p['Name'];
		$pl['version'] = $p['Version'];
		$pls[] = $pl;
	endforeach; endif;
	$o['version'] = get_bloginfo('version');
	$o['plugins'] = $pls;
	if(username_exists('admin')) { $o['has_admin_user'] = 1; }
	echo json_encode($o);
	exit;
}