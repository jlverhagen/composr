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
class url_monikers_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('urls2');
    }

    public function testMonikerGeneration()
    {
        $cases = [
            // Stop-word removal
            'This is a test for the feature' => 'feature',
            'This is a better test for the feature' => 'better-feature',

            // Edge cases
            'This is' => 'this-is', // All stop-words, so leave them
            '*' => 'untitled', // Gets fully stripped
            'x*(y)' => 'x-y', // Double symbols
            'I went to the woods today and found a surprise' => 'went-woods-today-found', // Long, shortened
        ];

        foreach ($cases as $title => $expected_moniker) {
            $result_moniker = _generate_moniker($title);
            $this->assertTrue($result_moniker == $expected_moniker, 'Failed on case: ' . $title . ' (got ' . $result_moniker . ', expected ' . $expected_moniker . ')');
        }
    }
}
