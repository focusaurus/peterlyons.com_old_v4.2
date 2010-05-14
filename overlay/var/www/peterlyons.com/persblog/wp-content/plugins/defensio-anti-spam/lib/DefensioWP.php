<?php
/* 
 * Manage all the behind the scenes Defensio logic, anything that can be abstracted from the hook functions and UI goes 
 * here (calling get/set _option and some use of wpdb are acceptable) 
 * 
 * @package Defensio
 */

class DefensioWP
{
    /* Defensio object, to be able to communicate with the server */
    private $defensio_client;
    /* Defensio DB operations */
    private $defensio_db;
    /* Where to receive callbacks from Defensio's servers */
    private $async_callback_url;
    /* Roles that will get trusted-user => true when their comments are send to Defensio */
    private $trusted_roles;
    private $authenticated;

    public $deferred_ham_to_spam;
    public $deferred_spam_to_ham;

    const DEFENSIO_PENDING_STATUS = 'defensio_pending';
    const PLATFORM_NAME = 'WordPress';
    const UNPROCESSED   = 'unprocessed';
    const PENDING       = 'pending';
    const OK            = 'ok';
    const CLIENT_ID     = 'Defensio for Wordpress | 2.0 | Websense Inc. | info@defensio.com';

    /**
     * @param string $api_key A Defensio API key
     * @param string $server URL of the Defensio server
     */
    function __construct($api_key, $async_callback_url=NULL)
    {
        $this->defensio_db   = new DefensioDB();
        $this->authenticated = NULL;
        $this->trusted_roles = array('administrator', 'editor', 'author');
        $this->defensio_client  = new Defensio($api_key, self::CLIENT_ID);
        $this->async_callback_url   = $async_callback_url;
        $this->deferred_ham_to_spam = array();
        $this->deferred_spam_to_ham = array();
    }

    /** 
     * Get stats for this user, try wp_cache first then load from Defensio server unlike the cache in Defensio's
     * counter widget this method relies on WordPresse's own object cache
     */
    public function getStats()
    {
        $stats = wp_cache_get('stats', 'defensio');

        if (!$stats) { 
            $stats = $this->refreshStats();
            wp_cache_set('stats' , $stats, 'defensio', 600);
        }

        return $stats;
    }

    /**
     *  Will change return true if that key is valid
     *  @param string $key the candidate key
     */
    public function verifyKey($key, &$err_code)
    {
        $out = FALSE;

        // If this object is still in memory and the key was authenticated successfully don't ask the server again
        if ( $this->defensio_client->getApiKey() == $key && $this->authenticated == TRUE )
        {
            $out = TRUE;

        } else {
            $this->defensio_client = new Defensio($key);

            try{
                $out = (200 == array_shift($this->defensio_client->getUser()));
            } catch ( DefensioFail $ex ){
                $err_code = -1;
            } catch ( DefensioUnexpectedHTTPStatus $ex ){
                $err_code = 500;
            } catch ( DefensioConnectionError $ex ) {
                $err_code = -1;
            }
        }

        $this->authenticated = $out;
        return $out;
    }

    /**
     * Will send a GET request to Defensio querying about pending comments and act accordingly
     */
    public function getPendingResults()
    {
        $pending_coments = $this->defensio_db->getPendingComments();

        if ( is_array($pending_coments) ) {

            foreach( $pending_coments as $comment ) {
                $result = NULL;

                try {
                    $response = $this->defensio_client->getDocument($comment->signature);
                    $result = $response[1];
                } catch (DefensioFail $ex) { 
                    /* 
                     * getDocument will throw DefensioFail on document not found instead of DefensioUnexpectedHTTPStatus; 
                     * since  HTTP 404 makes sense as not found. In case a GET request for a pending comment fails whit and 
                     * the HTTP status code is 404 something has gone terribly wrong and a signature was lost at some point; 
                     * update as unprocessed to start over
                     *  */
                    if ( $ex->http_status == 404 ) {
                        $this->defensio_db->updateDefensioRow($comment->comment_ID, array('status' => self::UNPROCESSED));
                    }
                    continue; 
                }
                catch (DefensioUnexpectedHTTPStatus $ex) { continue; } 
                $this->applyResult($comment, $result);
            }
        }
    }

    /** Shortcut for retrain */
    public function submitSpam($signatures)
    {
        $this->retrain('spam', $signatures);
    }

    /** Shortcut for retrain */
    public function submitHam($signatures)
    {
        $this->retrain('ham', $signatures);
    }

