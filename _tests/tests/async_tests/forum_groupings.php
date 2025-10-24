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
class forum_groupings_test_set extends cms_test_case
{
    protected $forum_cat_id;
    protected $access_mapping;

    public function setUp()
    {
        parent::setUp();

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }

        require_code('cns_forums_action');
        require_code('cns_forums_action2');
        require_lang('cns');

        $this->forum_cat_id = cns_make_forum_grouping('Test_cat', 'nothing', 1);

        $this->assertTrue('Test_cat' == $GLOBALS['FORUM_DB']->query_select_value('f_forum_groupings', 'c_title', ['id' => $this->forum_cat_id]));
    }

    public function testEditForumGrouping()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_edit_forum_grouping($this->forum_cat_id, 'New_title', 'somthing', 1);

        $this->assertTrue('New_title' == $GLOBALS['FORUM_DB']->query_select_value('f_forum_groupings', 'c_title', ['id' => $this->forum_cat_id]));
    }

    public function tearDown()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_delete_forum_grouping($this->forum_cat_id, 0);

        parent::tearDown();
    }
}
