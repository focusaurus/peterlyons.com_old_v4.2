<?php
/*
  Plugin Name: Defensio Anti-Spam
  Plugin URI: http://defensio.com/
  Description: Defensio is an advanced spam filtering web service that learns and adapts to your behaviors as well to those of your readers and commenters.  To use this plugin, you need to obtain a <a href="http://defensio.com/signup">free API Key</a>.  Tell the world how many spam Defensio caught!  Just put <code>&lt;?php defensio_counter(); ?></code> in your template.
  Version: 2.0.3
  Author: Karabunga, Inc
  Author URI: http://karabunga.com/
*/

include_once('lib/spyc.php');
include_once('lib/defensio_utils.php');
include_once('lib/defensio_configuration.php');
include_once('lib/defensio_quarantine.php');
include_once('lib/defensio_head.php');
include_once('lib/defensio_counter.php');
include_once('lib/defensio_moderation.php');


$defensio_conf = array(
	'server'       => 'api.defensio.com',
	'path'         => 'blog',
	'api-version'  => '1.2',
	'format'       => 'yaml',
	'blog'         => get_option('home'),
	'post_timeout' => 10
);

/* If you want to hard code the key for some reason, uncomment the following line and replace 1234567890 with your key. */
// $defensio_conf['key'] = '1234567890'; 

/* Define trusted roles here.  Only change these if you have custom roles (and you know what you're doing). */
$defensio_trusted_roles = array('administrator', 'editor', 'author');

/* acts_as_master forces the Defensio spam result to be retained in the event other anti-spam plugins are installed.
   Setting it to 'false' could have drastic negative effects on accuracy, so please leave it to true unless you 
   know what you are doing. In other words, set it to 'false' at your own risk. */ 
$acts_as_master = true;

/*-----------------------------------------------------------------------------------------------------------------------
  DO NOT EDIT PAST THIS
-----------------------------------------------------------------------------------------------------------------------*/
define('DF_SUCCESS', 'success');
define('DF_FAIL', 'fail');

if (!function_exists('wp_nonce_field') ) {
	function defensio_nonce_field($action = -1) { return; }
	$defensio_conf['nonce'] = -1;
} else {
	function defensio_nonce_field($action = -1) { return wp_nonce_field($action); }
	$defensio_conf['nonce'] = 'defensio-update-key';  
}

// Temporary stores Defensio's metadata, spaminess and signature
$defensio_meta = array();

// Initialize arrays for deferred training
$deferred_spam_to_ham = array();  
$deferred_ham_to_spam = array();
$defensio_retraining  = false;

// Installation function, creates defensio table
function defensio_install() {
	// Create table and set default options
	defensio_create_table();
	add_option(defensio_user_unique_option_key('threshold') , '80');
	add_option(defensio_user_unique_option_key('hide_more_than_threshold'), '1');
	add_option('defensio_delete_older_than_days', '30');
	add_option('defensio_delete_older_than', '0');
}
register_activation_hook(basename(__FILE__), 'defensio_install');


function defensio_create_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . "defensio";
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (comment_ID mediumint(9) NOT NULL, spaminess DECIMAL(5,4) NOT NULL, signature VARCHAR(55) NOT NULL, UNIQUE KEY comment_ID (comment_ID));";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
		$wpdb->query($sql);
	}
}

// Init hook
function defensio_init() {
	global $defensio_conf, $defensio_unprocessed_count;
	add_action('admin_menu', 'defensio_config_page');

	if (isset ($defensio_conf['key'])) {
		$defensio_conf['hckey'] = true;
	} else {
		$defensio_conf['key'] = trim(get_option('defensio_key'));
	}
	

	// In case the table is deleted create it again
	defensio_create_table(); 

	// Are there any unproccessed comments?
	$defensio_unprocessed_count = defensio_get_unprocessed_comments();
	if (count($defensio_unprocessed_count) > 0) {
		add_action('admin_print_scripts', 'defensio_admin_head');
		add_action('admin_notices', 'defensio_unprocessed_warning');
	}

	// Enqueue styles
	if (defensio_wp_version() < 2.6) {
		// for older versions
		add_action('admin_head', create_function('$a', 'echo "<link media=\"all\" type=\"text/css\" href=\"'. get_option('siteurl')  .'/wp-content/plugins/defensio-anti-spam/styles/defensio.css\" rel=\"stylesheet\"> </link>" ;'));
	}
}
add_action('init', 'defensio_init');

function defensio_styles() {
	if(defensio_wp_version() >= 2.7) {
		wp_enqueue_style('defensio', '/wp-content/plugins/defensio-anti-spam/styles/defensio_2.7.css' );
	} else {
		wp_enqueue_style('defensio', '/wp-content/plugins/defensio-anti-spam/styles/defensio.css' );
	}
}
add_action('admin_print_styles', 'defensio_styles');

function defensio_key_not_set_warning() {
	global $defensio_conf;
	
	if (!isset($defensio_conf['key']) or empty($defensio_conf['key'])) {
		// No need in 2.7
		if(defensio_wp_version() < 2.7)
			defensio_render_warning_styles();
		echo "<div id='defensio_warning' class='updated fade'>" .
		"<p><strong>Defensio is not active</strong> because you have not entered your Defensio API key.  <a href='http://defensio.com/signup' target='_blank'>Get one right here!</a></p></div>";
	}
	return; 
}
add_action('admin_notices', 'defensio_key_not_set_warning');

function defensio_unprocessed_warning() {
	global $defensio_unprocessed_count;
	defensio_render_unprocessed_in_moderation($defensio_unprocessed_count);
}

