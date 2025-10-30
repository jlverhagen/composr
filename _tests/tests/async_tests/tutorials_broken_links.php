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
class tutorials_broken_links_test_set extends cms_test_case
{
    protected $path;
    protected $pages;

    public function setUp()
    {
        parent::setUp();

        $this->path = get_file_base() . '/docs/pages/comcode_custom/EN';
        $dh = opendir($this->path);
        $this->pages = [];
        while (($file = readdir($dh)) !== false) {
            if ($file[0] == '.') {
                continue;
            }

            if (substr($file, -4) == '.txt') {
                $this->pages[basename($file, '.txt')] = true;
            }
        }
        closedir($dh);
    }

    public function testSelfLinks()
    {
        foreach (array_keys($this->pages) as $f) {
            $c = cms_file_get_contents_safe($this->path . '/' . $f . '.txt', FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);

            $this->assertTrue(strpos($c, ':' . $f . '"]') === false, 'Seems to have a self-linking situation in ' . $f);
        }
    }

    public function testLinksFromCode()
    {
        require_code('files2');
        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING, true, true, ['php']);
        $files[] = 'install.php';
        foreach ($files as $path) {
            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            $matches = [];
            $num_matches = preg_match_all('#(get_tutorial_url|set_helper_panel_tutorial)\(\'([^\']+)\'\)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $tutorial = $matches[2][$i];

                if ($tutorial == 'tutorials') {
                    continue;
                }

                $this->assertTrue(isset($this->pages[$tutorial]), 'Code link to a missing tutorial: ' . $tutorial . ' in ' . $path);
            }
        }
    }

    public function testTutorialBrokenLinks()
    {
        foreach (array_keys($this->pages) as $f) {
            $c = cms_file_get_contents_safe($this->path . '/' . $f . '.txt', FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT);

            $matches = [];
            $num_matches = preg_match_all('#\[page="(_SEARCH|_SELF|docs):([^"]+)"\]#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $page = preg_replace('/#.*$/', '', $matches[2][$i]);

                if ((substr($page, 0, 4) != 'sup_') && (substr($page, 0, 4) != 'tut_') && (!isset($this->pages['sup_' . $page])) && (!isset($this->pages['tut_' . $page]))) {
                    // We don't concern ourselves with non-tutorial links, but we do first check it wasn't a tutorial link with a missing prefix
                    continue;
                }

                $this->assertTrue(isset($this->pages[$page]), 'Bad tutorial link to ' . $page . ' from ' . $f);
            }
        }
    }
}
