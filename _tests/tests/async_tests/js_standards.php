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
class js_standards_test_set extends cms_test_case
{
    public function testSSLIssues()
    {
        foreach (['javascript', 'javascript_custom', 'templates', 'templates_custom'] as $dir) {
            $path = get_file_base() . '/themes/default/' . $dir;
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if (cms_strtolower_ascii(substr($file, -3)) == '.js') {
                    $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);

                    $matches = [];
                    $num_matches = preg_match_all('#(?<!\$util\.srl\([\'"])\{\$IMG[;*]+,(\w+)\}(.*)$#m', $c, $matches);
                    for ($i = 0; $i < $num_matches; $i++) {
                        $this->assertTrue(false, $file . '/' . $matches[1][$i] . ' not prepared for SSL');
                    }

                    $c2 = preg_replace('#\{\$PAGE_LINK[;*]?,[^,]*,[01],1#', '', $c); // This case is without keep_* params, so is okay. Strip out from data
                    $this->assertTrue(strpos($c2, '{$PAGE_LINK') === false, 'Should not encode page-links directly in JavaScript on ' . $file);

                    $this->check_for_script_override_issue($file, $c);
                }

                if (cms_strtolower_ascii(substr($file, -4)) == '.tpl') {
                    $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);

                    $this->check_for_script_override_issue($file, $c);
                }
            }
            closedir($dh);
        }
    }

    protected function check_for_script_override_issue($file, $c)
    {
        $c2 = str_replace('/index.php', '', $c);
        $c2 = str_replace('/empty.php', '', $c2);
        $this->assertTrue(preg_match('#/(data|adminzone|cms|site|forum)/\w+\.php#', $c2) == 0, $file . ' is directly referencing a script, bypassing override system');
    }
}
