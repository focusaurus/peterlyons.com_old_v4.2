<?php
function defensio_render_js_event_manager() {
?>
  <script>
  $$('.select-all').each(function(elm){elm.observe('click', SelectAll)} );
  $$('.empty-quarantine').each(function(elm){elm.observe('click', EmptyQuarantine)} );

  var  select_all_clicks = -1;
  function SelectAll(evt) {
  	select_all_clicks ++;
  	$$('.defensio_comment_checkbox').each(function(chbx, index){
  		if(select_all_clicks %2 == 0)
  			chbx.checked = true;
  		else
  			chbx.checked = false;
  	})
  		Event.stop(evt);
  }

  function EmptyQuarantine(evt){
  	if(confirm('You are about to delete all the spam comments from your quarantine.  Are you sure?')){
      // Do it
  		} else {
  		Event.stop(evt);
  		}
  }
  </script>
<?php
}

function defensio_render_quarantine_html($v) { 
	global $wp_version;  ?>

	<div class="wrap">
		<?php defensio_render_header_form($v); ?>
 		<?php //defensio_render_view_switch($v); ?>
		<form method="post">
  		<?php defensio_render_navigation_bar($v); ?>
  		<?php 
  		if($v['spam_count'] > 0):
  			defensio_render_spam_list($v); 
  		else: ?>
  	    <br />
  	    <p id="defensio_quarantine_empty"> 
          <?php if($v['type_filter'] == 'trackbacks')
  			    echo "Your quarantine doesn't contain any spam pingbacks or trackbacks.";
  		    elseif($v['type_filter'] == 'comments')
  			    echo "Your quarantine doesn't contain any spam comments.";
  		    else 
  			    echo "Your quarantine doesn't contain any spam.";

  		    if($v['spaminess_filter'] and isset($v['obvious_spam_count']) and $v['obvious_spam_count'] > 0)
  			    echo " However, you are hiding " . $v['obvious_spam_count'] . " obvious spam messages.";
          ?>
        </p>
  		<?php endif;?>
  		<?php defensio_render_navigation_bar($v); ?>
		</form>
		<br class="clear" /><br class="clear" />
		<?php defensio_render_stats($v); ?>
		<?php defensio_render_js_event_manager(); ?>
	</div>
<?php	}



function defensio_render_header_form($v) { 
	if ($v['spaminess_filter'] == '1' )
		$hide_obvious_checked = " checked=\"1\"";
	else
		$hide_obvious_checked = "";	

	$current_sort = defensio_current_sorting($v);
	$sort_by_spaminess_link = htmlspecialchars(add_query_arg('sort_by', 'spaminess'));
	$sort_by_post_date_link = htmlspecialchars(add_query_arg('sort_by', 'post_date'));
	$sort_by_comment_date_link = htmlspecialchars(add_query_arg('sort_by', 'comment_date'));
	$sort_by_spaminess_class = ($current_sort == 'spaminess' ? "current" : "");
	$sort_by_post_date_class = ($current_sort == 'post_date' ? "current" : "");
	$sort_by_comment_date_class = ($current_sort == 'comment_date' ? "current" : "");
?>
	<form id="posts-filter" method="post">
		<h2>Defensio Spam</h2>

		<p class="hide_obvious_spam">
				<input type="checkbox" id="hide_obvious_spam" name="hide_obvious_spam" <?php echo $hide_obvious_checked; ?> onclick="javascript:this.form.submit();" /> 
			        <input type="hidden" name="hide_obvious_spam_toggle" />
			<label for="hide_obvious_spam">
				Hide Obvious Spam (<?php echo $v['obvious_spam_count']; ?>)
			</label>
		</p>
	
		<ul class="subsubsub">
			<li>Sort by:</li>
			<li><a class="<?php echo $sort_by_spaminess_class; ?>"    href="<?php echo $sort_by_spaminess_link; ?>">Spaminess</a> | </li>
			<li><a class="<?php echo $sort_by_post_date_class; ?>"    href="<?php echo $sort_by_post_date_link; ?>">Post Date</a> | </li>
			<li><a class="<?php echo $sort_by_comment_date_class; ?>" href="<?php echo $sort_by_comment_date_link; ?>">Comment Date</a></li>
		</ul>

		<p id="post-search">
			<input id="post-search-input" type="text" value="" name="search"/>
			<input class="button" type="submit" value="Search Spam"/>
		</p>
	</form>

<?php }


