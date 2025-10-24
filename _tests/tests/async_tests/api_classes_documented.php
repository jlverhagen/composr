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
class api_classes_documented_test_set extends cms_test_case
{
    public function testAPIClassesDocumented()
    {
        /*
        NB: This only bothers with stuff we are going to include in the API compile scan. Otherwise we don't care as Composr doesn't (packages work on a file level, this isn't Java).
        */

        foreach (['sources', 'sources/database', 'sources/database/shared', 'sources/forum'] as $d) {
            $path = get_file_base() . '/' . $d;
            $dh = @opendir($path);
            if ($dh !== false) {
                while (($file = readdir($dh)) !== false) {
                    if (substr($file, -4) != '.php') {
                        continue;
                    }

                    $c = cms_file_get_contents_safe($path . '/' . $file);

                    if (strpos($c, 'CQC: No check') !== false) {
                        continue;
                    }
                    if (strpos($c, 'CQC: No API check') !== false) {
                        continue;
                    }

                    $matches = [];
                    $num_matches = preg_match_all('#\n\t*(abstract\s+)?class (\w+)#', $c, $matches);
                    for ($i = 0; $i < $num_matches; $i++) {
                        $this->assertTrue(
                            preg_match('# +\* @package\s+\w+\n\t* +\*/\n\t*(abstract\s+)?class ' . preg_quote($matches[2][$i], '#') . '#', $c) != 0,
                            'Undefined package for class: ' . $d . '/' . $file . ' (' . $matches[2][$i] . ')'
                        );
                    }
                }

                closedir($dh);
            }
        }
    }
}
