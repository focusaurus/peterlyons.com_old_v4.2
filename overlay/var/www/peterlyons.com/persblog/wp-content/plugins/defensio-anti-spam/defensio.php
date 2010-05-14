<?php
/*
 * Plugin Name: Defensio Anti-Spam
 * Plugin URI: http://defensio.com/
 * Description: Defensio is an advanced spam filtering web service that learns and adapts to your behaviors as well to those of your readers and commenters.  To use this plugin, you need to obtain a <a href="http://defensio.com/signup">free API Key</a>.  Tell the world how many spam Defensio caught!  Just put <code>&lt;?php defensio_counter(); ?&gt;</code> in your template.
 * Version: 2.5.3
 * Author: Websense, Inc.
 * Author URI: http://defensio.com
 *
 */

global $defensio_plugin_dir, $defensio_plugin_url, $defensio_conf;
$defensio_plugin_dir = WP_PLUGIN_DIR .'/'. basename(dirname(__FILE__));
$defensio_plugin_url = WP_PLUGIN_URL .'/'. basename(dirname(__FILE__));

require_once('lib/defensio-php/Defensio.php');
require_once('lib/DefensioDB.php');
require_once('lib/DefensioWP.php');
require_once('lib/defensio_utils.php');
require_once('config.php');
require_once('lib/views/defensio_configuration.php');
require_once('lib/views/defensio_quarantine.php');
require_once('lib/views/defensio_head.php');
require_once('lib/views/defensio_counter.php');

if (!function_exists('wp_nonce_field') ) {
    function defensio_nonce_field($action = -1) { return; }
        $defensio_conf['nonce'] = -1;
} else {
    function defensio_nonce_field($action = -1) { return wp_nonce_field($action); }
        $defensio_conf['nonce'] = 'defensio-update-key';
}

// Initialize arrays for deferred training
$defensio_retraining  = false;

/* 
 * Installation function, creates the Defensio table and populate it with default options
 */
function defensio_install() {
    defensio_create_table();
    add_option(defensio_user_unique_option_key('threshold') , '80');
    add_option(defensio_user_unique_option_key('hide_more_than_threshold'), '1');
    add_option('defensio_delete_older_than_days', '30');
    add_option('defensio_delete_older_than', '0');
    add_option('defensio_profanity_do', 'off');
}
register_activation_hook(__FILE__ , 'defensio_install');

/*
 * Creates Defensio table in MySQL
 */
function defensio_create_table() {
    $version = get_option('defensio_db_version');

    if (DefensioDB::createTable(DefensioDB::getTableName(), $version))
        update_option('defensio_db_version', DefensioDB::TABLE_VERSION);
}

/* 
 * Init hook. Instantiate DefensioDB and DefensioWP to access Defensio's REST service, and make sure the wp_defensio table 
 * is in the database. If not, create it.
 */
function defensio_init() {
    global $defensio_conf, $defensio_db, $defensio_manager;
    add_action('admin_menu', 'defensio_config_page');

    defensio_set_key();
    $defensio_db      = new DefensioDB();
    $defensio_manager = new DefensioWP($defensio_conf['key'], $defensio_conf['async_callback_url']);

    defensio_create_table();
}
add_action('init', 'defensio_init');


function defensio_styles() {
    global $defensio_plugin_url;

    wp_register_style('defensio', "$defensio_plugin_url/styles/defensio.css");
    wp_enqueue_style('defensio');
}
add_action('admin_print_styles', 'defensio_styles');

function defensio_key_not_set_warning() {
    global $defensio_conf;

    if (!isset($defensio_conf['key']) or empty($defensio_conf['key'])) {
        echo "<div id='defensio_warning' class='updated fade'>" .
             "<p><strong>Defensio is not active</strong> because you have not entered your Defensio API key. " .
             " <a href='http://defensio.com/signup' target='_blank'>Get one right here!</a></p></div>";
    }
}
add_action('admin_notices', 'defensio_key_not_set_warning');

