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
class js_lang_references_test_set extends cms_test_case
{
    public function testLangReferences()
    {
        $core_ini_files_contents = '';
        foreach ([
            'lang/EN/global.ini',
            'lang_custom/EN/global.ini',
            'lang/EN/critical_error.ini',
            'lang_custom/EN/critical_error.ini',
        ] as $path) {
            if (is_file(get_file_base() . '/' . $path)) {
                $core_ini_files_contents .= cms_file_get_contents_safe(get_file_base() . '/' . $path, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);
            }
        }

        foreach (['javascript', 'javascript_custom'] as $subdir) {
            $path = get_file_base() . '/themes/default/' . $subdir;
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if (cms_strtolower_ascii(substr($file, -3)) == '.js') {
                    $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT);

                    $matches = [];
                    $num_matches = preg_match_all('#\{\!(\w+)[\},;^\*]#', $c, $matches);
                    for ($i = 0; $i < $num_matches; $i++) {
                        $str = $matches[1][$i];
                        $this->assertTrue(strpos($core_ini_files_contents, "\n" . $str . '=') !== false, $file . '/' . $str . ' needs to have explicit file referencing');
                    }
                }
            }
            closedir($dh);
        }
    }
}