    /** Get stats from the Defensio's server */
    private function refreshStats()
    {
        $out = FALSE;

        try{
            $res =  $this->defensio_client->getBasicStats();
            $out = $res[1];
        } catch(DefensioError $ex){/*NO OP*/} 

        return $out;
    }

    /**
     * @param object $article a WP post 
     * @param object $userdata from get_currentuserinfo
     */
    public function postArticle($article_id, $userdata)
    {
        global $wpdb;

        $article = get_post($article_id);
        $params = array (
            'content'      => $article->post_content, 
            'title'        => $article->post_title, 
            'permalink'    => get_permalink($article->ID),
            'author-name'  => $userdata->user_login,
            'author-email' => $userdata->user_email,
            'type'         => 'article',
            'platform'     => self::PLATFORM_NAME
        );

        $this->defensio_client->postDocument($params);
    }

    /**
     * POSTs a comment to Defensio this will create a document entry, and Defensio will notify callback.php of the result.
     * In case callback.php is not notified a GET request will be send to Defensio inquiring about the result.
     *
     * @param integer $id value of comment_ID for the comment being posted
     * @param boolean $retrying 
     */
    public function postComment($id, $retrying = FALSE)
    {
        $comment  = get_comment($id);
        $document = $this->commentToDocument($comment);
        $document = array_merge($document, array('async' => 'true', 'async-callback' => $this->prepareCallBackUrl() ));
        $data     = array();

        try {
            $response = $this->defensio_client->postDocument($document);
            $result = $response[1];

            if ( $result->status == self::PENDING){
                $data['signature'] = $result->signature;
                $data['status']    = $result->status;

            } else {

                if($retrying)
                    $data['status'] = self::UNPROCESSED;
                else
                    return $this->postComment($id, TRUE);
            }

        } catch( DefensioError $ex) {
                if($retrying)
                    $data['status'] = self::UNPROCESSED;
                else
                    return $this->postComment($id, TRUE);
        }

        if ($comment->comment_approved != 1) {

            /* if($data['status'] == self::UNPROCESSED)
                wp_update_comment(array('comment_approved' => 0, 'comment_ID' => $id));
              else*/
            // If not approved by comment should not be shown until defensio has seen it.
            wp_update_comment(array('comment_approved' => self::DEFENSIO_PENDING_STATUS, 'comment_ID' => $id));

        }

        if( $this->defensio_db->getDefensioRow($id) ) {
            $this->defensio_db->updateDefensioRow($id, $data);

        } else { 
            $this->defensio_db->insertDefensioRow(array( 'comment_ID' => $id, 'status' => $data['status'],
                'signature' => $data['signature']), array( '%d', '%s', '%s'));
        }
    }

    /** 
     *   To be called in the pre-approve hook makes anything not automatically approved self::DEFENSIO_PENDING_STATUS
     * @param string $approved_value passed by the pre-comment-approved hook
     */
    public function preApproval($approved_value, $user_ID)
    {
        if($user_ID && $this->isTrustedUser($user_ID))
            return $approved_value;
        else
            return self::DEFENSIO_PENDING_STATUS;
    }

    /**
     * Will apply profanity filter to $input, by doing a POST call to profanity-filter and return it's result
     * @param string $input
     */
    private function filterProfanity($input)
    {
        $result = FALSE;

        if(!$input)
            return $result;

        try {
            $response = $this->defensio_client->postProfanityFilter(array('content' => $input));
            $result = $response[1]->filtered->content;
        } catch (DefensioError $ex) {
            $result = FALSE;
        }

        return $result;
    }

