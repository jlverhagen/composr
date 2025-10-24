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
class browser_upgrade_suggest_test_set extends cms_test_case
{
    public function testBrowserLib()
    {
        if (!addon_installed('browser_detect')) {
            $this->assertTrue(false, 'The browser_detect addon must be installed for this test to run');
            return;
        }

        require_code('browser_detect');

        $browser = new Browser('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1');
        $this->assertTrue($browser->getBrowser() == Browser::BROWSER_FIREFOX);
        $this->assertTrue(intval($browser->getVersion()) == 15, $browser->getVersion());
    }
}
