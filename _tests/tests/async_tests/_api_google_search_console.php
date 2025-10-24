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
class _api_google_search_console_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        $this->load_key_options('google');
    }

    public function testGoogleSearchConsoleQuerying()
    {
        if (!addon_installed('google_search_console')) {
            $this->assertTrue(false, 'google_search_console addon is needed for this test');
            return;
        }

        require_code('oauth');
        $refresh_token = get_oauth_refresh_token('google_search_console');
        if ($refresh_token === null) {
            $this->assertTrue(false, 'We have set the API key etc we need, but oAuth is still needed to establish an API connection');
            return;
        }

        require_code('health_check');
        $this->run_health_check('API connections', 'Google Search Console', CHECK_CONTEXT__LIVE_SITE, true);
    }
}
