<?php
$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	include_once($root.'/wp-load.php');
    } else {
    // Before 2.6
	include_once($root.'/wp-config.php');
	}

$wpSpamFreeVer				= get_option('wp_spamfree_version');
$spamfree_options			= get_option('spamfree_options');
$CookieValidationName  		= $spamfree_options['cookie_validation_name'];
$CookieValidationKey 		= $spamfree_options['cookie_validation_key'];

update_option( 'ak_count_pre', get_option('akismet_spam_count') );

@setcookie( $CookieValidationName, $CookieValidationKey, 0, '/' );
	
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Content-Type: application/x-javascript');
echo "
// WP-SpamFree ".$wpSpamFreeVer." JS Code :: BEGIN

// Cookie Handler :: BEGIN 
function GetCookie( name ) { 
	var start = document.cookie.indexOf( name + '=' ); 
	var len = start + name.length + 1; 
	if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) ) { 
		return null; 
	} 
	if ( start == -1 ) return null; 
	var end = document.cookie.indexOf( ';', len ); 
	if ( end == -1 ) end = document.cookie.length; 
	return unescape( document.cookie.substring( len, end ) ); 
}  
	
function SetCookie( name, value, expires, path, domain, secure ) { 
	var today = new Date(); 
	today.setTime( today.getTime() ); 
	if ( expires ) { 
		expires = expires * 1000 * 60 * 60 * 24; 
	} 
	var expires_date = new Date( today.getTime() + (expires) ); 
	document.cookie = name+'='+escape( value ) + 
		( ( expires ) ? ';expires='+expires_date.toGMTString() : '' ) + //expires.toGMTString() 
		( ( path ) ? ';path=' + path : '' ) + 
		( ( domain ) ? ';domain=' + domain : '' ) + 
		( ( secure ) ? ';secure' : '' ); 
}  
	
function DeleteCookie( name, path, domain ) { 
	if ( getCookie( name ) ) document.cookie = name + '=' + 
			( ( path ) ? ';path=' + path : '') + 
			( ( domain ) ? ';domain=' + domain : '' ) + 
			';expires=Thu, 01-Jan-1970 00:00:01 GMT'; 
} 
// Cookie Handler :: END  

function commentValidation() { 
	SetCookie('".$CookieValidationName."','".$CookieValidationKey."','','/');
	SetCookie('SJECT','CKON','','/');
}  

commentValidation();  

// WP-SpamFree ".$wpSpamFreeVer." JS Code :: END 
";

?>