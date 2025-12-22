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
class tutorials_codebox_test_set extends cms_test_case
{
    public function testTutorialCodeLangSpecified()
    {
        $path = get_file_base() . '/docs/pages/comcode_custom/EN';
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file[0] == '.') {
                continue;
            }

            if (substr($file, -4) == '.txt') {
                $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);

                $this->assertTrue(strpos($c, '[code]') === false, 'Has non-specified [code]-tag language in ' . $file);
                $this->assertTrue(strpos($c, '[codebox]') === false, 'Has non-specified [codebox]-tag language in ' . $file);
            }
        }
        closedir($dh);
    }

    public function testTutorialLangConsistency()
    {
        $allowed_langs = [
            'PHP',
            'HTML',
            'CSS',
            'SQL',
            'MySQL',
            'PostgreSQL',
            'SQLite',
            'tsql',
            'Commandr',
            'Bash',
            'INI',
            'robots',
            'Tempcode',
            'Comcode',
            'JavaScript',
            'XML',
            'BAT',
            'Selectcode',
            'Filtercode',
            'Maths',
            'YAML',
            'htaccess',
            'Page-link',
            'URL',
            'objc',
            'nginx',
            'Diff',

            // Use this if nothing else (or [font="Courier"]...[/font])
            'Text',
        ];

        $path = get_file_base() . '/docs/pages/comcode_custom/EN';
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file[0] == '.') {
                continue;
            }

            if (substr($file, -4) == '.txt') {
                $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);

                $matches = [];
                $num_matches = preg_match_all('#\[(code|codebox)="([^"]*)"\]#', $c, $matches);
                for ($i = 0; $i < $num_matches; $i++) {
                    $lang = $matches[2][$i];

                    $this->assertTrue(in_array($lang, $allowed_langs), 'Non-recognised [code]-tag language (' . $lang . ') in ' . $file);
                }
            }
        }
        closedir($dh);
    }
}