    /**
     * Acts based on the result of for an previously POSTed document, result may come from a GET request or a callback 
     * from Defensio
     *
     * @params Object $signature a Defensio row from DefensioDB#getDefensioRow
     * @params Object $result the result of a GET that comment (or the result pushed by Defensio to a callback)
     */
    public function applyResult($defensio_row, $result)
    {
        $comment = get_comment($defensio_row->comment_ID);

        if ( $result->status == 'success' ) {
            $profanity_action = NULL;

            if($result->{'profanity-match'} == 'true')
                $profanity_action = get_option('defensio_profanity_do');


            if( $result->allow == 'true' ) {

                if($comment->comment_approved == self::DEFENSIO_PENDING_STATUS || $comment->comment_approved == 'spam')
                    $approval_value = $this->reApplyWPAllow((array)$comment);

                elseif($comment->comment_approved == '1')
                    $approval_value = '1';


                $this->doApply($comment, $result, $approval_value, $profanity_action);

            } else {
                // If the article is old and the user wants to get rid of not allowed in old posts...
                $article = get_post($comment->comment_post_ID);
                $time_diff = time() - strtotime($article->post_modified_gmt);

                // A day has 86400 seconds
                if ( get_option('defensio_delete_older_than') == 1 and ($time_diff > (get_option('defensio_delete_older_than_days') * 86400 )) ) {
                    $this->defensio_db->deleteCommentAndDefensioRow($comment->comment_ID);

                } else {
                    // Do not mask profanity in spam no matter what!
                    if($profanity_action == 'mask') $profanity_action = NULL;
                    $this->doApply($comment, $result, 'spam', $profanity_action);
                }

            }

        } elseif ( $result->status == self::PENDING ) {
            // If it has been pending for 'too long' eg more than 30 minutes start over.
            $time_diff = time() - strtotime( $comment->comment_date );

            if($time_diff > 1800)
                $this->defensio_db->updateDefensioRow( $comment->comment_ID, array('status' => self::UNPROCESSED));

        } elseif ( $result->status == 'fail' ) { /* Do nothing */   }
    }

    private function doApply($comment, $result, $approved_value, $profanity_action=NULL )
    {
        $comment = $this->applyProfanityRules($comment, $profanity_action);

        if(is_null($comment)) return;

        $this->defensio_db->updateDefensioRow($comment->comment_ID, array( 'status'           => self::OK, 
                                                                           'spaminess'        => (float)$result->spaminess,
                                                                           'classification'   => $result->classification,
                                                                           'profanity_match'  => ($result->{'profanity-match'} == 'true') ? 1 : 0 ));


        wp_update_comment(array('comment_approved' => $approved_value, 
                                'comment_ID'       => $comment->comment_ID, 
                                'comment_content'  => $comment->comment_content)); 

        if($approved_value == '0' )
            wp_notify_moderator($comment->comment_ID); 
    }

    /**
     * Receives a comment and  a string or null and returns  either the modified comment or NULL if the comment was deleted
     * @param object $comment the comment
     * @param string $profanity_action a string telling the method what to do or NULL for nothing 'off' analogous to NULL 'mask' call Defensio and
     * mask the profanity with * 'delete' delete the comment and the Defensio meta-data
    */
    private function applyProfanityRules($comment, $profanity_action=NULL){

        $result = $comment;

        if($profanity_action == 'mask'){

            $new_content = $this->filterProfanity($comment->comment_content);

            if($new_content)
                $result->comment_content = $new_content;

        } elseif($profanity_action == 'delete'){
            $this->defensio_db->deleteCommentAndDefensioRow($comment->comment_ID);
            $result = NULL;
        }

        return $result;
    }


    /** Calls postComment on any unprocessed comments */
    public function postUnprocessed()
    {
        $unprocessed = $this->defensio_db->getUnprocessedComments();

        if ( is_array($unprocessed) )
        {
            foreach( $unprocessed as $comment_data )
            {
                $this->postComment($comment_data->comment_ID, FALSE);
            }
        }
    }

    /**
    * Receives a parsed Defensio result, useful when reading a callback from Defensio 
    * @param object $result a parsed Defensio result
    */
    public function applyCallbackResult($result)
    {
        $comment = $this->defensio_db->getDefensioRowBySignature($result->signature);

        if( $comment ) 
            $this->applyResult($comment[0], $result);
    }

    /**
    * Will PUT a new allow value to Defensio and keep the Defensio DB
    * in sync with the moderators criteria for spam/ham
    */
    private function retrain($new_value, $signatures)
    {
        if ( !is_array($signatures))
            $signatures = array($signatures);

        foreach($signatures as $signature) {

            try {

                if(!empty($signature)){
                    switch ($new_value){
                    case 'spam':
                        $this->defensio_client->putDocument($signature, array('allow' => 'false'));
                        break;
                    case 'ham':
                        $this->defensio_client->putDocument($signature, array('allow' => 'true'));
                        break;
                    default:
                        // Do nothing for any other values
                        // maybe throw an exception here?
                    }
                }

            } catch (DefensioUnexpectedHTTPStatus $ex) {
                /* Suppress the exception on 404 documents are not warrantied to be there after some time if this is 
                 * an old comment 404 makes sense, re-throw it on any other HTTP code
                 */
                if($ex->http_status != 404)
                    throw $ex;
            }

            $row = $this->defensio_db->getDefensioRowBySignature($signature);

            switch ($new_value){
            case 'spam':
                $this->defensio_db->updateDefensioRow($row[0]->comment_ID, array('spaminess' => 1, 'status' => self::OK));
                break;
            case 'ham':

                $profanity_match = $row[0]->profanity_match;

                if(get_option('defensio_profanity_do') == 'mask' && $row[0]->profanity_match){
                    $comment = get_comment($row[0]->comment_ID);
                    $filtered_content = $this->filterProfanity($comment->comment_content);

                    if($filtered_content){
                        $profanity_match = 0; // Should not match anymore, avoid further calls to $defensio_client->postDictionaryFilter
                        wp_update_comment(array('comment_ID' => $comment->comment_ID, 'comment_content' => $filtered_content ));
                    }
                }

                $this->defensio_db->updateDefensioRow($row[0]->comment_ID, array('spaminess' => 0, 'status' => self::OK, 'profanity_match' => $profanity_match));
                break;
            default:
                // Do nothing for any other values
            }

        }
    }

