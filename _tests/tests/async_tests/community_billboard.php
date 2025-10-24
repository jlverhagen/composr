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
class community_billboard_test_set extends cms_test_case
{
    protected $flag_id;

    public function setUp()
    {
        parent::setUp();

        if (!addon_installed('community_billboard')) {
            return;
        }

        if (in_safe_mode()) {
            $this->assertTrue(false, 'Cannot work in safe mode');
            return;
        }

        require_code('community_billboard');

        $this->flag_id = add_community_billboard_message('test', 3, 'Welcome to Composr', 1);

        $this->assertTrue('Welcome to Composr' == $GLOBALS['SITE_DB']->query_select_value('community_billboard', 'notes', ['id' => $this->flag_id]));
    }

    public function testEditCommunityBillboard()
    {
        if (!addon_installed('community_billboard')) {
            return;
        }

        if (in_safe_mode()) {
            $this->assertTrue(false, 'Cannot work in safe mode');
            return;
        }

        edit_community_billboard_message($this->flag_id, 'Tested', 'Thank you', 0);

        $this->assertTrue('Thank you' == $GLOBALS['SITE_DB']->query_select_value('community_billboard', 'notes', ['id' => $this->flag_id]));
    }

    public function tearDown()
    {
        if (!addon_installed('community_billboard')) {
            return;
        }

        if (in_safe_mode()) {
            $this->assertTrue(false, 'Cannot work in safe mode');
            return;
        }

        delete_community_billboard_message($this->flag_id);

        parent::tearDown();
    }
}
