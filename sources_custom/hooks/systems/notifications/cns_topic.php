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
 * @package    cms_homesite_tracker
 */

/*FORCE_ORIGINAL_LOAD_FIRST*/

class Hx_notification_cns_topic extends Hook_notification_cns_topic
{
    /**
     * Get a list of members who have enabled this notification (i.e. have permission to AND have chosen to or are defaulted to).
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?SHORT_TEXT $category The category within the notification code (null: none)
     * @param  ?array $to_member_ids List of member IDs we are restricting to (null: no restriction). This effectively works as a intersection set operator against those who have enabled.
     * @param  ?integer $from_member_id The member ID doing the sending. Either a MEMBER or a negative number (e.g. A_FROM_SYSTEM_UNPRIVILEGED) (null: current member)
     * @param  integer $start Start position (for pagination)
     * @param  integer $max Maximum (for pagination)
     * @return array A pair: Map of members to their notification setting, and whether there may be more
     */
    public function list_members_who_have_enabled(string $notification_code, ?string $category = null, ?array $to_member_ids = null, ?int $from_member_id = null, int $start = 0, int $max = 300) : array
    {
        $ret = parent::list_members_who_have_enabled($notification_code, $category, $to_member_ids, $from_member_id, $start, $max);
        if (($notification_code != 'cns_topic') || ($category === null)) {
            return $ret;
        }

        $topic_id = $GLOBALS['FORUM_DRIVER']->find_topic_id_for_topic_identifier(get_option('comments_forum_name'), $category, do_lang('COMMENT'));
        if ($topic_id === null) {
            return $ret;
        }

        // No cns_topic notifications on comment topics; this could leak security exploits from the tracker
        return [[], false];
    }
}
