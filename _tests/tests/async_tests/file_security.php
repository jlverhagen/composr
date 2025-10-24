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
class file_security_test_set extends cms_test_case
{
    public function setUp()
    {
        require_code('files2');

        parent::setUp();
    }

    public function testFilenameFixup()
    {
        $tests = [
            // Not safelisted
            'foo.example' => ['foo.example', false],

            // Files should be altered to remove double-file-extension
            'foo.php.php.gif' => ['foo-php-php.gif', true],
            'foo.php.bar.gif' => ['foo-php.bar.gif', true],
            'foo.bar.php.gif' => ['foo.bar-php.gif', true],
            'foo.php.gif' => ['foo-php.gif', true],
            'foo.php' => ['foo.php', false], // Blacklisted

            // Files inside directories should be altered to remove double-file-extension
            'x/foo.php.php.gif' => ['x/foo-php-php.gif', true],
            'x/foo.php.bar.gif' => ['x/foo-php.bar.gif', true],
            'x/foo.bar.php.gif' => ['x/foo.bar-php.gif', true],
            'x/foo.php.gif' => ['x/foo-php.gif', true],
            'x/foo.php' => ['x/foo.php', false], // Blacklisted

            // Directories should not be altered to remove double-file-extension
            'foo.php.bar/foo.php.php.gif' => ['foo.php.bar/foo-php-php.gif', true],
            'foo.php.bar/foo.php.bar.gif' => ['foo.php.bar/foo-php.bar.gif', true],
            'foo.php.bar/foo.bar.php.gif' => ['foo.php.bar/foo.bar-php.gif', true],
            'foo.php.bar/foo.php.gif' => ['foo.php.bar/foo-php.gif', true],
            'foo.php.bar/foo.php' => ['foo.php.bar/foo.php', false], // Blacklisted
        ];

        foreach ($tests as $from => $_) {
            list($to, $result) = $_;
            $name = $from;
            $this->assertTrue(check_extension($name/*changed by reference*/, false, null, true) == $result, 'Unexpected return result for ' . $from);
            $this->assertTrue($name == $to, 'Failed $to result for ' . $from . ', got ' . $name . ' but expected ' . $to);
        }
    }
}