function defensio_render_navigation_bar($v) { ?>
 	<?php defensio_render_view_switch($v); ?>
	<div class="tablenav">
		<div class="tablenav-pages">
			<?php defensio_render_page_navigation_links($v); ?>
		</div>
		
		<div class="alignleft">
 			<input type="submit" class="button-secondary select-all" name="select_all" value="Select All" />
			<input type="submit" class="button-secondary" name="defensio_restore" value="Restore" />
			<input type="submit" class="button-secondary" name="defensio_delete" value="Delete" />
			<input type="submit" class="button-secondary empty-quarantine" name="defensio_empty_quarantine" value="Empty Quarantine" />
		</div>

		<br class="clear" />
	</div>
<?php }



function defensio_render_view_switch($v) {
	$current_type = defensio_current_view_type();
	$all_link = htmlspecialchars(add_query_arg(array('type' => 'all')));
	$comments_link = htmlspecialchars(add_query_arg(array('type' => 'comments')));
	$trackbacks_link = htmlspecialchars(add_query_arg(array('type' => 'trackbacks')));
	$comments_class =   ($current_type == 'comments'   ? "current" : "");
	$trackbacks_class = ($current_type == 'trackbacks' ? "current" : "");
	$all_class =        ($current_type == "all"        ? "current" : "");
?>
	<ul class="view-switch" style="margin-top:0;">
		<li class="<?php echo $all_class; ?>"><a href="<?php echo $all_link; ?>">All</a></li>
		<li class="<?php echo $comments_class; ?>"><a href="<?php echo $comments_link; ?>">Comments</a></li>
		<li class="<?php echo $trackbacks_class; ?>"><a href="<?php echo $trackbacks_link; ?>">Ping/Trackbacks</a></li>
	</ul>
<?php }



function defensio_render_spam_list($v) {
	$valid_orders  = array('spaminess', 'post_date', 'comment_date');
	$order = $v['order'];

	if(!in_array($v['order'], $valid_orders)){
	  $order = 'spaminess';
	}
	$function_name  = "defensio_render_spam_list_sorted_by_" . $order ; 
	?>
	<ul class="defensio_comments">
		<?php call_user_func($function_name, $v); ?>
	</ul>
	<?php
}

function defensio_render_group_header($title) {
	echo "<li><ul class='defensio_comment_group'><li class='defensio_post_title'>  $title </li>";
}

function defensio_render_group_footer() {
	echo "</li></ul>";
}


function defensio_render_comment($comment) { 
	$spaminess_class = defensio_class_for_spaminess($comment->spaminess);
	$li_id = "defensio_comment_" . $comment->id;
	$checkbox_id = "defensio_comments[]";
	$body_id = "defensio_body_" . $comment->id;
        
?>
	

	<li class='<?php echo $spaminess_class; ?>' id='<?php echo $li_id; ?>'>
		<div class="defensio_comment_checkbox">
			<input type="checkbox" name="<?php echo $checkbox_id; ?>" id="<?php echo $checkbox_id; ?>" class="defensio_comment_checkbox" value="<?php echo $comment->comment_ID; ?>"  />
		</div>
		<p class="defensio_comment_header comment-author" style="margin:0">
			<span class="row-title">
				<?php 
				echo(get_avatar($comment, 32));
				echo( "<strong>" .  $comment->comment_author . "</strong>");
				?>
			</span><br />
			<?php
			if ($comment->comment_author_email != '') {
				echo '<a href="mailto:' . $comment->comment_author_email . '">' . $comment->comment_author_email . '</a>';
			}
			if($comment->comment_author_url && $comment->comment_author_url != 'http://' ) { 
			  if ($comment->comment_author_email != '')
				  echo "&nbsp;|&nbsp;"; 

        echo "<a class=\"defensio_author_url\" href=\"" . $comment->comment_author_url . "\">" . $comment->comment_author_url . "</a>";
			}
?>
		</p>

		<p class="defensio_body_shrunk" id="<?php echo $body_id; ?>">
			<?php echo nl2br($comment->comment_content) ?>
		</p>
		
		<p class="defensio_comment_meta">
			<a class="defensio_quarantine_action" id="defensio_view_full_comment_<?php echo $comment->id?>" href="#" onclick="javascript:defensio_toggle_height('<?php echo $comment->id?>');return false;">View full comment</a> | 
			<a class="defensio_quarantine_action" href="#" onclick="javascript:setTimeout('$(\'defensio_comment_<?php echo $comment->id ?>\').remove()', 1000); Fat.fade_element('defensio_comment_<?php echo $comment->id ?>',30,1000,  '#70b94c'  , '#fff');    new Ajax.Request('<?php echo get_option('siteurl')."/wp-admin/admin-ajax.php" ?>', {parameters: { cookie: document.cookie, action: 'defensio-restore', ham: <?php echo $comment->id ?> } });return false;">Restore comment</a> | 
			<?php echo("Spaminess: " . number_format($comment->spaminess * 100, 0) . "% | "); ?>
			<?php echo($comment->comment_date) . " | " ?>
			Post: <a href="<?php echo get_permalink($comment->comment_post_ID); ?>" title="<?php echo $omment->post_title; ?>"><?php echo $comment->post_title; ?></a>
			<span id="defensio_more_details_<?php echo $comment->id?>" class="defensio_more_details">
			  <a href="#" onclick="javascript:$('defensio_more_details_<?php echo $comment->id?>').removeClassName('defensio_more_details').update(' | Signature: <?php echo $comment->signature?>');return false;">+</a>
			</span>

		</p>
	</li>
<?php
}

