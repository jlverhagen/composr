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
class js_icon_use_test_set extends cms_test_case
{
    public function testLangReferences()
    {
        foreach (['javascript', 'javascript_custom'] as $subdir) {
            $path = get_file_base() . '/themes/default/' . $subdir;
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if (cms_strtolower_ascii(substr($file, -3)) == '.js') {
                    $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT);

                    $matches = [];
                    $num_matches = preg_match_all('#\$cms\.ui\.setIcon\(\w+, \'([^\']+)\'(, \'\{\$IMG;,\{\$\?,\{\$THEME_OPTION,use_monochrome_icons\},icons_monochrome,icons\}/([^\']+)\}\'\))?#', $c, $matches);
                    for ($i = 0; $i < $num_matches; $i++) {
                        $icon = $matches[1][$i];
                        $url_part = $matches[2][$i];
                        $icon_in_url = $matches[3][$i];

                        $this->assertTrue($url_part != '', 'Incorrect or missing URL component to setIcon call in ' . $file . ' for ' . $icon);

                        if ($url_part != '') {
                            $this->assertTrue($icon == $icon_in_url, 'Mismatched URL component to setIcon call in ' . $file . ' for ' . $icon);
                        }
                    }
                }
            }
            closedir($dh);
        }
    }
}
