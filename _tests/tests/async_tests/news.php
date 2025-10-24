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
class news_test_set extends cms_test_case
{
    protected $news_id;

    public function setUp()
    {
        parent::setUp();

        require_code('news2');

        $this->news_id = add_news('Today', 'hiiiiiiiiiii', 'rolly', 1, 1, 1, 1, '', 'test article', 2, [], 1262671781, null, 0, null, null, '');
        $this->assertTrue('Today' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('news', 'title', ['id' => $this->news_id])));
    }

    public function testEditNews()
    {
        edit_news($this->news_id, 'Politics', 'teheyehehj ', 'rolly', 1, 1, 1, 1, 'yedd', 'test article 22222222', 2, null, '', '', '');

        $translated = get_translated_text($GLOBALS['SITE_DB']->query_select_value('news', 'title', ['id' => $this->news_id]));
        $this->assertTrue('Politics' == $translated, 'Translated title did not come out Politics, instead was ' . $translated);
    }

    public function tearDown()
    {
        delete_news($this->news_id);

        parent::tearDown();
    }
}
