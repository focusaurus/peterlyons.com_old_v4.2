<?php
$defensio_widget_tones = Array('dark', 'light');

function defensio_render_counter_html($v) { ?>
	<style type="text/css" media="screen">
		#defensio_counter              { width: 100%; margin: 10px 0 10px 0; }
		#defensio_counter_image        { background:url('<?php echo $v['plugin_uri'] ?>images/defensio-counter-<?php echo $v['color'] ?>.gif') no-repeat top left; border:none; font: 10px 'Trebuchet MS', 'Myriad Pro', sans-serif; overflow: hidden; text-align: left; height: 50px; width: 120px; }

		#defensio_counter.center       { text-align: center; }
		#defensio_counter.left         { text-align: left; }
		#defensio_counter.right        { text-align: right; }

		#defensio_counter_image.center { margin: 0 auto; }
		#defensio_counter_image.left   { float: left; }
		#defensio_counter_image.right  { float: right; }

		#defensio_counter_link         { text-decoration: none; }
		#defensio_counter_image span   { display:block; width: 100px; padding: 9px 9px 25px 12px; line-height: 1em; color: #211d1e; }
		#defensio_counter_image span strong { font-size: 12px; font-weight: bold; }
		.defensio_clear                { clear:both; }

		#defensio_counter_image span.dark_counter   { color: #fff; }
		#defensio_counter_image span.light_counter  { color: #211d1e; }
	</style>

	<div id='defensio_counter' class="<?php echo $v['align'] ?>">
		<a id="defensio_counter_link" href="http://defensio.com?platform=wp">
			<div id='defensio_counter_image' class="<?php echo $v['align'] ?>">
				<span class="<?php echo $v['color'] ?>_counter"><strong><?php echo $v['smoked_spam'] ?></strong> spam comments blocked</span>
			</div>
			<div class='defensio_clear'></div>
		</a>
	</div>
<?php
}
?>