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

/* Replaces multiple checks for MU and standard WP.
 * It will return 2.x versions for any wp version, since
 * 2.3 and 1.3Mu share code they will be seen as 2.3 and so on
 */
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