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
 * @package    static_export
 */

/**
 * Hook class.
 */
class Hook_page_groupings_static_export
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
        if (!addon_installed('static_export')) {
            return [];
        }

        return [
            ['tools', 'admin/tool', ['static_export', ['utheme' => $GLOBALS['FORUM_DRIVER']->get_theme('')], get_page_zone('static_export', false, 'adminzone', 'minimodules')], make_string_tempcode('Export static site (TAR field)')],
            ['tools', 'admin/tool', ['static_export', ['dir' => '1', 'utheme' => $GLOBALS['FORUM_DRIVER']->get_theme('')], get_page_zone('static_export', false, 'adminzone', 'minimodules')], make_string_tempcode('Export static site (exports/static, with mtimes)')],
        ];
    }
}
