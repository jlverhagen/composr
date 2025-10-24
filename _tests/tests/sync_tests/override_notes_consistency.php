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
class override_notes_consistency_test_set extends cms_test_case
{
    public function testOverrideNotesConsistency()
    {
        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        require_code('files2');
        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_FLOATING, true, true, ['php']);
        $files[] = 'install.php';
        foreach ($files as $path) {
            if (file_exists(dirname($path) . '/index.php')) {
                continue; // Zone directory, no override support
            }

            if (preg_match('#(^sources|/modules|^data)(_custom)?/#', $path) == 0) {
                continue;
            }

            // Exceptions
            $exceptions = array_merge(list_untouchable_third_party_directories(), [
                '_tests',
            ]);
            if (preg_match('#^(' . implode('|', $exceptions) . ')/#', $path) != 0) {
                continue;
            }
            $exceptions = array_merge(list_untouchable_third_party_files(), [
            ]);
            if (in_array($path, $exceptions)) {
                continue;
            }

            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            if (strpos($c, 'CQC: No check') !== false) {
                continue;
            }
            if (strpos($c, 'CQC: No API check') !== false) {
                continue;
            }

            $pos = strpos($c, 'NOTE TO PROGRAMMERS:');
            if (strpos($path, '_custom/') === false) {
                $ok = ($pos !== false) && ($pos < 200);
                $this->assertTrue($ok, 'Bundled code must have a "NOTE TO PROGRAMMERS:" in ' . $path);
            } else {
                $ok = ($pos === false) || ($pos > 200);
                $this->assertTrue($ok, 'Non-bundled code must not have a "NOTE TO PROGRAMMERS:" in ' . $path);
            }
        }
    }
}