function defensio_collect_signatures($s) {
	$signatures = '';	   
	$i = 0;
	foreach($s as $signature){
		if ($i < count($s) -1 ) { 
			$signatures .= $signature. ','; 
		}
		else { 
			$signatures .= $signature; 
		}
		$i++;
	}
	return $signatures;
}

// Shutdown hook
// Train comments scheduled to be trained, in only one request to defensio server.
function defensio_finalize() {
	global $deferred_ham_to_spam, $deferred_spam_to_ham ;

	if (!empty($deferred_ham_to_spam)) {
		defensio_submit_spam(defensio_collect_signatures($deferred_ham_to_spam));
	}

	if (!empty($deferred_spam_to_ham)) {
		$signatures = '';
		defensio_submit_ham(defensio_collect_signatures($deferred_spam_to_ham));
	}
}
add_action('shutdown', 'defensio_finalize');


function defensio_config_page() {
	global $defensio_conf;

	if (function_exists('add_submenu_page')) {
		add_submenu_page('plugins.php', __('Defensio Configuration'), __('Defensio Configuration'), 'manage_options', 'defensio-config', 'defensio_configuration');
		add_submenu_page('options-general.php', __('Defensio Configuration'), __('Defensio'), 'manage_options', 'defensio-config', 'defensio_configuration');
	}
}

function defensio_configuration() {
	global $defensio_conf;

	$key = NULL;
	$err_code = NULL;
	
	if (isset($_POST['new_key'])) {
		check_admin_referer( $defensio_conf['nonce']);
		$key = trim($_POST['new_key']);
		$key = defensio_sql_escape($key);
		$defensio_conf['key'] = $key;
	}

	if (isset($defensio_conf['key'])) {
		if (defensio_verify_key($defensio_conf['key'], $err_code)) {
			$valid = true;
			update_option('defensio_key', $defensio_conf['key']);
		} else {
			$valid = false;
		}

		$key = $defensio_conf['key'];
	}

	if (isset($_POST['new_threshold'])) {
		$t = (int)$_POST['new_threshold'];

		if (0 <= $t and $t <= 100) {
			update_option(defensio_user_unique_option_key('threshold'), $t );
		}
	} 

	if (!$defensio_conf['hckey']) {
		$defensio_conf['hckey'] = false;
	}

	$older_than_error = '';
	$minimum_days = 15;

	if (isset($_POST['defensio_remove_older_than_toggle'])) {
		if (isset($_POST['defensio_remove_older_than']) and (isset($_POST['defensio_remove_older_than_days']) and ((int) $_POST['defensio_remove_older_than_days'] > $minimum_days))) {
			update_option('defensio_delete_older_than', '1');
			update_option('defensio_delete_older_than_days', (int) $_POST['defensio_remove_older_than_days']);
		} else {
			update_option('defensio_delete_older_than', '0');

			if (isset($_POST['defensio_remove_older_than_days']) and ((int)$_POST['defensio_remove_older_than_days'] < $minimum_days)) {
				$older_than_error = 'Days has to be a numeric value greater than '.$minimum_days;

			} elseif (isset($_POST['defensio_remove_older_than_days']) and ((int) $_POST['defensio_remove_older_than_days'] > $minimum_days)) {
				update_option('defensio_delete_older_than_days', (int) $_POST['defensio_remove_older_than_days']);
			}
		}

	} else {
		if ((isset($_POST['defensio_remove_older_than_days']) and ((int) $_POST['defensio_remove_older_than_days'] > $minimum_days) )) {
			update_option('defensio_delete_older_than_days', (int) $_POST['defensio_remove_older_than_days']);
		} elseif($_POST['defensio_remove_older_than_days'] > $minimum_days ){
			$older_than_error = 'Days has to be a numeric value greater than '.$minimum_days;
		}
	}


	$threshold = get_option(defensio_user_unique_option_key('threshold'));

	if(empty($threshold))
		$threshold  = 80;

	defensio_render_configuration_html(array(
		'key'				=> $key, 
		'hckey'				=> $defensio_conf['hckey'], 
		'threshold'			=> $threshold,
		'nonce'				=> $defensio_conf['nonce'],
		'valid'				=> $valid,
		'remove_older_than'		=> get_option('defensio_delete_older_than'),
		'remove_older_than_days' 	=> get_option('defensio_delete_older_than_days'),
		'remove_older_than_error' 	=> $older_than_error,
		'defensio_post_error_code' 	=> $err_code
	));
}

function defensio_generate_spaminess_filter($reverse = false, $ignore_option = false) {
	$spaminess_filter = '';

	$option_name = defensio_user_unique_option_key('hide_more_than_threshold');

	if (get_option($option_name) == '1' or $ignore_option) {
		$t = (int)get_option(defensio_user_unique_option_key('threshold'));
		$t = (float)($t) / 100.0;

		/* if the Defensio table was created using an old version of the plugin, the 
		   spaminess field was created as a float which is not precise.  for example,
		   a spaminess of 80% was being stored as 0.80000001, which caused the following
		   filters to not work properly sometimes.  this is simply a little dirty workaround
		   for this problem.  new users have their spaminess properly stored as numeric.
		   this hack will, not affect them, however. */
		$t = $t - 0.001;
		
		// MySQL does not like "," as decimal separator using sprintf to avoid that in 
		// some locales.
		if ($reverse) {
			$spaminess_filter = " AND IFNULL(spaminess, 1) >= ". sprintf('%F', $t);
		} else {
			$spaminess_filter = " AND IFNULL(spaminess, 1) < " . sprintf('%F', $t);
		}
	}

	return $spaminess_filter;
}


