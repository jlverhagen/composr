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
 * @package    karma
 */

/**
 * Hook class.
 */
class Hook_symbol_KARMA
{
    /**
     * Get information about this symbol.
     *
     * @return ?array Array of information (null: hook disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('karma')) {
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
        // Param 0 is member ID. Defaults to current member.
        if ((empty($param[0]))) {
            $member_id = get_member();
        } else {
            $member_id = intval($param[0]);
        }

        if (is_guest($member_id)) {
            return '0'; // Guests have no karma
        }

        require_code('karma');
        $karma = get_karma($member_id);

        if (empty($param[1])) {
            $param[1] = '0';
        }

        // Param 1: the karma to return. 0 = total karma (good - bad karma), 1 = good karma, 2 = bad karma.
        switch ($param[1]) {
            case '0':
                return strval($karma[0] - $karma[1]);
            case '1':
                if (!has_privilege(get_member(), 'view_bad_karma')) {
                    return '';
                }
                return strval($karma[0]);
            case '2':
                if (!has_privilege(get_member(), 'view_bad_karma')) {
                    return '';
                }
                return strval($karma[1]);
        }

        return '';
    }
}
