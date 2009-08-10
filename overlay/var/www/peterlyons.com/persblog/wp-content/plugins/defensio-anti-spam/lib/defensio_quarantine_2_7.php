<?php

function defensio_render_quarantine_html($v) { 
	global $wp_version;  ?>

	<div class="wrap">
		<?php defensio_render_header_form($v); ?>
 		<?php //defensio_render_view_switch($v); ?>
		<form method="post" id="comments-form">
  		<?php defensio_render_navigation_bar($v, "top"); ?>
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
  		<?php defensio_render_navigation_bar($v, "bottom"); ?>
		</form>
		<br class="clear" /><br class="clear" />
		<?php defensio_render_stats($v); ?>
	</div>
<?php	}



function defensio_render_header_form($v) { 

	$current_sort = defensio_current_sorting($v);
?>
	<h2>Defensio Spam</h2>
	
	<form id="posts-filter" method="post">
<?php
		defensio_render_subsubsub();
?>
		<p class="search-box">
			<label class="hidden" for="comment-search-input">Search Spam:</label>
			<input id="post-search-input" class="search-input" type="text" value="" name="search"/>
			<input class="button-primary" type="submit" value="Search Spam"/>
		</p>
	</form>

<?php }

function defensio_render_subsubsub() { ?>
	<ul class="subsubsub">
	<?php
	$status_links = array();
	$num_comments = wp_count_comments();
	$stati = array(
	                'moderated' => sprintf(__ngettext('Pending (%s)', 'Pending (%s)', number_format_i18n($num_comments->moderated) ), "<span class='comment-count'>" . number_format_i18n($num_comments->moderated) . "</span>"),
	                'approved' => _c('Approved|plural'),
	                'spam' => sprintf(__ngettext('Spam (%s)', 'Spam (%s)', number_format_i18n($num_comments->spam) ), "<span class='spam-comment-count'>" . number_format_i18n($num_comments->spam) . "</span>")
	        );
	$class = ( '' === $comment_status ) ? ' class="current"' : '';
	$status_links[] = "<li><a href=\"edit-comments.php\"$class>".__('Show All Comments')."</a>";
	$type = ( !$comment_type && 'all' != $comment_type ) ? '' : "&amp;comment_type=$comment_type";
	foreach ( $stati as $status => $label ) {
	        $class = '';

	        if ( $status == $comment_status )
	                $class = ' class="current"';

	        $status_links[] = "<li class='$status'><a href=\"edit-comments.php?comment_status=$status$type\"$class>$label</a>";
	}

	$status_links = apply_filters( 'comment_status_links', $status_links );

	echo implode(' | </li>', $status_links) . '</li>';
	unset($status_links);
	?>
	</ul>
<?php
}