function defensio_update_db($opts = null){
	global $wpdb, $defensio_conf, $defensio_retraining;

	if($opts == null or !is_array($opts))
		return false;

	if (function_exists('current_user_can') && !current_user_can('moderate_comments')) {
		die(__('You do not have sufficient permission to moderate comments.'));
	}

	// Single message to restore
	if(isset ($opts['ham'])) {
		$id = (int) $opts['ham'];
		defensio_set_status_approved($id);
	}

	// Many messages to process
	if (isset ($opts['defensio_comments'])) {
		// Restore
		if (isset ($opts['defensio_restore'])) {
			foreach ($opts['defensio_comments'] as $id ) {
				$id = (int)$id;
				defensio_set_status_approved($id);
			}
		}

		// Delete
		if (isset ($opts['defensio_delete'])) {
			foreach ($opts['defensio_comments'] as $k ) {
				$k = (int) $k;
				$wpdb->query("DELETE from $wpdb->prefix" . "defensio WHERE comment_ID = $k");
				$wpdb->query("DELETE from $wpdb->comments WHERE comment_ID = $k");
			}
		}
	}

	// Empty spam box, delete all 
	if (isset($opts['defensio_empty_quarantine'])) {
		defensio_empty_quarantine();
	}

}

// Prepare messages to be displayed in the quarantine
function defensio_caught( $opts = null ) {
	global $wpdb, $defensio_conf, $defensio_retraining;

	if($opts == null or !is_array($opts))
		return false;

	if (isset ($opts['page']) or empty ($opts['page'])) {
		if ((int) $opts['page'] < 2) {
			$page = 1;
		} else {
			$page = (int) $opts['page'];
		}
	} else {
		$page = 1;
	}

	// In case further ordering is needed
	$order = null;

	// A new ordering requested? update ordering creterion
	if ( isset($opts['sort_by']) and !empty ($opts['sort_by'])) {
		// Order by comment date
		if ($opts['sort_by'] == 'comment_date') {
			$order = 'comment_date';

		// order by post date
		} elseif ($opts['sort_by'] == 'post_date') { 
			$order = 'post_date';

		//order by spaminess
		} else {
			$order = 'spaminess';
		}

		update_option(defensio_user_unique_option_key('order'), $order);
	}

	if($order == null){
		// no request? get the ordering from options.
		$order = get_option(defensio_user_unique_option_key('order'));

		if($order == null){
			$order = 'spaminess';
			update_option(defensio_user_unique_option_key('order'), $order);
		}

	}

	$sql_order = defensio_sort_by_to_sql($order);


	// Hide obvious spam
	if (isset($opts['hide_obvious_spam_toggle'])) {
		$opt_name = defensio_user_unique_option_key('hide_more_than_threshold');

		if (isset($opts['hide_obvious_spam']))
			update_option($opt_name, '1');
		else
			update_option($opt_name, '0');
	}

	$spaminess_filter = defensio_generate_spaminess_filter();

	// search
	$search_query = '';
	if (isset($opts['search']) and !empty($opts['search'])) {
		$s = $opts['search'];
		$s = defensio_sql_escape($s);
		$search_query = " AND  (comment_author LIKE '%$s%' OR comment_author_email LIKE '%$s%' OR comment_author_url LIKE ('%$s%') OR comment_author_IP LIKE ('%$s%') OR comment_content LIKE ('%$s%') ) ";
		$query_param = $opts['search'];
	}
	else
		$query_param = $opts['search']; 

	if (!isset($opts['type']) or $opts['type'] == 'all' )
		$type_filter = ''; 

	elseif (isset($opts['type'])) {
		// Comments have empty type
		if ($opts['type'] == 'comments')
			$type_filter = " AND comment_type = '' ";
		else
			$type_filter = " AND comment_type != '' ";
	}

	// Count messages based on parameters passed
	$spam_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments LEFT JOIN $wpdb->prefix" . "defensio ON $wpdb->comments" . ".comment_ID = $wpdb->prefix" . "defensio.comment_ID
		WHERE comment_approved = 'spam' $spaminess_filter $search_query $type_filter  ");

	// Get actual messages
	$limit_start = ($page - 1) * $opts['items_per_page'];
	$limit_end = $opts['items_per_page'];

	$comments = $wpdb->get_results(
			"SELECT *,IFNULL(spaminess, 1) as spaminess, $wpdb->comments.comment_ID as id, $wpdb->posts.post_title as post_title, $wpdb->posts.post_date as post_date, $wpdb->comments.comment_content,
			$wpdb->comments.comment_post_ID  as post_id  FROM 
			$wpdb->comments LEFT JOIN $wpdb->prefix" . "defensio ON $wpdb->comments" . ".comment_ID = $wpdb->prefix" . "defensio.comment_ID LEFT JOIN  
			$wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID  WHERE comment_approved = 'spam'
			$spaminess_filter $search_query $type_filter ORDER BY $sql_order LIMIT $limit_start, $limit_end"
	);


	if (trim($order) == 'comment_date' || trim($order) == 'post_date')
		$order_param = trim($order);
	else
		$order_param = 'spaminess';


	global $plugin_uri;
	$reverse_spaminess_filter = defensio_generate_spaminess_filter(true, true);
	$err_code = NULL;

	return array(
		'comments'          => $comments,
		'current_page'      => $page,
		'type_filter'       => $opts['type'],
		'spam_count'        => $spam_count,
		'items_per_page'    => $opts['items_per_page'],
		'order'             => $order_param,
		'search_query'      => $query_param,
		'spaminess_filter'  => get_option(defensio_user_unique_option_key('hide_more_than_threshold')),	
		'nonce'             => $defensio_conf['nonce'],
		'stats'             => defensio_get_stats(),
		'obvious_spam_count' => defensio_obvious_spam_count(),
		'authenticated'     => defensio_verify_key($defensio_conf['key'], $err_code),
		'plugin_uri'        => $plugin_uri,
		'api_key'           => $defensio_conf['key']
	);
}

