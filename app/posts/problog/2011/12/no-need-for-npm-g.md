**UPDATE:** please see my new article on [Managing Per-project Interpreters and the PATH](/problog/2012/09/managing-per-project-interpreters-and-the-path) for a new and improved take on this topic.

---

So [npm](http://npmjs.org/) has this "-g" switch to install "global" packages that bundle command line executable scripts.  I've been on a strict project isolation kick lately after dealing with rbenv in the ruby world, and I just don't see any need for `npm -g`.  I want each project to have its own version of node, coffeescript, mocha, or whatever else I need.  Here's my principles for a harmonious multi-project system.

## 1. Install things under your project root

Node goes in `project/node`.

Install npm modules without `-g`.  `coffee` becomes `project/node_modules/.bin/coffee`. `mocha` becomes `project/node_modules/.bin/mocha`. And so on.

## 2. Set your PATH

Add `./node/bin:./node_modules/.bin` and to your `PATH`.

## Done
Here's an example.

    $ mkdir project1 project2
    $ cd project1
    $ npm install coffee-script@1.0.1
    coffee-script@1.0.1 ./node_modules/coffee-script 
    $ which coffee
    ./node_modules/.bin/coffee
    $ coffee --version
    CoffeeScript version 1.0.1
    $ cd ../project2
    $ npm install coffee-script@1.2.0
    coffee-script@1.2.0 ./node_modules/coffee-script 
    $ which coffee
    ./node_modules/.bin/coffee
    $ coffee --version
    CoffeeScript version 1.2.0

Same principle works for `node` and any scripts you get from npm modules.

While we're talking about `PATH`, here's how I set my `PATH` in my `~/.zshrc`.  It's nice because I can throw a bunch of crap in there that may or may not exist on any given computer and only directories that exist get into my `PATH`.

    ########## PATH ##########
    PATH=
    addPath() {
      if [ -d "${1}" ]; then
        export PATH=$PATH:"${1}"
      fi
    }
    addPath ./node_modules/.bin
    addPath ./node/bin
    #Repeat addPath lines for each directory...
    export PATH

## Security Caveat About Relative Paths in PATH

Having relative directory paths in your `PATH` is arguably a [security vulnerability](http://developer.apple.com/library/mac/#documentation/opensource/conceptual/shellscripting/ShellScriptSecurity/ShellScriptSecurity.html). [See also slide 20 here](https://www.securecoding.cert.org/confluence/pages/worddav/preview.action?pageId=3524&fileName=Environment+Variables+v3.pdf).  I'm not personally too concerned about this one for my personal interactive login shell.  However, this practice is probably not suitable for shell scripts that are run as programs.  Also note that in a production deployment you should launch your `node` or `coffee` executable via an absolute path when coding your [upstart](http://upstart.ubuntu.com/) or SysV init script.
