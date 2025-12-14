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
 * @package    cms_release_build
 */

/**
 * Hook class.
 */
class Hook_page_groupings_make_release
{
    /**
     * Run function for do_next_menu hooks. They find links to put on standard navigation menus of the system.
     *
     * @param  ?MEMBER $member_id Member ID to run as (null: current member)
     * @param  boolean $extensive_docs Whether to use extensive documentation tooltips, rather than short summaries
     * @return array List of tuple of links (page grouping, icon, do-next-style linking data), label, help (optional) and/or nulls
     */
    public function run(?int $member_id = null, bool $extensive_docs = false) : array
    {
        if (!addon_installed('cms_release_build')) {
            return [];
        }

        require_lang('cms_release_build');

        return [
            ['tools', 'admin/tool', ['plug_guid', [], get_page_zone('plug_guid', false, 'adminzone', 'minimodules')], do_lang_tempcode('RELEASE_TOOLS_FIX_GUIDS')],
            ['tools', 'admin/tool', ['admin_make_release', [], get_page_zone('admin_make_release', false, 'adminzone', 'modules')], do_lang_tempcode('RELEASE_TOOLS_MAKE_RELEASE')],
            ['tools', 'admin/tool', ['admin_make_hotfix', [], get_page_zone('admin_make_hotfix', false, 'adminzone', 'modules')], do_lang_tempcode('RELEASE_TOOLS_MAKE_HOTFIX')],
            ['tools', 'admin/tool', ['admin_modularisation', [], get_page_zone('admin_modularisation', false, 'adminzone', 'modules')], do_lang_tempcode('RELEASE_TOOLS_MODULARISATION')],
            ['tools', 'admin/tool', ['doc_index_build', [], get_page_zone('doc_index_build', false, 'adminzone', 'minimodules')], do_lang_tempcode('DOC_TOOLS_ADDON_TUTORIAL_INDEX')],
        ];
    }
}