function defensio_dispatch(){
	global $wpdb, $defensio_conf, $defensio_retraining;

	if (function_exists('current_user_can') && !current_user_can('moderate_comments')) {
		die(__('You do not have sufficient permission to moderate comments.'));
	}

	// Perform requested actions old versions
	if(defensio_wp_version() < 2.7){
		$db_req = array( 
				'defensio_comments' => $_POST['defensio_comments'],  
				'defensio_empty_quarantine' =>  $_POST['defensio_empty_quarantine'],  
				'defensio_restore' =>  $_POST['defensio_restore'],  
				'defensio_delete' =>  $_POST['defensio_delete'] 
				);

	// Perform requested actions 2.7
	}else{
		$db_req = array( 
				'defensio_comments'         => $_POST['delete_comments'],  
				'defensio_empty_quarantine' => ($_POST['action'] == 'emptyquarantine'||  $_POST['action2'] == 'emptyquarantine')? true : NULL,
				'defensio_restore'          => ($_POST['action'] == 'restore'        ||  $_POST['action2'] == 'restore'        )? true : NULL ,  
				'defensio_delete'           => ($_POST['action'] == 'delete'         ||  $_POST['action2'] == 'delete'         ) ? true : NULL
				);
	}

	$db_req ['ham'] = $_GET['ham'];

	if(!isset($db_req['ham']))
		$db_req['ham'] = $_POST['ham'];

	defensio_update_db($db_req);
	
	/* Query for comments */
	$query_opts = array(
			'items_per_page' => 50,
			'page' => $_GET['defensio_page'],
			'sort_by' => $_REQUEST['sort_by'],
			'hide_obvious_spam_toggle' => $_REQUEST['hide_obvious_spam_toggle'],
			'hide_obvious_spam' => $_REQUEST['hide_obvious_spam'],
			'search' => $_POST['search'],
			'type' => $_POST['comment_type']
	);


	if(!isset($query_opts['search']))
		$query_opts['search'] = $_GET['search'];

	$render_params = defensio_caught($query_opts);

	// Render quarantine
	defensio_render_quarantine_html($render_params);
}


function defensio_manage_page() {
	global $wpdb, $submenu, $menu, $defensio_conf;

	$spaminess_filter = defensio_generate_spaminess_filter();
	$spam_count = defensio_unhidden_spam_count();
        
        
	$page = NULL;
	
	if (defensio_wp_version() >= 2.7 ){
		$page = add_comments_page('Defensio Spam', "Defensio Spam ($spam_count)", 'moderate_comments', 'defensio-quarantine', 'defensio_dispatch');

	} elseif (isset($submenu['edit-comments.php'])   ) {
		$page = add_submenu_page('edit-comments.php', 'Defensio Spam', "Defensio Spam ($spam_count)", 'moderate_comments', 'defensio-quarantine', 'defensio_dispatch');
	} elseif (function_exists('add_management_page')) {
		$page = add_management_page('Defensio Spam', "Defensio Spam ($spam_count)", 'moderate_comments', 'defensio-admin', 'defensio_dispatch');
	}

	if ($page){
		add_action( "admin_print_scripts-$page", 'defensio_admin_head' );
	}
}
add_action('admin_menu', 'defensio_manage_page');

function defensio_admin_head(){
	global $plugin_uri;
	wp_enqueue_script('prototype');
	wp_enqueue_script('fat',  '/wp-content/plugins/defensio-anti-spam/scripts/fat.js');
	wp_enqueue_script('defensio', '/wp-content/plugins/defensio-anti-spam/scripts/defensio.js');
	// 2.7 +
	wp_enqueue_script('admin-comments');
	wp_enqueue_script('admin-forms');
}

function defensio_save_meta_data($comment_ID) {
	global $wpdb, $defensio_meta;
	$meta = $defensio_meta;
	$comment_ID = defensio_sql_escape($comment_ID);

	//Create Defensio record
	if (isset($meta['spaminess']) and isset($meta['signature'])) {
		$wpdb->query("INSERT INTO $wpdb->prefix" . "defensio (comment_ID, spaminess, signature) VALUES	(" . $comment_ID . ", " . $meta['spaminess'] . ", '" . $meta['signature'] . "')");
	} else {
		$wpdb->query("INSERT INTO $wpdb->prefix" . "defensio (comment_ID, spaminess, signature) VALUES	(" . $comment_ID . ", -1 , '' )");
	}

  return $comment_ID;
}

function defensio_update_meta_data($comment_ID) {
	global $wpdb, $defensio_meta;
	$meta = $defensio_meta;
	$comment_ID = defensio_sql_escape($comment_ID);

	if (isset($meta['remove']) and $meta['remove'] == true) {
		$wpdb->query("DELETE from $wpdb->prefix" . "defensio WHERE comment_ID = $comment_ID");
		$wpdb->query("DELETE from $wpdb->comments WHERE comment_ID = $comment_ID");
		return true;
	}

	// Update Defensio record
	if (isset($meta['spaminess']) and isset($meta['signature'])) {
		$wpdb->query("UPDATE  $wpdb->prefix" . "defensio set spaminess =  ". $meta['spaminess'] . " , signature =	'" . $meta['signature'] . "' WHERE comment_ID = $comment_ID ");

		// If this is an ajax call put spam in the quarantine since hooks wont run
		if (defined('DEFENSIO_AJAX') and isset($defensio_meta['spam']) and $defensio_meta['spam'] == true) {
			$wpdb->query("UPDATE $wpdb->comments set comment_approved = 'spam' WHERE comment_ID = $comment_ID ");
		}
	} else {
		return false;
	}

	return $comment_ID;
}

