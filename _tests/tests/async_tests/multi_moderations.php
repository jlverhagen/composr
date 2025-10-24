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
class multi_moderations_test_set extends cms_test_case
{
    protected $mod_id;

    public function setUp()
    {
        parent::setUp();

        if (!addon_installed('cns_multi_moderations')) {
            $this->assertTrue(false, 'Test only works when the cns_multi_moderations addon is installed');
            return;
        }

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }

        require_code('cns_multi_moderations');
        require_code('cns_multi_moderations2');

        $this->mod_id = cns_make_multi_moderation('Test Moderation', 'Test', null, 0, 0, '*', 'Nothing');

        $this->assertTrue('Test Moderation' == get_translated_text($GLOBALS['FORUM_DB']->query_select_value('f_multi_moderations', 'mm_name', ['id' => $this->mod_id]), $GLOBALS['FORUM_DB']));
    }

    public function testEditModeration()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_edit_multi_moderation($this->mod_id, 'Tested', 'Something', null, 0, 0, '*', 'Hello');

        $this->assertTrue('Tested' == get_translated_text($GLOBALS['FORUM_DB']->query_select_value('f_multi_moderations', 'mm_name', ['id' => $this->mod_id]), $GLOBALS['FORUM_DB']));
    }

    public function tearDown()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_delete_multi_moderation($this->mod_id);

        parent::tearDown();
    }
}
