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
class menus_test_set extends cms_test_case
{
    protected $menu_id;

    public function setUp()
    {
        parent::setUp();

        require_code('menus');
        require_code('menus2');

        $this->menu_id = add_menu_item('Test', 1, null, 'testing menu', 'https://duckduckgo.com/', 1, 'downloads', 0, 1, 'testing');

        $this->assertTrue('Test' == $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_menu', ['id' => $this->menu_id]));
    }

    public function testEditMenu()
    {
        edit_menu_item($this->menu_id, 'Service', 2, null, 'Serv', 'https://duckduckgo.com/', 0, 'catalogues', 1, 0, 'tested', '', 0);

        $this->assertTrue('Service' == $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_menu', ['id' => $this->menu_id]));
    }

    public function testURLRendering()
    {
        $urls = [];

        $branch = [
            'title' => 'test',
            'content_type' => 'stored_branch',
            'content_id' => null,
            'modifiers' => [],
            'only_on_page' => '',
            'extra_meta' => [
            ],
            'has_possible_children' => false,
            'children' => [],
        ];

        $tests = [];
        $tests[] = [
            'url' => static_evaluate_tempcode(build_url(['page' => 'rules'], '')),
            'page_link' => '',
        ];
        $tests[] = [
            'url' => '',
            'page_link' => ':rules',
        ];

        foreach ($tests as $test) {
            $result = _render_menu_branch($branch + $test, 'test', db_get_first_id(), 0, 'tree', false, [$branch], false);

            $url = $result[0]['URL']->evaluate();
            $urls[] = $url;
            $this->assertTrue($url == $urls[0], $url . ' vs ' . $urls[0]);
        }
    }

    public function tearDown()
    {
        delete_menu_item($this->menu_id);

        parent::tearDown();
    }
}