function defensio_unprocessed_warning() {
    global $defensio_db;
    $unprocessed_count = count($defensio_db->getUnprocessedComments());

    if ( $unprocessed_count > 0 ) {
        echo "<div id='defensio_warning' class='updated fade'>" .
            "<p>There are <strong>$unprocessed_count</strong> comment(s) that have not been processed by Defensio. </p>".
            "<p>There might be a connectivity issue between you and Defensio. Your comments will automatically processed within the next 10 minutes, or when connectivity is restored.</p></div>";
    }
}
add_action('admin_notices', 'defensio_unprocessed_warning');

function defensio_fsock_warning() {

    if ( !is_callable('fsockopen') || in_array('fsockopen', explode(',',  ini_get('disable_functions'))) ) {
        echo "<div id='defensio_warning' class='updated fade'>" .
            "<p>The administrator has disabled PHP\'s <code>fsockopen</code> on your server. <strong>Defensio cannot work correctly until this function is enabled.</strong>  Please contact your web hosting provider and ask them to enable it.</p></div>";
    }
}
add_action('admin_notices', 'defensio_fsock_warning');

function defensio_config_page() {
    global $defensio_conf;

    if (function_exists('add_submenu_page')) {
        add_submenu_page('plugins.php', __('Defensio Configuration'), __('Defensio Configuration'), 'manage_options', 'defensio-config', 'defensio_configuration');
        add_submenu_page('options-general.php', __('Defensio Configuration'), __('Defensio'), 'manage_options', 'defensio-config', 'defensio_configuration');
    }
}

function defensio_configuration() {
    global $defensio_conf, $defensio_manager;

    $key = NULL;
    $err_code = NULL;

    if (isset($_POST['new_key'])) {
        check_admin_referer( $defensio_conf['nonce']);
        $key = trim($_POST['new_key']);
        $defensio_conf['key'] = $key;
    }

    if (isset($defensio_conf['key'])) {

        if ($defensio_manager->verifyKey($defensio_conf['key'], $err_code)) {
            $valid = true;
            update_option('defensio_key', $defensio_conf['key']);
        } else {
            $valid = false;
        }

        $key = $defensio_conf['key'];
    }

    if (isset($_POST['new_threshold'])) {
        $t = (int)$_POST['new_threshold'];

        if (0 <= $t and $t <= 100) {
            update_option(defensio_user_unique_option_key('threshold'), $t );
        }
    } 

    if (!$defensio_conf['hckey']) {
        $defensio_conf['hckey'] = false;
    }

    $older_than_error = '';
    $minimum_days = 15;

    if (isset($_POST['defensio_remove_older_than_toggle'])) {
        if (isset($_POST['defensio_remove_older_than']) and (isset($_POST['defensio_remove_older_than_days']) and ((int) $_POST['defensio_remove_older_than_days'] > $minimum_days))) {
            update_option('defensio_delete_older_than', '1');
            update_option('defensio_delete_older_than_days', (int) $_POST['defensio_remove_older_than_days']);
        } else {
            update_option('defensio_delete_older_than', '0');

            if (isset($_POST['defensio_remove_older_than_days']) and ((int)$_POST['defensio_remove_older_than_days'] < $minimum_days)) {
                $older_than_error = 'Days has to be a numeric value greater than '.$minimum_days;

            } elseif (isset($_POST['defensio_remove_older_than_days']) and ((int) $_POST['defensio_remove_older_than_days'] > $minimum_days)) {
                update_option('defensio_delete_older_than_days', (int) $_POST['defensio_remove_older_than_days']);
            }
        }

    } else {
        if ((isset($_POST['defensio_remove_older_than_days']) and ((int) $_POST['defensio_remove_older_than_days'] > $minimum_days) )) {
            update_option('defensio_delete_older_than_days', (int) $_POST['defensio_remove_older_than_days']);
        } elseif($_POST['defensio_remove_older_than_days'] > $minimum_days ){
            $older_than_error = 'Days has to be a numeric value greater than '.$minimum_days;
        }
    }

    $threshold = get_option(defensio_user_unique_option_key('threshold'));

    if(empty($threshold))
        $threshold  = 80;

    if(isset($_POST['defensio_profanity_do'])){
        update_option('defensio_profanity_do', $_POST['defensio_profanity_do'] );
    }

    $profanity_do = get_option('defensio_profanity_do');

    if(!in_array($profanity_do, array('off', 'mask', 'delete')))
        $profanity_do = 'off';

    defensio_render_configuration_html(array(
        'key'           => $key, 
        'hckey'         => $defensio_conf['hckey'], 
        'threshold'     => $threshold,
        'nonce'         => $defensio_conf['nonce'],
        'valid'         => $valid,
        'remove_older_than_days'   => get_option('defensio_delete_older_than_days'),
        'remove_older_than'        => get_option('defensio_delete_older_than'),
        'profanity_do'         => $profanity_do,
        'remove_older_than_error'  => $older_than_error,
        'defensio_post_error_code' => $err_code
    ));
}

