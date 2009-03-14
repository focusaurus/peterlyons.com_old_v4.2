<?php
if ( function_exists('register_sidebar') ) {
  register_sidebar(array('name' => 'Header Left Sidebar', 'before_title' => '<h3>', 'after_title' => '</h3>'));
  register_sidebar(array('name' => 'Header Right Sidebar', 'before_title' => '<h3>', 'after_title' => '</h3>'));
  register_sidebar(array('name' => 'Body Right Sidebar', 'before_title' => '<h3>', 'after_title' => '</h3>'));
  register_sidebar(array('name' => 'Post Left Sidebar'));
}
?>