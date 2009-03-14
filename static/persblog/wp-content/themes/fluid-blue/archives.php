<?php
/*
Template Name: Archives
*/
?>

<?php get_header(); ?>

<div id="content">
<div class="post">
<h2><?php _e('Archives') ?></h2>

<h3><?php _e('Archives by Month') ?></h3>
<ul>
    <?php wp_get_archives('type=monthly'); ?>
</ul>
<p>&nbsp;</p>
<h3><?php _e('Archives by Subject') ?></h3>
<ul>
     <?php wp_list_cats(); ?>
</ul>

<?php if( function_exists('wp_tag_cloud') ) { ?>
<p>&nbsp;</p>
<h3><?php _e('Tags') ?></h3>
<?php wp_tag_cloud(); ?>
<?php } ?>

</div>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