function defensio_update_db($opts = null){
    global $defensio_conf, $defensio_retraining, $defensio_db;

    if($opts == null or !is_array($opts))
        return false;

    if (function_exists('current_user_can') && !current_user_can('moderate_comments')) {
        die(__('You do not have sufficient permission to moderate comments.'));
    }

    // Single message to restore
    if(isset ($opts['ham'])) {
        $id = (int) $opts['ham'];
        defensio_set_status_approved($id);
    }

    // Many messages to process
    if (isset ($opts['defensio_comments'])) {
        // Restore
        if (isset ($opts['defensio_restore'])) {
            foreach ($opts['defensio_comments'] as $id ) {
                $id = (int)$id;
                defensio_set_status_approved($id);
            }
        }

        // Delete
        if (isset ($opts['defensio_delete'])) {
            foreach ($opts['defensio_comments'] as $k ) {
                $k = (int) $k;
                $defensio_db->deleteCommentAndDefensioRow($k);
            }
        }
    }

    // Empty spam box, delete all 
    if (isset($opts['defensio_empty_quarantine'])) {
        $defensio_db->deleteAllSpam();
    }

}

// Prepare messages to be displayed in the quarantine
function defensio_caught( $opts = null ) {
    global $defensio_conf, $defensio_retraining, $defensio_manager, $defensio_db, $plugin_uri;
    $page = 1;

    if($opts == null or !is_array($opts))
        return false;

    if (isset ($opts['page']) or empty ($opts['page'])) {
        if ((int) $opts['page'] < 2) {
            $page = 1;
        } else {
            $page = (int) $opts['page'];
        }
    } 

    // In case further ordering is needed
    $order = null;

    // A new ordering requested? update ordering creterion
    if ( isset($opts['sort_by']) && !empty ($opts['sort_by'])) {
        if ($opts['sort_by'] == 'comment_date') {
            $order = 'comment_date';

        } elseif ($opts['sort_by'] == 'post_date') { 
            $order = 'post_date';

        } else {
            $order = 'spaminess';
        }

        update_option(defensio_user_unique_option_key('order'), $order);
    } else {
        // no request? get the ordering from options.
        $order = get_option(defensio_user_unique_option_key('order'));

        if($order == null){
            $order = 'spaminess';
            update_option(defensio_user_unique_option_key('order'), $order);
        }
    }

    // Hide obvious spam
    if (isset($opts['hide_obvious_spam_toggle'])) {
        $opt_name = defensio_user_unique_option_key('hide_more_than_threshold');

        if (isset($opts['hide_obvious_spam']))
            update_option($opt_name, '1');
        else
            update_option($opt_name, '0');
    }

    $type = '';
    if (isset($opts['type']) && $opts['type'] != 'all' )
        $type = trim($opts['type']);

    $query_param = $opts['search']; 
    $comments    =  $defensio_db->getQuarantineComments($page, $opts['items_per_page'], $order, $type, $query_param );
    $spam_count  = count($comments);

    if (trim($order) == 'comment_date' || trim($order) == 'post_date')
        $order_param = trim($order);
    else
        $order_param = 'spaminess';

    $err_code = NULL;

    return array(
        'comments'          => $comments,
        'current_page'      => $page,
        'type_filter'       => $opts['type'],
        'spam_count'        => $defensio_db->unhiddenSpamCount(),
        'items_per_page'    => $opts['items_per_page'],
        'order'             => $order_param,
        'search_query'      => $query_param,
        'spaminess_filter'  => get_option(defensio_user_unique_option_key('hide_more_than_threshold')),
        'nonce'             => $defensio_conf['nonce'],
        'stats'             => $defensio_manager->getStats(),
        'obvious_spam_count' => $defensio_db->obviousSpamCount(),
        'authenticated'     => $defensio_manager->verifyKey($defensio_conf['key'], $err_code),
        'plugin_uri'        => $plugin_uri,
        'api_key'           => $defensio_conf['key']
    );
}