function defensio_render_navigation_bar($v, $position) {
	if ($v['spaminess_filter'] == '1' )
		$hide_obvious_checked = " checked=\"1\"";
	else
		$hide_obvious_checked = "";	

	$comments_per_page = apply_filters('comments_per_page', 20, "spam");
	if ( isset( $_GET['apage'] ) )
		$page = abs( (int) $_GET['apage'] );
	else
		$page = 1;

	$start = $offset = ( $page - 1 ) * $comments_per_page;
	
	$page_links = defensio_page_navigation_links($v);
	// TODO: make page links work.  See edit-comment.php around line 169
////////$page_links = paginate_links( array(
////////        'base' => add_query_arg( 'apage', '%#%' ),
////////        'format' => '',
////////        'prev_text' => __('&laquo;'),
////////        'next_text' => __('&raquo;'),
////////        'total' => ceil($total / $comments_per_page),
////////        'current' => $page
////////));
	
?>
	<div class="tablenav">
		<?php
		if ( $page_links )
			echo "<div class='tablenav-pages'>$page_links</div>";
		?>

		<div class="alignleft actions">
			<select name="<?php if($position == 'top') echo "action"; else echo "action2"; ?>">
				<option selected="selected" value="-1">Actions</option>
				<option value="restore">Mark as innocent</option>
				<option value="delete">Delete</option>
				<option value="emptyquarantine">Empty quarantine</option>
			</select>

		<?php if($position == 'top'): ?>
			<input id="doaction" class="button-secondary apply" type="submit" value="Apply" name="doaction" />
		<?php else: ?>
			<input id="doaction2" class="button-secondary apply" type="submit" value="Apply" name="doaction2" />
		<?endif;?>


			<?php wp_nonce_field('bulk-comments'); ?>
<?php
			if($position=="top") {
?>
				<select name="comment_type">
					<option <?php if( $v['type_filter'] == "all") echo 'selected="1"'; ?> value="all">Show all comment types</option>
					<option <?php if( $v['type_filter'] == "comments") echo 'selected="1"'; ?> value="comments">Comments</option>
					<option <?php if( $v['type_filter'] == "pings") echo 'selected="1"'; ?> value="pings">Pings</option>
				</select>
				<input id="post-query-submit" class="button-secondary" type="submit" value="Filter"/>

				<select name="sort_by">
					<option <?php if($v['order']== 'spaminess'  ) echo 'selected="1"' ; ?> value="spaminess">Sort by spaminess</option>
					<option <?php if($v['order']== 'post_date' ) echo 'selected="1"' ; ?> value="post_date">Sort by post date</option>
					<option <?php if($v['order']== 'comment_date' ) echo 'selected="1"' ; ?> value="comment_date">Sort by comment date</option>
				</select>
				<input id="post-query-submit" class="button-secondary" type="submit" value="Sort"/>
			</div>
		
			<div class="alignright actions">
				<input type="checkbox" id="hide_obvious_spam" name="hide_obvious_spam" <?php echo $hide_obvious_checked; ?> onclick="javascript:this.form.submit();" /> 
				<label for="hide_obvious_spam">Hide Obvious Spam (<?php echo $v['obvious_spam_count']; ?>)</label> 
				<input type="hidden" name="hide_obvious_spam_toggle" value="1" />
<?php } ?>

		</div>
		
<!--	

TODO: update page links
		<div class="tablenav-pages">
			<?php echo  defensio_page_navigation_links($v); ?>
		</div>
-->
<!--
		<div class="alignleft">
 			<input type="submit" class="button-secondary select-all" name="select_all" value="Select All" />
			<input type="submit" class="button-secondary" name="defensio_restore" value="Restore" />
			<input type="submit" class="button-secondary" name="defensio_delete" value="Delete" />
			<input type="submit" class="button-secondary empty-quarantine" name="defensio_empty_quarantine" value="Empty Quarantine" />
		</div>
-->
		<br class="clear" />
	</div>
<?php }