function defensio_render_spam_list_sorted_by_spaminess($v) {
	$current_group = NULL;
	
	foreach($v['comments'] as $spam_comment) {
		if ($current_group != defensio_spaminess_level($spam_comment->spaminess)) {
			if ($current_group != NULL) 
				defensio_render_group_footer();
			$current_group = defensio_spaminess_level($spam_comment->spaminess);
			defensio_render_group_header($current_group);
		}
		
		defensio_render_comment($spam_comment);
	}
	
	defensio_render_group_footer();
}

function defensio_render_spam_list_sorted_by_post_date($v) {
	$current_group = NULL;
	
	foreach($v['comments'] as $spam_comment) {
		if ($current_group != $spam_comment->comment_post_ID) {
			if ($current_group != NULL) 
				defensio_render_group_footer();
			$current_group = $spam_comment->comment_post_ID;
			defensio_render_group_header( $spam_comment->post_title ." <span>(" . strftime("%B %d, %Y", strtotime($spam_comment->post_date)  ) . ")</span>" );
		}
		
		defensio_render_comment($spam_comment);
	}
	
	defensio_render_group_footer();
}

function defensio_render_spam_list_sorted_by_comment_date($v) {
	$current_group = NULL;
	foreach($v['comments'] as $spam_comment) {
		if ($current_group != strftime("%B %d,%Y", strtotime($spam_comment->comment_date))) {
			if ($current_group != NULL) 
				defensio_render_group_footer();
			$current_group = strftime("%B %d,%Y", strtotime($spam_comment->comment_date));
			defensio_render_group_header($current_group);
		}
		
		defensio_render_comment($spam_comment);
	}
	
	defensio_render_group_footer();
}


function defensio_render_stats($v) { 
if($v['stats'] and $v['authenticated']) :
?>
	<h2>Statistics</h2>
	<div class="defensio_stats">

<?php 		if(isset($v['stats']['learning']) and $v['stats']['learning'] == true ) { ?>
				<h3 class="defensio_learning"><?php echo $v['stats']['learning-status'] ?></h3>
<?php		} ?>
				<ul>
					<li><strong>Recent accuracy: <?php echo number_format( $v['stats']['accuracy'] * 100, 2, '.', '')  ?>%</strong></li>
					<li><?php echo $v['stats']['spam']?> spam</li>
					<li><?php echo $v['stats']['ham']?> legitimate comments</li>
					<li><?php echo $v['stats']['false-negatives']?> false negatives (undetected spam)</li>
					<li><?php echo $v['stats']['false-positives']?> false positives (legitimate comments identified as spam)</li>
				</ul>
		
        </div>
	<div class="defensio_more_stats">
		<h3>There's more!</h3>
			<p>For more detailed statistics (and gorgeous charts), please visit your Defensio <a href="http://defensio.com/manage/stats/<?php echo $v['api_key']?>" target="_blank">Account Management</a> panel.</p>
	</div>

	<div style="clear:both"></div>
<?php 
	else:
?>
		<p>Statistics could not be retrieved, please check back later.</p>
<?php
	 endif;
}


function defensio_render_page_navigation_links($v){
	$page_links = paginate_links( array(
		'base' => add_query_arg('defensio_page', '%#%'),
		'format' => '',
		'total' => defensio_page_count($v['spam_count'], $v['items_per_page']),
		'current' => $v['current_page']
	));
	echo "$page_links";
}


?>