function defensio_dispatch()
{
    global $defensio_conf, $defensio_retraining;

    if (function_exists('current_user_can') && !current_user_can('moderate_comments')) {
        die(__('You do not have sufficient permission to moderate comments.'));
    }

    $db_req = array( 
        'ham'                       =>  $_GET['ham'],
        'defensio_comments'         =>  $_POST['delete_comments'],
        'defensio_empty_quarantine' => ($_POST['action'] == 'emptyquarantine'||  $_POST['action2'] == 'emptyquarantine') ? true : NULL,
        'defensio_restore'          => ($_POST['action'] == 'restore'        ||  $_POST['action2'] == 'restore'        ) ? true : NULL,
        'defensio_delete'           => ($_POST['action'] == 'delete'         ||  $_POST['action2'] == 'delete'         ) ? true : NULL
    );

    if(!isset($db_req['ham']))
        $db_req['ham'] = $_POST['ham'];

    defensio_update_db($db_req);

    /* Query for comments */
    $query_opts = array(
        'items_per_page' => 50,
        'page' => $_GET['defensio_page'],
        'sort_by' => $_REQUEST['sort_by'],
        'hide_obvious_spam_toggle' => $_REQUEST['hide_obvious_spam_toggle'],
        'hide_obvious_spam' => $_REQUEST['hide_obvious_spam'],
        'search' => $_POST['search'],
        'type' => $_POST['comment_type']
    );

    if(!isset($query_opts['search']))
        $query_opts['search'] = $_GET['search'];

    defensio_render_quarantine_html(defensio_caught($query_opts));
}


function defensio_manage_page() {
    global $defensio_db;

    $spam_count = $defensio_db->unhiddenSpamCount();

    $page = add_comments_page('Defensio Spam', "Defensio Spam ($spam_count)", 'moderate_comments', 'defensio-quarantine', 'defensio_dispatch');

    add_action( "admin_print_scripts-$page", 'defensio_admin_head' );
}
add_action('admin_menu', 'defensio_manage_page');

function defensio_admin_head(){
    global $defensio_plugin_url;
    wp_enqueue_script('prototype');
    wp_enqueue_script('fat',  "$defensio_plugin_url/scripts/fat.js");
    wp_enqueue_script('defensio', "$defensio_plugin_url/scripts/defensio.js");
    wp_enqueue_script('admin-comments');
    wp_enqueue_script('admin-forms');
}

/* 
 * Posts a comment to Defensio
 *
 * @param id $comment_ID an array representing a WordPress comment
 * @see http://defensio.com/api
 */
function defensio_send_comment($comment_ID, $wp_status = NULL ) {
    global $defensio_manager;

    $defensio_manager->postComment($comment_ID);
}
add_action('comment_post', 'defensio_send_comment');

function defensio_pre_comment_approved($approved){
    global $defensio_manager, $user_ID;
    return $defensio_manager->preApproval($approved, $user_ID);
}
add_action('pre_comment_approved', 'defensio_pre_comment_approved');

