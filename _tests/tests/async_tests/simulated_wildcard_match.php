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
class simulated_wildcard_match_test_set extends cms_test_case
{
    public function testWildcards()
    {
        // Full cover
        $this->assertTrue(simulated_wildcard_match('Test sentence', 'Test', false));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', 'Test', true));

        // Normal syntax
        $this->assertTrue(simulated_wildcard_match('Test sentence', 'Test*', true));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', 'X', true));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', '*X*', true));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', '?X?', true));
        $this->assertTrue(simulated_wildcard_match('Test sentence', '*sentence', true));
        $this->assertTrue(simulated_wildcard_match('Test sentence', '???? sentence', true));

        // SQL syntax
        $this->assertTrue(simulated_wildcard_match('Test sentence', 'Test%', true));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', 'X', true));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', '%X%', true));
        $this->assertTrue(!simulated_wildcard_match('Test sentence', '_X_', true));
        $this->assertTrue(simulated_wildcard_match('Test sentence', '%sentence', true));
        $this->assertTrue(simulated_wildcard_match('Test sentence', '____ sentence', true));

        // Complexity
        $this->assertTrue(simulated_wildcard_match('Test sentence', 'Test \\sentence', false)); // Because we strip "\" from patterns
        $this->assertTrue(!simulated_wildcard_match('Test sentence', 'Test Xsentence', false));
    }
}
