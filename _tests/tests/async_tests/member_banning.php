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
class member_banning_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }

        require_code('cns_members_action');
        require_code('cns_members_action2');
        require_lang('cns');
    }

    public function testBanUnban()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_ban_member(3);
        $this->assertTrue('1' == $GLOBALS['FORUM_DB']->query_select_value('f_members', 'm_is_perm_banned', ['id' => 3]));

        cns_unban_member(3);
        $this->assertTrue('0' == $GLOBALS['FORUM_DB']->query_select_value('f_members', 'm_is_perm_banned', ['id' => 3]));
    }
}
