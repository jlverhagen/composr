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
class cpfs_test_set extends cms_test_case
{
    public function testCPFFullCycle()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        require_code('cpf_install');

        $member_id = $this->get_canonical_member_id('admin');

        install_name_fields();

        $GLOBALS['FORUM_DRIVER']->set_custom_field($member_id, 'firstname', 'Foobar');

        $fields = cns_get_custom_field_mappings($member_id);
        $this->assertTrue(strpos(serialize($fields), 'Foobar') !== false);

        $looked_up = get_cms_cpf('firstname', $member_id);
        $this->assertTrue($looked_up == 'Foobar', 'Got ' . $looked_up . ', expected Foobar');
    }
}