// get stats from cache, or from server is cache is invalidated
function defensio_get_stats() {
	$stats = wp_cache_get('stats', 'defensio');

	if (!$stats) { 
		$stats = defensio_refresh_stats();
		wp_cache_set('stats' , $stats, 'defensio', 600);
	}

	return $stats;
}

// Refresh stats from server
function defensio_refresh_stats() {
	global $defensio_conf;
	$err_code = NULL;
	$r = defensio_post('get-stats', array('owner-url' => $defensio_conf['blog']), $err_code); 

	$ar = Spyc::YAMLLoad($r); 
	if (isset($ar['defensio-result'])) {
		defensio_update_stats_cache($ar['defensio-result']);
		return $ar['defensio-result'];
	} else {
		return false;
	}
}

/* Look for wp_openid */
function defensio_is_openid_enabled(){
	return function_exists('is_user_openid');
}

// Receives an associative array representing
// a comment before it has been sent defensio,
// returns the comment with the appropiate 
// opeind related values.
//
// If there is not OpenID enabled or the user 
// is not logged in using OpenID returns exactly 
// the same comment.
function defensio_get_openid($com){
	global $wpdb;

	if (!defensio_is_openid_enabled())
		return $com;

	if (is_user_openid()){
		$identity = get_user_openids(null);
		// Take the first URL.
		if(is_array($identity)) {
			$identity = @array_pop($identity);
		}
		$com['openid'] = $identity;
	} elseif(function_exists('finish_openid_auth')) {
		$identity = finish_openid_auth();
		$com['openid'] =  $identity;
		// Not really logged in but a valid openid
		if(!is_null($identity))
			$com['user-logged-in'] = 'true';
	}
	return $com;
}

