<?php
$defensio_conf = array(
    'async_callback_url' =>  "$defensio_plugin_url/callback.php"
);

/* If you want to hard code the key for some reason, uncomment the following line and replace 1234567890 with your key. */
// $defensio_conf['key'] = '1234567890'; 

function defensio_set_key()
{
    global $defensio_conf;

    if (isset ($defensio_conf['key'])) {
        $defensio_conf['hckey'] = true;
    } else {
        $defensio_conf['key'] = trim(get_option('defensio_key'));
    }
}


?>