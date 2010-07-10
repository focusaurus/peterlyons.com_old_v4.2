<?php
/* Include update functions when necessary */
if(defined('ABSPATH') && ( function_exists('wp_get_current_user') ||  array_shift(split('\.', $wp_version))) == 3 ){

    if(file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    } else {
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    }
}

/* 
 * Abstract most of the necessary database work for Defensio's plugin
 * @package Defensio
 */
class DefensioDB 
{
    public $table_name;
    const TABLE_VERSION = 2;

    public static function getTableName()
    {
        global $wpdb;
        return $table_name =  $wpdb->prefix . "defensio";
    }

    /* Creates an empty table in Wordpresse's DB  ... or updates and old one from Defensio 1.x */
    public static function createTable($table_name, $version, $force = FALSE)
    { 
        global $wpdb;
        $out = FALSE;
        $existent = DefensioDB::tableExists($table_name);

        if(is_null($version))
            $version = 0;

        if ( $force || $version < DefensioDB::TABLE_VERSION || !$existent ) {
            $out = TRUE;

            /* From WP docs:
             *
             * ... dbDelta function is rather picky, however. For instance:
             *
             * You have to put each field on its own line in your SQL statement.
             * You have to have two spaces between the words PRIMARY KEY and the definition of your primary key.
             * You must use the key word KEY rather than its synonym INDEX and you must include at least one KEY.
             */

            $sql = "CREATE TABLE " . $table_name . " ( 
                comment_ID mediumint(9) NOT NULL, 
                spaminess DECIMAL(5,4) , 
                signature VARCHAR(55) NOT NULL,
                status ENUM('ok', 'unprocessed', 'pending'),
                classification ENUM('spam', 'legitimate', 'malicious'),
                profanity_match TINYINT,
                UNIQUE KEY comment_ID (comment_ID)
            );";

            dbDelta($sql);

            if($existent){
                $wpdb->query("UPDATE ". $wpdb->prefix. 'defensio SET status = "ok"  WHERE spaminess >= 0');
                $wpdb->query("UPDATE ". $wpdb->prefix. 'defensio SET status = "unprocessed" WHERE spaminess <  0');
            }
        }

