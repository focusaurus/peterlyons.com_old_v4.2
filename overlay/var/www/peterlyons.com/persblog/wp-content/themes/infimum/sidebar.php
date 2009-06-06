<div id="sidebar">
	<ul>
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
		
		<li id="search">
			<form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
				<div>
					<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />
					<input type="submit" id="searchsubmit" value="Search" />
				</div>
			</form>
		</li>
		
		<?php wp_list_pages('title_li=<h2>Pages</h2>'); ?>
		
		<li id="archives">
			<h2>Archives</h2>
			<ul>
				<?php wp_get_archives('type=monthly'); ?>
			</ul>
		</li>
		
		<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>
		
		<?php wp_list_bookmarks(); ?>
		
		<?php endif; ?>
	</ul>
</div>