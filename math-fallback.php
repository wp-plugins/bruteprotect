<?php
/*
If the API is unavailable, we fall back and block spam by math!
*/

add_action('login_form', 'brute_math_form');



// Protection function for submitted login form
function brute_math_authenticate() {
	$salt = get_option('bruteprotect_api_key').get_option('siteurl');
	$ans = (int)$_POST['brute_num'];
	$salted_ans = sha1($salt.$ans);
	$correct_ans = $_POST['brute_ck'];
	
	if($salted_ans != $correct_ans) {
		wp_die('You have not proven your humanity!');
	} else {
		return true;
	}
}

function brute_math_form() {
	$salt = get_option('bruteprotect_api_key').get_option('siteurl');
	$num1 = rand(0, 10);
	$num2 = rand(0, 10);
	$sum = $num1 + $num2;
	$ans = sha1($salt.$sum);
	?>
	<div style="margin: 5px 0 20px;">
		<strong>Prove your humanity: </strong>
		<?php echo $num1 ?> &nbsp; + &nbsp; <?php echo $num2 ?> &nbsp; = &nbsp; <input type="input" name="brute_num" value="" size="2" />
		<input type="hidden" name="brute_ck" value="<?php echo $ans; ?>" id="brute_ck" />
	</div>
	<?php 
}