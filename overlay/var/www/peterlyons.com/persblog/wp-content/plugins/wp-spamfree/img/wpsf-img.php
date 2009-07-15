<?php
$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	include_once($root.'/wp-load.php');
    } else {
    // Before 2.6
	include_once($root.'/wp-config.php');
	}

$spamfree_options			= get_option('spamfree_options');
$CookieValidationName  		= $spamfree_options['cookie_validation_name'];
$CookieValidationKey 		= $spamfree_options['cookie_validation_key'];

update_option( 'ak_count_pre', get_option('akismet_spam_count') );

@setcookie( $CookieValidationName, $CookieValidationKey, 0, '/' );
	
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('HTTP/1.1 200 OK');
header('Content-Type: image/gif');

include('spacer.gif');

?>