<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title><?php bloginfo("name"); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?> | Peter Lyons </title>
<link rel="stylesheet" href="<?php bloginfo("template_url"); ?>/print.css" type="text/css" media="print" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo("name"); ?> RSS Feed" href="<?php bloginfo("rss2_url"); ?>" />
<link rel="pingback" href="<?php bloginfo("pingback_url"); ?>" />
<?php if ( is_singular() ) wp_enqueue_script( "comment-reply" ); ?>
<?php wp_head(); ?>
<meta name="keywords" content="peter lyons, pete lyons, web development, node.js, ruby on rails, afronauts, boulder, colorado, turtle dove, startups, music, sax, saxophone, saxophonist, sunny daze, confunktion junction, oberlin, smartears, smart ears, big clock"><meta name="author" content="Peter Lyons"><meta name="description" content="Pete Lyons, node.js coder for hire"><link rel="stylesheet" href="screen.css"><link rel="stylesheet" href="css/overcast/jquery-ui.css"><script src="js/jquery.js"></script><script src="js/jquery-ui.js"></script><link href="/favicon.ico" type="image/x-icon" rel="icon"><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"><link rel="openid.server" href="http://www.livejournal.com/openid/server.bml"><link rel="openid.delegate" href="http://focusaurus.livejournal.com/"><link href="http://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet"></head><body><div class="content"><header><h1><a href="/">Peter Lyons</a></h1></header><nav class="blog"><form method="get" id="searchform" action="/problog/">
 <input type="text" size="20" name="s" id="s" value="Search..." onblur="if(this.value=='') this.value='Search...';" onfocus="if(this.value=='Search...') this.value='';">
 </form>
<a href="<?php echo site_url('feed') ?>">
<img src="<?php echo esc_url( includes_url( 'images/rss.png' ) ); ?>" /></a>
</nav>
