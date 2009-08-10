<?php
/**** Helpers for the plugin ****/

// Load environment if it is an AJAX call
if (defined('DEFENSIO_AJAX')) {
	// we have to chdir in order for admin.php to be able to require its own files.
	$old_dir = getcwd();
	chdir('../../../wp-admin/');
	require_once('admin.php');
	require_once('admin-functions.php');
	chdir($old_dir);
}

$site_uri = get_option('siteurl');
$df_utils_file = __FILE__;
$plugin_name = "defensio-anti-spam";
$plugin_path = substr(dirname($df_utils_file ), 0, strlen(dirname($df_utils_file))-3);
$plugin_uri = get_option('siteurl') . "/wp-content/plugins/$plugin_name/";


// Does not require twice a file, if it has the name of an already required/included file
function defensio_require_once_by_name($filename){
	$included_names = array_map(create_function('$full_name', 'return basename($full_name);'),
		array_merge(get_included_files(),get_required_files()));
    
	// If a file with this name exists return true as require once does http://ca.php.net/manual/en/function.require-once.php
	if(in_array(basename($filename), $included_names))
		return true;
	else
		require($filename);
}

function is_mu() {
	return function_exists("is_site_admin");
}

function is_wp_version_supported() {
	global $wp_version;
	if (is_mu())
		return ($wp_version >= 1.1);
	else
		return ($wp_version >= 2.1);
}

// Returns the appropriate defensio_warning style based on the current WordPress version
function defensio_warning_style() {
	global $wp_version;

	$old_style = "#defensio_warning { position: absolute; top: 7em; }";
	
	$new_style = "";
	
	if((float)$wp_version < 2.7)
		$new_style = "#defensio_warning { position: absolute; top: 11.5em; }";

	if (!is_new_gen_wordpress())
		return $old_style;
	else
		return $new_style;
}

// Returns true if running on Wordpress 2.5+ or MU 1.5+
function is_new_gen_wordpress() {
	global $wp_version;
	if (is_mu())
		return $wp_version >= 1.5;
	else
		return $wp_version >= 2.5;
}

// Replaces multiple checks for MU and standard WP.
// It will return 2.x versions for any wp version, since
// 2.3 and 1.3Mu share code they will be seen as 2.3 and so on
function defensio_wp_version(){
	global $wp_version;
	$version = (float)$wp_version;

	if(is_mu() and $version < 1.5){
		return $version + 1;
	} else {
		return $version;
	}

}

?>