/* To train multiple messages at once, we push them into an array and process them in the shutdown hook. */
function defensio_defer_training($id, $new_status = null) {
    global $defensio_retraining, $wpdb, $defensio_db, $defensio_manager;

    $comment = $wpdb->get_row("SELECT * FROM $wpdb->comments NATURAL JOIN $wpdb->prefix" . "defensio WHERE $wpdb->comments.comment_ID = '$id'");

    if (!$comment) return; 
    // we only care about changes on comments being approved when they used to be spam
    if ($new_status == 'approve' and $comment->approved != 'spam') return; 

    if ($comment->comment_approved == 'spam' and isset($new_status) ) {

        if (defined('DOING_AJAX')) {
            $defensio_manager->submitSpam($comment->signature);
        } else {
            array_push($defensio_manager->deferred_ham_to_spam, $comment->signature );
        }
    }

    if ($comment->comment_approved == 1) {

        if (defined('DOING_AJAX' )) {
            $defensio_manager->submitHam($comment->signature);
        } else {
            array_push($defensio_manager->deferred_spam_to_ham, $comment->signature );
        }
    }
}
add_action('wp_set_comment_status', 'defensio_defer_training', 10, 2);
add_action('edit_comment', 'defensio_defer_training', 10, 1);

function defensio_announce_article($id) {
    global $wpdb, $userdata, $defensio_manager;

    get_currentuserinfo();
    $defensio_manager->postArticle($id, $userdata);
}
add_action('publish_post', 'defensio_announce_article');


// To be used with admin-ajax
function defensio_restore() {
    define('DOING_AJAX', true);

    if (isset ($_POST['ham'])) {
        $id = (int) $_POST['ham'];
        if(isset($id)){
            defensio_set_status_approved($id);
        }
    } 
}
add_action('wp_ajax_defensio-restore', 'defensio_restore');
add_filter('comment_spam_to_approved', create_function('$comment', 'defensio_set_status_approved($comment->comment_ID);'));

function defensio_set_status_approved($id) 
{
    global $defensio_retraining, $defensio_db, $defensio_manager;

    // Human checked.. spaminess => 0 
    try {
        $row = $defensio_db->getDefensioRow($id);
        $defensio_manager->submitHam($row[0]->signature);

    } catch (DefensioUnexpectedHTTPStatus $ex) {
        // Supress exception on 404, re-trhow in any other code
        if($ex->http_code != 404)
            throw $ex;
    }

    // wp_set_comment_status will call defensio_defer_training that's why defensio_retraining is set to true here
    $defensio_retraining = true;
    wp_set_comment_status($id, 'approve');
    $defensio_retraining = false;
}

function defensio_counter($color='dark', $align='left') 
{
    global $plugin_uri, $defensio_manager;

    // Use Ad-hoc cache instead of wp_cahce, we don't want a requests to defensio per request to the blog's front page 
    // if cache is disabled

    $last_updated = get_option(defensio_user_unique_option_key('defensio_stats_updated_at'));
    $two_hours = 60 * 60 * 2;

    if ( ($last_updated == NULL) or ( (mktime() - $last_updated) > $two_hours) ) {
        $response = $defensio_manager->getStats();
        /* Keep the integer cast here it is important since 'total' is a SimpleXML node and by definition a built-in
         * object; that should not be serialized [http://www.rhinocerus.net/node/16347] if unserialized it will throw 
         * a 'Node no longer exists' exception. 
         */
        $s = array('unwanted' => array('total' => (integer)$response->unwanted->total));
        update_option(defensio_user_unique_option_key('defensio_stats_updated_at'), mktime());
        update_option(defensio_user_unique_option_key('defensio_stats'), serialize($s));

    } else {
        $s = unserialize(get_option(defensio_user_unique_option_key('defensio_stats')));
    }

    if ($s) {
        defensio_render_counter_html(array('smoked_spam'=> $s['unwanted']['total'], 'color'=>$color, 'align'=>$align, 
            'plugin_uri'=>$plugin_uri ));
    }
}

