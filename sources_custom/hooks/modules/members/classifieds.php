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
 * @package    classified_ads
 */

/**
 * Hook class.
 */
class Hook_members_classifieds
{
    /**
     * Find member-related links to inject to details section of the about tab of the member profile.
     *
     * @param  MEMBER $member_id The ID of the member we are getting links for
     * @return array List of pairs: title to value
     */
    public function run(int $member_id) : array
    {
        if (!addon_installed('classified_ads')) {
            return [];
        }

        if (!has_actual_page_access(get_member(), 'classifieds', get_module_zone('classifieds'))) {
            return [];
        }

        require_lang('classifieds');

        $result = [];

        if (($member_id == get_member()) || (has_privilege(get_member(), 'assume_any_member'))) {
            $result[] = ['content', do_lang('CLASSIFIED_ADVERTS'), build_url(['page' => 'classifieds', 'type' => 'browse', 'member_id' => $member_id], get_module_zone('classifieds')), 'spare/classifieds'];
        }

        return $result;
    }
}
