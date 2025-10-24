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
class php_versioning_test_set extends cms_test_case
{
    public function testPhpVersionChecking()
    {
        require_code('version2');

        // NOTE: These tests may fail if PHP changes the format of their supported version data
        //  Or just naturally as PHP supported versions change

        $v = strval(PHP_MAJOR_VERSION) . '.' . strval(PHP_MINOR_VERSION);
        $this->assertTrue((is_php_version_supported_by_phpdevs($v) !== null), 'Could not check if your PHP version is supported. Other PHP version checks may fail.');

        // This needs updating occasionally. The key thing is Composr itself will update itself, and this just checks that automatic updating works as we'd expect.
        $this->assertTrue((is_php_version_supported_by_phpdevs(float_to_raw_string(8.1, 1)) === true), 'Expected PHP version 8.1 to be supported, but it appears it is not.'); // Normally supported
        $this->assertTrue((is_php_version_supported_by_phpdevs(float_to_raw_string(9.0, 1)) === true), 'Expected PHP version 9.0 to be supported, but it appears it is not.'); // Future, assume supported
        $this->assertTrue((is_php_version_supported_by_phpdevs(float_to_raw_string(7.4, 1)) === false), 'Expected PHP version 7.4 to NOT be supported, but it appears it is.'); // Known unsupported
        $this->assertTrue((is_php_version_supported_by_phpdevs(float_to_raw_string(5.5, 1)) === false), 'Expected PHP version 5.5 to NOT be supported, but it appears it is.'); // Known unsupported and too old to be tracked

        $message = 'This test will need manually updating with PHP\'s current supported and unsupported versions.';
        $this->dump($message, 'INFO:');
    }
}