function defensio_widget_register() 
{
    if (function_exists('register_sidebar_widget')) {
        function defensio_widget() 
        { 
            $alignment = get_option('defensio_counter_alignment'); 
            $color = get_option('defensio_counter_color');
            if (!isset($alignment) or empty($alignment)){ $alignment = 'left'; }
            if (!isset($color) or empty($color)){ $color = 'dark'; }

                defensio_counter(strtolower($color),strtolower($alignment)); 
        }

        function defensio_widget_control() 
        {
            global $defensio_widget_tones;
            if ($_POST['defensio_counter_alignment']) {
                update_option('defensio_counter_alignment', $_POST['defensio_counter_alignment']);
            }

            if ($_POST['defensio_counter_color']) {
                update_option('defensio_counter_color', strtolower($_POST['defensio_counter_color']));
            }

            $alignment = get_option('defensio_counter_alignment');
            $color = get_option('defensio_counter_color');

            if (!isset($alignment) or empty($alignment)){ $alignment = 'Left'; }
            if (!isset($color) or empty($color)){ $color = 'dark'; }
?>
      <label for="defensio_counter_alignment"	 style="width: 100px; display: block; float: left;">Alignment</label>
      <select name="defensio_counter_alignment" id="defensio_counter_alignment">
        <option <?php if ($alignment == 'Left'):?>selected="1" <?php endif;?> >Left</option>
        <option <?php if ($alignment == 'Center'):?> selected="1"<?php endif;?> >Center</option>
        <option <?php if ($alignment === 'Right'):?>selected="1" <?php endif; ?> >Right</option>
      </select> 
      <br />
      <label for="defensio_counter_color" style="width: 100px; display: block; float: left;">Color</label>
      <select name="defensio_counter_color" id="defensio_counter_color">
        <?php foreach($defensio_widget_tones as $t): ?>
          <option <?php if ($t == $color) :?> selected="1"<?php endif;?> ><?php echo ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select>
<?php
        }
        register_sidebar_widget('Defensio Counter', 'defensio_widget', null, 'defensio');
        register_widget_control('Defensio Counter', 'defensio_widget_control', 300, 75, 'defensio');
    }
}
add_action('init', 'defensio_widget_register');

/*
 * Shutdown hook, train comments scheduled to be trained
 */
function defensio_finalize() {
    global $defensio_manager;

    if (!empty($defensio_manager->deferred_ham_to_spam)) {
        $defensio_manager->submitSpam($defensio_manager->deferred_ham_to_spam);
    }

    if (!empty($defensio_manager->deferred_spam_to_ham)) {
        $defensio_manager->submitHam($defensio_manager->deferred_spam_to_ham);
    }
}
add_action('shutdown', 'defensio_finalize');

function defensio_unhidden_spam_count(){
    global $defensio_db;

}

function defensio_render_activity_box() {
    global $defensio_db;

    $link_base = 'edit-comments.php';
    $link = clean_url($link_base . "?page=defensio-quarantine.php");

    $obvious_spam_count = $defensio_db->obviousSpamCount();
    $total_spam_count =   $defensio_db->spamCount();

    echo "<p class='youhave'>";

    if ($total_spam_count == 0) {
        echo "Your <strong>Defensio quarantine is empty</strong>. Awesome!</p>";
    }
    else {  // some spam in quarantine
        if ($total_spam_count <= 1)
            echo "You have <strong>$total_spam_count spam comment</strong>";
        else
            echo "You have <strong>$total_spam_count spam comments</strong>";

        if ($obvious_spam_count > 0) 
            echo " ($obvious_spam_count obvious)";

        echo " in your <a href='$link'>Defensio quarantine</a>.";
    }

    echo "</p>";
}
add_action('activity_box_end', 'defensio_render_activity_box');

