=== Defensio Anti-Spam ===
Contributors: karabunga
Donate link: http://defensio.com/
Tags: comments, spam, antispam, defensio
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: 2.5.3
 
Defensio is an advanced spam filtering web service that learns and adapts to your behaviors and those of your readers.
 
== Description ==
 
Defensio is an advanced spam filtering web service that learns and adapts to your behaviors and those of your readers.
 
Advanced features such as support for OpenID, detailed statistics, charts, RSS feed of our comments (innocent and spam)
and counter widget are also available.
 
Defensio aims to be an all-in-one anti-spam solution.  Therefore, using it along with other anti-spam plugins WILL
cause problems.  PLEASE deactivate Akismet and other similar plugins before activating Defensio.
 
 
 
== Installation ==
 
1. IMPORTANT:  Deactivate Akismet and/or any other anti-spam plugins by going to your Plugins tab in the WordPress 
   control panel. Simply click "deactivate" to deactivate the desired plugins.
   
   Using multiple spam-filtering solutions will negatively impact Defensio's performance.
   
   Note: If are upgrading from an older version of Defensio, please deactivate the old plugin before proceeding with
   the upgrade.  Moreover, please delete the old Defensio plugin directory from your server.  Not doing so could lead
   to version conflicts.
   
2. Copy the 'defensio-anti-spam' folder (unzipped from the file you downloaded) into 'wp-content/plugins/'.  You
   should now have a file called 'defensio.php' in 'wp-content/plugins/defensio-anti-spam/'.
   
3. Return to the Plugins tab in your control panel and activate Defensio.
 
4. Under the Plugins tab, you will find a new tab called Defensio Configuration.  Go there and enter your API key.  
   If the key turns green, this means it has been successfully validated. If you don't have an API key, you can
   obtain one for free at [http://defensio.com/signup](http://defensio.com/signup).
 
   Under Defensio Configuration, you may also set the threshold level for "obvious spam" (this can be hidden from 
   your spambox, at your option).  We recommend you leave this "obvious spam" level at its default value (80%) until
   you get a feel for what a better value might be for your own purposes.  You may also choose to enable automatic 
   deletion of spam on old articles, though we do do not recommend this by default.
 
   Note: If you're upgrading from an older version of Defensio, the API key should be pre-populated for you. 
   
*  Your blog is now protected by Defensio's enhanced spam filtering web service.  From now on, all comments posted 
   to your blog will be analyzed by Defensio and attributed a "spaminess" value that will help you sort and manage
   your Quarantine.  Under your blog's Comments tab, notice that the quarantine has been re-labelled "Defensio Spam".
   Go there experience the Defensio goodness first-hand.
 
 
 
== OpenID Support == 
 
If you wish to enable OpenID support, you must also install another plugin called WP-OpenID.  It can be found at
[http://wordpress.org/extend/plugins/openid/](http://wordpress.org/extend/plugins/openid/).
 
 
 
== Spread the Defensio Love! ==
 
Love Defensio?  Spread the word by featuring how many spam comments Defensio filtered with the Defensio Counter!
 
If your Wordpress theme supports widgets, simply add the Defensio Counter widget to your sidebar.  You can set color
and alignment options.
 
If your theme does NOT support widgets, all hope is not lost.  You can simply edit your 'sidebar.php' file and add 
the following code snippet where you want the counter to appear:
 
<?php defensio_counter(); ?>
 
By default, the counter is dark grey and left aligned.  However, you can customize your counter by passing arguments
as follow:
 
<?php defensio_counter('color', 'align'); ?>
 
You can replace 'color' by 'dark' or 'light' and you can replace 'align' by 'left', 'right', 'center'.
 
If you are familiar with basic image manipulation, CSS and HTML, you'll find it very easy to create your very own
Defensio counter.  To do so, you only need to modify lib/defensio_counter.php and put a custom .gif image file in
the images directory.  Be careful to follow the naming convention we already have in place.
 
 
 
== Screenshots ==
 
1. The standard Defensio Quarantine view, sorted and color-coded by spaminess.
 
2. The recent accuracy chart.

3. Advanced settings.
 

== Compatibility ==

Defensio does not work with Wordpress 2.9 due to a known Wordpress bug (http://core.trac.wordpress.org/ticket/11505).
This bug has been fixed in Wordpress 2.9.1, so make sure to update your installation before using Defensio.

 
== Frequently Asked Questions ==
 
Visit our FAQ at [http://defensio.com/faq](http://defensio.com/faq)
 
 
 
== Need Help? ==
 
Visit our user forum at [http://forum.defensio.com](http://forum.defensio.com)