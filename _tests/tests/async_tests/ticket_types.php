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
class ticket_types_test_set extends cms_test_case
{
    protected $ticket_type_id;

    public function setUp()
    {
        parent::setUp();

        require_lang('tickets');
        require_code('tickets');
        require_code('tickets2');

        $this->ticket_type_id = add_ticket_type('platinum', 0, 0);
        $ticket_type_name = $GLOBALS['SITE_DB']->query_select_value('ticket_types', 'ticket_type_name', ['id' => $this->ticket_type_id]);
        $this->assertTrue('platinum' == get_translated_text($ticket_type_name));
    }

    public function testEditTicketType()
    {
        edit_ticket_type($this->ticket_type_id, 'gold', 0, 0);
        $ticket_type_name = $GLOBALS['SITE_DB']->query_select_value('ticket_types', 'ticket_type_name', ['id' => $this->ticket_type_id]);
        $this->assertTrue('gold' == get_translated_text($ticket_type_name));
    }

    public function tearDown()
    {
        delete_ticket_type($this->ticket_type_id);

        parent::tearDown();
    }
}
