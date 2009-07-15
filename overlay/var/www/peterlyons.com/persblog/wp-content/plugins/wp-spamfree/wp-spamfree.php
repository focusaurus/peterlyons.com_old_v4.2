<?php
/*
Plugin Name: WP-SpamFree
Plugin URI: http://www.hybrid6.com/webgeek/plugins/wp-spamfree
Description: An extremely powerful anti-spam plugin that virtually eliminates comment spam. Finally, you can enjoy a spam-free WordPress blog! Includes spam-free contact form feature as well.
Author: Scott Allen
Version: 2.1.0.7
Author URI: http://www.hybrid6.com/
*/

/*  Copyright 2007-2009    Scott Allen  (email : wp.spamfree [at] hybrid6 [dot] com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Begin the Plugin

/* Note to any other PHP developers reading this:
My use of the end curly braces "}" is a little funky in that I indent them, I know. IMO it's easier to debug. Just know that it's on purpose even though it's not standard. One of my programming quirks, and just how I roll. :)
*/

function spamfree_init() {
	$wpSpamFreeVer='2.1.0.7';
	update_option('wp_spamfree_version', $wpSpamFreeVer);
	spamfree_update_keys(0);
	}
	
function spamfree_create_random_key() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $keyCode = $keyCode . $tmp;
        $i++;
    	}
		
	if ($keyCode=='') {
		srand((double)74839201183*1000000);
    	$i = 0;
    	$pass = '' ;

    	while ($i <= 7) {
        	$num = rand() % 33;
        	$tmp = substr($chars, $num, 1);
        	$keyCode = $keyCode . $tmp;
        	$i++;
    		}
		}
    return $keyCode;
	}
	
function spamfree_update_keys($reset_keys) {
	$spamfree_options 								= get_option('spamfree_options');
	
	// Determine Time Key Was Last Updated
	$KeyUpdateTime = $spamfree_options['last_key_update'];

	// Set Random Cookie Name
	$CookieValidationName = $spamfree_options['cookie_validation_name'];
	if (!$CookieValidationName||$reset_keys==1) {
		$randomComValCodeCVN1 = spamfree_create_random_key();
		$randomComValCodeCVN2 = spamfree_create_random_key();
		$CookieValidationName = $randomComValCodeCVN1.$randomComValCodeCVN2;
		}
	// Set Random Cookie Value
	$CookieValidationKey = $spamfree_options['cookie_validation_key'];
	if (!$CookieValidationKey||$reset_keys==1) {
		$randomComValCodeCKV1 = spamfree_create_random_key();
		$randomComValCodeCKV2 = spamfree_create_random_key();
		$CookieValidationKey = $randomComValCodeCKV1.$randomComValCodeCKV2;
		}
	// Set Random Form Field Name
	$FormValidationFieldJS = $spamfree_options['form_validation_field_js'];
	if (!$FormValidationFieldJS||$reset_keys==1) {
		$randomComValCodeJSFFN1 = spamfree_create_random_key();
		$randomComValCodeJSFFN2 = spamfree_create_random_key();
		$FormValidationFieldJS = $randomComValCodeJSFFN1.$randomComValCodeJSFFN2;
		}
	// Set Random Form Field Value
	$FormValidationKeyJS = $spamfree_options['form_validation_key_js'];
	if (!$FormValidationKeyJS||$reset_keys==1) {
		$randomComValCodeJS1 = spamfree_create_random_key();
		$randomComValCodeJS2 = spamfree_create_random_key();
		$FormValidationKeyJS = $randomComValCodeJS1.$randomComValCodeJS2;
		}
	if (!$KeyUpdateTime||$reset_keys==1) {
		$KeyUpdateTime = time();
		}
	$spamfree_options_update = array (
		'cookie_validation_name' 				=> $CookieValidationName,
		'cookie_validation_key' 				=> $CookieValidationKey,
		'form_validation_field_js' 				=> $FormValidationFieldJS,
		'form_validation_key_js' 				=> $FormValidationKeyJS,
		'cookie_get_function_name' 				=> '',
		'cookie_set_function_name' 				=> '',
		'cookie_delete_function_name' 			=> '',
		'comment_validation_function_name' 		=> '',
		'last_key_update'						=> $KeyUpdateTime,
		'wp_cache' 								=> $spamfree_options['wp_cache'],
		'wp_super_cache' 						=> $spamfree_options['wp_super_cache'],
		'block_all_trackbacks' 					=> $spamfree_options['block_all_trackbacks'],
		'block_all_pingbacks' 					=> $spamfree_options['block_all_pingbacks'],
		'use_alt_cookie_method' 				=> $spamfree_options['use_alt_cookie_method'],
		'use_alt_cookie_method_only' 			=> $spamfree_options['use_alt_cookie_method_only'],
		'use_captcha_backup' 					=> $spamfree_options['use_captcha_backup'],
		'use_trackback_verification'		 	=> $spamfree_options['use_trackback_verification'],
		'comment_logging'						=> $spamfree_options['comment_logging'],
		'comment_logging_start_date'			=> $spamfree_options['comment_logging_start_date'],
		'comment_logging_all'					=> $spamfree_options['comment_logging_all'],
		'enhanced_comment_blacklist'			=> $spamfree_options['enhanced_comment_blacklist'],
		'allow_proxy_users'						=> $spamfree_options['allow_proxy_users'],
		'hide_extra_data'						=> $spamfree_options['hide_extra_data'],
		'form_include_website' 					=> $spamfree_options['form_include_website'],
		'form_require_website' 					=> $spamfree_options['form_require_website'],
		'form_include_phone' 					=> $spamfree_options['form_include_phone'],
		'form_require_phone' 					=> $spamfree_options['form_require_phone'],
		'form_include_company' 					=> $spamfree_options['form_include_company'],
		'form_require_company' 					=> $spamfree_options['form_require_company'],
		'form_include_drop_down_menu'			=> $spamfree_options['form_include_drop_down_menu'],
		'form_require_drop_down_menu'			=> $spamfree_options['form_require_drop_down_menu'],
		'form_drop_down_menu_title'				=> $spamfree_options['form_drop_down_menu_title'],
		'form_drop_down_menu_item_1'			=> $spamfree_options['form_drop_down_menu_item_1'],
		'form_drop_down_menu_item_2'			=> $spamfree_options['form_drop_down_menu_item_2'],
		'form_drop_down_menu_item_3'			=> $spamfree_options['form_drop_down_menu_item_3'],
		'form_drop_down_menu_item_4'			=> $spamfree_options['form_drop_down_menu_item_4'],
		'form_drop_down_menu_item_5'			=> $spamfree_options['form_drop_down_menu_item_5'],
		'form_drop_down_menu_item_6'			=> $spamfree_options['form_drop_down_menu_item_6'],
		'form_drop_down_menu_item_7'			=> $spamfree_options['form_drop_down_menu_item_7'],
		'form_drop_down_menu_item_8'			=> $spamfree_options['form_drop_down_menu_item_8'],
		'form_drop_down_menu_item_9'			=> $spamfree_options['form_drop_down_menu_item_9'],
		'form_drop_down_menu_item_10'			=> $spamfree_options['form_drop_down_menu_item_10'],
		'form_message_width' 					=> $spamfree_options['form_message_width'],
		'form_message_height' 					=> $spamfree_options['form_message_height'],
		'form_message_min_length'				=> $spamfree_options['form_message_min_length'],
		'form_message_recipient'				=> $spamfree_options['form_message_recipient'],
		'form_response_thank_you_message'		=> $spamfree_options['form_response_thank_you_message'],
		'form_include_user_meta'				=> $spamfree_options['form_include_user_meta'],
		'promote_plugin_link'					=> $spamfree_options['promote_plugin_link'],
		);
	update_option('spamfree_options', $spamfree_options_update);		
	}
	
function spamfree_count() {
	$spamfree_count = get_option('spamfree_count');	
	return $spamfree_count;
	}

function spamfree_counter($counter_option) {
	$counter_option_max = 9;
	$counter_option_min = 1;
	if ( !$counter_option || $counter_option > $counter_option_max || $counter_option < $counter_option_min ) {
		$spamfree_count = number_format( get_option('spamfree_count') );
		echo '<a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" style="text-decoration:none;" rel="external" title="WP-SpamFree - WordPress Anti-Spam Plugin" >'.$spamfree_count.' spam blocked by WP-SpamFree</a>';
		return;
		}
	// Display Counter
	/* Implementation: <?php if ( function_exists(spamfree_counter) ) { spamfree_counter(1); } ?> */
	$spamfree_count = number_format( get_option('spamfree_count') );
	$counter_div_height = array('0','66','66','66','106','61','67','66','66','106');
	$counter_count_padding_top = array('0','11','11','11','79','14','17','11','11','79');
	
	// Pre-2.6 compatibility
	if ( !defined('WP_CONTENT_URL') ) {
		define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
		}
	if ( !defined('WP_CONTENT_DIR') ) {
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}
	// Guess the location
	$wpsf_plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
	$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
	?>
	
	<style type="text/css">
	#spamfree_counter_wrap {color:#ffffff;text-decoration:none;width:140px;}
	#spamfree_counter {background:url(<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-bg-<?php echo $counter_option; ?>.png) no-repeat top left;height:<?php echo $counter_div_height[$counter_option]; ?>px;width:140px;overflow:hidden;border-style:none;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;padding-top:<?php echo $counter_count_padding_top[$counter_option]; ?>px;}
	</style>
	
	<div id="spamfree_counter_wrap" >
		<div id="spamfree_counter" >
		<?php 
			$server_ip_first_char = substr($_SERVER['SERVER_ADDR'], 0, 1);
			if ( ( $counter_option >= 1 && $counter_option <= 3 ) || ( $counter_option >= 7 && $counter_option <= 8 ) ) {
				if ( $server_ip_first_char > '5' ) {
					$spamfree_counter_title_text = 'WP-SpamFree Spam Plugin for WordPress';
					}
				else {
					$spamfree_counter_title_text = 'WP-SpamFree WordPress Anti-Spam Plugin';
					}
				echo '<strong style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;text-decoration:none;border-style:none;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;" rel="external" title="'.$spamfree_counter_title_text.'" >';
				echo '<span style="color:#ffffff;font-size:20px;line-height:100%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">'.$spamfree_count.'</span><br />'; 
				echo '<span style="color:#ffffff;font-size:14px;line-height:110%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">SPAM KILLED</span><br />'; 
				echo '<span style="color:#ffffff;font-size:9px;line-height:120%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">BY WP-SPAMFREE</span>';
				echo '</a></strong>'; 
				}
			else if ( $counter_option == 4 || $counter_option == 9 ) {
				if ( $server_ip_first_char > '5' ) {
					$spamfree_counter_title_text = 'WP-SpamFree - WordPress Spam Protection';
					}
				else {
					$spamfree_counter_title_text = 'WP-SpamFree - WordPress Anti-Spam Protection';
					}
				echo '<strong style="color:#000000;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;text-decoration:none;border-style:none;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" style="color:#000000;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;" rel="external" title="'.$spamfree_counter_title_text.'" >';
				echo '<span style="color:#000000;font-size:9px;line-height:100%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">'.$spamfree_count.' SPAM KILLED</span><br />'; 
				echo '</a></strong>'; 
				}
			else if ( $counter_option == 5 ) {
				echo '<strong style="color:#FEB22B;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;text-decoration:none;border-style:none;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" style="color:#FEB22B;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;" rel="external" title="Spam Killed by WP-SpamFree, a WordPress Anti-Spam Plugin" >';
				echo '<span style="color:#FEB22B;font-size:14px;line-height:100%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">'.$spamfree_count.'</span><br />'; 
				echo '</a></strong>'; 
				}
			else if ( $counter_option == 6 ) {
				if ( $server_ip_first_char > '5' ) {
					$spamfree_counter_title_text = 'Spam Killed by WP-SpamFree - Powerful Spam Protection';
					}
				else {
					$spamfree_counter_title_text = 'Spam Killed by WP-SpamFree - Powerful WordPress Anti-Spam Protection';
					}
				echo '<strong style="color:#000000;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;text-decoration:none;border-style:none;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" style="color:#000000;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;" rel="external" title="'.$spamfree_counter_title_text.'" >';
				echo '<span style="color:#000000;font-size:14px;line-height:100%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">'.$spamfree_count.'</span><br />'; 
				echo '</a></strong>'; 
				}
		?>
		</div>
	</div>
	
	<?php
	}

function spamfree_counter_sm($counter_sm_option) {
	$counter_sm_option_max = 5;
	$counter_sm_option_min = 1;
	if ( !$counter_sm_option || $counter_sm_option > $counter_sm_option_max || $counter_sm_option < $counter_sm_option_min ) {
		$counter_sm_option = 1;
		}
	// Display Small Counter
	/* Implementation: <?php if ( function_exists(spamfree_counter_sm) ) { spamfree_counter_sm(1); } ?> */
	$spamfree_count = number_format( get_option('spamfree_count') );
	$counter_sm_div_height = array('0','50','50','50','50','50');
	$counter_sm_count_padding_top = array('0','11','11','11','11','11');
	
	// Pre-2.6 compatibility
	if ( !defined('WP_CONTENT_URL') ) {
		define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
		}
	
	if ( !defined('WP_CONTENT_DIR') ) {
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}
	
	// Guess the location
	$wpsf_plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
	$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
	?>
	
	<style type="text/css">
	#spamfree_counter_sm_wrap {color:#ffffff;text-decoration:none;width:120px;}
	#spamfree_counter_sm {background:url(<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-sm-bg-<?php echo $counter_sm_option; ?>.png) no-repeat top left;height:<?php echo $counter_sm_div_height[$counter_sm_option]; ?>px;width:120px;overflow:hidden;border-style:none;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;padding-top:<?php echo $counter_sm_count_padding_top[$counter_sm_option]; ?>px;}
	</style>
	
	<div id="spamfree_counter_sm_wrap" >
		<div id="spamfree_counter_sm" >
		<?php 
			$server_ip_first_char = substr($_SERVER['SERVER_ADDR'], 0, 1);

			if ( ( $counter_sm_option >= 1 && $counter_sm_option <= 5 ) ) {
				if ( $server_ip_first_char > '5' ) {
					$spamfree_counter_title_text = 'Protected by WP-SpamFree - WordPress Anti-Spam Plugin';
					}
				else {
					$spamfree_counter_title_text = 'Protected by WP-SpamFree - WordPress Spam Plugin';
					}
				echo '<strong style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;line-height:100%;text-align:center;text-decoration:none;border-style:none;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;" rel="external" title="'.$spamfree_counter_title_text.'" >';
				echo '<span style="color:#ffffff;font-size:18px;line-height:100%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">'.$spamfree_count.'</span><br />'; 
				echo '<span style="color:#ffffff;font-size:10px;line-height:120%;font-family:Arial,Helvetica,sans-serif;font-weight:bold;text-decoration:none;border-style:none;">SPAM BLOCKED</span>';
				echo '</a></strong>'; 
				}
		?>
		</div>
	</div>
	
	<?php
	}

// Widget
function widget_spamfree_register() {
	function widget_spamfree($args) {
		extract($args);
		echo $before_widget;
		echo $before_title.'Spam'.$after_title;
		spamfree_counter_sm();
		echo $after_widget;
		}
	register_sidebar_widget('WP-SpamFree Counter','widget_spamfree');
	}
	
function spamfree_log_reset() {
	// Pre-2.6 compatibility
	if ( !defined('WP_CONTENT_DIR') ) {
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}
	// Guess the location
	$wpsf_plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
	
	$wpsf_log_filename = 'temp-comments-log.txt';
	$wpsf_log_empty_filename = 'temp-comments-log.init.txt';
	$wpsf_htaccess_filename = '.htaccess';
	$wpsf_htaccess_orig_filename = 'htaccess.txt';
	$wpsf_htaccess_empty_filename = 'htaccess.init.txt';
	$wpsf_log_dir = $wpsf_plugin_path.'/data';
	$wpsf_log_file = $wpsf_log_dir.'/'.$wpsf_log_filename;
	$wpsf_log_empty_file = $wpsf_log_dir.'/'.$wpsf_log_empty_filename;
	$wpsf_htaccess_file = $wpsf_log_dir.'/'.$wpsf_htaccess_filename;
	$wpsf_htaccess_orig_file = $wpsf_log_dir.'/'.$wpsf_htaccess_orig_filename;
	$wpsf_htaccess_empty_file = $wpsf_log_dir.'/'.$wpsf_htaccess_empty_filename;
	
	clearstatcache();
	if ( !file_exists( $wpsf_htaccess_file ) ) {
		@chmod( $wpsf_log_dir, 0775 );
		@chmod( $wpsf_htaccess_orig_file, 0666 );
		@chmod( $wpsf_htaccess_empty_file, 0666 );
		@rename( $wpsf_htaccess_orig_file, $wpsf_htaccess_file );
		@copy( $wpsf_htaccess_empty_file, $wpsf_htaccess_orig_file );
		}

	clearstatcache();
	$wpsf_perm_log_dir = substr(sprintf('%o', fileperms($wpsf_log_dir)), -4);
	$wpsf_perm_log_file = substr(sprintf('%o', fileperms($wpsf_log_file)), -4);
	$wpsf_perm_log_empty_file = substr(sprintf('%o', fileperms($wpsf_log_empty_file)), -4);
	$wpsf_perm_htaccess_file = substr(sprintf('%o', fileperms($wpsf_htaccess_file)), -4);
	$wpsf_perm_htaccess_empty_file = substr(sprintf('%o', fileperms($wpsf_htaccess_empty_file)), -4);
	if ( $wpsf_perm_log_dir < '0775' || !is_writable($wpsf_log_dir) || $wpsf_perm_log_file < '0666' || !is_writable($wpsf_log_file) || $wpsf_perm_log_empty_file < '0666' || !is_writable($wpsf_log_empty_file) || $wpsf_perm_htaccess_file < '0666' || !is_writable($wpsf_htaccess_file) || $wpsf_perm_htaccess_empty_file < '0666' || !is_writable($wpsf_htaccess_empty_file) ) {
		@chmod( $wpsf_log_dir, 0775 );
		@chmod( $wpsf_log_file, 0666 );
		@chmod( $wpsf_log_empty_file, 0666 );
		@chmod( $wpsf_htaccess_file, 0666 );
		@chmod( $wpsf_htaccess_empty_file, 0666 );
		}
	if ( file_exists( $wpsf_log_file ) && file_exists( $wpsf_log_empty_file ) ) {
		@copy( $wpsf_log_empty_file, $wpsf_log_file );
		}
	if ( file_exists( $wpsf_htaccess_file ) && file_exists( $wpsf_htaccess_empty_file ) ) {
		@copy( $wpsf_htaccess_empty_file, $wpsf_htaccess_file );
		}
	if ( $_SERVER['REMOTE_ADDR'] ) {
		$wpsf_htaccess_siteurl = get_option('siteurl');
		$wpsf_htaccess_http_host = str_replace( '.', '\.', $_SERVER['HTTP_HOST'] );
		$wpsf_htaccess_blog_url = str_replace( '.', '\.', $wpsf_htaccess_siteurl );
		if ( $wpsf_htaccess_blog_url ) {
			$wpsf_htaccess_data  = "SetEnvIfNoCase Referer ".$wpsf_htaccess_blog_url."/wp-admin/ wpsf_access\n";
			}
		$wpsf_htaccess_data .= "SetEnvIf Remote_Addr ^".$_SERVER['REMOTE_ADDR']."$ wpsf_access\n\n";	
		$wpsf_htaccess_data .= "<Files temp-comments-log.txt>\n";
		$wpsf_htaccess_data .= "order deny,allow\n";
		$wpsf_htaccess_data .= "deny from all\n";
		$wpsf_htaccess_data .= "allow from env=wpsf_access\n";
		$wpsf_htaccess_data .= "</Files>\n";
		}
	@$wpsf_htaccess_fp = fopen( $wpsf_htaccess_file,'a+' );
	@fwrite( $wpsf_htaccess_fp, $wpsf_htaccess_data );
	@fclose( $wpsf_htaccess_fp );
	}

function spamfree_log_data($wpsf_log_comment_data_array,$wpsf_log_comment_data_errors,$wpsf_log_comment_type,$wpsf_log_contact_form_data) {

	// Pre-2.6 compatibility
	if ( !defined('WP_CONTENT_DIR') ) {
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}
	// Guess the location
	$wpsf_plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
	
	$wpsf_log_filename = 'temp-comments-log.txt';
	$wpsf_log_empty_filename = 'temp-comments-log.init.txt';
	$wpsf_log_dir = $wpsf_plugin_path.'/data';
	$wpsf_log_file = $wpsf_log_dir.'/'.$wpsf_log_filename;
	$wpsf_log_max_filesize = 2*1048576; // 2 MB
	
	if (!$wpsf_log_comment_type) {
		$wpsf_log_comment_type = 'comment';
		}
	$wpsf_log_comment_type_display = strtoupper($wpsf_log_comment_type);
	
	$spamfree_options = get_option('spamfree_options');
	
	$CommentLogging 			= $spamfree_options['comment_logging'];
	$CommentLoggingStartDate 	= $spamfree_options['comment_logging_start_date'];
	$CommentLoggingAll 			= $spamfree_options['comment_logging_all'];
	
	$GetCurrentTime = time();
	$ResetIntervalHours = 24 * 7; // Reset interval in hours
	$ResetIntervalMinutes = 60; // Reset interval minutes default
	$ResetIntervalMinutesOverride = $ResetIntervalMinutes; // Use as override for testing; leave = $ResetIntervalMinutes when not testing

	if ( $ResetIntervalMinutesOverride != $ResetIntervalMinutes ) {
		$ResetIntervalHours = 1;
		$ResetIntervalMinutes = $ResetIntervalMinutesOverride;
		}
	$TimeThreshold = $GetCurrentTime - ( 60 * $ResetIntervalMinutes * $ResetIntervalHours ); // seconds * minutes * hours
	// This turns off if over x amount of time since starting, or filesize exceeds max

	if ( ( $CommentLoggingStartDate && $TimeThreshold > $CommentLoggingStartDate ) || ( file_exists( $wpsf_log_file ) && filesize( $wpsf_log_file ) >= $wpsf_log_max_filesize ) ) {
		//spamfree_log_reset();
		$CommentLogging = 0;
		$CommentLoggingStartDate = 0;
		$CommentLoggingAll = 0;
		$spamfree_options_update = array (
			'cookie_validation_name' 				=> $spamfree_options['cookie_validation_name'],
			'cookie_validation_key' 				=> $spamfree_options['cookie_validation_key'],
			'form_validation_field_js' 				=> $spamfree_options['form_validation_field_js'],
			'form_validation_key_js' 				=> $spamfree_options['form_validation_key_js'],
			'cookie_get_function_name' 				=> $spamfree_options['cookie_get_function_name'],
			'cookie_set_function_name' 				=> $spamfree_options['cookie_set_function_name'],
			'cookie_delete_function_name' 			=> $spamfree_options['cookie_delete_function_name'],
			'comment_validation_function_name' 		=> $spamfree_options['comment_validation_function_name'],
			'last_key_update'						=> $spamfree_options['last_key_update'],
			'wp_cache' 								=> $spamfree_options['wp_cache'],
			'wp_super_cache' 						=> $spamfree_options['wp_super_cache'],
			'block_all_trackbacks' 					=> $spamfree_options['block_all_trackbacks'],
			'block_all_pingbacks' 					=> $spamfree_options['block_all_pingbacks'],
			'use_alt_cookie_method' 				=> $spamfree_options['use_alt_cookie_method'],
			'use_alt_cookie_method_only' 			=> $spamfree_options['use_alt_cookie_method_only'],
			'use_captcha_backup' 					=> $spamfree_options['use_captcha_backup'],
			'use_trackback_verification' 			=> $spamfree_options['use_trackback_verification'],
			'comment_logging'						=> $CommentLogging,
			'comment_logging_start_date'			=> $CommentLoggingStartDate,
			'comment_logging_all'					=> $CommentLoggingAll,
			'enhanced_comment_blacklist'			=> $spamfree_options['enhanced_comment_blacklist'],
			'allow_proxy_users'						=> $spamfree_options['allow_proxy_users'],
			'hide_extra_data'						=> $spamfree_options['hide_extra_data'],
			'form_include_website' 					=> $spamfree_options['form_include_website'],
			'form_require_website' 					=> $spamfree_options['form_require_website'],
			'form_include_phone' 					=> $spamfree_options['form_include_phone'],
			'form_require_phone' 					=> $spamfree_options['form_require_phone'],
			'form_include_company' 					=> $spamfree_options['form_include_company'],
			'form_require_company' 					=> $spamfree_options['form_require_company'],
			'form_include_drop_down_menu'			=> $spamfree_options['form_include_drop_down_menu'],
			'form_require_drop_down_menu'			=> $spamfree_options['form_require_drop_down_menu'],
			'form_drop_down_menu_title'				=> $spamfree_options['form_drop_down_menu_title'],
			'form_drop_down_menu_item_1'			=> $spamfree_options['form_drop_down_menu_item_1'],
			'form_drop_down_menu_item_2'			=> $spamfree_options['form_drop_down_menu_item_2'],
			'form_drop_down_menu_item_3'			=> $spamfree_options['form_drop_down_menu_item_3'],
			'form_drop_down_menu_item_4'			=> $spamfree_options['form_drop_down_menu_item_4'],
			'form_drop_down_menu_item_5'			=> $spamfree_options['form_drop_down_menu_item_5'],
			'form_drop_down_menu_item_6'			=> $spamfree_options['form_drop_down_menu_item_6'],
			'form_drop_down_menu_item_7'			=> $spamfree_options['form_drop_down_menu_item_7'],
			'form_drop_down_menu_item_8'			=> $spamfree_options['form_drop_down_menu_item_8'],
			'form_drop_down_menu_item_9'			=> $spamfree_options['form_drop_down_menu_item_9'],
			'form_drop_down_menu_item_10'			=> $spamfree_options['form_drop_down_menu_item_10'],
			'form_message_width' 					=> $spamfree_options['form_message_width'],
			'form_message_height' 					=> $spamfree_options['form_message_height'],
			'form_message_min_length' 				=> $spamfree_options['form_message_min_length'],
			'form_message_recipient' 				=> $spamfree_options['form_message_recipient'],
			'form_response_thank_you_message' 		=> $spamfree_options['form_response_thank_you_message'],
			'form_include_user_meta' 				=> $spamfree_options['form_include_user_meta'],
			'promote_plugin_link' 					=> $spamfree_options['promote_plugin_link'],
			);
		update_option('spamfree_options', $spamfree_options_update);
		}
	else {
		// LOG DATA
		$wpsf_log_datum = date("Y-m-d (D) H:i:s",$GetCurrentTime);
		$wpsf_log_comment_data  = ":: ".$wpsf_log_comment_type_display." BEGIN ::"."\n";
		
		$submitter_ip_address = $_SERVER['REMOTE_ADDR'];
		$submitter_ip_address_short_l = trim( substr( $submitter_ip_address, 0, 6) );
		$submitter_ip_address_short_r = trim( substr( $submitter_ip_address, -6, 2) );
		$submitter_ip_address_obfuscated = $submitter_ip_address_short_l.'****'.$submitter_ip_address_short_r.'.***';
		$submitter_remote_host = $_SERVER['REMOTE_HOST'];

		// IP / PROXY INFO :: BEGIN
		$ip = $_SERVER['REMOTE_ADDR'];
		$ipBlock=explode('.',$ip);
		$ipProxyVIA=$_SERVER['HTTP_VIA'];
		$MaskedIP=$_SERVER['HTTP_X_FORWARDED_FOR']; // Stated Original IP - Can be faked
		$MaskedIPBlock=explode('.',$MaskedIP);
		if (eregi("^([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])",$MaskedIP)&&$MaskedIP!=""&&$MaskedIP!="unknown"&&!eregi("^192.168.",$MaskedIP)) {
			$MaskedIPValid=true;
			$MaskedIPCore=rtrim($MaskedIP," unknown;,");
			}
		if ( !$MaskedIP ) { $MaskedIP='[no data]'; }
		$ReverseDNS = gethostbyaddr($ip);
		$ReverseDNSIP = gethostbyname($ReverseDNS);
		
		if ( $ReverseDNSIP != $ip || $ip == $ReverseDNS ) {
			$ReverseDNSAuthenticity = '[Possibly Forged]';
			} 
		else {
			$ReverseDNSAuthenticity = '[Verified]';
			}
		// Detect Use of Proxy
		if ($_SERVER['HTTP_VIA']||$_SERVER['HTTP_X_FORWARDED_FOR']) {
			$ipProxy='PROXY DETECTED';
			$ipProxyShort='PROXY';
			$ipProxyData=$ip.' | MASKED IP: '.$MaskedIP;
			$ProxyStatus='TRUE';
			}
		else {
			$ipProxy='No Proxy';
			$ipProxyShort=$ipProxy;
			$ipProxyData=$ip;
			$ProxyStatus='FALSE';
			}
		// IP / PROXY INFO :: END
		
		if ( $wpsf_log_comment_type == 'comment' ) {
			$comment_author_email = $wpsf_log_comment_data_array['comment_author_email'];
			$comment_author_email_short_l = trim( substr( $comment_author_email, 0, 4) );
			$comment_author_email_short_r = trim( substr( $comment_author_email, -5, 5) );
			$comment_author_email_obfuscated = $comment_author_email_short_l.'***@***'.$comment_author_email_short_r;
			if ( !$comment_author_email ) { $comment_author_email_obfuscated = '[none]'; }
			
			$wpsf_log_comment_data .= $wpsf_log_datum."\n";
			$wpsf_log_comment_data .= "comment_post_ID: ".$wpsf_log_comment_data_array['comment_post_ID']."\n";
			$wpsf_log_comment_data .= "comment_author: ".$wpsf_log_comment_data_array['comment_author']."\n";
			//$wpsf_log_comment_data .= "comment_author_email: ".$comment_author_email_obfuscated." [Obfuscated for Privacy]\n";
			$wpsf_log_comment_data .= "comment_author_email: ".$comment_author_email."\n";
			$wpsf_log_comment_data .= "comment_author_url: ".$wpsf_log_comment_data_array['comment_author_url']."\n";
			$wpsf_log_comment_data .= "comment_content: "."\n".$wpsf_log_comment_data_array['comment_content']."\n";
			$wpsf_log_comment_data .= "comment_type: ";
			if ( $wpsf_log_comment_data_array['comment_type'] ) {
				$wpsf_log_comment_data .= $wpsf_log_comment_data_array['comment_type'];
				}
			else {
				$wpsf_log_comment_data .= "comment";
				}
			}
		else if ( $wpsf_log_comment_type == 'contact form' ) {
			$wpsf_log_comment_data .= $wpsf_log_datum."\n";			
			$wpsf_log_comment_data .= '----------------------------------------------------------------------------------'."\n";
			$wpsf_log_comment_data .= $wpsf_log_contact_form_data;
			$wpsf_log_comment_data .= '----------------------------------------------------------------------------------'."\n";
			}
			
		$wpsf_log_comment_data .= "\n";
		//$wpsf_log_comment_data .= "IP Address: ".$submitter_ip_address_obfuscated." [Obfuscated for Privacy]\n";
		$wpsf_log_comment_data .= "IP Address: ".$submitter_ip_address."\n";
		$wpsf_log_comment_data .= "Remost Host: ".$submitter_remote_host."\n";
		$wpsf_log_comment_data .= "Reverse DNS: ".$ReverseDNS."\n";
		$wpsf_log_comment_data .= "Reverse DNS IP: ".$ReverseDNSIP."\n";
		$wpsf_log_comment_data .= "Reverse DNS Authenticity: ".$ReverseDNSAuthenticity."\n";
		$wpsf_log_comment_data .= "Proxy Info: ".$ipProxy."\n";
		$wpsf_log_comment_data .= "Proxy Data: ".$ipProxyData."\n";
		$wpsf_log_comment_data .= "Proxy Status: ".$ProxyStatus."\n";
		if ( $_SERVER['HTTP_VIA'] ) {
			$wpsf_log_comment_data .= "HTTP_VIA: ".$_SERVER['HTTP_VIA']."\n";
			}
		if ( $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
			$wpsf_log_comment_data .= "HTTP_X_FORWARDED_FOR: ".$_SERVER['HTTP_X_FORWARDED_FOR']."\n";
			}
		$wpsf_log_comment_data .= "HTTP_ACCEPT_LANGUAGE: ".$_SERVER['HTTP_ACCEPT_LANGUAGE']."\n";
		$wpsf_log_comment_data .= "HTTP_HTTP_ACCEPT: ".$_SERVER['HTTP_ACCEPT']."\n";
		$wpsf_log_comment_data .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\n";
		$wpsf_log_comment_data .= "Referrer: ";
		if ( $_SERVER['HTTP_REFERER'] ) {
			$wpsf_log_comment_data .= $_SERVER['HTTP_REFERER'];
			}
		else {
			$wpsf_log_comment_data .= '[none]';
			}
		$wpsf_log_comment_data .= "\n";
		if ( !$wpsf_log_comment_data_errors ) { $wpsf_log_comment_data_errors = '[none]'; }
		$wpsf_log_comment_data .= "Failed Test Code(s): ".$wpsf_log_comment_data_errors."\n";
		$wpsf_log_comment_data .= ":: ".$wpsf_log_comment_type_display." END ::"."\n\n";
		
		@$wpsf_log_fp = fopen( $wpsf_log_file,'a+' );
		@fwrite( $wpsf_log_fp, $wpsf_log_comment_data );
		@fclose( $wpsf_log_fp );
		}
	}

function spamfree_content_addendum($content) {

	if ( !is_feed() && !is_page() && !is_home() ) {	

		$spamfree_options = get_option('spamfree_options');
	
		if ( ($_COOKIE[$spamfree_options['cookie_validation_name']] != $spamfree_options['cookie_validation_key'] && $spamfree_options['use_alt_cookie_method'] ) || $spamfree_options['use_alt_cookie_method_only'] ) {
		
			if ( !defined('WP_CONTENT_URL') ) {
				define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
				}
			if ( !eregi( 'opera', $_SERVER['HTTP_USER_AGENT'] ) ) { 
				$wpsf_img_p_disp = ' style="clear:both;display:none;"';
				$wpsf_img_disp = 'display:none;';
				}
			else { 
				$wpsf_img_p_disp = ' style="clear:both;"';
				$wpsf_img_disp = '';
				}	
			$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			
			$content .=  '<span'.$wpsf_img_p_disp.'><img src="'.$wpsf_plugin_url.'/img/wpsf-img.php" width="0" height="0" alt="" style="border-style:none;width:0px;height:0px;'.$wpsf_img_disp.'" /></span>';
			}	
		}
	return $content;
	}

function spamfree_comment_form() {

	$spamfree_options = get_option('spamfree_options');
	
	$PromotePluginLink = $spamfree_options['promote_plugin_link'];
	
	if ( $PromotePluginLink ) {
		$server_ip_first_char = substr($_SERVER['SERVER_ADDR'], 0, 1);
		$server_ip_fourth_char = substr($_SERVER['SERVER_ADDR'], 3, 1);
		if ( $server_ip_first_char == '6' ) {
			echo '<p style="font-size:9px;clear:both;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree WordPress Anti-Spam Plugin" >Spam Protection</a> by WP-SpamFree</p>'."\n";
			}
		else if ( $server_ip_first_char == '7' ) {
			echo '<p style="font-size:9px;clear:both;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree WordPress Anti-Spam Plugin" >Anti-Spam Protection</a> by WP-SpamFree</p>'."\n";
			}
		else if ( $server_ip_first_char == '8' ) {
			echo '<p style="font-size:9px;clear:both;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree WordPress Anti-Spam Plugin" >Comment Spam Protection</a> by WP-SpamFree</p>'."\n";
			}
		else if ( $server_ip_first_char == '9' ) {
			echo '<p style="font-size:9px;clear:both;">Spam Protection by <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree WordPress Anti-Spam Plugin" >WP-SpamFree Plugin</a></p>'."\n";
			}
		else if ( $server_ip_fourth_char == '5' ) {
			echo '<p style="font-size:9px;clear:both;"><a href="http://wordpress.org/extend/plugins/wp-spamfree/" title="WP-SpamFree WordPress Anti-Spam Plugin" >Anti-Spam Protection</a> by WP-SpamFree</p>'."\n";
			}
		else {
			echo '<p style="font-size:9px;clear:both;">Spam Protection by <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree WordPress Anti-Spam Plugin" >WP-SpamFree</a></p>'."\n";
			}
		}
	
	if ( !$spamfree_options['use_alt_cookie_method'] && !$spamfree_options['use_alt_cookie_method_only'] ) {
		echo '<noscript><p><strong>Currently you have JavaScript disabled. In order to post comments, please make sure JavaScript and Cookies are enabled, and reload the page.</strong> <a href="http://www.google.com/support/bin/answer.py?answer=23852" rel="nofollow external" >Click here for instructions</a> on how to enable JavaScript in your browser.</p></noscript>'."\n";	
		}
	// If need to add anything else to comment area, start here	

	}
	
function spamfree_contact_form($content) {
	if ( !defined('WP_CONTENT_URL') ) {
		define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
		}

	$spamfree_contact_form_url = $_SERVER['REQUEST_URI'];
	if ( $_SERVER['QUERY_STRING'] ) {
		$spamfree_contact_form_query_op = '&amp;';
		}
	else {
		$spamfree_contact_form_query_op = '?';
		}
	$spamfree_contact_form_content = '';
	if ( is_page() && ( !is_home() && !is_feed() && !is_archive() && !is_search() && !is_404() ) ) {

		$spamfree_options				= get_option('spamfree_options');
		$CookieValidationName  			= $spamfree_options['cookie_validation_name'];
		$CookieValidationKey 			= $spamfree_options['cookie_validation_key'];
		$WPCommentValidationJS 			= $_COOKIE[$CookieValidationName];
		$FormIncludeWebsite				= $spamfree_options['form_include_website'];
		$FormRequireWebsite				= $spamfree_options['form_require_website'];
		$FormIncludePhone				= $spamfree_options['form_include_phone'];
		$FormRequirePhone				= $spamfree_options['form_require_phone'];
		$FormIncludeCompany				= $spamfree_options['form_include_company'];
		$FormRequireCompany				= $spamfree_options['form_require_company'];
		$FormIncludeDropDownMenu		= $spamfree_options['form_include_drop_down_menu'];
		$FormRequireDropDownMenu		= $spamfree_options['form_require_drop_down_menu'];
		$FormDropDownMenuTitle			= $spamfree_options['form_drop_down_menu_title'];
		$FormDropDownMenuItem1			= $spamfree_options['form_drop_down_menu_item_1'];
		$FormDropDownMenuItem2			= $spamfree_options['form_drop_down_menu_item_2'];
		$FormDropDownMenuItem3			= $spamfree_options['form_drop_down_menu_item_3'];
		$FormDropDownMenuItem4			= $spamfree_options['form_drop_down_menu_item_4'];
		$FormDropDownMenuItem5			= $spamfree_options['form_drop_down_menu_item_5'];
		$FormDropDownMenuItem6			= $spamfree_options['form_drop_down_menu_item_6'];
		$FormDropDownMenuItem7			= $spamfree_options['form_drop_down_menu_item_7'];
		$FormDropDownMenuItem8			= $spamfree_options['form_drop_down_menu_item_8'];
		$FormDropDownMenuItem9			= $spamfree_options['form_drop_down_menu_item_9'];
		$FormDropDownMenuItem10			= $spamfree_options['form_drop_down_menu_item_10'];
		$FormMessageWidth				= $spamfree_options['form_message_width'];
		$FormMessageHeight				= $spamfree_options['form_message_height'];
		$FormMessageMinLength			= $spamfree_options['form_message_min_length'];
		$FormMessageRecipient			= $spamfree_options['form_message_recipient'];
		$FormResponseThankYouMessage	= $spamfree_options['form_response_thank_you_message'];
		$FormIncludeUserMeta			= $spamfree_options['form_include_user_meta'];
		$PromotePluginLink				= $spamfree_options['promote_plugin_link'];
		
		if ( $FormMessageWidth < 40 ) {
			$FormMessageWidth = 40;
			}
			
		if ( $FormMessageHeight < 5 ) {
			$FormMessageHeight = 5;
			}
		else if ( !$FormMessageHeight ) {
			$FormMessageHeight = 10;
			}
			
		if ( $FormMessageMinLength < 15 ) {
			$FormMessageMinLength = 15;
			}
		else if ( !$FormMessageMinLength ) {
			$FormMessageMinLength = 25;
			}

		if ( $_GET['form'] == 'response' ) {
		
			// PROCESSING CONTACT FORM :: BEGIN
			$wpsf_contact_name 				= Trim(stripslashes(strip_tags($_POST['wpsf_contact_name'])));
			$wpsf_contact_email 			= Trim(stripslashes(strip_tags($_POST['wpsf_contact_email'])));
			$wpsf_contact_website 			= Trim(stripslashes(strip_tags($_POST['wpsf_contact_website'])));
			$wpsf_contact_phone 			= Trim(stripslashes(strip_tags($_POST['wpsf_contact_phone'])));
			$wpsf_contact_company 			= Trim(stripslashes(strip_tags($_POST['wpsf_contact_company'])));
			$wpsf_contact_drop_down_menu	= Trim(stripslashes(strip_tags($_POST['wpsf_contact_drop_down_menu'])));
			$wpsf_contact_subject 			= Trim(stripslashes(strip_tags($_POST['wpsf_contact_subject'])));
			$wpsf_contact_message 			= Trim(stripslashes(strip_tags($_POST['wpsf_contact_message'])));
			/*
			$wpsf_contact_cc 				= Trim(stripslashes(strip_tags($_POST['wpsf_contact_cc'])));
			*/
			// PROCESSING CONTACT FORM :: END
			
			/*
			if ( !$wpsf_contact_cc ) {
				$wpsf_contact_cc ='No';
				}
			*/
			
			// FORM INFO :: BEGIN
			
			if ( $FormMessageRecipient ) {
				$wpsf_contact_form_to			= $FormMessageRecipient;
				}
			else {
				$wpsf_contact_form_to 			= get_option('admin_email');
				}
			//$wpsf_contact_form_to 			= get_option('admin_email');
			//$wpsf_contact_form_cc_to 			= $wpsf_contact_email;
			$wpsf_contact_form_to_name 			= $wpsf_contact_form_to;
			//$wpsf_contact_form_cc_to_name 		= $wpsf_contact_name;
			$wpsf_contact_form_subject 			= '[Website Contact] '.$wpsf_contact_subject;
			//$wpsf_contact_form_cc_subject		= '[Website Contact CC] '.$wpsf_contact_subject;
			$wpsf_contact_form_msg_headers 		= "From: $wpsf_contact_name <$wpsf_contact_email>" . "\r\n" . "Reply-To: $wpsf_contact_email" . "\r\n" . "Content-Type: text/plain\r\n";
			$wpsf_contact_form_blog				= get_option('siteurl');
			// Another option: "Content-Type: text/html"
			
			// FORM INFO :: END
			
			// TEST TO PREVENT CONTACT FORM SPAM :: BEGIN
			
			$ip = $_SERVER['REMOTE_ADDR'];
			$ReverseDNS = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			$wpsf_contact_message_lc = strtolower( $wpsf_contact_message );
			
			if ( $WPCommentValidationJS != $CookieValidationKey ) { // Check for Cookie
				$JSCookieFail=1;
				$spamfree_error_code .= ' CONTACTFORM-COOKIEFAIL';
				}
				
			// ERROR CHECKING
			
			$contact_form_spam_1_count = substr_count( $wpsf_contact_message_lc, 'link'); //10
			$contact_form_spam_1_limit = 7;
			$contact_form_spam_2_count = substr_count( $wpsf_contact_message_lc, 'link building'); //4
			$contact_form_spam_2_limit = 3;
			$contact_form_spam_3_count = substr_count( $wpsf_contact_message_lc, 'link exchange');
			$contact_form_spam_3_limit = 2;
			$contact_form_spam_4_count = substr_count( $wpsf_contact_message_lc, 'link request'); // Subject
			$contact_form_spam_4_limit = 1;
			$contact_form_spam_5_count = substr_count( $wpsf_contact_message_lc, 'link building service');
			$contact_form_spam_5_limit = 2;
			$contact_form_spam_6_count = substr_count( $wpsf_contact_message_lc, 'link building experts india'); // 2
			$contact_form_spam_6_limit = 0;
			$contact_form_spam_7_count = substr_count( $wpsf_contact_message_lc, 'india'); //2
			$contact_form_spam_7_limit = 1;
			
			
			$wpsf_contact_subject_lc = strtolower( $wpsf_contact_subject );
			$contact_form_spam_subj_1_count = substr_count( $wpsf_contact_subject_lc, 'link request'); // Subject
			$contact_form_spam_subj_1_limit = 0;
			$contact_form_spam_subj_2_count = substr_count( $wpsf_contact_subject_lc, 'link exchange'); // Subject
			$contact_form_spam_subj_2_limit = 0;
			
			$contact_form_spam_term_total = $contact_form_spam_1_count + $contact_form_spam_2_count + $contact_form_spam_3_count + $contact_form_spam_4_count + $contact_form_spam_5_count + $contact_form_spam_6_count + $contact_form_spam_7_count + $contact_form_spam_subj_1_count + $contact_form_spam_subj_2_count;
			$contact_form_spam_term_total_limit = 15;
			
			if ( eregi( "\.in$", $ReverseDNS ) ) {
				$contact_form_spam_loc_in = 1;
				}
			if ( ( $contact_form_spam_term_total > $contact_form_spam_term_total_limit || $contact_form_spam_1_count > $contact_form_spam_1_limit || $contact_form_spam_2_count > $contact_form_spam_2_limit || $contact_form_spam_5_count > $contact_form_spam_5_limit || $contact_form_spam_6_count > $contact_form_spam_6_limit ) && $contact_form_spam_loc_in ) {
				$MessageSpam=1;
				$spamfree_error_code .= ' CONTACTFORM-MESSAGESPAM1';
				$contact_response_status_message_addendum .= '&bull; Message appears to be spam. Please note that link requests and link exchange requests will be automatically deleted, and are not an acceptable use of this contact form.<br />&nbsp;<br />';
				}
			else if ( $contact_form_spam_subj_1_count > $contact_form_spam_subj_1_limit || $contact_form_spam_subj_2_count > $contact_form_spam_subj_2_limit ) {
				$MessageSpam=1;
				$spamfree_error_code .= ' CONTACTFORM-MESSAGESPAM2';
				$contact_response_status_message_addendum .= '&bull; Message appears to be spam. Please note that link requests and link exchange requests will be automatically deleted, and are not an acceptable use of this contact form.<br />&nbsp;<br />';
				}
				
			if ( !$wpsf_contact_name || !$wpsf_contact_email || !$wpsf_contact_subject || !$wpsf_contact_message || ( $FormIncludeWebsite && $FormRequireWebsite && !$wpsf_contact_website ) || ( $FormIncludePhone && $FormRequirePhone && !$wpsf_contact_phone ) || ( $FormIncludeCompany && $FormRequireCompany && !$wpsf_contact_company ) || ( $FormIncludeDropDownMenu && $FormRequireDropDownMenu && !$wpsf_contact_drop_down_menu ) ) {
				$BlankField=1;
				$spamfree_error_code .= ' CONTACTFORM-BLANKFIELD';
				$contact_response_status_message_addendum .= '&bull; At least one required field was left blank.<br />&nbsp;<br />';
				}
				
			if (!eregi("^([-_\.a-z0-9])+@([-a-z0-9]+\.)+([a-z]{2}|com|net|org|edu|gov|mil|int|biz|pro|info|arpa|aero|coop|name|museum)$",$wpsf_contact_email)) {
				$InvalidValue=1;
				$BadEmail=1;
				$spamfree_error_code .= ' CONTACTFORM-INVALIDVALUE-EMAIL';
				$contact_response_status_message_addendum .= '&bull; Please enter a valid email address.<br />&nbsp;<br />';
				}
			
			$wpsf_contact_phone_zerofake1 = str_replace( '000-000-0000', '', $wpsf_contact_phone );
			$wpsf_contact_phone_zerofake2 = str_replace( '(000) 000-0000', '', $wpsf_contact_phone );
			$wpsf_contact_phone_zero = str_replace( '0', '', $wpsf_contact_phone );
			$wpsf_contact_phone_na1 = str_replace( 'N/A', '', $wpsf_contact_phone );
			$wpsf_contact_phone_na2 = str_replace( 'NA', '', $wpsf_contact_phone );
			if ( $FormIncludePhone && $FormRequirePhone && ( !$wpsf_contact_phone_zerofake1 || !$wpsf_contact_phone_zerofake2 || !$wpsf_contact_phone_zero || !$wpsf_contact_phone_na1 || !$wpsf_contact_phone_na2 ) ) {
				$InvalidValue=1;
				$BadPhone=1;
				$spamfree_error_code .= ' CONTACTFORM-INVALIDVALUE-PHONE';
				$contact_response_status_message_addendum .= '&bull; Please enter a valid phone number.<br />&nbsp;<br />';
				}
				
			$MessageLength = strlen( $wpsf_contact_message );
			if ( $MessageLength < $FormMessageMinLength ) {
				$MessageShort=1;
				$spamfree_error_code .= ' CONTACTFORM-MESSAGESHORT';
				$contact_response_status_message_addendum .= '&bull; Message too short. Please enter a complete message.<br />&nbsp;<br />';
				}		
			
			// MESSAGE CONTENT :: BEGIN
			$wpsf_contact_form_msg_1 .= "Message: "."\n";
			$wpsf_contact_form_msg_1 .= $wpsf_contact_message."\n";
			
			$wpsf_contact_form_msg_1 .= "\n";
		
			$wpsf_contact_form_msg_1 .= "Name: ".$wpsf_contact_name."\n";
			$wpsf_contact_form_msg_1 .= "Email: ".$wpsf_contact_email."\n";
			if ( $FormIncludePhone ) {
				$wpsf_contact_form_msg_1 .= "Phone: ".$wpsf_contact_phone."\n";
				}
			if ( $FormIncludeCompany ) {
				$wpsf_contact_form_msg_1 .= "Company: ".$wpsf_contact_company."\n";
				}
			if ( $FormIncludeWebsite ) {
				$wpsf_contact_form_msg_1 .= "Website: ".$wpsf_contact_website."\n";
				}
			if ( $FormIncludeDropDownMenu ) {
				$wpsf_contact_form_msg_1 .= $FormDropDownMenuTitle.": ".$wpsf_contact_drop_down_menu."\n";
				}
			
			$wpsf_contact_form_msg_2 .= "\n";
			//Check following variables tomake sure not repeating
			if ( $FormIncludeUserMeta ) {
				$wpsf_contact_form_msg_2 .= "\n";
				$wpsf_contact_form_msg_2 .= "Website Generating This Email: ".$wpsf_contact_form_blog."\n";
				$wpsf_contact_form_msg_2 .= "\n";					
				$wpsf_contact_form_msg_2 .= "Referrer: ".$_SERVER['HTTP_REFERER']."\n";
				$wpsf_contact_form_msg_2 .= "\n";
				$wpsf_contact_form_msg_2 .= "User-Agent (Browser/OS): ".$_SERVER['HTTP_USER_AGENT']."\n";
				$wpsf_contact_form_msg_2 .= "\n";
				$wpsf_contact_form_msg_2 .= "IP Address: ".$_SERVER['REMOTE_ADDR']."\n";
				$wpsf_contact_form_msg_2 .= "Server: ".$_SERVER['REMOTE_HOST']."\n";
				$wpsf_contact_form_msg_2 .= "Reverse DNS: ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."\n";
				$wpsf_contact_form_msg_2 .= "IP Address Lookup: http://www.dnsstuff.com/tools/ipall/?ip=".$_SERVER['REMOTE_ADDR']."\n";
				}
				
			$wpsf_contact_form_msg_3 .= "\n";
			$wpsf_contact_form_msg_3 .= "\n";
			
			$wpsf_contact_form_msg = $wpsf_contact_form_msg_1.$wpsf_contact_form_msg_2.$wpsf_contact_form_msg_3;
			$wpsf_contact_form_msg_cc = $wpsf_contact_form_msg_1.$wpsf_contact_form_msg_3;
			// MESSAGE CONTENT :: END

			if ( !$BlankField && !$InvalidValue && !$MessageShort && !$MessageSpam && !$JSCookieFail ) {  
				// SEND MESSAGE
				@wp_mail( $wpsf_contact_form_to, $wpsf_contact_form_subject, $wpsf_contact_form_msg, $wpsf_contact_form_msg_headers );								
				$contact_response_status = 'thank-you';
				$spamfree_error_code = 'No Error';
				if ( $spamfree_options['comment_logging'] && $spamfree_options['comment_logging_all'] ) {
					spamfree_log_data( '', $spamfree_error_code, 'contact form', $wpsf_contact_form_msg );
					}
				}
			else {
				update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
				if ( $spamfree_options['comment_logging'] ) {
					$spamfree_error_code = ltrim($spamfree_error_code);
					spamfree_log_data( '', $spamfree_error_code, 'contact form', $wpsf_contact_form_msg );
					}
				}				
			
			// TEST TO PREVENT CONTACT FORM SPAM :: END
			
			$FormResponseThankYouMessageDefault = '<p>Your message was sent successfully. Thank you.</p><p>&nbsp;</p>';
			$FormResponseThankYouMessage = str_replace( "\\", "", $FormResponseThankYouMessage );
		
			if ( $contact_response_status == 'thank-you' ) {
				if ( $FormResponseThankYouMessage ) {
					$spamfree_contact_form_content .= '<p>'.$FormResponseThankYouMessage.'</p><p>&nbsp;</p>'."\n";
					}
				else {
					$spamfree_contact_form_content .= $FormResponseThankYouMessageDefault."\n";
					}
				}
			else {
				if ( eregi ( '\&form\=response', $spamfree_contact_form_url ) ) {
					$spamfree_contact_form_back_url = str_replace('&form=response','',$spamfree_contact_form_url );
					}
				else if ( eregi ( '\?form\=response', $spamfree_contact_form_url ) ) {
					$spamfree_contact_form_back_url = str_replace('?form=response','',$spamfree_contact_form_url );
					}
				if ( $MessageSpam ) {
					if ( !$spamfree_options['use_alt_cookie_method'] && !$spamfree_options['use_alt_cookie_method_only'] ) {
						$contact_response_status_message_addendum .= '<noscript><br />&nbsp;<br />&bull; Currently you have JavaScript disabled.</noscript>'."\n";
						}
					$spamfree_contact_form_content .= '<p><strong>ERROR: <br />&nbsp;<br />'.$contact_response_status_message_addendum.'</strong><p>&nbsp;</p>'."\n";
					}
				else {
					if ( !$spamfree_options['use_alt_cookie_method'] && !$spamfree_options['use_alt_cookie_method_only'] ) {
						$contact_response_status_message_addendum .= '<noscript><br />&nbsp;<br />&bull; Currently you have JavaScript disabled.</noscript>'."\n";
						}
					$spamfree_contact_form_content .= '<p><strong>ERROR: Please return to the <a href="'.$spamfree_contact_form_back_url.'" >contact form</a> and fill out all required fields.';
					if ( !$spamfree_options['use_alt_cookie_method'] && !$spamfree_options['use_alt_cookie_method_only'] ) {
						$spamfree_contact_form_content .= ' Please make sure JavaScript and Cookies are enabled in your browser.';
						}
					else if ( $spamfree_options['use_alt_cookie_method_only'] ) {
						$spamfree_contact_form_content .= ' Please make sure Images and Cookies are enabled in your browser.';
						}
					else {
						$spamfree_contact_form_content .= ' Please make sure Cookies are enabled in your browser.';
						}
					$spamfree_contact_form_content .= '<br />&nbsp;<br />'.$contact_response_status_message_addendum.'</strong><p>&nbsp;</p>'."\n";
					}

				}
			$content_new = str_replace($content, $spamfree_contact_form_content, $content);
			}
		else {		
			$spamfree_contact_form_content .= '<form id="wpsf_contact_form" name="wpsf_contact_form" action="'.$spamfree_contact_form_url.$spamfree_contact_form_query_op.'form=response" method="post" style="text-align:left;" >'."\n";

			$spamfree_contact_form_content .= '<p><label><strong>Name</strong> *<br />'."\n";

			$spamfree_contact_form_content .= '<input type="text" id="wpsf_contact_name" name="wpsf_contact_name" value="" size="40" /> </label></p>'."\n";
			$spamfree_contact_form_content .= '<p><label><strong>Email</strong> *<br />'."\n";
			$spamfree_contact_form_content .= '<input type="text" id="wpsf_contact_email" name="wpsf_contact_email" value="" size="40" /> </label></p>'."\n";
			
			if ( $FormIncludeWebsite ) {
				$spamfree_contact_form_content .= '<p><label><strong>Website</strong> ';
				if ( $FormRequireWebsite ) { 
					$spamfree_contact_form_content .= '*'; 
					}
				$spamfree_contact_form_content .= '<br />'."\n";
				$spamfree_contact_form_content .= '<input type="text" id="wpsf_contact_website" name="wpsf_contact_website" value="" size="40" /> </label></p>'."\n";
				}
				
			if ( $FormIncludePhone ) {
				$spamfree_contact_form_content .= '<p><label><strong>Phone</strong> ';
				if ( $FormRequirePhone ) { 
					$spamfree_contact_form_content .= '*'; 
					}
				$spamfree_contact_form_content .= '<br />'."\n";
				$spamfree_contact_form_content .= '<input type="text" id="wpsf_contact_phone" name="wpsf_contact_phone" value="" size="40" /> </label></p>'."\n";
				}

			if ( $FormIncludeCompany ) {
				$spamfree_contact_form_content .= '<p><label><strong>Company</strong> ';
				if ( $FormRequireCompany ) { 
					$spamfree_contact_form_content .= '*'; 
					}
				$spamfree_contact_form_content .= '<br />'."\n";
				$spamfree_contact_form_content .= '<input type="text" id="wpsf_contact_company" name="wpsf_contact_company" value="" size="40" /> </label></p>'."\n";
				}

			if ( $FormIncludeDropDownMenu && $FormDropDownMenuTitle && $FormDropDownMenuItem1 && $FormDropDownMenuItem2 ) {
				$spamfree_contact_form_content .= '<p><label><strong>'.$FormDropDownMenuTitle.'</strong> ';
				if ( $FormRequireDropDownMenu ) { 
					$spamfree_contact_form_content .= '*'; 
					}
				$spamfree_contact_form_content .= '<br />'."\n";
				$spamfree_contact_form_content .= '<select id="wpsf_contact_drop_down_menu" name="wpsf_contact_drop_down_menu" > '."\n";
				$spamfree_contact_form_content .= '<option value="" selected="selected">Please Select</option> '."\n";
				$spamfree_contact_form_content .= '<option value="">--------------------------</option> '."\n";
				if ( $FormDropDownMenuItem1 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem1.'">'.$FormDropDownMenuItem1.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem2 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem2.'">'.$FormDropDownMenuItem2.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem3 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem3.'">'.$FormDropDownMenuItem3.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem4 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem4.'">'.$FormDropDownMenuItem4.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem5 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem5.'">'.$FormDropDownMenuItem5.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem6 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem6.'">'.$FormDropDownMenuItem6.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem7 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem7.'">'.$FormDropDownMenuItem7.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem8 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem8.'">'.$FormDropDownMenuItem8.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem9 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem9.'">'.$FormDropDownMenuItem9.'</option> '."\n";
					}
				if ( $FormDropDownMenuItem10 ) {
					$spamfree_contact_form_content .= '<option value="'.$FormDropDownMenuItem10.'">'.$FormDropDownMenuItem10.'</option> '."\n";
					}
				$spamfree_contact_form_content .= '</select> '."\n";
				$spamfree_contact_form_content .= '</label></p>'."\n";
				}
			
			$spamfree_contact_form_content .= '<p><label><strong>Subject</strong> *<br />'."\n";
    		$spamfree_contact_form_content .= '<input type="text" id="wpsf_contact_subject" name="wpsf_contact_subject" value="" size="40" /> </label></p>'."\n";			

			$spamfree_contact_form_content .= '<p><label><strong>Message</strong> *<br />'."\n";
			$spamfree_contact_form_content .= '<textarea id="wpsf_contact_message" name="wpsf_contact_message" cols="'.$FormMessageWidth.'" rows="'.$FormMessageHeight.'"></textarea> </label></p>'."\n";
			
			if ( ( !$spamfree_options['use_alt_cookie_method'] && !$spamfree_options['use_alt_cookie_method_only'] ) ) {
				$spamfree_contact_form_content .= '<noscript><p><strong>Currently you have JavaScript disabled. In order to use this contact form, please make sure JavaScript and Cookies are enabled, and reload the page.</strong> <a href="http://www.google.com/support/bin/answer.py?answer=23852" rel="nofollow external" >Click here for instructions</a> on how to enable JavaScript in your browser.</p></noscript>'."\n";		
				}

			$spamfree_contact_form_content .= '<p><input type="submit" id="wpsf_contact_submit" name="wpsf_contact_submit" value="Send Message" /></p>'."\n";

			$spamfree_contact_form_content .= '<p>* Required Field</p>'."\n";
			$spamfree_contact_form_content .= '<p>&nbsp;</p>'."\n";
			
			if ( $PromotePluginLink ) {
				$server_ip_first_char = substr($_SERVER['SERVER_ADDR'], 0, 1);
				if ( $server_ip_first_char == '7' ) {
					$spamfree_contact_form_content .= '<p style="font-size:9px;clear:both;"><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree Contact Form for WordPress" >Contact Form</a> Powered by WP-SpamFree</p>'."\n";
					}
				else if ( $server_ip_first_char == '6' ) {
					$spamfree_contact_form_content .= '<p style="font-size:9px;clear:both;">Powered by <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree Contact Form for WordPress" >WP-SpamFree Contact Form</a></p>'."\n";
					}
				else {
					$spamfree_contact_form_content .= '<p style="font-size:9px;clear:both;">Contact Form Powered by <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" title="WP-SpamFree Contact Form for WordPress" >WP-SpamFree</a></p>'."\n";
					}
				$spamfree_contact_form_content .= '<p>&nbsp;</p>'."\n";
				}
			$spamfree_contact_form_content .= '</form>'."\n";
			
			if ( ($_COOKIE[$spamfree_options['cookie_validation_name']] != $spamfree_options['cookie_validation_key'] && $spamfree_options['use_alt_cookie_method'] ) || $spamfree_options['use_alt_cookie_method_only'] ) {
				if ( !eregi( 'opera', $_SERVER['HTTP_USER_AGENT'] ) ) { 
					$wpsf_img_p_disp = ' style="clear:both;display:none;"';
					$wpsf_img_disp = 'display:none;';
					}
				else { 
					$wpsf_img_p_disp = ' style="clear:both;"';
					$wpsf_img_disp = ''; 
					}	
				$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));

				$spamfree_contact_form_content .=  '<span'.$wpsf_img_p_disp.'><img src="'.$wpsf_plugin_url.'/img/wpsf-img.php" width="0" height="0" alt="" style="border-style:none;width:0px;height:0px;'.$wpsf_img_disp.'" /></span>';
				}	
			
			$contact_form_blacklist_status = '';
			$spamfree_contact_form_ip_bans = array(
													'66.60.98.1',
													'67.227.135.200',
													'74.86.148.194',
													'77.92.88.13',
													'77.92.88.27',
													'78.129.202.15',
													'78.129.202.2',
													'78.157.143.202',
													'87.106.55.101',
													'91.121.77.168',
													'92.241.176.200',
													'92.48.122.2',
													'92.48.122.3',
													'92.48.65.27',
													'92.241.168.216',
													'115.42.64.19',
													'116.71.33.252',
													'116.71.35.192',
													'116.71.59.69',
													'122.160.70.94',
													'122.162.251.167',
													'123.237.144.189',
													'123.237.144.92',
													'123.237.147.71',
													'193.37.152.242',
													'193.46.236.151',
													'193.46.236.152',
													'193.46.236.234',
													);
			// Check following variables to make sure not repeating										
			$commentdata_remote_addr_lc = strtolower($_SERVER['REMOTE_ADDR']);
			$commentdata_remote_host_lc = strtolower($_SERVER['REMOTE_HOST']);
			if ( in_array( $commentdata_remote_addr_lc, $spamfree_contact_form_ip_bans ) || eregi( "^78\.129\.202\.", $commentdata_remote_addr_lc ) || eregi( "^123\.237\.144\.", $commentdata_remote_addr_lc ) || eregi( "^123\.237\.147\.", $commentdata_remote_addr_lc ) || eregi( "^194\.8\.7([45])\.", $commentdata_remote_addr_lc ) || eregi( "^193\.37\.152\.", $commentdata_remote_addr_lc ) || eregi( "^193\.46\.236\.", $commentdata_remote_addr_lc ) || eregi( "^92\.48\.122\.([0-9]|[12][0-9]|3[01])$", $commentdata_remote_addr_lc ) || eregi( "^116\.71\.", $commentdata_remote_addr_lc ) || eregi( 'keywordspy.com', $commentdata_remote_host_lc ) || eregi( 'keywordspy.com', $ReverseDNS ) || eregi( "clients\.your-server\.de$", $commentdata_remote_host_lc ) || eregi( "clients\.your-server\.de$", $ReverseDNS ) || eregi( "^rover\-host\.com$", $commentdata_remote_host_lc ) || eregi( "^rover-host\.com$", $ReverseDNS ) || eregi( "^host\.lotosus\.com$", $commentdata_remote_host_lc ) || eregi( "^host\.lotosus\.com$", $ReverseDNS ) || ( eregi( "^192\.168\.", $commentdata_remote_addr_lc ) && !eregi( "^192\.168\.", $_SERVER['SERVER_ADDR'] ) && !eregi( 'localhost', $_SERVER['SERVER_NAME'] ) ) ) {
				// 194.8.74.0 - 194.8.75.255 BAD spam network - BRITISH VIRGIN ISLANDS
				// 193.37.152.0 - 193.37.152.255 SPAM NETWORK - WEB HOST, NOT ISP - GERMANY
				// 193.46.236.0 - 193.46.236.255 SPAM NETWORK - WEB HOST, NOT ISP - LATVIA
				// 92.48.122.0 - 92.48.122.31 SPAM NETWORK - SERVERS, NOT ISP - BELGRADE
				// KeywordSpy caught using IP's in the range 123.237.144. and 123.237.147.
				// 91.121.77.168 real-url.org
				// 92.48.122.0 - 92.48.122.31 SPAM NETWORK - SERVERS, NOT ISP - BELGRADE
				
				// 87.106.55.101 SPAM NETWORK - SERVERS, NOT ISP - (.websitehome.co.uk)
				// 74.86.148.194 SPAM NETWORK - WEB HOST, NOT ISP (rover-host.com)
				// 67.227.135.200 SPAM NETWORK - WEB HOST, NOT ISP (host.lotosus.com)
				// 66.60.98.1 SPAM NETWORK - WEB SITE/HOST, NOT ISP - (rdns.softwiseonline.com)
				// 116.71.0.0 - 116.71.255.255 - SPAM NETWORK - PAKISTAN - Ptcl Triple Play Project
				$contact_form_blacklist_status = '2';
				}
			$user_agent_lc = strtolower(trim($_SERVER['HTTP_USER_AGENT']));
			$user_agent_lc_word_count = count( explode( " ", $user_agent_lc ) );
			if ( !$user_agent_lc ) {
				$contact_form_blacklist_status = '2';
				$spamfree_error_code .= ' CF-UA1001';
				}
			if ( $user_agent_lc && $user_agent_lc_word_count < 3 ) {
				$contact_form_blacklist_status = '2';
				$spamfree_error_code .= ' CF-UA1001.1-'.$user_agent_lc;
				}
			if ( eregi( 'libwww-perl', $user_agent_lc ) || eregi( "^(nutch|larbin|jakarta|java)", $user_agent_lc ) ) {
				$contact_form_blacklist_status = '2';
				$spamfree_error_code .= ' CF-UA1002';
				}
			if ( eregi( 'iopus-', $user_agent_lc ) ) {
				$contact_form_blacklist_status = '2';
				$spamfree_error_code .= ' CF-UA1003';
				}
			$user_http_accept_language = trim($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if ( !$user_http_accept_language ) {
				$contact_form_blacklist_status = '2';
				$spamfree_error_code .= ' CF-HAL1001';
				}

			// Add blacklist check - IP's only though.
				
			if ( $contact_form_blacklist_status ) {
				$spamfree_contact_form_content = '<strong>Your location has been identified as part of a reported spam network. Contact form has been disabled to prevent spam.</strong>';
				}				
			$content_new = str_replace('<!--spamfree-contact-->', $spamfree_contact_form_content, $content);
			}

		}
	if ( $_GET['form'] == response ) {
		$content_new = str_replace($content, $spamfree_contact_form_content, $content);
		}
	else {
		$content_new = str_replace('<!--spamfree-contact-->', $spamfree_contact_form_content, $content);
		}
	return $content_new;
	}
	
function spamfree_check_comment_type($commentdata) {
	
	$spamfree_options = get_option('spamfree_options');
	
	if ( !is_admin() && !current_user_can('moderate_comments') && !current_user_can('edit_post') ) {
		// ONLY IF NOT ADMINS, EDITORS, AUTHORS :: BEGIN
		$BlockAllTrackbacks 		= $spamfree_options['block_all_trackbacks'];
		$BlockAllPingbacks 			= $spamfree_options['block_all_pingbacks'];
	
		$content_short_status		= spamfree_content_short($commentdata);
			
		if ( !$content_short_status ) {
			$content_filter_status 	= spamfree_content_filter($commentdata);
			}
		
		if ( $content_short_status ) {
			add_filter('pre_comment_approved', 'spamfree_denied_post_short', 1);
			}
		else if ( $content_filter_status == '2' ) {
			add_filter('pre_comment_approved', 'spamfree_denied_post_content_filter', 1);
			}
		else if ( $content_filter_status == '10' ) {
			add_filter('pre_comment_approved', 'spamfree_denied_post_proxy', 1);
			}
		else if ( $content_filter_status == '100' ) {
			add_filter('pre_comment_approved', 'spamfree_denied_post_wp_blacklist', 1);
			}
		else if ( $content_filter_status ) {
			add_filter('pre_comment_approved', 'spamfree_denied_post', 1);
			}	
		else if ( ( $commentdata['comment_type'] != 'trackback' && $commentdata['comment_type'] != 'pingback' ) || ( $BlockAllTrackbacks && $BlockAllPingbacks ) || ( $BlockAllTrackbacks && $commentdata['comment_type'] == 'trackback' ) || ( $BlockAllPingbacks && $commentdata['comment_type'] == 'pingback' ) ) {
			// If Comment is not a trackback or pingback, or 
			// Trackbacks and Pingbacks are blocked, or 
			// Trackbacks are blocked and comment is Trackback, or 
			// Pingbacks are blocked and comment is Pingback
			add_filter('pre_comment_approved', 'spamfree_allowed_post', 1);
			
			// LOG DATA :: BEGIN
			if ( $spamfree_options['comment_logging'] ) {
				$CookieValidationName  		= $spamfree_options['cookie_validation_name'];
				$CookieValidationKey 		= $spamfree_options['cookie_validation_key'];
				$FormValidationFieldJS 		= $spamfree_options['form_validation_field_js'];
				$FormValidationKeyJS 		= $spamfree_options['form_validation_key_js'];
				$WPCommentValidationJS 		= $_COOKIE[$CookieValidationName];
				if( $_COOKIE[$spamfree_options['cookie_validation_name']] != $spamfree_options['cookie_validation_key'] ) {
					$spamfree_error_code = 'COOKIE';
					spamfree_log_data( $commentdata, $spamfree_error_code );
					}
				else if ( $spamfree_options['comment_logging_all'] ) {
					$spamfree_error_code = 'No Error';
					spamfree_log_data( $commentdata, $spamfree_error_code );
					}
				}
			// LOG DATA :: END
			}
			
		// ONLY IF NOT ADMINS, EDITORS, AUTHORS :: END
		}

	else if ( $spamfree_options['comment_logging_all'] ) {
		$spamfree_error_code = 'No Error';
		spamfree_log_data( $commentdata, $spamfree_error_code );
		}
			
	return $commentdata;
	}

function spamfree_allowed_post($approved) {
	// TEST TO PREVENT COMMENT SPAM FROM BOTS :: BEGIN
	$spamfree_options			= get_option('spamfree_options');
	$CookieValidationName  		= $spamfree_options['cookie_validation_name'];
	$CookieValidationKey 		= $spamfree_options['cookie_validation_key'];
	$FormValidationFieldJS 		= $spamfree_options['form_validation_field_js'];
	$FormValidationKeyJS 		= $spamfree_options['form_validation_key_js'];
	$KeyUpdateTime 				= $spamfree_options['last_key_update'];
	$WPCommentValidationJS 		= $_COOKIE[$CookieValidationName];
	//$WPFormValidationPost 		= $_POST[$FormValidationFieldJS]; //Comments Post Verification
	//if( $WPCommentValidationJS == $CookieValidationKey ) { // Comment allowed
	if( $_COOKIE[$spamfree_options['cookie_validation_name']] == $spamfree_options['cookie_validation_key'] ) { // Comment allowed
		// Clear Key Values and Update
		$GetCurrentTime = time();
		$ResetIntervalHours = 24; // Reset interval in hours
		$ResetIntervalMinutes = 60; // Reset interval minutes default
		$ResetIntervalMinutesOverride = $ResetIntervalMinutes; // Use as override for testing; leave = $ResetIntervalMinutes when not testing
        if ( $ResetIntervalMinutesOverride != $ResetIntervalMinutes ) {
			$ResetIntervalHours = 1;
			$ResetIntervalMinutes = $ResetIntervalMinutesOverride;
			}
		$TimeThreshold = $GetCurrentTime - ( 60 * $ResetIntervalMinutes * $ResetIntervalHours ); // seconds * minutes * hours
		// This only resets key if over x amount of time after last reset
		if ( $TimeThreshold > $KeyUpdateTime  ) {
			spamfree_update_keys(1);
			}
		return $approved;
		}
	else { // Comment spam killed
	
		// Update Count
		update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
		// Akismet Accuracy Fix :: BEGIN
		// Akismet's counter is currently taking credit for some spams killed by WP-SpamFree - the following ensures accurate reporting.
		// The reason for this fix is that Akismet may have marked the same comment as spam, but WP-SpamFree actually kills it - with or without Akismet.
		$ak_count_pre	= get_option('ak_count_pre');
		$ak_count_post	= get_option('akismet_spam_count');
		if ($ak_count_post > $ak_count_pre) {
			update_option( 'akismet_spam_count', $ak_count_pre );
			}
		// Akismet Accuracy Fix :: END

		$spamfree_jsck_error_ck_test = $_COOKIE['SJECT']; // Default value is 'CKON'
		
		if ( $spamfree_jsck_error_ck_test == 'CKON' ) {
			$spamfree_jsck_error_ck_status = 'PHP detects that cookies appear to be enabled.';
			}
		else {
			$spamfree_jsck_error_ck_status = 'PHP detects that cookies appear to be disabled. <script type="text/javascript">if (navigator.cookieEnabled==true) { document.write(\'(However, JavaScript detects that cookies are enabled.)\'); } else { document.write(\'\(JavaScript also detects that cookies are disabled.\)\'); }; </script>';
			}
		
		$spamfree_jsck_error_message_standard = 'Sorry, there was an error. Please be sure JavaScript and Cookies are enabled in your browser and try again.';

		$spamfree_jsck_error_message_detailed = '<span style="font-size:12px;"><strong>Sorry, there was an error. JavaScript and Cookies are required in order to post a comment.</strong><br /><br />'."\n";
		$spamfree_jsck_error_message_detailed .= '<noscript>Status: JavaScript is currently disabled.<br /><br /></noscript>'."\n";
		$spamfree_jsck_error_message_detailed .= '<strong>Please be sure JavaScript and Cookies are enabled in your browser. Then, please hit the back button on your browser, and try posting your comment again. (You may need to reload the page)</strong><br /><br />'."\n";
		$spamfree_jsck_error_message_detailed .= '<br /><hr noshade />'."\n";
		if ( $spamfree_jsck_error_ck_test == 'CKON' ) {
			$spamfree_jsck_error_message_detailed .= 'If you feel you have received this message in error (for example <em>if JavaScript and Cookies are in fact enabled</em> and you have tried to post several times), there is most likely a technical problem (could be a plugin conflict or misconfiguration). Please contact the author of this blog, and let them know they need to look into it.<br />'."\n";
			$spamfree_jsck_error_message_detailed .= '<hr noshade /><br />'."\n";
			}
		$spamfree_jsck_error_message_detailed .= '</span>'."\n";
		//$spamfree_jsck_error_message_detailed .= '<span style="font-size:9px;">This message was generated by WP-SpamFree.</span><br /><br />'."\n";
	
		$spamfree_imgphpck_error_message_standard = 'Sorry, there was an error. Please enable Images and Cookies in your browser and try again.';
		
		$spamfree_imgphpck_error_message_detailed = '<span style="font-size:12px;"><strong>Sorry, there was an error. Images and Cookies are required in order to post a comment.<br/>You appear to have at least one of these disabled.</strong><br /><br />'."\n";
		$spamfree_imgphpck_error_message_detailed .= '<strong>Please enable Images and Cookies in your browser. Then, please go back, reload the page, and try posting your comment again.</strong><br /><br />'."\n";
		$spamfree_imgphpck_error_message_detailed .= '<br /><hr noshade />'."\n";
		$spamfree_imgphpck_error_message_detailed .= 'If you feel you have received this message in error (for example <em>if Images and Cookies are in fact enabled</em> and you have tried to post several times), please alert the author of this blog, and let them know they need to look into it.<br />'."\n";
		$spamfree_imgphpck_error_message_detailed .= '<hr noshade /><br /></span>'."\n";
		//$spamfree_imgphpck_error_message_detailed .= '<span style="font-size:9px;">This message was generated by WP-SpamFree.</span><br /><br />'."\n";

		if( $spamfree_options['use_alt_cookie_method_only'] ) {
			wp_die( __($spamfree_imgphpck_error_message_detailed) );
			}
		else {
			wp_die( __($spamfree_jsck_error_message_detailed) );
			}
			
		return false;
		}
	// TEST TO PREVENT COMMENT SPAM FROM BOTS :: END
	}
		
function spamfree_denied_post($approved) {
	// REJECT SPAM :: BEGIN
	
	// Update Count
	update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
	// Akismet Accuracy Fix :: BEGIN
	// Akismet's counter is currently taking credit for some spams killed by WP-SpamFree - the following ensures accurate reporting.
	// The reason for this fix is that Akismet may have marked the same comment as spam, but WP-SpamFree actually kills it - with or without Akismet.
	$ak_count_pre	= get_option('ak_count_pre');
	$ak_count_post	= get_option('akismet_spam_count');
	if ($ak_count_post > $ak_count_pre) {
		update_option( 'akismet_spam_count', $ak_count_pre );
		}
	// Akismet Accuracy Fix :: END

	$spamfree_filter_error_message_standard = '<span style="font-size:12px;">Comments have been temporarily disabled to prevent spam. Please try again later.</span>'; // Stop spammers without revealing why.
	
	$spamfree_filter_error_message_detailed = '<span style="font-size:12px;"><strong>Hmmm, your comment seems a bit spammy. We\'re not real big on spam around here.</strong><br /><br />'."\n";
	$spamfree_filter_error_message_detailed .= 'Please go back and try again.</span>'."\n";

	wp_die( __($spamfree_filter_error_message_detailed) );
	return false;
	// REJECT SPAM :: END
	}

function spamfree_denied_post_short($approved) {
	// REJECT SHORT COMMENTS :: BEGIN

	// Update Count
	update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
	// Akismet Accuracy Fix :: BEGIN
	// Akismet's counter is currently taking credit for some spams killed by WP-SpamFree - the following ensures accurate reporting.
	// The reason for this fix is that Akismet may have marked the same comment as spam, but WP-SpamFree actually kills it - with or without Akismet.
	$ak_count_pre	= get_option('ak_count_pre');
	$ak_count_post	= get_option('akismet_spam_count');
	if ($ak_count_post > $ak_count_pre) {
		update_option( 'akismet_spam_count', $ak_count_pre );
		}
	// Akismet Accuracy Fix :: END

	wp_die( __('<span style="font-size:12px;">Your comment was a bit too short. Please go back and try again.</span>') );
	return false;
	// REJECT SHORT COMMENTS :: END
	}
	
function spamfree_denied_post_content_filter($approved) {
	// REJECT BASED ON CONTENT FILTER :: BEGIN

	// Update Count
	update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
	// Akismet Accuracy Fix :: BEGIN
	// Akismet's counter is currently taking credit for some spams killed by WP-SpamFree - the following ensures accurate reporting.
	// The reason for this fix is that Akismet may have marked the same comment as spam, but WP-SpamFree actually kills it - with or without Akismet.
	$ak_count_pre	= get_option('ak_count_pre');
	$ak_count_post	= get_option('akismet_spam_count');
	if ($ak_count_post > $ak_count_pre) {
		update_option( 'akismet_spam_count', $ak_count_pre );
		}
	// Akismet Accuracy Fix :: END
	
	$spamfree_content_filter_error_message_detailed = '<span style="font-size:12px;"><strong>Your location has been identified as part of a reported spam network. Comments have been disabled to prevent spam.</strong><br /><br /></span>'."\n";
	
	wp_die( __($spamfree_content_filter_error_message_detailed) );
	return false;
	// REJECT BASED ON COMMENT FILTER :: END
	}
	
function spamfree_denied_post_proxy($approved) {
	// REJECT PROXY COMMENTERS :: BEGIN

	// Update Count
	update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
	// Akismet Accuracy Fix :: BEGIN
	$ak_count_pre	= get_option('ak_count_pre');
	$ak_count_post	= get_option('akismet_spam_count');
	if ($ak_count_post > $ak_count_pre) {
		update_option( 'akismet_spam_count', $ak_count_pre );
		}
	// Akismet Accuracy Fix :: END
	
	$spamfree_proxy_error_message_detailed = '<span style="font-size:12px;"><strong>Your comment has been blocked because the blog owner has set their spam filter to not allow comments from users behind proxies.</strong><br/><br/>If you are a regular commenter or you feel that your comment should not have been blocked, please contact the blog owner and ask them to modify this setting.<br /><br /></span>'."\n";
	
	wp_die( __($spamfree_proxy_error_message_detailed) );
	return false;
	// REJECT PROXY COMMENTERS :: END
	}

function spamfree_denied_post_wp_blacklist($approved) {
	// REJECT BLACKLISTED COMMENTERS :: BEGIN

	// Update Count
	update_option( 'spamfree_count', get_option('spamfree_count') + 1 );
	// Akismet Accuracy Fix :: BEGIN
	$ak_count_pre	= get_option('ak_count_pre');
	$ak_count_post	= get_option('akismet_spam_count');
	if ($ak_count_post > $ak_count_pre) {
		update_option( 'akismet_spam_count', $ak_count_pre );
		}
	// Akismet Accuracy Fix :: END
	
	$spamfree_blacklist_error_message_detailed = '<span style="font-size:12px;"><strong>Your comment has been blocked based on the blog owner\'s blacklist settings.</strong><br/><br/>If you feel this is in error, please contact the blog owner by some other method.<br /><br /></span>'."\n";
	
	wp_die( __($spamfree_blacklist_error_message_detailed) );
	return false;
	// REJECT BLACKLISTED COMMENTERS :: END
	}

function spamfree_content_short($commentdata) {
	// COMMENT LENGTH CHECK :: BEGIN
	$commentdata_comment_content					= $commentdata['comment_content'];
	$commentdata_comment_content_lc					= strtolower($commentdata_comment_content);
	$commentdata_comment_content_lc_stripped		= stripslashes($commentdata_comment_content_lc);
	
	$commentdata_comment_content_length 			= strlen($commentdata_comment_content_lc);
	$commentdata_comment_content_min_length 		= 15;
	
	$commentdata_comment_type						= $commentdata['comment_type'];
	
	if( $commentdata_comment_content_length < $commentdata_comment_content_min_length && $commentdata_comment_type != 'trackback' && $commentdata_comment_type != 'pingback' ) {
		$content_short_status = true;
		$spamfree_error_code .= ' SHORT15';
		}
	
	if ( !$spamfree_error_code ) {
		$spamfree_error_code = 'No Error';
		}
	else {
		$spamfree_error_code = ltrim($spamfree_error_code);
		$spamfree_options = get_option('spamfree_options');
		if ( $spamfree_options['comment_logging'] ) {
			spamfree_log_data( $commentdata, $spamfree_error_code );
			}
		}
	
	$spamfree_error_data = array( $spamfree_error_code, $blacklist_word_combo, $blacklist_word_combo_total );
	
	return $content_short_status;
	// COMMENT LENGTH CHECK :: END
	}
	
function spamfree_content_filter($commentdata) {
	// Supplementary Defense - Blocking the Obvious to Improve Human/Pingback/Trackback Defense
	// FYI, Certain loops are unrolled because of a weird compatibility issue with certain servers. Works fine on most, but for some unforeseen reason, a few have issues. When I get more time to test, will try to figure it out. for now these have to stay unrolled. Won't require any more server resources, just more lines of code. Overall, still a tiny program for a server to run.

	$spamfree_options = get_option('spamfree_options');
	
	// CONTENT FILTERING :: BEGIN
	$CurrentWordPressVersionMaxCheck = '3.0';
	
	$commentdata_comment_author						= $commentdata['comment_author'];
	$commentdata_comment_author_lc					= strtolower($commentdata_comment_author);
	$commentdata_comment_author_lc_space 			= ' '.$commentdata_comment_author_lc.' ';
	$commentdata_comment_author_email				= $commentdata['comment_author_email'];
	$commentdata_comment_author_email_lc			= strtolower($commentdata_comment_author_email);
	$commentdata_comment_author_url					= $commentdata['comment_author_url'];
	$commentdata_comment_author_url_lc				= strtolower($commentdata_comment_author_url);
	
	$commentdata_comment_content					= $commentdata['comment_content'];
	$commentdata_comment_content_lc					= strtolower($commentdata_comment_content);
	
	$replace_apostrophes							= array('\’','\`','&acute;','&grave;','&#39;','&#96;','&#101;','&#145;','&#146;','&#158;','&#180;','&#207;','&#208;','&#8216;','&#8217;');
	$commentdata_comment_content_lc_norm_apost 		= str_replace($replace_apostrophes,"\'",$commentdata_comment_content_lc);
	
	$commentdata_comment_type						= $commentdata['comment_type'];
	
	// Altered to Accommodate WP 2.5+
	$commentdata_user_agent					= $_SERVER['HTTP_USER_AGENT'];
	$commentdata_user_agent_lc				= strtolower($commentdata_user_agent);
	$commentdata_remote_addr				= $_SERVER['REMOTE_ADDR'];
	$commentdata_remote_addr_lc				= strtolower($commentdata_remote_addr);
	$commentdata_remote_host				= $_SERVER['REMOTE_HOST'];
	$commentdata_remote_host_lc				= strtolower($commentdata_remote_host);
	$commentdata_referrer					= $_SERVER['HTTP_REFERER'];
	$commentdata_referrer_lc				= strtolower($commentdata_referrer);
	$commentdata_blog						= get_option('siteurl');
	$commentdata_blog_lc					= strtolower($commentdata_blog);
	$commentdata_php_self					= $_SERVER['PHP_SELF'];
	$commentdata_php_self_lc				= strtolower($commentdata_php_self);
	
	if ( !$commentdata_remote_host_lc ) {
		$commentdata_remote_host_lc = 'blank';
		}
		
	$BlogServerIP = $_SERVER['SERVER_ADDR'];
	$BlogServerName = $_SERVER['SERVER_NAME'];

	// IP / PROXY INFO :: BEGIN
	$ipBlock=explode('.',$commentdata_remote_addr);
	$ipProxyVIA=$_SERVER['HTTP_VIA'];
	$MaskedIP=$_SERVER['HTTP_X_FORWARDED_FOR']; // Stated Original IP - Can be faked
	$MaskedIPBlock=explode('.',$MaskedIP);
	if (eregi("^([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])",$MaskedIP)&&$MaskedIP!=""&&$MaskedIP!="unknown"&&!eregi("^192.168.",$MaskedIP)) {
		$MaskedIPValid=true;
		$MaskedIPCore=rtrim($MaskedIP," unknown;,");
		}
	if ( !$MaskedIP ) { $MaskedIP='[no data]'; }
	$ReverseDNS = gethostbyaddr($commentdata_remote_addr);
	$ReverseDNSIP = gethostbyname($ReverseDNS);
	
	if ( $ReverseDNSIP != $commentdata_remote_addr || $commentdata_remote_addr == $ReverseDNS ) {
		$ReverseDNSAuthenticity = '[Possibly Forged]';
		} 
	else {
		$ReverseDNSAuthenticity = '[Verified]';
		}
	// Detect Use of Proxy
	if ($_SERVER['HTTP_VIA']||$_SERVER['HTTP_X_FORWARDED_FOR']) {
		$ipProxy='PROXY DETECTED';
		$ipProxyShort='PROXY';
		$ipProxyData=$commentdata_remote_addr.' | MASKED IP: '.$MaskedIP;
		$ProxyStatus='TRUE';
		}
	else {
		$ipProxy='No Proxy';
		$ipProxyShort=$ipProxy;
		$ipProxyData=$commentdata_remote_addr;
		$ProxyStatus='FALSE';
		}
	// IP / PROXY INFO :: END

	// Simple Filters
	
	$blacklist_word_combo_total_limit = 10; // you may increase to 30+ if blog's topic is adult in nature
	$blacklist_word_combo_total = 0;
	
	// Filter 1: Number of occurrences of 'http://' in comment_content
	$filter_1_count_http = substr_count($commentdata_comment_content_lc, 'http://');
	$filter_1_count_https = substr_count($commentdata_comment_content_lc, 'https://');
	$filter_1_count = $filter_1_count_http + $filter_1_count_https;
	$filter_1_limit = 4;
	$filter_1_trackback_limit = 1;
	
	// Medical-Related Filters
	
	/*
	// Filter 2: Number of occurrences of 'viagra' in comment_content
	$filter_2_count = substr_count($commentdata_comment_content_lc, 'viagra');
	$filter_2_limit = 2;
	// Filter 3: Number of occurrences of 'v1agra' in comment_content
	$filter_3_count = substr_count($commentdata_comment_content_lc, 'v1agra');
	$filter_3_limit = 1;
	// Filter 4: Number of occurrences of 'cialis' in comment_content
	$filter_4_count = substr_count($commentdata_comment_content_lc, 'cialis');
	$filter_4_limit = 2;
	// Filter 5: Number of occurrences of 'c1alis' in comment_content
	$filter_5_count = substr_count($commentdata_comment_content_lc, 'c1alis');
	$filter_5_limit = 1;
	// Filter 6: Number of occurrences of 'levitra' in comment_content
	$filter_6_count = substr_count($commentdata_comment_content_lc, 'levitra');
	$filter_6_limit = 2;
	// Filter 7: Number of occurrences of 'lev1tra' in comment_content
	$filter_7_count = substr_count($commentdata_comment_content_lc, 'lev1tra');
	$filter_7_limit = 1;
	// Filter 8: Number of occurrences of 'erectile dysfunction ' in comment_content
	$filter_8_count = substr_count($commentdata_comment_content_lc, 'erectile dysfunction ');
	$filter_8_limit = 2;
	// Filter 9: Number of occurrences of 'erection' in comment_content
	$filter_9_count = substr_count($commentdata_comment_content_lc, 'erection');
	$filter_9_limit = 2;
	// Filter 10: Number of occurrences of 'erectile' in comment_content
	$filter_10_count = substr_count($commentdata_comment_content_lc, 'erectile');
	$filter_10_limit = 2;
	// Filter 11: Number of occurrences of 'xanax' in comment_content
	$filter_11_count = substr_count($commentdata_comment_content_lc, 'xanax');
	$filter_11_limit = 5;
	// Filter 12: Number of occurrences of 'valium' in comment_content
	$filter_12_count = substr_count($commentdata_comment_content_lc, 'valium');
	$filter_12_limit = 5;
	*/
	
	// Dev Note: Redo later to use word breaks in php regex
	
	$filter_2_term = 'viagra';
	$filter_2_count = substr_count($commentdata_comment_content_lc, $filter_2_term);
	$filter_2_limit = 2;
	$filter_2_trackback_limit = 1;
	$filter_2_author_count = substr_count($commentdata_comment_author_lc, $filter_2_term);
	$filter_2_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_2_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_2_author_count;
	// Filter 3: Number of occurrences of 'v1agra' in comment_content
	$filter_3_term = 'v1agra';
	$filter_3_count = substr_count($commentdata_comment_content_lc, $filter_3_term);
	$filter_3_limit = 1;
	$filter_3_trackback_limit = 1;
	$filter_3_author_count = substr_count($commentdata_comment_author_lc, $filter_3_term);
	$filter_3_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_3_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_3_author_count;
	// Filter 4: Number of occurrences of ' cialis' in comment_content
	$filter_4_term = 'cialis'; 
	// Testing something next 4 lines. Will make more efficient soon.
	$filter_4_term_space = ' '.$filter_4_term; 
	$filter_4_term_slash = '-'.$filter_4_term; 
	$filter_4_term_dash = '/'.$filter_4_term;
	$filter_4_count = substr_count($commentdata_comment_content_lc, $filter_4_term_space)+substr_count($commentdata_comment_content_lc, $filter_4_term_slash)+substr_count($commentdata_comment_content_lc, $filter_4_term_dash);
	//$filter_4_count = substr_count($commentdata_comment_content_lc, $filter_4_term);
	$filter_4_limit = 2;
	$filter_4_trackback_limit = 1;
	$filter_4_author_count = substr_count($commentdata_comment_author_lc, $filter_4_term_space)+substr_count($commentdata_comment_author_lc, $filter_4_term_slash)+substr_count($commentdata_comment_author_lc, $filter_4_term_dash);
	//$filter_4_author_count = substr_count($commentdata_comment_author_lc, $filter_4_term);
	$filter_4_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_4_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_4_author_count;
	// Filter 5: Number of occurrences of 'c1alis' in comment_content
	$filter_5_term = 'c1alis';
	$filter_5_count = substr_count($commentdata_comment_content_lc, $filter_5_term);
	$filter_5_limit = 1;
	$filter_5_trackback_limit = 1;
	$filter_5_author_count = substr_count($commentdata_comment_author_lc, $filter_5_term);
	$filter_5_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_5_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_5_author_count;
	// Filter 6: Number of occurrences of 'levitra' in comment_content
	$filter_6_term = 'levitra';
	$filter_6_count = substr_count($commentdata_comment_content_lc, $filter_6_term);
	$filter_6_limit = 2;
	$filter_6_trackback_limit = 1;
	$filter_6_author_count = substr_count($commentdata_comment_author_lc, $filter_6_term);
	$filter_6_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_6_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_6_author_count;
	// Filter 7: Number of occurrences of 'lev1tra' in comment_content
	$filter_7_term = 'lev1tra';
	$filter_7_count = substr_count($commentdata_comment_content_lc, $filter_7_term);
	$filter_7_limit = 1;
	$filter_7_trackback_limit = 1;
	$filter_7_author_count = substr_count($commentdata_comment_author_lc, $filter_7_term);
	$filter_7_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_7_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_7_author_count;
	// Filter 8: Number of occurrences of 'erectile dysfunction' in comment_content
	$filter_8_term = 'erectile dysfunction';
	$filter_8_count = substr_count($commentdata_comment_content_lc, $filter_8_term);
	$filter_8_limit = 2;
	$filter_8_trackback_limit = 1;
	$filter_8_author_count = substr_count($commentdata_comment_author_lc, $filter_8_term);
	$filter_8_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_8_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_8_author_count;
	// Filter 9: Number of occurrences of 'erection' in comment_content
	$filter_9_term = 'erection';
	$filter_9_count = substr_count($commentdata_comment_content_lc, $filter_9_term);
	$filter_9_limit = 3;
	$filter_9_trackback_limit = 1;
	$filter_9_author_count = substr_count($commentdata_comment_author_lc, $filter_9_term);
	$filter_9_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_9_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_9_author_count;
	// Filter 10: Number of occurrences of 'erectile' in comment_content
	$filter_10_term = 'erectile';
	$filter_10_count = substr_count($commentdata_comment_content_lc, $filter_10_term);
	$filter_10_limit = 2;
	$filter_10_trackback_limit = 1;
	$filter_10_author_count = substr_count($commentdata_comment_author_lc, $filter_10_term);
	$filter_10_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_10_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_10_author_count;
	// Filter 11: Number of occurrences of 'xanax' in comment_content
	$filter_11_term = 'xanax';
	$filter_11_count = substr_count($commentdata_comment_content_lc, $filter_11_term);
	$filter_11_limit = 3;
	$filter_11_trackback_limit = 2;
	$filter_11_author_count = substr_count($commentdata_comment_author_lc, $filter_11_term);
	$filter_11_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_11_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_11_author_count;
	// Filter 12: Number of occurrences of 'zithromax' in comment_content
	$filter_12_term = 'zithromax';
	$filter_12_count = substr_count($commentdata_comment_content_lc, $filter_12_term);
	$filter_12_limit = 3;
	$filter_12_trackback_limit = 2;
	$filter_12_author_count = substr_count($commentdata_comment_author_lc, $filter_12_term);
	$filter_12_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_12_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_12_author_count;
	// Filter 13: Number of occurrences of 'phentermine' in comment_content
	$filter_13_term = 'phentermine';
	$filter_13_count = substr_count($commentdata_comment_content_lc, $filter_13_term);
	$filter_13_limit = 3;
	$filter_13_trackback_limit = 2;
	$filter_13_author_count = substr_count($commentdata_comment_author_lc, $filter_13_term);
	$filter_13_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_13_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_13_author_count;
	// Filter 14: Number of occurrences of ' soma ' in comment_content
	$filter_14_term = ' soma ';
	$filter_14_count = substr_count($commentdata_comment_content_lc, $filter_14_term);
	$filter_14_limit = 3;
	$filter_14_trackback_limit = 2;
	$filter_14_author_count = substr_count($commentdata_comment_author_lc, $filter_14_term);
	$filter_14_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_14_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_14_author_count;
	// Filter 15: Number of occurrences of ' soma.' in comment_content
	$filter_15_term = ' soma.';
	$filter_15_count = substr_count($commentdata_comment_content_lc, $filter_15_term);
	$filter_15_limit = 3;
	$filter_15_trackback_limit = 2;
	$filter_15_author_count = substr_count($commentdata_comment_author_lc, $filter_15_term);
	$filter_15_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_15_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_15_author_count;
	// Filter 16: Number of occurrences of 'prescription' in comment_content
	$filter_16_term = 'prescription';
	$filter_16_count = substr_count($commentdata_comment_content_lc, $filter_16_term);
	$filter_16_limit = 3;
	$filter_16_trackback_limit = 2;
	$filter_16_author_count = substr_count($commentdata_comment_author_lc, $filter_16_term);
	$filter_16_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_16_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_16_author_count;
	// Filter 17: Number of occurrences of 'tramadol' in comment_content
	$filter_17_term = 'tramadol';
	$filter_17_count = substr_count($commentdata_comment_content_lc, $filter_17_term);
	$filter_17_limit = 3;
	$filter_17_trackback_limit = 2;
	$filter_17_author_count = substr_count($commentdata_comment_author_lc, $filter_17_term);
	$filter_17_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_17_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_17_author_count;
	// Filter 18: Number of occurrences of 'penis enlargement' in comment_content
	$filter_18_term = 'penis enlargement';
	$filter_18_count = substr_count($commentdata_comment_content_lc, $filter_18_term);
	$filter_18_limit = 2;
	$filter_18_trackback_limit = 1;
	$filter_18_author_count = substr_count($commentdata_comment_author_lc, $filter_18_term);
	$filter_18_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_18_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_18_author_count;
	// Filter 19: Number of occurrences of 'buy pills' in comment_content
	$filter_19_term = 'buy pills';
	$filter_19_count = substr_count($commentdata_comment_content_lc, $filter_19_term);
	$filter_19_limit = 3;
	$filter_19_trackback_limit = 2;
	$filter_19_author_count = substr_count($commentdata_comment_author_lc, $filter_19_term);
	$filter_19_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_19_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_19_author_count;
	// Filter 20: Number of occurrences of 'diet pill' in comment_content
	$filter_20_term = 'diet pill';
	$filter_20_count = substr_count($commentdata_comment_content_lc, $filter_20_term);
	$filter_20_limit = 3;
	$filter_20_trackback_limit = 2;
	$filter_20_author_count = substr_count($commentdata_comment_author_lc, $filter_20_term);
	$filter_20_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_20_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_20_author_count;
	// Filter 21: Number of occurrences of 'weight loss pill' in comment_content
	$filter_21_term = 'weight loss pill';
	$filter_21_count = substr_count($commentdata_comment_content_lc, $filter_21_term);
	$filter_21_limit = 3;
	$filter_21_trackback_limit = 2;
	$filter_21_author_count = substr_count($commentdata_comment_author_lc, $filter_21_term);
	$filter_21_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_21_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_21_author_count;
	// Filter 22: Number of occurrences of 'pill' in comment_content
	$filter_22_term = 'pill';
	$filter_22_count = substr_count($commentdata_comment_content_lc, $filter_22_term);
	$filter_22_limit = 10;
	$filter_22_trackback_limit = 2;
	$filter_22_author_count = substr_count($commentdata_comment_author_lc, $filter_22_term);
	$filter_22_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_22_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_22_author_count;
	// Filter 23: Number of occurrences of ' pill,' in comment_content
	$filter_23_term = ' pill,';
	$filter_23_count = substr_count($commentdata_comment_content_lc, $filter_23_term);
	$filter_23_limit = 5;
	$filter_23_trackback_limit = 2;
	$filter_23_author_count = substr_count($commentdata_comment_author_lc, $filter_23_term);
	$filter_23_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_23_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_23_author_count;
	// Filter 24: Number of occurrences of ' pills,' in comment_content
	$filter_24_term = ' pills,';
	$filter_24_count = substr_count($commentdata_comment_content_lc, $filter_24_term);
	$filter_24_limit = 5;
	$filter_24_trackback_limit = 2;
	$filter_24_author_count = substr_count($commentdata_comment_author_lc, $filter_24_term);
	$filter_24_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_24_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_24_author_count;
	// Filter 25: Number of occurrences of 'propecia' in comment_content
	$filter_25_term = 'propecia';
	$filter_25_count = substr_count($commentdata_comment_content_lc, $filter_25_term);
	$filter_25_limit = 2;
	$filter_25_trackback_limit = 1;
	$filter_25_author_count = substr_count($commentdata_comment_author_lc, $filter_25_term);
	$filter_25_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_25_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_25_author_count;
	// Filter 26: Number of occurrences of 'propec1a' in comment_content
	$filter_26_term = 'propec1a';
	$filter_26_count = substr_count($commentdata_comment_content_lc, $filter_26_term);
	$filter_26_limit = 1;
	$filter_26_trackback_limit = 1;
	$filter_26_author_count = substr_count($commentdata_comment_author_lc, $filter_26_term);
	$filter_26_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_26_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_26_author_count;
	// Filter 27: Number of occurrences of 'online pharmacy' in comment_content
	$filter_27_term = 'online pharmacy';
	$filter_27_count = substr_count($commentdata_comment_content_lc, $filter_27_term);
	$filter_27_limit = 5;
	$filter_27_trackback_limit = 2;
	$filter_27_author_count = substr_count($commentdata_comment_author_lc, $filter_27_term);
	$filter_27_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_27_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_27_author_count;
	// Filter 28: Number of occurrences of 'medication' in comment_content
	$filter_28_term = 'medication';
	$filter_28_count = substr_count($commentdata_comment_content_lc, $filter_28_term);
	$filter_28_limit = 7;
	$filter_28_trackback_limit = 3;
	$filter_28_author_count = substr_count($commentdata_comment_author_lc, $filter_28_term);
	$filter_28_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_28_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_28_author_count;
	// Filter 29: Number of occurrences of 'buy now' in comment_content
	$filter_29_term = 'buy now';
	$filter_29_count = substr_count($commentdata_comment_content_lc, $filter_29_term);
	$filter_29_limit = 7;
	$filter_29_trackback_limit = 3;
	$filter_29_author_count = substr_count($commentdata_comment_author_lc, $filter_29_term);
	$filter_29_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_29_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_29_author_count;
	// Filter 30: Number of occurrences of 'ephedrin' in comment_content
	$filter_30_term = 'ephedrin';
	$filter_30_count = substr_count($commentdata_comment_content_lc, $filter_30_term);
	$filter_30_limit = 3;
	$filter_30_trackback_limit = 2;
	$filter_30_author_count = substr_count($commentdata_comment_author_lc, $filter_30_term);
	$filter_30_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_30_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_30_author_count;
	// Filter 31: Number of occurrences of 'ephedrin' in comment_content
	$filter_31_term = 'ephedrine';
	$filter_31_count = substr_count($commentdata_comment_content_lc, $filter_31_term);
	$filter_31_limit = 3;
	$filter_31_trackback_limit = 2;
	$filter_31_author_count = substr_count($commentdata_comment_author_lc, $filter_31_term);
	$filter_31_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_31_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_31_author_count;
	// Filter 32: Number of occurrences of 'ephedrin' in comment_content
	$filter_32_term = 'ephedr1n';
	$filter_32_count = substr_count($commentdata_comment_content_lc, $filter_32_term);
	$filter_32_limit = 1;
	$filter_32_trackback_limit = 1;
	$filter_32_author_count = substr_count($commentdata_comment_author_lc, $filter_32_term);
	$filter_32_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_32_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_32_author_count;
	// Filter 33: Number of occurrences of 'ephedrin' in comment_content
	$filter_33_term = 'ephedr1ne';
	$filter_33_count = substr_count($commentdata_comment_content_lc, $filter_33_term);
	$filter_33_limit = 1;
	$filter_33_trackback_limit = 1;
	$filter_33_author_count = substr_count($commentdata_comment_author_lc, $filter_33_term);
	$filter_33_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_33_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_33_author_count;
	// Filter 34: Number of occurrences of 'ephedra' in comment_content
	$filter_34_term = 'ephedra';
	$filter_34_count = substr_count($commentdata_comment_content_lc, $filter_34_term);
	$filter_34_limit = 3;
	$filter_34_trackback_limit = 2;
	$filter_34_author_count = substr_count($commentdata_comment_author_lc, $filter_34_term);
	$filter_34_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_34_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_34_author_count;
	// Filter 35: Number of occurrences of 'valium' in comment_content
	$filter_35_term = 'valium';
	$filter_35_count = substr_count($commentdata_comment_content_lc, $filter_35_term);
	$filter_35_limit = 3;
	$filter_35_trackback_limit = 2;
	$filter_35_author_count = substr_count($commentdata_comment_author_lc, $filter_35_term);
	$filter_35_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_35_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_35_author_count;
	// Filter 36: Number of occurrences of 'adipex' in comment_content
	$filter_36_term = 'adipex';
	$filter_36_count = substr_count($commentdata_comment_content_lc, $filter_36_term);
	$filter_36_limit = 3;
	$filter_36_trackback_limit = 2;
	$filter_36_author_count = substr_count($commentdata_comment_author_lc, $filter_36_term);
	$filter_36_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_36_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_36_author_count;
	// Filter 37: Number of occurrences of 'accutane' in comment_content
	$filter_37_term = 'accutane';
	$filter_37_count = substr_count($commentdata_comment_content_lc, $filter_37_term);
	$filter_37_limit = 3;
	$filter_37_trackback_limit = 2;
	$filter_37_author_count = substr_count($commentdata_comment_author_lc, $filter_37_term);
	$filter_37_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_37_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_37_author_count;
	// Filter 38: Number of occurrences of 'acomplia' in comment_content
	$filter_38_term = 'acomplia';
	$filter_38_count = substr_count($commentdata_comment_content_lc, $filter_38_term);
	$filter_38_limit = 3;
	$filter_38_trackback_limit = 2;
	$filter_38_author_count = substr_count($commentdata_comment_author_lc, $filter_38_term);
	$filter_38_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_38_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_38_author_count;
	// Filter 39: Number of occurrences of 'rimonabant' in comment_content
	$filter_39_term = 'rimonabant';
	$filter_39_count = substr_count($commentdata_comment_content_lc, $filter_39_term);
	$filter_39_limit = 3;
	$filter_39_trackback_limit = 2;
	$filter_39_author_count = substr_count($commentdata_comment_author_lc, $filter_39_term);
	$filter_39_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_39_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_39_author_count;
	// Filter 40: Number of occurrences of 'zimulti' in comment_content
	$filter_40_term = 'zimulti';
	$filter_40_count = substr_count($commentdata_comment_content_lc, $filter_40_term);
	$filter_40_limit = 3;
	$filter_40_trackback_limit = 2;
	$filter_40_author_count = substr_count($commentdata_comment_author_lc, $filter_40_term);
	$filter_40_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_40_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_40_author_count;
	// Filter 41: Number of occurrences of 'herbalife' in comment_content
	$filter_41_term = 'herbalife';
	$filter_41_count = substr_count($commentdata_comment_content_lc, $filter_41_term);
	$filter_41_limit = 8;
	$filter_41_trackback_limit = 7;
	$filter_41_author_count = substr_count($commentdata_comment_author_lc, $filter_41_term);
	$filter_41_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_41_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_41_author_count;


	// Non-Medical Author Tests
	// Filter 210: Number of occurrences of 'drassyassut' in comment_content
	$filter_210_term = 'drassyassut'; //DrassyassuT
	$filter_210_count = substr_count($commentdata_comment_content_lc, $filter_210_term);
	$filter_210_limit = 1;
	$filter_210_trackback_limit = 1;
	$filter_210_author_count = substr_count($commentdata_comment_author_lc, $filter_210_term);
	$filter_210_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_210_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_210_author_count;

	// Sex-Related Filter
	// Filter 104: Number of occurrences of 'porn' in comment_content
	$filter_104_count = substr_count($commentdata_comment_content_lc, 'porn');
	$filter_104_limit = 5;
	$filter_104_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_104_count;
	// Filter 105: Number of occurrences of 'teen porn' in comment_content
	$filter_105_count = substr_count($commentdata_comment_content_lc, 'teen porn');
	$filter_105_limit = 1;
	$filter_105_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_105_count;
	// Filter 106: Number of occurrences of 'rape porn' in comment_content
	$filter_106_count = substr_count($commentdata_comment_content_lc, 'rape porn');
	$filter_106_limit = 1;
	$filter_106_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_106_count;
	// Filter 107: Number of occurrences of 'incest porn' in comment_content
	$filter_107_count = substr_count($commentdata_comment_content_lc, 'incest porn');
	$filter_107_limit = 1;
	$filter_107_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_107_count;
	// Filter 108: Number of occurrences of 'hentai' in comment_content
	$filter_108_count = substr_count($commentdata_comment_content_lc, 'hentai');
	$filter_108_limit = 2;
	$filter_108_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_108_count;
	// Filter 109: Number of occurrences of 'sex movie' in comment_content
	$filter_109_count = substr_count($commentdata_comment_content_lc, 'sex movie');
	$filter_109_limit = 2;
	$filter_109_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_109_count;
	// Filter 110: Number of occurrences of 'sex tape' in comment_content
	$filter_110_count = substr_count($commentdata_comment_content_lc, 'sex tape');
	$filter_110_limit = 2;
	$filter_110_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_110_count;
	// Filter 111: Number of occurrences of 'sex' in comment_content
	$filter_111_count = substr_count($commentdata_comment_content_lc, 'sex');
	$filter_111_limit = 5; // you may increase to 15+ if blog's topic is adult in nature
	$filter_111_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_111_count;
	// Filter 112: Number of occurrences of 'sex' in comment_content
	$filter_112_count = substr_count($commentdata_comment_content_lc, 'pussy');
	$filter_112_limit = 3;
	$filter_112_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_112_count;
	// Filter 113: Number of occurrences of 'penis' in comment_content
	$filter_113_count = substr_count($commentdata_comment_content_lc, 'penis');
	$filter_113_limit = 3;
	$filter_113_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_113_count;
	// Filter 114: Number of occurrences of 'vagina' in comment_content
	$filter_114_count = substr_count($commentdata_comment_content_lc, 'vagina');
	$filter_114_limit = 3;
	$filter_114_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_114_count;
	// Filter 115: Number of occurrences of 'gay porn' in comment_content
	$filter_115_count = substr_count($commentdata_comment_content_lc, 'gay porn');
	$filter_115_limit = 2;
	$filter_115_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_115_count;
	// Filter 116: Number of occurrences of 'torture porn' in comment_content
	$filter_116_count = substr_count($commentdata_comment_content_lc, 'torture porn');
	$filter_116_limit = 1;
	$filter_116_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_116_count;
	// Filter 117: Number of occurrences of 'masturbation' in comment_content
	$filter_117_count = substr_count($commentdata_comment_content_lc, 'masturbation');
	$filter_117_limit = 3;
	$filter_117_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_117_count;
	// Filter 118: Number of occurrences of 'masterbation' in comment_content
	$filter_118_count = substr_count($commentdata_comment_content_lc, 'masterbation');
	$filter_118_limit = 2;
	$filter_118_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_118_count;
	// Filter 119: Number of occurrences of 'masturbate' in comment_content
	$filter_119_count = substr_count($commentdata_comment_content_lc, 'masturbate');
	$filter_119_limit = 3;
	$filter_119_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_119_count;
	// Filter 120: Number of occurrences of 'masterbate' in comment_content
	$filter_120_count = substr_count($commentdata_comment_content_lc, 'masterbate');
	$filter_120_limit = 2;
	$filter_120_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_120_count;
	// Filter 121: Number of occurrences of 'masturbating' in comment_content
	$filter_121_count = substr_count($commentdata_comment_content_lc, 'masturbating');
	$filter_121_limit = 3;
	$filter_121_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_121_count;
	// Filter 122: Number of occurrences of 'masterbating' in comment_content
	$filter_122_count = substr_count($commentdata_comment_content_lc, 'masterbating');
	$filter_122_limit = 2;
	$filter_122_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_122_count;
	// Filter 123: Number of occurrences of 'anal sex' in comment_content
	$filter_123_count = substr_count($commentdata_comment_content_lc, 'anal sex');
	$filter_123_limit = 3;
	$filter_123_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_123_count;
	// Filter 124: Number of occurrences of 'xxx' in comment_content
	$filter_124_count = substr_count($commentdata_comment_content_lc, 'xxx');
	$filter_124_limit = 5;
	$filter_124_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_124_count;
	// Filter 125: Number of occurrences of 'naked' in comment_content
	$filter_125_count = substr_count($commentdata_comment_content_lc, 'naked');
	$filter_125_limit = 5;
	$filter_125_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_125_count;
	// Filter 126: Number of occurrences of 'nude' in comment_content
	$filter_126_count = substr_count($commentdata_comment_content_lc, 'nude');
	$filter_126_limit = 5;
	$filter_126_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_126_count;
	// Filter 127: Number of occurrences of 'fucking' in comment_content
	$filter_127_count = substr_count($commentdata_comment_content_lc, 'fucking');
	$filter_127_limit = 5;
	$filter_127_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_127_count;
	// Filter 128: Number of occurrences of 'orgasm' in comment_content
	$filter_128_count = substr_count($commentdata_comment_content_lc, 'orgasm');
	$filter_128_limit = 5;
	$filter_128_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_128_count;
	// Filter 129: Number of occurrences of 'pron' in comment_content
	$filter_129_count = substr_count($commentdata_comment_content_lc, 'pron');
	$filter_129_limit = 5;
	$filter_129_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_129_count;
	// Filter 130: Number of occurrences of 'bestiality' in comment_content
	$filter_130_count = substr_count($commentdata_comment_content_lc, 'bestiality');
	$filter_130_limit = 2;
	$filter_130_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_130_count;
	// Filter 131: Number of occurrences of 'animal sex' in comment_content
	$filter_131_count = substr_count($commentdata_comment_content_lc, 'animal sex');
	$filter_131_limit = 2;
	$filter_131_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_131_count;
	// Filter 132: Number of occurrences of 'dildo' in comment_content
	$filter_132_count = substr_count($commentdata_comment_content_lc, 'dildo');
	$filter_132_limit = 4;
	$filter_132_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_132_count;
	// Filter 133: Number of occurrences of 'ejaculate' in comment_content
	$filter_133_count = substr_count($commentdata_comment_content_lc, 'ejaculate');
	$filter_133_limit = 3;
	$filter_133_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_133_count;
	// Filter 134: Number of occurrences of 'ejaculation' in comment_content
	$filter_134_count = substr_count($commentdata_comment_content_lc, 'ejaculation');
	$filter_134_limit = 3;
	$filter_134_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_134_count;
	// Filter 135: Number of occurrences of 'ejaculating' in comment_content
	$filter_135_count = substr_count($commentdata_comment_content_lc, 'ejaculating');
	$filter_135_limit = 3;
	$filter_135_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_135_count;
	// Filter 136: Number of occurrences of 'lesbian' in comment_content
	$filter_136_count = substr_count($commentdata_comment_content_lc, 'lesbian');
	$filter_136_limit = 7;
	$filter_136_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_136_count;
	// Filter 137: Number of occurrences of 'sex video' in comment_content
	$filter_137_count = substr_count($commentdata_comment_content_lc, 'sex video');
	$filter_137_limit = 2;
	$filter_137_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_137_count;
	// Filter 138: Number of occurrences of ' anal ' in comment_content
	$filter_138_count = substr_count($commentdata_comment_content_lc, ' anal ');
	$filter_138_limit = 5;
	$filter_138_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_138_count;
	// Filter 139: Number of occurrences of '>anal ' in comment_content
	$filter_139_count = substr_count($commentdata_comment_content_lc, '>anal ');
	$filter_139_limit = 5;
	$filter_139_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_139_count;
	// Filter 140: Number of occurrences of 'desnuda' in comment_content
	$filter_140_count = substr_count($commentdata_comment_content_lc, 'desnuda');
	$filter_140_limit = 5;
	$filter_140_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_140_count;
	// Filter 141: Number of occurrences of 'cumshots' in comment_content
	$filter_141_count = substr_count($commentdata_comment_content_lc, 'cumshots');
	$filter_141_limit = 2;
	$filter_141_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_141_count;
	// Filter 142: Number of occurrences of 'porntube' in comment_content
	$filter_142_count = substr_count($commentdata_comment_content_lc, 'porntube');
	$filter_142_limit = 2;
	$filter_142_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_142_count;
	// Filter 143: Number of occurrences of 'fuck' in comment_content
	$filter_143_count = substr_count($commentdata_comment_content_lc, 'fuck');
	$filter_143_limit = 6;
	$filter_143_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_143_count;
	// Filter 144: Number of occurrences of 'celebrity' in comment_content
	$filter_144_count = substr_count($commentdata_comment_content_lc, 'celebrity');
	$filter_144_limit = 6;
	$filter_144_trackback_limit = 6;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_144_count;
	// Filter 145: Number of occurrences of 'celebrities' in comment_content
	$filter_145_count = substr_count($commentdata_comment_content_lc, 'celebrities');
	$filter_145_limit = 6;
	$filter_145_trackback_limit = 6;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_145_count;
	// Filter 146: Number of occurrences of 'erotic' in comment_content
	$filter_146_count = substr_count($commentdata_comment_content_lc, 'erotic');
	$filter_146_limit = 6;
	$filter_146_trackback_limit = 4;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_146_count;
	// Filter 147: Number of occurrences of 'gay' in comment_content
	$filter_147_count = substr_count($commentdata_comment_content_lc, 'gay');
	$filter_147_limit = 7;
	$filter_147_trackback_limit = 4;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_147_count;
	// Filter 148: Number of occurrences of 'heterosexual' in comment_content
	$filter_148_count = substr_count($commentdata_comment_content_lc, 'heterosexual');
	$filter_148_limit = 7;
	$filter_148_trackback_limit = 4;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_148_count;
	// Filter 149: Number of occurrences of 'blowjob' in comment_content
	$filter_149_count = substr_count($commentdata_comment_content_lc, 'blowjob');
	$filter_149_limit = 2;
	$filter_149_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_149_count;
	// Filter 150: Number of occurrences of 'blow job' in comment_content
	$filter_150_count = substr_count($commentdata_comment_content_lc, 'blow job');
	$filter_150_limit = 2;
	$filter_150_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_150_count;
	// Filter 151: Number of occurrences of 'rape' in comment_content
	$filter_151_count = substr_count($commentdata_comment_content_lc, 'rape');
	$filter_151_limit = 5;
	$filter_151_trackback_limit = 3;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_151_count;
	// Filter 152: Number of occurrences of 'prostitute' in comment_content
	$filter_152_count = substr_count($commentdata_comment_content_lc, 'prostitute');
	$filter_152_limit = 7;
	$filter_152_trackback_limit = 5;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_152_count;
	// Filter 153: Number of occurrences of 'call girl' in comment_content
	$filter_153_count = substr_count($commentdata_comment_content_lc, 'call girl');
	$filter_153_limit = 7;
	$filter_153_trackback_limit = 5;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_153_count;
	// Filter 154: Number of occurrences of 'escort service' in comment_content
	$filter_154_count = substr_count($commentdata_comment_content_lc, 'escort service');
	$filter_154_limit = 7;
	$filter_154_trackback_limit = 5;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_154_count;
	// Filter 155: Number of occurrences of 'sexual service' in comment_content
	$filter_155_count = substr_count($commentdata_comment_content_lc, 'sexual service');
	$filter_155_limit = 7;
	$filter_155_trackback_limit = 5;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_155_count;
	// Filter 156: Number of occurrences of 'adult movie' in comment_content
	$filter_156_count = substr_count($commentdata_comment_content_lc, 'adult movie');
	$filter_156_limit = 4;
	$filter_156_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_156_count;
	// Filter 157: Number of occurrences of 'adult video' in comment_content
	$filter_157_count = substr_count($commentdata_comment_content_lc, 'adult video');
	$filter_157_limit = 4;
	$filter_157_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_157_count;
	// Filter 158: Number of occurrences of 'clitoris' in comment_content
	$filter_158_count = substr_count($commentdata_comment_content_lc, 'clitoris');
	$filter_158_limit = 3;
	$filter_158_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_158_count;
	
	// Pingback/Trackback Filters
	// Filter 200: Pingback: Blank data in comment_content: [...]  [...]
	$filter_200_count = substr_count($commentdata_comment_content_lc, '[...]  [...]');
	$filter_200_limit = 1;
	$filter_200_trackback_limit = 1;

	// Authors Only - Non-Trackback
	// SEO/WebDev/Offshore-Related Filter - 
	// Filter 300: Number of occurrences of 'web development' in comment_author
	$filter_300_term = 'web development'; //'web development'
	$filter_300_count = substr_count($commentdata_comment_content_lc, $filter_300_term);
	$filter_300_limit = 8;
	$filter_300_trackback_limit = 8;
	$filter_300_author_count = substr_count($commentdata_comment_author_lc, $filter_300_term);
	$filter_300_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_300_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_300_author_count;
	// Filter 301: Number of occurrences of 'website development' in comment_author
	$filter_301_term = 'website development';
	$filter_301_count = substr_count($commentdata_comment_content_lc, $filter_301_term);
	$filter_301_limit = 8;
	$filter_301_trackback_limit = 8;
	$filter_301_author_count = substr_count($commentdata_comment_author_lc, $filter_301_term);
	$filter_301_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_301_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_301_author_count;
	// Filter 302: Number of occurrences of 'web site development' in comment_author
	$filter_302_term = 'web site development';
	$filter_302_count = substr_count($commentdata_comment_content_lc, $filter_302_term);
	$filter_302_limit = 8;
	$filter_302_trackback_limit = 8;
	$filter_302_author_count = substr_count($commentdata_comment_author_lc, $filter_302_term);
	$filter_302_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_302_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_302_author_count;
	// Filter 303: Number of occurrences of 'web design' in comment_author
	$filter_303_term = 'web design';
	$filter_303_count = substr_count($commentdata_comment_content_lc, $filter_303_term);
	$filter_303_limit = 8;
	$filter_303_trackback_limit = 8;
	$filter_303_author_count = substr_count($commentdata_comment_author_lc, $filter_303_term);
	$filter_303_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_303_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_303_author_count;
	// Filter 304: Number of occurrences of 'website design' in comment_author
	$filter_304_term = 'website design';
	$filter_304_count = substr_count($commentdata_comment_content_lc, $filter_304_term);
	$filter_304_limit = 8;
	$filter_304_trackback_limit = 8;
	$filter_304_author_count = substr_count($commentdata_comment_author_lc, $filter_304_term);
	$filter_304_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_304_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_304_author_count;
	// Filter 305: Number of occurrences of 'web site design' in comment_author
	$filter_305_term = 'web site design';
	$filter_305_count = substr_count($commentdata_comment_content_lc, $filter_305_term);
	$filter_305_limit = 8;
	$filter_305_trackback_limit = 8;
	$filter_305_author_count = substr_count($commentdata_comment_author_lc, $filter_305_term);
	$filter_305_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_305_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_305_author_count;
	// Filter 306: Number of occurrences of 'search engine optimization' in comment_author
	$filter_306_term = 'search engine optimization';
	$filter_306_count = substr_count($commentdata_comment_content_lc, $filter_306_term);
	$filter_306_limit = 8;
	$filter_306_trackback_limit = 8;
	$filter_306_author_count = substr_count($commentdata_comment_author_lc, $filter_306_term);
	$filter_306_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_306_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_306_author_count;
	// Filter 307: Number of occurrences of 'link building' in comment_author
	$filter_307_term = 'link building';
	$filter_307_count = substr_count($commentdata_comment_content_lc, $filter_307_term);
	$filter_307_limit = 8;
	$filter_307_trackback_limit = 8;
	$filter_307_author_count = substr_count($commentdata_comment_author_lc, $filter_307_term);
	$filter_307_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_307_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_307_author_count;
	// Filter 308: Number of occurrences of 'india offshore' in comment_author
	$filter_308_term = 'india offshore';
	$filter_308_count = substr_count($commentdata_comment_content_lc, $filter_308_term);
	$filter_308_limit = 8;
	$filter_308_trackback_limit = 8;
	$filter_308_author_count = substr_count($commentdata_comment_author_lc, $filter_308_term);
	$filter_308_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_308_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_308_author_count;
	// Filter 309: Number of occurrences of 'offshore india' in comment_author
	$filter_309_term = 'offshore india';
	$filter_309_count = substr_count($commentdata_comment_content_lc, $filter_309_term);
	$filter_309_limit = 8;
	$filter_309_trackback_limit = 8;
	$filter_309_author_count = substr_count($commentdata_comment_author_lc, $filter_309_term);
	$filter_309_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_309_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_309_author_count;
	// Filter 310: Number of occurrences of ' seo ' in comment_author & comment_author
	$filter_310_term = ' seo ';
	$filter_310_count = substr_count($commentdata_comment_content_lc, $filter_310_term);
	$filter_310_limit = 8;
	$filter_310_trackback_limit = 8;
	$filter_310_author_count = substr_count($commentdata_comment_author_lc_space, $filter_310_term);
	$filter_310_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_310_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_310_author_count;
	// Filter 311: Number of occurrences of 'search engine marketing' in comment_author
	$filter_311_term = 'search engine marketing';
	$filter_311_count = substr_count($commentdata_comment_content_lc, $filter_311_term);
	$filter_311_limit = 8;
	$filter_311_trackback_limit = 8;
	$filter_311_author_count = substr_count($commentdata_comment_author_lc, $filter_311_term);
	$filter_311_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_311_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_311_author_count;
	// Filter 312: Number of occurrences of 'internet marketing' in comment_author
	$filter_312_term = 'internet marketing';
	$filter_312_count = substr_count($commentdata_comment_content_lc, $filter_312_term);
	$filter_312_limit = 8;
	$filter_312_trackback_limit = 8;
	$filter_312_author_count = substr_count($commentdata_comment_author_lc, $filter_312_term);
	$filter_312_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_312_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_312_author_count;
	// Filter 313: Number of occurrences of 'social media optimization' in comment_author
	$filter_313_term = 'social media optimization';
	$filter_313_count = substr_count($commentdata_comment_content_lc, $filter_313_term);
	$filter_313_limit = 8;
	$filter_313_trackback_limit = 8;
	$filter_313_author_count = substr_count($commentdata_comment_author_lc, $filter_313_term);
	$filter_313_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_313_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_313_author_count;
	// Filter 314: Number of occurrences of 'social media marketing' in comment_author
	$filter_314_term = 'social media marketing';
	$filter_314_count = substr_count($commentdata_comment_content_lc, $filter_314_term);
	$filter_314_limit = 8;
	$filter_314_trackback_limit = 8;
	$filter_314_author_count = substr_count($commentdata_comment_author_lc, $filter_314_term);
	$filter_314_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_314_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_314_author_count;
	// Filter 315: Number of occurrences of 'web developer' in comment_author
	$filter_315_term = 'web developer'; //'web development'
	$filter_315_count = substr_count($commentdata_comment_content_lc, $filter_315_term);
	$filter_315_limit = 8;
	$filter_315_trackback_limit = 8;
	$filter_315_author_count = substr_count($commentdata_comment_author_lc, $filter_315_term);
	$filter_315_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_315_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_315_author_count;
	// Filter 316: Number of occurrences of 'website developer' in comment_author
	$filter_316_term = 'website developer';
	$filter_316_count = substr_count($commentdata_comment_content_lc, $filter_316_term);
	$filter_316_limit = 8;
	$filter_316_trackback_limit = 8;
	$filter_316_author_count = substr_count($commentdata_comment_author_lc, $filter_316_term);
	$filter_316_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_316_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_316_author_count;
	// Filter 317: Number of occurrences of 'web site developer' in comment_author
	$filter_317_term = 'web site developer';
	$filter_317_count = substr_count($commentdata_comment_content_lc, $filter_317_term);
	$filter_317_limit = 8;
	$filter_317_trackback_limit = 8;
	$filter_317_author_count = substr_count($commentdata_comment_author_lc, $filter_317_term);
	$filter_317_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_317_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_317_author_count;
	// Filter 318: Number of occurrences of 'javascript' in comment_author
	$filter_318_term = 'javascript';
	$filter_318_count = substr_count($commentdata_comment_content_lc, $filter_318_term);
	$filter_318_limit = 8;
	$filter_318_trackback_limit = 8;
	$filter_318_author_count = substr_count($commentdata_comment_author_lc, $filter_318_term);
	$filter_318_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_318_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_318_author_count;
	// Filter 319: Number of occurrences of 'search engine optimizer' in comment_author
	$filter_319_term = 'search engine optimizer';
	$filter_319_count = substr_count($commentdata_comment_content_lc, $filter_319_term);
	$filter_319_limit = 8;
	$filter_319_trackback_limit = 8;
	$filter_319_author_count = substr_count($commentdata_comment_author_lc, $filter_319_term);
	$filter_319_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_319_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_319_author_count;
	// Filter 320: Number of occurrences of 'link builder' in comment_author
	$filter_320_term = 'link builder';
	$filter_320_count = substr_count($commentdata_comment_content_lc, $filter_320_term);
	$filter_320_limit = 8;
	$filter_320_trackback_limit = 8;
	$filter_320_author_count = substr_count($commentdata_comment_author_lc, $filter_320_term);
	$filter_320_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_320_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_320_author_count;
	// Filter 321: Number of occurrences of 'search engine marketer' in comment_author
	$filter_321_term = 'search engine marketer';
	$filter_321_count = substr_count($commentdata_comment_content_lc, $filter_321_term);
	$filter_321_limit = 8;
	$filter_321_trackback_limit = 8;
	$filter_321_author_count = substr_count($commentdata_comment_author_lc, $filter_321_term);
	$filter_321_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_321_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_321_author_count;
	// Filter 322: Number of occurrences of 'internet marketer' in comment_author
	$filter_322_term = 'internet marketer';
	$filter_322_count = substr_count($commentdata_comment_content_lc, $filter_322_term);
	$filter_322_limit = 8;
	$filter_322_trackback_limit = 8;
	$filter_322_author_count = substr_count($commentdata_comment_author_lc, $filter_322_term);
	$filter_322_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_322_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_322_author_count;
	// Filter 323: Number of occurrences of 'social media optimizer' in comment_author
	$filter_323_term = 'social media optimizer';
	$filter_323_count = substr_count($commentdata_comment_content_lc, $filter_323_term);
	$filter_323_limit = 8;
	$filter_323_trackback_limit = 8;
	$filter_323_author_count = substr_count($commentdata_comment_author_lc, $filter_323_term);
	$filter_323_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_323_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_323_author_count;
	// Filter 324: Number of occurrences of 'social media marketer' in comment_author
	$filter_324_term = 'social media marketer';
	$filter_324_count = substr_count($commentdata_comment_content_lc, $filter_324_term);
	$filter_324_limit = 8;
	$filter_324_trackback_limit = 8;
	$filter_324_author_count = substr_count($commentdata_comment_author_lc, $filter_324_term);
	$filter_324_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_324_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_324_author_count;
	// Filter 325: Number of occurrences of 'social media consultant' in comment_author
	$filter_325_term = 'social media consultant';
	$filter_325_count = substr_count($commentdata_comment_content_lc, $filter_325_term);
	$filter_325_limit = 8;
	$filter_325_trackback_limit = 8;
	$filter_325_author_count = substr_count($commentdata_comment_author_lc, $filter_325_term);
	$filter_325_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_325_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_325_author_count;
	// Filter 326: Number of occurrences of 'social media consulting' in comment_author
	$filter_326_term = 'social media consulting';
	$filter_326_count = substr_count($commentdata_comment_content_lc, $filter_326_term);
	$filter_326_limit = 8;
	$filter_326_trackback_limit = 8;
	$filter_326_author_count = substr_count($commentdata_comment_author_lc, $filter_326_term);
	$filter_326_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_326_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_326_author_count;
	// Filter 327: Number of occurrences of 'web promotion' in comment_author
	$filter_327_term = 'web promotion'; 
	$filter_327_count = substr_count($commentdata_comment_content_lc, $filter_327_term);
	$filter_327_limit = 8;
	$filter_327_trackback_limit = 8;
	$filter_327_author_count = substr_count($commentdata_comment_author_lc, $filter_327_term);
	$filter_327_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_327_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_327_author_count;
	// Filter 328: Number of occurrences of 'website promotion' in comment_author
	$filter_328_term = 'website promotion';
	$filter_328_count = substr_count($commentdata_comment_content_lc, $filter_328_term);
	$filter_328_limit = 8;
	$filter_328_trackback_limit = 8;
	$filter_328_author_count = substr_count($commentdata_comment_author_lc, $filter_328_term);
	$filter_328_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_328_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_328_author_count;
	// Filter 329: Number of occurrences of 'web site promotion' in comment_author
	$filter_329_term = 'web site promotion';
	$filter_329_count = substr_count($commentdata_comment_content_lc, $filter_329_term);
	$filter_329_limit = 8;
	$filter_329_trackback_limit = 8;
	$filter_329_author_count = substr_count($commentdata_comment_author_lc, $filter_329_term);
	$filter_329_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_329_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_329_author_count;
	// Filter 330: Number of occurrences of 'search engine ranking' in comment_author
	$filter_330_term = 'search engine ranking';
	$filter_330_count = substr_count($commentdata_comment_content_lc, $filter_330_term);
	$filter_330_limit = 8;
	$filter_330_trackback_limit = 8;
	$filter_330_author_count = substr_count($commentdata_comment_author_lc, $filter_330_term);
	$filter_330_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_330_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_330_author_count;
	// Filter 331: Number of occurrences of 'modulesoft' in comment_author
	$filter_331_term = 'modulesoft';
	$filter_331_count = substr_count($commentdata_comment_content_lc, $filter_331_term);
	$filter_331_limit = 8;
	$filter_331_trackback_limit = 8;
	$filter_331_author_count = substr_count($commentdata_comment_author_lc, $filter_331_term);
	$filter_331_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_331_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_331_author_count;
	// Filter 332: Number of occurrences of 'zoekmachine optimalisatie' in comment_author
	$filter_332_term = 'zoekmachine optimalisatie';
	$filter_332_count = substr_count($commentdata_comment_content_lc, $filter_332_term);
	$filter_332_limit = 8;
	$filter_332_trackback_limit = 8;
	$filter_332_author_count = substr_count($commentdata_comment_author_lc, $filter_332_term);
	$filter_332_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_332_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_332_author_count;
	// Filter 333: Number of occurrences of 'data entry india' in comment_author
	$filter_333_term = 'data entry india';
	$filter_333_count = substr_count($commentdata_comment_content_lc, $filter_333_term);
	$filter_333_limit = 8;
	$filter_333_trackback_limit = 8;
	$filter_333_author_count = substr_count($commentdata_comment_author_lc, $filter_333_term);
	$filter_333_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_333_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_333_author_count;
	// Filter 334: Number of occurrences of 'webdesigner' in comment_author
	$filter_334_term = 'webdesigner';
	$filter_334_count = substr_count($commentdata_comment_content_lc, $filter_334_term);
	$filter_334_limit = 8;
	$filter_334_trackback_limit = 8;
	$filter_334_author_count = substr_count($commentdata_comment_author_lc, $filter_334_term);
	$filter_334_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_334_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_334_author_count;
	// Filter 335: Number of occurrences of 'webdesign' in comment_author
	$filter_335_term = 'webdesign';
	$filter_335_count = substr_count($commentdata_comment_content_lc, $filter_335_term);
	$filter_335_limit = 8;
	$filter_335_trackback_limit = 8;
	$filter_335_author_count = substr_count($commentdata_comment_author_lc, $filter_335_term);
	$filter_335_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_335_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_335_author_count;
	// Other
	// Filter 336: Number of occurrences of 'company' in comment_author
	$filter_336_term = 'company';
	$filter_336_count = substr_count($commentdata_comment_content_lc, $filter_336_term);
	$filter_336_limit = 15;
	$filter_336_trackback_limit = 15;
	$filter_336_author_count = substr_count($commentdata_comment_author_lc, $filter_336_term);
	$filter_336_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_336_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_336_author_count;
	// Filter 337: Number of occurrences of 'blackjack' in comment_author
	$filter_337_term = 'blackjack';
	$filter_337_count = substr_count($commentdata_comment_content_lc, $filter_337_term);
	$filter_337_limit = 12;
	$filter_337_trackback_limit = 12;
	$filter_337_author_count = substr_count($commentdata_comment_author_lc, $filter_337_term);
	$filter_337_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_337_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_337_author_count;
	// Filter 338: Number of occurrences of 'website' in comment_author
	$filter_338_term = 'website';
	$filter_338_count = substr_count($commentdata_comment_content_lc, $filter_338_term);
	$filter_338_limit = 25;
	$filter_338_trackback_limit = 25;
	$filter_338_author_count = substr_count($commentdata_comment_author_lc, $filter_338_term);
	$filter_338_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_338_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_338_author_count;
	// Filter 339: Number of occurrences of 'template' in comment_author
	$filter_339_term = 'template';
	$filter_339_count = substr_count($commentdata_comment_content_lc, $filter_339_term);
	$filter_339_limit = 25;
	$filter_339_trackback_limit = 25;
	$filter_339_author_count = substr_count($commentdata_comment_author_lc, $filter_339_term);
	$filter_339_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_339_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_339_author_count;
	// Filter 340: Number of occurrences of 'gambling' in comment_author
	$filter_340_term = 'gambling';
	$filter_340_count = substr_count($commentdata_comment_content_lc, $filter_340_term);
	$filter_340_limit = 12;
	$filter_340_trackback_limit = 12;
	$filter_340_author_count = substr_count($commentdata_comment_author_lc, $filter_340_term);
	$filter_340_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_340_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_340_author_count;
	// Filter 341: Number of occurrences of 'phpdug' in comment_author
	$filter_341_term = 'phpdug';
	$filter_341_count = substr_count($commentdata_comment_content_lc, $filter_341_term);
	$filter_341_limit = 12;
	$filter_341_trackback_limit = 12;
	$filter_341_author_count = substr_count($commentdata_comment_author_lc, $filter_341_term);
	$filter_341_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_341_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_341_author_count;
	// Filter 342: Number of occurrences of 'social poster' in comment_author
	$filter_342_term = 'social poster';
	$filter_342_count = substr_count($commentdata_comment_content_lc, $filter_342_term);
	$filter_342_limit = 12;
	$filter_342_trackback_limit = 12;
	$filter_342_author_count = substr_count($commentdata_comment_author_lc, $filter_342_term);
	$filter_342_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_342_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_342_author_count;
	// Filter 343: Number of occurrences of 'submitter' in comment_author
	$filter_343_term = 'submitter';
	$filter_343_count = substr_count($commentdata_comment_content_lc, $filter_343_term);
	$filter_343_limit = 30;
	$filter_343_trackback_limit = 30;
	$filter_343_author_count = substr_count($commentdata_comment_author_lc, $filter_343_term);
	$filter_343_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_343_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_343_author_count;
	// Filter 344: Number of occurrences of ' review' in comment_author
	$filter_344_term = ' review';
	$filter_344_count = substr_count($commentdata_comment_content_lc, $filter_344_term);
	$filter_344_limit = 30;
	$filter_344_trackback_limit = 30;
	$filter_344_author_count = substr_count($commentdata_comment_author_lc, $filter_344_term);
	$filter_344_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_344_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_344_author_count;
	// Filter 345: Number of occurrences of 'property vault' in comment_author
	$filter_345_term = 'property vault';
	$filter_345_count = substr_count($commentdata_comment_content_lc, $filter_345_term);
	$filter_345_limit = 12;
	$filter_345_trackback_limit = 12;
	$filter_345_author_count = substr_count($commentdata_comment_author_lc, $filter_345_term);
	$filter_345_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_345_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_345_author_count;
	// Filter 346: Number of occurrences of ' seminar' in comment_author
	$filter_346_term = ' seminar';
	$filter_346_count = substr_count($commentdata_comment_content_lc, $filter_346_term);
	$filter_346_limit = 25;
	$filter_346_trackback_limit = 25;
	$filter_346_author_count = substr_count($commentdata_comment_author_lc, $filter_346_term);
	$filter_346_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_346_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_346_author_count;
	// Filter 347: Number of occurrences of 'foreclosure' in comment_author
	$filter_347_term = 'foreclosure';
	$filter_347_count = substr_count($commentdata_comment_content_lc, $filter_347_term);
	$filter_347_limit = 25;
	$filter_347_trackback_limit = 25;
	$filter_347_author_count = substr_count($commentdata_comment_author_lc, $filter_347_term);
	$filter_347_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_347_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_347_author_count;
	// Filter 348: Number of occurrences of 'trackback submitter' in comment_author
	$filter_348_term = 'trackback submitter';
	$filter_348_count = substr_count($commentdata_comment_content_lc, $filter_348_term);
	$filter_348_limit = 12;
	$filter_348_trackback_limit = 12;
	$filter_348_author_count = substr_count($commentdata_comment_author_lc, $filter_348_term);
	$filter_348_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_348_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_348_author_count;
	// Filter 349: Number of occurrences of 'earn money' in comment_author
	$filter_349_term = 'earn money';
	$filter_349_count = substr_count($commentdata_comment_content_lc, $filter_349_term);
	$filter_349_limit = 12;
	$filter_349_trackback_limit = 12;
	$filter_349_author_count = substr_count($commentdata_comment_author_lc, $filter_349_term);
	$filter_349_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_349_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_349_author_count;
	// Filter 350: Number of occurrences of 'software' in comment_author
	$filter_350_term = 'software';
	$filter_350_count = substr_count($commentdata_comment_content_lc, $filter_350_term);
	$filter_350_limit = 25;
	$filter_350_trackback_limit = 25;
	$filter_350_author_count = substr_count($commentdata_comment_author_lc, $filter_350_term);
	$filter_350_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_350_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_350_author_count;
	// Filter 351: Number of occurrences of 'home design' in comment_author
	$filter_351_term = 'home design';
	$filter_351_count = substr_count($commentdata_comment_content_lc, $filter_351_term);
	$filter_351_limit = 25;
	$filter_351_trackback_limit = 25;
	$filter_351_author_count = substr_count($commentdata_comment_author_lc, $filter_351_term);
	$filter_351_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_351_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_351_author_count;
	// Filter 352: Number of occurrences of 'webmaster' in comment_author
	$filter_352_term = 'webmaster';
	$filter_352_count = substr_count($commentdata_comment_content_lc, $filter_352_term);
	$filter_352_limit = 25;
	$filter_352_trackback_limit = 25;
	$filter_352_author_count = substr_count($commentdata_comment_author_lc, $filter_352_term);
	$filter_352_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_352_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_352_author_count;
	// Filter 353: Number of occurrences of 'learning ' in comment_author
	$filter_353_term = 'learning ';
	$filter_353_count = substr_count($commentdata_comment_content_lc, $filter_353_term);
	$filter_353_limit = 25;
	$filter_353_trackback_limit = 25;
	$filter_353_author_count = substr_count($commentdata_comment_author_lc, $filter_353_term);
	$filter_353_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_353_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_353_author_count;
	// Filter 354: Number of occurrences of 'student loans' in comment_author
	$filter_354_term = 'students loans';
	$filter_354_count = substr_count($commentdata_comment_content_lc, $filter_354_term);
	$filter_354_limit = 25;
	$filter_354_trackback_limit = 25;
	$filter_354_author_count = substr_count($commentdata_comment_author_lc, $filter_354_term);
	$filter_354_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_354_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_354_author_count;
	// Filter 355: Number of occurrences of 'comments poster' in comment_author
	$filter_355_term = 'comments poster';
	$filter_355_count = substr_count($commentdata_comment_content_lc, $filter_355_term);
	$filter_355_limit = 12;
	$filter_355_trackback_limit = 12;
	$filter_355_author_count = substr_count($commentdata_comment_author_lc, $filter_355_term);
	$filter_355_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_355_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_355_author_count;
	// Filter 356: Number of occurrences of 'comment poster' in comment_author
	$filter_356_term = 'comment poster';
	$filter_356_count = substr_count($commentdata_comment_content_lc, $filter_356_term);
	$filter_356_limit = 12;
	$filter_356_trackback_limit = 12;
	$filter_356_author_count = substr_count($commentdata_comment_author_lc, $filter_356_term);
	$filter_356_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_356_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_356_author_count;
	// Filter 357: Number of occurrences of 'youtube' in comment_author
	$filter_357_term = 'youtube';
	$filter_357_count = substr_count($commentdata_comment_content_lc, $filter_357_term);
	$filter_357_limit = 25;
	$filter_357_trackback_limit = 25;
	$filter_357_author_count = substr_count($commentdata_comment_author_lc, $filter_357_term);
	$filter_357_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_357_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_357_author_count;
	// Filter 358: Number of occurrences of 'united states' in comment_author
	$filter_358_term = 'united states';
	$filter_358_count = substr_count($commentdata_comment_content_lc, $filter_358_term);
	$filter_358_limit = 25;
	$filter_358_trackback_limit = 25;
	$filter_358_author_count = substr_count($commentdata_comment_author_lc, $filter_358_term);
	$filter_358_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_358_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_358_author_count;
	// Filter 359: Number of occurrences of 'business' in comment_author
	$filter_359_term = 'business';
	$filter_359_count = substr_count($commentdata_comment_content_lc, $filter_359_term);
	$filter_359_limit = 25;
	$filter_359_trackback_limit = 25;
	$filter_359_author_count = substr_count($commentdata_comment_author_lc, $filter_359_term);
	$filter_359_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_359_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_359_author_count;
	// Filter 360: Number of occurrences of 'for sale' in comment_author
	$filter_360_term = 'for sale';
	$filter_360_count = substr_count($commentdata_comment_content_lc, $filter_360_term);
	$filter_360_limit = 25;
	$filter_360_trackback_limit = 25;
	$filter_360_author_count = substr_count($commentdata_comment_author_lc, $filter_360_term);
	$filter_360_author_limit = 1;
	//$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_360_count;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_360_author_count;
	// After this, remove line: //$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_360_count;
	// Filter 361: Number of occurrences of 'buy cheap' in comment_author
	$filter_361_term = 'buy cheap';
	$filter_361_count = substr_count($commentdata_comment_content_lc, $filter_361_term);
	$filter_361_limit = 25;
	$filter_361_trackback_limit = 25;
	$filter_361_author_count = substr_count($commentdata_comment_author_lc, $filter_361_term);
	$filter_361_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_361_author_count;
	// Filter 362: Number of occurrences of 'steroid' in comment_author
	$filter_362_term = 'steroid';
	$filter_362_count = substr_count($commentdata_comment_content_lc, $filter_362_term);
	$filter_362_limit = 25;
	$filter_362_trackback_limit = 25;
	$filter_362_author_count = substr_count($commentdata_comment_author_lc, $filter_362_term);
	$filter_362_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_362_author_count;
	// Filter 363: Number of occurrences of 'property' in comment_author
	$filter_363_term = 'property';
	$filter_363_count = substr_count($commentdata_comment_content_lc, $filter_363_term);
	$filter_363_limit = 25;
	$filter_363_trackback_limit = 25;
	$filter_363_author_count = substr_count($commentdata_comment_author_lc, $filter_363_term);
	$filter_363_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_363_author_count;
	// Filter 364: Number of occurrences of 'logo design' in comment_author
	$filter_364_term = 'logo design';
	$filter_364_count = substr_count($commentdata_comment_content_lc, $filter_364_term);
	$filter_364_limit = 25;
	$filter_364_trackback_limit = 25;
	$filter_364_author_count = substr_count($commentdata_comment_author_lc, $filter_364_term);
	$filter_364_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_364_author_count;
	// Filter 365: Number of occurrences of 'injury lawyer' in comment_author
	$filter_365_term = 'injury lawyer';
	$filter_365_count = substr_count($commentdata_comment_content_lc, $filter_365_term);
	$filter_365_limit = 25;
	$filter_365_trackback_limit = 25;
	$filter_365_author_count = substr_count($commentdata_comment_author_lc, $filter_365_term);
	$filter_365_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_365_author_count;
	// Filter 366: Number of occurrences of 'internastional' in comment_author
	$filter_366_term = 'internastional';
	$filter_366_count = substr_count($commentdata_comment_content_lc, $filter_366_term);
	$filter_366_limit = 25;
	$filter_366_trackback_limit = 25;
	$filter_366_author_count = substr_count($commentdata_comment_author_lc, $filter_366_term);
	$filter_366_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_366_author_count;
	// Filter 367: Number of occurrences of 'information' in comment_author
	$filter_367_term = 'information';
	$filter_367_count = substr_count($commentdata_comment_content_lc, $filter_367_term);
	$filter_367_limit = 25;
	$filter_367_trackback_limit = 25;
	$filter_367_author_count = substr_count($commentdata_comment_author_lc, $filter_367_term);
	$filter_367_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_367_author_count;
	// Filter 368: Number of occurrences of 'advertising' in comment_author
	$filter_368_term = 'advertising';
	$filter_368_count = substr_count($commentdata_comment_content_lc, $filter_368_term);
	$filter_368_limit = 25;
	$filter_368_trackback_limit = 25;
	$filter_368_author_count = substr_count($commentdata_comment_author_lc, $filter_368_term);
	$filter_368_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_368_author_count;
	// Filter 369: Number of occurrences of 'car rental' in comment_author
	$filter_369_term = 'car rental';
	$filter_369_count = substr_count($commentdata_comment_content_lc, $filter_369_term);
	$filter_369_limit = 25;
	$filter_369_trackback_limit = 25;
	$filter_369_author_count = substr_count($commentdata_comment_author_lc, $filter_369_term);
	$filter_369_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_369_author_count;
	// Filter 370: Number of occurrences of 'rent a car' in comment_author
	$filter_370_term = 'rent a car';
	$filter_370_count = substr_count($commentdata_comment_content_lc, $filter_370_term);
	$filter_370_limit = 25;
	$filter_370_trackback_limit = 25;
	$filter_370_author_count = substr_count($commentdata_comment_author_lc, $filter_370_term);
	$filter_370_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_370_author_count;
	// Filter 371: Number of occurrences of 'development' in comment_author
	$filter_371_term = 'development';
	$filter_371_count = substr_count($commentdata_comment_content_lc, $filter_371_term);
	$filter_371_limit = 25;
	$filter_371_trackback_limit = 25;
	$filter_371_author_count = substr_count($commentdata_comment_author_lc, $filter_371_term);
	$filter_371_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_371_author_count;
	// Filter 372: Number of occurrences of 'technology' in comment_author
	$filter_372_term = 'technology';
	$filter_372_count = substr_count($commentdata_comment_content_lc, $filter_372_term);
	$filter_372_limit = 25;
	$filter_372_trackback_limit = 25;
	$filter_372_author_count = substr_count($commentdata_comment_author_lc, $filter_372_term);
	$filter_372_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_372_author_count;
	// Filter 373: Number of occurrences of 'cash advance' in comment_author
	$filter_373_term = 'cash advance';
	$filter_373_count = substr_count($commentdata_comment_content_lc, $filter_373_term);
	$filter_373_limit = 25;
	$filter_373_trackback_limit = 25;
	$filter_373_author_count = substr_count($commentdata_comment_author_lc, $filter_373_term);
	$filter_373_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_373_author_count;
	// Filter 374: Number of occurrences of 'forex trading' in comment_author
	$filter_374_term = 'forex trading';
	$filter_374_count = substr_count($commentdata_comment_content_lc, $filter_374_term);
	$filter_374_limit = 25;
	$filter_374_trackback_limit = 25;
	$filter_374_author_count = substr_count($commentdata_comment_author_lc, $filter_374_term);
	$filter_374_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_374_author_count;
	// Filter 375: Number of occurrences of 'anonymous' in comment_author
	$filter_375_term = 'anonymous';
	$filter_375_count = substr_count($commentdata_comment_content_lc, $filter_375_term);
	$filter_375_limit = 25;
	$filter_375_trackback_limit = 25;
	$filter_375_author_count = substr_count($commentdata_comment_author_lc, $filter_375_term);
	$filter_375_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_375_author_count;
	// Filter 376: Number of occurrences of 'php expert' in comment_author
	$filter_376_term = 'php expert';
	$filter_376_count = substr_count($commentdata_comment_content_lc, $filter_376_term);
	$filter_376_limit = 25;
	$filter_376_trackback_limit = 25;
	$filter_376_author_count = substr_count($commentdata_comment_author_lc, $filter_376_term);
	$filter_376_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_376_author_count;
	// Filter 377: Number of occurrences of 'designer handbags' in comment_author
	$filter_377_term = 'designer handbags';
	$filter_377_count = substr_count($commentdata_comment_content_lc, $filter_377_term);
	$filter_377_limit = 25;
	$filter_377_trackback_limit = 25;
	$filter_377_author_count = substr_count($commentdata_comment_author_lc, $filter_377_term);
	$filter_377_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_377_author_count;
	// Filter 378: Number of occurrences of 'travel deals' in comment_author
	$filter_378_term = 'travel deals';
	$filter_378_count = substr_count($commentdata_comment_content_lc, $filter_378_term);
	$filter_378_limit = 25;
	$filter_378_trackback_limit = 25;
	$filter_378_author_count = substr_count($commentdata_comment_author_lc, $filter_378_term);
	$filter_378_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_378_author_count;
	// Filter 379: Number of occurrences of 'social bookmark' in comment_author
	$filter_379_term = 'social bookmark';
	$filter_379_count = substr_count($commentdata_comment_content_lc, $filter_379_term);
	$filter_379_limit = 25;
	$filter_379_trackback_limit = 25;
	$filter_379_author_count = substr_count($commentdata_comment_author_lc, $filter_379_term);
	$filter_379_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_379_author_count;
	// Filter 380: Number of occurrences of 'win now' in comment_author
	$filter_380_term = 'win now';
	$filter_380_count = substr_count($commentdata_comment_content_lc, $filter_380_term);
	$filter_380_limit = 25;
	$filter_380_trackback_limit = 25;
	$filter_380_author_count = substr_count($commentdata_comment_author_lc, $filter_380_term);
	$filter_380_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_380_author_count;
	// Filter 381: Number of occurrences of 'poker online' in comment_author
	$filter_381_term = 'poker online';
	$filter_381_count = substr_count($commentdata_comment_content_lc, $filter_381_term);
	$filter_381_limit = 25;
	$filter_381_trackback_limit = 25;
	$filter_381_author_count = substr_count($commentdata_comment_author_lc, $filter_381_term);
	$filter_381_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_381_author_count;
	// Filter 382: Number of occurrences of 'online poker' in comment_author
	$filter_382_term = 'online poker';
	$filter_382_count = substr_count($commentdata_comment_content_lc, $filter_382_term);
	$filter_382_limit = 25;
	$filter_382_trackback_limit = 25;
	$filter_382_author_count = substr_count($commentdata_comment_author_lc, $filter_382_term);
	$filter_382_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_382_author_count;
	// Filter 383: Number of occurrences of 'college student' in comment_author
	$filter_383_term = 'college student';
	$filter_383_count = substr_count($commentdata_comment_content_lc, $filter_383_term);
	$filter_383_limit = 25;
	$filter_383_trackback_limit = 25;
	$filter_383_author_count = substr_count($commentdata_comment_author_lc, $filter_383_term);
	$filter_383_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_383_author_count;
	// Filter 384: Number of occurrences of 'health insurance' in comment_author
	$filter_384_term = 'health insurance';
	$filter_384_count = substr_count($commentdata_comment_content_lc, $filter_384_term);
	$filter_384_limit = 25;
	$filter_384_trackback_limit = 25;
	$filter_384_author_count = substr_count($commentdata_comment_author_lc, $filter_384_term);
	$filter_384_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_384_author_count;
	// Filter 385: Number of occurrences of 'click here' in comment_author
	$filter_385_term = 'click here';
	$filter_385_count = substr_count($commentdata_comment_content_lc, $filter_385_term);
	$filter_385_limit = 25;
	$filter_385_trackback_limit = 25;
	$filter_385_author_count = substr_count($commentdata_comment_author_lc, $filter_385_term);
	$filter_385_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_385_author_count;
	// Filter 386: Number of occurrences of 'health care' in comment_author
	$filter_386_term = 'health care';
	$filter_386_count = substr_count($commentdata_comment_content_lc, $filter_386_term);
	$filter_386_limit = 25;
	$filter_386_trackback_limit = 25;
	$filter_386_author_count = substr_count($commentdata_comment_author_lc, $filter_386_term);
	$filter_386_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_386_author_count;
	// Filter 387: Number of occurrences of 'healthcare' in comment_author
	$filter_387_term = 'healthcare';
	$filter_387_count = substr_count($commentdata_comment_content_lc, $filter_387_term);
	$filter_387_limit = 25;
	$filter_387_trackback_limit = 25;
	$filter_387_author_count = substr_count($commentdata_comment_author_lc, $filter_387_term);
	$filter_387_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_387_author_count;
	// Filter 388: Number of occurrences of 'visit now' in comment_author
	$filter_388_term = 'visit now';
	$filter_388_count = substr_count($commentdata_comment_content_lc, $filter_388_term);
	$filter_388_limit = 25;
	$filter_388_trackback_limit = 25;
	$filter_388_author_count = substr_count($commentdata_comment_author_lc, $filter_388_term);
	$filter_388_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_388_author_count;
	// Filter 389: Number of occurrences of 'turbo tax' in comment_author
	$filter_389_term = 'turbo tax';
	$filter_389_count = substr_count($commentdata_comment_content_lc, $filter_389_term);
	$filter_389_limit = 25;
	$filter_389_trackback_limit = 25;
	$filter_389_author_count = substr_count($commentdata_comment_author_lc, $filter_389_term);
	$filter_389_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_389_author_count;
	// Filter 390: Number of occurrences of 'photoshop' in comment_author
	$filter_390_term = 'photoshop';
	$filter_390_count = substr_count($commentdata_comment_content_lc, $filter_390_term);
	$filter_390_limit = 25;
	$filter_390_trackback_limit = 25;
	$filter_390_author_count = substr_count($commentdata_comment_author_lc, $filter_390_term);
	$filter_390_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_390_author_count;
	// Filter 391: Number of occurrences of 'drug rehab' in comment_author
	$filter_391_term = 'drug rehab';
	$filter_391_count = substr_count($commentdata_comment_content_lc, $filter_391_term);
	$filter_391_limit = 25;
	$filter_391_trackback_limit = 25;
	$filter_391_author_count = substr_count($commentdata_comment_author_lc, $filter_391_term);
	$filter_391_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_391_author_count;
	// Filter 392: Number of occurrences of 'power kite' in comment_author
	$filter_392_term = 'power kite';
	$filter_392_count = substr_count($commentdata_comment_content_lc, $filter_392_term);
	$filter_392_limit = 25;
	$filter_392_trackback_limit = 25;
	$filter_392_author_count = substr_count($commentdata_comment_author_lc, $filter_392_term);
	$filter_392_author_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_392_author_count;

	// Author Test: for *author names* surrounded by asterisks
	if ( eregi( "^\*", $commentdata_comment_author_lc ) || eregi( "\*$", $commentdata_comment_author_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 300001AUTH';
		}

	//Simple Author equals X (==) Tests
	$filter_300400_term = 'business';
	$filter_300401_term = 'marketing';
	$filter_300402_term = 'cialis';
	$filter_300403_term = 'seo';
	$filter_300404_term = 'cheap';
	$filter_300405_term = 'discount';
	$filter_300406_term = 'insurance';
	$filter_300407_term = 'development';
	$filter_300408_term = 'software';
	$filter_300409_term = 'guide';
	$filter_300410_term = 'tips';
	$filter_300411_term = 'reviews';
	$filter_300412_term = 'test';
	

	// General Spam Terms
	// Filter 500: Number of occurrences of ' loan' in comment_content
	$filter_500_count = substr_count($commentdata_comment_content_lc, ' loan');
	$filter_500_limit = 7;
	$filter_500_trackback_limit = 3;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_500_count;
	// Filter 501: Number of occurrences of 'student ' in comment_content
	$filter_501_count = substr_count($commentdata_comment_content_lc, 'student ');
	$filter_501_limit = 11;
	$filter_501_trackback_limit = 6;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_501_count;
	// Filter 502: Number of occurrences of 'loan consolidation' in comment_content
	$filter_502_count = substr_count($commentdata_comment_content_lc, 'loan consolidation');
	$filter_502_limit = 5;
	$filter_502_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_502_count;
	// Filter 503: Number of occurrences of 'credit card' in comment_content
	$filter_503_count = substr_count($commentdata_comment_content_lc, 'credit card');
	$filter_503_limit = 5;
	$filter_503_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_503_count;
	// Filter 504: Number of occurrences of 'health insurance' in comment_content
	$filter_504_count = substr_count($commentdata_comment_content_lc, 'health insurance');
	$filter_504_limit = 5;
	$filter_504_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_504_count;
	// Filter 505: Number of occurrences of 'student loan' in comment_content
	$filter_505_count = substr_count($commentdata_comment_content_lc, 'student loan');
	$filter_505_limit = 4;
	$filter_505_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_505_count;
	// Filter 506: Number of occurrences of 'student credit card' in comment_content
	$filter_506_count = substr_count($commentdata_comment_content_lc, 'student credit card');
	$filter_506_limit = 4;
	$filter_506_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_506_count;
	// Filter 507: Number of occurrences of 'consolidation student' in comment_content
	$filter_507_count = substr_count($commentdata_comment_content_lc, 'consolidation student');
	$filter_507_limit = 4;
	$filter_507_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_507_count;
	// Filter 508: Number of occurrences of 'student health insurance' in comment_content
	$filter_508_count = substr_count($commentdata_comment_content_lc, 'student health insurance');
	$filter_508_limit = 4;
	$filter_508_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_508_count;
	// Filter 509: Number of occurrences of 'student loan consolidation' in comment_content
	$filter_509_count = substr_count($commentdata_comment_content_lc, 'student loan consolidation');
	$filter_509_limit = 4;
	$filter_509_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_509_count;
	// Filter 510: Number of occurrences of 'data entry' in comment_content
	$filter_510_count = substr_count($commentdata_comment_content_lc, 'data entry');
	$filter_510_limit = 5;
	$filter_510_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_510_count;
	// Filter 511: Number of occurrences of 'asdf' in comment_content
	$filter_511_count = substr_count($commentdata_comment_content_lc, 'asdf');
	$filter_511_limit = 6;
	$filter_511_trackback_limit = 2;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_511_count;

	/*
	// Medical-Related Filters
	$filter_set_2 = array(
						'viagra[::wpsf::]2[::wpsf::]2',
						'v1agra[::wpsf::]1[::wpsf::]1',
						'cialis[::wpsf::]2[::wpsf::]2',
						'c1alis[::wpsf::]1[::wpsf::]1',
						'levitra[::wpsf::]2[::wpsf::]2',
						'lev1tra[::wpsf::]1[::wpsf::]1',
						'erectile[::wpsf::]3[::wpsf::]3',
						'erectile dysfuntion[::wpsf::]2[::wpsf::]2',
						'erection[::wpsf::]2[::wpsf::]2',
						'valium[::wpsf::]5[::wpsf::]5',
						'xanax[::wpsf::]5[::wpsf::]5'
						);
	
	// Sex-Related Filters - Common Words occuring in Sex/Porn Spam
	$filter_set_3 = array(
						'porn[::wpsf::]5[::wpsf::]5',
						'teen porn[::wpsf::]1[::wpsf::]1',
						'rape porn[::wpsf::]1[::wpsf::]1',
						'incest porn[::wpsf::]1[::wpsf::]1',
						'torture porn[::wpsf::]1[::wpsf::]1',
						'hentai[::wpsf::]2[::wpsf::]2',
						'sex movie[::wpsf::]3[::wpsf::]3',
						'sex tape[::wpsf::]3[::wpsf::]3',
						'sex[::wpsf::]5[::wpsf::]5',
						'xxx[::wpsf::]5[::wpsf::]5',
						'nude[::wpsf::]5[::wpsf::]5',
						'naked[::wpsf::]5[::wpsf::]5',
						'fucking[::wpsf::]6[::wpsf::]6',
						'pussy[::wpsf::]3[::wpsf::]3',
						'penis[::wpsf::]3[::wpsf::]3',
						'vagina[::wpsf::]3[::wpsf::]3',
						'gay porn[::wpsf::]3[::wpsf::]3',
						'anal sex[::wpsf::]3[::wpsf::]3',
						'masturbation[::wpsf::]3[::wpsf::]3',
						'masterbation[::wpsf::]2[::wpsf::]2',
						'masturbating[::wpsf::]3[::wpsf::]3',
						'masterbating[::wpsf::]2[::wpsf::]2',
						'masturbate[::wpsf::]3[::wpsf::]3',
						'masterbate[::wpsf::]2[::wpsf::]2',
						'bestiality[::wpsf::]2[::wpsf::]2',
						'animal sex[::wpsf::]3[::wpsf::]3',
						'orgasm[::wpsf::]5[::wpsf::]5',
						'ejaculating[::wpsf::]3[::wpsf::]3',
						'ejaculation[::wpsf::]3[::wpsf::]3',
						'ejaculate[::wpsf::]3[::wpsf::]3',
						'dildo[::wpsf::]4[::wpsf::]4'
						);

	// Pingback/Trackback Filters
	$filter_set_4 = array( 
						'[...]  [...][::wpsf::]0[::wpsf::]1'
						);
		
	// Test Filters
	$filter_set_5 = array( 
						'wpsfteststring-3n44j57kkdsmks39248sje83njd839[::wpsf::]1[::wpsf::]1'
						);
	
	$filter_set_master = array_merge( $filter_set_1, $filter_set_2, $filter_set_3, $filter_set_4, $filter_set_5 );
	$filter_set_master_count = count($filter_set_master);
	*/
	
	// Complex Filters
	// Check for Optimized URL's and Keyword Phrases Ocurring in Author Name and Content
	
	// Filter 10001: Number of occurrences of 'this is something special' in comment_content
	$filter_10001_count = substr_count($commentdata_comment_content_lc, 'this is something special');
	$filter_10001_limit = 1;
	$filter_10001_trackback_limit = 1;
	// Filter 10002: Number of occurrences of 'http://groups.google.com/group/' in comment_content
	$filter_10002_count = substr_count($commentdata_comment_content_lc, 'http://groups.google.com/group/');
	$filter_10002_limit = 1;
	$filter_10002_trackback_limit = 1;
	// Filter 10003: Number of occurrences of 'youporn' in comment_content
	$filter_10003_count = substr_count($commentdata_comment_content_lc, 'youporn');
	$filter_10003_limit = 1;
	$filter_10003_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_10003_count;
	// Filter 10004: Number of occurrences of 'pornotube' in comment_content
	$filter_10004_count = substr_count($commentdata_comment_content_lc, 'pornotube');
	$filter_10004_limit = 1;
	$filter_10004_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_10004_count;
	// Filter 10005: Number of occurrences of 'porntube' in comment_content
	$filter_10005_count = substr_count($commentdata_comment_content_lc, 'porntube');
	$filter_10005_limit = 1;
	$filter_10005_trackback_limit = 1;
	$blacklist_word_combo_total = $blacklist_word_combo_total + $filter_10005_count;
	// Filter 10006: Number of occurrences of 'http://groups.google.us/group/' in comment_content
	$filter_10006_count = substr_count($commentdata_comment_content_lc, 'http://groups.google.us/group/');
	$filter_10006_limit = 1;
	$filter_10006_trackback_limit = 1;
	
	// Filter 20001: Number of occurrences of 'groups.google.com' in comment_author_url
	$filter_20001_count = substr_count($commentdata_comment_author_url_lc, 'groups.google.com');
	$filter_20001C_count = substr_count($commentdata_comment_content_lc, 'groups.google.com');
	$filter_20001_limit = 1;
	$filter_20001_trackback_limit = 1;
	// Filter 20002: Number of occurrences of 'groups.yahoo.com' in comment_author_url
	$filter_20002_count = substr_count($commentdata_comment_author_url_lc, 'groups.yahoo.com');
	$filter_20002C_count = substr_count($commentdata_comment_content_lc, 'groups.yahoo.com');
	$filter_20002_limit = 1;
	$filter_20002_trackback_limit = 1;
	// Filter 20003: Number of occurrences of '.phpbbserver.com' in comment_author_url
	$filter_20003_count = substr_count($commentdata_comment_author_url_lc, '.phpbbserver.com');
	$filter_20003C_count = substr_count($commentdata_comment_content_lc, '.phpbbserver.com');
	$filter_20003_limit = 1;
	$filter_20003_trackback_limit = 1;
	// Filter 20004: Number of occurrences of '.freehostia.com' in comment_author_url
	$filter_20004_count = substr_count($commentdata_comment_author_url_lc, '.freehostia.com');
	$filter_20004C_count = substr_count($commentdata_comment_content_lc, '.freehostia.com');
	$filter_20004_limit = 1;
	$filter_20004_trackback_limit = 1;
	// Filter 20005: Number of occurrences of 'groups.google.us' in comment_author_url
	$filter_20005_count = substr_count($commentdata_comment_author_url_lc, 'groups.google.us');
	$filter_20005C_count = substr_count($commentdata_comment_content_lc, 'groups.google.us');
	$filter_20005_limit = 1;
	$filter_20005_trackback_limit = 1;
	// Filter 20006: Number of occurrences of 'groups.google.us' in comment_author_url
	$filter_20006_count = substr_count($commentdata_comment_author_url_lc, 'www.google.com/notebook/public/');
	$filter_20006C_count = substr_count($commentdata_comment_content_lc, 'www.google.com/notebook/public/');
	$filter_20006_limit = 1;
	$filter_20006_trackback_limit = 1;
	// Filter 20007: Number of occurrences of 'groups.google.us' in comment_author_url
	$filter_20007_count = substr_count($commentdata_comment_author_url_lc, '.free-site-host.com');
	$filter_20007C_count = substr_count($commentdata_comment_content_lc, '.free-site-host.com');
	$filter_20007_limit = 1;
	$filter_20007_trackback_limit = 1;
	// Filter 20008: Number of occurrences of 'youporn736.vox.com' in comment_author_url
	$filter_20008_count = substr_count($commentdata_comment_author_url_lc, 'youporn736.vox.com');
	$filter_20008C_count = substr_count($commentdata_comment_content_lc, 'youporn736.vox.com');
	$filter_20008_limit = 1;
	$filter_20008_trackback_limit = 1;
	// Filter 20009: Number of occurrences of 'keywordspy.com' in comment_author_url
	$filter_20009_count = substr_count($commentdata_comment_author_url_lc, 'keywordspy.com');
	$filter_20009C_count = substr_count($commentdata_comment_content_lc, 'keywordspy.com');
	$filter_20009_limit = 1;
	$filter_20009_trackback_limit = 1;
	// Filter 20010: Number of occurrences of '.t35.com' in comment_author_url
	$filter_20010_count = substr_count($commentdata_comment_author_url_lc, '.t35.com');
	$filter_20010C_count = substr_count($commentdata_comment_content_lc, '.t35.com');
	$filter_20010_limit = 1;
	$filter_20010_trackback_limit = 1;
	// Filter 20011: Number of occurrences of '.150m.com' in comment_author_url
	$filter_20011_count = substr_count($commentdata_comment_author_url_lc, '.150m.com');
	$filter_20011C_count = substr_count($commentdata_comment_content_lc, '.150m.com');
	$filter_20011_limit = 1;
	$filter_20011_trackback_limit = 1;
	// Filter 20012: Number of occurrences of '.250m.com' in comment_author_url
	$filter_20012_count = substr_count($commentdata_comment_author_url_lc, '.250m.com');
	$filter_20012C_count = substr_count($commentdata_comment_content_lc, '.250m.com');
	$filter_20012_limit = 1;
	$filter_20012_trackback_limit = 1;
	// Filter 20013: Number of occurrences of 'blogs.ign.com' in comment_author_url
	$filter_20013_count = substr_count($commentdata_comment_author_url_lc, 'blogs.ign.com');
	$filter_20013C_count = substr_count($commentdata_comment_content_lc, 'blogs.ign.com');
	$filter_20013_limit = 1;
	$filter_20013_trackback_limit = 1;
	// Filter 20014: Number of occurrences of 'members.lycos.co.uk' in comment_author_url
	$filter_20014_count = substr_count($commentdata_comment_author_url_lc, 'members.lycos.co.uk');
	$filter_20014C_count = substr_count($commentdata_comment_content_lc, 'members.lycos.co.uk');
	$filter_20014_limit = 1;
	$filter_20014_trackback_limit = 1;
	// Filter 20015: Number of occurrences of '/christiantorrents.ru' in comment_author_url
	$filter_20015_count = substr_count($commentdata_comment_author_url_lc, '/christiantorrents.ru');
	$filter_20015C_count = substr_count($commentdata_comment_content_lc, '/christiantorrents.ru');
	$filter_20015_limit = 1;
	$filter_20015_trackback_limit = 1;
	// Filter 20016: Number of occurrences of '.christiantorrents.ru' in comment_author_url
	$filter_20016_count = substr_count($commentdata_comment_author_url_lc, '.christiantorrents.ru');
	$filter_20016C_count = substr_count($commentdata_comment_content_lc, '.christiantorrents.ru');
	$filter_20016_limit = 1;
	$filter_20016_trackback_limit = 1;
	// Filter 20017: Number of occurrences of '/lifecity.tv' in comment_author_url
	$filter_20017_count = substr_count($commentdata_comment_author_url_lc, '/lifecity.tv');
	$filter_20017C_count = substr_count($commentdata_comment_content_lc, '/lifecity.tv');
	$filter_20017_limit = 1;
	$filter_20017_trackback_limit = 1;
	// Filter 20018: Number of occurrences of '.lifecity.tv' in comment_author_url
	$filter_20018_count = substr_count($commentdata_comment_author_url_lc, '.lifecity.tv');
	$filter_20018C_count = substr_count($commentdata_comment_content_lc, '.lifecity.tv');
	$filter_20018_limit = 1;
	$filter_20018_trackback_limit = 1;
	// Filter 20019: Number of occurrences of '/lifecity.info' in comment_author_url
	$filter_20019_count = substr_count($commentdata_comment_author_url_lc, '/lifecity.info');
	$filter_20019C_count = substr_count($commentdata_comment_content_lc, '/lifecity.info');
	$filter_20019_limit = 1;
	$filter_20019_trackback_limit = 1;
	// Filter 20020: Number of occurrences of '.lifecity.info' in comment_author_url
	$filter_20020_count = substr_count($commentdata_comment_author_url_lc, '.lifecity.info');
	$filter_20020C_count = substr_count($commentdata_comment_content_lc, '.lifecity.info');
	$filter_20020_limit = 1;
	$filter_20020_trackback_limit = 1;
	// NEW20000 CODES SETUP
	// Filter 20021: Number of occurrences of 'widecircles.com' in comment_author_url / comment_content
	$filter_20021_domain = 'widecircles.com'; // MASSIVE SPAMMERS
	$filter_20021_domain_http = 'http://'.$filter_20021_domain;
	$filter_20021_domain_dot = '.'.$filter_20021_domain;
	$filter_20021_count = substr_count($commentdata_comment_author_url_lc, $filter_20021_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20021_domain_dot);
	$filter_20021C_count = substr_count($commentdata_comment_content_lc, $filter_20021_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20021_domain_dot);
	$filter_20021_limit = 1;
	$filter_20021_trackback_limit = 1;
	// Filter 20022: Number of occurrences of 'netcallidus.com' in comment_author_url / comment_content
	$filter_20022_domain = 'netcallidus.com'; // SPAMMERS
	$filter_20022_domain_http = 'http://'.$filter_20022_domain;
	$filter_20022_domain_dot = '.'.$filter_20022_domain;
	$filter_20022_count = substr_count($commentdata_comment_author_url_lc, $filter_20022_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20022_domain_dot);
	$filter_20022C_count = substr_count($commentdata_comment_content_lc, $filter_20022_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20022_domain_dot);
	$filter_20022_limit = 1;
	$filter_20022_trackback_limit = 1;
	// Filter 20023: Number of occurrences of 'webseomasters.com' in comment_author_url / comment_content
	$filter_20023_domain = 'webseomasters.com'; // SPAMMERS
	$filter_20023_domain_http = 'http://'.$filter_20023_domain;
	$filter_20023_domain_dot = '.'.$filter_20023_domain;
	$filter_20023_count = substr_count($commentdata_comment_author_url_lc, $filter_20023_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20023_domain_dot);
	$filter_20023C_count = substr_count($commentdata_comment_content_lc, $filter_20023_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20023_domain_dot);
	$filter_20023_limit = 1;
	$filter_20023_trackback_limit = 1;
	// Filter 20024: Number of occurrences of 'mastersofseo.com' in comment_author_url / comment_content
	$filter_20024_domain = 'mastersofseo.com'; // SPAMMERS
	$filter_20024_domain_http = 'http://'.$filter_20024_domain;
	$filter_20024_domain_dot = '.'.$filter_20024_domain;
	$filter_20024_count = substr_count($commentdata_comment_author_url_lc, $filter_20024_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20024_domain_dot);
	$filter_20024C_count = substr_count($commentdata_comment_content_lc, $filter_20024_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20024_domain_dot);
	$filter_20024_limit = 1;
	$filter_20024_trackback_limit = 1;
	// Filter 20025: Number of occurrences of 'mysmartseo.com' in comment_author_url / comment_content
	$filter_20025_domain = 'mysmartseo.com'; // SPAMMERS
	$filter_20025_domain_http = 'http://'.$filter_20025_domain;
	$filter_20025_domain_dot = '.'.$filter_20025_domain;
	$filter_20025_count = substr_count($commentdata_comment_author_url_lc, $filter_20025_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20025_domain_dot);
	$filter_20025C_count = substr_count($commentdata_comment_content_lc, $filter_20025_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20025_domain_dot);
	$filter_20025_limit = 1;
	$filter_20025_trackback_limit = 1;
	// Filter 20026: Number of occurrences of 'sitemapwriter.com' in comment_author_url / comment_content
	$filter_20026_domain = 'sitemapwriter.com'; // SPAMMERS
	$filter_20026_domain_http = 'http://'.$filter_20026_domain;
	$filter_20026_domain_dot = '.'.$filter_20026_domain;
	$filter_20026_count = substr_count($commentdata_comment_author_url_lc, $filter_20026_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20026_domain_dot);
	$filter_20026C_count = substr_count($commentdata_comment_content_lc, $filter_20026_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20026_domain_dot);
	$filter_20026_limit = 1;
	$filter_20026_trackback_limit = 1;
	// Filter 20027: Number of occurrences of 'shredderwarehouse.com' in comment_author_url / comment_content
	$filter_20027_domain = 'shredderwarehouse.com'; // SPAMMERS
	$filter_20027_domain_http = 'http://'.$filter_20027_domain;
	$filter_20027_domain_dot = '.'.$filter_20027_domain;
	$filter_20027_count = substr_count($commentdata_comment_author_url_lc, $filter_20027_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20027_domain_dot);
	$filter_20027C_count = substr_count($commentdata_comment_content_lc, $filter_20027_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20027_domain_dot);
	$filter_20027_limit = 1;
	$filter_20027_trackback_limit = 1;
	// Filter 20028: Number of occurrences of 'mmoinn.com' in comment_author_url / comment_content
	$filter_20028_domain = 'mmoinn.com'; // SPAMMERS
	$filter_20028_domain_http = 'http://'.$filter_20028_domain;
	$filter_20028_domain_dot = '.'.$filter_20028_domain;
	$filter_20028_count = substr_count($commentdata_comment_author_url_lc, $filter_20028_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20028_domain_dot);
	$filter_20028C_count = substr_count($commentdata_comment_content_lc, $filter_20028_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20028_domain_dot);
	$filter_20028_limit = 1;
	$filter_20028_trackback_limit = 1;
	// Filter 20029: Number of occurrences of 'animatedfavicon.com' in comment_author_url / comment_content
	$filter_20029_domain = 'animatedfavicon.com'; // SPAMMERS
	$filter_20029_domain_http = 'http://'.$filter_20029_domain;
	$filter_20029_domain_dot = '.'.$filter_20029_domain;
	$filter_20029_count = substr_count($commentdata_comment_author_url_lc, $filter_20029_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20029_domain_dot);
	$filter_20029C_count = substr_count($commentdata_comment_content_lc, $filter_20029_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20029_domain_dot);
	$filter_20029_limit = 1;
	$filter_20029_trackback_limit = 1;
	// Filter 20030: Number of occurrences of 'cignusweb.com' in comment_author_url / comment_content
	$filter_20030_domain = 'cignusweb.com'; // SPAMMERS
	$filter_20030_domain_http = 'http://'.$filter_20030_domain;
	$filter_20030_domain_dot = '.'.$filter_20030_domain;
	$filter_20030_count = substr_count($commentdata_comment_author_url_lc, $filter_20030_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20030_domain_dot);
	$filter_20030C_count = substr_count($commentdata_comment_content_lc, $filter_20030_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20030_domain_dot);
	$filter_20030_limit = 1;
	$filter_20030_trackback_limit = 1;
	// Filter 20031: Number of occurrences of 'rsschannelwriter.com' in comment_author_url / comment_content
	$filter_20031_domain = 'rsschannelwriter.com'; // SPAMMERS
	$filter_20031_domain_http = 'http://'.$filter_20031_domain;
	$filter_20031_domain_dot = '.'.$filter_20031_domain;
	$filter_20031_count = substr_count($commentdata_comment_author_url_lc, $filter_20031_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20031_domain_dot);
	$filter_20031C_count = substr_count($commentdata_comment_content_lc, $filter_20031_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20031_domain_dot);
	$filter_20031_limit = 1;
	$filter_20031_trackback_limit = 1;
	// Filter 20032: Number of occurrences of 'clickaudit.com' in comment_author_url / comment_content
	$filter_20032_domain = 'clickaudit.com'; // SPAMMERS
	$filter_20032_domain_http = 'http://'.$filter_20032_domain;
	$filter_20032_domain_dot = '.'.$filter_20032_domain;
	$filter_20032_count = substr_count($commentdata_comment_author_url_lc, $filter_20032_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20032_domain_dot);
	$filter_20032C_count = substr_count($commentdata_comment_content_lc, $filter_20032_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20032_domain_dot);
	$filter_20032_limit = 1;
	$filter_20032_trackback_limit = 1;
	// Filter 20033: Number of occurrences of 'choice-direct.com' in comment_author_url / comment_content
	$filter_20033_domain = 'choice-direct.com'; // SPAMMERS
	$filter_20033_domain_http = 'http://'.$filter_20033_domain;
	$filter_20033_domain_dot = '.'.$filter_20033_domain;
	$filter_20033_count = substr_count($commentdata_comment_author_url_lc, $filter_20033_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20033_domain_dot);
	$filter_20033C_count = substr_count($commentdata_comment_content_lc, $filter_20033_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20033_domain_dot);
	$filter_20033_limit = 1;
	$filter_20033_trackback_limit = 1;
	// Filter 20034: Number of occurrences of 'experl.com' in comment_author_url / comment_content
	$filter_20034_domain = 'experl.com'; // SPAMMERS
	$filter_20034_domain_http = 'http://'.$filter_20034_domain;
	$filter_20034_domain_dot = '.'.$filter_20034_domain;
	$filter_20034_count = substr_count($commentdata_comment_author_url_lc, $filter_20034_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20034_domain_dot);
	$filter_20034C_count = substr_count($commentdata_comment_content_lc, $filter_20034_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20034_domain_dot);
	$filter_20034_limit = 1;
	$filter_20034_trackback_limit = 1;
	// Filter 20035: Number of occurrences of 'registry-error-cleaner.com' in comment_author_url / comment_content
	$filter_20035_domain = 'registry-error-cleaner.com'; // SPAMMERS
	$filter_20035_domain_http = 'http://'.$filter_20035_domain;
	$filter_20035_domain_dot = '.'.$filter_20035_domain;
	$filter_20035_count = substr_count($commentdata_comment_author_url_lc, $filter_20035_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20035_domain_dot);
	$filter_20035C_count = substr_count($commentdata_comment_content_lc, $filter_20035_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20035_domain_dot);
	$filter_20035_limit = 1;
	$filter_20035_trackback_limit = 1;
	// Filter 20036: Number of occurrences of 'sunitawedsamit.com' in comment_author_url / comment_content
	$filter_20036_domain = 'sunitawedsamit.com'; // SPAMMERS
	$filter_20036_domain_http = 'http://'.$filter_20036_domain;
	$filter_20036_domain_dot = '.'.$filter_20036_domain;
	$filter_20036_count = substr_count($commentdata_comment_author_url_lc, $filter_20036_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20036_domain_dot);
	$filter_20036C_count = substr_count($commentdata_comment_content_lc, $filter_20036_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20036_domain_dot);
	$filter_20036_limit = 1;
	$filter_20036_trackback_limit = 1;
	// Filter 20037: Number of occurrences of 'agriimplements.com' in comment_author_url / comment_content
	$filter_20037_domain = 'agriimplements.com'; // SPAMMERS
	$filter_20037_domain_http = 'http://'.$filter_20037_domain;
	$filter_20037_domain_dot = '.'.$filter_20037_domain;
	$filter_20037_count = substr_count($commentdata_comment_author_url_lc, $filter_20037_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20037_domain_dot);
	$filter_20037C_count = substr_count($commentdata_comment_content_lc, $filter_20037_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20037_domain_dot);
	$filter_20037_limit = 1;
	$filter_20037_trackback_limit = 1;
	// Filter 20038: Number of occurrences of 'real-url.org' in comment_author_url / comment_content
	$filter_20038_domain = 'real-url.org'; // SPAMMERS
	$filter_20038_domain_http = 'http://'.$filter_20038_domain;
	$filter_20038_domain_dot = '.'.$filter_20038_domain;
	$filter_20038_count = substr_count($commentdata_comment_author_url_lc, $filter_20038_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20038_domain_dot);
	$filter_20038C_count = substr_count($commentdata_comment_content_lc, $filter_20038_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20038_domain_dot);
	$filter_20038_limit = 1;
	$filter_20038_trackback_limit = 1;
	// Filter 20039: Number of occurrences of 'phpdug.net' in comment_author_url / comment_content
	$filter_20039_domain = 'phpdug.net'; // SPAMMERS
	$filter_20039_domain_http = 'http://'.$filter_20039_domain;
	$filter_20039_domain_dot = '.'.$filter_20039_domain;
	$filter_20039_count = substr_count($commentdata_comment_author_url_lc, $filter_20039_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20039_domain_dot);
	$filter_20039C_count = substr_count($commentdata_comment_content_lc, $filter_20039_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20039_domain_dot);
	$filter_20039_limit = 1;
	$filter_20039_trackback_limit = 1;
	// Filter 20040: Number of occurrences of 'submit-trackback.com' in comment_author_url / comment_content
	$filter_20040_domain = 'submit-trackback.com'; // SPAMMERS
	$filter_20040_domain_http = 'http://'.$filter_20040_domain;
	$filter_20040_domain_dot = '.'.$filter_20040_domain;
	$filter_20040_count = substr_count($commentdata_comment_author_url_lc, $filter_20040_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20040_domain_dot);
	$filter_20040C_count = substr_count($commentdata_comment_content_lc, $filter_20040_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20040_domain_dot);
	$filter_20040_limit = 1;
	$filter_20040_trackback_limit = 1;
	// Filter 20041: Number of occurrences of 'commentposter.com' in comment_author_url / comment_content
	$filter_20041_domain = 'commentposter.com'; // SPAMMERS
	$filter_20041_domain_http = 'http://'.$filter_20041_domain;
	$filter_20041_domain_dot = '.'.$filter_20041_domain;
	$filter_20041_count = substr_count($commentdata_comment_author_url_lc, $filter_20041_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20041_domain_dot);
	$filter_20041C_count = substr_count($commentdata_comment_content_lc, $filter_20041_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20041_domain_dot);
	$filter_20041_limit = 1;
	$filter_20041_trackback_limit = 1;
	// Filter 20042: Number of occurrences of 'post-comments.com' in comment_author_url / comment_content
	$filter_20042_domain = 'post-comments.com'; // SPAMMERS
	$filter_20042_domain_http = 'http://'.$filter_20042_domain;
	$filter_20042_domain_dot = '.'.$filter_20042_domain;
	$filter_20042_count = substr_count($commentdata_comment_author_url_lc, $filter_20042_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20042_domain_dot);
	$filter_20042C_count = substr_count($commentdata_comment_content_lc, $filter_20042_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20042_domain_dot);
	$filter_20042_limit = 1;
	$filter_20042_trackback_limit = 1;
	// Filter 20043: Number of occurrences of 'submitbookmark.com' in comment_author_url / comment_content
	$filter_20043_domain = 'submitbookmark.com'; // SPAMMERS
	$filter_20043_domain_http = 'http://'.$filter_20043_domain;
	$filter_20043_domain_dot = '.'.$filter_20043_domain;
	$filter_20043_count = substr_count($commentdata_comment_author_url_lc, $filter_20043_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20043_domain_dot);
	$filter_20043C_count = substr_count($commentdata_comment_content_lc, $filter_20043_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20043_domain_dot);
	$filter_20043_limit = 1;
	$filter_20043_trackback_limit = 1;
	// Filter 20044: Number of occurrences of 'youtube-poster.com' in comment_author_url / comment_content
	$filter_20044_domain = 'youtube-poster.com'; // SPAMMERS
	$filter_20044_domain_http = 'http://'.$filter_20044_domain;
	$filter_20044_domain_dot = '.'.$filter_20044_domain;
	$filter_20044_count = substr_count($commentdata_comment_author_url_lc, $filter_20044_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20044_domain_dot);
	$filter_20044C_count = substr_count($commentdata_comment_content_lc, $filter_20044_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20044_domain_dot);
	$filter_20044_limit = 1;
	$filter_20044_trackback_limit = 1;
	// Filter 20045: Number of occurrences of 'wordpressautocomment.com' in comment_author_url / comment_content
	$filter_20045_domain = 'wordpressautocomment.com'; // SPAMMERS
	$filter_20045_domain_http = 'http://'.$filter_20045_domain;
	$filter_20045_domain_dot = '.'.$filter_20045_domain;
	$filter_20045_count = substr_count($commentdata_comment_author_url_lc, $filter_20045_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20045_domain_dot);
	$filter_20045C_count = substr_count($commentdata_comment_content_lc, $filter_20045_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20045_domain_dot);
	$filter_20045_limit = 1;
	$filter_20045_trackback_limit = 1;
	// Filter 20046: Number of occurrences of 'johnbeck.com' in comment_author_url / comment_content
	$filter_20046_domain = 'johnbeck.com'; // SPAMMERS
	$filter_20046_domain_http = 'http://'.$filter_20046_domain;
	$filter_20046_domain_dot = '.'.$filter_20046_domain;
	$filter_20046_count = substr_count($commentdata_comment_author_url_lc, $filter_20046_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20046_domain_dot);
	$filter_20046C_count = substr_count($commentdata_comment_content_lc, $filter_20046_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20046_domain_dot);
	$filter_20046_limit = 1;
	$filter_20046_trackback_limit = 1;
	// Filter 20047: Number of occurrences of 'johnbeck.net' in comment_author_url / comment_content
	$filter_20047_domain = 'johnbeck.net'; // SPAMMERS
	$filter_20047_domain_http = 'http://'.$filter_20047_domain;
	$filter_20047_domain_dot = '.'.$filter_20047_domain;
	$filter_20047_count = substr_count($commentdata_comment_author_url_lc, $filter_20047_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20047_domain_dot);
	$filter_20047C_count = substr_count($commentdata_comment_content_lc, $filter_20047_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20047_domain_dot);
	$filter_20047_limit = 1;
	$filter_20047_trackback_limit = 1;
	// Filter 20048: Number of occurrences of 'johnbeck.tv' in comment_author_url / comment_content
	$filter_20048_domain = 'johnbeck.tv'; // SPAMMERS
	$filter_20048_domain_http = 'http://'.$filter_20048_domain;
	$filter_20048_domain_dot = '.'.$filter_20048_domain;
	$filter_20048_count = substr_count($commentdata_comment_author_url_lc, $filter_20048_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20048_domain_dot);
	$filter_20048C_count = substr_count($commentdata_comment_content_lc, $filter_20048_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20048_domain_dot);
	$filter_20048_limit = 1;
	$filter_20048_trackback_limit = 1;
	// Filter 20049: Number of occurrences of 'johnbeckseminar.com' in comment_author_url / comment_content
	$filter_20049_domain = 'johnbeckseminar.com'; // SPAMMERS
	$filter_20049_domain_http = 'http://'.$filter_20049_domain;
	$filter_20049_domain_dot = '.'.$filter_20049_domain;
	$filter_20049_count = substr_count($commentdata_comment_author_url_lc, $filter_20049_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20049_domain_dot);
	$filter_20049C_count = substr_count($commentdata_comment_content_lc, $filter_20049_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20049_domain_dot);
	$filter_20049_limit = 1;
	$filter_20049_trackback_limit = 1;
	// Filter 20050: Number of occurrences of 'johnbeckssuccessstories.com' in comment_author_url / comment_content
	$filter_20050_domain = 'johnbeckssuccessstories.com'; // SPAMMERS
	$filter_20050_domain_http = 'http://'.$filter_20050_domain;
	$filter_20050_domain_dot = '.'.$filter_20050_domain;
	$filter_20050_count = substr_count($commentdata_comment_author_url_lc, $filter_20050_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20050_domain_dot);
	$filter_20050C_count = substr_count($commentdata_comment_content_lc, $filter_20050_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20050_domain_dot);
	$filter_20050_limit = 1;
	$filter_20050_trackback_limit = 1;
	// Filter 20051: Number of occurrences of 'grillpartssteak.com' in comment_author_url / comment_content
	$filter_20051_domain = 'grillpartssteak.com'; // SPAMMERS
	$filter_20051_domain_http = 'http://'.$filter_20051_domain;
	$filter_20051_domain_dot = '.'.$filter_20051_domain;
	$filter_20051_count = substr_count($commentdata_comment_author_url_lc, $filter_20051_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20051_domain_dot);
	$filter_20051C_count = substr_count($commentdata_comment_content_lc, $filter_20051_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20051_domain_dot);
	$filter_20051_limit = 1;
	$filter_20051_trackback_limit = 1;
	// Filter 20052: Number of occurrences of 'kankamforum.net' in comment_author_url / comment_content
	$filter_20052_domain = 'kankamforum.net'; // SPAMMERS
	$filter_20052_domain_http = 'http://'.$filter_20052_domain;
	$filter_20052_domain_dot = '.'.$filter_20052_domain;
	$filter_20052_count = substr_count($commentdata_comment_author_url_lc, $filter_20052_domain_http) + substr_count($commentdata_comment_author_url_lc, $filter_20052_domain_dot);
	$filter_20052C_count = substr_count($commentdata_comment_content_lc, $filter_20052_domain_http) + substr_count($commentdata_comment_content_lc, $filter_20052_domain_dot);
	$filter_20052_limit = 1;
	$filter_20052_trackback_limit = 1;



	$commentdata_comment_author_lc_spam_strong = '<strong>'.$commentdata_comment_author_lc.'</strong>'; // Trackbacks
	$commentdata_comment_author_lc_spam_strong_dot1 = '...</strong>'; // Trackbacks
	$commentdata_comment_author_lc_spam_strong_dot2 = '...</b>'; // Trackbacks
	$commentdata_comment_author_lc_spam_a1 = $commentdata_comment_author_lc.'</a>'; // Trackbacks/Pingbacks
	$commentdata_comment_author_lc_spam_a2 = $commentdata_comment_author_lc.' </a>'; // Trackbacks/Pingbacks
	
	$WPCommentsPostURL = $commentdata_blog_lc.'/wp-comments-post.php';

	$Domains = array('.aero','.arpa','.asia','.biz','.cat','.com','.coop','.edu','.gov','.info','.int','.jobs','.mil','.mobi','.museum','.name','.net','.org','.pro','.tel','.travel','.ac','.ad','.ae','.af','.ai','.al','.am','.an','.ao','.aq','.ar','.as','.at','.au','.aw','.ax','.az','.ba','.bb','.bd','.be','.bf','.bg','.bh','.bi','.bj','.bl','.bm','.bn','.bo','.br','.bs','.bt','.bv','.bw','.by','.bz','.ca','.cc','.cf','.cg','.ch','.ci','.ck','.cl','.cm','.cn','.co','.cr','.cu','.cv','.cx','.cy','.cz','.de','.dj','.dk','.dm','.do','.dz','.ec','.ee','.eg','.eh','.er','.es','.et','.eu','.fi','.fj','.fk','.fm','.fo','.fr','.ga','.gb','.gd','.ge','.gf','.gg','.gh','.gi','.gl','.gm','.gn','.gp','.gq','.gr','.gs','.gt','.gu','.gw','.gy','.hk','.hm','.hn','.hr','.ht','.hu','.id','.ie','.il','.im','.in','.io','.iq','.ir','.is','.it','.je','.jm','.jo','.jp','.ke','.kg','.kh','.ki','.km','.km','.kp','.kr','.kw','.ky','.kz','.la','.lb','.lc','.li','.lk','.lr','.ls','.lt','.lu','.lv','.ly','.ma','.mc','.mc','.md','.me','.mf','.mg','.mh','.mk','.ml','.mm','.mn','.mo','.mq','.mr','.ms','.mt','.mu','.mv','.mw','.mx','.my','.mz','.na','.nc','.ne','.nf','.ng','.ni','.nl','.no','.np','.nr','.nu','.nz','.om','.pa','.pe','.pf','.pg','.ph','.pk','.pl','.pm','.pn','.pr','.ps','.pt','.pw','.py','.qa','.re','.ro','.rs','.ru','.rw','.sa','.sb','.sc','.sd','.se','.sg','.sh','.si','.sj','.sk','.sl','.sm','.sn','.so','.sr','.st','.su','.sv','.sy','.sz','.tc','.td','.tf','.tg','.th','.tj','.tk','.tl','.tm','.tn','.to','.tp','.tr','.tt','.tv','.tw','.tz','.ua','.ug','.uk','.um','.us','.uy','.uz','.va','.vc','.ve','.vg','.vi','.vn','.vu','.wf','.ws','.ye','.yt','.yu','.za','.zm','.zw');
	// from http://www.iana.org/domains/root/db/
	$ConversionSeparator = '-';
	$ConversionSeparators = array('-','_');
	$FilterElementsPrefix = array('http://www.','http://');
	$FilterElementsPage = array('.php','.asp','.cfm','.jsp','.html','.htm','.shtml');
	$FilterElementsNum = array('1','2','3','4','5','6','7','8','9','0');
	$FilterElementsSlash = array('////','///','//');
	$TempPhrase1 = str_replace($FilterElementsPrefix,'',$commentdata_comment_author_url_lc);
	$TempPhrase2 = str_replace($FilterElementsPage,'',$TempPhrase1);
	$TempPhrase3 = str_replace($Domains,'',$TempPhrase2);
	$TempPhrase4 = str_replace($FilterElementsNum,'',$TempPhrase3);
	$TempPhrase5 = str_replace($FilterElementsSlash,'/',$TempPhrase4);
	$TempPhrase6 = strtolower(str_replace($ConversionSeparators,' ',$TempPhrase5));
	$KeywordURLPhrases = explode('/',$TempPhrase6);
	$KeywordURLPhrasesCount = count($KeywordURLPhrases);
	$KeywordCommentAuthorPhrasePunct = array('\:','\;','\+','\-','\!','\.','\,','\[','\]','\@','\#','\$','\%','\^','\&','\*','\(','\)','\/','\\','\|','\=','\_');
	$KeywordCommentAuthorTempPhrase = str_replace($KeywordCommentAuthorPhrasePunct,'',$commentdata_comment_author_lc);
	$KeywordCommentAuthorPhrase1 = str_replace(' ','',$KeywordCommentAuthorTempPhrase);
	$KeywordCommentAuthorPhrase2 = str_replace(' ','-',$KeywordCommentAuthorTempPhrase);
	$KeywordCommentAuthorPhrase3 = str_replace(' ','_',$KeywordCommentAuthorTempPhrase);
	$KeywordCommentAuthorPhraseURLVariation = $FilterElementsPage;
	$KeywordCommentAuthorPhraseURLVariation[] = '/';
	$KeywordCommentAuthorPhraseURLVariationCount = count($KeywordCommentAuthorPhraseURLVariation);
	
	$SplogTrackbackPhrase1 		= 'an interesting post today.here\'s a quick excerpt';
	$SplogTrackbackPhrase1a 	= 'an interesting post today.here&#8217;s a quick excerpt';
	$SplogTrackbackPhrase2 		= 'an interesting post today. here\'s a quick excerpt';
	$SplogTrackbackPhrase2a 	= 'an interesting post today. here&#8217;s a quick excerpt';
	$SplogTrackbackPhrase3 		= 'an interesting post today onhere\'s a quick excerpt';
	$SplogTrackbackPhrase3a		= 'an interesting post today onhere&#8217;s a quick excerpt';
	$SplogTrackbackPhrase4 		= 'read the rest of this great post here';
	$SplogTrackbackPhrase5 		= 'here to see the original:';
		
	$SplogTrackbackPhrase20a 	= 'an interesting post today on';
	$SplogTrackbackPhrase20b 	= 'here\'s a quick excerpt';
	$SplogTrackbackPhrase20c 	= 'here&#8217;s a quick excerpt';
	
	$blacklist_word_combo_limit = 7;
	$blacklist_word_combo = 0;

	$i = 0;
	
	// Execute Simple Filter Test(s)
	if ( $filter_1_count >= $filter_1_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 1';
		}
	if ( $filter_2_count >= $filter_2_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 2';
		}
	if ( $filter_2_count ) { $blacklist_word_combo++; }
	if ( $filter_3_count >= $filter_3_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 3';
		}
	if ( $filter_3_count ) { $blacklist_word_combo++; }
	if ( $filter_4_count >= $filter_4_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 4';
		}
	if ( $filter_4_count ) { $blacklist_word_combo++; }
	if ( $filter_5_count >= $filter_5_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 5';
		}
	if ( $filter_5_count ) { $blacklist_word_combo++; }
	if ( $filter_6_count >= $filter_6_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 6';
		}
	if ( $filter_6_count ) { $blacklist_word_combo++; }
	if ( $filter_7_count >= $filter_7_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 7';
		}
	if ( $filter_7_count ) { $blacklist_word_combo++; }
	if ( $filter_8_count >= $filter_8_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 8';
		}
	if ( $filter_8_count ) { $blacklist_word_combo++; }
	if ( $filter_9_count >= $filter_9_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 9';
		}
	if ( $filter_9_count ) { $blacklist_word_combo++; }
	if ( $filter_10_count >= $filter_10_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 10';
		}
	if ( $filter_10_count ) { $blacklist_word_combo++; }
	if ( $filter_11_count >= $filter_11_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 11';
		}
	if ( $filter_11_count ) { $blacklist_word_combo++; }
	if ( $filter_12_count >= $filter_12_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 12';
		}
	if ( $filter_12_count ) { $blacklist_word_combo++; }
	if ( $filter_13_count >= $filter_13_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 13';
		}
	if ( $filter_13_count ) { $blacklist_word_combo++; }	
	if ( $filter_14_count >= $filter_14_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 14';
		}
	if ( $filter_14_count ) { $blacklist_word_combo++; }	
	if ( $filter_15_count >= $filter_15_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 15';
		}
	if ( $filter_15_count ) { $blacklist_word_combo++; }	
	if ( $filter_16_count >= $filter_16_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 16';
		}
	if ( $filter_16_count ) { $blacklist_word_combo++; }
	if ( $filter_17_count >= $filter_17_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 17';
		}
	if ( $filter_17_count ) { $blacklist_word_combo++; }
	if ( $filter_18_count >= $filter_18_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 18';
		}
	if ( $filter_18_count ) { $blacklist_word_combo++; }
	if ( $filter_19_count >= $filter_19_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 19';
		}
	if ( $filter_19_count ) { $blacklist_word_combo++; }
	if ( $filter_20_count >= $filter_20_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20';
		}
	if ( $filter_20_count ) { $blacklist_word_combo++; }
	if ( $filter_21_count >= $filter_21_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 21';
		}
	if ( $filter_21_count ) { $blacklist_word_combo++; }
	if ( $filter_22_count >= $filter_22_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 22';
		}
	if ( $filter_22_count ) { $blacklist_word_combo++; }
	if ( $filter_23_count >= $filter_23_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 23';
		}
	if ( $filter_23_count ) { $blacklist_word_combo++; }
	if ( $filter_24_count >= $filter_24_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 24';
		}
	if ( $filter_24_count ) { $blacklist_word_combo++; }
	if ( $filter_25_count >= $filter_25_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 25';
		}
	if ( $filter_25_count ) { $blacklist_word_combo++; }
	if ( $filter_26_count >= $filter_26_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 26';
		}
	if ( $filter_26_count ) { $blacklist_word_combo++; }
	if ( $filter_27_count >= $filter_27_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 27';
		}
	if ( $filter_27_count ) { $blacklist_word_combo++; }
	if ( $filter_28_count >= $filter_28_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 28';
		}
	if ( $filter_28_count ) { $blacklist_word_combo++; }
	if ( $filter_29_count >= $filter_29_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 29';
		}
	if ( $filter_29_count ) { $blacklist_word_combo++; }
	if ( $filter_30_count >= $filter_30_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 30';
		}
	if ( $filter_30_count ) { $blacklist_word_combo++; }
	if ( $filter_31_count >= $filter_31_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 31';
		}
	if ( $filter_31_count ) { $blacklist_word_combo++; }
	if ( $filter_32_count >= $filter_32_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 32';
		}
	if ( $filter_32_count ) { $blacklist_word_combo++; }
	if ( $filter_33_count >= $filter_33_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 33';
		}
	if ( $filter_33_count ) { $blacklist_word_combo++; }
	if ( $filter_34_count >= $filter_34_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 34';
		}
	if ( $filter_34_count ) { $blacklist_word_combo++; }
	if ( $filter_35_count >= $filter_35_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 35';
		}
	if ( $filter_35_count ) { $blacklist_word_combo++; }
	if ( $filter_36_count >= $filter_36_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 36';
		}
	if ( $filter_36_count ) { $blacklist_word_combo++; }
	if ( $filter_37_count >= $filter_37_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 37';
		}
	if ( $filter_37_count ) { $blacklist_word_combo++; }
	if ( $filter_38_count >= $filter_38_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 38';
		}
	if ( $filter_38_count ) { $blacklist_word_combo++; }
	if ( $filter_39_count >= $filter_39_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 39';
		}
	if ( $filter_39_count ) { $blacklist_word_combo++; }
	if ( $filter_40_count >= $filter_40_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 40';
		}
	if ( $filter_40_count ) { $blacklist_word_combo++; }
	if ( $filter_41_count >= $filter_41_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 41';
		}
	if ( $filter_41_count ) { $blacklist_word_combo++; }
		
	if ( $filter_104_count >= $filter_104_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 104';
		}
	if ( $filter_104_count ) { $blacklist_word_combo++; }
	if ( $filter_105_count >= $filter_105_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 105';
		}
	if ( $filter_105_count ) { $blacklist_word_combo++; }
	if ( $filter_106_count >= $filter_106_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 106';
		}
	if ( $filter_106_count ) { $blacklist_word_combo++; }
	if ( $filter_107_count >= $filter_107_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 107';
		}
	if ( $filter_107_count ) { $blacklist_word_combo++; }
	if ( $filter_108_count >= $filter_108_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 108';
		}
	if ( $filter_108_count ) { $blacklist_word_combo++; }
	if ( $filter_109_count >= $filter_109_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 109';
		}
	if ( $filter_109_count ) { $blacklist_word_combo++; }
	if ( $filter_110_count >= $filter_110_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 110';
		}
	if ( $filter_110_count ) { $blacklist_word_combo++; }
	if ( $filter_111_count >= $filter_111_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 111';
		}
	if ( $filter_111_count ) { $blacklist_word_combo++; }
	if ( $filter_112_count >= $filter_112_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 112';
		}
	if ( $filter_112_count ) { $blacklist_word_combo++; }
	if ( $filter_113_count >= $filter_113_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 113';
		}
	if ( $filter_113_count ) { $blacklist_word_combo++; }
	if ( $filter_114_count >= $filter_114_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 114';
		}
	if ( $filter_114_count ) { $blacklist_word_combo++; }
	if ( $filter_115_count >= $filter_115_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 115';
		}
	if ( $filter_115_count ) { $blacklist_word_combo++; }
	if ( $filter_116_count >= $filter_116_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 116';
		}
	if ( $filter_116_count ) { $blacklist_word_combo++; }
	if ( $filter_117_count >= $filter_117_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 117';
		}
	if ( $filter_117_count ) { $blacklist_word_combo++; }
	if ( $filter_118_count >= $filter_118_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 118';
		}
	if ( $filter_118_count ) { $blacklist_word_combo++; }
	if ( $filter_119_count >= $filter_119_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 119';
		}
	if ( $filter_119_count ) { $blacklist_word_combo++; }
	if ( $filter_120_count >= $filter_120_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 120';
		}
	if ( $filter_120_count ) { $blacklist_word_combo++; }
	if ( $filter_121_count >= $filter_121_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 121';
		}
	if ( $filter_121_count ) { $blacklist_word_combo++; }
	if ( $filter_122_count >= $filter_122_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 122';
		}
	if ( $filter_122_count ) { $blacklist_word_combo++; }
	if ( $filter_123_count >= $filter_123_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 123';
		}
	if ( $filter_123_count ) { $blacklist_word_combo++; }
	if ( $filter_124_count >= $filter_124_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 124';
		}
	if ( $filter_124_count ) { $blacklist_word_combo++; }
	if ( $filter_125_count >= $filter_125_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 125';
		}
	if ( $filter_125_count ) { $blacklist_word_combo++; }
	if ( $filter_126_count >= $filter_126_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 126';
		}
	if ( $filter_126_count ) { $blacklist_word_combo++; }
	if ( $filter_127_count >= $filter_127_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 127';
		}
	if ( $filter_127_count ) { $blacklist_word_combo++; }
	if ( $filter_128_count >= $filter_128_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 128';
		}
	if ( $filter_128_count ) { $blacklist_word_combo++; }
	if ( $filter_129_count >= $filter_129_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 129';
		}
	if ( $filter_129_count ) { $blacklist_word_combo++; }
	if ( $filter_130_count >= $filter_130_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 130';
		}
	if ( $filter_130_count ) { $blacklist_word_combo++; }
	if ( $filter_131_count >= $filter_131_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 131';
		}
	if ( $filter_131_count ) { $blacklist_word_combo++; }
	if ( $filter_132_count >= $filter_132_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 132';
		}
	if ( $filter_132_count ) { $blacklist_word_combo++; }
	if ( $filter_133_count >= $filter_133_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 133';
		}
	if ( $filter_133_count ) { $blacklist_word_combo++; }
	if ( $filter_134_count >= $filter_134_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 134';
		}
	if ( $filter_134_count ) { $blacklist_word_combo++; }
	if ( $filter_135_count >= $filter_135_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 135';
		}
	if ( $filter_135_count ) { $blacklist_word_combo++; }
	if ( $filter_136_count >= $filter_136_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 136';
		}
	if ( $filter_136_count ) { $blacklist_word_combo++; }
	if ( $filter_137_count >= $filter_137_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 137';
		}
	if ( $filter_137_count ) { $blacklist_word_combo++; }
	if ( $filter_138_count >= $filter_138_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 138';
		}
	if ( $filter_138_count ) { $blacklist_word_combo++; }
	if ( $filter_139_count >= $filter_139_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 139';
		}
	if ( $filter_139_count ) { $blacklist_word_combo++; }
	if ( $filter_140_count >= $filter_140_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 140';
		}
	if ( $filter_140_count ) { $blacklist_word_combo++; }
	if ( $filter_141_count >= $filter_141_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 141';
		}
	if ( $filter_141_count ) { $blacklist_word_combo++; }
	if ( $filter_142_count >= $filter_142_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 142';
		}
	if ( $filter_142_count ) { $blacklist_word_combo++; }
	if ( $filter_143_count >= $filter_143_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 143';
		}
	if ( $filter_143_count ) { $blacklist_word_combo++; }
	if ( $filter_144_count >= $filter_144_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 144';
		}
	if ( $filter_144_count ) { $blacklist_word_combo++; }
	if ( $filter_145_count >= $filter_145_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 145';
		}
	if ( $filter_145_count ) { $blacklist_word_combo++; }
	if ( $filter_146_count >= $filter_146_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 146';
		}
	if ( $filter_146_count ) { $blacklist_word_combo++; }
	if ( $filter_147_count >= $filter_147_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 147';
		}
	if ( $filter_147_count ) { $blacklist_word_combo++; }
	if ( $filter_148_count >= $filter_148_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 148';
		}
	if ( $filter_148_count ) { $blacklist_word_combo++; }
	if ( $filter_149_count >= $filter_149_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 149';
		}
	if ( $filter_149_count ) { $blacklist_word_combo++; }
	if ( $filter_150_count >= $filter_150_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 150';
		}
	if ( $filter_150_count ) { $blacklist_word_combo++; }
	if ( $filter_151_count >= $filter_151_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 151';
		}
	if ( $filter_151_count ) { $blacklist_word_combo++; }
	if ( $filter_152_count >= $filter_152_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 152';
		}
	if ( $filter_152_count ) { $blacklist_word_combo++; }
	if ( $filter_153_count >= $filter_153_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 153';
		}
	if ( $filter_153_count ) { $blacklist_word_combo++; }
	if ( $filter_154_count >= $filter_154_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 154';
		}
	if ( $filter_154_count ) { $blacklist_word_combo++; }
	if ( $filter_155_count >= $filter_155_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 155';
		}
	if ( $filter_155_count ) { $blacklist_word_combo++; }
	if ( $filter_156_count >= $filter_156_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 156';
		}
	if ( $filter_156_count ) { $blacklist_word_combo++; }
	if ( $filter_157_count >= $filter_157_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 157';
		}
	if ( $filter_157_count ) { $blacklist_word_combo++; }
	if ( $filter_158_count >= $filter_158_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 158';
		}
	if ( $filter_158_count ) { $blacklist_word_combo++; }


	if ( $filter_500_count >= $filter_500_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 500';
		}
	if ( $filter_500_count ) { $blacklist_word_combo++; }
	if ( $filter_501_count >= $filter_501_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 501';
		}
	if ( $filter_501_count ) { $blacklist_word_combo++; }
	if ( $filter_502_count >= $filter_502_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 502';
		}
	if ( $filter_502_count ) { $blacklist_word_combo++; }
	if ( $filter_503_count >= $filter_503_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 503';
		}
	if ( $filter_503_count ) { $blacklist_word_combo++; }
	if ( $filter_504_count >= $filter_504_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 504';
		}
	if ( $filter_504_count ) { $blacklist_word_combo++; }
	if ( $filter_505_count >= $filter_505_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 505';
		}
	if ( $filter_505_count ) { $blacklist_word_combo++; }
	if ( $filter_506_count >= $filter_506_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 506';
		}
	if ( $filter_506_count ) { $blacklist_word_combo++; }
	if ( $filter_507_count >= $filter_507_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 507';
		}
	if ( $filter_507_count ) { $blacklist_word_combo++; }
	if ( $filter_508_count >= $filter_508_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 508';
		}
	if ( $filter_508_count ) { $blacklist_word_combo++; }
	if ( $filter_509_count >= $filter_509_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 509';
		}
	if ( $filter_509_count ) { $blacklist_word_combo++; }
	if ( $filter_510_count >= $filter_510_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 510';
		}
	if ( $filter_510_count ) { $blacklist_word_combo++; }
	if ( $filter_511_count >= $filter_511_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 511';
		}
	if ( $filter_511_count ) { $blacklist_word_combo++; }

	/*
	// Execute Filter Test(s)

	$i = 0;
	while ( $i <= $filter_set_master_count ) {
		$filter_phrase_parameters = explode( '[::wpsf::]', $filter_set_master[$i] );
		$filter_phrase 					= $filter_phrase_parameters[0];
		$filter_phrase_limit 			= $filter_phrase_parameters[1];
		$filter_phrase_trackback_limit 	= $filter_phrase_parameters[2];
		$filter_phrase_count			= substr_count( $commentdata_comment_content_lc, $filter_phrase );
		if ( ( $filter_phrase_limit != 0 && $filter_phrase_count >= $filter_phrase_limit ) || ( $filter_phrase_limit == 1 && eregi( $filter_phrase, $commentdata_comment_author_lc ) ) || ( $commentdata_comment_author_lc == $filter_phrase ) ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			}
		$i++;
		}
	*/
	
	// Regular Expression Tests
	
	if ( eregi( "\<a\ href\=\"http\:\/\/([a-z0-9\.\-]+)\.com/\"\>([a-z0-9\.\-]+)\<\/a\>\,\ \[url\=http\:\/\/([a-z0-9\.\-]+)\.com\/\]([a-z0-9\.\-]+)\[\/url\]\,\ \[link\=http\:\/\/([a-z0-9\.\-]+)\.com\/\]([a-z0-9\.\-]+)\[\/link\]\,\ http\:\/\/([a-z0-9\.\-]+)\.com\/", $commentdata_comment_content_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' RE10001';
		}
	if ( eregi( "\<a\ href\=\\\"http\:\/\/([a-z0-9\.\-]+)\.com\/\\\"\>([a-z0-9\.\-]+)\<\/a\>\,\ \[url\=http\:\/\/([a-z0-9\.\-]+)\.com\/\]([a-z0-9\.\-]+)\[\/url\]\,\ \[link\=http\:\/\/([a-z0-9\.\-]+)\.com\/\]([a-z0-9\.\-]+)\[\/link\]\,\ http\:\/\/([a-z0-9\.\-]+)\.com\/", $commentdata_comment_content_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' RE10001';
		}
	if ( eregi( "\<a\ href\=\\\"http\:\/\/([a-z0-9\.\-]+)\.com\/\\\"\>([a-z0-9\.\-]+)\<\/a\>\,\ \[url\=http\:\/\/([a-z0-9\.\-]+)\.com\/\]([a-z0-9\.\-]+)\[\/url\]\,\ \[link\=http\:\/\/([a-z0-9\.\-]+)\.\com\/]([a-z0-9\.\-\ ]+)\[\/link\]\,\ http\:\/\/([a-z0-9\.\-]+)\.com\/", $commentdata_comment_content_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' RE10002';
		}
	if ( eregi( "\<a\ href\=", $commentdata_comment_content_lc ) && eregi( "\<\/a\>,", $commentdata_comment_content_lc ) && eregi( "\[url\=http\:\/\/", $commentdata_comment_content_lc ) && eregi( "\.com\/\]", $commentdata_comment_content_lc ) && eregi( "\[\/url\]\,", $commentdata_comment_content_lc ) && eregi( "\[link\=http\:\/\/", $commentdata_comment_content_lc )  && eregi( "\[\/link\]\,", $commentdata_comment_content_lc ) && substr_count(  $commentdata_comment_content_lc, "http\:\/\/" ) > 2 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' RE10003';
		}
	
	// Test Comment Author 
	// Words in Comment Author Repeated in Content - With Keyword Density
	$RepeatedTermsFilters = array('.','-',':');
	$RepeatedTermsTempPhrase = str_replace($RepeatedTermsFilters,'',$commentdata_comment_author_lc);
	$RepeatedTermsTest = explode(' ',$RepeatedTermsTempPhrase);
	$RepeatedTermsTestCount = count($RepeatedTermsTest);
	$CommentContentTotalWords = count( explode( ' ', $commentdata_comment_content_lc ) );
	$i = 0;
	while ( $i <= $RepeatedTermsTestCount ) {
		if ( $RepeatedTermsTest[$i] ) {
			$RepeatedTermsInContentCount = substr_count( $commentdata_comment_content_lc, $RepeatedTermsTest[$i] );
			$RepeatedTermsInContentStrLength = strlen($RepeatedTermsTest[$i]);
			if ( $RepeatedTermsInContentCount > 1 && $CommentContentTotalWords < $RepeatedTermsInContentCount ) {
				$RepeatedTermsInContentCount = 1;
				}
			$RepeatedTermsInContentDensity = ( $RepeatedTermsInContentCount / $CommentContentTotalWords ) * 100;
			//$spamfree_error_code .= ' 9000-'.$i.' KEYWORD: '.$RepeatedTermsTest[$i].' DENSITY: '.$RepeatedTermsInContentDensity.'% TIMES WORD OCCURS: '.$RepeatedTermsInContentCount.' TOTAL WORDS: '.$CommentContentTotalWords;
			if ( $RepeatedTermsInContentCount >= 5 && $RepeatedTermsInContentStrLength >= 4 && $RepeatedTermsInContentDensity > 40 ) {		
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' 9000-'.$i;
				}
			}
		$i++;
		}
	// Comment Author and Comment Author URL appearing in Content
	if ( $commentdata_comment_author_url_lc ) {
		$commentdata_comment_author_lc_inhref = '>'.$commentdata_comment_author_lc.'</a>';
		//$commentdata_comment_author_url_lc_insquote = "\\'".$commentdata_comment_author_url_lc."\\'";
		//$commentdata_comment_author_url_lc_indquote = "\\\"".$commentdata_comment_author_url_lc."\\\"";
		
		if ( eregi($commentdata_comment_author_url_lc,$commentdata_comment_content_lc) && eregi($commentdata_comment_author_lc_inhref,$commentdata_comment_content_lc) ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 9100';
			}
		/* Not working
		if ( eregi( $commentdata_comment_author_url_lc_insquote, $commentdata_comment_content_lc ) ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 9101';
			}
		if ( eregi( $commentdata_comment_author_url_lc_indquote, $commentdata_comment_content_lc ) ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 9102';
			}
		*/
		}
	// Emails
	if ( $commentdata_comment_author_email_lc == 'aaron@yahoo.com' || $commentdata_comment_author_email_lc == 'asdf@yahoo.com' || $commentdata_comment_author_email_lc == 'a@a.com' || $commentdata_comment_author_email_lc == 'bill@berlin.com' || $commentdata_comment_author_email_lc == 'capricanrulz@hotmail.com' || $commentdata_comment_author_email_lc == 'dominic@mail.com' || $commentdata_comment_author_email_lc == 'fuck@you.com' || $commentdata_comment_author_email_lc == 'heel@mail.com' || $commentdata_comment_author_email_lc == 'jane@mail.com' || $commentdata_comment_author_email_lc == 'neo@hotmail.com' || $commentdata_comment_author_email_lc == 'nick76@mailbox.com' || $commentdata_comment_author_email_lc == '12345@yahoo.com' || 	$commentdata_comment_author_email_lc == 'poster78@gmail.com' || $commentdata_comment_author_email_lc == 'ycp_m23@hotmail.com' || $commentdata_comment_author_email_lc == 'grey_dave@yahoo.com' || $commentdata_comment_author_email_lc == 'grren_dave55@hotmail.com' || $commentdata_comment_author_email_lc == 'dave_morales@hotmail.com' || $commentdata_comment_author_email_lc == 'tbs_guy@hotmail.com' || $commentdata_comment_author_email_lc == 'test@test.com' || eregi( '.seo@gmail.com', $commentdata_comment_author_email_lc ) || eregi( '@keywordspy.com', $commentdata_comment_author_email_lc ) || eregi( '@fuckyou.com', $commentdata_comment_author_email_lc ) || eregi( 'fuckyou@', $commentdata_comment_author_email_lc ) || eregi( 'spammer@', $commentdata_comment_author_email_lc ) || eregi( 'spambot@', $commentdata_comment_author_email_lc ) || eregi( 'spam@', $commentdata_comment_author_email_lc ) || eregi( 'anonymous@', $commentdata_comment_author_email_lc ) || eregi( 'root@', $commentdata_comment_author_email_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 9200';
		}
	// Test Referrers
	if ( eregi( $commentdata_php_self_lc, $WPCommentsPostURL ) && $commentdata_referrer_lc == $WPCommentsPostURL ) {
		// Often spammers send the referrer as the URL for the wp-comments-post.php page. Nimrods.
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' REF1000';
		}

	if( $_POST[ 'refJS' ] && $_POST[ 'refJS' ] != '' ) {
		$refJS = addslashes( urldecode( $_POST[ 'refJS' ] ) );
		$refJS = str_replace( '%3A', ':', $refJS );
		if ( eregi( "\.google\.co(m|\.[a-z]{2})", $refJS ) && eregi( "leave a comment", $refJS ) ) { 
			// make test more robust for other versions of google & search query
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' REF1001';
			}
		// add Keyword Script Here
		}

	/*
	// Disabled temp because of IE8 Private Browsing
	//if ( !$commentdata_referrer_lc ) {
	if ( !eregi( $BlogServerName, $commentdata_referrer_lc ) ) {
		// Often spammers send the referrer as blank. Only valid if equal to site domain.
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 1001';
		}
	*/
	
	// Spam Network :: BEGIN

	// Test User-Agents
	if ( !$commentdata_user_agent_lc  ) {
		// There is no reason for a blank UA String, unless it's been altered.
		$content_filter_status = '2';
		$spamfree_error_code .= ' UA1001';
		}
	$commentdata_user_agent_lc_word_count = count( explode( " ", $commentdata_user_agent_lc ) );
	if ( $commentdata_user_agent_lc && $commentdata_user_agent_lc_word_count < 3 ) {
		if ( $commentdata_comment_type != 'trackback' && $commentdata_comment_type != 'pingback' || ( !eregi( 'movabletype', $commentdata_user_agent_lc ) && ( $commentdata_comment_type == 'trackback' || $commentdata_comment_type == 'pingback' ) ) ) {
			// Another test for altered UA's.
			$content_filter_status = '2';
			$spamfree_error_code .= ' UA1001.1-'.$commentdata_user_agent_lc;
			}
		}
	if ( eregi( 'libwww-perl', $commentdata_user_agent_lc ) || eregi( "^(nutch|larbin|jakarta|java)", $commentdata_user_agent_lc ) ) {
		// There is no reason for a human to use one of these UA strings. Commonly used to attack/spam WP.
		$content_filter_status = '2';
		$spamfree_error_code .= ' UA1002';
		}
	if ( eregi( 'iopus-', $commentdata_user_agent_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' UA1003';
		}
	
	if ( $commentdata_comment_type != 'trackback' && $commentdata_comment_type != 'pingback' ) {
	
		//Test HTTP_ACCEPT_LANGUAGE
		$user_http_accept_language = trim($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if ( !$user_http_accept_language ) {
			$content_filter_status = '2';
			$spamfree_error_code .= ' HAL1001';
			}

		//Test PROXY STATUS if option
		if ( $ipProxy == 'PROXY DETECTED' && !$spamfree_options['allow_proxy_users'] ) {
			$content_filter_status = '10';
			$spamfree_error_code .= ' PROXY1001';
			}
	
		}


	// Test IPs
	//if ( $commentdata_remote_addr_lc == '64.20.49.178' || $commentdata_remote_addr_lc == '206.123.92.245' || $commentdata_remote_addr_lc == '72.249.100.188' || $commentdata_remote_addr_lc == '61.24.158.174' || $commentdata_remote_addr_lc == '77.92.88.27' || $commentdata_remote_addr_lc == '89.113.78.6' || $commentdata_remote_addr_lc == '92.48.65.27' || $commentdata_remote_addr_lc == '92.48.122.2' || $commentdata_remote_addr_lc == '92.241.176.200' || $commentdata_remote_addr_lc == '78.129.202.2' || $commentdata_remote_addr_lc == '78.129.202.15' || eregi( "^78.129.202.", $commentdata_remote_addr_lc ) || eregi( "^123.237.144.", $commentdata_remote_addr_lc ) || eregi( "^123.237.147.", $commentdata_remote_addr_lc ) ) {
	$spamfree_ip_bans = array(
								'66.60.98.1',
								'67.227.135.200',
								'74.86.148.194',
								'77.92.88.13',
								'77.92.88.27',
								'78.129.202.15',
								'78.129.202.2',
								'78.157.143.202',
								'87.106.55.101',
								'91.121.77.168',
								'92.241.176.200',
								'92.48.122.2',
								'92.48.122.3',
								'92.48.65.27',
								'92.241.168.216',
								'115.42.64.19',
								'116.71.33.252',
								'116.71.35.192',
								'116.71.59.69',
								'122.160.70.94',
								'122.162.251.167',
								'123.237.144.189',
								'123.237.144.92',
								'123.237.147.71',
								'193.37.152.242',
								'193.46.236.151',
								'193.46.236.152',
								'193.46.236.234',
								);
	if ( in_array( $commentdata_remote_addr, $spamfree_ip_bans ) || eregi( "^78\.129\.202\.", $commentdata_remote_addr_lc ) || eregi( "^123\.237\.144\.", $commentdata_remote_addr_lc ) || eregi( "^123\.237\.147.", $commentdata_remote_addr_lc ) || eregi( "^194\.8\.7([45])\.", $commentdata_remote_addr_lc ) || eregi( "^193\.37\.152.", $commentdata_remote_addr_lc ) || eregi( "^193\.46\.236\.", $commentdata_remote_addr_lc ) || eregi( "^92\.48\.122\.([0-9]|[12][0-9]|3[01])$", $commentdata_remote_addr_lc ) || eregi( "^116\.71\.", $commentdata_remote_addr_lc ) ) {
		// 194.8.74.0 - 194.8.75.255 BAD spam network - BRITISH VIRGIN ISLANDS
		// 193.37.152.0 - 193.37.152.255 SPAM NETWORK - WEB HOST, NOT ISP - GERMANY
		// 193.46.236.0 - 193.46.236.255 SPAM NETWORK - WEB HOST, NOT ISP - LATVIA
		// 92.48.122.0 - 92.48.122.31 SPAM NETWORK - SERVERS, NOT ISP - BELGRADE
		// KeywordSpy.com caught using IP's in the range 123.237.144. and 123.237.147.
		// 91.121.77.168 real-url.org
		
		// 87.106.55.101 SPAM NETWORK - SERVERS, NOT ISP - (.websitehome.co.uk)
		// 74.86.148.194 SPAM NETWORK - WEB HOST, NOT ISP (rover-host.com)
		// 67.227.135.200 SPAM NETWORK - WEB HOST, NOT ISP (host.lotosus.com)
		// 66.60.98.1 SPAM NETWORK - WEB SITE/HOST, NOT ISP - (rdns.softwiseonline.com)
		// 116.71.0.0 - 116.71.255.255 - SPAM NETWORK - PAKISTAN - Ptcl Triple Play Project

		$content_filter_status = '2';
		$spamfree_error_code .= ' IP1002-'.$commentdata_remote_addr_lc;
		}
	if ( eregi( "^192\.168\.", $commentdata_remote_addr_lc ) && !eregi( "^192\.168\.", $BlogServerIP ) && !eregi( 'localhost', $BlogServerName ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' IP1003-'.$commentdata_remote_addr_lc;
		}
	// Test Remote Hosts
	if ( eregi( 'keywordspy.com', $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1003-'.$commentdata_remote_host_lc;
		}
	if ( eregi( "clients\.your\-server\.de$", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1004-'.$commentdata_remote_host_lc;
		}
	if ( eregi( "^rover\-host\.com$", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1005-'.$commentdata_remote_host_lc;
		}
	if ( eregi( "^host\.lotosus\.com$", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1006-'.$commentdata_remote_host_lc;
		}
	if ( eregi( "^rdns\.softwiseonline\.com$", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1007-'.$commentdata_remote_host_lc;
		}
	if ( eregi( "s([a-z0-9]+)\.websitehome\.co\.uk$", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1008-'.$commentdata_remote_host_lc;
		}
	if ( eregi( "\.opentransfer\.com$", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1009-'.$commentdata_remote_host_lc;
		}

		
	/*	
	if ( eregi( "^host\.", $commentdata_remote_host_lc ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' RH1009-'.$commentdata_remote_host_lc;
		}
	*/

	/*
	// Following is causing errors on some systems. - 06/17/08
	if ( $commentdata_remote_host_lc == 'blank' && $commentdata_comment_type != 'trackback' && $commentdata_comment_type != 'pingback' ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' 1004';
		}
	*/
	// Test Reverse DNS Hosts
	if ( eregi( 'keywordspy.com', $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1023-'.$ReverseDNS;
		}
	if ( eregi( "clients\.your\-server\.de$", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1024-'.$ReverseDNS;
		}
	if ( eregi( "^rover\-host\.com$", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1025-'.$ReverseDNS;
		}
	if ( eregi( "^host\.lotosus\.com$", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1026-'.$ReverseDNS;
		}
	if ( eregi( "^rdns\.softwiseonline\.com$", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1027-'.$ReverseDNS;
		}
	if ( eregi( "^s([a-z0-9]+)\.websitehome\.co\.uk$", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1028-'.$ReverseDNS;
		}
	if ( eregi( "\.opentransfer\.com$", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1029-'.$ReverseDNS;
		}		
		
	/*	
	if ( eregi( "^host\.", $ReverseDNS ) ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' REVD1029-'.$ReverseDNS;
		}
	*/


	// Test Reverse DNS IP's
	/* 
	// Temporarily disabling to investigate errors - 02/22/09
	// Possibly remove permanently in next version
	// 
	// If faked to Match blog Server IP
	if ( $ReverseDNSIP == $BlogServerIP && $commentdata_comment_type != 'trackback' && $commentdata_comment_type != 'pingback' ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' 1031';
		}
	// If faked to be single dot
	if ( $ReverseDNSIP == '.' ) {
		$content_filter_status = '2';
		$spamfree_error_code .= ' 1032';
		}
	*/	
	// Spam Network :: END	
	// Test Pingbacks and Trackbacks
	if ( $commentdata_comment_type == 'pingback' || $commentdata_comment_type == 'trackback' ) {
	
		if ( $filter_1_count >= $filter_1_trackback_limit ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T1';
			}
		if ( $filter_200_count >= $filter_200_trackback_limit ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T200';
			}
		if ( $filter_200_count ) { $blacklist_word_combo++; }
		if ( $commentdata_comment_type == 'trackback' && eregi( 'WordPress', $commentdata_user_agent_lc ) ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T3000';
			}
		// Check History of WordPress User-Agents and Keep up to Date
		if ( eregi( 'Incutio XML-RPC -- WordPress/', $commentdata_user_agent_lc ) ) {
			$commentdata_user_agent_lc_explode = explode( '/', $commentdata_user_agent_lc );
			if ( $commentdata_user_agent_lc_explode[1] > $CurrentWordPressVersionMaxCheck && $commentdata_user_agent_lc_explode[1] !='MU' ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T1001';
				}
			}
		if ( eregi( 'The Incutio XML-RPC PHP Library -- WordPress/', $commentdata_user_agent_lc ) ) {
			$commentdata_user_agent_lc_explode = explode( '/', $commentdata_user_agent_lc );
			if ( $commentdata_user_agent_lc_explode[1] > $CurrentWordPressVersionMaxCheck && $commentdata_user_agent_lc_explode[1] !='MU' ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T1002';
				}
			}
		if ( $commentdata_comment_author == $commentdata_comment_author_lc && eregi( "([a-z]+)", $commentdata_comment_author ) ) {
			// Check to see if Comment Author is lowercase. Normal blog pings Authors are properly capitalized. No brainer.
			// Added second test to only run when using standard alphabet.
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T1010';
			}
		if ( $ipProxy == 'PROXY DETECTED' ) {
			// Check to see if Trackback/Pingback is using proxy. Red flag.
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T1011';
			}
		if ( $commentdata_comment_content == '[...] read more [...]' ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T1020';
			}
		if ( eregi( $SplogTrackbackPhrase1, $commentdata_comment_content_lc_norm_apost ) || eregi( $SplogTrackbackPhrase1a, $commentdata_comment_content_lc ) || eregi( $SplogTrackbackPhrase2, $commentdata_comment_content_lc_norm_apost ) || eregi( $SplogTrackbackPhrase2a, $commentdata_comment_content_lc ) || eregi( $SplogTrackbackPhrase3, $commentdata_comment_content_lc_norm_apost ) || eregi( $SplogTrackbackPhrase3a, $commentdata_comment_content_lc ) || eregi( $SplogTrackbackPhrase4, $commentdata_comment_content_lc_norm_apost ) || eregi( $SplogTrackbackPhrase5, $commentdata_comment_content_lc_norm_apost ) || ( eregi( $SplogTrackbackPhrase20a, $commentdata_comment_content_lc_norm_apost ) && ( eregi( $SplogTrackbackPhrase20b, $commentdata_comment_content_lc_norm_apost ) || eregi( $SplogTrackbackPhrase20c, $commentdata_comment_content_lc ) ) ) ) {
			// Check to see if common patterns exist in comment content.
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T2002';
			}
		if ( eregi( $commentdata_comment_author_lc_spam_strong, $commentdata_comment_content_lc ) ) {
			// Check to see if Comment Author is repeated in content, enclosed in <strong> tags.
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T2003';
			}
		if ( eregi( $commentdata_comment_author_lc_spam_a1, $commentdata_comment_content_lc ) || eregi( $commentdata_comment_author_lc_spam_a2, $commentdata_comment_content_lc ) ) {
			// Check to see if Comment Author is repeated in content, enclosed in <a> tags.
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T2004';
			}
		if ( eregi( $commentdata_comment_author_lc_spam_strong_dot1, $commentdata_comment_content_lc ) ) {
			// Check to see if Phrase... in bold is in content
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T2005';
			}
		if ( eregi( $commentdata_comment_author_lc_spam_strong_dot2, $commentdata_comment_content_lc ) ) {
			// Check to see if Phrase... in bold is in content
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' T2006';
			}
		// Check to see if keyword phrases in url match Comment Author - spammers do this to get links with desired keyword anchor text.
		// Start with url and convert to text phrase for matching against author.
		$i = 0;
		while ( $i <= $KeywordURLPhrasesCount ) {
			if ( $KeywordURLPhrases[$i] == $commentdata_comment_author_lc ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T3001';
				}
			if ( $KeywordURLPhrases[$i] == $commentdata_comment_content_lc ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T3002';
				}
			$i++;
			}
		// Reverse check to see if keyword phrases in url match Comment Author. Start with author and convert to url phrases.
		$i = 0;
		while ( $i <= $KeywordCommentAuthorPhraseURLVariationCount ) {
			$KeywordCommentAuthorPhrase1Version = '/'.$KeywordCommentAuthorPhrase1.$KeywordCommentAuthorPhraseURLVariation[$i];
			$KeywordCommentAuthorPhrase2Version = '/'.$KeywordCommentAuthorPhrase2.$KeywordCommentAuthorPhraseURLVariation[$i];
			$KeywordCommentAuthorPhrase3Version = '/'.$KeywordCommentAuthorPhrase3.$KeywordCommentAuthorPhraseURLVariation[$i];
			$KeywordCommentAuthorPhrase1SubStrCount = substr_count($commentdata_comment_author_url_lc, $KeywordCommentAuthorPhrase1Version);
			$KeywordCommentAuthorPhrase2SubStrCount = substr_count($commentdata_comment_author_url_lc, $KeywordCommentAuthorPhrase2Version);
			$KeywordCommentAuthorPhrase3SubStrCount = substr_count($commentdata_comment_author_url_lc, $KeywordCommentAuthorPhrase3Version);
			if ( $KeywordCommentAuthorPhrase1SubStrCount >= 1 ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T3003-1-'.$KeywordCommentAuthorPhrase1Version;
				}
			else if ( $KeywordCommentAuthorPhrase2SubStrCount >= 1 ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T3003-2-'.$KeywordCommentAuthorPhrase2Version;
				}
			else if ( $KeywordCommentAuthorPhrase3SubStrCount >= 1 ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T3003-3-'.$KeywordCommentAuthorPhrase3Version;
				}
			$i++;
			}
		/*
		$i = 0;
		while ( $i <= $filter_set_master_count ) {
			$filter_phrase_parameters = explode( '[::wpsf::]', $filter_set_master[$i] );
			$filter_phrase 					= $filter_phrase_parameters[0];
			$filter_phrase_limit 			= $filter_phrase_parameters[1];
			$filter_phrase_trackback_limit 	= $filter_phrase_parameters[2];
			$filter_phrase_count			= substr_count( $commentdata_comment_content_lc, $filter_phrase );
			if ( $filter_phrase_count >= $filter_phrase_trackback_limit ) {
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				}
			$i++;
			}
		*/

		// Test Comment Author 
		// Words in Comment Author Repeated in Content		
		$RepeatedTermsFilters = array('.','-',':');
		$RepeatedTermsTempPhrase = str_replace($RepeatedTermsFilters,'',$commentdata_comment_author_lc);
		$RepeatedTermsTest = explode(' ',$RepeatedTermsTempPhrase);
		$RepeatedTermsTestCount = count($RepeatedTermsTest);
		$i = 0;
		while ( $i <= $RepeatedTermsTestCount ) {
			$RepeatedTermsInContentCount = substr_count( $commentdata_comment_content_lc, $RepeatedTermsTest[$i] );
			$RepeatedTermsInContentStrLength = strlen($RepeatedTermsTest[$i]);
			if ( $RepeatedTermsInContentCount >= 6 && $RepeatedTermsInContentStrLength >= 4 ) {		
				if ( !$content_filter_status ) { $content_filter_status = '1'; }
				$spamfree_error_code .= ' T9000-'.$i;
				}
			$i++;
			}
		}
	// Miscellaneous
	if ( $commentdata_comment_content == '[...]  [...]' ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 5000';
		}
	if ( $commentdata_comment_content == '<new comment>' ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 5001';
		}
	if ( eregi( 'blastogranitic atremata antiviral unteacherlike choruser coccygalgia corynebacterium reason', $commentdata_comment_content ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 5002';
		}


	// Execute Complex Filter Test(s)
	if ( $filter_10001_count >= $filter_10001_limit && $filter_10002_count >= $filter_10002_limit &&  ( $filter_10003_count >= $filter_10003_limit || $filter_10004_count >= $filter_10004_limit ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' CF10000';
		}
	if ( $filter_10003_count ) { $blacklist_word_combo++; }

	// Comment Author URL Tests - Free Websites / Crap Websites
	if ( eregi( 'groups.google.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20001';
		}
	if ( $filter_20001_count >= $filter_20001_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20001A';
		}
	if ( $filter_20001C_count >= $filter_20001_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20001C';
		}
	if ( eregi( 'groups.yahoo.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20002';
		}
	if ( $filter_20002_count >= $filter_20002_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20002A';
		}
	if ( $filter_20002C_count >= $filter_20002_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20002C';
		}
	if ( eregi( ".?phpbbserver\.com", $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20003';
		}
	if ( $filter_20003_count >= $filter_20003_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20003A';
		}
	if ( $filter_20003C_count >= $filter_20003_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20003C';
		}
	if ( eregi( '.freehostia.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20004';
		}
	if ( $filter_20004_count >= $filter_20004_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20004A';
		}
	if ( $filter_20004C_count >= $filter_20004_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20004C';
		}
	if ( eregi( 'groups.google.us', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20005';
		}
	if ( $filter_20005_count >= $filter_20005_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20005A';
		}
	if ( $filter_20005C_count >= $filter_20005_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20005C';
		}
	if ( eregi( 'www.google.com/notebook/public/', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20006';
		}
	if ( $filter_20006_count >= $filter_20006_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20006A';
		}
	if ( $filter_20006C_count >= $filter_20006_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20006C';
		}
	if ( eregi( '.free-site-host.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20007';
		}
	if ( $filter_20007_count >= $filter_20007_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20007A';
		}
	if ( $filter_20007C_count >= $filter_20007_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20007C';
		}
	if ( eregi( 'youporn736.vox.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20008';
		}
	if ( $filter_20008_count >= $filter_20008_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20008A';
		}
	if ( $filter_20008C_count >= $filter_20008_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20008C';
		}
	if ( eregi( 'keywordspy.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20009';
		}
	if ( $filter_20009_count >= $filter_20009_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20009A';
		}
	if ( $filter_20009C_count >= $filter_20009_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20009C';
		}
	if ( eregi( '.t35.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20010';
		}
	if ( $filter_20010_count >= $filter_20010_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20010A';
		}
	if ( $filter_20010C_count >= $filter_20010_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20010C';
		}
	if ( eregi( '.150m.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20011';
		}
	if ( $filter_20011_count >= $filter_20011_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20011A';
		}
	if ( $filter_20011C_count >= $filter_20011_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20011C';
		}
	if ( eregi( '.250m.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20012';
		}
	if ( $filter_20012_count >= $filter_20012_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20012A';
		}
	if ( $filter_20012C_count >= $filter_20012_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20012C';
		}
	if ( eregi( 'blogs.ign.com', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20013';
		}
	if ( $filter_20013_count >= $filter_20013_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20013A';
		}
	if ( $filter_20013C_count >= $filter_20013_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20013C';
		}
	if ( eregi( 'members.lycos.co.uk', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20014';
		}
	if ( $filter_20014_count >= $filter_20014_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20014A';
		}
	if ( $filter_20014C_count >= $filter_20014_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20014C';
		}
	if ( eregi( '/christiantorrents.ru', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20015';
		}
	if ( $filter_20015_count >= $filter_20015_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20015A';
		}
	if ( $filter_20015C_count >= $filter_20015_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20015C';
		}
	if ( eregi( '.christiantorrents.ru', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20016';
		}
	if ( $filter_20016_count >= $filter_20016_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20016A';
		}
	if ( $filter_20016C_count >= $filter_20016_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20016C';
		}
	if ( eregi( '/lifecity.tv', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20017';
		}
	if ( $filter_20017_count >= $filter_20017_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20017A';
		}
	if ( $filter_20017C_count >= $filter_20017_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20017C';
		}
	if ( eregi( '.lifecity.tv', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20018';
		}
	if ( $filter_20018_count >= $filter_20018_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20018A';
		}
	if ( $filter_20018C_count >= $filter_20018_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20018C';
		}
	if ( eregi( '/lifecity.info', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20019';
		}
	if ( $filter_20019_count >= $filter_20019_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20019A';
		}
	if ( $filter_20019C_count >= $filter_20019_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20019C';
		}
	if ( eregi( '.lifecity.info', $commentdata_comment_author_url_lc ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20020';
		}
	if ( $filter_20020_count >= $filter_20020_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20020A';
		}
	if ( $filter_20020C_count >= $filter_20020_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20020C';
		}
	// NEW20000 CODES SETUP
	if ( $filter_20021_count >= $filter_20021_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20021A';
		}
	if ( $filter_20021C_count >= $filter_20021_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20021C';
		}	
	if ( $filter_20022_count >= $filter_20022_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20022A';
		}
	if ( $filter_20022C_count >= $filter_20022_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20022C';
		}	
	if ( $filter_20023_count >= $filter_20023_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20023A';
		}
	if ( $filter_20023C_count >= $filter_20023_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20023C';
		}	
	if ( $filter_20024_count >= $filter_20024_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20024A';
		}
	if ( $filter_20024C_count >= $filter_20024_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20024C';
		}	
	if ( $filter_20025_count >= $filter_20025_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20025A';
		}
	if ( $filter_20025C_count >= $filter_20025_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20025C';
		}	
	if ( $filter_20026_count >= $filter_20026_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20026A';
		}
	if ( $filter_20026C_count >= $filter_20026_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20026C';
		}	
	if ( $filter_20027_count >= $filter_20027_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20027A';
		}
	if ( $filter_20027C_count >= $filter_20027_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20027C';
		}	
	if ( $filter_20028_count >= $filter_20028_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20028A';
		}
	if ( $filter_20028C_count >= $filter_20028_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20028C';
		}	
	if ( $filter_20029_count >= $filter_20029_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20029A';
		}
	if ( $filter_20029C_count >= $filter_20029_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20029C';
		}	
	if ( $filter_20030_count >= $filter_20030_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20030A';
		}
	if ( $filter_20030C_count >= $filter_20030_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20030C';
		}	
	if ( $filter_20031_count >= $filter_20031_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20031A';
		}
	if ( $filter_20031C_count >= $filter_20031_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20031C';
		}	
	if ( $filter_20032_count >= $filter_20032_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20032A';
		}
	if ( $filter_20032C_count >= $filter_20032_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20032C';
		}	
	if ( $filter_20033_count >= $filter_20033_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20033A';
		}
	if ( $filter_20033C_count >= $filter_20033_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20033C';
		}	
	if ( $filter_20034_count >= $filter_20034_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20034A';
		}
	if ( $filter_20034C_count >= $filter_20034_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20034C';
		}	
	if ( $filter_20035_count >= $filter_20035_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20035A';
		}
	if ( $filter_20035C_count >= $filter_20035_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20035C';
		}	
	if ( $filter_20036_count >= $filter_20036_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20036A';
		}
	if ( $filter_20036C_count >= $filter_20036_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20036C';
		}	
	if ( $filter_20037_count >= $filter_20037_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20037A';
		}
	if ( $filter_20037C_count >= $filter_20037_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20037C';
		}
	if ( $filter_20038_count >= $filter_20038_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20038A';
		}
	if ( $filter_20038C_count >= $filter_20038_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20038C';
		}
	if ( $filter_20039_count >= $filter_20039_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20039A';
		}
	if ( $filter_20039C_count >= $filter_20039_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20039C';
		}
	if ( $filter_20040_count >= $filter_20040_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20040A';
		}
	if ( $filter_20040C_count >= $filter_20040_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20040C';
		}
	if ( $filter_20041_count >= $filter_20041_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20041A';
		}
	if ( $filter_20041C_count >= $filter_20041_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20041C';
		}
	if ( $filter_20042_count >= $filter_20042_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20042A';
		}
	if ( $filter_20042C_count >= $filter_20042_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20042C';
		}
	if ( $filter_20043_count >= $filter_20043_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20043A';
		}
	if ( $filter_20043C_count >= $filter_20043_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20043C';
		}
	if ( $filter_20044_count >= $filter_20044_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20044A';
		}
	if ( $filter_20044C_count >= $filter_20044_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20044C';
		}
	if ( $filter_20045_count >= $filter_20045_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20045A';
		}
	if ( $filter_20045C_count >= $filter_20045_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20045C';
		}
	if ( $filter_20046_count >= $filter_20046_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20046A';
		}
	if ( $filter_20046C_count >= $filter_20046_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20046C';
		}
	if ( $filter_20047_count >= $filter_20047_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20047A';
		}
	if ( $filter_20047C_count >= $filter_20047_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20047C';
		}
	if ( $filter_20048_count >= $filter_20048_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20048A';
		}
	if ( $filter_20048C_count >= $filter_20048_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20048C';
		}
	if ( $filter_20049_count >= $filter_20049_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20049A';
		}
	if ( $filter_20049C_count >= $filter_20049_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20049C';
		}
	if ( $filter_20050_count >= $filter_20050_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20050A';
		}
	if ( $filter_20050C_count >= $filter_20050_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20050C';
		}
	if ( $filter_20051_count >= $filter_20051_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20051A';
		}
	if ( $filter_20051C_count >= $filter_20051_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20051C';
		}
	if ( $filter_20052_count >= $filter_20052_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20052A';
		}
	if ( $filter_20052C_count >= $filter_20052_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20052C';
		}


	// Comment Author Tests
	if ( $filter_2_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 2AUTH';
		}
	if ( $filter_2_count ) { $blacklist_word_combo++; }
	if ( $filter_3_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 3AUTH';
		}
	if ( $filter_3_count ) { $blacklist_word_combo++; }
	if ( $filter_4_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 4AUTH';
		}
	if ( $filter_4_count ) { $blacklist_word_combo++; }
	if ( $filter_5_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 5AUTH';
		}
	if ( $filter_5_count ) { $blacklist_word_combo++; }
	if ( $filter_6_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 6AUTH';
		}
	if ( $filter_6_count ) { $blacklist_word_combo++; }
	if ( $filter_7_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 7AUTH';
		}
	if ( $filter_7_count ) { $blacklist_word_combo++; }
	if ( $filter_8_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 8AUTH';
		}
	if ( $filter_8_count ) { $blacklist_word_combo++; }
	if ( $filter_9_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 9AUTH';
		}
	if ( $filter_9_count ) { $blacklist_word_combo++; }
	if ( $filter_10_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 10AUTH';
		}
	if ( $filter_10_count ) { $blacklist_word_combo++; }
	if ( $filter_11_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 11AUTH';
		}
	if ( $filter_11_count ) { $blacklist_word_combo++; }
	if ( $filter_12_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 12AUTH';
		}
	if ( $filter_12_count ) { $blacklist_word_combo++; }
	if ( $filter_13_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 13AUTH';
		}
	if ( $filter_13_count ) { $blacklist_word_combo++; }	
	if ( $filter_14_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 14AUTH';
		}
	if ( $filter_14_count ) { $blacklist_word_combo++; }	
	if ( $filter_15_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 15AUTH';
		}
	if ( $filter_15_count ) { $blacklist_word_combo++; }
	if ( $filter_16_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 16AUTH';
		}
	if ( $filter_16_count ) { $blacklist_word_combo++; }	
	if ( $filter_17_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 17AUTH';
		}
	if ( $filter_17_count ) { $blacklist_word_combo++; }
	if ( $filter_18_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 18AUTH';
		}
	if ( $filter_18_count ) { $blacklist_word_combo++; }
	if ( $filter_19_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 19AUTH';
		}
	if ( $filter_19_count ) { $blacklist_word_combo++; }
	if ( $filter_20_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 20AUTH';
		}
	if ( $filter_20_count ) { $blacklist_word_combo++; }
	if ( $filter_21_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 21AUTH';
		}
	if ( $filter_21_count ) { $blacklist_word_combo++; }
	if ( $filter_22_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 22AUTH';
		}
	if ( $filter_22_count ) { $blacklist_word_combo++; }
	if ( $filter_23_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 23AUTH';
		}
	if ( $filter_23_count ) { $blacklist_word_combo++; }
	if ( $filter_24_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 24AUTH';
		}
	if ( $filter_24_count ) { $blacklist_word_combo++; }
	if ( $filter_25_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 25AUTH';
		}
	if ( $filter_25_count ) { $blacklist_word_combo++; }
	if ( $filter_26_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 26AUTH';
		}
	if ( $filter_26_count ) { $blacklist_word_combo++; }
	if ( $filter_27_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 27AUTH';
		}
	if ( $filter_27_count ) { $blacklist_word_combo++; }
	if ( $filter_28_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 28AUTH';
		}
	if ( $filter_28_count ) { $blacklist_word_combo++; }
	if ( $filter_29_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 29AUTH';
		}
	if ( $filter_29_count ) { $blacklist_word_combo++; }
	if ( $filter_30_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 30AUTH';
		}
	if ( $filter_30_count ) { $blacklist_word_combo++; }
	if ( $filter_31_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 31AUTH';
		}
	if ( $filter_31_count ) { $blacklist_word_combo++; }
	if ( $filter_32_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 32AUTH';
		}
	if ( $filter_32_count ) { $blacklist_word_combo++; }
	if ( $filter_33_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 33AUTH';
		}
	if ( $filter_33_count ) { $blacklist_word_combo++; }
	if ( $filter_34_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 34AUTH';
		}
	if ( $filter_34_count ) { $blacklist_word_combo++; }

	if ( eregi( 'buy', $commentdata_comment_author_lc ) && ( eregi( 'online', $commentdata_comment_author_lc ) || eregi( 'pill', $commentdata_comment_author_lc ) ) ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 200AUTH';
		$blacklist_word_combo++;
		}

	// Non-Medical Author Tests
	if ( $filter_210_author_count >= 1 ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' 210AUTH';
		}
	if ( $filter_210_count ) { $blacklist_word_combo++; }
	
	// Comment Author Tests - Non-Trackback - SEO/WebDev/Offshore + Other
	if ( $commentdata_comment_type != 'trackback' && $commentdata_comment_type != 'pingback' ) {
		if ( $filter_300_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300AUTH';
			}
		if ( $filter_301_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 301AUTH';
			}
		if ( $filter_302_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 302AUTH';
			}
		if ( $filter_303_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 303AUTH';
			}
		if ( $filter_304_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 304AUTH';
			}
		if ( $filter_305_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 305AUTH';
			}
		if ( $filter_306_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 306AUTH';
			}
		if ( $filter_307_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 307AUTH';
			}
		if ( $filter_308_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 308AUTH';
			}
		if ( $filter_309_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 309AUTH';
			}
		if ( $filter_310_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 310AUTH';
			}
		if ( $filter_311_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 311AUTH';
			}
		if ( $filter_312_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 312AUTH';
			}
		if ( $filter_313_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 313AUTH';
			}
		if ( $filter_314_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 314AUTH';
			}
		if ( $filter_315_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 315AUTH';
			}
		if ( $filter_316_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 316AUTH';
			}
		if ( $filter_317_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 317AUTH';
			}
		if ( $filter_318_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 318AUTH';
			}
		if ( $filter_319_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 319AUTH';
			}
		if ( $filter_320_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 320AUTH';
			}
		if ( $filter_321_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 321AUTH';
			}
		if ( $filter_322_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 322AUTH';
			}
		if ( $filter_323_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 323AUTH';
			}
		if ( $filter_324_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 324AUTH';
			}
		if ( $filter_325_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 325AUTH';
			}
		if ( $filter_326_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 326AUTH';
			}
		if ( $filter_327_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 327AUTH';
			}
		if ( $filter_328_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 328AUTH';
			}
		if ( $filter_329_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 329AUTH';
			}
		if ( $filter_330_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 330AUTH';
			}
		if ( $filter_331_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 331AUTH';
			}
		if ( $filter_332_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 332AUTH';
			}
		if ( $filter_333_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 333AUTH';
			}
		if ( $filter_334_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 334AUTH';
			}
		if ( $filter_335_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 335AUTH';
			}
		if ( $filter_336_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 336AUTH';
			}
		if ( $filter_337_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 337AUTH';
			}
		if ( $filter_338_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 338AUTH';
			}
		if ( $filter_339_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 339AUTH';
			}
		if ( $filter_340_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 340AUTH';
			}
		if ( $filter_341_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 341AUTH';
			}
		if ( $filter_342_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 342AUTH';
			}
		if ( $filter_343_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 343AUTH';
			}
		if ( $filter_344_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 344AUTH';
			}
		if ( $filter_345_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 345AUTH';
			}
		if ( $filter_346_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 346AUTH';
			}
		if ( $filter_347_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 347AUTH';
			}
		if ( $filter_348_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 348AUTH';
			}
		if ( $filter_349_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 349AUTH';
			}
		if ( $filter_350_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 350AUTH';
			}
		if ( $filter_351_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 351AUTH';
			}
		if ( $filter_352_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 352AUTH';
			}
		if ( $filter_353_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 353AUTH';
			}
		if ( $filter_354_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 354AUTH';
			}
		if ( $filter_355_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 355AUTH';
			}
		if ( $filter_356_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 356AUTH';
			}
		if ( $filter_357_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 357AUTH';
			}
		if ( $filter_358_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 358AUTH';
			}
		if ( $filter_359_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 359AUTH';
			}
		if ( $filter_360_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 360AUTH';
			}
		if ( $filter_361_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 361AUTH';
			}
		if ( $filter_362_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 362AUTH';
			}
		if ( $filter_363_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 363AUTH';
			}
		if ( $filter_364_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 364AUTH';
			}
		if ( $filter_365_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 365AUTH';
			}
		if ( $filter_366_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 366AUTH';
			}
		if ( $filter_367_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 367AUTH';
			}
		if ( $filter_368_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 368AUTH';
			}
		if ( $filter_369_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 369AUTH';
			}
		if ( $filter_370_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 370AUTH';
			}
		if ( $filter_371_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 371AUTH';
			}
		if ( $filter_372_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 372AUTH';
			}
		if ( $filter_373_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 373AUTH';
			}
		if ( $filter_374_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 374AUTH';
			}
		if ( $filter_375_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 375AUTH';
			}
		if ( $filter_376_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 376AUTH';
			}
		if ( $filter_377_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 377AUTH';
			}
		if ( $filter_378_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 378AUTH';
			}
		if ( $filter_379_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 379AUTH';
			}
		if ( $filter_380_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 380AUTH';
			}
		if ( $filter_381_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 381AUTH';
			}
		if ( $filter_382_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 382AUTH';
			}
		if ( $filter_383_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 383AUTH';
			}
		if ( $filter_384_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 384AUTH';
			}
		if ( $filter_385_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 385AUTH';
			}
		if ( $filter_386_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 386AUTH';
			}
		if ( $filter_387_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 387AUTH';
			}
		if ( $filter_388_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 388AUTH';
			}
		if ( $filter_389_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 389AUTH';
			}
		if ( $filter_390_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 390AUTH';
			}
		if ( $filter_391_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 391AUTH';
			}
		if ( $filter_392_author_count >= 1 ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 392AUTH';
			}

	
		// Simple Author='' Tests - Non-Trackback/Non-Pingback
		if ( $commentdata_comment_author_lc == $filter_300400_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300400AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300401_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300401AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300402_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300402AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300403_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300403AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300404_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300404AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300405_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300405AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300406_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300406AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300407_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300407AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300408_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300408AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300409_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300409AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300410_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300410AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300411_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300411AUTH';
			}
		if ( $commentdata_comment_author_lc == $filter_300412_term ) {
			if ( !$content_filter_status ) { $content_filter_status = '1'; }
			$spamfree_error_code .= ' 300412AUTH';
			}
		}
	
	// Blacklist Word Combinations
	if ( $blacklist_word_combo >= $blacklist_word_combo_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' BLC1000';
		}
	if ( $blacklist_word_combo_total >= $blacklist_word_combo_total_limit ) {
		if ( !$content_filter_status ) { $content_filter_status = '1'; }
		$spamfree_error_code .= ' BLC1010';
		}


	// WP Blacklist Check :: BEGIN
	
	// Test WP Blacklist if option set
	
	// Before long make own blacklist function - WP's is flawed with IP's
	if ( $spamfree_options['enhanced_comment_blacklist'] && !$content_filter_status ) {
		if ( wp_blacklist_check($commentdata_comment_author, $commentdata_comment_author_email, $commentdata_comment_author_url, $commentdata_comment_content, $commentdata_remote_addr, $commentdata_user_agent) ) {
			if ( !$content_filter_status ) { $content_filter_status = '100'; }
			$spamfree_error_code .= ' WP-BLACKLIST';
			}
		}
	// WP Blacklist Check :: END
	
	if ( !$spamfree_error_code ) {
		$spamfree_error_code = 'No Error';
		}
	else {
		$spamfree_error_code = ltrim($spamfree_error_code);
		if ( $spamfree_options['comment_logging'] ) {
			spamfree_log_data( $commentdata, $spamfree_error_code );
			}
		}

	$spamfree_error_data = array( $spamfree_error_code, $blacklist_word_combo, $blacklist_word_combo_total );
	
	return $content_filter_status;
	// CONTENT FILTERING :: END
	}

function spamfree_stats() {
	global $wp_version;
	$BlogWPVersion = $wp_version;
	if ($BlogWPVersion < '2.5') {
		echo '<h3>WP-SpamFree</h3>';
		}
	$spamfree_count = get_option('spamfree_count');
	if ( !$spamfree_count ) {
		echo '<p>No comment spam attempts have been detected yet.</p>';
		}
	else {
		echo '<p>'.sprintf(__('<a href="%1$s" target="_blank">WP-SpamFree</a> has blocked <strong>%2$s</strong> spam comments.'), 'http://www.hybrid6.com/webgeek/plugins/wp-spamfree',  number_format($spamfree_count) ).'</p>';
		}
	}

function spamfree_filter_plugin_actions( $links, $file ){
	//Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	
	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=wp-spamfree/wp-spamfree.php">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
		}
	return $links;
	}

function spamfree_comment_add_referrer_js($post_id) {
	?>
	<script type='text/javascript'>
	<!--
	refJS = escape( document[ 'referrer' ] );
	document.write("<input type='hidden' name='refJS' value='"+refJS+"'>");
	// -->
	</script>
	<?php
	}

function spamfree_modify_notification( $text, $comment_id ) {

	$spamfree_options = get_option('spamfree_options');
	$wpsf_siteurl = get_option('siteurl');
	
	// IP / PROXY INFO :: BEGIN
	$ip = $_SERVER['REMOTE_ADDR'];
	$ipBlock=explode('.',$ip);
	$ipProxyVIA=$_SERVER['HTTP_VIA'];
	$MaskedIP=$_SERVER['HTTP_X_FORWARDED_FOR']; // Stated Original IP - Can be faked
	$MaskedIPBlock=explode('.',$MaskedIP);
	if (eregi("^([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])",$MaskedIP)&&$MaskedIP!=""&&$MaskedIP!="unknown"&&!eregi("^192.168.",$MaskedIP)) {
		$MaskedIPValid=true;
		$MaskedIPCore=rtrim($MaskedIP,' unknown;,');
		}
	if ( !$MaskedIP ) { $MaskedIP='[no data]'; }
	$ReverseDNS = gethostbyaddr($ip);
	$ReverseDNSIP = gethostbyname($ReverseDNS);
	
	if ( $ReverseDNSIP != $ip || $ip == $ReverseDNS ) {
		$ReverseDNSAuthenticity = '[Possibly Forged]';
		} 
	else {
		$ReverseDNSAuthenticity = '[Verified]';
		}
	// Detect Use of Proxy
	if ($_SERVER['HTTP_VIA']||$_SERVER['HTTP_X_FORWARDED_FOR']) {
		$ipProxy='PROXY DETECTED';
		$ipProxyShort='PROXY';
		$ipProxyData=$ip.' | MASKED IP: '.$MaskedIP;
		$ProxyStatus='TRUE';
		}
	else {
		$ipProxy='No Proxy';
		$ipProxyShort=$ipProxy;
		$ipProxyData=$ip;
		$ProxyStatus='FALSE';
		}
	// IP / PROXY INFO :: END

	$text .= "\r\nBlacklist the IP Address: ".$wpsf_siteurl.'/wp-admin/options-general.php?page=wp-spamfree/wp-spamfree.php&wpsf_action=blacklist_ip&comment_ip='.$ip;
	$text .= "\r\n";

	if ( !$spamfree_options['hide_extra_data'] ) {

		$text .= "\r\n----------------------------------------------------";
		$text .= "\r\n:: Additional Technical Data Added by WP-SpamFree ::";
		$text .= "\r\n----------------------------------------------------";
		$text .= "\r\n";
	
		if( $_POST[ 'refJS' ] && $_POST[ 'refJS' ] != '' ) {
			$refJS = addslashes( urldecode( $_POST[ 'refJS' ] ) );
			$refJS = str_replace( '%3A', ':', $refJS );
			$text .= "\r\nPage Referrer: $refJS\r\n";
			}
		$text .= "\r\nComment Processor Referrer: ".$_SERVER['HTTP_REFERER'];
		$text .= "\r\n";
		$text .= "\r\nUser-Agent: ".$_SERVER['HTTP_USER_AGENT'];
		$text .= "\r\n";
		$text .= "\r\nIP Address               : ".$ip;
		$text .= "\r\nRemote Host              : ".$_SERVER['REMOTE_HOST'];
		$text .= "\r\nReverse DNS              : ".$ReverseDNS;
		$text .= "\r\nReverse DNS IP           : ".$ReverseDNSIP;
		$text .= "\r\nReverse DNS Authenticity : ".$ReverseDNSAuthenticity;
		$text .= "\r\nProxy Info               : ".$ipProxy;
		$text .= "\r\nProxy Data               : ".$ipProxyData;
		$text .= "\r\nProxy Status             : ".$ProxyStatus;
		if ( $_SERVER['HTTP_VIA'] ) {
			$text .= "\r\nHTTP_VIA                 : ".$_SERVER['HTTP_VIA'];
			}
		if ( $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
			$text .= "\r\nHTTP_X_FORWARDED_FOR     : ".$_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		$text .= "\r\nHTTP_ACCEPT_LANGUAGE     : ".$_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$text .= "\r\n";
		$text .= "\r\nHTTP_ACCEPT: ".$_SERVER['HTTP_ACCEPT'];
		$text .= "\r\n";
		$text .= "\r\nIP Address Lookup: http://www.dnsstuff.com/tools/ipall/?ip=".$ip;
		$text .= "\r\n";
		$text .= "\r\n(This data is helpful if you need to submit a spam sample.)";
		$text .= "\r\n";
		}
	return $text;
	}
	
function spamfree_add_ip_to_blacklist($ip_to_blacklist) {
	$blacklist_keys = trim(stripslashes(get_option('blacklist_keys')));
	$blacklist_keys_update = $blacklist_keys."\n".$ip_to_blacklist;
	update_option('blacklist_keys', $blacklist_keys_update);
	}

if (!class_exists('wpSpamFree')) {
    class wpSpamFree {
	
		/**
		* @var string   The name the options are saved under in the database.
		*/
		var $adminOptionsName = 'wp_spamfree_options';
		
		/**
		* @var string   The name of the database table used by the plugin
		*/	
		var $db_table_name = 'wp_spamfree';
		
		
		/**
		* PHP 4 Compatible Constructor
		*/
		//function wpSpamFree(){$this->__construct();}

		/**
		* PHP 5 Constructor
		*/		
		//function __construct(){

		function wpSpamFree(){
			
			global $wpdb;
					
			error_reporting(0); // Prevents errors when page is accessed directly, outside WordPress
			
			register_activation_hook(__FILE__,array(&$this,'install_on_activation'));
			add_action('init', 'spamfree_init');
			add_action('init', 'widget_spamfree_register');
			add_action('admin_menu', array(&$this,'add_admin_pages'));
			add_action('wp_head', array(&$this, 'wp_head_intercept'));
			add_filter('the_content', 'spamfree_contact_form', 10);
			add_filter('the_content', 'spamfree_content_addendum', 999);
			add_action('comment_form', 'spamfree_comment_form',10);
			add_action('comment_form', 'spamfree_comment_add_referrer_js',1);
			add_action('preprocess_comment', 'spamfree_check_comment_type',1);
			add_filter('comment_notification_text', 'spamfree_modify_notification', 10, 2);
			add_filter('comment_moderation_text', 'spamfree_modify_notification', 10, 2);
			add_action('activity_box_end', 'spamfree_stats');
			add_filter('plugin_action_links', 'spamfree_filter_plugin_actions', 10, 2 );
        	}
		
		function add_admin_pages(){
			add_submenu_page("plugins.php","WP-SpamFree","WP-SpamFree",10, __FILE__, array(&$this,"output_existing_menu_sub_admin_page"));
			if ( current_user_can('level_8') ) {
				add_submenu_page("options-general.php","WP-SpamFree","WP-SpamFree",1, __FILE__, array(&$this,"output_existing_menu_sub_admin_page"));
				}
			}
		
		function output_existing_menu_sub_admin_page(){
			$wpSpamFreeVer=get_option('wp_spamfree_version');
			if ($wpSpamFreeVer!='') {
				$wpSpamFreeVerAdmin='Version '.$wpSpamFreeVer;
				}
			$spamCount=spamfree_count();
			$SiteURL = get_option('siteurl');
			?>
			<div class="wrap">
			<h2>WP-SpamFree</h2>
			
			<?php
			// Pre-2.6 compatibility
			if ( !defined('WP_CONTENT_URL') ) {
				define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
				}
			if ( !defined('WP_CONTENT_DIR') ) {
				define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
				}
			// Guess the location
			$wpsf_plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
			$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
					
			$installation_plugins_get_test_1		= 'wp-spamfree/wp-spamfree.php';
			$installation_file_test_0 				= $wpsf_plugin_path . '/wp-spamfree.php';
			if ( file_exists( ABSPATH . 'wp-load.php' ) ) {
				// WP 2.6
				$installation_file_test_1 			= ABSPATH . 'wp-load.php';
				$installation_file_test_1_status	= true;
				} 
			else {
				// Before 2.6
				$installation_file_test_1 			= ABSPATH . 'wp-config.php';
				if ( file_exists( $installation_file_test_1 ) ) {
					$installation_file_test_1_status= true;
					}
				}
			$installation_file_test_2 				= $wpsf_plugin_path . '/img/wpsf-img.php';
			$installation_file_test_3 				= $wpsf_plugin_path . '/js/wpsf-js.php';
			
			clearstatcache();
			$installation_file_test_2_perm = substr(sprintf('%o', fileperms($installation_file_test_2)), -4);
			$installation_file_test_3_perm = substr(sprintf('%o', fileperms($installation_file_test_3)), -4);
			if ( $installation_file_test_2_perm < '0755' || $installation_file_test_3_perm < '0755' || !is_readable($installation_file_test_2) || !is_executable($installation_file_test_2) || !is_readable($installation_file_test_3) || !is_executable($installation_file_test_3) ) {
				@chmod( $installation_file_test_2, 0755 );
				@chmod( $installation_file_test_3, 0755 );
				}
			clearstatcache();
			if ( $installation_plugins_get_test_1 == $_GET['page'] && file_exists($installation_file_test_0) && $installation_file_test_1_status && file_exists($installation_file_test_2) && file_exists($installation_file_test_3) ) {
			//if ( $installation_plugins_get_test_1 == $_GET['page'] && file_exists($installation_file_test_0) && $installation_file_test_1_status && file_exists($installation_file_test_2) && file_exists($installation_file_test_3) && $installation_file_test_2_perm == '0644' && $installation_file_test_3_perm == '0644' ) {
				$wp_installation_status = 1;
				$wp_installation_status_image = 'status-installed-correctly-24';
				$wp_installation_status_color = 'green';
				$wp_installation_status_bg_color = '#CCFFCC';
				$wp_installation_status_msg_main = 'Installed Correctly';
				$wp_installation_status_msg_text = strtolower($wp_installation_status_msg_main);
				}
			else {
				$wp_installation_status = 0;
				$wp_installation_status_image = 'status-not-installed-correctly-24';
				$wp_installation_status_color = 'red';
				$wp_installation_status_bg_color = '#FFCCCC';
				$wp_installation_status_msg_main = 'Not Installed Correctly';
				$wp_installation_status_msg_text = strtolower($wp_installation_status_msg_main);
				}

			if ( $_REQUEST['submit_wpsf_general_options'] ) {
				echo '<div class="updated fade"><p>Plugin Spam settings saved.</p></div>';
				}
			if ( $_REQUEST['submit_wpsf_contact_options'] ) {
				echo '<div class="updated fade"><p>Plugin Contact Form settings saved.</p></div>';
				}
			if ( $_REQUEST['wpsf_action'] == 'blacklist_ip' && $_REQUEST['comment_ip'] && !$_REQUEST['submit_wpsf_general_options'] && !$_REQUEST['submit_wpsf_contact_options'] ) {
				$ip_to_blacklist = trim(stripslashes($_REQUEST['comment_ip']));
				if (ereg("^([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])$",$ip_to_blacklist)) {
					$$ip_to_blacklist_valid='1';
					spamfree_add_ip_to_blacklist($ip_to_blacklist);
					echo '<div class="updated fade"><p>IP Address added to Comment Blacklist.</p></div>';
					}
				else {
					echo '<div class="updated fade"><p>Invalid IP Address - not added to Comment Blacklist.</p></div>';
					}
				}

			?>
			<div style='width:600px;border-style:solid;border-width:1px;border-color:<?php echo $wp_installation_status_color; ?>;background-color:<?php echo $wp_installation_status_bg_color; ?>;padding:0px 15px 0px 15px;margin-top:15px;'>
			<p><strong><?php echo "<img src='".$wpsf_plugin_url."/img/".$wp_installation_status_image.".png' alt='' width='24' height='24' style='border-style:none;vertical-align:middle;padding-right:7px;' /> Installation Status: <span style='color:".$wp_installation_status_color.";'>".$wp_installation_status_msg_main."</span>"; ?></strong></p>
			</div>

			
			<?php
			if ($spamCount) {
				echo "
				<br />
				<div style='width:600px;border-style:solid;border-width:1px;border-color:#000033;background-color:#CCCCFF;padding:0px 15px 0px 15px;'>
				<p><img src='".$wpsf_plugin_url."/img/spam-protection-24.png' alt='' width='24' height='24' style='border-style:none;vertical-align:middle;padding-right:7px;' /> WP-SpamFree has blocked <strong>".number_format($spamCount)."</strong> spam comments!</p></div>
				";
				}
			$spamfree_options = get_option('spamfree_options');
			if ($_REQUEST['submitted_wpsf_general_options']) {
				if ( $_REQUEST['comment_logging'] && !$spamfree_options['comment_logging_start_date'] ) {
					$CommentLoggingStartDate = time();
					spamfree_log_reset();
					}
				else if ( $_REQUEST['comment_logging'] && $spamfree_options['comment_logging_start_date'] ) {
					$CommentLoggingStartDate = $spamfree_options['comment_logging_start_date'];
					}				
				else {
					$CommentLoggingStartDate = 0;
					}
				$spamfree_options_update = array (
						'cookie_validation_name' 				=> $spamfree_options['cookie_validation_name'],
						'cookie_validation_key' 				=> $spamfree_options['cookie_validation_key'],
						'form_validation_field_js' 				=> $spamfree_options['form_validation_field_js'],
						'form_validation_key_js' 				=> $spamfree_options['form_validation_key_js'],
						'cookie_get_function_name' 				=> $spamfree_options['cookie_get_function_name'],
						'cookie_set_function_name' 				=> $spamfree_options['cookie_set_function_name'],
						'cookie_delete_function_name' 			=> $spamfree_options['cookie_delete_function_name'],
						'comment_validation_function_name' 		=> $spamfree_options['comment_validation_function_name'],
						'last_key_update'						=> $spamfree_options['last_key_update'],
						'wp_cache' 								=> $spamfree_options['wp_cache'],
						'wp_super_cache' 						=> $spamfree_options['wp_super_cache'],
						'block_all_trackbacks' 					=> $_REQUEST['block_all_trackbacks'],
						'block_all_pingbacks' 					=> $_REQUEST['block_all_pingbacks'],
						'use_alt_cookie_method' 				=> $_REQUEST['use_alt_cookie_method'],
						'use_alt_cookie_method_only' 			=> $_REQUEST['use_alt_cookie_method_only'],
						'use_captcha_backup' 					=> $spamfree_options['use_captcha_backup'],
						'use_trackback_verification' 			=> $spamfree_options['use_trackback_verification'],
						'comment_logging'						=> $_REQUEST['comment_logging'],
						'comment_logging_start_date'			=> $CommentLoggingStartDate,
						'comment_logging_all'					=> $_REQUEST['comment_logging_all'],
						'enhanced_comment_blacklist'			=> $_REQUEST['enhanced_comment_blacklist'],
						'allow_proxy_users'						=> $_REQUEST['allow_proxy_users'],
						'hide_extra_data'						=> $_REQUEST['hide_extra_data'],
						'form_include_website' 					=> $spamfree_options['form_include_website'],
						'form_require_website' 					=> $spamfree_options['form_require_website'],
						'form_include_phone' 					=> $spamfree_options['form_include_phone'],
						'form_require_phone' 					=> $spamfree_options['form_require_phone'],
						'form_include_company' 					=> $spamfree_options['form_include_company'],
						'form_require_company' 					=> $spamfree_options['form_require_company'],
						'form_include_drop_down_menu'			=> $spamfree_options['form_include_drop_down_menu'],
						'form_require_drop_down_menu'			=> $spamfree_options['form_require_drop_down_menu'],
						'form_drop_down_menu_title'				=> $spamfree_options['form_drop_down_menu_title'],
						'form_drop_down_menu_item_1'			=> $spamfree_options['form_drop_down_menu_item_1'],
						'form_drop_down_menu_item_2'			=> $spamfree_options['form_drop_down_menu_item_2'],
						'form_drop_down_menu_item_3'			=> $spamfree_options['form_drop_down_menu_item_3'],
						'form_drop_down_menu_item_4'			=> $spamfree_options['form_drop_down_menu_item_4'],
						'form_drop_down_menu_item_5'			=> $spamfree_options['form_drop_down_menu_item_5'],
						'form_drop_down_menu_item_6'			=> $spamfree_options['form_drop_down_menu_item_6'],
						'form_drop_down_menu_item_7'			=> $spamfree_options['form_drop_down_menu_item_7'],
						'form_drop_down_menu_item_8'			=> $spamfree_options['form_drop_down_menu_item_8'],
						'form_drop_down_menu_item_9'			=> $spamfree_options['form_drop_down_menu_item_9'],
						'form_drop_down_menu_item_10'			=> $spamfree_options['form_drop_down_menu_item_10'],
						'form_message_width' 					=> $spamfree_options['form_message_width'],
						'form_message_height' 					=> $spamfree_options['form_message_height'],
						'form_message_min_length' 				=> $spamfree_options['form_message_min_length'],
						'form_message_recipient' 				=> $spamfree_options['form_message_recipient'],
						'form_response_thank_you_message' 		=> $spamfree_options['form_response_thank_you_message'],
						'form_include_user_meta' 				=> $spamfree_options['form_include_user_meta'],
						'promote_plugin_link' 					=> $_REQUEST['promote_plugin_link'],
						);
				update_option('spamfree_options', $spamfree_options_update);
				//$blacklist_keys = trim(stripslashes(get_option('blacklist_keys')));
				$blacklist_keys_update = trim(stripslashes($_REQUEST['wordpress_comment_blacklist']));
				update_option('blacklist_keys', $blacklist_keys_update);
				}
			if ($_REQUEST['submitted_wpsf_contact_options']) {
				$spamfree_options_update = array (
						'cookie_validation_name' 				=> $spamfree_options['cookie_validation_name'],
						'cookie_validation_key' 				=> $spamfree_options['cookie_validation_key'],
						'form_validation_field_js' 				=> $spamfree_options['form_validation_field_js'],
						'form_validation_key_js' 				=> $spamfree_options['form_validation_key_js'],
						'cookie_get_function_name' 				=> $spamfree_options['cookie_get_function_name'],
						'cookie_set_function_name' 				=> $spamfree_options['cookie_set_function_name'],
						'cookie_delete_function_name' 			=> $spamfree_options['cookie_delete_function_name'],
						'comment_validation_function_name' 		=> $spamfree_options['comment_validation_function_name'],
						'last_key_update'						=> $spamfree_options['last_key_update'],
						'wp_cache' 								=> $spamfree_options['wp_cache'],
						'wp_super_cache' 						=> $spamfree_options['wp_super_cache'],
						'block_all_trackbacks' 					=> $spamfree_options['block_all_trackbacks'],
						'block_all_pingbacks' 					=> $spamfree_options['block_all_pingbacks'],
						'use_alt_cookie_method' 				=> $spamfree_options['use_alt_cookie_method'],
						'use_alt_cookie_method_only'			=> $spamfree_options['use_alt_cookie_method_only'],
						'use_captcha_backup' 					=> $spamfree_options['use_captcha_backup'],
						'use_trackback_verification' 			=> $spamfree_options['use_trackback_verification'],
						'comment_logging'						=> $spamfree_options['comment_logging'],
						'comment_logging_start_date'			=> $spamfree_options['comment_logging_start_date'],
						'comment_logging_all'					=> $spamfree_options['comment_logging_all'],
						'enhanced_comment_blacklist'			=> $spamfree_options['enhanced_comment_blacklist'],
						'allow_proxy_users'						=> $spamfree_options['allow_proxy_users'],
						'hide_extra_data'						=> $spamfree_options['hide_extra_data'],
						'form_include_website' 					=> $_REQUEST['form_include_website'],
						'form_require_website' 					=> $_REQUEST['form_require_website'],
						'form_include_phone' 					=> $_REQUEST['form_include_phone'],
						'form_require_phone' 					=> $_REQUEST['form_require_phone'],
						'form_include_company' 					=> $_REQUEST['form_include_company'],
						'form_require_company' 					=> $_REQUEST['form_require_company'],
						'form_include_drop_down_menu'			=> $_REQUEST['form_include_drop_down_menu'],
						'form_require_drop_down_menu'			=> $_REQUEST['form_require_drop_down_menu'],
						'form_drop_down_menu_title'				=> trim(stripslashes($_REQUEST['form_drop_down_menu_title'])),
						'form_drop_down_menu_item_1'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_1'])),
						'form_drop_down_menu_item_2'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_2'])),
						'form_drop_down_menu_item_3'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_3'])),
						'form_drop_down_menu_item_4'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_4'])),
						'form_drop_down_menu_item_5'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_5'])),
						'form_drop_down_menu_item_6'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_6'])),
						'form_drop_down_menu_item_7'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_7'])),
						'form_drop_down_menu_item_8'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_8'])),
						'form_drop_down_menu_item_9'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_9'])),
						'form_drop_down_menu_item_10'			=> trim(stripslashes($_REQUEST['form_drop_down_menu_item_10'])),
						'form_message_width' 					=> trim(stripslashes($_REQUEST['form_message_width'])),
						'form_message_height' 					=> trim(stripslashes($_REQUEST['form_message_height'])),
						'form_message_min_length' 				=> trim(stripslashes($_REQUEST['form_message_min_length'])),
						'form_message_recipient' 				=> trim(stripslashes($_REQUEST['form_message_recipient'])),
						'form_response_thank_you_message' 		=> trim(stripslashes($_REQUEST['form_response_thank_you_message'])),
						'form_include_user_meta' 				=> $_REQUEST['form_include_user_meta'],
						'promote_plugin_link' 					=> $spamfree_options['promote_plugin_link'],
						);
				update_option('spamfree_options', $spamfree_options_update);
				}
			$spamfree_options = get_option('spamfree_options');
			?>
			
			<div style="width:305px;height:250px;border-style:none;border-width:0px;border-color:#000000;padding:0px 15px 0px 15px;margin-top:15px;margin-right:15px;float:left;clear:left;">
			
			<p><a name="wpsf_top"><strong>Quick Navigation - Contents</strong></a></p>
			
			<ol style="list-style-type:decimal;padding-left:30px;">
				<li><a href="#wpsf_general_options">General Options</a></li>
				<li><a href="#wpsf_contact_form_options">Contact Form Options</a></li>
				<li><a href="#wpsf_installation_instructions">Installation Instructions</a></li>
				<li><a href="#wpsf_displaying_stats">Displaying Spam Stats on Your Blog</a></li>
				<li><a href="#wpsf_adding_contact_form">Adding a Contact Form to Your Blog</a></li>
				<li><a href="#wpsf_configuration">Configuration Information</a></li>
				<li><a href="#wpsf_known_conflicts">Known Plugin Conflicts</a></li>
				<li><a href="#wpsf_troubleshooting">Troubleshooting Guide / Support</a></li>
				<li><a href="#wpsf_let_others_know">Let Others Know About WP-SpamFree</a></li>
				<li><a href="#wpsf_download_plugin_documentation">Download Plugin / Documentation</a></li>
			</ol>
			</div>
			
			<div style="width:250px;height:250px;border-style:solid;border-width:1px;border-color:#000033;background-color:#CCCCFF;padding:0px 15px 0px 15px;margin-top:15px;margin-right:15px;float:left;">
			
			<p>
			<?php if ( $spamCount > 100 ) { ?>
			<strong>Happy with WP-SpamFree?</strong><br /> Let others know by <a href="http://wordpress.org/extend/plugins/wp-spamfree/" target="_blank" rel="external" >giving it a good rating</a> on WordPress.org!<br />
			<img src='<?php echo $wpsf_plugin_url; ?>/img/5-stars-rating.gif' alt='' width='99' height='19' style='border-style:none;padding-top:3px;padding-bottom:0px;' /><br /><br />
			<?php } ?>
			
			<strong>Documentation:</strong> <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" target="_blank" rel="external" >Plugin Homepage</a><br />
			<strong>Tech Support:</strong> <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/support" target="_blank" rel="external" >WP-SpamFree Support</a><br />
			<strong>Follow on Twitter:</strong> <a href="http://twitter.com/WPSpamFree" target="_blank" rel="external" >@WPSpamFree</a><br />			
			<strong>Let Others Know:</strong> <a href="http://www.hybrid6.com/webgeek/2007/11/wp-spamfree-1-wordpress-plugin-released.php#comments" target="_blank" rel="external" >Leave Comments</a><br />

			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="margin-top:10px;">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHmAYJKoZIhvcNAQcEoIIHiTCCB4UCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAkl3h7+WDTBC76t4rXWjOtAk0ZEn5ZvuELCP6NunlUQIZMaLtWdgHjzz3++oLFXai+EpiP8fN6O/3xhJPuUipcxbHLOZU9yjGfqtLGR9y5L55+6fOnr1Jwvu2AkFSqHuSf4RUtSqyl4hjIU7bQRgNVirytHmViBFOdENwoX7ev1TELMAkGBSsOAwIaBQAwggEUBgkqhkiG9w0BBwEwFAYIKoZIhvcNAwcECELKNLOKeLaQgIHwDGBKAvnywBVbZFjkI99LQxH84PBi+gK8Jde5qjYUVX0MAE7F7s1o9gZJlpNE/djbIntuY5qRn1FaqEUYwIL/DWt2dSzBz+0zRb6b6pHe7ZjY5cNmOGFQjjY46/qKem2dNQ9eWiVvQuWWFGwbgGfhqxuXrE1VzNMtVVa3T1KeuCdvioObTeF68K0f2oIF+bWqEi8wqStrU4prhdyrcG5EWzwxzbtBE/Bn6tujJWlRy9b9fO4HCSjxRymKjE3pzXbNU8Tq70M2rRWwzcwGcgSA31GYPkU1C18K3MZ28EIJh2VRIUK9i382PPhRHn8e7et2oIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMDgwMTIxMDcxNzE4WjAjBgkqhkiG9w0BCQQxFgQUFJe3LShiMspPH9IZH3CcqbEz4VYwDQYJKoZIhvcNAQEBBQAEgYBgg1FPRZ/fyNWSriz9Pji9rFgP0rF6F1UN8h8nCDRJNhfVrQmSZWmslRU13FthP9Tdcx2mqtovNGJP2xuTcPkzmepWiwd49AoeQ2/Sv2NmH7HWW7mVGpQlpebYYu11uoR369nDGW8LGRww4oGsjx91+SsO/jxUflowrczYym086g==-----END PKCS7-----">
			</form>	
			</p>
			
			</div>


			<p style="clear:both;">&nbsp;</p>
			
			<p><a name="wpsf_general_options"><strong>General Options</strong></a></p>

			<form name="wpsf_general_options" method="post">
			<input type="hidden" name="submitted_wpsf_general_options" value="1" />

			<fieldset class="options">
				<ul style="list-style-type:none;padding-left:30px;">
					<li>
					<label for="use_alt_cookie_method">
						<input type="checkbox" id="use_alt_cookie_method" name="use_alt_cookie_method" <?php echo ($spamfree_options['use_alt_cookie_method']==true?"checked=\"checked\"":"") ?> />
						<strong>M2 - Use two methods to set cookies.</strong><br />This adds a secondary non-JavaScript method to set cookies in addition to the standard JS method.<br />&nbsp;
					</label>
					</li>
					<?php if ( $_REQUEST['showHiddenOptions']=='on' ) { // Still Testing ?>
					<li>
					<label for="use_alt_cookie_method_only">
						<input type="checkbox" id="use_alt_cookie_method_only" name="use_alt_cookie_method_only" <?php echo ($spamfree_options['use_alt_cookie_method_only']==true?"checked=\"checked\"":"") ?> />
						<strong style="color:red;">Use non-JavaScript method to set cookies. **STILL IN TESTING**</strong><br />This will ONLY use the non-JavaScript method to set cookies, INSTEAD of the standard JS method.<br />&nbsp;
					</label>
					</li>
					<?php } ?>
										
					<li>
					<label for="comment_logging">
						<input type="checkbox" id="comment_logging" name="comment_logging" <?php echo ($spamfree_options['comment_logging']==true?"checked=\"checked\"":"") ?> />
						<strong>Blocked Comment Logging Mode</strong><br />Temporary diagnostic mode that logs blocked comment submissions for 7 days, then turns off automatically.<br />Log is cleared each time this feature is turned on.<br /><em>May use slightly higher server resources, so for best performance, only use when necessary. (Most websites won't notice any difference.)</em>
					</label>
					<?php
					if ( $spamfree_options['comment_logging'] ) {			
						$wpsf_log_filename = 'temp-comments-log.txt';
						$wpsf_log_empty_filename = 'temp-comments-log.init.txt';
						$wpsf_htaccess_filename = '.htaccess';
						$wpsf_htaccess_orig_filename = 'htaccess.txt';
						$wpsf_htaccess_empty_filename = 'htaccess.init.txt';
						$wpsf_log_dir = $wpsf_plugin_path.'/data';
						$wpsf_log_file = $wpsf_log_dir.'/'.$wpsf_log_filename;
						$wpsf_log_empty_file = $wpsf_log_dir.'/'.$wpsf_log_empty_filename;
						$wpsf_htaccess_file = $wpsf_log_dir.'/'.$wpsf_htaccess_filename;
						$wpsf_htaccess_orig_file = $wpsf_log_dir.'/'.$wpsf_htaccess_orig_filename;
						$wpsf_htaccess_empty_file = $wpsf_log_dir.'/'.$wpsf_htaccess_empty_filename;
						
						clearstatcache();
						if ( !file_exists( $wpsf_htaccess_file ) ) {
							@chmod( $wpsf_log_dir, 0775 );
							@chmod( $wpsf_htaccess_orig_file, 0666 );
							@chmod( $wpsf_htaccess_empty_file, 0666 );
							@rename( $wpsf_htaccess_orig_file, $wpsf_htaccess_file );
							@copy( $wpsf_htaccess_empty_file, $wpsf_htaccess_orig_file );
							}

						clearstatcache();
						$wpsf_perm_log_dir = substr(sprintf('%o', fileperms($wpsf_log_dir)), -4);
						$wpsf_perm_log_file = substr(sprintf('%o', fileperms($wpsf_log_file)), -4);
						$wpsf_perm_log_empty_file = substr(sprintf('%o', fileperms($wpsf_log_empty_file)), -4);
						$wpsf_perm_htaccess_file = substr(sprintf('%o', fileperms($wpsf_htaccess_file)), -4);
						$wpsf_perm_htaccess_empty_file = substr(sprintf('%o', fileperms($wpsf_htaccess_empty_file)), -4);
						if ( $wpsf_perm_log_dir < '0775' || !is_writable($wpsf_log_dir) || $wpsf_perm_log_file < '0666' || !is_writable($wpsf_log_file) || $wpsf_perm_log_empty_file < '0666' || !is_writable($wpsf_log_empty_file) || $wpsf_perm_htaccess_file < '0666' || !is_writable($wpsf_htaccess_file) || $wpsf_perm_htaccess_empty_file < '0666' || !is_writable($wpsf_htaccess_empty_file) ) {
							@chmod( $wpsf_log_dir, 0775 );
							@chmod( $wpsf_log_file, 0666 );
							@chmod( $wpsf_log_empty_file, 0666 );
							@chmod( $wpsf_htaccess_file, 0666 );
							@chmod( $wpsf_htaccess_empty_file, 0666 );
							}
						clearstatcache();
						$wpsf_perm_log_dir = substr(sprintf('%o', fileperms($wpsf_log_dir)), -4);
						$wpsf_perm_log_file = substr(sprintf('%o', fileperms($wpsf_log_file)), -4);
						$wpsf_perm_log_empty_file = substr(sprintf('%o', fileperms($wpsf_log_empty_file)), -4);
						$wpsf_perm_htaccess_file = substr(sprintf('%o', fileperms($wpsf_htaccess_file)), -4);
						$wpsf_perm_htaccess_empty_file = substr(sprintf('%o', fileperms($wpsf_htaccess_empty_file)), -4);
						if ( $wpsf_perm_log_dir < '0755' || !is_writable($wpsf_log_dir) || $wpsf_perm_log_file < '0644' || !is_writable($wpsf_log_file) || $wpsf_perm_log_empty_file < '0644' || !is_writable($wpsf_log_empty_file) || ( file_exists( $wpsf_htaccess_file ) && ( $wpsf_perm_htaccess_file < '0644' || !is_writable($wpsf_htaccess_file) ) ) || $wpsf_perm_htaccess_empty_file < '0644' || !is_writable($wpsf_htaccess_empty_file) ) {
							echo '<br/>'."\n".'<span style="color:red;"><strong>The log file may not be writeable. You may need to manually correct the file permissions.<br/>Set the  permission for the "/wp-spamfree/data" directory to 755 and all files within to 644.</strong><br/>If that doesn\'t work then you may want to read the <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree#wpsf_faqs_5" target="_blank">FAQ</a> for this topic.</span><br/>'."\n";
							}
						}
					?>
					<br /><strong>Download <a href="<?php echo $wpsf_plugin_url; ?>/data/temp-comments-log.txt" target="_blank">Comment Log File</a> - Right-click, and select "Save Link As"</strong><br />&nbsp;
					</li>
					<li>
					<label for="comment_logging_all">
						<input type="checkbox" id="comment_logging_all" name="comment_logging_all" <?php echo ($spamfree_options['comment_logging_all']==true?"checked=\"checked\"":"") ?> />
						<strong>Log All Comments</strong><br />Requires that Blocked Comment Logging Mode be engaged. Instead of only logging blocked comments, this will allow the log to capture <em>all</em> comments while logging mode is turned on. This provides more technical data for comment submissions than WordPress provides, and helps us improve the plugin.<br/>If you plan on submitting spam samples to us for analysis, it's helpful for you to turn this on, otherwise it's not necessary.</label>
					<br/>For more about this, see <a href="#wpsf_configuration_log_all_comments">below</a>.<br />&nbsp;
					
					</li>
					<li>
					<label for="enhanced_comment_blacklist">
						<input type="checkbox" id="enhanced_comment_blacklist" name="enhanced_comment_blacklist" <?php echo ($spamfree_options['enhanced_comment_blacklist']==true?"checked=\"checked\"":"") ?> />
						<strong>Enhanced Comment Blacklist</strong><br />Enhances WordPress's Comment Blacklist - instead of just sending comments to moderation, they will be completely blocked. Also adds a link in the comment notification emails that will let you blacklist a commenter's IP with one click.<br/>(Useful if you receive repetitive human spam or harassing comments from a particular commenter.)<br/>&nbsp;</label>					
					</li>
					<label for="wordpress_comment_blacklist">
						<?php 
						$WordPressCommentBlacklist = trim(get_option('blacklist_keys'));
						?>
						<strong>Your current WordPress Comment Blacklist</strong><br/>When a comment contains any of these words in its content, name, URL, e-mail, or IP, it will be completely blocked, not just marked as spam. One word or IP per line. It is not case-sensitive and will match included words, so "press" on your blacklist will block "WordPress" in a comment.<br />
						<textarea id="wordpress_comment_blacklist" name="wordpress_comment_blacklist" cols="80" rows="8" /><?php echo $WordPressCommentBlacklist; ?></textarea><br/>
					</label>
					You can either update this list here or on the <a href="<?php echo $SiteURL; ?>/wp-admin/options-discussion.php">WordPress Discussion Settings page</a>.<br/>&nbsp;
					<li>
					<label for="block_all_trackbacks">
						<input type="checkbox" id="block_all_trackbacks" name="block_all_trackbacks" <?php echo ($spamfree_options['block_all_trackbacks']==true?"checked=\"checked\"":"") ?> />
						<strong>Disable trackbacks.</strong><br />Use if trackback spam is excessive. (Not recommended)<br />&nbsp;
					</label>
					</li>
					<li>
					<label for="block_all_pingbacks">
						<input type="checkbox" id="block_all_pingbacks" name="block_all_pingbacks" <?php echo ($spamfree_options['block_all_pingbacks']==true?"checked=\"checked\"":"") ?> />
						<strong>Disable pingbacks.</strong><br />Use if pingback spam is excessive. Disadvantage is reduction of communication between blogs. (Not recommended)<br />&nbsp;
					</label>
					</li>
					<li>
					<label for="allow_proxy_users">
						<input type="checkbox" id="allow_proxy_users" name="allow_proxy_users" <?php echo ($spamfree_options['allow_proxy_users']==true?"checked=\"checked\"":"") ?> />
						<strong>Allow users behind proxy servers to comment?</strong><br />Most users should leave this unchecked. Many human spammers hide behind proxies.<br/>&nbsp;</label>					
					</li>
					<li>
					<label for="hide_extra_data">
						<input type="checkbox" id="hide_extra_data" name="hide_extra_data" <?php echo ($spamfree_options['hide_extra_data']==true?"checked=\"checked\"":"") ?> />
						<strong>Hide extra technical data in comment notifications.</strong><br />This data is helpful if you need to submit a spam sample. If you dislike seeing the extra info, you can use this option.<br/>&nbsp;</label>					
					</li>
					<li>
					<label for="promote_plugin_link">
						<input type="checkbox" id="promote_plugin_link" name="promote_plugin_link" <?php echo ($spamfree_options['promote_plugin_link']==true?"checked=\"checked\"":"") ?> />
						<strong>Help promote WP-SpamFree?</strong><br />This places a small link under the comments and contact form, letting others know what's blocking spam on your blog.<br />&nbsp;
					</label>
					</li>
				</ul>
			</fieldset>
			<p class="submit">
			<input type="submit" name="submit_wpsf_general_options" value="Update Options &raquo;" class="button-primary" style="float:left;" />
			</p>
			</form>

			<p>&nbsp;</p>

			<p>&nbsp;</p>
			
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>
			
			<p><a name="wpsf_contact_form_options"><strong>Contact Form Options</strong></a></p>

			<form name="wpsf_contact_options" method="post">
			<input type="hidden" name="submitted_wpsf_contact_options" value="1" />

			<fieldset class="options">
				<ul style="list-style-type:none;padding-left:30px;">
					<li>
					<label for="form_include_website">
						<input type="checkbox" id="form_include_website" name="form_include_website" <?php echo ($spamfree_options['form_include_website']==true?"checked=\"checked\"":"") ?> />
						<strong>Include "Website" field.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_require_website">
						<input type="checkbox" id="form_require_website" name="form_require_website" <?php echo ($spamfree_options['form_require_website']==true?"checked=\"checked\"":"") ?> />
						<strong>Require "Website" field.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_include_phone">
						<input type="checkbox" id="form_include_phone" name="form_include_phone" <?php echo ($spamfree_options['form_include_phone']==true?"checked=\"checked\"":"") ?> />
						<strong>Include "Phone" field.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_require_phone">
						<input type="checkbox" id="form_require_phone" name="form_require_phone" <?php echo ($spamfree_options['form_require_phone']==true?"checked=\"checked\"":"") ?> />
						<strong>Require "Phone" field.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_include_company">
						<input type="checkbox" id="form_include_company" name="form_include_company" <?php echo ($spamfree_options['form_include_company']==true?"checked=\"checked\"":"") ?> />
						<strong>Include "Company" field.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_require_company">
						<input type="checkbox" id="form_require_company" name="form_require_company" <?php echo ($spamfree_options['form_require_company']==true?"checked=\"checked\"":"") ?> />
						<strong>Require "Company" field.</strong><br />&nbsp;
					</label>
					</li>					<li>
					<label for="form_include_drop_down_menu">
						<input type="checkbox" id="form_include_drop_down_menu" name="form_include_drop_down_menu" <?php echo ($spamfree_options['form_include_drop_down_menu']==true?"checked=\"checked\"":"") ?> />
						<strong>Include drop-down menu select field.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_require_drop_down_menu">
						<input type="checkbox" id="form_require_drop_down_menu" name="form_require_drop_down_menu" <?php echo ($spamfree_options['form_require_drop_down_menu']==true?"checked=\"checked\"":"") ?> />
						<strong>Require drop-down menu select field.</strong><br />&nbsp;
					</label>
					</li>					
					<li>
					<label for="form_drop_down_menu_title">
						<?php $FormDropDownMenuTitle = trim(stripslashes($spamfree_options['form_drop_down_menu_title'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_title" name="form_drop_down_menu_title" value="<?php if ( $FormDropDownMenuTitle ) { echo $FormDropDownMenuTitle; } else { echo '';} ?>" />
						<strong>Title of drop-down select menu. (Menu won't be shown if empty.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_1">
						<?php $FormDropDownMenuItem1 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_1'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_1" name="form_drop_down_menu_item_1" value="<?php if ( $FormDropDownMenuItem1 ) { echo $FormDropDownMenuItem1; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 1. (Menu won't be shown if empty.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_2">
						<?php $FormDropDownMenuItem2 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_2'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_2" name="form_drop_down_menu_item_2" value="<?php if ( $FormDropDownMenuItem2 ) { echo $FormDropDownMenuItem2; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 2. (Menu won't be shown if empty.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_3">
						<?php $FormDropDownMenuItem3 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_3'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_3" name="form_drop_down_menu_item_3" value="<?php if ( $FormDropDownMenuItem3 ) { echo $FormDropDownMenuItem3; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 3. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_4">
						<?php $FormDropDownMenuItem4 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_4'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_4" name="form_drop_down_menu_item_4" value="<?php if ( $FormDropDownMenuItem4 ) { echo $FormDropDownMenuItem4; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 4. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_5">
						<?php $FormDropDownMenuItem5 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_5'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_5" name="form_drop_down_menu_item_5" value="<?php if ( $FormDropDownMenuItem5 ) { echo $FormDropDownMenuItem5; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 5. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_6">
						<?php $FormDropDownMenuItem6 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_6'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_6" name="form_drop_down_menu_item_6" value="<?php if ( $FormDropDownMenuItem6 ) { echo $FormDropDownMenuItem6; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 6. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_7">
						<?php $FormDropDownMenuItem7 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_7'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_7" name="form_drop_down_menu_item_7" value="<?php if ( $FormDropDownMenuItem7 ) { echo $FormDropDownMenuItem7; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 7. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_8">
						<?php $FormDropDownMenuItem8 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_8'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_8" name="form_drop_down_menu_item_8" value="<?php if ( $FormDropDownMenuItem8 ) { echo $FormDropDownMenuItem8; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 8. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_9">
						<?php $FormDropDownMenuItem9 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_9'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_9" name="form_drop_down_menu_item_9" value="<?php if ( $FormDropDownMenuItem9 ) { echo $FormDropDownMenuItem9; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 9. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_drop_down_menu_item_10">
						<?php $FormDropDownMenuItem10 = trim(stripslashes($spamfree_options['form_drop_down_menu_item_10'])); ?>
						<input type="text" size="40" id="form_drop_down_menu_item_10" name="form_drop_down_menu_item_10" value="<?php if ( $FormDropDownMenuItem10 ) { echo $FormDropDownMenuItem10; } else { echo '';} ?>" />
						<strong>Drop-down select menu item 10. (Leave blank if not using.)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_message_width">
						<?php $FormMessageWidth = trim(stripslashes($spamfree_options['form_message_width'])); ?>
						<input type="text" size="4" id="form_message_width" name="form_message_width" value="<?php if ( $FormMessageWidth && $FormMessageWidth >= 40 ) { echo $FormMessageWidth; } else { echo '40';} ?>" />
						<strong>"Message" field width. (Minimum 40)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_message_height">
						<?php $FormMessageHeight = trim(stripslashes($spamfree_options['form_message_height'])); ?>
						<input type="text" size="4" id="form_message_height" name="form_message_height" value="<?php if ( $FormMessageHeight && $FormMessageHeight >= 5 ) { echo $FormMessageHeight; } else if ( !$FormMessageHeight ) { echo '10'; } else { echo '5';} ?>" />
						<strong>"Message" field height. (Minimum 5, Default 10)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_message_min_length">
						<?php $FormMessageMinLength = trim(stripslashes($spamfree_options['form_message_min_length'])); ?>
						<input type="text" size="4" id="form_message_min_length" name="form_message_min_length" value="<?php if ( $FormMessageMinLength && $FormMessageMinLength >= 15 ) { echo $FormMessageMinLength; } else if ( !$FormMessageWidth ) { echo '25'; } else { echo '15';} ?>" />
						<strong>Minimum message length (# of characters). (Minimum 15, Default 25)</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_message_recipient">
						<?php $FormMessageRecipient = trim(stripslashes($spamfree_options['form_message_recipient'])); ?>
						<input type="text" size="40" id="form_message_recipient" name="form_message_recipient" value="<?php if ( !$FormMessageRecipient ) { echo get_option('admin_email'); } else { echo $FormMessageRecipient; } ?>" />
						<strong>Optional: Enter alternate form recipient. Default is blog admin email.</strong><br />&nbsp;
					</label>
					</li>
					<li>
					<label for="form_response_thank_you_message">
						<?php 
						$FormResponseThankYouMessage = trim(stripslashes($spamfree_options['form_response_thank_you_message']));
						?>
						<strong>Enter message to be displayed upon successful contact form submission.</strong><br/>Can be plain text, HTML, or an ad, etc.<br />
						<textarea id="form_response_thank_you_message" name="form_response_thank_you_message" cols="80" rows="3" /><?php if ( !$FormResponseThankYouMessage ) { echo 'Your message was sent successfully. Thank you.'; } else { echo $FormResponseThankYouMessage; } ?></textarea><br/>&nbsp;
					</label>
					</li>
					<li>
					<label for="form_include_user_meta">
						<input type="checkbox" id="form_include_user_meta" name="form_include_user_meta" <?php echo ($spamfree_options['form_include_user_meta']==true?"checked=\"checked\"":"") ?> />
						<strong>Include user technical data in email.</strong><br />This adds some extra technical data to the end of the contact form email about the person submitting the form.<br />It includes: <strong>Browser / User Agent</strong>, <strong>Referrer</strong>, <strong>IP Address</strong>, <strong>Server</strong>, etc.<br />This is helpful for dealing with abusive or threatening comments. You can use the IP address provided to identify or block trolls from your site with whatever method you prefer.<br />&nbsp;
					</label>
					</li>					

				</ul>
			</fieldset>
			<p class="submit">
			<input type="submit" name="submit_wpsf_contact_options" value="Update Options &raquo;" class="button-primary" style="float:left;" />
			</p>
			</form>
			
			<p>&nbsp;</p>
			
			<p>&nbsp;</p>
			
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>
			
			<p><a name="wpsf_installation_instructions"><strong>Installation Instructions</strong></a></p>

			<ol style="list-style-type:decimal;padding-left:30px;">
			    <li>After downloading, unzip file and upload the enclosed 'wp-spamfree' directory to your WordPress plugins directory: '/wp-content/plugins/'.<br />&nbsp;</li>
				<li>As always, <strong>activate</strong> the plugin on your WordPress plugins page.<br />&nbsp;</li>
				<li>Check to make sure the plugin is installed properly. Many support requests for this plugin originate from improper installation and can be easily prevented. To check proper installation status, go to the WP-SpamFree page in your Admin. It's a submenu link on the Plugins page. Go the the 'Installation Status' area near the top and it will tell you if the plugin is installed correctly. If it tells you that the plugin is not installed correctly, please double-check what directory you have installed WP-SpamFree in, delete any WP-SpamFree files you have uploaded to your server, re-read the Installation Instructions, and start the Installation process over from step 1. If it is installed correctly, then move on to the next step.<br />&nbsp;<br /><strong>Currently your plugin is: <?php echo "<span style='color:".$wp_installation_status_color.";'>".$wp_installation_status_msg_main."</span>"; ?></strong><br />&nbsp;</li>
				<li>Select desired configuration options. Due to popular request, I've added the option to block trackbacks and pingbacks if the user feels they are excessive. I'd recommend not doing this, but the choice is yours.<br />&nbsp;</li>
				<li>If you are using front-end anti-spam plugins (CAPTCHA's, challenge questions, etc), be sure they are disabled since there's no longer a need for them, and these could likely conflict. (Back-end anti-spam plugins like Akismet are fine, although unnecessary.)</li>
			</ol>	
			<p>&nbsp;</p>
			<p>You're done! Sit back and see what it feels like to blog without comment spam!</p>
					
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>
			
			<p><a name="wpsf_displaying_stats"><strong>Displaying Spam Stats on Your Blog</strong></a></p>

			<p>Want to show off your spam stats on your blog and tell others about WP-SpamFree? Simply add the following code to your WordPress theme where you'd like the stats displayed: <br />&nbsp;<br /><code>&lt;?php if ( function_exists(spamfree_counter) ) { spamfree_counter(1); } ?&gt;</code><br />&nbsp;<br /> where '1' is the style. Replace the '1' with a number from 1-9 that corresponds to one of the following sample styles you'd like to use. To simply display text stats on your site (no graphic), replace the '1' with '0'.</code></p>

<p>
<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-1-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-2-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-3-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-4-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-5-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-6-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-7-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-8-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-lg-bg-9-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 170px; height: 136px" width="170" height="136" />
</p>

<p>To add stats to individual posts, you'll need to install the <a href="http://wordpress.org/extend/plugins/exec-php/" rel="external" target="_blank" >Exec-PHP</a> plugin.</p>
						
			<p><strong>Small Counter</strong><br /><br />To add smaller counter to your site, add the following code to your WordPress theme where you'd like the stats displayed: <br />&nbsp;<br /><code>&lt;?php if ( function_exists(spamfree_counter_sm) ) { spamfree_counter_sm(1); } ?&gt;</code><br />&nbsp;<br /> where '1' is the style. Replace the '1' with a number from 1-5 that corresponds to one of the following.</p>

<p>
<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-sm-bg-1-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 150px; height: 90px" width="150" height="90" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-sm-bg-2-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 150px; height: 90px" width="150" height="90" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-sm-bg-3-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 150px; height: 90px" width="150" height="90" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-sm-bg-4-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 150px; height: 90px" width="150" height="90" />

<img src='<?php echo $wpsf_plugin_url; ?>/counter/spamfree-counter-sm-bg-5-preview.png' style="border-style:none; margin-right: 10px; margin-top: 7px; margin-bottom: 7px; width: 150px; height: 90px" width="150" height="90" />
</p>

<p>Or, you can simply use the widget. It displays stats in the style of small counter #1. Now you can show spam stats on your blog without knowing any code.</p>	
				
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>
			
			<p><a name="wpsf_adding_contact_form"><strong>Adding a Contact Form to Your Blog</strong></a></p>

			<p>First create a page (not post) where you want to have your contact form. Then, insert the following tag (using the HTML editing tab, NOT the Visual editor) and you're done: <code>&lt;!--spamfree-contact--&gt;</code><br />&nbsp;<br />
			
			There is no need to configure the form. It allows you to simply drop it into the page you want to install it on. However, there are a few basic configuration options. You can choose whether or not to include Phone and Website fields, whether they should be required, add a drop down menu with up to 10 options, set the width and height of the Message box, set the minimum message length, set the form recipient, enter a custom message to be displayed upon successful contact form submission, and choose whether or not to include user technical data in the email.<br />&nbsp;<br />
			
			If you want to modify the style of the form using CSS, all the form elements have an ID attribute you can reference in your stylesheet.<br />&nbsp;<br />

			<strong>What the Contact Form feature IS:</strong> A simple drop-in contact form that won't get spammed.<br />
			<strong>What the Contact Form feature is NOT:</strong> A configurable and full-featured plugin like some other contact form plugins out there.<br />
			<strong>Note:</strong> Please do not request new features for the contact form, as the main focus of the plugin is spam protection. Thank you.</p>
			
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>
			
			<p><a name="wpsf_configuration"><strong>Configuration Information</strong></a></p>
			
			<p><a name="wpsf_configuration_spam_options"><strong>Spam Options</strong></a>
			
			<p><a name="wpsf_configuration_m2"><strong>M2 - Use two methods to set cookies.</strong></a><br />This adds a secondary non-JavaScript method to set cookies in addition to the standard JS method.</p>

			<p><a name="wpsf_configuration_blocked_comment_logging_mode"><strong>Blocked Comment Logging Mode</strong></a><br />This is a temporary diagnostic mode that logs blocked comment submissions for 7 days, then turns off automatically. If you want to see what spam has been blocked on your site, this is the option to use. Also, if you experience any technical issues, this will help with diagnosis, as you can email this log file to support if necessary. If you suspect you are having a technical issue, please turn this on right away and start logging data. Then submit a <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/support" target="_blank">support request</a>, and we'll email you back asking to see the log file so we can help you fix whatever the issue may be. The log is cleared each time this feature is turned on, so make sure you download the file before turning it back on. Also the log is capped at 2MB for security. <em>This feature may use slightly higher server resources, so for best performance, only use when necessary. (Most websites won't notice any difference.)</em> </p>

			<p><a name="wpsf_configuration_log_all_comments"><strong>Log All Comments</strong></a><br />Requires that Blocked Comment Logging Mode be engaged. Instead of only logging blocked comments, this will allow the log to capture <em>all</em> comments while logging mode is turned on. This provides more technical data for comment submissions than WordPress provides, and helps us improve the plugin. If you plan on submitting spam samples to us for analysis, it's helpful for you to turn this on, otherwise it's not necessary. If you have any spam comments that you feel WP-SpamFree should have blocked (usually human spam), then please submit a <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/support" target="_blank">support request</a>. When we email you back we will ask you to forward the data to us by email.</p>
			
			<p>This extra data will be extremely valuable in helping us improve the spam protection capabilites of the plugin.</p>
			
			<p><a name="wpsf_configuration_enhanced_comment_blacklist"><strong>Enhanced Comment Blacklist</strong></a><br />Enhances WordPress's Comment Blacklist - instead of just sending comments to moderation, they will be completely blocked if this is enabled. (Useful if you receive repetitive human spam or harassing comments from a particular commenter.) Also adds <strong>one-click blacklisting</strong> - a link will now appear in the comment notification emails that you can click to blacklist a commenter's IP. This link appears whether or not the feature is enabled. If you click the link and this feature is diabled, it will add the commenter's IP to the blacklist but blacklisting will operate according to WordPress's default functionality.</p>
			
			<p>The WP-SpamFree blacklist shares the WordPress Comment Blacklist data, but the difference is that now when a comment contains any of these words in its content, name, URL, e-mail, or IP, it will be completely blocked, not just marked as spam. One word or IP per line...add each new blacklist item on a new line. If you're not sure how to use it, start by just adding an IP address, or click on the link in one of the notification emails. It is not case-sensitive and will match included words, so "press" on your blacklist will block "WordPress" in a comment.</p>			

			<p><a name="wpsf_configuration_disable_trackbacks"><strong>Disable trackbacks.</strong></a><br />Use if trackback spam is excessive. It is recomended that you don't use this option unless you are experiencing an extreme spam attack.</p>

			<p><a name="wpsf_configuration_disable_pingbacks"><strong>Disable pingbacks.</strong></a><br />Use if pingback spam is excessive. The disadvantage is a reduction of communication between blogs. When blogs ping each other, it's like saying "Hi, I just wrote about you" and disabling these pingbacks eliminates that ability. It is recomended that you don't use this option unless you are experiencing an extreme spam attack.</p>

			<p><a name="wpsf_configuration_allow_proxy_users"><strong>Allow users behind proxy servers to comment?</strong></a><br />Most users should leave this unchecked. Many human spammers hide behind proxies. Leaving this unckecked adds an extra layer of spam protection. In the rare even that a non-spam commenter gets blocked by this, they will be notified what the situation is, and instructed to contact you to ask you to modify this setting.</p>
			
			<p><a name="wpsf_configuration_hide_extra_data"><strong>Hide extra technical data in comment notifications.</strong></a><br />The plugin now addes some extra technical data to the comment moderation and notification emails, including the referrer that brought the user to the page where they commented, the referrer that brought them to the WordPress comments processing page (helps with fighting spam), User-Agent, Remote Host, Reverse DNS, Proxy Info, Browser Language, and more. This data is helpful if you ever need to <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/support" target="_blank">submit a spam sample</a>. If you dislike seeing the extra info, you can use this option to prevent the info from being displayed in the emails. If you don't mind seeing it, please leave it this unchecked, because if you ever need to submit a spam sample, it helps us track spam patterns.</p>
			
			<p><a name="wpsf_configuration_help_promote_plugin"><strong>Help promote WP-SpamFree?</strong></a><br />This places a small link under the comments and contact form, letting others know what's blocking spam on your blog. This plugin is provided for free, so this is much appreciated. It's a small way you can give back and let others know about WP-SpamFree.</p>
			
			<p><a name="wpsf_configuration_contact_form_options"><strong>Contact Form Options</strong></a><br />
			These are self-explanatory.</p>
					
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>	

			<p><a name="wpsf_known_conflicts"><strong>Known Plugin Conflicts</strong></a></p>
			
			<p>For the most up-to-date info, view the <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree#wpsf_known_conflicts" target="_blank" >Known Plugin Conflicts</a> list.</p>
			
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>	

			<p><a name="wpsf_troubleshooting"><strong>Troubleshooting Guide / Support</strong></a></p>
			<p>If you're having trouble getting things to work after installing the plugin, here are a few things to check:</p>
			<ol style="list-style-type:decimal;padding-left:30px;">
				<li>Check the <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree#wpsf_faqs" target="_blank">FAQ's</a>.<br />&nbsp;</li>
				<li>If you haven't yet, please upgrade to the latest version.<br />&nbsp;</li>
				<li>Check to make sure the plugin is installed properly. Many support requests for this plugin originate from improper installation and can be easily prevented. To check proper installation status, go to the WP-SpamFree page in your Admin. It's a submenu link on the Plugins page. Go the the 'Installation Status' area near the top and it will tell you if the plugin is installed correctly. If it tells you that the plugin is not installed correctly, please double-check what directory you have installed WP-SpamFree in, delete any WP-SpamFree files you have uploaded to your server, re-read the Installation Instructions, and start the Installation process over from step 1.<br />&nbsp;<br /><strong>Currently your plugin is: <?php echo "<span style='color:".$wp_installation_status_color.";'>".$wp_installation_status_msg_main."</span>"; ?></strong><br />&nbsp;</li>
				<li>Clear your browser's cache, clear your cookies, and restart your browser. Then reload the page.<br />&nbsp;</li>
				<li>If you are receiving the error message: "Sorry, there was an error. Please enable JavaScript and Cookies in your browser and try again." then you need to make sure <em>JavaScript</em> and <em>cookies</em> are enabled in your browser. (JavaScript is different from Java. Java is not required.) These are enabled by default in web browsers. The status display will let you know if these are turned on or off (as best the page can detect - occasionally the detection does not work.) If this message comes up consistently even after JavaScript and cookies are enabled, then there most likely is an installation problem, plugin conflict, or JavaScript conflict. Read on for possible solutions.<br />&nbsp;</li>
				<li>If you have multiple domains that resolve to the same server, or are parked on the same hosting account, make sure the domain set in the WordPress configuration options matches the domain where you are accessing the blog from. In other words, if you have people going to your blog using http://www.yourdomain.com/ and the WordPress configuration has: http://www.yourdomain2.com/ you will have a problem (not just with this plugin, but with a lot of things.)<br />&nbsp;</li>
				<li>Check your WordPress Version. If you are using a release earlier than 2.3, you may want to upgrade for a whole slew of reasons, including features and security.<br />&nbsp;</li>
				<li>Check the options you have selected to make sure they are not disabling a feature you want to use.<br />&nbsp;</li>
				<li>Make sure that you are not using other front-end anti-spam plugins (CAPTCHA's, challenge questions, etc) since there's no longer a need for them, and these could likely conflict. (Back-end anti-spam plugins like Akismet are fine, although unnecessary.)<br />&nbsp;</li>
				<li>Visit http://www.yourblog.com/wp-content/plugins/wp-spamfree/js/wpsf-js.php (where <em>yourblog.com</em> is your blog url) and check two things. <br />&nbsp;<br /><strong>First, see if the file comes normally or if it comes up blank or with errors.</strong> That would indicate a problem. Submit a support request (see last troubleshooting step) and copy and past any error messages on the page into your message. <br />&nbsp;<br /><strong>Second, check for a 403 Forbidden error.</strong> That means there is a problem with your file permissions. If the files in the wp-spamfree folder don't have standard permissions (at least 644 or higher) they won't work. This usually only happens by manual modification, but strange things do happen. <strong>The <em>AskApache Password Protect Plugin</em> is known to cause this error.</strong> Users have reported that using its feature to protect the /wp-content/ directory creates an .htaccess file in that directory that creates improper permissions and conflicts with WP-SpamFree (and most likely other plugins as well). You'll need to disable this feature, or disable the <em>AskApache Password Protect Plugin</em> and delete any .htaccess files it has created in your /wp-content/ directory before using WP-SpamFree.<br />&nbsp;</li>
        <li>Check for conflicts with other JavaScripts installed on your site. This usually occurs with with JavaScripts unrelated to WordPress or plugins. However some themes contain JavaScripts that aren't compatible. (And some don't have the call to the <code>wp_head()</code> function which is also a problem. Read on to see how to test/fix this issue.) If in doubt, try switching themes. If that fixes it, then you know the theme was at fault. If you discover a conflicting theme, please let us know.<br />&nbsp;</li>
        <li>Check for conflicts with other WordPress plugins installed on your blog. Although errors don't occur often, this is one of the most common causes of the errors that do occur. I can't guarantee how well-written other plugins will be. First, see the <a href="#wpsf_known_conflicts">Known Plugin Conflicts</a> list. If you've disabled any plugins on that list and still have a problem, then proceed. <br />&nbsp;<br />To start testing for conflicts, temporarily deactivate all other plugins except WP-SpamFree. Then check to see if WP-SpamFree works by itself. (For best results make sure you are logged out and clear your cookies. Alternatively you can use another browser for testing.) If WP-SpamFree allows you to post a comment with no errors, then you know there is a plugin conflict. The next step is to activate each plugin, one at a time, log out, and try to post a comment. Then log in, deactivate that plugin, and repeat with the next plugin. (If possible, use a second browser to make it easier. Then you don't have to keep logging in and out with the first browser.) Be sure to clear cookies between attempts (before loading the page you want to comment on). If you do identify a plugin that conflicts, please let me know so I can work on bridging the compatibility issues.<br />&nbsp;</li>
		<li>Make sure the theme you are using has the call to <code>wp_head()</code> (which most properly coded themes do) usually found in the <code>header.php</code> file. It will be located somewhere before the <code>&lt;/head&gt;</code> tag. If not, you can insert it before the <code>&lt;/head&gt;</code> tag and save the file. If you've never edited a theme before, proceed at your own risk: <br />&nbsp;
			<ol style="list-style-type:decimal;padding-left:30px;">
				<li>In the WordPress admin, go to <em>Themes (Appearance) - Theme Editor</em><br />&nbsp;</li>
				<li>Click on Header (or <code>header.php</code>)<br />&nbsp;</li>
				<li>Locate the line with <code>&lt;/head&gt;</code> and insert <code>&lt;?php wp_head(); ?&gt;</code> before it.<br />&nbsp;</li>
				<li>Click 'Save'<br/>&nbsp;</li>
			</ol>
		</li>
        <li>On the WP-SpamFree Options page in the WordPress Admin, under <a href="#wpsf_general_options">General Options</a>, check the option "M2 - Use two methods to set cookies." and see if this helps.<br />&nbsp;</li>
		<li>If have checked all of these, and still can't quite get it working, please submit a support request at the <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/support" target="_blank" rel="external" >WP-SpamFree Support Page</a>.</li>
	</ol>
			
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>
			
  			<p><a name="wpsf_let_others_know"><strong>Let Others Know About WP-SpamFree</strong></a></p>
	
			<p><strong>How does it feel to blog without being bombarded by automated comment spam?</strong> If you're happy with WP-SpamFree, there's a few things you can do to let others know:</p>
			
			<ul style="list-style-type:disc;padding-left:30px;">
				<li><a href="http://www.hybrid6.com/webgeek/2007/11/wp-spamfree-1-wordpress-plugin-released.php#comments" target="_blank" >Post a comment.</a></li>
				<li><a href="http://wordpress.org/extend/plugins/wp-spamfree/" target="_blank" >Give WP-SpamFree a good rating</a> on WordPress.org.</li>
				<li><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/end-blog-spam" target="_blank" >Place a graphic link</a>  on your site letting others know how they can help end blog spam. ( &lt/BLOGSPAM&gt; )</li>
			</ul>
			
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>			
			
			<p><a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" target="_blank" rel="external" style="border-style:none;text-decoration:none;" ><img src="<?php echo $wpsf_plugin_url; ?>/img/end-blog-spam-button-01-black.png" alt="End Blog Spam! WP-SpamFree Comment Spam Protection for WordPress" width="140" height="66" style="border-style:none;text-decoration:none;" /></a></p>
			
			<p><a name="wpsf_download_plugin_documentation"><strong>Download Plugin / Documentation</strong></a><br />
			Latest Version: <a href="http://www.hybrid6.com/webgeek/downloads/wp-spamfree.zip" target="_blank" rel="external" >Download Now</a><br />
			Plugin Homepage / Documentation: <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree" target="_blank" rel="external" >WP-SpamFree</a><br />
			Leave Comments: <a href="http://www.hybrid6.com/webgeek/2007/11/wp-spamfree-1-wordpress-plugin-released.php" target="_blank" rel="external" >WP-SpamFree Release Announcement Blog Post</a><br />
			WordPress.org Page: <a href="http://wordpress.org/extend/plugins/wp-spamfree/" target="_blank" rel="external" >WP-SpamFree</a><br />
			Tech Support/Questions: <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/support" target="_blank" rel="external" >WP-SpamFree Support Page</a><br />
			End Blog Spam: <a href="http://www.hybrid6.com/webgeek/plugins/wp-spamfree/end-blog-spam" target="_blank" rel="external" >Let Others Know About WP-SpamFree!</a><br />
			Twitter: <a href="http://twitter.com/WPSpamFree" target="_blank" rel="external" >@WPSpamFree</a><br />

			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="margin-top:10px;">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHmAYJKoZIhvcNAQcEoIIHiTCCB4UCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAkl3h7+WDTBC76t4rXWjOtAk0ZEn5ZvuELCP6NunlUQIZMaLtWdgHjzz3++oLFXai+EpiP8fN6O/3xhJPuUipcxbHLOZU9yjGfqtLGR9y5L55+6fOnr1Jwvu2AkFSqHuSf4RUtSqyl4hjIU7bQRgNVirytHmViBFOdENwoX7ev1TELMAkGBSsOAwIaBQAwggEUBgkqhkiG9w0BBwEwFAYIKoZIhvcNAwcECELKNLOKeLaQgIHwDGBKAvnywBVbZFjkI99LQxH84PBi+gK8Jde5qjYUVX0MAE7F7s1o9gZJlpNE/djbIntuY5qRn1FaqEUYwIL/DWt2dSzBz+0zRb6b6pHe7ZjY5cNmOGFQjjY46/qKem2dNQ9eWiVvQuWWFGwbgGfhqxuXrE1VzNMtVVa3T1KeuCdvioObTeF68K0f2oIF+bWqEi8wqStrU4prhdyrcG5EWzwxzbtBE/Bn6tujJWlRy9b9fO4HCSjxRymKjE3pzXbNU8Tq70M2rRWwzcwGcgSA31GYPkU1C18K3MZ28EIJh2VRIUK9i382PPhRHn8e7et2oIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMDgwMTIxMDcxNzE4WjAjBgkqhkiG9w0BCQQxFgQUFJe3LShiMspPH9IZH3CcqbEz4VYwDQYJKoZIhvcNAQEBBQAEgYBgg1FPRZ/fyNWSriz9Pji9rFgP0rF6F1UN8h8nCDRJNhfVrQmSZWmslRU13FthP9Tdcx2mqtovNGJP2xuTcPkzmepWiwd49AoeQ2/Sv2NmH7HWW7mVGpQlpebYYu11uoR369nDGW8LGRww4oGsjx91+SsO/jxUflowrczYym086g==-----END PKCS7-----">
			</form>	
			</p>

			<p>&nbsp;</p>

			<p><em><?php echo $wpSpamFreeVerAdmin; ?></em></p>
	
			<p><div style="float:right;font-size:12px;">[ <a href="#wpsf_top">BACK TO TOP</a> ]</div></p>

			<p>&nbsp;</p>

			<p>&nbsp;</p>
			</div>
			<?php
			}

		function wp_head_intercept(){
			if (!is_admin()) {

				if ( !defined('WP_CONTENT_URL') ) {
					define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
					}
				$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
	
	
				$spamfree_options = get_option('spamfree_options');
				$wpSpamFreeVer=get_option('wp_spamfree_version');
				if ($wpSpamFreeVer!='') {
					$wpSpamFreeVerJS=' v'.$wpSpamFreeVer;
					}
				echo "\n";
				if ( $spamfree_options['use_alt_cookie_method_only'] ) {
					echo '<!-- Protected by WP-SpamFree'.$wpSpamFreeVerJS.' :: M2 -->'."\n";
					}
				else {
					echo '<!-- Protected by WP-SpamFree'.$wpSpamFreeVerJS.' :: JS BEGIN -->'."\n";
					echo '<script type="text/javascript" src="'.$wpsf_plugin_url.'/js/wpsf-js.php"></script> '."\n";
					echo '<!-- Protected by WP-SpamFree'.$wpSpamFreeVerJS.' :: JS END -->'."\n";
					}				
				echo "\n";
				
				}
			}
		
		function install_on_activation() {
			global $wpdb;
			$plugin_db_version = "2.1.0.7";
			$installed_ver = get_option('wp_spamfree_version');
			$spamfree_options = get_option('spamfree_options');
			//only run installation if not installed or if previous version installed
			if ( ( $installed_ver === false || $installed_ver != $plugin_db_version ) && !$spamfree_options ) {
			
				//add a database version number for future upgrade purposes
				update_option('wp_spamfree_version', $plugin_db_version);
				
				// Set Random Cookie Name
				$randomComValCodeCVN1 = spamfree_create_random_key();
				$randomComValCodeCVN2 = spamfree_create_random_key();
				$CookieValidationName = strtoupper($randomComValCodeCVN1.$randomComValCodeCVN2);
				// Set Random Cookie Value
				$randomComValCodeCKV1 = spamfree_create_random_key();
				$randomComValCodeCKV2 = spamfree_create_random_key();
				$CookieValidationKey = $randomComValCodeCKV1.$randomComValCodeCKV2;
				// Set Random Form Field Name
				$randomComValCodeJSFFN1 = spamfree_create_random_key();
				$randomComValCodeJSFFN2 = spamfree_create_random_key();
				$FormValidationFieldJS = $randomComValCodeJSFFN1.$randomComValCodeJSFFN2;
				// Set Random Form Field Value
				$randomComValCodeJS1 = spamfree_create_random_key();
				$randomComValCodeJS2 = spamfree_create_random_key();
				$FormValidationKeyJS = $randomComValCodeJS1.$randomComValCodeJS2;
				// TIME
				$KeyUpdateTime = time();

				// Options array
				$spamfree_options_update = array (
					'cookie_validation_name' 				=> $CookieValidationName,
					'cookie_validation_key' 				=> $CookieValidationKey,
					'form_validation_field_js' 				=> $FormValidationFieldJS,
					'form_validation_key_js' 				=> $FormValidationKeyJS,
					'cookie_get_function_name' 				=> '',
					'cookie_set_function_name' 				=> '',
					'cookie_delete_function_name' 			=> '',
					'comment_validation_function_name' 		=> '',
					'last_key_update'						=> $KeyUpdateTime,
					'wp_cache' 								=> 0,
					'wp_super_cache' 						=> 0,
					'block_all_trackbacks' 					=> 0,
					'block_all_pingbacks' 					=> 0,
					'use_alt_cookie_method'					=> 1,
					'use_alt_cookie_method_only'			=> 0,
					'use_captcha_backup' 					=> 0,
					'use_trackback_verification'		 	=> 0,
					'comment_logging'						=> 0,
					'comment_logging_start_date'			=> 0,
					'comment_logging_all'					=> 0,
					'enhanced_comment_blacklist'			=> 0,
					'allow_proxy_users'						=> 1,
					'hide_extra_data'						=> 0,
					'form_include_website' 					=> 1,
					'form_require_website' 					=> 0,
					'form_include_phone' 					=> 1,
					'form_require_phone' 					=> 0,
					'form_include_company' 					=> 0,
					'form_require_company' 					=> 0,
					'form_include_drop_down_menu'			=> 0,
					'form_require_drop_down_menu'			=> 0,
					'form_drop_down_menu_title'				=> '',
					'form_drop_down_menu_item_1'			=> '',
					'form_drop_down_menu_item_2'			=> '',
					'form_drop_down_menu_item_3'			=> '',
					'form_drop_down_menu_item_4'			=> '',
					'form_drop_down_menu_item_5'			=> '',
					'form_drop_down_menu_item_6'			=> '',
					'form_drop_down_menu_item_7'			=> '',
					'form_drop_down_menu_item_8'			=> '',
					'form_drop_down_menu_item_9'			=> '',
					'form_drop_down_menu_item_10'			=> '',
					'form_message_width' 					=> 40,
					'form_message_height' 					=> 10,
					'form_message_min_length'				=> 25,
					'form_message_recipient'				=> get_option('admin_email'),
					'form_response_thank_you_message'		=> 'Your message was sent successfully. Thank you.',
					'form_include_user_meta'				=> 1,
					'promote_plugin_link'					=> 1,
					);
					
				$spamfree_count = get_option('spamfree_count');
				if (!$spamfree_count) {
					update_option('spamfree_count', 0);
					}
				update_option('spamfree_options', $spamfree_options_update);
				update_option('ak_count_pre', get_option('akismet_spam_count'));
				// Turn on Comment Moderation
				//update_option('comment_moderation', 1);
				//update_option('moderation_notify', 1);
				
				// Ensure Correct Permissions of IMG and JS file :: BEGIN
				
				// Pre-2.6 compatibility
				if ( !defined('WP_CONTENT_URL') ) {
					define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
					}
				if ( !defined('WP_CONTENT_DIR') ) {
					define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
					}
				// Guess the location
				$wpsf_plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
				$wpsf_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			
				$installation_file_test_2 				= $wpsf_plugin_path . '/img/wpsf-img.php';
				$installation_file_test_3 				= $wpsf_plugin_path . '/js/wpsf-js.php';
				
				clearstatcache();
				$installation_file_test_2_perm = substr(sprintf('%o', fileperms($installation_file_test_2)), -4);
				$installation_file_test_3_perm = substr(sprintf('%o', fileperms($installation_file_test_3)), -4);
				if ( $installation_file_test_2_perm < '0755' || $installation_file_test_3_perm < '0755' || !is_readable($installation_file_test_2) || !is_executable($installation_file_test_2) || !is_readable($installation_file_test_3) || !is_executable($installation_file_test_3) ) {
					@chmod( $installation_file_test_2, 0755 );
					@chmod( $installation_file_test_3, 0755 );
					}
					
				// Ensure Correct Permissions of IMG and JS file :: BEGIN

				}
			}
					
		}
	}

//instantiate the class
if (class_exists('wpSpamFree')) {
	$wpSpamFree = new wpSpamFree();
	}

?>