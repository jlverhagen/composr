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
class Hook_commandr_command_karmic_influence
{
    /**
     * Run function for Commandr hooks.
     *
     * @param  array $options The options with which the command was called
     * @param  array $parameters The parameters with which the command was called
     * @param  object $commandr_fs A reference to the Commandr filesystem object
     * @return array Array of stdcommand, stdhtml, stdout, and stderr responses
     */
    public function run(array $options, array $parameters, object &$commandr_fs) : array
    {
        $err = new Tempcode();
        if (!addon_installed__messaged('karma', $err)) {
            return ['', '', '', $err->evaluate()];
        }

        require_lang('karma');

        if ((array_key_exists('h', $options)) || (array_key_exists('help', $options))) {
            return ['', do_command_help('karmic_influence', ['h'], [true]), '', ''];
        }

        if (!array_key_exists(0, $parameters)) {
            $member_id = get_member();
        } else {
            $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($parameters[0]);
            if (($member_id === null) || (is_guest($member_id))) {
                return ['', '', '', do_lang('MEMBER_NO_EXIST')];
            }
        }

        if (($member_id != get_member()) && (!has_privilege(get_member(), 'view_others_karma'))) {
            require_lang('permissions');
            return ['', '', '', do_lang('ACCESS_DENIED__PRIVILEGE', $GLOBALS['FORUM_DRIVER']->get_username(get_member()), 'view_others_karma')];
        }

        require_code('karma');

        return ['', '', strval(get_karmic_influence($member_id)), ''];
    }
}