        return $out;
    }

    /* Is defensio's table in there? */
    public static function tableExists($table_name)
    {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    }

    public function __construct()
    {
        $this->table_name    = DefensioDB::getTableName();
        $this->table_version = DefensioDB::TABLE_VERSION;
    }

    /** 
     * @param integer $limit 
     * @return array All the comments with status unprocessed
     */
    public function getUnprocessedComments($limit=20)
    {
        return $this->getCommentsByStatus('unprocessed', $limit);
    }

    /** @return array All the comments with status pending */
    public function getPendingComments($limit=20)
    {
        return $this->getCommentsByStatus('pending', $limit);
    }

    /** 
     * @param string Get all the comments with status $status, status can be ok, peding and unprocessed
     * @return array All the comments with status defined by $status */
    public function getCommentsByStatus($status, $limit = NULL)
    {
        global $wpdb;

        $limit_clause = is_null($limit) ? " LIMIT $limit " : "";
        $out = array();

        if ( in_array($status, array('ok', 'pending', 'unprocessed')) )
            $out = $wpdb->get_results("SELECT $wpdb->comments.comment_ID, signature FROM $wpdb->comments  LEFT JOIN $this->table_name" . " ON $wpdb->comments" . ".comment_ID = $this->table_name" . ".comment_ID  WHERE status = '$status' $limit_clause");

        return $out;
    }

    /** 
     * Get a defensio row of metadata  by its comment_ID
     * @param integer $comment_ID the id of the comment 
     * @return a one element array with the defensio data for that commet's id
     */
    public function getDefensioRow($comment_ID)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $this->table_name WHERE comment_ID = %d LIMIT 1",
            $comment_ID));
    }

    /** 
     * Get a defensio row of metadata  by its comment_ID
     * @param string $signature the signature of the comment 
     * @return a one element array with the defensio data for that commet's signature
     */
    public function getDefensioRowBySignature($signature)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $this->table_name WHERE signature = %s LIMIT 1", $signature));
    }

    /**
     * @params integer $comment_ID the id of the comment for which Defensio data will be update
     * @params array $new_values associative array with the new parameters
     */
    public function updateDefensioRow($comment_ID, $new_values)
    {
        global $wpdb;
        $wpdb->update($this->table_name, $new_values, array('comment_ID' => $comment_ID));
    }

    /** 
     * Shorthand for wpdb->insert
     *
     * @param array $values
     * @param array $types
     */
    public function insertDefensioRow($values, $types)
    {
        global $wpdb;
        $wpdb->insert($this->table_name, $values, $types);
    }

    /** Deletes a comment along with its Defensio metadata */
    public function deleteCommentAndDefensioRow($comment_ID)
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM $this->table_name WHERE comment_ID = %d", $comment_ID));
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->comments   WHERE comment_ID = %d", $comment_ID));
    }

    /** Deletes all comments marked as spam and their Defensio metadata*/
    public function deleteAllSpam()
    {
        global $wpdb;
        $wpdb->query("DELETE $this->table_name.* FROM  $this->table_name NATURAL JOIN $wpdb->comments WHERE comment_approved = 'spam'");
        $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'");
    }

    public function spamCount()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT count(*) FROM $wpdb->comments LEFT JOIN $this->table_name ON $wpdb->comments" . ".comment_ID = $this->table_name.comment_ID WHERE comment_approved = 'spam';");
    }

    public function obviousSpamCount()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT count(*) FROM $wpdb->comments LEFT JOIN $this->table_name ON $wpdb->comments" . ".comment_ID = $this->table_name.comment_ID WHERE comment_approved = 'spam' ". $this->generateSpaminessFilter(true, true) . ";");
    }

    public function unhiddenSpamCount()
    {
        $count = 0;
        if(get_option(defensio_user_unique_option_key('hide_more_than_threshold')) == 1){
            $count = $this->spamCount() - $this->obviousSpamCount();
        } else {
            $count = $this->spamCount();
        }
        return $count;
    }

    /** 
     * Fetches the data to build Defensio's spam quarantine
     *
     * @param integer $page page number
     * @param integer $items_per_page number of items per page
     * @param string  $sort_by  spaminess, comment_date or post_date
     * @param string  $type all, comments, pings
     * @param string  $search  arbitrary search string
     *
     * @return an array of objects, a return value from wpdb->get_results
     */
    public function getQuarantineComments($page=1, $items_per_page=50, $sort_by=NULL,  $type=NULL, $search=NULL)
    {
        global $wpdb;

        $sort_by_statement = '';
        $spaminess_filter  = $this->generateSpaminessFilter();
        $search_statement  = '';
        $type_statement    = '';


        switch($sort_by){
        case 'post_date':
            $sort_by_statement = ' post_date DESC, IFNULL(spaminess, 1) ASC ';
            break;
        case 'comment_date':
            $sort_by_statement = ' comment_date DESC, IFNULL(spaminess, 1) ASC ';
            break;
        default:
            $sort_by_statement = ' IFNULL(spaminess, 1) ASC, comment_date DESC ' ;
            break;
        }

        if(isset($search) && !empty($search)) {
            $s = $search;
            $search_statement = " AND  (comment_author LIKE '%%$s%%' OR comment_author_email LIKE '%%$s%%' OR comment_author_url LIKE ('%%$s%%') OR comment_author_IP LIKE ('%%$s%%') OR comment_content LIKE ('%%$s%%') ) ";
        }

        // Comments have empty type, pings have something.
        if(isset($type) && !empty($type)) {
            if ($type == 'comments')
                $type_statement = " AND comment_type = '' ";
            elseif ($type == 'malicious')
                $type_statement = " AND $this->table_name.classification = 'malicious'";
            else
                $type_statement = " AND comment_type != '' ";
        }

        $limit_start = ($page - 1) * $items_per_page;
        $limit_end = $items_per_page;

        $sql = $wpdb->prepare(
<<<SQL
    SELECT *,IFNULL(spaminess, 1) as spaminess, $wpdb->comments.comment_ID as id,  $wpdb->posts.post_title as post_title, 
        $wpdb->posts.post_date as post_date, $wpdb->comments.comment_content,$wpdb->comments.comment_post_ID  as post_id  
        FROM  $wpdb->comments LEFT JOIN $this->table_name ON $wpdb->comments.comment_ID = $this->table_name.comment_ID 
        LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID WHERE comment_approved = 'spam' AND 
        ( status = 'ok' OR status IS NULL) $spaminess_filter $search_statement $type_statement ORDER BY $sort_by_statement LIMIT 
        $limit_start, $limit_end
SQL
    );
        return $wpdb->get_results($sql);
    }

    private function generateSpaminessFilter($reverse = false, $ignore_option = false) {
      $spaminess_filter = '';

      $option_name = defensio_user_unique_option_key('hide_more_than_threshold');

      if (get_option($option_name) == '1' or $ignore_option) {
        $t = (int)get_option(defensio_user_unique_option_key('threshold'));
        $t = (float)($t) / 100.0;

        /* if the Defensio table was created using an old version of the plugin, the 
           spaminess field was created as a float which is not precise. For example,
           a spaminess of 80% was being stored as 0.80000001, which caused the following
           filters to not work properly sometimes. This is simply a little dirty workaround
           for this problem. New users have their spaminess properly stored as numeric.
           This hack will, not affect them, however. */
        $t = $t - 0.001;

        // MySQL does not like "," as decimal separator using sprintf to avoid that in  some locales.
        if ($reverse) {
          $spaminess_filter = " AND IFNULL(spaminess, 1) >= ". sprintf('%F', $t);
        } else {
          $spaminess_filter = " AND IFNULL(spaminess, 1) < " . sprintf('%F', $t);
        }
      }

      return $spaminess_filter;
    }
}
?>
