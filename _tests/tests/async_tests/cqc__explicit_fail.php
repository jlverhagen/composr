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
class cqc__explicit_fail_test_set extends cms_test_case
{
    public function testCQCTestsStillWork()
    {
        $url = get_base_url() . '/_tests/codechecker/codechecker.php?test=10&somewhat_pedantic=1';
        $result = http_get_contents($url, ['convert_to_internal_encoding' => true]);
        $this->assertTrue(strpos($result, 'Bad return type') !== false, $result);
    }

    public function testCQCFailuresStillWork()
    {
        $path = get_file_base() . '/temp/temp.php';
        require_code('files');
        cms_file_put_contents_safe($path, "<" . "?= foo() . 1 + ''\n");
        $url = get_base_url() . '/_tests/codechecker/codechecker.php?to_use=temp/temp.php&api=1&somewhat_pedantic=1';
        $result = http_get_contents($url, ['convert_to_internal_encoding' => true, 'timeout' => 10.0]);
        unlink($path);

        $this->assertTrue(strpos($result, 'Could not find function') !== false, 'Should have an error but does not (' . $result . ')');
    }
}
