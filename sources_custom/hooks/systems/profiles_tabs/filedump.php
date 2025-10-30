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
 * @package    member_filedumps
 */

/**
 * Hook class.
 */
class Hook_profiles_tabs_filedump
{
    /**
     * Find whether this hook is active.
     *
     * @param  MEMBER $member_id_of The ID of the member who is being viewed
     * @param  MEMBER $member_id_viewing The ID of the member who is doing the viewing
     * @return boolean Whether this hook is active
     */
    public function is_active(int $member_id_of, int $member_id_viewing) : bool
    {
        if (!addon_installed('member_filedumps')) {
            return false;
        }
        if (!addon_installed('filedump')) {
            return false;
        }

        return (($member_id_of == $member_id_viewing) || (has_privilege($member_id_viewing, 'assume_any_member')));
    }

    /**
     * Render function for profile tab hooks.
     *
     * @param  MEMBER $member_id_of The ID of the member who is being viewed
     * @param  MEMBER $member_id_viewing The ID of the member who is doing the viewing
     * @param  boolean $leave_to_ajax_if_possible Whether to leave the tab contents null, if this hook supports it, so that AJAX can load it later
     * @return array A tuple: The tab title, the tab contents, the suggested tab order, the icon
     */
    public function render_tab(int $member_id_of, int $member_id_viewing, bool $leave_to_ajax_if_possible = false) : array
    {
        require_lang('filedump');

        $title = do_lang_tempcode('_FILEDUMP');

        $order = 70;

        if ($leave_to_ajax_if_possible) {
            return [$title, null, $order, 'menu/cms/filedump'];
        }

        $content = do_template('CNS_MEMBER_PROFILE_FILEDUMP', ['_GUID' => '87c683590a6e2d435d877cec1c97baba', 'MEMBER_ID' => strval($member_id_of)]);

        return [$title, $content, $order, 'menu/cms/filedump'];
    }
}