function defensio_check_comment($com, $incoming = true, $retrying = false) {
	global $wpdb, $defensio_conf, $defensio_meta, $userdata, $acts_as_master;

	$comment = array();

	/* If it is an incoming message (not yet in the database).
	   get current user info, otherwise get the info from the 
	   user who posted the comment 
	*/

	if ($incoming) {
		$comment['referrer'] = $_SERVER['HTTP_REFERER'];
		$comment['user-ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);
		get_currentuserinfo();
	} else {
		$userdata = get_userdata($com['user_id']);
		$comment['user-ip'] = $com['comment_author_IP'];
	}

	if ($userdata->ID) {
		$comment['user-logged-in'] = 'true';
		$caps = get_usermeta( $userdata->ID, $wpdb->prefix . 'capabilities');
		if (defensio_is_trusted_user($caps)) {
			$comment['trusted-user'] = 'true';
		}
	}

	$comment['owner-url'] = $defensio_conf['blog'];

	if (isset($com['comment_post_ID'])) {
		$comment['article-date'] = strftime("%Y/%m/%d", strtotime($wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE ID=" . $com['comment_post_ID'])));
		$comment['permalink'] = get_permalink($com['comment_post_ID']);
	}

	$comment['comment-author'] = $com['comment_author'];

	if (!isset($com['comment_type']) or empty($com['comment_type'])) {
		$comment['comment-type'] = 'comment';
	} else {
		$comment['comment-type'] = $com['comment_type'];
	}

	// Make sure it we don't send an SQL escaped string to the server
	$comment['comment-content'] = defensio_unescape_string($com['comment_content']);
	$comment['comment-author-email'] = $com['comment_author_email'];
	$comment['comment-author-url'] = $com['comment_author_url'];

	// If wp_openid is installed, use it
	$comment['user_ID'] = $com['user_ID'];
	$comment = defensio_get_openid($comment);
	unset( $comment['user_ID']);
	$err_code = NULL;

	if ($r = defensio_post('audit-comment', $comment, $err_code)) {
		$ar = Spyc :: YAMLLoad($r);

		if (isset($ar['defensio-result'])) {
			if ($ar['defensio-result']['status'] == DF_SUCCESS ) {
				// Set metadata about the comment
				$defensio_meta['spaminess'] = $ar['defensio-result']['spaminess'];
				$defensio_meta['signature'] = $ar['defensio-result']['signature'];

				// Hook a function to store that metadata
				add_action('comment_post', 'defensio_save_meta_data');
		
				// Mark it as SPAM
				if ($ar['defensio-result']['spam']) {
					add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'), 99);
					$defensio_meta['spam'] = true;
					$article = get_post($com['comment_post_ID']);	

					// Get the difference in seconds from the article publication date until today
					$time_diff = time() - strtotime($article->post_modified_gmt);

					// A day has 86400 seconds
					if (get_option('defensio_delete_older_than') == 1 and ($time_diff > (get_option('defensio_delete_older_than_days') * 86400))) {
						if ($incoming) {
							die;
						} else {
							$defensio_meta['remove'] = true;
						}
					}
				} else {
					// Apply wp preferences in case approved value has been changed to spam by another plug-in
					if ($acts_as_master == true) {
						add_filter('pre_comment_approved', create_function('$a', 'if ($a == \'spam\') return defensio_reapply_wp_comment_preferences(' .var_export($com, true). '); else return $a; '), 99);
					}
				}
			}else{
				// Successful http request, valid Defensio Result but Defensio failre
				if(!$retrying){
					defensio_check_comment($com, $incoming, true) ;
				 } else {
					// Put comment in moderation queue.
					defensio_send_to_moderation();
				}
			}
		} else {
			// Successful http request, but invalid Defensio result. Retry, once
			if(!$retrying){
				defensio_check_comment($com, $incoming, true) ;
			 } else {
				// Put comment in moderation queue.
				defensio_send_to_moderation();
			}
		}
	} else {
		// Unsuccessful POST to the server. Defensio might be down.  Retry, once
		if(!$retrying) {
			 defensio_check_comment($com, $incoming, true) ;
		// No luck... put comment in moderation queue
		} else {
			defensio_send_to_moderation();
		}
	}

	return $com;
}
add_action('preprocess_comment', 'defensio_check_comment', 1);

// Sets the hooks that will send the message being processed to moderation and 
// save Defensio's meta-data, if defensio spaminess is not available, the comment will
// be threated as unproccessed
function defensio_send_to_moderation(){
	add_filter('pre_comment_approved', create_function('$a', 'return 0;'), 99);
	add_action('comment_post', 'defensio_save_meta_data');
}


function defensio_verify_key($key, &$err_code) {
	global $defensio_conf;
	$result = false;
	$params = array('key'		=> $key,
					'owner-url' => $defensio_conf['blog']);

	if ($r = defensio_post('validate-key', $params, $err_code)) {
		// Parse result
		$ar = Spyc :: YAMLLoad($r);

		// Spyc will return an empty array in case the result is not a well-formed YAML string.
		// Verify that the array is a valid Defensio result before continuing
		if (isset ($ar['defensio-result'])) {
			if ($ar['defensio-result']['status'] == DF_SUCCESS) {
				$result = true; 
				return $result;
			}
		} else {
			return $result;
		}
	} else {
	  return $result;
	}

	return $result;
}



function defensio_submit_ham($signatures) {
	global $wpdb, $defensio_conf;

	$params = array(
		'signatures' => $signatures,
		'owner-url'	 => $defensio_conf['blog'],
		'user-ip'	 => $comment->comment_author_IP );
	$err_code = NULL;
	$r = defensio_post('report-false-positives', $params, $err_code);
}


function defensio_submit_spam($signatures){
	global $wpdb, $defensio_conf;

	$params = array(
		'signatures' => $signatures,
		'owner-url'	 => $defensio_conf['blog'],
		'user-ip'	 => $comment->comment_author_IP);
	$err_code = NULL;
	$r = defensio_post('report-false-negatives', $params, $err_code);
}


// To train multiple messages at once, we push them into an array and process them in the shutdown hook.
function defensio_defer_training($id, $new_status = null) {
	global $deferred_spam_to_ham, $deferred_ham_to_spam, $defensio_retraining, $wpdb;
	
	// 'approve' should only be retrained when a message is being marked as SPAM
	if (!(($new_status == 'approve' and $defensio_retraining) or $new_status == 'spam' or $new_status == null  )) {
		return;
	}

	$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments NATURAL JOIN $wpdb->prefix" . "defensio WHERE $wpdb->comments.comment_ID = '$id'");

	if (!$comment) { return; }
	if (!isset($comment->signature) or empty($comment->signature)) { return; }
	
	if ($comment->comment_approved == 'spam' and isset($new_status) ) {
		// Set new spaminess to 100%, it is spam for sure
		$wpdb->get_row("UPDATE	$wpdb->prefix" . "defensio SET spaminess = 1 WHERE $wpdb->prefix"."defensio.comment_ID = '$id'");

		// If ajax retrain, the shutdown hook won't be called, and no defered training will occur 
		if (defined('DOING_AJAX')) {
		  defensio_submit_spam($comment->signature);
		// Push for training	
		} else {
		  array_push($deferred_ham_to_spam, $comment->signature );
		}
	}

	if ($comment->comment_approved == 1) {
		// Set new spaminess to 0%, it is ham for sure
		$wpdb->get_row("UPDATE	$wpdb->prefix" . "defensio SET spaminess = 0 WHERE $wpdb->prefix"."defensio.comment_ID = '$id'");

		if (defined('DOING_AJAX' )) {
			defensio_submit_ham($comment->signature);
		} else	{
			array_push($deferred_spam_to_ham, $comment->signature );
		}
	}
}
add_action('wp_set_comment_status', 'defensio_defer_training', 10, 2);
add_action('edit_comment', 'defensio_defer_training', 10, 1);


function defensio_announce_article($id) {
	global $defensio_conf, $wpdb, $userdata;

	get_currentuserinfo();
	$id = defensio_sql_escape($id);
	$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE $wpdb->posts.ID = '$id' ");

	$params = array (
		'article-content' => $post->post_content, 
		'article-title'	  => $post->post_title, 
		'permalink'		  => get_permalink($post->ID),
		'owner-url'		  => $defensio_conf['blog'],
		'article-author'  => $userdata->user_login,
		'article-author-email' => $userdata->user_email );

	$err_code = NULL;
	$r = defensio_post('announce-article', $params, $err_code);
} 
add_action('publish_post', 'defensio_announce_article');


// Post an action to Defesio and use args as POST data, returns false on error 
// call supressing the warnings if err_code is not passed.
function defensio_post($action, $args = null, &$err_code) {
	global $defensio_conf;

	// Use snoopy to post
	require_once (ABSPATH . 'wp-includes/class-snoopy.php');

	$snoopy = new Snoopy();
	$snoopy->read_timeout = $defensio_conf['post_timeout'];

	// Supress the possible fsock warning 
	@$snoopy->submit(defensio_url_for($action, $defensio_conf['key']), $args, array ());

	// Defensio will return 200 on success, 401 on authentication failure, anything else is unexpected behaviour
	if ($snoopy->status == 200 or $snoopy->status == 401) {
		return $snoopy->results; 
	} else {
		$err_code = $snoopy->status;
		return false;
	}
}


// Returns the URL for possible actions
function defensio_url_for($action, $key = null) {
	global $defensio_conf;

	if ($key == null) {
		return null;
	} else {
		return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key . '.' . $defensio_conf['format'];
	}
}


function defensio_empty_quarantine() {
	global $wpdb;
	$wpdb->query("DELETE $wpdb->prefix"."defensio.* FROM  $wpdb->prefix"."defensio NATURAL JOIN $wpdb->comments WHERE comment_approved = 'spam'");
	$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'");
}


function defensio_sql_escape($str) {
	global $wpdb;
	return $wpdb->escape($str);
}

// To be used with admin-ajax
function defensio_restore() {
	define('DOING_AJAX', true);
	if (isset ($_POST['ham'])) {
		$id = (int) $_POST['ham'];
		if(isset($id)){
			//defensio_set_status_approved(get_comment($id));
			defensio_set_status_approved($id);
		}
	} 
}

add_action('wp_ajax_defensio-restore', 'defensio_restore');
add_filter('comment_spam_to_approved',  create_function('$comment', 'defensio_set_status_approved($comment->comment_ID);'));

// Sets the spamines value for a comment already in the db
function defensio_set_spaminess($comment_id, $value){
	global $wpdb;
	$wpdb->get_row("UPDATE	$wpdb->prefix" . 
			"defensio SET spaminess = " . 
			(string)$value  ." WHERE $wpdb->prefix".
			"defensio.comment_ID = '$comment_id'"
			);
}

function defensio_is_trusted_user($cap) {
	global $defensio_trusted_roles;

	if (!is_array($cap)) { return false; }

	foreach ($cap as $k => $v) {
		if (in_array($k, $defensio_trusted_roles)) { return true; }
	}

	return false;
}

function defensio_get_signature($comment_id){
	global $wpdb, $defensio_conf;
	return $wpdb->get_var("SELECT signature FROM $wpdb->prefix". "defensio WHERE comment_ID = '$comment_id' LIMIT 1");
}

function defensio_set_status_approved($id) {
	global $defensio_retraining;

	// Human checked.. spaminess => 0 
	defensio_set_spaminess($id, 0);
	defensio_submit_ham(defensio_get_signature($id)); 

	$defensio_retraining = true;
	wp_set_comment_status($id, 'approve');
	$defensio_retraining = false;
}


function defensio_reapply_wp_comment_preferences($comment_data) {  
	//Taken from wp_comment.php
	global $wpdb;
	extract($comment_data, EXTR_SKIP);

	if ($user_id) {
		$userdata = get_userdata($user_id);
		$user = new WP_User($user_id);
		$post_author = $wpdb->get_var("SELECT post_author FROM $wpdb->posts WHERE ID = '$comment_post_ID' LIMIT 1");
	}

	if ($userdata && ($user_id == $post_author || $user->has_cap('level_9'))) {
		// The author and the admins get respect.
		$approved = 1;
	} else {
		// Everyone else's comments will be checked.
		if ( check_comment($comment_author, $comment_author_email, $comment_author_url, $comment_content, $comment_author_IP, $comment_agent, $comment_type)) {
			$approved = 1;
		} else {
			$approved = 0;
		}

		if (wp_blacklist_check($comment_author, $comment_author_email, $comment_author_url, $comment_content, $comment_author_IP, $comment_agent)) {
			$approved = 'spam';
		}
	}

	return $approved;
}

function defensio_unescape_string($str) {
	return stripslashes($str);
}

function defensio_counter($color='dark', $align='left') {
	global $plugin_uri;
	$last_updated = get_option('defensio_stats_updated_at');
	$two_hours = 60 * 60 * 2;

	if ( ($last_updated == NULL) or (mktime() - $last_updated > $two_hours) ) {
		$s = defensio_get_stats();
	} else {
		$s = get_option('defensio_stats');
	}

	if ($s) {
		defensio_render_counter_html(array('smoked_spam'=>$s['spam'], 'color'=>$color, 'align'=>$align, 'plugin_uri'=>$plugin_uri ));
	}
}

function defensio_update_stats_cache($stats) {
	update_option('defensio_stats', $stats);
	update_option('defensio_stats_updated_at', mktime());
}

function defensio_widget_register() {
	if (function_exists('register_sidebar_widget')) {
		function defensio_widget() { 
			$alignment = get_option('defensio_counter_alignment'); 
			$color = get_option('defensio_counter_color');
			if (!isset($alignment) or empty($alignment)){ $alignment = 'left'; }
			if (!isset($color) or empty($color)){ $color = 'dark'; }

			defensio_counter(strtolower($color),strtolower($alignment)); 
		}

		function defensio_widget_control() {
			global $defensio_widget_tones;
			if ($_POST['defensio_counter_alignment']) {
				update_option('defensio_counter_alignment', $_POST['defensio_counter_alignment']);
			}

			if ($_POST['defensio_counter_color']) {
				update_option('defensio_counter_color', strtolower($_POST['defensio_counter_color']));
			}

			$alignment = get_option('defensio_counter_alignment');
			$color = get_option('defensio_counter_color');

			if (!isset($alignment) or empty($alignment)){ $alignment = 'Left'; }
			if (!isset($color) or empty($color)){ $color = 'dark'; }
?>
			<label for="defensio_counter_alignment"	 style="width: 100px; display: block; float: left;">Alignment</label>
			<select name="defensio_counter_alignment" id="defensio_counter_alignment">
				<option <?php if ($alignment == 'Left'):?>selected="1" <?php endif;?> >Left</option>
				<option <?php if ($alignment == 'Center'):?> selected="1"<?php endif;?> >Center</option>
				<option <?php if ($alignment === 'Right'):?>selected="1" <?php endif; ?> >Right</option>
			</select> 
			<br />
			<label for="defensio_counter_color" style="width: 100px; display: block; float: left;">Color</label>
			<select name="defensio_counter_color" id="defensio_counter_color">
				<?php foreach($defensio_widget_tones as $t): ?>
					<option <?php if ($t == $color) :?> selected="1"<?php endif;?> ><?php echo ucfirst($t) ?></option>
				<?php endforeach; ?>
			</select>
<?php
		}
		register_sidebar_widget('Defensio Counter', 'defensio_widget', null, 'defensio');
		register_widget_control('Defensio Counter', 'defensio_widget_control', 300, 75, 'defensio');
	}
}
add_action('init', 'defensio_widget_register');


function defensio_get_unprocessed_comments() {
	global $wpdb;
	// Spaminess -1 means the comment never reached Defensio server
	$comments = $wpdb->get_results("SELECT $wpdb->comments.comment_ID as id FROM $wpdb->comments  LEFT JOIN $wpdb->prefix" . "defensio ON $wpdb->comments" . ".comment_ID = $wpdb->prefix" . "defensio.comment_ID  WHERE spaminess = -1  "); 
	return($comments);
}

function defensio_spam_count() {
	global $wpdb;
	return $wpdb->get_var("SELECT count(*) FROM $wpdb->comments LEFT JOIN $wpdb->prefix"."defensio ON $wpdb->comments" . ".comment_ID = $wpdb->prefix" . "defensio.comment_ID WHERE comment_approved = 'spam';");
}

function defensio_obvious_spam_count() {
	global $wpdb;
	return $wpdb->get_var("SELECT count(*) FROM $wpdb->comments LEFT JOIN $wpdb->prefix"."defensio ON $wpdb->comments" . ".comment_ID = $wpdb->prefix" . "defensio.comment_ID WHERE comment_approved = 'spam' ". defensio_generate_spaminess_filter(true, true) . ";");
}

function defensio_unhidden_spam_count(){
        $spam_count = 0;
        if(get_option(defensio_user_unique_option_key('hide_more_than_threshold')) == 1){
		$spam_count = defensio_spam_count() - defensio_obvious_spam_count();
	} else {
		$spam_count = defensio_spam_count();
	}
	return $spam_count;
}

function defensio_render_activity_box() {
	$link_base = 'edit-comments.php';
	$link = clean_url($link_base . "?page=defensio-quarantine.php");

	$obvious_spam_count = defensio_obvious_spam_count();
	$total_spam_count = defensio_spam_count();

	if (is_new_gen_wordpress())
		echo "<p class='youhave'>";
	else 
		echo "<p>";

	
	if ($total_spam_count == 0) {
		echo "Your <strong>Defensio quarantine is empty</strong>. Awesome!</p>";
	}
	else {  // some spam in quarantine
		if ($total_spam_count <= 1)
			echo "You have <strong>$total_spam_count spam comment</strong>";
		else
			echo "You have <strong>$total_spam_count spam comments</strong>";

		if ($obvious_spam_count > 0) 
			echo " ($obvious_spam_count obvious)";
			
		echo " in your <a href='$link'>Defensio quarantine</a>.";
	}

	echo "</p>";
}
add_action('activity_box_end', 'defensio_render_activity_box');

// Orphan rows have spaminess -1; they were never filtered by Defensio
function defensio_clean_up_orphan_rows($id, $status) {
	global $wpdb;
	if ($status == 'hold') {
		// If it stays in moderation, it can still be sent to defensio, do nothing
	} elseif ($status == 'spam') {
		// spam for sure
		$wpdb->query("UPDATE  $wpdb->prefix"."defensio set spaminess = 1 WHERE spaminess = -1 AND comment_ID = $id " );
	} elseif ($status == 'approve') {
		// ham for sure
		$wpdb->query("UPDATE  $wpdb->prefix"."defensio set spaminess = 0 WHERE spaminess = -1 AND comment_ID = $id " );
	} elseif ($$status == 'delete') {
		$wpdb->query("DELETE FROM $wpdb->prefix"."defensio WHERE spaminess = -1 AND comment_ID = $id " );
	}
}
add_action('wp_set_comment_status', 'defensio_clean_up_orphan_rows', 10, 2);


// Generates a key name for wp options that is user unique
function defensio_user_unique_option_key( $opt_name = null ){
	global $userdata;
	if($opt_name != null){
		get_currentuserinfo();
		return "defensio_". $userdata->ID."_$opt_name";
	}
}

function defensio_sort_by_to_sql($sort_by = null){
	switch($sort_by){
		case 'post_date':
			return ' post_date DESC, IFNULL(spaminess, 1) ASC ';
		case 'comment_date':
			return ' comment_date DESC, IFNULL(spaminess, 1) ASC ';
		default:
			return ' IFNULL(spaminess, 1) ASC, comment_date DESC ' ;
	}
}

// Only 2.7
if (defensio_wp_version() >= 2.7 ){
	add_filter('comment_status_links', 'defensio_replace_default_quarantine_link', 99, 1);

	/* In WP 2.7 there is a built-in  SPAM quarantine. This filter function
	* will take the array of status links in wp-admin/comments and replace the
	* link to spam type by a link to defensio's quarantine
	*/
	function defensio_replace_default_quarantine_link($status_links){

		foreach($status_links as $index => $link){

			if(preg_match('/Spam/', $link)){
				$status_links[$index] = '<a href="edit-comments.php?page=defensio-quarantine">Defensio Spam ('. defensio_unhidden_spam_count() . ") </a> ";
				break;
			}	
		}
		return $status_links;
	}
	
	// Redirect default quarantine to defensio's. There is no useful hook to change the
	// link in dashboard.php... just redirect 
	add_action('load-edit-comments.php', 'defensio_redirect_to_qurantine');

	function defensio_redirect_to_qurantine($a){
		if($_REQUEST['comment_status'] == 'spam')
			wp_redirect("edit-comments.php?page=defensio-quarantine");
	}
}

?>
