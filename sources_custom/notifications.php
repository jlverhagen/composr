<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    password_censor
 */

if (!function_exists('init__notifications')) {
    function init__notifications($in = null)
    {
        $before = '$dispatcher = ';
        $after = 'if ($notification_code == \'cns_topic\' && is_numeric($code_category) && is_null($GLOBALS[\'FORUM_DB\']->query_select_value_if_there(\'f_topics\', \'t_forum_id\', array(\'id\' => intval($code_category)))) || $notification_code == \'cns_new_pt\' || $notification_code == \'ticket_new_staff\' || $notification_code == \'ticket_reply\' || $notification_code == \'ticket_reply_staff\') { require_code(\'password_censor\'); $message = _password_censor($message, PASSWORD_CENSOR__INTERACTIVE_SCAN); } $dispatcher = ';
        $in = str_replace($before, $after, $in);
        return $in;
    }
}
