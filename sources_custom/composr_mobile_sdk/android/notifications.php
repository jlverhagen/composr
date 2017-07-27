<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    composr_mobile_sdk
 */

/**
 * Android notifications sender class.
 */
class AndroidPushNotifications
{
    /**
     * Called to process the notification for Android.
     *
     * @param  MEMBER $to_member_id Member to send to
     * @param  ID_TEXT $notification_code The notification code to use
     * @param  ?SHORT_TEXT $code_category The category within the notification code (null: none)
     * @param  SHORT_TEXT $subject Message subject (in Comcode)
     * @param  LONG_TEXT $message Message body
     * @param  array $properties Custom properties to add to outbound message
     * @param  integer $from_member_id The member ID doing the sending. Either a MEMBER or a negative number (e.g. A_FROM_SYSTEM_UNPRIVILEGED)
     * @param  integer $priority The message priority (1=urgent, 3=normal, 5=low)
     * @range  1 5
     * @param  boolean $no_cc Whether to NOT CC to the CC address
     * @param  ?array $attachments An list of attachments (each attachment being a map, path=>filename) (null: none)
     * @param  boolean $use_real_from Whether we will make a "reply to" direct -- we only do this if we're allowed to disclose email addresses for this particular notification type (i.e. if it's a direct contact)
     */
    private function android_dispatch($to_member_id, $notification_code, $code_category, $subject, $message, $properties, $from_member_id, $priority, $no_cc, $attachments, $use_real_from)
    {
        $notification = array(
            'title' => $subject,
            'body' => $message,
            'icon' => get_option('android_icon_name'),
        );

        $data = array();
        $data['identifier'] = $notification_code . '_' . (is_null($code_category) ? '' : $code_category) . '_' . strval(get_member()) . '_' . strval(time());
        $data['notification_code'] = $notification_code;
        $data['code_category'] = is_null($code_category) ? '' : $code_category;
        $data['from_id'] = strval($from_member_id);
        $data['from_username'] = ($from_member_id >= 0) ? $GLOBALS['FORUM_DRIVER']->get_username($from_member_id) : '';
        $data['from_displayname'] = ($from_member_id >= 0) ? $GLOBALS['FORUM_DRIVER']->get_username($from_member_id, true) : '';
        $data['from_avatar_url'] = ($from_member_id >= 0) ? $GLOBALS['FORUM_DRIVER']->get_member_avatar_url($from_member_id) : '';
        $data['from_photo_url'] = ($from_member_id >= 0) ? $GLOBALS['FORUM_DRIVER']->get_member_photo_url($from_member_id) : '';
        $data['to_id'] = strval($to_member_id);
        $data['to_username'] = $GLOBALS['FORUM_DRIVER']->get_username($to_member_id);
        $data['to_displayname'] = $GLOBALS['FORUM_DRIVER']->get_username($to_member_id, true);

        uasort($properties, 'strlen');
        $properties = array_reverse($properties, true);
        foreach ($properties as $key => $val) {
            if (strlen(serialize($data)) > 800) {
                break;
            }

            $data[$key] = $val;
        }

        $token = $GLOBALS['SITE_DB']->query_select_value('device_token_details', 'device_token', array('member_id' => $to_member_id, 'token_type' => 'android'));
        $fields = array(
            'to' => $token,
            'notification' => $notification,
            'data' => $data,
            'priority' => ($priority < 3) ? 'high' : 'normal',
            'delay_while_idle' => ($priority == 5),
        );

        $api_access_key = get_option('enable_notifications_instant_android');
        $extra_headers = array(
            'Authorization' => 'key=' . $api_access_key,
        );
        $url = 'https://android.googleapis.com/gcm/send';
        require_code('json');
        $post_params = json_encode($fields);
        http_download_file($url, null, true, false, 'Composr', array($post_params), null, null, null, null, null, null, null, 6.0, true, null, $extra_headers, null, 'application/json');
    }
}
