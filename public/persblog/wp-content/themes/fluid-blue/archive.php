<?php get_header(); ?>

	<div id="content">

	<?php if (have_posts()) : ?>

	<?php /* If this is a category archive */ if (is_category()) { ?>
		<p>Archive for the &#8216;<?php echo single_cat_title(); ?>&#8217; Category.</p>

 		<?php /* If this is a tag archive */ } elseif ( function_exists ('is_tag') && (is_tag()) ) { ?>
		<p>Posts tagged &#8216;<?php echo single_tag_title(); ?>&#8217;</p>

	 <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<p>Archive for <?php the_time('jS F Y'); ?></p>

	 <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<p>Archive for <?php the_time('F Y'); ?></p>

		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<p>Archive for <?php the_time('Y'); ?></p>

	  <?php /* If this is an author archive */ } elseif (is_author()) { ?>
		<p>Author Archive</p>

		<?php } ?>
		
		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Previous Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Next Entries &raquo;') ?></div>
		</div>

		<?php while (have_posts()) : the_post(); ?>
				
			<div class="post" id="post-<?php the_ID(); ?>">
				<h2 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h2>
				<div class="postmetadata"><?php the_time(get_option('date_format').', '.get_option('time_format')) ?> <!-- <?php _e('by') ?> <?php the_author() ?> --></div>
				<div class="postentry">
					<?php the_content("<small>".__('Continue reading'). " &#8216;" . the_title('', '', false) . "&#8217; &raquo;</small>"); ?>
				</div>
		
				<div class="postmetadata">
					<?php if( function_exists('the_tags') ) 
						the_tags(__('Tags: '), ', ', '<br />'); 
					?>
					<?php _e('Category:') ?> <?php the_category(', ') ?>&nbsp;&nbsp;|&nbsp;
					<?php comments_popup_link(__('Comment'), __('1 Comment'), __('% Comments')); ?>
					<?php edit_post_link(__('Edit'), '&nbsp;|&nbsp;&nbsp;', ''); ?>
				 </div>
			</div>
	
		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link(__('&laquo; Previous Entries')) ?></div>
			<div class="alignright"><?php previous_posts_link(__('Next Entries &raquo;')) ?></div>
		</div>
		
	<?php else : ?>
		<div class="post">
			<h2 class="posttitle"><?php _e('Not Found') ?></h2>
			<div class="postentry"><p><?php _e('Sorry, no posts matched your criteria.'); ?></p></div>
		</div>

	<?php endif; ?>

	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
