So I'm working on a new design for my site and using the super-great [stylus](http://learnboost.github.com/stylus/) CSS preprocessor along with [this great gist for simple responsive layout](https://gist.github.com/1549029) (again with stylus and the [nib](https://github.com/visionmedia/nib) library).

Well, I wanted to see which of my CSS3 media queries were in effect.  Here's a simple way to do it with the :before selector.

First, put some markup in your HTML near the top, like this (using [jade template language](https://github.com/visionmedia/jade).

    body
      .content
        header
          p BUGBUG responsive layout max-width:
          h1
            a(href="/") Peter Lyons

Then in your .styl stylesheet, use :after to tell which media query is active.

    ////////// Responsive layout //////////
    @media screen and (max-width: 960px)
      header
        p
          &:after
            content "960"

    @media screen and (max-width: 720px)
      header
        p
          &:after
            content "720"


Now load that in your browser and resize the window. The CSS will change the text in the header telling you exactly which rules are firing.  Nice!