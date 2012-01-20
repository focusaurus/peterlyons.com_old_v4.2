So [Linkzie](https://linkzie.com) has had a nice series of updates to the underlying software that runs it.  It's now pretty much bleeding edge latest and greatest.

* Ubuntu 11.10
* rbenv
* Ruby 1.9.3-p0
* Rails 3.1.3
* Unicorn 4.1.1
* jQuery 1.7

It was an interesting process.  I just went through a pretty challenging upgrade from Ruby 1.8.7 to 1.9.3 at work and it was pretty destabilizing and chaotic.  So for this one I went slow and steady, changing one thing at a time, and keeping the app running and tests passing.  First I left ruby at 1.8.7 and switched from the Ubuntu ruby to rbenv without changing anything else.  Then I upgraded some of the supporting gems like Capistrano without messing with the key gems (rails).  Then I upgraded to Rails 3.1.1 and got that working.  Then I did a carte blanche bundle update and pulled everything else up to latest.  Only then did I switch to ruby 1.9.3.

Ruby 1.9.3 still has bugs in the debugger, which I find a bit shocking, but I'm limping along with "pry" as a poor substitute.  I'm a bit shocked by the issues with Ruby 1.9.2 and 1.9.3 given how old the project is.  I don't remember any actual bugs going from Python 2.4 all the way up to 2.7, just the normal code evolution stuff.

In any case, if Linkzie had any users, in theory it might be loading and responding faster for them.  I learned a lot in the process and got my staging server VM to very closely mirror production, which is good.