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
class wiki_test_set extends cms_test_case
{
    protected $id;

    public function setUp()
    {
        parent::setUp();

        require_code('wiki');
    }

    public function testAddWikipage()
    {
        require_code('permissions2');
        $this->id = wiki_add_page('test page', 'test description', 'test notes', 0);
        set_category_permissions_from_environment('wiki_page', strval($this->id), 'cms_wiki');

        // Check the page was actually created
        $this->assertTrue('test notes' == $GLOBALS['SITE_DB']->query_select_value('wiki_pages', 'notes', ['id' => $this->id]));
    }

    public function testEditWikiPage()
    {
        require_code('permissions2');
        set_category_permissions_from_environment('wiki_page', strval($this->id), 'cms_wiki');
        wiki_edit_page($this->id, 'title-edited', 'test description', 'notes_edited', 0, '', '');

        // Check the page was edited
        $this->assertTrue('notes_edited' == $GLOBALS['SITE_DB']->query_select_value('wiki_pages', 'notes', ['id' => $this->id]));
    }

    public function testDeleteWikipage()
    {
        // Delete Wiki+ page
        wiki_delete_page($this->id);
    }
}
