<?php // Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if (!empty($post->post_password)) { // if there's a password
		if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
			?>

			<p class="nocomments">This post is password protected. Enter the password to view comments.</p>

			<?php
			return;
		}
	}

	/* This variable is for alternating comment background */
	$oddcomment = 'even';
?>

<!-- You can start editing here. -->

<div id="comments">
<?php if ($comments) : ?>
	<h3><?php comments_number(__('Comments'), __('One Comment'), __('% Comments') );?></h3>

	<ol class="commentlist">

	<?php foreach ($comments as $comment) : ?>

		<li class="comment <?php echo $oddcomment; ?>" id="comment-<?php comment_ID() ?>">
		<div id="div-comment-<?php comment_ID() ?>">
		<div class="comment-author">
	<?php // Gravatar code
		if(function_exists('get_avatar')) echo get_avatar( $comment, 48 );
	?>
		<cite><?php comment_author_link() ?>:<cite></div>
			<?php comment_text() ?>
			<?php if ($comment->comment_approved == '0') : ?>
			<em>(<?php _e('Comment awaits moderation') ?>)</em>
			<?php endif; ?>
			<div class="comment-meta"><a href="#comment-<?php comment_ID() ?>" title=""><?php comment_date(get_option('date_format')) ?>, <?php comment_time(get_option('time_format')) ?></a>
			<?php edit_comment_link(__('Edit'),'&nbsp;|&nbsp;&nbsp;',''); ?></div></div>
		</li>

	<?php
		/* Changes every other comment to a different class */
		$oddcomment = ( $oddcomment == 'odd' ) ? 'even' : 'odd';
	?>
	<?php endforeach; /* end for each comment */ ?>

	</ol>

 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ('open' == $post->comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->

	<?php endif; ?>
<?php endif; ?>

<?php if ('open' == $post->comment_status) : ?>

<h3 id="respond"><?php _e('Leave a comment') ?></h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<p><?php _e('Logged in as') ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account"><?php _e('Logout') ?> &raquo;</a></p>

<?php else : ?>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"><small><?php _e('Name') ?> <?php if ($req) _e('(required)'); ?></small></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><small><?php _e('E-Mail') ?> (<?php _e('will not be published'); ?>) <?php if ($req) _e('(required)'); ?></small></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><small><?php _e('Website') ?></small></label></p>

<?php endif; ?>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->

<p><textarea name="comment" id="comment" rows="10" cols="" tabindex="4"></textarea></p>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>
<?php endif; // if you delete this the sky will fall on your head ?>
</div>

