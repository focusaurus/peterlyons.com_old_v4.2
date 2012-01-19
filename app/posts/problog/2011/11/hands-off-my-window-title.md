So I recently ssh'ed into a shiny new Ubuntu 11.10 server on EC2 and noticed some new things.  Firstly, Ubuntu seems to have enabled the [byobu](https://help.ubuntu.com/11.10/serverguide/C/byobu.html) terminal multiplexer configuration by default.  This looks potentially handy, but just like [Oh my zsh](https://github.com/robbyrussell/oh-my-zsh) I don't feel motivated to futz with it just now.  It's easy to disable byobu with just a quick <code>byobu-disable</code>, which will, somewhat surprisingly, fully exit your shell, log you out and close your ssh session.  But next time you'll get a normal shell instead of a byobu/screen session.

Now one thing I noticed and finally got motivated with to "research" (and by that I mean ask smarter people on twitter) and fix was that when I connected via ssh, the window title in my iTerm2 tab was dynamically changed from what I had carefully set it to ("asset pipeline") to the supremely unhelpful "ubuntu@ip-10-11-12-13".  In some non-cloud situations where the server has a meaningful hostname, this might be handy, but distinguishing dozens of servers by their internal EC2 IP is not appealing to me.  Initial research suggested my <code>PROMPT</code> or <code>PROMPT_COMMAND</code> environment variables, but neither was set.  I read through [this DeveloperWorks article](http://www.ibm.com/developerworks/linux/library/l-tip-prompt/) and found the responsible code in the <code>~/.bashrc</code> file.  Here's the offending excerpt.

    # If this is an xterm set the title to user@host:dir
    case "$TERM" in
    xterm*|rxvt*)
        PS1="\[\e]0;${debian_chroot:+($debian_chroot)}\u@\h: \w\a\]$PS1"
        ;;
    *)
        ;;
    esac

So you can comment out that line that changes <code>PS1</code> if you prefer your own manually-set window title.