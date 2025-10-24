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
 * @package    content_read_tracking
 */

/**
 * Hook class.
 */
class Hook_symbol_MARK_READ
{
    /**
     * Get information about this symbol.
     *
     * @return ?array Array of information (null: hook disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('content_read_tracking')) {
            return null;
        }

        return [
            'compile' => SYMBOL_COMPILE_STATIC_NONE,
            'public' => false,
        ];
    }

    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @param  string $lang The language to evaluate this symbol in (some symbols refer to language elements)
     * @param  array $escaped Array of escaping operations
     * @return string Result
     */
    public function run(array $param, string $lang, array $escaped) : string
    {
        if ((empty($param[0])) || (!isset($param[1]))) {
            return ''; // Not enough parameters
        }
        if (is_guest()) {
            return ''; // Guests can't be tracked
        }

        $GLOBALS['SITE_DB']->query_insert('content_read', [
            'r_content_type' => $param[0],
            'r_content_id' => $param[1],
            'r_member_id' => get_member(),
            'r_time' => time(),
        ], false, true);

        // Cleanup stale data
        $cleanup_days = (!empty($param[2])) ? intval($param[2]) : 0;
        if (($cleanup_days != 0) && (mt_rand(0, 100) == 1/*Only do 1% of the time, for performance reasons*/)) {
            cms_register_shutdown_function_safe(function () use ($cleanup_days) {
                if (!$GLOBALS['SITE_DB']->table_is_locked('content_read')) {
                    $GLOBALS['SITE_DB']->query('DELETE FROM ' . get_table_prefix() . 'content_read WHERE r_time<' . strval(time() - 60 * 60 * 24 * $cleanup_days), 500/*to reduce lock times*/, 0, true); // Errors suppressed in case DB write access broken
                }
            });
        }

        return ''; // Not output for this symbol ever
    }
}
