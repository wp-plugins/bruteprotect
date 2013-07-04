<?php
/**
 * @package Brute Protect
 */
/*
Plugin Name: Brute Protect
Plugin URI: http://bruteprotect.com/
Description: Brute Protect allows the millions of WordPress bloggers to work together to defeat Brute Force attacks. It keeps your site protected from brute force security attacks even while you sleep. To get started: 1) Click the "Activate" link to the left of this description, 2) Sign up for a Brute Protect API key, and 3) Go to your Brute Protect configuration page, and save your API key.
Version: 0.9.7.1
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

define('BRUTEPROTECT_VERSION', '0.9.7.1');
define('BRUTEPROTECT_PLUGIN_URL', plugin_dir_url( __FILE__ ));

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

add_action('login_head', 'brute_check_loginability');
add_action('login_head', 'brute_check_use_math');
add_action('wp_authenticate', 'brute_check_preauth', 1);
add_action('wp_login_failed', 'brute_log_failed_attempt');
add_action('login_init', 'brute_init_securewatch');

function brute_init_securewatch() {	
	include('uptime_securewatch.php');
}

//Make sure that they didn't try to sneak past the login form...
function brute_check_preauth($username) {
	brute_check_loginability(true);
	$bum = get_site_transient('brute_use_math');
	if(1 == $bum && isset($_POST['log'])) {
		if(false == function_exists('brute_math_authenticate'))
			include 'math-fallback.php';
		brute_math_authenticate();
	}
}

function brute_check_loginability($preauth = false) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_name = 'brute_loginable_'.str_replace('.', '_', $ip);
	$transient_value = get_site_transient( $transient_name );
	
	//This IP has been OKed, proceed to login
	if(isset($transient_value) && $transient_value['status'] == 'ok') { return true; }
	
	if(isset($transient_value) && $transient_value['status'] == 'blocked') { 
		if($transient_value['expire'] < time()) {
			//the block is expired but the transient didn't go away naturally, clear it out and allow login.
			delete_site_transient($transient_name);
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
	$bum = get_site_transient('brute_use_math');
	
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
	$host = 'http://'.strtolower($_SERVER['HTTP_HOST']);
	
	if(is_multisite()) {
		$host = network_home_url();
	}
	
	$hostdata = parse_url($host);
	
	$domain = $hostdata['host'];
	
	//if we still don't have it, get the site_url
	if (!$domain) {
		$host = get_site_url(1);
		$hostdata = parse_url($host);
		$domain = $hostdata['host'];
	}
	
	if(strpos($domain, 'www.') === 0) {
		$ct = 1;
		$domain = str_replace('www.', '', $domain, $ct);
	}

	return $domain;
}

function get_bruteprotect_host() {
	//Some servers can't access https-- we'll check once a day to see if we can.
	$use_https = get_site_transient('bruteprotect_use_https');
	if(!$use_https) {
		$test = wp_remote_get( 'https://api.bruteprotect.com/https_check.php' );
		$use_https = 'no';
		if( !is_wp_error( $test ) && $test['body'] == 'ok' ) {
			$use_https = 'yes';
		}
		set_site_transient('bruteprotect_use_https', $use_https, 86400);
	}
	if($use_https == 'yes') {
		return 'https://api.bruteprotect.com/';
	} else {
		return 'http://api.bruteprotect.com/';
	}
}

function brute_call($action = 'check_ip') {
	global $wp_version;
		
	$api_key = get_site_option('bruteprotect_api_key');

	$host = get_bruteprotect_host();
	
	$brute_ua = "WordPress/{$wp_version} | ";
	$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );
	
	$request['action'] = $action;
	$request['ip'] = $_SERVER['REMOTE_ADDR'];
	$request['host'] = brute_get_host();
	$request['api_key'] = $api_key;
	$request['multisite'] = 0;
	if(is_multisite()) {
		$request['multisite'] = get_blog_count();
	}
	
	$args = array(
		'body' => $request,
		'user-agent' => $brute_ua,
		'httpversion'	=> '1.0',
		'timeout'		=> 15
	);
	
	$response_json = wp_remote_post( $host, $args );
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$transient_name = 'brute_loginable_'.str_replace('.', '_', $ip);
	delete_site_transient($transient_name);
	
	if(is_array($response_json))
		$response = json_decode($response_json['body'], true);

	if(isset($response['status']) && !$response['error']) :
		$response['expire'] = time() + $response['seconds_remaining'];
		set_site_transient($transient_name, $response, $response['seconds_remaining']);
		delete_site_transient('brute_use_math');
	else :
		//no response from the API host?  Let's use math!
		set_site_transient('brute_use_math', 1, 600);
		$response['status'] = 'ok';
		$response['math'] = true;
	endif;
	
	if($response['error']) :
		update_site_option('bruteprotect_error', $response['error']);
	else :
		delete_site_option('bruteprotect_error');
	endif;
	
	return $response;
}