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
class mobile_detect_test_set extends cms_test_case
{
    public function testUAs()
    {
        // Desktop
        $this->assertTrue(!is_mobile('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36', false));
        $this->assertTrue(!is_mobile('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36', true));

        // Chrome OS
        $this->assertTrue(!is_mobile('Mozilla/5.0 (X11; CrOS x86_64 14150.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', false));
        $this->assertTrue(!is_mobile('Mozilla/5.0 (X11; CrOS x86_64 14150.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', true));

        // iOS phone
        $this->assertTrue(is_mobile('Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1', false));
        $this->assertTrue(is_mobile('Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1', true));

        // iOS tablet
        $this->assertTrue(!is_mobile('Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', false));
        $this->assertTrue(is_mobile('Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', true));

        // Android phone
        $this->assertTrue(is_mobile('Mozilla/5.0 (Linux; Android 12) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.50 Mobile Safari/537.36', false));
        $this->assertTrue(is_mobile('Mozilla/5.0 (Linux; Android 12) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.50 Mobile Safari/537.36', true));

        // Android tablet
        $this->assertTrue(!is_mobile('Mozilla/5.0 (Linux; Android 5.0.2; LG-V410/V41020c Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/34.0.1847.118 Safari/537.36', false));
        $this->assertTrue(is_mobile('Mozilla/5.0 (Linux; Android 5.0.2; LG-V410/V41020c Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/34.0.1847.118 Safari/537.36', true));
    }
}
