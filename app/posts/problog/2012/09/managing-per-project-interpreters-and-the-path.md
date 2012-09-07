So let's talk about managing runtimes and interpreters for projects and applications. This post comes about after I have seen a vast jungle of non-solutions and dead ends out there for managing installations of interpreters such as Python, Ruby, node.js, etc. First, let's clear the air of a bunch of nonesense you may find out there that makes this problem confusing.

## Never use the interpreter provided by your operating system

If you are building a web application or any other project that should by all rights be cross-platform and doesn't ship with OS, then you should have absolutely nothing to do with the interpreter that may be included with the OS. The OS interpreters are there for components of the OS itself written in those languages. They have no business being used by third party projects and applications that run on top of the OS as opposed to being part of the OS. Forget about the Ruby that comes with OS X. Forget about the Python that comes with Ubuntu. Forget about the Debian packages for node.js (slightly different/better, but still, ignore them).

The reasoning behind this guideline is as follows.

* **Exact version**: Applications need exact and strict control of the version of their interpreter. You should be using the exact same version of your interpreter across all of your development, test, staging, and production environments. This will avoid problems which are easily-avoidable, so do it.
* **Modern version**: OSes tend to ship versions of these interpreters that are significantly behind the latest stable version. New applications should be written to work with the latest stable version and should keep up with ongoing releases, never getting more than 3 months behind.
* **Independence** Applications need independence from one another. If you have 3 Django projects on the same machine, each one needs to have the ability to use whatever interpreter **version** it needs on its own independent **schedule**. Due to this fact, that means the correct location for these interpretters is within your application's directory alongside your application code, which is why I advise you to ignore the node.js debian packages you may find out there because it installs into a shared location, which does not meet our goals here.

## Keep the app-specific interpreter within the application install directory

Again, don't let the OS's notion of shared interpreters in a shared location distract you from the right layout here. The app-specific interpreter installation belongs inside you project's installation directory.

* `project_root/python`
* `project_root/node`
* `project_root/ruby`

Basically, the old school unix principles have gone stale on us. Years ago, sysadmins had rules for filesystem layout with different goals. For example, sysadmins wanted to be able to NFS mount a shared directory where binaries could live, be maintained in a single place, and be mounted and run by many additional servers. They wanted to do this to be efficient with disk space and to be able to make manual changes in one place and have them to affect immediately on an arbitrary number of servers that use the same NFS volume.

Now we use automated tools to manage deployments to clusters of independent servers, and disk space is cheap, so we want each server to have its own copy of what it needs to run with as few external dependencies as possible. We want to be able to do rolling deploys across a cluster or run 1/2 the cluster on the new code and half on the old code.  Disk space is cheap and plentiful, so if we have 5 or 10 apps running on the same staging server, we could not care less about a few megabytes of duplication to handle a bunch of python installations.

## Installing local versions of node.js

Now that Joyent is shipping pre-compiled binaries, installing node interpreters in the manner described here is a snap. Here's an `install_node.sh` script. Pass it the version you want and where you want it installed (works with node >= 0.8).

    #!/bin/sh
    VERSION=${1-0.8.8}
    PREFIX=${2-node}
    PLATFORM=$(uname | tr A-Z a-z)
    ARCH=x64
    case $(uname -p) in
        i686)
            ARCH=x86
        ;;
    esac
    if [ -e "${PREFIX}" ]; then
        TS=$(date +%Y%m%d-%H%M%S)
        echo "WARNING: Moving existing file at ${PREFIX} to ${PREFIX}-previous-${TS}" 1>&2
        mv "${PREFIX}" "${PREFIX}-previous-${TS}"
    fi
    mkdir -p "${PREFIX}"
    curl --silent \
      "http://nodejs.org/dist/v${VERSION}/node-v${VERSION}-${PLATFORM}-${ARCH}.tar.gz" \
      | tar xzf - --strip-components=1 -C "${PREFIX}"

## Never use npm -g

