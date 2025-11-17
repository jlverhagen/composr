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
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class commandr_command_lang_strings_test_set extends cms_test_case
{
    public function testStringsAllDefined()
    {
        require_code('commandr');
        require_code('commandr_fs');
        $fs = object_factory('Source_commandr_fs');
        $hooks = find_all_hook_obs('systems', 'commandr_commands', 'Hook_commandr_command_');
        foreach ($hooks as $hook => $ob) {
            if ($hook == 'help') {
                continue;
            }

            $ret = $ob->run(['h' => 1], [], $fs);
            $this->assertTrue(strlen($ret[1]->evaluate()) > 0, 'Missing Commandr help for ' . $hook);
            $this->assertTrue(count($ret) == 4, 'Unexpected returned values for ' . $hook);
        }
    }
}
