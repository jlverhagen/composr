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
class gravatars_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }
    }

    public function testSimple()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        $url = find_script('gravatar') . '?id=' . strval($GLOBALS['FORUM_DRIVER']->get_guest_id());
        $result = cms_http_request($url, ['no_redirect' => true]);
        $this->assertTrue($result->message == '302');

        // Test with occhris's gravatar, as we need to do something
        $GLOBALS['FORUM_DB']->query_update('f_members', ['m_email_address' => 'chris@ocproducts.com'], ['m_username' => 'test', 'm_email_address' => '']);
        $member_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'id', ['m_email_address' => 'chris@ocproducts.com']);
        if ($member_id !== null) {
            $url = find_script('gravatar') . '?id=' . strval($member_id);
            $result = cms_http_request($url);
            $this->assertTrue(strpos($result->download_mime_type, 'image/') === 0);

            $GLOBALS['FORUM_DB']->query_update('f_members', ['m_email_address' => ''], ['m_username' => 'test']);
        }
    }
}