// Orphan rows have spaminess -1; they were never filtered by Defensio
function defensio_clean_up_orphan_rows($id, $status) {
    global $wpdb;
    if ($status == 'hold') {
        // If it stays in moderation, it can still be sent to defensio, do nothing
    } elseif ($status == 'spam') {
        // spam for sure
        $wpdb->query("UPDATE  $wpdb->prefix"."defensio set spaminess = 1 WHERE spaminess = -1 AND comment_ID = $id " );
    } elseif ($status == 'approve') {
        // ham for sure
        $wpdb->query("UPDATE  $wpdb->prefix"."defensio set spaminess = 0 WHERE spaminess = -1 AND comment_ID = $id " );
    } elseif ($status == 'delete') {
        $wpdb->query("DELETE FROM $wpdb->prefix"."defensio WHERE spaminess = -1 AND comment_ID = $id " );
    }
}
add_action('wp_set_comment_status', 'defensio_clean_up_orphan_rows', 10, 2);

// Generates a key name for wp options that is user unique
function defensio_user_unique_option_key( $opt_name = null ){
    global $userdata;
    if($opt_name != null){
        get_currentuserinfo();
        return "defensio_". $userdata->ID."_$opt_name";
    }
}

/* In WP 2.7+, there is a built-in spam quarantine. This filter function
 * will take the array of status links in wp-admin/comments and replace the
 * link to spam type by a link to Defensio's quarantine
 */
function defensio_replace_default_quarantine_link($status_links){
    global $defensio_db;

    foreach($status_links as $index => $link){

        if(preg_match('/Spam/', $link)){
            $status_links[$index] = '<a href="edit-comments.php?page=defensio-quarantine">Defensio Spam ('. $defensio_db->unhiddenSpamCount() . ") </a> ";
            break;
        }
    }
    return $status_links;
}
add_filter('comment_status_links', 'defensio_replace_default_quarantine_link', 99, 1);

// Redirect default quarantine to defensio's. There is no useful hook to change the link in dashboard.php... just redirect 
function defensio_redirect_to_qurantine($a){
    if($_REQUEST['comment_status'] == 'spam')
        wp_redirect("edit-comments.php?page=defensio-quarantine");
}
add_action('load-edit-comments.php', 'defensio_redirect_to_qurantine');

// Scheduling wp-cront task to take care of unprocessed and pending comments if callback was not received sdasd 

/* Add a custom wp_cron reccurence */
function defensio_custom_reccurence()
{
    // Try to add every ten minutes, now warrantied by wp-cron!
    return array('tenminutely' => array('interval' => 600, 'diplay' => 'Every ten minutes' ));
}
add_filter('cron_schedules', 'defensio_custom_reccurence');

function defensio_recurrent_actions()
{
    global $defensio_manager;

    $defensio_manager->getPendingResults();
    $defensio_manager->postUnprocessed();
}

if (!wp_next_scheduled('defensio_recurrent_actions_hook'))
    wp_schedule_event(time(), 'tenminutely', 'defensio_recurrent_actions_hook');
add_action('defensio_recurrent_actions_hook', 'defensio_recurrent_actions');

