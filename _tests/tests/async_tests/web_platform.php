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
class web_platform_test_set extends cms_test_case
{
    public function testNoBadRegexp()
    {
        $c = cms_file_get_contents_safe(get_file_base() . '/web.config', FILE_READ_LOCK | FILE_READ_BOM);
        $this->assertTrue(strpos($c, '\\_') === false, 'Apache allows any character to be escaped, IIS only allows ones that must be');
    }

    public function testNoBadComments()
    {
        $c = cms_file_get_contents_safe(get_file_base() . '/web.config', FILE_READ_LOCK);
        $this->assertTrue(strpos($c, '<--') === false, 'Comments must be <!-- in web.config');
    }

    public function testNoDuplicateNames()
    {
        $c = cms_file_get_contents_safe(get_file_base() . '/web.config', FILE_READ_LOCK);
        $matches = [];
        $names = [];
        $num_matches = preg_match_all('#name="([^"]*)"#', $c, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $names[] = $matches[1][$i];
        }
        $this->assertTrue($names == array_unique($names), 'Names in web.config must be unique');
    }
}
