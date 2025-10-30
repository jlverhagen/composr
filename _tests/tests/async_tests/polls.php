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
class polls_test_set extends cms_test_case
{
    protected $poll_id;
    protected $topic_id;

    public function setUp()
    {
        parent::setUp();

        require_code('polls');
        require_code('polls2');

        $this->poll_id = add_poll('Who are you ?', 'a', 'b', 'c');

        $this->assertTrue('Who are you ?' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('poll', 'question', ['id' => $this->poll_id])));
    }

    public function testPollVote()
    {
        $member_id = $this->get_canonical_member_id('admin'); // In case of low permissions
        if ($member_id === null) {
            $member_id = get_member(); // Probably would work anyway
        }
        vote_in_poll($this->poll_id, 2, null, $member_id);

        $poll_details = $GLOBALS['SITE_DB']->query_select('poll', ['*'], ['id' => $this->poll_id], '', 1);
        $this->assertTrue(array_key_exists(0, $poll_details));

        $this->assertTrue($poll_details[0]['votes2'] == 1, 'Got ' . strval($poll_details[0]['votes2']));
    }

    public function testEditPoll()
    {
        edit_poll($this->poll_id, 'Who am I?', 'a', 'b', 'c', '', '', '', '', '', '', '', 3, 1, 1, 1, '');

        $this->assertTrue('Who am I?' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('poll', 'question', ['id' => $this->poll_id])));
    }

    public function tearDown()
    {
        delete_poll($this->poll_id);

        parent::tearDown();
    }
}
