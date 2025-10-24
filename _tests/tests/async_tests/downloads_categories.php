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
class downloads_categories_test_set extends cms_test_case
{
    protected $dwn_cat_id;

    public function setUp()
    {
        parent::setUp();

        require_code('downloads');
        require_code('downloads2');

        $this->dwn_cat_id = add_download_category('test', 1, 'test', 'test', '', null);

        $this->assertTrue('test' == $GLOBALS['SITE_DB']->query_select_value('download_categories', 'notes', ['id' => $this->dwn_cat_id]));
    }

    public function testEditDownloadsCategory()
    {
        edit_download_category($this->dwn_cat_id, 'test', 1, 'test', 'edit_test', '', '', '');

        $this->assertTrue('edit_test' == $GLOBALS['SITE_DB']->query_select_value('download_categories', 'notes', ['id' => $this->dwn_cat_id]));
    }

    public function tearDown()
    {
        delete_download_category($this->dwn_cat_id);

        parent::tearDown();
    }
}
