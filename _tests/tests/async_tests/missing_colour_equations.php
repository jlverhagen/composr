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
class missing_colour_equations_test_set extends cms_test_case
{
    public function testMissingColourEquations()
    {
        require_code('files2');

        $dont_check = [
            'commandr.css',
            'install.css',
            'widget_plupload.css',
            'widget_color.css',
            'widget_select2.css',
            'phpinfo.css',
            'mediaelementplayer.css',
            'skitter.css',
        ];

        $files = get_directory_contents(get_file_base() . '/themes/default/css', get_file_base() . '/themes/default/css', null, false, true, ['css']);
        foreach ($files as $path) {
            if (in_array(basename($path), $dont_check)) {
                continue;
            }

            $c = cms_file_get_contents_safe($path, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);
            $matches = [];
            $count = preg_match_all('/^.+(\#[0-9A-Fa-f]{3,6})(.*)$/m', $c, $matches);
            for ($i = 0; $i < $count; $i++) {
                if (strpos($matches[0][$i], '{$') === false) { // If /*{$,hardcoded_ok}*/ is not on the line
                    $line = substr_count(substr($c, 0, strpos($c, $matches[0][$i])), "\n") + 1;
                    $this->assertTrue(false, 'Missing colour equation in ' . $path . ':' . strval($line) . ' for ' . $matches[1][$i]);
                }
            }
        }
    }
}
