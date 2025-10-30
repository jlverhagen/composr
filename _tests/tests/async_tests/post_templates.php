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
class post_templates_test_set extends cms_test_case
{
    protected $post_id;

    public function setUp()
    {
        parent::setUp();

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }

        require_code('cns_general_action');
        require_code('cns_general_action2');

        $this->post_id = cns_make_post_template('Test Post', 'Testing', 'Code', 0);

        $this->assertTrue('Test Post' == $GLOBALS['FORUM_DB']->query_select_value('f_post_templates', 't_title', ['id' => $this->post_id]));
    }

    public function testEditpost_template()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_edit_post_template($this->post_id, 'Tested Post', 'Hello', 'Nothing', 1);

        $this->assertTrue('Tested Post' == $GLOBALS['FORUM_DB']->query_select_value('f_post_templates', 't_title', ['id' => $this->post_id]));
    }

    public function tearDown()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        cns_delete_post_template($this->post_id);

        parent::tearDown();
    }
}
