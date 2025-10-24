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
class Hook_symbol_HAS_READ
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
            'compile' => SYMBOL_COMPILE_STATIC_IF_AGGRESSIVE,
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
            return '1'; // Not enough parameters
        }
        if (is_guest()) {
            return '1'; // Guests can't be tracked, assume read (so no unread icon might show, which is the normal use-case for this feature)
        }
        if (!addon_installed('content_read_tracking')) {
            return '0'; // Not installed yet
        }

        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('content_read', 'r_time', [
            'r_content_type' => $param[0],
            'r_content_id' => $param[1],
            'r_member_id' => get_member()
        ]);

        if ($test !== null) {
            return '1';
        }

        // First check this isn't really old content that we no longer track
        if ((!empty($param[2])) && (!empty($param[3]))) {
            $cleanup_days = intval($param[2]);
            $content_time = intval($param[3]);
            if ($content_time < time() - 60 * 60 * 24 * $cleanup_days) {
                return '1'; // Content too old, not tracking, assume read
            }
        }

        return '0';
    }
}
