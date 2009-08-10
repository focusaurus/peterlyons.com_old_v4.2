<?php
define('DEFENSIO_AJAX', 1);
require_once('lib/defensio_utils.php');
require_once($plugin_path.'../../../wp-config.php');

if(file_exists($plugin_path.'../../../wp-admin/admin-functions.php')) { defensio_require_once_by_name($plugin_path.'../../../wp-admin/admin-functions.php'); }
if(file_exists($plugin_path.'../../../wp-admin/admin-db.php')) { defensio_require_once_by_name($plugin_path.'../../../wp-admin/admin-db.php'); }
if(file_exists($plugin_path.'../../../wp-admin/admin.php')) { defensio_require_once_by_name($plugin_path.'../../../wp-admin/admin.php'); }

if (function_exists('current_user_can') && !current_user_can('moderate_comments')) {
	die(__('You do not have sufficient permission to moderate comments.'));
}

class defensioAjaxHandler {
	function defensio_ajax_check_comment($params){
		require_once('defensio.php');
		global $defensio_meta;
		if (!isset($params['id'])) { return "{id: null, success: false}"; }

		$com = (Array)get_comment($params['id']);
		
		// Set incomming to false this comment is alredy in the db
		defensio_check_comment($com, false);
		$result = defensio_update_meta_data($params['id']);

		if($result) { 
			if($defensio_meta['spam']) {return "{id: ".$params[id].", success: true, spam: true}";}
			else {return "{id: ".$params[id].", success: true, spam: false}";}
		}
		else { return "{id: ".$params[id].", success: false}"; }
	}
}
$handler = new defensioAjaxHandler();


//Dispatch
if(isset($_GET['action']) and method_exists($handler, 'defensio_ajax_' . $_GET['action'])) {
	// Call only methods from $handler that will make it less prone to attacks
	$response = call_user_func(array('defensioAjaxHandler','defensio_ajax_' .  $_GET['action']), $_POST);
  
	// Make sure prototype will evaluate the JS generated here
	header('Content-type: application/javascript', true);
	echo $response;

//No such action
} else {
	header("HTTP/1.0 404 Not Found", true, 404);
}
?>
