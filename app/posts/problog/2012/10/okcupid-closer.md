I have written a little bookmarklet that will allow you to filter your [OKCupid](http://okcupid.com) matches by a smaller geographic region. By default, their search filters allow no smaller than a 25-mile radius. Drag the bookmarklet link below onto your bookmarks toolbar, then click it when you are on the OKC search page, and voilà, you will have the options for 5 and 10-mile searches. 10-mile radius will be selected, so just click "search" and you will have your results.

<a href="javascript:(function(){var%20opt5=document.createElement('option');opt5.value=5;opt5.text='5%20miles';var%20opt10=document.createElement('option');opt10.value=10;opt10.text='10%20miles';var%20radius=document.getElementById('radius');radius.insertBefore(opt10,radius.firstChild);radius.insertBefore(opt5,radius.firstChild);opt10.selected=true;var%20label=document.getElementById('location_interface_button_text');label.textContent=label.textContent.replace(/\d+/,'10');})();">OKCupid Closer Matches</a>

Some notes:

* **If you have no idea WTF a bookmarklet is or how to use one, read [this](http://support.mozilla.org/en-US/kb/bookmarklets-perform-common-web-page-tasks)**
* I live close to both Boulder and Denver Colorado. These are two quite different worlds, but they are both within 25 miles of my town. Since Denver's population is 10x the size of Boulder's, most of my search results are Denver residents, when I'm really only insterested in folks within biking distance.
* After you click search, the default search form will re-appear and it will look like your search has a 25 mile radius. Never fear, the query string has `filter3=10`, which is what is required, and based on my results, yes, the OKC servers do seem to actually respect this query string parameter even if the value is not offered in the search form.
* You need to re-click the bookmarklet for each new search. Sorry. Send feature requests to OKCupid until they add this permanently. Technically I could make this into a Chrome extension and have it always work, so I may do that at some point.
* Thanks to [Chris Zarate's handy Bookmarkleter utility](http://chris.zarate.org/bookmarkleter)
* And for you web dev nerds out there, I even wrote this with (gasp!) [Vanilla JS](http://vanilla-js.com/)
