I had an interesting thought about programming languages and complexity yesterday.  I think of languages like Python and CoffeeScript (and even C <sup>[1](#endnote1)</sup> aim to be small and consistent as a language.  Things like Perl and Ruby are much more complex.  I have been studying both Ruby and CoffeeScript for a while  (I think about a year for Ruby and maybe 6 months for CoffeeScript guessing from unreliable memory), and I feel like my JavaScript/CoffeeScript knowledge is solid and with Ruby I'm mired in intermediate status. However, the fact is I write lots of code in each of these languages.  So what does that indicate?

It seems to me that the more complex your language, the larger portion of the total extant codebase written in that language will have been authored by programmers that do not completely understand the language.  I would venture that if you took all existing Perl code, about 80% of it was written by programmers or sysadmins with cursory knowledge of the language.  And within the Perl community, finding truly competent programmers with solid mastery of the language is definitely the exception, not the rule.  I think the combination of ruby's largeness and complexity coupled with the huge popularity of rails means the same is true for Ruby.  But I don't think there's anything about Perl or Ruby that makes it this way other than just it's the nature of having complexity in your language.

I'm solidly in the camp of tiny, simple languages with programs authored by programmers with mastery, and this post has been interesting food for thought for me on this topic.

<hr>

<aside id="endnote1">
1: End Note on C

While C as a language is usually considered small and simple, the POSIX interfaces you need are so old and devilishly tricky that I wouldn't trust C code written by beginner or intermediate C programmers.
</aside>
