<?php
if ( function_exists('register_sidebar') )
    register_sidebar();
    
add_filter('comments_template', 'legacy_comments');
function legacy_comments($file) {
	if(!function_exists('wp_list_comments')) 	$file = TEMPLATEPATH . '/legacy.comments.php';
	return $file;
}    
    
?>