function defensio_wp_comment_row( $c, $mode, $checkbox = true) {
        global $comment, $post;
	$comment = $c;
	$spaminess_class = defensio_class_for_spaminess($comment->spaminess);
        $post = get_post($comment->comment_post_ID);
        $authordata = get_userdata($post->post_author);
	

        if ( current_user_can( 'edit_post', $post->ID ) ) {
                $post_link = "<a href='" . get_edit_post_link($post->ID) . "'>";
                $post_link .= get_the_title($comment->comment_post_ID) . '</a>';
        } else {
                $post_link = get_the_title($comment->comment_post_ID);
        }

        $author_url = get_comment_author_url();
        if ( 'http://' == $author_url )
                $author_url = '';
        $author_url_display = $author_url;
        if ( strlen($author_url_display) > 50 )
                $author_url_display = substr($author_url_display, 0, 49) . '...';

        $ptime = date('G', strtotime( $comment->comment_date ) );
        if ( ( abs(time() - $ptime) ) < 86400 )
                $ptime = sprintf( __('%s ago'), human_time_diff( $ptime ) );
        else
                $ptime = mysql2date(__('Y/m/d \a\t g:i A'), $comment->comment_date );

        $delete_url = clean_url( wp_nonce_url( "comment.php?action=deletecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "delete-comment_$comment->comment_ID" ) );
        $approve_url = clean_url( wp_nonce_url( "comment.php?action=approvecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "approve-comment_$comment->comment_ID" ) );
        $unapprove_url = clean_url( wp_nonce_url( "comment.php?action=unapprovecomment&p=$comment->comment_post_ID&c=$comment->comment_ID", "unapprove-comment_$comment->comment_ID" ) );
        $spam_url = clean_url( wp_nonce_url( "comment.php?action=deletecomment&dt=spam&p=$comment->comment_post_ID&c=$comment->comment_ID", "delete-comment_$comment->comment_ID" ) );

        echo "<tr id='comment-$comment->comment_ID' class='spam $spaminess_class'>";
        $columns = get_column_headers('edit-comments');
	error_log(print_r($columns, true));
        
        $hidden = (array) get_user_option( 'manage-comment-columns-hidden' );
        foreach ( $columns as $column_name => $column_display_name ) {
                $class = "class=\"$column_name column-$column_name\"";

                $style = '';
                if ( in_array($column_name, $hidden) )
                        $style = ' style="display:none;"';

                $attributes = "$class$style";

                switch ($column_name) {
                        case 'cb':
                                if ( !$checkbox ) break;
                                echo '<th scope="row" class="check-column">';
                                if ( current_user_can('edit_post', $comment->comment_post_ID) ) echo "<input type='checkbox' name='delete_comments[]' value='$comment->comment_ID' />";
                                echo '</th>';
                                break;
                        case 'comment':
                                echo "<td $attributes>"; 

				echo '<div id="submitted-on">';
				printf(__('Submitted on <a href="%1$s">%2$s at %3$s</a>'), get_comment_link($comment->comment_ID), get_comment_date(__('Y/m/d')), get_comment_date(__('g:ia')));
				echo '</div>';


?>
																
																<p class="defensio_body_shrunk" id="<?php echo "defensio_body_" . $comment->id; ?>">
																	<?php echo ($comment->comment_content) ?>
																</p>

                                <div id="inline-<?php echo $comment->comment_ID; ?>" class="hidden">
                                <textarea class="comment" rows="3" cols="10"><?php echo $comment->comment_content; ?></textarea>
                                <div class="author-email"><?php echo attribute_escape( $comment->comment_author_email ); ?></div>
                                <div class="author"><?php echo attribute_escape( $comment->comment_author ); ?></div>
                                <div class="author-url"><?php echo attribute_escape( $comment->comment_author_url ); ?></div>
                                <div class="comment_status"><?php echo $comment->comment_approved; ?></div>
                                </div>
                                <?php
                                $actions = array();

                                if ( current_user_can('edit_post', $comment->comment_post_ID) ) {
																			  $actions['expand'] = "<a class='defensio_quarantine_action' id='defensio_view_full_comment_" . $comment->comment_ID . "' href='#' onclick=\"javascript:defensio_toggle_height('" . $comment->id . "');return false;\">View full comment</a>";
                                        $actions['approve'] = "<a href='$approve_url' class='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=approved vim-a' title='" . __( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
                                        $actions['approve'] = "<a href='$approve_url' class='delete:the-comment-list:comment-$comment->comment_ID:e7e7d3:action=dim-comment vim-a vim-destructive' title='" . __( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
                                        $actions['delete'] = "<a href='$delete_url' class='delete:the-comment-list:comment-$comment->comment_ID delete vim-d vim-destructive'>" . __('Delete') . '</a>';
                                        $actions['edit'] = "<a href='comment.php?action=editcomment&amp;c={$comment->comment_ID}' title='" . __('Edit comment') . "'>". __('Edit') . '</a>';
                                        //$actions['quickedit'] = '<a onclick="commentReply.open(\''.$comment->comment_ID.'\',\''.$post->ID.'\',\'edit\');return false;" class="vim-q" title="'.__('Quick Edit').'" href="#">' . __('Quick&nbsp;Edit') . '</a>';
																				$actions['details'] = "<span id='defensio_more_details_" . $comment->id . "' class='defensio_more_details'><a href='#' onclick=\"javascript:$('defensio_more_details_" . $comment->id . "').removeClassName('defensio_more_details').update('Signature: $comment->signature | Spaminess: " . number_format($comment->spaminess * 100, 0) . "%');return false;\">Details</a></span>";
                                        $actions = apply_filters( 'comment_row_actions', $actions, $comment );

					echo "<div class=\"row-actions\">";
                                        $i = 0;
                                        foreach ( $actions as $action => $link ) {
                                                ++$i;
                                                ( ( ('approve' == $action || 'unapprove' == $action) && 3 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';

                                                // Reply and quickedit need a hide-if-no-js span
                                                if ( 'reply' == $action || 'quickedit' == $action )
                                                        $action .= ' hide-if-no-js';
	                                                echo "<span class='$action'>$sep$link</span>";
                                        }
					echo "</div>";
                                }

                                echo '</td>';
                                break;
                        case 'author':
                                echo "<td $attributes><strong>"; comment_author(); echo '</strong><br />';
                                if ( !empty($author_url) )
                                        echo "<a href='$author_url'>$author_url_display</a><br />";
                                if ( current_user_can( 'edit_post', $post->ID ) ) {
                                        if ( !empty($comment->comment_author_email) ) {
                                                comment_author_email_link();
                                                echo '<br />';
                                        }
                                        echo '<a href="edit-comments.php?s=';
                                        comment_author_IP();
                                        echo '&amp;mode=detail">';
                                        comment_author_IP();
                                        echo '</a>';
                                } //current_user_can
                                echo '</td>';
                                break;
                        case 'date':
                                echo "<td $attributes>" . get_comment_date(__('Y/m/d \a\t g:ia')) . '</td>';
                                break;
                        case 'response':
                                if ( 'single' !== $mode ) {
                                        echo "<td $attributes>\n";
                                        echo "&quot;$post_link&quot; ";
                                        echo '<a href="edit-comments.php?p=' . $post->ID;
                                        if ( !empty($_GET['comment_type']) ) echo '&amp;comment_type=' . htmlspecialchars( $_GET['comment_type'] );
                                        echo '">' . sprintf ( __ngettext('(%s comment)', '(%s comments)', $post->comment_count), $post->comment_count ) . '</a><br />';
                                        echo get_the_time(__('Y/m/d \a\t g:ia'));
                                        echo '</td>';
                                }
                }
        }
        echo "</tr>\n";
}





function defensio_render_spam_list($v) {
	$valid_orders  = array('spaminess', 'post_date', 'comment_date');
	$order = $v['order'];

	if(!in_array($v['order'], $valid_orders)){
	  $order = 'spaminess';
	}
	
	$function_name  = "defensio_render_spam_list_sorted_by_" . $order ; 
	?>
	<table class="widefat fixed">
		<thead>
			<tr>
				<th id="cb" class="manage-column column-cb check-column" style="" scope="col"><input type="checkbox"/></th>
				<th id="author" class="manage-column column-author" style="" scope="col">Author</th>
				<th id="comment" class="manage-column column-comment" style="" scope="col">Comment</th>
				<th id="response" class="manage-column column-response" style="" scope="col">In Response To</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="manage-column column-cb check-column" style="" scope="col"><input type="checkbox"/></th>
				<th class="manage-column column-author" style="" scope="col">Author</th>
				<th class="manage-column column-comment" style="" scope="col">Comment</th>
				<th class="manage-column column-date" style="" scope="col">In Response To</th>
			</tr>
		</tfoot>
		
		<tbody id="the-comment-list" class="list:comment">
		
		</tbody>
		<?php call_user_func($function_name, $v); ?>
	</table>
	<?php
}

function defensio_render_group_header($title) {
	//echo "<li><ul class='defensio_comment_group'><li class='defensio_post_title'>  $title </li>";
	echo "<tr class='defensio_comment_group'><td colspan=4>$title</td></tr>";
}

function defensio_render_spam_list_sorted_by_spaminess($v) {
	$current_group = NULL;
	foreach($v['comments'] as $spam_comment) {
		if ($current_group != defensio_spaminess_level($spam_comment->spaminess)) {
			$current_group = defensio_spaminess_level($spam_comment->spaminess);
			defensio_render_group_header($current_group);
		}
		defensio_wp_comment_row($spam_comment, "detail", "spam", true);
	}
	
}

function defensio_render_spam_list_sorted_by_post_date($v) {
	$current_group = NULL;
	
	foreach($v['comments'] as $spam_comment) {
		if ($current_group != $spam_comment->comment_post_ID) {
			$current_group = $spam_comment->comment_post_ID;
			defensio_render_group_header( $spam_comment->post_title ." <span>(" . strftime("%B %d, %Y", strtotime($spam_comment->post_date)  ) . ")</span>" );
		}
		defensio_wp_comment_row($spam_comment, "detail", "spam", true);
	}
}

function defensio_render_spam_list_sorted_by_comment_date($v) {
	$current_group = NULL;
	foreach($v['comments'] as $spam_comment) {
		if ($current_group != strftime("%B %d,%Y", strtotime($spam_comment->comment_date))) {
			$current_group = strftime("%B %d,%Y", strtotime($spam_comment->comment_date));
			defensio_render_group_header($current_group);
		}
		defensio_wp_comment_row($spam_comment, "detail", "spam", true);
	}
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


function defensio_page_navigation_links($v){
	return paginate_links( array(
		'base' => add_query_arg('defensio_page', '%#%'),
		'format' => '',
		'total' => defensio_page_count($v['spam_count'], $v['items_per_page']),
		'current' => $v['current_page']
	));
	
}


?>
