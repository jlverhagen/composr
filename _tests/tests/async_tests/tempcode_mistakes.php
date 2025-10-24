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
class Tempcode_mistakes_test_set extends cms_test_case
{
    public function testIfPassedGuards()
    {
        require_code('files');
        require_code('files2');
        $files = get_directory_contents(get_file_base() . '/themes');

        $regexp = '#\{\+START,IF_PASSED,(\w+)\}[^\{\}]*\{(?:(?!\1)\w)*\*?\}[^\{\}]*\{\+END\}#';

        $exceptions = [
            'default/templates/ATTACHMENT.tpl',
            'default/templates/FORM_SCREEN_INPUT_UPLOAD.tpl',
            'default/templates/FORM_SCREEN_INPUT_UPLOAD_MULTI.tpl',
        ];

        foreach ($files as $file) {
            if (in_array($file, $exceptions)) {
                continue;
            }

            if (substr($file, -4) == '.tpl') {
                $c = str_replace('{}', '', cms_file_get_contents_safe(get_file_base() . '/themes/' . $file, FILE_READ_LOCK | FILE_READ_BOM));
                $this->assertTrue(preg_match($regexp, $c) == 0, 'Found dodgy looking IF_PASSED situation in ' . $file);

                // By convention we have HTML coming out nicely formatted EXCEPT where there is Tempcode guarding an attribute at the start of the tag where we want the raw .tpl to work well in a code editor
                $matches = [];
                $this->assertTrue(preg_match('#<\w+\{\+#', $c, $matches) == 0, 'Code editors would find it hard to detect a tag start in ' . $file . ' (' . implode(' | ', $matches) . ')');
            }
        }
    }
}
