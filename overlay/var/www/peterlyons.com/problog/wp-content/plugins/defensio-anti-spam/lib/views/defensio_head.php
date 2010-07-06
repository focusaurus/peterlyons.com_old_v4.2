<?php
function defensio_render_html_head($v) {
?>

<style type="text/css" media="screen">

</style>

<?php } 


function defensio_render_warning_styles() {
?>
	<style type="text/css" media="screen">
		#adminmenu { margin-bottom: 6em; }
    #adminmenu.large { margin-bottom: 8.5em; }
    #defensio_warning { position: absolute; top: 11.5em; }
		#defensio_warning_controls_wrap { width:auto; margin-bottom:3px; display:none; }
		#defensio_warning p.defensio_error { color: red; }
		#defensio_warning p.defensio_success { color: green; }
		#defensio_progress_bar { width:300px; height:16px; border:1px solid black; padding:2px; float:left; margin-bottom:10px; }
		#defensio_progress_bar_value { width:0%; height:16px; background:blue; }
		#defensio_spinner { padding: 2px 10px 0 10px; float: left;}
		#defensio_start_processing { margin-left: 10px; clear:both; }
		#defensio_stop_processing { clear:both; }
	</style>
<?php
}

?>