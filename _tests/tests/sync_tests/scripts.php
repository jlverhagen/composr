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
class scripts_test_set extends cms_test_case
{
    public function testReferences()
    {
        disable_php_memory_limit();
        cms_extend_time_limit(TIME_LIMIT_EXTEND__MODEST);

        require_code('files2');
        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING, true, true, ['php', 'tpl', 'js', 'xml', 'txt']);
        foreach ($files as $path) {
            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            $matches = [];

            if (substr($path, -4) == '.php') {
                $num_matches = preg_match_all('#find_script\(\'(\w+)\'#', $c, $matches);
                for ($i = 0; $i < $num_matches; $i++) {
                    $script = $matches[1][$i];
                    $this->assertTrue(file_exists(preg_replace('#^' . preg_quote(get_base_url() . '/') . '#', get_file_base() . '/', find_script($script))), 'Could not find ' . $script);
                }
            }

            if (substr($path, -4) == '.tpl' || substr($path, -3) == '.js' || substr($path, -4) == '.xml' || substr($path, -4) == '.txt') {
                $num_matches = preg_match_all('#\{\$FIND_SCRIPT(_NOHTTP)?[^\s\w,]?,(\w+)#', $c, $matches);
                for ($i = 0; $i < $num_matches; $i++) {
                    $script = $matches[2][$i];
                    $this->assertTrue(file_exists(preg_replace('#^' . preg_quote(get_base_url() . '/') . '#', get_file_base() . '/', find_script($script))), 'Could not find ' . $script);
                }
            }
        }
    }
}
