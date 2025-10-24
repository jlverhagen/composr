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
 * @package    activity_feed
 */

/**
 * Hook class.
 */
class Hook_profiles_tabs_activity_feed
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
        if (!addon_installed('activity_feed')) {
            return false;
        }

        return true;
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
        // Need to declare these here as the Tempcode engine can't look as deep, into a loop (I think), as it would need to, to find the block declaring the dependency
        require_lang('activity_feed');
        require_css('activity_feed');
        require_javascript('activity_feed');
        require_javascript('jquery');

        require_code('site');
        inject_feed_url('?mode=activities&select=' . strval($member_id_of), do_lang('ACTIVITY'));

        require_lang('activity_feed');

        $title = do_lang_tempcode('ACTIVITY');

        $order = 70;

        if ($leave_to_ajax_if_possible) {
            return [$title, null, $order, 'spare/activity'];
        }

        $content = do_template('CNS_MEMBER_PROFILE_ACTIVITY_FEED', ['_GUID' => '9fe3b8bb9a4975fa19631c43472b4539', 'MEMBER_ID' => strval($member_id_of)]);

        return [$title, $content, $order, 'spare/activity'];
    }
}
