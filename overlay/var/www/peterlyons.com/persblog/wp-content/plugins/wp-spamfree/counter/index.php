<?php
// This page keeps search engines and unwanted visitors from viewing your private plugin directory contents.

// The following sends an error code to search engine bots so they won't index this page. 
header("HTTP/1.1 403 Forbidden");

/* 
You can avoid the need for pages like this by adding a single line of code to the beginning of your .htaccess file:

## Add the following line to the beginning of your .htaccess
Options All -Indexes
## This will turn off indexes so your site won't reveal contents directories that don't have an index file.

*/
?>