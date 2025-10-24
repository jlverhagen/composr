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

// Twitter API will no longer be free and hence we will not be able to auto-test it so easily

/**
 * Composr test case class (unit testing).
 */
class __api_twitter_test_set extends cms_test_case
{
    public function testTwitterApi()
    {
        $this->load_key_options('twitter_');

        require_code('health_check');
        $this->run_health_check('API connections', 'Twitter', CHECK_CONTEXT__TEST_SITE, true);
    }
}
