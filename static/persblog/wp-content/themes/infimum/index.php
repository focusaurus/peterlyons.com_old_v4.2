<?php get_header(); ?>

<?php /* this is to deal with author pages */
if(isset($_GET['author_name'])) :
$curauth = get_userdatabylogin($author_name); // NOTE: 2.0 bug requires get_userdatabylogin(get_the_author_login());
else :
$curauth = get_userdata(intval($author));
endif;
?>

<div id="content">
	<?php if (have_posts()) : ?>
		
		<?php if (!is_single() && !is_page()) echo "<h2 class=\"pagetitle\">";
		if (is_home()) {
			echo 'Latest posts';
		} elseif (is_category()) {
			echo 'Posts categorized &#8220;' . single_cat_title('', false) . '&#8221;';
		} elseif (is_tag()) {
			echo 'Posts tagged  &#8220;' . single_tag_title('', false) . '&#8221;';
		} elseif (is_author()) {
			echo 'Posts by ' . $curauth->nickname;
		} elseif (is_day()) {
			echo 'Posts from ' . get_the_time('F jS, Y');
		} elseif (is_month()) {
			echo 'Posts from ' . get_the_time('F Y');
		} elseif (is_year()) {
			echo 'Posts from ' . get_the_time('Y');
		} elseif (is_time()) {
			echo 'Posts from a particular time on ' . get_the_time('F, jS, Y');
		} ;
		if (!is_single() && !is_page()) echo ".</h2>"; ?>
		
		<?php while (have_posts()) : the_post() ?>
			
		<?php if (is_single() || is_page()) { /* for single-item pages, make that thing big */ ?>
			<h2 class="pagetitle"><?php the_title(); ?></h2>
			
			<div class="post" id="post-<?php the_ID(); ?>">
		<?php } else { /* otherwise, make it small since you'll have one of the headings above */ ?>
			<div class="post" id="post-<?php the_ID(); ?>">
				<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
			<?php } ?>
				<div class="entry">
					<?php the_content('More... &raquo;'); ?>
				</div>
				
				<?php if (is_single() || is_page()) { ?>
					<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				<?php } ?>
				
				<p class="postmetadata">Posted by <?php the_author_posts_link(); ?> at <?php the_time('g:i a'); ?> on <?php the_time('F jS, Y')?>. <?php edit_post_link('Edit this post.', '(', ')'); ?> <?php comments_popup_link('No comments... &#187;', 'One comment... &#187;', '% comments... &#187;', 'comments-link', ''); ?><br />
				<?php /* pages don't have categories or tags */ if (!is_page()) { ?> Categories: <?php the_category(', '); ?>. <?php /* } */?>
				<?php if (get_the_tags()) the_tags('Tags: ', ', ', '.'); ?><? } ?></p>
			</div>
		
		<?php comments_template(); ?>
		
		<?php endwhile; ?>
		
		<?php if (!is_single() && !is_page()) { ?>
			<div class="navigation">
						<div class="goback"><?php next_posts_link('&laquo; Back...'); ?></div>
						<div class="goforward"><?php previous_posts_link('Forward... &raquo;'); ?></div>
			</div>
		<?php } ?>

	<?php else : ?>

		<h2>Not Found</h2>
		<p>Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
