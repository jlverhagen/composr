<?php

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Jason L Verhagen (jlverhagen@tfo.net)
 * @package    twitter_feed
 */

/**
 * This addon pulls and displays a user Twitter feed using the Twitter Class provided by Tijs Verkoyen which is included with the
 * Composr Twitter Support non-bundled addon.  PHP5 and PHP CuRL Extension are required.
 */
 
 /**
 * Block class.
 */
class Block_twitter_feed
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Jason Verhagen';
        $info['organisation'] = 'HolleywoodStudio.com';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('consumer_key', 'consumer_secret', 'access_token', 'access_token_secret', 'screen_name', 'title', 'template_main', 'template_style', 'max_statuses', 'style', 'show_profile_image', 'follow_button_size', 'twitter_logo_color', 'twitter_logo_size');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = array('block_twitter_feed__cache_on');
        $info['ttl'] = intval(get_option('twitterfeed_update_time'));
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        safe_ini_set('ocproducts.type_strictness', '0');

        // Set up variables from parameters
        $api_key = array_key_exists('consumer_key', $map) ? $map['consumer_key'] : '';
        $api_secret = array_key_exists('consumer_secret', $map) ? $map['consumer_secret'] : '';
        $token = array_key_exists('access_token', $map) ? $map['access_token'] : '';
        $token_secret = array_key_exists('access_token_secret', $map) ? $map['access_token_secret'] : '';
        if ((get_option('twitterfeed_use_twitter_support_config') == '1') && ($api_key == '' || $api_secret == '' || $token == '' || $token_secret == '')) {
            $api_key = get_option('twitter_api_key');
            $api_secret = get_option('twitter_api_secret');
            $token = get_value('twitter_oauth_token', null, true);
            $token_secret = get_value('twitter_oauth_token_secret', null, true);
        }
        $twitter_name = array_key_exists('screen_name', $map) ? $map['screen_name'] : 'coolweens';
        $twitter_title = array_key_exists('title', $map) ? $map['title'] : 'Twitter Feed';
        $twitter_tempmain = array_key_exists('template_main', $map) ? $map['template_main'] : '';
        if ($twitter_tempmain) {
            $twitter_tempmain = '_' . $twitter_tempmain;
        }
        $twitter_templatemain = 'BLOCK_TWITTER_FEED' . $twitter_tempmain;
        $twitter_tempstyle = array_key_exists('template_style', $map) ? $map['template_style'] : '';
        if ($twitter_tempstyle) {
            $twitter_tempstyle = '_' . $twitter_tempstyle;
        }
        $twitter_templatestyle = 'BLOCK_TWITTER_FEED_STYLE' . $twitter_tempstyle;
        $twitter_maxstatuses = array_key_exists('max_statuses', $map) ? intval($map['max_statuses']) : 10;
        $twitter_showprofileimage = array_key_exists('show_profile_image', $map) ? intval($map['show_profile_image']) : 1;
        $twitter_followbuttonsize = array_key_exists('follow_button_size', $map) ? intval($map['follow_button_size']) : 1;
        $twitter_style = array_key_exists('style', $map) ? intval($map['style']) : 1;
        $twitter_url = 'http://www.twitter.com/' . $twitter_name;
        $twitter_logocolorparam = array_key_exists('twitter_logo_color', $map) ? intval($map['twitter_logo_color']) : 1;
        $twitter_logosizeparam = array_key_exists('twitter_logo_size', $map) ? intval($map['twitter_logo_size']) : 2;
        $twitter_error = '';

        // Sanitize the input - be sure some key values are in range
        if (($twitter_maxstatuses < 1) || ($twitter_maxstatuses > 200)) {
            $twitter_maxstatuses = 10;
        }
        if (($twitter_showprofileimage < 0) || ($twitter_showprofileimage > 1)) {
            $twitter_showprofileimage = 1;
        }
        if (($twitter_followbuttonsize < 0) || ($twitter_followbuttonsize > 2)) {
            $twitter_followbuttonsize = 1;
        }
        if (($twitter_logocolorparam < 1) || ($twitter_logocolorparam > 3)) {
            $twitter_logocolorparam = 1;
        }
        if (($twitter_logosizeparam < 1) || ($twitter_logosizeparam > 3)) {
            $twitter_logosizeparam = 2;
        }

        // Set twitter logo code
        if ($twitter_logocolorparam == 2) {
            $twitter_color = 'gray';
        } elseif ($twitter_logocolorparam == 3) {
            $twitter_color = 'black';
        } else {
            $twitter_color = 'blue';
        }
        if ($twitter_logosizeparam == 2) {
            $twitter_size = '32';
        } elseif ($twitter_logosizeparam == 3) {
            $twitter_size = '48';
        } else {
            $twitter_size = '16';
        }
        //twitter_logo_img_code is set with the code needed for the $IMG Tempcode
        $twitter_logo_img_code = 'twitter_feed/bird_' . $twitter_color . '_' . $twitter_size;

        // Create template object
        $content = new Tempcode();

        // Check for Twitter Support addon dependency before we go any further
        if (!addon_installed('twitter_support', true)) {
            $twitter_error = 'The Twitter Support addon is not installed. The Twitter Feed Integration Block will not work unless the Twitter Support addon is installed. Please download and install the appropriate version of the Twitter Support addon from compo.sr.<br />';
            return do_template($twitter_templatemain, array(
                'TWITTER_TITLE' => $twitter_title,
                'TWITTER_ERROR' => $twitter_error,
                'CONTENT' => $content,
                'STYLE' => strval($twitter_style),
                'TWITTER_LOGO_IMG_CODE' => $twitter_logo_img_code,
                'USER_SCREEN_NAME' => $twitter_name
            ));
        }

        // Initiate Twitter connection
        require_code('twitter');
        $twitter = new Twitter($api_key, $api_secret);
        $twitter->setOAuthToken($token);
        $twitter->setOAuthTokenSecret($token_secret);

        // Get statuses
        try {
            $twitter_statuses = $twitter->statusesUserTimeline(null, $twitter_name, null, null, $twitter_maxstatuses, null, false, true, true);
        } catch (TwitterException $e) {
            $twitter_error = $e->getMessage();
            $twitter_error .= '<br />';
            return do_template($twitter_templatemain, array(
                'TWITTER_TITLE' => $twitter_title,
                'TWITTER_ERROR' => $twitter_error,
                'CONTENT' => $content,
                'STYLE' => strval($twitter_style),
                'TWITTER_LOGO_IMG_CODE' => $twitter_logo_img_code,
                'USER_SCREEN_NAME' => $twitter_name
            ));
        }

        if (count($twitter_statuses) == 0) {
            return do_template('BLOCK_NO_ENTRIES', array('_GUID' => '24982bb4c3bfdc2ada1b4ccad92b5039', 'TITLE' => 'Twitter Profile Details', 'MESSAGE' => do_lang_tempcode('NO_ENTRIES'), 'ADD_NAME' => '', 'SUBMIT_URL' => ''));
        }

        // Generate variables and pass them to Style template for each status (status=tweet)
        foreach ($twitter_statuses as $status) {
            // Process $tweet_text to convert twitter screen names, hashtags, emails and urls into clickable links
            $tweet_text = ' ' . htmlentities($status['text'], ENT_NOQUOTES, 'UTF-8');
            $tweet_text = preg_replace_callback("#@(\w+)#is", array($this, '_convert_name_callback'), $tweet_text);
            $tweet_text = preg_replace_callback("#\#(\w+)#is", array($this, '_convert_hashtag_callback'), $tweet_text);
            $tweet_text = preg_replace_callback("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#is", array($this, '_convert_url_callback'), $tweet_text);
            $tweet_text = preg_replace_callback("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", array($this, '_convert_website_callback'), $tweet_text);
            $tweet_text = preg_replace("#(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $tweet_text);

            // Process $twitter_userdescription to convert twitter screen names, hashtags, emails and urls into clickable links
            $twitter_userdescription = ' ' . htmlentities($status['user']['description'], ENT_NOQUOTES, 'UTF-8');
            $twitter_userdescription = preg_replace_callback("#@(\w+)#is", array($this, '_convert_name_callback'), $twitter_userdescription);
            $twitter_userdescription = preg_replace_callback("#\#(\w+)#is", array($this, '_convert_hashtag_callback'), $twitter_userdescription);
            $twitter_userdescription = preg_replace_callback("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#is", array($this, '_convert_url_callback'), $twitter_userdescription);
            $twitter_userdescription = preg_replace_callback("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", array($this, '_convert_website_callback'), $twitter_userdescription);
            $twitter_userdescription = preg_replace("#(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $twitter_userdescription);

            // Generate retweet, favorite, reply, and user page URLs
            $retweet_url = 'https://twitter.com/intent/retweet?tweet_id=' . strval($status['id']);
            $favorite_url = 'https://twitter.com/intent/favorite?tweet_id=' . strval($status['id']);
            $reply_url = 'https://twitter.com/intent/tweet?in_reply_to=' . strval($status['id']);
            $user_page_url = 'http://www.twitter.com/' . urlencode($status['user']['screen_name']);

            // Generate follow buttons
            // must have the following javascript code in the main template for these to fully work:
            // <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
            $follow_button_normal = "<a href=\"https://twitter.com/" . escape_html(urlencode($status['user']['screen_name'])) . "\" class=\"twitter-follow-button\" data-show-count=\"false\" data-show-screen-name=\"false\">Follow @" . escape_html($status['user']['screen_name']) . "</a>";
            $follow_button_large = "<a href=\"https://twitter.com/" . escape_html(urlencode($status['user']['screen_name'])) . "\" class=\"twitter-follow-button\" data-show-count=\"false\" data-size=\"large\" data-show-screen-name=\"false\">Follow @" . escape_html($status['user']['screen_name']) . "</a>";

            // Convert created_at date/time to unix timestamp and 'time ago' string...

            // Get current timestamp
            $current_timestamp = strtotime('now');

            // Get timestamp of created_at string
            $tweet_timestamp = strtotime($status['created_at']);

            // Get difference
            $time_ago_timestamp = $current_timestamp - $tweet_timestamp;

            // Calculate different time values
            $minute = 60;
            $hour = $minute * 60;
            $day = $hour * 24;
            $week = $day * 7;

            if (is_numeric($time_ago_timestamp) && $time_ago_timestamp > 0) {
                //if less then 3 seconds
                if ($time_ago_timestamp < 3) {
                    $time_ago = 'right now'; // if less then minute
                } elseif ($time_ago_timestamp < $minute) {
                    $time_ago = strval($time_ago_timestamp) . ' seconds ago'; // if less then 2 minutes
                } elseif ($time_ago_timestamp < $minute * 2) {
                    $time_ago = 'about 1 minute ago'; // if less then hour
                } elseif ($time_ago_timestamp < $hour) {
                    $time_ago = strval(intval(floor($time_ago_timestamp / $minute))) . ' minutes ago'; // if less then 2 hours
                } elseif ($time_ago_timestamp < $hour * 2) {
                    $time_ago = 'about 1 hour ago'; // if less then day
                } elseif ($time_ago_timestamp < $day) {
                    $time_ago = strval(intval(floor($time_ago_timestamp / $hour))) . ' hours ago'; // if more then day, but less then 2 days
                } elseif ($time_ago_timestamp > $day && $time_ago_timestamp < $day * 2) {
                    $time_ago = 'yesterday'; // if less then year
                } elseif ($time_ago_timestamp < $day * 365) {
                    $time_ago = strval(intval(floor($time_ago_timestamp / $day))) . ' days ago'; // else more than a year
                } else {
                    $time_ago = 'over a year ago';
                }
            }

            $content->attach(do_template($twitter_templatestyle, array(
                'TWEET_TIME_AGO' => $time_ago,
                'TWITTER_LOGO_IMG_CODE' => $twitter_logo_img_code,
                'FOLLOW_BUTTON_SIZE' => strval($twitter_followbuttonsize),
                'SHOW_PROFILE_IMAGE' => strval($twitter_showprofileimage),
                'STYLE' => strval($twitter_style),
                'REPLY_URL' => $reply_url,
                'USER_PAGE_URL' => $user_page_url,
                'RETWEET_URL' => $retweet_url,
                'FOLLOW_BUTTON_NORMAL' => $follow_button_normal,
                'FOLLOW_BUTTON_LARGE' => $follow_button_large,
                'FAVORITE_URL' => $favorite_url,
                'TWEET_CREATED_AT' => $status['created_at'],
                'TWEET_ID' => $status['id'],
                'TWEET_RETWEET_COUNT' => strval($status['retweet_count']),
                'TWEET_FAVORITED' => strval($status['favorited']),
                'TWEET_RETWEETED' => strval($status['retweeted']),
                'TWEET_TEXT' => $tweet_text,
                'USER_NAME' => $status['user']['name'],
                'USER_SCREEN_NAME' => $status['user']['screen_name'],
                'USER_LOCATION' => $status['user']['location'],
                'USER_URL' => $status['user']['url'],
                'USER_DESCRIPTION' => $twitter_userdescription,
                'USER_FOLLOWERS_COUNT' => strval($status['user']['followers_count']),
                'USER_FOLLOWING_COUNT' => strval($status['user']['friends_count']),
                'USER_CREATED_AT' => $status['user']['created_at'],
                'USER_FAVOURITES_COUNT' => strval($status['user']['favourites_count']),
                'USER_STATUS_COUNT' => strval($status['user']['statuses_count']),
                'USER_VERIFIED' => $status['user']['verified'],
                'USER_PROFILE_IMG_URL' => $status['user']['profile_image_url']
            )));
        }

        // Pass all the Styled statuses to the main template container
        return do_template($twitter_templatemain, array(
            'TWITTER_ERROR' => $twitter_error,
            'TWITTER_TITLE' => $twitter_title,
            'CONTENT' => $content,
            'TWITTER_LOGO_IMG_CODE' => $twitter_logo_img_code,
            'FOLLOW_BUTTON_SIZE' => strval($twitter_followbuttonsize),
            'SHOW_PROFILE_IMAGE' => strval($twitter_showprofileimage),
            'STYLE' => strval($twitter_style),
            'USER_PAGE_URL' => $user_page_url,
            'FOLLOW_BUTTON_NORMAL' => $follow_button_normal,
            'FOLLOW_BUTTON_LARGE' => $follow_button_large,
            'USER_NAME' => $status['user']['name'],
            'USER_SCREEN_NAME' => $status['user']['screen_name'],
            'USER_LOCATION' => $status['user']['location'],
            'USER_URL' => $status['user']['url'],
            'USER_DESCRIPTION' => $twitter_userdescription,
            'USER_FOLLOWERS_COUNT' => strval($status['user']['followers_count']),
            'USER_FOLLOWING_COUNT' => strval($status['user']['friends_count']),
            'USER_CREATED_AT' => $status['user']['created_at'],
            'USER_FAVOURITES_COUNT' => strval($status['user']['favourites_count']),
            'USER_STATUS_COUNT' => strval($status['user']['statuses_count']),
            'USER_VERIFIED' => $status['user']['verified'],
            'USER_PROFILE_IMG_URL' => $status['user']['profile_image_url'],
        ));
    }

    /**
     * Used to convert a twitter @name to a clickable link. preg_replace_callback callback
     *
     * @param  array $matches Array of matches
     * @return string Substituted text
     */
    public function _convert_name_callback($matches)
    {
        return '<a href="http://www.twitter.com/' . escape_html(urlencode($matches[1])) . '" target="_blank" rel="nofollow">@' . escape_html($matches[1]) . '</a>';
    }

    /**
     * Used to convert a twitter #hashtag to a clickable link. preg_replace_callback callback
     *
     * @param  array $matches Array of matches
     * @return string Substituted text
     */
    public function _convert_hashtag_callback($matches)
    {
        return '<a href="https://twitter.com/#!/search?q=%23' . escape_html(urlencode($matches[1])) . '" target="_blank" rel="nofollow">#' . escape_html($matches[1]) . '</a>';
    }

    /**
     * Used to convert a full url (i.e. http://www.twitter.com/) into a clickable link. preg_replace_callback callback
     *
     * @param  array $matches Array of matches
     * @return string Substituted text
     */
    public function _convert_url_callback($matches)
    {
        return $matches[1] . '<a href="' . escape_html($matches[2]) . '" target="_blank" >' . escape_html($matches[2]) . '</a>';
    }

    /**
     * Used to convert a website (i.e. www.twitter.com) into a clickable link. preg_replace_callback callback
     *
     * @param  array $matches Array of matches
     * @return string Substituted text
     */
    public function _convert_website_callback($matches)
    {
        return $matches[1] . '<a href="http://' . escape_html($matches[2]) . '" target="_blank" >' . escape_html($matches[2]) . '</a>';
    }
}

/**
 * Find the cache signature for the block.
 *
 * @param  array $map The block parameters.
 * @return array The cache signature.
 */
function block_twitter_feed__cache_on($map)
{
    return array(
        array_key_exists('api_key', $map) ? $map['api_key'] : '',
        array_key_exists('api_secret', $map) ? $map['api_secret'] : '',
        array_key_exists('twitter_logo_size', $map) ? intval($map['twitter_logo_size']) : 2,
        array_key_exists('twitter_logo_color', $map) ? intval($map['twitter_logo_color']) : 1,
        array_key_exists('max_statuses', $map) ? intval($map['max_statuses']) : 10,
        array_key_exists('style', $map) ? intval($map['style']) : 1,
        array_key_exists('title', $map) ? $map['title'] : '',
        array_key_exists('screen_name', $map) ? $map['screen_name'] : 'coolweens',
        array_key_exists('template_main', $map) ? $map['template_main'] : '',
        array_key_exists('template_style', $map) ? $map['template_style'] : '',
        array_key_exists('show_profile_image', $map) ? $map['show_profile_image'] : '1',
        array_key_exists('follow_button_size', $map) ? $map['follow_button_size'] : '1'
    );
}
