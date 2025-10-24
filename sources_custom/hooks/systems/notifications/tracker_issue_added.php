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

/**
 * Hook class.
 */
class Hook_notification_tracker_issue_added extends Hook_notification__Staff
{
    /**
     * Find the initial setting that members have for a notification code (only applies to the member_could_potentially_enable members).
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?SHORT_TEXT $category The category within the notification code (null: none)
     * @param  MEMBER $member_id The member the notification would be for
     * @return integer Initial setting
     */
    public function get_initial_setting(string $notification_code, ?string $category, int $member_id) : int
    {
        return A__STATISTICAL;
    }

    /**
     * Get a list of all the notification codes this hook can handle.
     * (Addons can define hooks that handle whole sets of codes, so hooks are written so they can take wide authority).
     *
     * @return array List of codes (mapping between code names, and a pair: section and labelling for those codes)
     */
    public function list_handled_codes() : array
    {
        if (!addon_installed('cms_homesite')) {
            return [];
        }
        if (!addon_installed('cms_homesite_tracker')) {
            return [];
        }

        $list = [];
        $list['tracker_issue_added'] = [do_lang('cms_homesite:TRACKER_ISSUE'), do_lang('NOTIFICATION_TYPE_tracker_issue_added')];
        return $list;
    }
}
