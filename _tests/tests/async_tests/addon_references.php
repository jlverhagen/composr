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
class addon_references_test_set extends cms_test_case
{
    protected $files;

    public function setUp()
    {
        parent::setUp();

        require_code('files2');

        $this->files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING | IGNORE_CUSTOM_THEMES, true, true, ['php', 'tpl']);
        $this->files[] = 'install.php';

        cms_extend_time_limit(TIME_LIMIT_EXTEND__MODEST);
    }

    public function testPHP()
    {
        foreach ($this->files as $path) {
            if (substr($path, -4) != '.php') {
                continue;
            }

            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path, FILE_READ_LOCK);
            $matches = [];
            $num_matches = preg_match_all('#addon_installed\(\'([^\']*)\'\)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $addon_name = $matches[1][$i];
                $this->assertTrue(addon_installed($addon_name), 'Could not find PHP-referenced addon, ' . $addon_name . ', in file ' . $path);
            }

            unset($c);
        }
    }

    public function testTemplates()
    {
        foreach ($this->files as $path) {
            if (substr($path, -4) != '.tpl') {
                continue;
            }

            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path, FILE_READ_LOCK);
            $matches = [];
            $num_matches = preg_match_all('#\{\$ADDON_INSTALLED,(\w+)\}#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $addon_name = $matches[1][$i];
                $this->assertTrue(addon_installed($addon_name), 'Could not find template-referenced addon, ' . $addon_name);
            }

            unset($c);
        }
    }
}
