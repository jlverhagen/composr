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
class cqc_forum_test_set extends cms_test_case
{
    public function testForum()
    {
        $message = 'You may need to re-run _cqc_function_sigs after making changes to function signatures or PHPDocs.';
        $this->dump($message, 'INFO:');

        cms_set_time_limit(120);
        $url = get_base_url() . '/_tests/codechecker/codechecker.php?subdir=forum';
        $url = $this->extend_cqc_call($url);
        $result = http_get_contents($url, ['convert_to_internal_encoding' => true, 'timeout' => 10000.0]);
        foreach (explode('<br />', $result) as $line) {
            $this->assertTrue($this->should_filter_cqc_line($line), $line);
        }
    }
}
