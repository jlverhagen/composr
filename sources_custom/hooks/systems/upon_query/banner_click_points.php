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
 * @package    banner_click_points
 */

/**
 * Hook class.
 */
class Hook_upon_query_banner_click_points
{
    public function run_post($ob, $query, $max, $start, $fail_ok, $get_insert_id, $ret)
    {
        if ($query[0] == 'S') {
            return;
        }

        if (get_mass_import_mode()) {
            return;
        }

        if (strpos($query, 'INTO ' . get_table_prefix() . 'banner_clicks') !== false) {
            if (!addon_installed('banner_click_points')) {
                return;
            }

            if (!addon_installed('banners')) {
                return;
            }
            if (!addon_installed('points')) {
                return;
            }

            load_user_stuff();
            $GLOBALS['FORUM_DRIVER']->forum_layer_initialise();

            global $FORCE_INVISIBLE_GUEST, $MEMBER_CACHED;
            $FORCE_INVISIBLE_GUEST = false;
            $MEMBER_CACHED = null;

            if (!is_guest()) {
                require_code('comcode');
                require_code('permissions');

                $member_id = get_member();

                $dest = get_param_string('dest', '');

                $cnt = $GLOBALS['SITE_DB']->query_select_value('banner_clicks', 'COUNT(*)', [
                    'c_member_id' => $member_id,
                    'c_banner_id' => $dest,
                ]);
                if ($cnt == 0) {
                    require_code('points');
                    require_code('points2');
                    points_credit_member($member_id, 'Clicking banner #' . strval($dest), 1, 0, null, 0, 'banner', 'click', $dest);
                }
            }
        }
    }
}
