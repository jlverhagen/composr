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
class awards_test_set extends cms_test_case
{
    protected $award_id;

    public function setUp()
    {
        parent::setUp();

        require_code('awards2');

        $this->award_id = add_award_type('test', 'test', 1, 'download', 0, 250);

        $this->assertTrue('download' == $GLOBALS['SITE_DB']->query_select_value('award_types', 'a_content_type', ['id' => $this->award_id]));
    }

    public function testEditawards()
    {
        edit_award_type($this->award_id, 'test', 'test', 2, 'image', 0, 194);

        $this->assertTrue('image' == $GLOBALS['SITE_DB']->query_select_value('award_types', 'a_content_type', ['id' => $this->award_id]));
    }

    public function tearDown()
    {
        delete_award_type($this->award_id);

        parent::tearDown();
    }
}
