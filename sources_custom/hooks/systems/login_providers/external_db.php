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
 * @package    external_db_login
 */

/**
 * Hook class.
 */
class Hook_login_provider_external_db
{
    /**
     * Standard login provider hook.
     *
     * @param  ?MEMBER $member_id Member ID already detected as logged in (null: none). May be a guest ID.
     * @return ?MEMBER Member ID now detected as logged in (null: none). May be a guest ID.
     */
    public function try_login(?int $member_id) : ?int
    {
        if (!addon_installed('external_db_login')) {
            return $member_id;
        }

        if (get_forum_type() != 'cns') {
            return $member_id;
        }

        if (($member_id === null) || (is_guest($member_id))) {
            require_code('external_db');

            $record = external_db_user_from_session();

            if ($record === null) {
                return $member_id;
            }

            // Existing Composr user?
            $username_field = get_value('external_db_login__username_field', null, true);
            $email_address_field = get_value('external_db_login__email_address_field', null, true);
            $member_id = null;
            if (get_option('one_per_email_address') != '0') {
                $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_email_address($record[$email_address_field]);
            }
            if (($member_id === null) && (get_option('one_per_email_address') != '2')) {
                $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($record[$username_field]);
            }
            if ($member_id !== null) {
                external_db_user_sync($member_id, $record);

                // Return existing user
                return $member_id;
            }

            // Create new user
            return external_db_user_add($record);
        }

        return $member_id;
    }
}
