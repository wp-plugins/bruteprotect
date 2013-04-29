<?php
/**
 * @package Brute Protect
 */
/*
Plugin Name: Brute Protect
Plugin URI: http://bruteprotect.com/
Description: Brute Protect allows the millions of WordPress bloggers to work together to defeat Brute Force attacks. It keeps your site protected from brute force security attacks even while you sleep. To get started: 1) Click the "Activate" link to the left of this description, 2) Sign up for a Brute Protect API key, and 3) Go to your Brute Protect configuration page, and save your API key.
Version: 0.9.2
Author: Hotchkiss Consulting Group
Author URI: http://hotchkissconsulting.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('BRUTEPROTECT_VERSION', '1.0');
define('BRUTEPROTECT_PLUGIN_URL', plugin_dir_url( __FILE__ ));

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

add_action('login_head', 'brute_check_loginability');
add_action('login_head', 'brute_check_use_math');
add_action('wp_authenticate', 'brute_check_preauth', 1);
add_action('wp_login_failed', 'brute_log_failed_attempt');

//Make sure that they didn't try to sneak past the login form...
function brute_check_preauth($username) {
	brute_check_loginability(true);
	$bum = get_transient('brute_use_math');
	if(1 == $bum && isset($_POST['log'])) {
		if(false == function_exists('brute_math_authenticate'))
			include 'math-fallback.php';
		brute_math_authenticate();
	}
}

function brute_check_loginability($preauth = false) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_name = 'brute_loginable_'.str_replace('.', '_', $ip);
	$transient_value = get_transient( $transient_name );
	
	//This IP has been OKed, proceed to login
	if(isset($transient_value) && $transient_value['status'] == 'ok') { return true; }
	
	if(isset($transient_value) && $transient_value['status'] == 'blocked') { 
		if($transient_value['expire'] < time()) {
			//the block is expired but the transient didn't go away naturally, clear it out and allow login.
			delete_transient($transient_name);
			return true;
		}
		//there is a current block-- prevent login
		brute_kill_login();
	}
		
	//If we've reached this point, this means that the IP isn't cached.
	//Now we check with the bruteprotect.com servers to see if we should allow login
	$response = brute_call($action = 'check_ip');
	
	if(isset($response['math']) && false == function_exists('brute_math_authenticate')) {
		include 'math-fallback.php';
	}
	
	if($response['status'] == 'blocked') { brute_kill_login(); }
	
	return true;
}
function brute_check_use_math() {
	$bum = get_transient('brute_use_math');
	
	if($bum && !function_exists('brute_math_authenticate')) {
		include 'math-fallback.php';
	}
}

function brute_kill_login() {
	wp_die('Your IP ('.$_SERVER['REMOTE_ADDR'].') has been flagged for potential security violations.  Please try again in a little while...');
}

function brute_log_failed_attempt() {
	brute_call('failed_attempt');
}

function brute_get_host() {
	return preg_replace('#^www\.(.+\.)#i', '$1', $_SERVER['HTTP_HOST']);
}

function brute_call($action = 'check_ip') {
	global $wp_version;
		
	$api_key = get_option('bruteprotect_api_key');

	$host = 'http://api.bruteprotect.com/';
	$brute_ua = "WordPress/{$wp_version} | ";
	$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );
	
	$request['action'] = $action;
	$request['ip'] = $_SERVER['REMOTE_ADDR'];
	$request['host'] = brute_get_host();
	$request['api_key'] = $api_key;
	
	$args = array(
		'body' => $request,
		'user-agent' => $brute_ua,
		'httpversion'	=> '1.0',
		'timeout'		=> 15
	);
	
	$response_json = wp_remote_post( $host, $args );
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_name = 'brute_loginable_'.str_replace('.', '_', $ip);
	delete_transient($transient_name);
	
	if(is_array($response_json))
		$response = json_decode($response_json['body'], true);

	if(isset($response['status'])) :
		$response['expire'] = time() + $response['seconds_remaining'];
		set_transient($transient_name, $response, $response['seconds_remaining']);
		delete_transient('brute_use_math');
		
	else :
		//no response from the API host?  Let's use math!
		set_transient('brute_use_math', 1, 600);
		$response['status'] = 'ok';
		$response['math'] = true;
	endif;
	
	if($response['error']) :
		update_option('bruteprotect_error', $response['error']);
	endif;
	
	return $response;
}