// Define wp_notify_postauthor so it does not send notifications when the status of a comment is defensio_pending
if ( !function_exists('wp_notify_postauthor') ):
    function wp_notify_postauthor($comment_id, $comment_type='') {
        $comment = get_comment($comment_id);
        $post    = get_post($comment->comment_post_ID);
        $user    = get_userdata( $post->post_author );
        $current_user = wp_get_current_user();

        if ( $comment->user_id == $post->post_author ) return false; // The author moderated a comment on his own post
        if ( $comment->comment_approved == DefensioWP::DEFENSIO_PENDING_STATUS ) return false; // Do nothing unless defensio has cleared this comment

        if ('' == $user->user_email) return false; // If there's no email to send the comment to

        $comment_author_domain = @gethostbyaddr($comment->comment_author_IP);

        $blogname = get_option('blogname');

        if ( empty( $comment_type ) ) $comment_type = 'comment';

        if ('comment' == $comment_type) {
            /* translators: 1: post id, 2: post title */
            $notify_message  = sprintf( __('New comment on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
            /* translators: 1: comment author, 2: author IP, 3: author domain */
            $notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
            $notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
            $notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
            $notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "\r\n";
            $notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
            $notify_message .= __('You can see all comments on this post here: ') . "\r\n";
            /* translators: 1: blog name, 2: post title */
            $subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
        } elseif ('trackback' == $comment_type) {
            /* translators: 1: post id, 2: post title */
            $notify_message  = sprintf( __('New trackback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
            /* translators: 1: website name, 2: author IP, 3: author domain */
            $notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
            $notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
            $notify_message .= __('Excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
            $notify_message .= __('You can see all trackbacks on this post here: ') . "\r\n";
            /* translators: 1: blog name, 2: post title */		
            $subject = sprintf( __('[%1$s] Trackback: "%2$s"'), $blogname, $post->post_title );
        } elseif ('pingback' == $comment_type) {
            /* translators: 1: post id, 2: post title */
            $notify_message  = sprintf( __('New pingback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
            /* translators: 1: comment author, 2: author IP, 3: author domain */
            $notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
            $notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
            $notify_message .= __('Excerpt: ') . "\r\n" . sprintf('[...] %s [...]', $comment->comment_content ) . "\r\n\r\n";
            $notify_message .= __('You can see all pingbacks on this post here: ') . "\r\n";
            /* translators: 1: blog name, 2: post title */
            $subject = sprintf( __('[%1$s] Pingback: "%2$s"'), $blogname, $post->post_title );
        }
        $notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
        $notify_message .= sprintf( __('Delete it: %s'), admin_url("comment.php?action=cdc&c=$comment_id") ) . "\r\n";
        $notify_message .= sprintf( __('Spam it: %s'), admin_url("comment.php?action=cdc&dt=spam&c=$comment_id") ) . "\r\n";

        $wp_email = 'wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));

        if ( '' == $comment->comment_author ) {
            $from = "From: \"$blogname\" <$wp_email>";
            if ( '' != $comment->comment_author_email )
                $reply_to = "Reply-To: $comment->comment_author_email";
        } else {
            $from = "From: \"$comment->comment_author\" <$wp_email>";
            if ( '' != $comment->comment_author_email )
                $reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
        }

        $message_headers = "$from\n"
            . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

        if ( isset($reply_to) )
            $message_headers .= $reply_to . "\n";

        $notify_message = apply_filters('comment_notification_text', $notify_message, $comment_id);
        $subject = apply_filters('comment_notification_subject', $subject, $comment_id);
        $message_headers = apply_filters('comment_notification_headers', $message_headers, $comment_id);

        @wp_mail($user->user_email, $subject, $notify_message, $message_headers);

        return true;
    }

endif;

/* Using comments_array hook to add defensio_pending comment to what comment posters can see right after they have posted */
function defensio_add_defensio_pending($comments)
{
    global $user_ID, $post, $wpdb;

    $commenter = wp_get_current_commenter();
    $comment_author = $commenter['comment_author'];
    $comment_author_email = $commenter['comment_author_email'];  
    $comment_author_url = esc_url($commenter['comment_author_url']);

    if ( $user_ID) {
        $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND (comment_approved = '1' OR ( user_id = %d AND ( comment_approved = '0' OR 'comment_approved' = '" . DefensioWP::DEFENSIO_PENDING_STATUS . "' ) ) )  ORDER BY comment_date_gmt", $post->ID, $user_ID));
    } else if ( empty($comment_author) ) {
        $comments = get_comments( array('post_id' => $post->ID, 'status' => 'approve', 'order' => 'ASC') );
    } else {
        $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND ( comment_approved = '1' OR ( comment_author = %s AND comment_author_email = %s AND ( comment_approved = '0' OR comment_approved = '". DefensioWP::DEFENSIO_PENDING_STATUS ."' ))) ORDER BY comment_date_gmt", $post->ID, wp_specialchars_decode($comment_author,ENT_QUOTES), $comment_author_email));
    }
    return $comments;
}
add_filter('comments_array', 'defensio_add_defensio_pending');


?>
