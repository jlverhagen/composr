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

class Hx_notification_catalogues extends Hook_notification_catalogues
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
        // By default, staff should get notifications for the tracker catalogue
        if (($notification_code == 'catalogue_entry__tracker') && $GLOBALS['FORUM_DRIVER']->is_staff($member_id)) {
            return A__STATISTICAL;
        }

        return parent::get_initial_setting($notification_code, $category, $member_id);
    }

    /**
     * Find a bitmask of settings (e-mail, SMS, etc) a notification code supports for listening on.
     *
     * @param  ID_TEXT $notification_code Notification code
     * @return integer Allowed settings
     */
    public function allowed_settings(string $notification_code) : int
    {
        // For security, do not allow notifications on the tracker catalogue except for staff
        if (($notification_code == 'catalogue_entry__tracker') && !$GLOBALS['FORUM_DRIVER']->is_staff(get_member())) {
            return A_NA;
        }

        return parent::allowed_settings($notification_code);
    }
}
