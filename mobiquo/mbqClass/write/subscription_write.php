<?php /*

 The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at http://opensource.org/licenses/cpal_1.0.

 Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 See the License for the specific language governing rights and limitations under the License.

 The Original Code is Composr CMS.

 The Original Developer is the Initial Developer.

 The Initial Developer of the Original Code is Chris Graham.
 All portions of the code written by Chris Graham are Copyright (c) Christopher Graham. All Rights Reserved.

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cns_tapatalk
 */

/**
 * Composr API helper class.
 */
class CMSSubscriptionWrite
{
    /**
     * Set up notifications on a forum.
     *
     * @param  AUTO_LINK $forum_id Forum ID
     */
    public function subscribe_forum(int $forum_id)
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        $member_id = get_member();

        $notification_code = 'cns_topic';
        require_code('notifications');

        set_notifications($notification_code, 'forum:' . strval($forum_id), $member_id);
    }

    /**
     * Remove notifications on a forum.
     *
     * @param  ?AUTO_LINK $forum_id Forum ID (null: all)
     */
    public function unsubscribe_forum(?int $forum_id)
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        $member_id = get_member();

        $notification_code = 'cns_topic';
        require_code('notifications');

        if ($forum_id === null) {
            $subscriptions = $GLOBALS['FORUM_DB']->query_select('notifications_enabled', ['l_code_category'], ['l_notification_code' => $notification_code, 'l_member_id' => $member_id], ' AND l_code_category LIKE \'forum:%\'');
            foreach ($subscriptions as $subs) {
                reset_notifications($notification_code, $subs['l_code_category'], $member_id);
            }
        } else {
            reset_notifications($notification_code, 'forum:' . strval($forum_id), $member_id);
        }
    }

    /**
     * Set up notifications on a topic.
     *
     * @param  AUTO_LINK $topic_id Topic ID
     */
    public function subscribe_topic(int $topic_id)
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        $member_id = get_member();

        $notification_code = 'cns_topic';
        require_code('notifications');

        set_notifications($notification_code, strval($topic_id), $member_id);
    }

    /**
     * Remove notifications on a topic.
     *
     * @param  ?AUTO_LINK $topic_id Topic ID (null: all)
     */
    public function unsubscribe_topic(?int $topic_id)
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        $member_id = get_member();

        $notification_code = 'cns_topic';
        require_code('notifications');

        if ($topic_id === null) {
            $subscriptions = $GLOBALS['FORUM_DB']->query_select('notifications_enabled', ['l_code_category'], ['l_notification_code' => $notification_code, 'l_member_id' => $member_id], ' AND l_code_category NOT LIKE \'forum:%\'');
            foreach ($subscriptions as $subs) {
                if (is_numeric($subs['l_code_category'])) {
                    reset_notifications($notification_code, $subs['l_code_category'], $member_id);
                }
            }
        } else {
            reset_notifications($notification_code, strval($topic_id), $member_id);
        }
    }
}
