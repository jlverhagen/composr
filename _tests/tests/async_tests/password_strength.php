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
class password_strength_test_set extends cms_test_case
{
    public function testPasswordStrength()
    {
        require_code('password_rules');

        $username = 'theU$ERname';
        $email_address = 'bob@example.com';

        $expects = [
            // Test tainted passwords
            'theU$ERname' => 1,
            'bob@example.com' => 1,

            // Special tests
            '' => 1, // Empty
            'abCIYZ' => 2, // Test that abc subtracts 1
            'sjh876BR' => 2, // Test that 876 subtracts 2

            // Special tests: repeat character deductions
            'CCCCC^jk9I6c&h8cE' => 7,
            'CCCCCcCc9I6c&h8cE' => 4,
            'CCCCCCCCCCCcCcC1#' => 1,

            // Random passwords test
            'T' => 1,
            'oWbE' => 2,
            '0LR1wp' => 3,
            'R9=.K~C' => 4,
            '3n21Q;Xi' => 5,
            'Aw7HUQ%;6~' => 6,
            '_GWfe^t2E;-' => 7,
            'qDLp02xKy8@a7' => 8,
            'ypEWDE8s0G8082e5' => 9,
            'fxg%xo~?9J`8Y3|E' => 10,
        ];

        require_code('spelling');
        $spell_checker = _find_spell_checker();
        if ($spell_checker !== null) {
            $expects['hippopotamus'] = 1; // Dictionary word
        }

        foreach ($expects as $password => $score_expected) {
            $score_got = test_password(strval($password), $username, $email_address);
            $this->assertTrue($score_got == $score_expected, 'For ' . $password . ', got ' . integer_format($score_got) . ' expected ' . integer_format($score_expected));
        }
    }
}
