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
class Hook_member_boxes_karma
{
    /**
     * Find member box details.
     *
     * @param  MEMBER $member_id The ID of the member we are getting extra details for
     * @return ?array Map of extra box details (null: disabled)
     */
    public function run(int $member_id) : ?array
    {
        if (!addon_installed('karma')) {
            return null;
        }

        require_lang('karma');
        require_code('karma');

        $karma = get_karma($member_id);

        if (has_privilege(get_member(), 'view_bad_karma')) {
            return [
                do_lang('KARMA') => do_lang('GOOD_BAD_KARMA', escape_html(integer_format($karma[0])), escape_html(integer_format($karma[1]))),
            ];
        }

        return [
            do_lang('KARMA') => integer_format($karma[0] - $karma[1]),
        ];
    }
}
