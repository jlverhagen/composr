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
class chatrooms_test_set extends cms_test_case
{
    protected $chatroom_id;

    public function setUp()
    {
        parent::setUp();

        require_code('chat');
        require_code('chat2');

        $this->chatroom_id = add_chatroom('test_message', 'test_chat_room', 4, '', '2,3,4,5,6,7,8,9,10', '', '', 'EN', 0);
        $this->assertTrue('test_chat_room' == $GLOBALS['SITE_DB']->query_select_value('chat_rooms', 'room_name', ['id' => $this->chatroom_id]));
    }

    public function testEditChatroom()
    {
        edit_chatroom($this->chatroom_id, 'test message 1', 'test_chat_room1', 4, '', '2,3,4,5,6,7,8,9,10', '', '', 'EN');
        $this->assertTrue('test_chat_room1' == $GLOBALS['SITE_DB']->query_select_value('chat_rooms', 'room_name', ['id' => $this->chatroom_id]));
    }

    public function tearDown()
    {
        delete_chatroom($this->chatroom_id);

        parent::tearDown();
    }
}
