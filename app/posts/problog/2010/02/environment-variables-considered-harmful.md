Many projects reference environment variables at either build time, install time, or run time to handle configuration that can't be made to work across all of the target environments.  It is better to use plain text simple configuration files for the reasons that follow.  First, let's quickly review common usage of environment variables.

* Directory path to supporting tools and libraries (JAVA_HOME, LD_LIBRARY_PATH, CATALINA_HOME, etc)
* Customization of build time locations (BUILD_DIR, OUTPUT_DIR, DIST_DIR, etc)
* Customization of compiler options and other build time configurations (STATIC_LINK, etc)
* Settings that apply OS-wide and to several programs (http_proxy, etc). In theory this would almost make sense.  You set your http_proxy environment variable in one place, and any program that makes HTTP requests respects that setting.  In practice, these settings are more realistically effective higher up in your desktop environment, and AFAIK in the whole GNU/Linux/UNIX ecosystem, there are only a small handful of cross-program environment variables that are actually used commonly.

So what's the problem with environment variables?

* The are ephemeral, nebulous, stored in memory within your shell and process tree
* How and where they are set is inconsistent across shells (~/.bash_profile, ~/.zshrc, etc)
* The syntax to specify them is needlessly different across different shells (csh vs. bash vs. cmd.exe, etc)
* How to fully unset them varies per shell and is often unclear
* There is widespread confusion on the distinction between shell variables and environment variables, how to set each, and how each interacts with subprocesses
* They are often tied to a user account due to where they are specified above, and can vary between login shell verses non-login shell. They can therefore often vary when a program runs via init compared to run from an interactive root login shell.  This can be difficult to detect and troubleshoot
* They are rife with [major security concerns](https://www.securecoding.cert.org/confluence/pages/worddav/preview.action?pageId=3524&fileName=Environment+Variables+v3.pdf) and a common attack vector

All of these reasons combined mean that in general environment variables are losers in our goal of managing complexity and making simple, easy to use software that is cross platform.  So what's the solution?  The solution, as it so often is, is simple plain text configuration files.  At the end of the day, environment variables end up set in a shell script as KEY=VALUE type pairs, and that's where they belong in a configuration file on the filesystem. How does this make things better?

* One consistent place to set your application's configuration
* Same syntax regardless of shell, programming language or OS
* Files on disk are concrete and reliable. You can email it to someone for help with troubleshooting and be confident about its content

So go forth and configure with simple plain text configuration files.  And there will be much rejoicing.
