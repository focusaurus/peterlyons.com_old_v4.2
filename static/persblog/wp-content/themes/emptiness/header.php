<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
  <head>
    <title><?php wp_title(); ?> <?php bloginfo('name'); ?></title>
    <meta http-equiv="content-type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <style type="text/css" media="screen">
      @import url( <?php bloginfo('stylesheet_url'); ?> );
    </style>
    <link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
    <link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <?php wp_get_archives('type=monthly&format=link'); ?>
    <?php wp_head(); ?>
    <!-- Emptiness Theme by Qoqoa - Cliffano Subagio -->
  </head>
  <body>
    <div id="container">
      <div id="header">
        <div class="item">
          <div class="side left">
            &nbsp;
          </div>
          <div class="main">
            <h1><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
            <?php bloginfo('description'); ?>
          </div>
          <div class="side right">
            <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
              <div><input type="text" value="search..." name="s" onclick="this.value = ''"/></div>
            </form>
          </div>
        </div>
        <div class="item">
          <div class="side left">
            <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Header Left Sidebar') ) : ?>
              <ul>
                <li><a href="<?php bloginfo('url'); ?>">Home</a></li>
                <?php wp_list_pages('title_li='); ?>
                <li><a href="<?php bloginfo('atom_url'); ?>">Feed</a></li>
                <?php if (is_user_logged_in()) { ?>
                	<li><a href="<?php echo get_option('siteurl'); ?>/wp-admin/">Admin</a></li>
                <?php } ?>
                <li><?php wp_loginout(); ?></li>
              </ul>
            <?php endif; ?>
          </div>
          <div class="main splash">
            &nbsp;
          </div>
          <div class="side right">
            <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Header Right Sidebar') ) : ?>
              <h3>Tags</h3>
              <div>
                <?php wp_tag_cloud('smallest=9&largest=14&number=25'); ?>
              </div>
              <h3>Categories</h3>
              <ul>
                <?php wp_list_categories('hierarchical=false&title_li='); ?> 
              </ul>
            <?php endif; ?>
          </div>
        </div>
      </div>