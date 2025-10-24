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
class unpack_test_set extends cms_test_case
{
    public function testBitwise()
    {
        $test_data = [
            // Lower 1-byte
            [chr(0x0A), 0x0A, 0x0A],
            [chr(0x0B), 0x0B, 0x0B],

            // Upper 1-byte
            [chr(0xFE), 0xFE, 0xFE],
            [chr(0xFF), 0xFF, 0xFF],

            // Lower 2-bytes
            [chr(0x0A) . chr(0x0B), 0x0A0B, 0x0A0B],
            [chr(0x0B) . chr(0x0A), 0x0B0A, 0x0B0A],

            // Upper 2-bytes
            [chr(0xFE) . chr(0xFF), 0xFEFF, 0xFEFF],
            [chr(0xFF) . chr(0xFE), 0xFFFE, 0xFFFE],
        ];

        foreach ($test_data as $_parts) {
            list($str, $hex, $expected) = $_parts;

            $output = (cms_unpack_to_uinteger($str) & $hex);
            $this->assertTrue($output == $expected);
        }
    }
}
