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
class eslint_test_set extends cms_test_case
{
    public function testESLint()
    {
        cms_set_time_limit(120);

        $result = shell_exec('npx eslint -v');
        if ($result === null) {
            $result = '';
        }

        if (strpos($result, 'v') === false) {
            $this->assertTrue(false, 'eslint not available');
            return;
        }

        $current_path = '?';
        $path = get_file_base() . '/themes/default/';
        $result = shell_exec('eslint ' . escapeshellarg($path) . ' 2>&1');
        if (!empty($result)) {
            foreach (explode("\n", $result) as $line) {
                if (substr($line, 0, strlen($path)) == $path) {
                    $current_path = substr($line, strlen(get_file_base()) + 1);
                }

                $matches = [];
                if (preg_match('#^\s*(\d+):\d+\s+\w+\s+(.*)$#', $line, $matches) != 0) {
                    $this->assertTrue(false, $current_path . ':' . $matches[1] . ' -- ' . $matches[2]);
                }
            }
        }
    }
}
