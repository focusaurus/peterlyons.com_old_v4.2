<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title><?php bloginfo("name"); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?> | Peter Lyons </title>
<link rel="stylesheet" href="<?php bloginfo("template_url"); ?>/print.css" type="text/css" media="print" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo("name"); ?> RSS Feed" href="<?php bloginfo("rss2_url"); ?>" />
<link rel="pingback" href="<?php bloginfo("pingback_url"); ?>" />
<?php if ( is_singular() ) wp_enqueue_script( "comment-reply" ); ?>
<?php wp_head(); ?>
<meta name="keywords" content="peter lyons, pete lyons, web development, startups, music, sax, saxophone, saxophonist, sunny daze, confunktion junction, oberlin, smartears, smart ears"><meta name="author" content="Peter Lyons"><meta name="description" content="The web site for Peter Lyons, Web Developer and Musician"><meta name="copyright" content="2001, Peter Lyons"><link rel="stylesheet" href="/screen.css" type="text/css"><link rel="stylesheet" href="/css/overcast/jquery-ui.css" type="text/css"><script src="/js/jquery.js"></script><script src="/js/jquery-ui.js"></script><link href="/favicon.ico" type="image/x-icon" rel="icon"><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"><link rel="openid.server" href="http://www.livejournal.com/openid/server.bml"><link rel="openid.delegate" href="http://focusaurus.livejournal.com/"><!--link(href='http://fonts.googleapis.com/css?family=Inconsolata', rel='stylesheet')--></head><body><div class="content"><header><h1><a href="/">Peter Lyons</a></h1></header><nav class="blog"><a href="feed">Subscribe</a><form method="get" id="searchform" action="/problog/">
<input type="text" size="20" name="s" id="s" value="Search..." onblur="if(this.value=='') this.value='Search...';" onfocus="if(this.value=='Search...') this.value='';">
</form>
</nav>