    /**
     * Convert a comment object (result of get_comment) into an array according to Defensio's API
     * @param object $comment a WP comment object typically the return value of get_comment
     * @returns array an array ready to send to Defensio /user/xxx/documents
     */
    private function commentToDocument($comment)
    {
        global $wpdb;
        $doc = array();

        if($comment->user_id){
            $doc['author-logged-in'] = 'true';

            if ($this->isTrustedUser($comment->user_id))
                $doc['author-trusted'] = 'true';
        }

        if (!isset($comment->comment_type) || empty($comment->comment_type)) {
            $doc['type'] = 'comment';

        } else {
            $doc['type'] = $comment->comment_type;
        }

        // Make sure it we don't send an SQL escaped string to the server
        $doc['content'] = stripslashes($comment->comment_content);
        $doc['author-email'] = $comment->comment_author_email;
        $doc['author-name']  = $comment->comment_author;
        $doc['author-url']   = $comment->comment_author_url;
        $doc['author-ip']    =  preg_replace( '/[^0-9., ]/', '', $comment->comment_author_IP );

        if ( $this->isOpenIdEnabled() ) {
            $identity = get_user_openids(null);

            // Take the first URL.
            if(is_array($identity)) {
                $identity = @array_pop($identity);
            }
            $doc['author-openid'] = $identity;
        }

        $doc['platform']  = self::PLATFORM_NAME ;
        $doc['parent-document-permalink'] = get_permalink($comment->comment_post_ID);
        $doc['parent-document-date'] = strftime( "%Y-%m-%d", strtotime(get_post($comment->comment_post_ID)->post_modified_gmt));
        return $doc;
    }

    /**
     * @param integer $user_id The user id of the user we are querying 
     * @return boolean is this a trusted user ?
     */
    private function isTrustedUser($user_id) {
        global $wpdb;

        $out = FALSE;

        $caps = get_usermeta( $user_id, $wpdb->prefix . 'capabilities');
        if (!is_array($caps)) { return $out; }

        foreach ($caps as $k => $v) {
            if (in_array($k, $this->trusted_roles)) {
                $out = TRUE; 
                break; 
            }
        }

        return $out;
    }

    /** Look for wp_openid */
    private function isOpenIdEnabled()
    {
        return function_exists('is_user_openid');
    }

    /** Add an id to the URL for checking in the callback responder */
    private function prepareCallBackUrl()
    {
        return $this->async_callback_url . "?id=" . md5($this->defensio_client->getApiKey());
    }

    /** Does the same as wp_allow_comment */
    private function reApplyWPAllow($comment_data)
    {
        global $wpdb;
        extract($comment_data, EXTR_SKIP);

        if ($user_id) {
            $userdata = get_userdata($user_id);
            $user = new WP_User($user_id);
            $post_author = $wpdb->get_var("SELECT post_author FROM $wpdb->posts WHERE ID = '$comment_post_ID' LIMIT 1");
        }

        if ($userdata && ($user_id == $post_author || $user->has_cap('level_9'))) {
            // The author and the admins get respect.
            $approved = 1;
        } else {
            // Everyone else's comments will be checked.
            if ( check_comment($comment_author, $comment_author_email, $comment_author_url, $comment_content, $comment_author_IP, $comment_agent, $comment_type)) {
                $approved = 1;
            } else {
                $approved = 0;
            }

            if (wp_blacklist_check($comment_author, $comment_author_email, $comment_author_url, $comment_content, $comment_author_IP, $comment_agent)) {
                $approved = 'spam';
            }
        }

        return $approved;
    }
}

?>