This is a follow up to my [earlier blog post about avoiding npm -g](/problog/2011/12/no-need-for-npm-g), now improved and revised. For the most part, I believe [npm](https://npmjs.org/) to be the state-of-the-art package management system and to be superior to the messes available for python and ruby. However, the `-g` switch, which installs commands `globally`, should be avoided in favor of the system described here. You don't want to have to upgrade all your express.js apps at once, so give them each their own copy of the `express` script.

## Provide a single script to launch your application commands

Encapsulate each version with a wrapper shell script that understands the project directory layout and manages your PATH appropriately. I tend to call this file `project_root/do` but `project_root/bin/tasks.sh` or similar are good locations for this. This script should handle your service operations like start, stop, reload, etc, as well as any one-off commands you make have like clearing a cache, regenering static files, and so forth.

Here's a snippet of my `project_root/do` script which locates the correct installation of python and fabric and passes control to them.

    #!/bin/sh -e
    cd $(dirname "${0}")
    exec ./python/bin/fab "${@}"

Thus I can run this script from any directory, or from an init/upstart script, with any PATH, and the application correctly handles its own required settings. The above is the bare bones and the crux of the separation of concerns in the design. I normally have some other code in there to bootstrap the project's dependencies, but I'll save that topic for another blog post.

##For local development, manage your PATH intelligently and automatically

As you work on many projects which contain their own interpreter installations, you don't want to always have to A) work from the project root directory and B) run commands like `./python/bin/python myapp.py`. So here are some utilities that can intelligently manage your PATH similar to what is done by [rbenv](https://github.com/sstephenson/rbenv), but not tied to ruby and based on you changing project directories.

First, here's how I set up my `PATH` in my `~/.zshrc` file (works equally well for bash or bourne shell). I've added extra explanatory comments inline.

    #This helper function will add a directory to the PATH if it exists
    #This is a simple way to handle different machines, OSes, and configurations
    addPath() {
        if [ -d "${1}" ]; then
            if [ -z "${PATH}" ]; then
                export PATH="${1}"
            else
              export PATH=$PATH:"${1}"
            fi
        fi
    }

    setupPath() {
        #Start with an empty PATH
        PATH=
        #Local pwd stuff
        addPath "${PWD}/script"
        addPath "${PWD}/bin"
        #For node
        addPath "${PWD}/node_modules/.bin"
        addPath "${PWD}/node/bin"
        #For python virtualenvs
        addPath "${PWD}/python/bin"

        #Personal home dir stuff
        addPath ~/bin
        #For rbenv
        addPath ~/.rbenv/bin
        addPath ~/.cabal/bin
        #Homebrew
        addPath ~/Library/Python/2.7/bin
        addPath /usr/local/share/python
        addPath /usr/local/bin
        #XCode/Developer
        addPath /Developer/usr/bin
        #Normal system stuff
        addPath /bin
        addPath /usr/bin
        addPath /sbin
        addPath /usr/sbin
        addPath /usr/X11/bin
    }
    #Run this during shell startup. Can be re-run as needed manually as well
    setupPath

OK, so that's how the `PATH` gets built up, but we want to change the PATH as we move our current working directory between projects. For that we use a shell hook function. What this does is try to detect if we've changed into a project directory, and if so, rebuild the `PATH`, which will put our project-specific directories early in the `PATH` list, so when we type `node` or `python` or `coffee`, etc, we get the project specific one under the project root. Because this adds absolute paths and only changes the `PATH` when we `cd` to a project root, we can cd to subdirectories within the project and still be running the correct project-specific interpreter. This does breakdown, however, if you cd directly into a project subdirectory without stopping in the project root. I don't hit that problem because I'm not in the habit of doing that, but YMMV. Here's the zsh version, which uses the [chpwd](http://www.refining-linux.org/archives/42/ZSH-Gem-8-Hook-function-chpwd/) hook function.

    if [ -n "${ZSH_VERSION}" ]; then
        chpwd() {
            [ -d .git -o \
              -d  node_modules/.bin -o \
              -d python/bin -o \
              -d node/bin ] && setupPath
        }
    fi

Bash users, [you're on your own](http://stackoverflow.com/questions/3276247/is-there-a-hook-in-bash-to-find-out-when-the-cwd-changes).

Here's an example of this at work.

    ~-> cd projects/peterlyons.com
    ~/projects/peterlyons.com-> which node
    /Users/plyons/projects/peterlyons.com/node/bin/node
    ~/projects/peterlyons.com-> cd ../craft
    ~/projects/craft-> which node
    /Users/plyons/projects/craft/node/bin/node
    ~/projects/craft-> cd ../othenticate.com
    ~/projects/othenticate.com-> which node
    /Users/plyons/projects/othenticate.com/node/bin/node
    ~/projects/othenticate.com-> cd ../m-cm/indivo_provision
    ~/projects/m-cm/indivo_provision-> which python
    /Users/plyons/projects/m-cm/indivo_provision/python/bin/python
    ~/projects/m-cm/indivo_provision-> cd ./conf
    ~/projects/m-cm/indivo_provision/conf-> which python
    /Users/plyons/projects/m-cm/indivo_provision/python/bin/python

## (Bonus item) Keep variable files for data and logging under your project directory

* `project_root/var/log`
* `project_root/var/data`


This is a mindset shift from traditional unix administration best practices. It's in my opinion a less complex and more application-centric design that makes better sense given our focus on applications that tend to be providing network services and generally are less tightly coupled to the underlying OS these days. Traditional unix administration (as documented in the [Filesystem Heirarchy Standard](http://www.pathname.com/fhs/)) has a strong and system-wide distinction that runtime variable data like data files and log files go under `/var` and everything else except for `/home` and `/tmp` is static data. Again, this no longer applies to modern applications. These rules had to do with preventing key filesystems from filling up, primarily. They wanted application data to be static and allocate a certain amount of space that had separate filesystem limits from the variable data, which they wanted organized centrally under `/var` so they could manage log file growth and space disk space centrally. There were reasons for these designs at the time that made sense given the constraints and goals, but times have changed.
