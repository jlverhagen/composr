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
class authors_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('authors');

        add_author('author1', 'https://duckduckgo.com/', 3, 'Happy', 'Play', '', '');

        add_author('author2', 'http://www.yahoo.com/', 3, 'Welcome', 'Drama', '', '');

        $this->assertTrue('author1' == $GLOBALS['SITE_DB']->query_select_value('authors', 'author', ['author' => 'author1']));
    }

    public function testMergeAuthors()
    {
        merge_authors('author1', 'author2');
    }

    public function tearDown()
    {
        delete_author('author2');

        parent::tearDown();
    }
}
