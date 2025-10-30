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
class news_categories_test_set extends cms_test_case
{
    protected $news_id;

    public function setUp()
    {
        parent::setUp();

        require_code('news2');

        $this->news_id = add_news_category('Today', 'news.gif', 'Headlines', null, null);

        $this->assertTrue('Today' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('news_categories', 'nc_title', ['id' => $this->news_id])));
    }

    public function testEditNewscategory()
    {
        edit_news_category($this->news_id, 'Politics', 'world.jpg', 'Around the world', null);

        $this->assertTrue('Politics' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('news_categories', 'nc_title', ['id' => $this->news_id])));
    }

    public function tearDown()
    {
        delete_news_category($this->news_id);

        parent::tearDown();
    }
}
