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
class tempcode_errors_test_set extends cms_test_case
{
    public function testNoBadPassed()
    {
        require_code('files');

        disable_php_memory_limit();

        $paths = [
            get_file_base() . '/themes/default/templates',
            get_file_base() . '/themes/default/templates_custom',
        ];
        foreach ($paths as $path) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if (cms_strtolower_ascii(substr($file, -4)) == '.tpl') {
                    $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT);

                    $this->assertTrue(strpos($c, '{+START,IF_PASSED,{') === false, 'Bad IF_PASSED parameter in ' . $file . ' template');
                    $this->assertTrue(strpos($c, '{+START,IF_NON_PASSED,{') === false, 'Bad IF_NON_PASSED parameter in ' . $file . ' template');
                    $this->assertTrue(preg_match('#\{\+START,IF,[A-Z]#', $c) == 0, 'Bad IF parameter in ' . $file . ' template');
                }
            }
            closedir($dh);
        }
    }
}
