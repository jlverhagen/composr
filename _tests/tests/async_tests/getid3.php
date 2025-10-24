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
class getid3_test_set extends cms_test_case
{
    public function testGetId3()
    {
        if (!addon_installed('getid3')) {
            $this->assertTrue(false, 'The getid3 addon must be installed for this test to run');
            return;
        }

        require_code('galleries2');
        $result = get_video_details_from_file(get_file_base() . '/_tests/assets/images/crop_both_32x18_16x9.png', 'crop_both_32x18_16x9.png');
        $this->assertTrue($result[0] == 32);
        $this->assertTrue($result[1] == 18);
    }
}
