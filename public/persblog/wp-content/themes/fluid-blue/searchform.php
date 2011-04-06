<form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
<input type="text" size="20" name="s" id="s" value="<?php _e('Search') ?>..."  onblur="if(this.value=='') this.value='<?php _e('Search') ?>...';" onfocus="if(this.value=='<?php _e('Search') ?>...') this.value='';"/>
</form>
