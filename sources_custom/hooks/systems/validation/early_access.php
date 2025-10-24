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
 * @package    early_access
 */

/**
 * Hook class.
 */
 class Hook_validation_early_access
 {
    /**
     * Check if the given member has the privilege to access the given content by means of validation.
     * This function will throw an access denied if the given member does not have the privilege.
     *
     * @param  ID_TEXT $content_type The content type being accessed
     * @param  ID_TEXT $content_id The content ID being accessed
     * @param  MEMBER $member_viewing The member trying to view the content
     * @param  array $bypass_members Array of members that should be granted access even if they don't have the privilege, such as the submitters
     * @param  boolean $ret Whether not to error with access denied and simply return a boolean instead
     * @return boolean Whether we have the privilege
     */
    public function check_jump_to_not_validated(string $content_type, string $content_id, int $member_viewing, array $bypass_members = [], bool $ret = false) : bool
    {
        if (!addon_installed('early_access')) {
            return false; // No privilege if the addon is not installed
        }

        require_code('early_access');
        return check_has_special_page_access_for_unvalidated_content($member_viewing, $content_type, $content_id);
    }
 }
