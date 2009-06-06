<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
	
	<title><?php bloginfo('name'); ?> <?php wp_title('&mdash;'); ?></title>
	
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
	<?php wp_head(); ?>
</head>

<body>
	
<div id="page">
	
<div id="header">
	<h1><a href="<?php echo get_option('home'); ?>/"><?php bloginfo('name'); ?></a></h1>

	<?php if (get_bloginfo('description') != '') { ?><div id="tagline"><?php bloginfo('description'); ?></div><?php } ?>
	
</div>
