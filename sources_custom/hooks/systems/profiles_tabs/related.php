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
 * @package    related_members
 */

/**
 * Hook class.
 */
class Hook_profiles_tabs_related
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
        if (!addon_installed('related_members')) {
            return false;
        }

        require_lang('related');

        return (get_cms_cpf(do_lang('RELATED_CPF'), $member_id_of) != '');
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
        require_lang('related');

        $title = do_lang_tempcode('RELATED_MEMBERS');

        $order = 150;

        if ($leave_to_ajax_if_possible) {
            return [$title, null, $order, 'menu/social/members'];
        }

        require_css('cns_member_directory');

        $cpf_value = get_cms_cpf(do_lang('RELATED_CPF'), $member_id_of);
        $filter = do_lang('RELATED_CPF') . '=' . $cpf_value . ',id<>' . strval($GLOBALS['FORUM_DRIVER']->get_guest_id()) . ',id<>' . strval($member_id_of);
        $content = do_block('main_multi_content', ['param' => 'member', 'render_mode' => 'boxes', 'pinned' => '', 'sort' => 'title', 'filter' => $filter, 'no_links' => '1', 'guid' => 'module']);

        return [$title, $content, $order, 'menu/social/members'];
    }
}
