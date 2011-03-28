<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title><?php bloginfo("name"); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?> | Peter Lyons </title>
<link rel="stylesheet" href="<?php bloginfo("template_url"); ?>/print.css" type="text/css" media="print" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo("name"); ?> RSS Feed" href="<?php bloginfo("rss2_url"); ?>" />
<link rel="pingback" href="<?php bloginfo("pingback_url"); ?>" />
<?php if ( is_singular() ) wp_enqueue_script( "comment-reply" ); ?>
<?php wp_head(); ?>
<meta name="keywords" content="peter lyons, pete lyons, web development, startups, music, sax, saxophone, saxophonist, sunny daze, confunktion junction, oberlin, smartears, smart ears"><meta name="author" content="Peter Lyons"><meta name="description" content="The web site for Peter Lyons, Web Developer and Musician"><meta name="copyright" content="2001, Peter Lyons"><link rel="stylesheet" href="/screen.css" type="text/css"><link href="/favicon.ico" type="image/x-icon" rel="icon"><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"><link rel="openid.server" href="http://www.livejournal.com/openid/server.bml"><link rel="openid.delegate" href="http://focusaurus.livejournal.com/"><link href="http://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet" type="text/css"><script type="text/javascript" src="/js/jquery.js"></script></head><body><div id="header"><a href="/"><img src="/images/peter_lyons_logo.png" alt="Peter Lyons" class="logo"></a></div><div id="site_nav"><div class="technology"><div class="gray">Technology</div><img src="/images/keyboard_icon_alt.png" class="keyboard_icon"><div class="nav_links"><a href="/problog">Blog</a><br><a href="/career.html">Career</a><br><a href="/hackstars.html">HackStars</a><br>Projects<br><span class="indent"><a href="/linkzie.html">Linkzie</a></span><br><span class="indent"><a href="/smartears.html">SmartEars</a></span><br><span class="indent"><a href="/bigclock.html">BigClock</a></span><br><a href="/code_conventions.html">Code Conventions</a></div></div><div class="music"><div class="gray">Music</div><img src="/images/sax_icon.png" class="sax_icon"><div class="nav_links"><a href="/bands.html">Bands</a><br><a href="/oberlin.html">Oberlin</a><br><a href="/favorites.html">Favorites</a></div></div><div class="personal"><div class="gray">Personal</div><img src="/images/peter_icon.png" class="peter_icon"><div class="nav_links"><a href="/persblog">Blog</a><br><a href="/app/photos">Photos</a></div></div><?php get_sidebar(); ?>
</div><div id="main_content"> 
