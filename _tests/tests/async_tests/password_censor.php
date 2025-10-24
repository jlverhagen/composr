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
class password_censor_test_set extends cms_test_case
{
    public function testCensorWorks()
    {
        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }

        $this->establish_admin_session();

        require_code('cns_topics');
        require_code('cns_posts');
        require_code('cns_forums');
        require_code('cns_posts_action');
        require_code('cns_posts_action2');
        require_code('cns_posts_action3');
        require_code('cns_topics_action');
        require_code('cns_topics_action2');

        $test_password = 'abcxxxx';

        $_forum = get_option('ticket_forum_name');
        if (is_numeric($_forum)) {
            $forum_id = intval($_forum);
        } else {
            $forum_id = $GLOBALS['FORUM_DRIVER']->forum_id_from_name($_forum);
        }

        $topic_id = cns_make_topic($forum_id, 'Test');
        $post_id = cns_make_post($topic_id, '', 'Password: ' . $test_password, 0, false, null, 0, null, null, null, null, null, null, null, true, true, null, true, '', null, false, false, false);

        require_code('password_censor');
        password_censor(true, false, 0);

        $GLOBALS['FORUM_DB']->text_lookup_original_cache = [];
        $GLOBALS['FORUM_DB']->text_lookup_cache = [];

        $text = get_translated_text($GLOBALS['FORUM_DB']->query_select_value('f_posts', 'p_post', ['id' => $post_id]), $GLOBALS['FORUM_DB']);

        $this->assertTrue(strpos($text, $test_password) === false);
    }
}
