#Web Programming Concepts for Non-programmers

After this class you will know a lot more about LAMP and POST and I don't mean this.

<img src="/images/2011/lamp_post.jpg" alt="Lamp Post" width="200"/>

##Class Materials

Please bring to the class:

* a laptop with wifi
* Google Chrome web browser installed
    * [Download Google Chrome here](http://www.google.com/chrome) if you don't already have it

## Web Basics
* Internet vs. the Web
* 5-minute lightning history of the web
  * Tim Berners-Lee, Marc Andreessen
  * Mosaic, Netscape, Yahoo
  * See [the wikipedia article](http://en.wikipedia.org/wiki/History_of_the_World_Wide_Web) for more details, although it is fairly lacking.
* Browsers and servers
* URLs, Documents, HTML, Hyperlinks
* HTTP, CSS, Javascript
  * See [CSS Zen Garden](http://www.csszengarden.com/) for dramatic examples of the same HTML with vastly different CSS

## Web "Sites" Part 1
* HTML
  * Basic formatting elements
* CSS
  * Separating content from style
* Browser tools
  * View source
  * Chrome Inspector, Firebug
  * clear cache
  * view cookies, etc

## Exercise: Write a web page from scratch

    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8" />
      <title>Hey, this is my awesome HTML5 document!</title>
      <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <![endif]-->
    </head>

    <body id="home">

      <h1>Hello, Web!</h1>

    </body>
    </html>

* Exercise steps
  * bold
  * paragraphs
  * link to another site
* Reference
  * [w3schools interactive HTML tutorial](http://www.w3schools.com/html/)
  * [w3schools interactive CSS tutorial](http://www.w3schools.com/css/)
## Exercise: Add some Cascading Style Sheets

    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8" />
      <title>HTML5 boilerplate—all you really need…</title>
      <style type="text/css"  />
        p {
          font-color: red;
          font-size: 3em;
        }
      </style>
      <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <![endif]-->
    </head>

    <body id="home">

      <h1>HTML5 boilerplate</h1>
      <p>This is a big red paragraph</p>

    </body>
    </html>


## Web "Sites" Part 2
* Flash
  * games
  * multimedia
  * terrible restaurant sites
* Plugins
* Audio
## Web Applications
* Dynamically generated HTML
* Sessions, Cookies
* Databases
* CGI
* Application Servers
* Web Servers
* Forms
* HTTP Methods
* APIs, XML, JSON
## Web Application Stacks
* [Wikipedia article summarizing Web Application Frameworks](http://en.wikipedia.org/wiki/Web_application_framework)
* Microsoft
  * Windows Server
  * .NET Framework
  * [Microsoft Server Software Catalog](http://www.microsoft.com/servers/en/us/default.aspx)
  * Internet Information Services (IIS) ([official site](http://www.iis.net/))
  * [ASP.NET](http://www.asp.net/)
* [LAMP](http://en.wikipedia.org/wiki/LAMP_(software_bundle))
  * Linux, Apache, MySQL, PHP
* CakePHP
* Droopal
* Joomla
* Django (Python)
* Ruby on Rails (Ruby)
* Perl/CGI
* Google Web Toolkit (GWT)
* Java/J2EE

## Security and Privacy
* TLS and encryption vs clear text
* Cookies, cross domain rules
* SQL injection
* XSS
## HTML5 and modern web applications
* The Document Object Model - [DOM](http://www.w3.org/DOM/)
  * Official API specification for representing a document on the web
* AJAX
* Javascript, V8, SpiderMonkey
* HTML5 semantic elements
* jQuery

## Exercise: Manipulate a web page's DOM using JavaScript
* Open [the jQuery home page](http://jquery.org/)
* Click on View -> Developer -> JavaScript Console

_

    $('#jq-header').hide()
    $('#jq-header').show()

## Search Engine Optimization
