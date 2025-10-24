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
class diff_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('diff');
    }

    public function testSimpleDiff()
    {
        // Unified
        $result = diff_simple_text("a\nb\nc", "a\nb\nd", true);
        $this->assertTrue($result == '@@ -1,3 +1,3 @@<br /> a<br /> b<br /><del>-c</del><br /><ins>+d</ins><br /><br />');

        // Not unified
        $result = diff_simple_text("a\nb\nc", "a\nb\nd", false);
        $this->assertTrue($result == 'a<br />b<br /><del>c</del><ins>d</ins><br />');
    }

    public function test3WayDiff()
    {
        $result = diff_3way_text("a\nb\nc", "a\nb\nd", "b\nb\nc");
        $this->assertTrue($result == "b\nb\nd", 'Got ' . $result . '!');
    }
}
