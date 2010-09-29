<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
<link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'/>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="keywords" content="peter lyons, pete lyons, computer, computers, java, software engineer, programmer, programming, Sun Certified Programmer for the Java 2 Platform, music, musician, sax, saxophone, saxophonist, sunny daze, confunktion junction, jazz, oberlin, classical" />
<meta name="author" content="Peter Lyons" />
<meta name="description" content="The blog of for Peter Lyons, Software Engineer and Musician" />
<meta name="copyright" content="2001, Peter Lyons" />
<meta name="generator" content="jEdit, Wordpress" />

<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/print.css" type="text/css" media="print" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>
</head>

<body>
<div id="page">
<div id="header">
	<div id="headertitle">
		<h1><a href="<?php bloginfo('url') ?>" title="<?php bloginfo('name') ?>: <?php bloginfo('description') ?>"><?php bloginfo('name') ?></a></h1>
		<p><?php bloginfo('description') ?></p>
	</div> 
	<!-- Search box (If you prefer having search form as a sidebar widget, remove this block) -->
	<div class="search">
		<?php  include (TEMPLATEPATH . "/searchform.php"); ?>
	</div> 
	<!-- Search ends here-->
		
</div>

<div id="wrapper">
