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
 * @package    user_mappr
 */

/**
 * Hook class.
 */
class Hook_upon_query_google_maps_users
{
    public function run_post($ob, $query, $max, $start, $fail_ok, $get_insert_id, $ret)
    {
        if ($query[0] == 'S') {
            return;
        }

        if ((strpos($query, 'f_member_custom_fields') !== false) && ((strpos($query, 'INSERT INTO ') !== false) || (strpos($query, 'UPDATE ') !== false))) {
            if (!addon_installed('user_mappr')) {
                return;
            }

            if (function_exists('delete_cache_entry')) {
                delete_cache_entry('main_google_map_users');
            }
        }
    }
}
