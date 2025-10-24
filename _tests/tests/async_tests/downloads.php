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
class downloads_test_set extends cms_test_case
{
    protected $dwn_id;

    public function setUp()
    {
        parent::setUp();

        push_query_limiting(false);

        require_code('downloads');
        require_code('downloads2');

        $this->dwn_id = add_download(db_get_first_id(), '111', 'https://duckduckgo.com/', 'Testing download', 'sujith', '', 0, 1, 1, 1, 0, '', 'apple.jpeg', 110, 0, 0, null, null, 0, 0, null, null, null);

        $this->assertTrue('https://duckduckgo.com/' == $GLOBALS['SITE_DB']->query_select_value('download_downloads', 'url', ['id' => $this->dwn_id]));
    }

    public function testEditDownloads()
    {
        edit_download($this->dwn_id, db_get_first_id(), '222', 'http://www.yahoo.com/', 'edited download', 'sujith', '', 0, 0, 1, 1, 1, 0, '', 'fruit.jpeg', 210, 0, 0, null, '', '');

        $this->assertTrue('http://www.yahoo.com/' == $GLOBALS['SITE_DB']->query_select_value('download_downloads', 'url', ['id' => $this->dwn_id]));
    }

    public function tearDown()
    {
        delete_download($this->dwn_id, false);

        parent::tearDown();
    }
}
